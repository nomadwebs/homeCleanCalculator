<?php
require __DIR__ . '/db.php';
require __DIR__ . '/partials.php';
$s = get_settings();
page_head('Configuración · Limpieza', 'settings'); ?>
<section class="card narrow">
  <h2>Configuración general</h2>
  <p class="hint">Valores por defecto para los días nuevos. Cada línea del pedido se puede editar después; los cambios aquí no afectan a los días ya añadidos.</p>
  <form id="settings-form">
    <label>Tarifa por hora (€)
      <input type="number" name="hourly_rate" step="0.01" min="0" value="<?= $s['hourly_rate'] ?>" required>
    </label>
    <label>Horas por defecto por visita
      <input type="number" name="default_hours" step="0.25" min="0.25" max="24" value="<?= $s['default_hours'] ?>" required>
    </label>
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
    <label class="chk"><input type="checkbox" name="everything"> Borrar también la configuración (tarifa, horas, comunidad) y los festivos</label>
    <label>Para confirmar, escribe <b>sí borrar</b>
      <input type="text" name="phrase" autocomplete="off" placeholder="sí borrar">
    </label>
    <div class="actions"><button type="button" class="reset-cancel">Cancelar</button><button class="danger-btn" id="reset-go" type="submit" disabled>Borrar definitivamente</button></div>
  </form>
</div>
<?php page_foot('settings.js');
