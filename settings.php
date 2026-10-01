<?php
require __DIR__ . '/db.php';
require __DIR__ . '/partials.php';
$s = get_settings();
$PALETTE = ['#0f766e' => 'Verde azulado', '#b45309' => 'Naranja', '#7c3aed' => 'Violeta', '#be123c' => 'Rojo',
            '#1d4ed8' => 'Azul', '#4d7c0f' => 'Verde', '#be185d' => 'Rosa', '#475569' => 'Gris'];
$workers = list_workers();
$usedBy = [];
foreach ($workers as $w) $usedBy[strtolower($w['color'])] = $w;
/** Selector de color: los colores ya usados por otra persona aparecen tachados y no se pueden elegir. */
function swatches(array $palette, array $usedBy, string $selected, int $selfId): void { ?>
  <div class="color-pick"><span class="lbl">Color de esta persona:</span>
  <?php foreach ($palette as $hex => $label):
      $other = $usedBy[$hex] ?? null; $taken = $other && $other['id'] !== $selfId; ?>
    <label class="sw <?= $taken ? 'taken' : '' ?>" title="<?= $label . ($taken ? ' — en uso por ' . htmlspecialchars($other['name']) : '') ?>">
      <input type="radio" name="color" value="<?= $hex ?>" <?= strcasecmp($selected, $hex) === 0 ? 'checked' : '' ?> <?= $taken ? 'disabled' : '' ?>>
      <span style="--c: <?= $hex ?>"></span>
    </label>
  <?php endforeach; ?></div>
<?php }
page_head('Configuración · Gastos del hogar', 'settings'); ?>
<section class="card narrow">
  <h2>Personas</h2>
  <p class="hint">Cada persona tiene su propio calendario, tarifa, pagos y saldo. La tarifa y las horas son los valores por defecto de los días nuevos; cada línea se puede editar después y los cambios no afectan a los días ya añadidos.</p>
  <?php foreach ($workers as $w): ?>
  <form class="worker-row" data-id="<?= $w['id'] ?>" style="--c: <?= htmlspecialchars($w['color']) ?>">
    <div class="f">
      <label>Nombre<input type="text" name="name" value="<?= htmlspecialchars($w['name']) ?>" maxlength="60" required></label>
      <label>€ / hora<input type="number" name="hourly_rate" step="0.01" min="0" value="<?= $w['hourly_rate'] ?>" required></label>
      <label>Horas por visita<input type="number" name="default_hours" step="0.25" min="0.25" max="24" value="<?= $w['default_hours'] ?>" required></label>
    </div>
    <?php swatches($PALETTE, $usedBy, $w['color'], $w['id']); ?>
    <div class="actions"><button class="primary" type="submit">Guardar</button><button type="button" class="w-del">Eliminar</button></div>
  </form>
  <?php endforeach; ?>
  <details style="margin-top:12px"><summary>➕ Añadir otra persona (canguro, jardinero…)</summary>
    <form id="worker-add" class="worker-row" style="--c: #0f766e">
      <div class="f">
        <label>Nombre<input type="text" name="name" maxlength="60" placeholder="Canguro" required></label>
        <label>€ / hora<input type="number" name="hourly_rate" step="0.01" min="0" value="10" required></label>
        <label>Horas por visita<input type="number" name="default_hours" step="0.25" min="0.25" max="24" value="2" required></label>
      </div>
      <?php $free = array_key_first(array_diff_key($PALETTE, $usedBy)) ?? array_key_first($PALETTE); swatches($PALETTE, $usedBy, $free, 0); ?>
      <div class="actions"><button class="primary" type="submit">Añadir persona</button></div>
    </form>
  </details>
</section>

<section class="card narrow" style="margin-top:16px">
  <h2>General</h2>
  <form id="settings-form">
    <label>Comunidad autónoma (para los festivos)
      <select name="region">
        <option value="">Solo festivos nacionales</option>
        <?php foreach (REGIONS as $code => $name): ?>
          <option value="<?= $code ?>" <?= $s['region'] === $code ? 'selected' : '' ?>><?= $name ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="primary" type="submit">Guardar</button>
  </form>
</section>
<section class="card narrow" style="margin-top:16px">
  <div class="order-head">
    <h2>Festivos</h2>
    <select id="hol-year"></select>
  </div>
  <p class="hint">Se descargan automáticamente de <b>date.nager.at</b> (nacionales y de tu comunidad). Los festivos locales del municipio no vienen incluidos: añádelos abajo.</p>
  <ul id="hol-list" class="hol-list big"></ul>
  <button id="hol-import" type="button">Volver a descargar este año</button>
  <form id="hol-form" class="inline">
    <input type="date" name="date" required>
    <input type="text" name="name" placeholder="Nombre (p. ej. San Isidro)" required maxlength="120">
    <button class="primary" type="submit">Añadir</button>
  </form>
</section>
<section class="card narrow" style="margin-top:16px">
  <h2>Base de datos</h2>
  <p class="hint">Ahora estás usando: <b><?= htmlspecialchars(active_db_name()) ?></b> <?= is_demo() ? '(pruebas)' : '(real)' ?>.
    La base de pruebas es una copia independiente con datos de ejemplo, ideal para probar sin miedo. El cambio solo afecta a este navegador.</p>
  <div class="actions">
    <button id="mode-btn" type="button" data-mode="<?= is_demo() ? 'real' : 'demo' ?>" class="<?= is_demo() ? 'primary' : '' ?>">
      <?= is_demo() ? 'Volver a la base REAL' : 'Cambiar a la base de PRUEBAS' ?></button>
    <?php if (is_demo()): ?><button id="demo-reset" type="button">Restaurar datos de ejemplo</button><?php endif; ?>
  </div>
</section>

<section class="card narrow danger" style="margin-top:16px">
  <h2>Zona de peligro</h2>
  <p class="hint">Borra todos los pedidos, días y pagos registrados. No se puede deshacer: haz antes una copia de seguridad si quieres conservarlos.</p>
  <button id="reset-btn" type="button" class="danger-btn">Borrar todos los datos…</button>
</section>

<div id="reset-modal" class="modal" hidden>
  <div id="reset-step1" class="card">
    <h2>¿Borrar todos los datos?</h2>
    <p>Se eliminarán <b>todos los pedidos, días trabajados y pagos</b>. Esta acción no se puede deshacer.</p>
    <div class="actions"><button type="button" class="reset-cancel">Cancelar</button><button type="button" id="reset-next" class="danger-btn">Sí, continuar</button></div>
  </div>
  <form id="reset-form" class="card" hidden>
    <h2>⚠️ Confirmación final</h2>
    <p>Última oportunidad: una vez borrado, no hay vuelta atrás.</p>
    <label class="chk"><input type="checkbox" name="everything"> Borrar también las personas (se queda solo una con valores por defecto), la comunidad y los festivos</label>
    <label>Para confirmar, escribe <b>sí borrar</b>
      <input type="text" name="phrase" autocomplete="off" placeholder="sí borrar">
    </label>
    <div class="actions"><button type="button" class="reset-cancel">Cancelar</button><button class="danger-btn" id="reset-go" type="submit" disabled>Borrar definitivamente</button></div>
  </form>
</div>
<?php page_foot('settings.js');
