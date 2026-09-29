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
<?php page_foot('settings.js');
