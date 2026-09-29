<?php
function page_head(string $title, string $active): void { ?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?></title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="top">
  <h1>🧹 Gastos de limpieza</h1>
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
<script src="assets/<?= $script ?>"></script>
</body>
</html>
<?php }
