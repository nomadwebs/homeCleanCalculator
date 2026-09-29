<?php
require __DIR__ . '/partials.php';
page_head('Historial · Limpieza', 'history'); ?>
<section class="card">
  <div class="cal-head">
    <button id="prev" aria-label="Año anterior">‹</button>
    <h2 id="year-title"></h2>
    <button id="next" aria-label="Año siguiente">›</button>
  </div>
  <div class="pay-box four">
    <div><span>Total del año</span><b id="y-total"></b></div>
    <div><span>Pagado realmente</span><b id="y-paid"></b></div>
    <div><span>Saldo a final de año</span><b id="y-pending" class="acc"></b></div>
    <div><span>Días · horas</span><b id="y-days"></b></div>
  </div>
  <div id="bars" class="bars"></div>
  <table id="months">
    <thead><tr><th>Mes</th><th class="r">Días</th><th class="r">Horas</th><th class="r">Total</th><th class="r">Pagado real</th><th class="r">Diferencia</th><th class="r">Saldo acumulado</th><th>Estado</th></tr></thead>
    <tbody></tbody>
    <tfoot></tfoot>
  </table>
  <p class="hint"><b>Diferencia</b> = total del mes − pagado realmente. <b>Saldo acumulado</b> = diferencia del mes + lo arrastrado de los meses anteriores. Positivo: queda por pagar; negativo: pagado de más.</p>
</section>
<?php page_foot('history.js');
