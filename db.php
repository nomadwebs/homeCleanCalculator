<?php
// Conexión local a XAMPP. Crea la base de datos y las tablas la primera vez.
const DB_HOST = '127.0.0.1';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'home_clean_calculator';

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, $opts);
    $exists = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'")->fetchColumn();
    if (!$exists) {
        $pdo->exec(file_get_contents(__DIR__ . '/schema.sql'));
    }
    $pdo->exec('USE `' . DB_NAME . '`');
    migrate($pdo);
    return $pdo;
}

function get_settings(): array {
    $s = db()->query('SELECT hourly_rate, default_hours, region FROM settings WHERE id = 1')->fetch();
    return ['hourly_rate' => (float)$s['hourly_rate'], 'default_hours' => (float)$s['default_hours'], 'region' => $s['region']];
}

/** Actualiza bases de datos creadas con versiones anteriores del esquema. */
function migrate(PDO $pdo): void {
    $has = fn(string $t, string $c) => (bool)$pdo->query("SHOW COLUMNS FROM `$t` LIKE '$c'")->fetch();
    if (!$has('settings', 'region')) $pdo->exec('ALTER TABLE settings ADD region VARCHAR(6) NULL');
    if (!$has('order_lines', 'paid_at')) $pdo->exec('ALTER TABLE order_lines ADD paid_at DATETIME NULL');
    $pdo->exec("CREATE TABLE IF NOT EXISTS payments (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      order_id INT UNSIGNED NOT NULL,
      paid_on DATE NOT NULL,
      amount DECIMAL(10,2) NOT NULL,
      note VARCHAR(200) NULL,
      CONSTRAINT fk_pay_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB");
    if (!$has('order_lines', 'payment_id')) $pdo->exec('ALTER TABLE order_lines ADD payment_id INT UNSIGNED NULL');
    $pdo->exec("CREATE TABLE IF NOT EXISTS holidays (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      holiday_date DATE NOT NULL UNIQUE,
      name VARCHAR(120) NOT NULL,
      source ENUM('api','manual') NOT NULL DEFAULT 'manual'
    ) ENGINE=InnoDB");
}

const REGIONS = [
    'ES-AN' => 'Andalucía', 'ES-AR' => 'Aragón', 'ES-AS' => 'Asturias', 'ES-IB' => 'Baleares',
    'ES-CN' => 'Canarias', 'ES-CB' => 'Cantabria', 'ES-CL' => 'Castilla y León', 'ES-CM' => 'Castilla-La Mancha',
    'ES-CT' => 'Cataluña', 'ES-CE' => 'Ceuta', 'ES-VC' => 'Comunidad Valenciana', 'ES-EX' => 'Extremadura',
    'ES-GA' => 'Galicia', 'ES-RI' => 'La Rioja', 'ES-MD' => 'Madrid', 'ES-ML' => 'Melilla',
    'ES-MC' => 'Murcia', 'ES-NC' => 'Navarra', 'ES-PV' => 'País Vasco',
];

/** Descarga los festivos del año (nacionales + de la comunidad configurada) desde Nager.Date. */
function import_holidays(int $year): int {
    $pdo = db();
    $region = $pdo->query('SELECT region FROM settings WHERE id = 1')->fetchColumn() ?: null;
    $ctx = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
    $raw = @file_get_contents("https://date.nager.at/api/v3/PublicHolidays/$year/ES", false, $ctx);
    $list = $raw ? json_decode($raw, true) : null;
    if (!is_array($list)) throw new RuntimeException('No se pudo contactar con el servicio de festivos');
    $st = $pdo->prepare("INSERT INTO holidays (holiday_date, name, source) VALUES (?, ?, 'api')
                         ON DUPLICATE KEY UPDATE name = IF(source = 'api', VALUES(name), name)");
    $n = 0;
    foreach ($list as $h) {
        $applies = !empty($h['global']) || ($region && in_array($region, $h['counties'] ?? [], true));
        if (!$applies) continue;
        $st->execute([$h['date'], $h['localName'] ?: $h['name']]);
        $n++;
    }
    return $n;
}

/** Festivos del año; si aún no hay ninguno intenta importarlos (sin fallar si no hay conexión). */
function holidays_for_year(int $year): array {
    $pdo = db();
    $q = $pdo->prepare('SELECT id, holiday_date, name, source FROM holidays WHERE YEAR(holiday_date) = ? ORDER BY holiday_date');
    $q->execute([$year]);
    $rows = $q->fetchAll();
    if (!$rows) {
        try { import_holidays($year); $q->execute([$year]); $rows = $q->fetchAll(); } catch (Throwable $e) {}
    }
    return array_map(fn($r) => ['id' => (int)$r['id'], 'date' => $r['holiday_date'], 'name' => $r['name'], 'source' => $r['source']], $rows);
}
