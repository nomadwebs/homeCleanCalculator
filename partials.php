<?php
require_once __DIR__ . '/db.php';
/** URL de un fichero de assets con su fecha de modificación, para que el navegador nunca use una versión antigua en caché. */
function asset(string $path): string { return $path . '?v=' . (@filemtime(__DIR__ . '/' . $path) ?: 1); }

function page_head(string $title, string $active): void {
    $workers = list_workers(); $me = current_worker(); ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="<?= asset('assets/style.css') ?>">
<script>window.HCC = <?= json_encode(['worker' => $me]) ?>;</script>
</head>
<body style="--wc: <?= htmlspecialchars($me['color']) ?>">
<?php if (is_demo()): ?>
<div class="demo-banner">🧪 MODO PRUEBAS — estás usando la base de datos de ejemplo (<b><?= htmlspecialchars(active_db_name()) ?></b>), no la real.</div>
<?php endif; ?>
<header class="top">
  <h1>🏠 Gastos del hogar</h1>
  <?php if (count($workers) > 1): ?>
  <div class="who" role="tablist" aria-label="Persona">
    <?php foreach ($workers as $w): ?>
      <button type="button" data-id="<?= $w['id'] ?>" class="<?= $w['id'] === $me['id'] ? 'on' : '' ?>" style="--c: <?= htmlspecialchars($w['color']) ?>"><?= htmlspecialchars($w['name']) ?></button>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <nav>
    <a href="index.php" class="<?= $active === 'home' ? 'on' : '' ?>">Calendario</a>
    <a href="history.php" class="<?= $active === 'history' ? 'on' : '' ?>">Historial</a>
    <a href="settings.php" class="<?= $active === 'settings' ? 'on' : '' ?>">Configuración</a>
  </nav>
</header>
<main>
<?php }
function page_foot(string $script): void { ?>
</main>
<div id="toast" role="status"></div>
<script>
document.querySelectorAll('.who button').forEach(b => b.onclick = async () => {
  await fetch('api.php', {method: 'POST', body: JSON.stringify({action: 'set_worker', id: +b.dataset.id})});
  location.reload();
});
</script>
<script src="<?= asset('assets/' . $script) ?>"></script>
</body>
</html>
<?php }
