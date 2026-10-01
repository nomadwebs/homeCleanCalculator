<?php
// Conexión local a XAMPP. Crea la base de datos y las tablas la primera vez.
// Los ajustes de conexión viven en config.php (copia de config.sample.php). Si no existe, se usan los de XAMPP por defecto.
if (file_exists(__DIR__ . '/config.php')) require __DIR__ . '/config.php';
defined('DB_HOST') || define('DB_HOST', '127.0.0.1');
defined('DB_PORT') || define('DB_PORT', 3306);
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');
defined('DB_NAME') || define('DB_NAME', 'home_clean_calculator');

/** Modo de pruebas: se activa por navegador (cookie) desde Configuración y usa la base "<nombre>_demo". */
function is_demo(): bool { return ($_COOKIE['hcc_mode'] ?? '') === 'demo'; }
function active_db_name(): string { return DB_NAME . (is_demo() ? '_demo' : ''); }

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    $pdo = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4', DB_USER, DB_PASS, $opts);
    $name = active_db_name();
    $exists = $pdo->query("SHOW DATABASES LIKE '" . str_replace('_', '\\_', $name) . "'")->fetchColumn();
    if (!$exists) create_database($pdo, $name);
    $pdo->exec('USE `' . $name . '`');
    migrate($pdo);
    return $pdo;
}

/** Crea la base de datos con su esquema; la de pruebas se rellena además con demo/demo_data.sql. */
function create_database(PDO $pdo, string $name): void {
    $pdo->exec(str_replace('home_clean_calculator', $name, file_get_contents(__DIR__ . '/schema.sql')));
    $demo = __DIR__ . '/demo/demo_data.sql';
    if ($name === DB_NAME . '_demo' && is_file($demo)) {
        $pdo->exec('USE `' . $name . '`');
        migrate($pdo);
        $pdo->exec(str_replace('home_clean_calculator', $name, file_get_contents($demo)));
    }
}

function get_settings(): array {
    $s = db()->query('SELECT region FROM settings WHERE id = 1')->fetch();
    return ['region' => $s['region']];
}

/** Personas a las que se paga. */
function list_workers(): array {
    return array_map(fn($w) => ['id' => (int)$w['id'], 'name' => $w['name'], 'hourly_rate' => (float)$w['hourly_rate'],
                                'default_hours' => (float)$w['default_hours'], 'color' => $w['color']],
        db()->query('SELECT * FROM workers ORDER BY id')->fetchAll());
}
function get_worker(int $id): ?array {
    foreach (list_workers() as $w) if ($w['id'] === $id) return $w;
    return null;
}
/** Persona seleccionada en este navegador (cookie); si no hay o no existe, la primera. */
function current_worker(): array {
    return get_worker((int)($_COOKIE['hcc_worker'] ?? 0)) ?? list_workers()[0];
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
    // --- Varias personas + gastos adicionales ---
    $pdo->exec("CREATE TABLE IF NOT EXISTS workers (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(60) NOT NULL,
      hourly_rate DECIMAL(8,2) NOT NULL DEFAULT 10.00,
      default_hours DECIMAL(4,2) NOT NULL DEFAULT 4.00,
      color VARCHAR(7) NOT NULL DEFAULT '#0f766e'
    ) ENGINE=InnoDB");
    if (!$pdo->query('SELECT 1 FROM workers LIMIT 1')->fetch()) {
        // Los datos existentes pasan a la primera persona, con la tarifa que había en la configuración
        $rate = 10.0; $hours = 4.0;
        if ($has('settings', 'hourly_rate')) {
            $s = $pdo->query('SELECT hourly_rate, default_hours FROM settings WHERE id = 1')->fetch();
            if ($s) { $rate = (float)$s['hourly_rate']; $hours = (float)$s['default_hours']; }
        }
        $pdo->prepare("INSERT INTO workers (id, name, hourly_rate, default_hours) VALUES (1, 'Limpieza', ?, ?)")->execute([$rate, $hours]);
    }
    if (!$has('orders', 'worker_id')) {
        $pdo->exec('ALTER TABLE orders ADD worker_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id');
        $pdo->exec('ALTER TABLE orders DROP INDEX uq_year_month, ADD UNIQUE KEY uq_worker_month (worker_id, year, month)');
        $pdo->exec('ALTER TABLE orders ADD CONSTRAINT fk_order_worker FOREIGN KEY (worker_id) REFERENCES workers(id)');
    }
    if (!$has('order_lines', 'extra_amount')) {
        $pdo->exec('ALTER TABLE order_lines ADD extra_amount DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER hourly_rate, ADD extra_note VARCHAR(120) NULL AFTER extra_amount');
    }
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
