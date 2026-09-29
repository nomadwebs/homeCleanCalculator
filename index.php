<?php
require __DIR__ . '/partials.php';
page_head('Calendario · Limpieza', 'home'); ?>
<div class="grid">
  <section class="card">
    <div class="cal-head">
      <button id="prev" aria-label="Mes anterior">‹</button>
      <h2 id="month-title"></h2>
      <button id="next" aria-label="Mes siguiente">›</button>
    </div>
    <div class="cal" id="calendar"></div>
    <p class="hint">Toca un día para añadirlo al pedido; tócalo de nuevo para quitarlo.</p>
    <div class="legend"><span><i class="dot hol"></i> Festivo</span><span><i class="dot paid"></i> Pagado</span><span><i class="dot sel"></i> Pendiente</span></div>
    <ul id="holiday-list" class="hol-list"></ul>
  </section>

  <section class="card">
    <div class="order-head">
      <h2>Pedido de <span id="order-title"></span></h2>
      <span id="status" class="badge"></span>
    </div>
    <table id="lines">
      <thead><tr><th>Día</th><th>Horas</th><th>€/hora</th><th class="r">Importe</th><th title="Pagado">Pag.</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
    <p id="empty" class="hint">Aún no hay días en este mes.</p>
    <div class="total">
      <div><span id="sum-days">0</span> días · <span id="sum-hours">0</span> h</div>
      <div class="amount"><span id="total">0,00</span> €</div>
    </div>
    <div class="pay-box four">
      <div><span>Arrastre anterior</span><b id="carry-in">0,00 €</b></div>
      <div><span>Total del mes</span><b id="due-total">0,00 €</b></div>
      <div><span>Pagado realmente</span><b id="paid-real">0,00 €</b></div>
      <div><span>Pendiente</span><b id="pending-total" class="acc">0,00 €</b></div>
    </div>
    <p class="hint" id="carry-note"></p>
    <p class="hint" id="balance-all"></p>
    <ul id="payments" class="hol-list big"></ul>
    <div class="actions">
      <a id="summary-link" class="btn" href="#">Ver resumen</a>
      <button id="pay-btn">Registrar pago</button>
      <button id="close-btn" class="primary"></button>
    </div>
  </section>
</div>
<div id="modal" class="modal" hidden>
  <form id="pay-form" class="card">
    <h2>Registrar pago</h2>
    <p class="hint">Marca los días que cubre este pago. La cantidad puede ser distinta (p. ej. redondeada); la diferencia queda reflejada en el saldo.</p>
    <div id="pay-lines" class="pay-lines"></div>
    <label>Importe de esos días <b id="pay-due"></b></label>
    <label>Cantidad pagada realmente (€)<input type="number" step="0.01" min="0" name="amount" required></label>
    <label>Fecha del pago<input type="date" name="date" required></label>
    <label>Nota (opcional)<input type="text" name="note" maxlength="200"></label>
    <p class="hint" id="pay-diff"></p>
    <div class="actions"><button type="button" id="pay-cancel">Cancelar</button><button class="primary" type="submit">Guardar pago</button></div>
  </form>
</div>
<?php page_foot('app.js');
