<?php
require __DIR__ . '/partials.php';
page_head('Resumen del mes · Limpieza', 'history'); ?>
<section class="card narrow wide" id="sheet">
  <div class="order-head">
    <h2>Resumen de <span id="s-title"></span></h2>
    <span id="s-status" class="badge"></span>
  </div>
  <table id="s-lines">
    <thead><tr><th>Fecha</th><th class="r">Horas</th><th class="r">€/hora</th><th class="r">Importe</th><th>Pago</th></tr></thead>
    <tbody></tbody>
    <tfoot></tfoot>
  </table>
  <p id="s-empty" class="hint" style="display:none">No hay días registrados este mes.</p>
  <div class="pay-box four" id="s-boxes"></div>
  <div id="s-payments"></div>
  <p id="s-notes" class="hint"></p>
  <div class="actions noprint">
    <a class="btn" id="s-back" href="#">← Volver al calendario</a>
    <button id="s-print" class="primary">Imprimir / guardar PDF</button>
  </div>
</section>
<?php page_foot('summary.js');
