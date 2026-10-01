const MONTHS = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fmt = n => n.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2});
const $ = id => document.getElementById(id);

let cur = new Date();
const qs = new URLSearchParams(location.search);
let year = +qs.get('year') || cur.getFullYear(), month = +qs.get('month') || cur.getMonth() + 1;
let state = null;
const timers = {};
const amtOf = l => Math.round((l.hours * l.rate + (l.extra || 0)) * 100) / 100;

function toast(msg, err) {
  const t = $('toast'); t.textContent = msg; t.className = 'show' + (err ? ' err' : '');
  clearTimeout(toast.t); toast.t = setTimeout(() => t.className = '', 2200);
}

async function call(payload) {
  const r = await fetch('api.php', {method: 'POST', body: JSON.stringify({year, month, worker_id: HCC.worker.id, ...payload})});
  const d = await r.json();
  if (!r.ok) { toast(d.error || 'Error', true); await load(); return null; }
  return d;
}

async function load() {
  const r = await fetch(`api.php?action=state&year=${year}&month=${month}&worker=${HCC.worker.id}`);
  state = await r.json();
  render();
}

function render() {
  const closed = state.status === 'closed';
  $('month-title').textContent = `${MONTHS[month - 1]} ${year}`;
  $('order-title').textContent = `${MONTHS[month - 1]} ${year} · ${HCC.worker.name}`;
  renderCalendar(closed);
  renderLines(closed);
  $('holiday-list').innerHTML = state.holidays.map(h => `<li><span>${+h.date.slice(8)}</span> ${h.name.replace(/</g, '&lt;')}</li>`).join('');
  const st = $('status');
  st.textContent = closed ? 'Cerrado' : 'Abierto';
  st.className = 'badge ' + (closed ? 'closed' : 'open');
  const btn = $('close-btn');
  btn.textContent = closed ? 'Reabrir pedido' : 'Cerrar pedido del mes';
  btn.className = closed ? '' : 'primary';
  $('summary-link').href = `summary.php?year=${year}&month=${month}`;
  updateTotals();
}

function renderCalendar(closed) {
  const cal = $('calendar');
  cal.innerHTML = '';
  ['L','M','X','J','V','S','D'].forEach(d => cal.insertAdjacentHTML('beforeend', `<div class="dow">${d}</div>`));
  const first = new Date(year, month - 1, 1);
  const offset = (first.getDay() + 6) % 7;
  const days = new Date(year, month, 0).getDate();
  for (let i = 0; i < offset; i++) cal.insertAdjacentHTML('beforeend', '<div></div>');
  const picked = new Set(state.lines.map(l => l.date));
  const paidSet = new Set(state.lines.filter(l => l.paid).map(l => l.date));
  const hol = Object.fromEntries(state.holidays.map(h => [h.date, h.name]));
  const today = new Date();
  for (let d = 1; d <= days; d++) {
    const iso = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const b = document.createElement('button');
    b.textContent = d;
    if (hol[iso]) b.title = hol[iso];
    b.className = 'day' + (picked.has(iso) ? ' sel' : '') + (paidSet.has(iso) ? ' paid' : '') + (hol[iso] ? ' hol' : '') +
      (today.getFullYear() === year && today.getMonth() + 1 === month && today.getDate() === d ? ' today' : '');
    b.disabled = closed;
    b.onclick = async () => { const s = await call({action: 'toggle_day', date: iso}); if (s) { state = s; render(); } };
    cal.appendChild(b);
  }
}

function renderLines(closed) {
  const tb = document.querySelector('#lines tbody');
  tb.innerHTML = '';
  $('empty').style.display = state.lines.length ? 'none' : '';
  $('lines').style.display = state.lines.length ? '' : 'none';
  for (const l of state.lines) {
    const [y, m, d] = l.date.split('-').map(Number);
    const wd = new Date(y, m - 1, d).toLocaleDateString('es-ES', {weekday: 'short'});
    const tr = document.createElement('tr');
    tr.dataset.id = l.id;
    tr.innerHTML = `<td>${wd} ${d}</td>
      <td><input class="h" type="number" step="0.25" min="0" max="24" value="${l.hours}" ${closed || l.paid ? 'disabled' : ''}></td>
      <td><input class="p" type="number" step="0.01" min="0" value="${l.rate}" ${closed || l.paid ? 'disabled' : ''}></td>
      <td class="extra"><input class="e" type="number" step="0.01" min="0" value="${l.extra || ''}" placeholder="0" ${closed || l.paid ? 'disabled' : ''}>
        <input class="en" type="text" maxlength="120" placeholder="concepto" value="" ${closed || l.paid ? 'disabled' : ''}></td>
      <td class="r amt"></td>
      <td><input class="pd" type="checkbox" ${l.paid ? 'checked' : ''} title="Registrar pago de este día"></td>
      <td>${closed || l.paid ? '' : '<button class="x" title="Quitar día">✕</button>'}</td>`;
    tr.querySelector('.en').value = l.extra_note || '';
    tr.querySelectorAll('.h, .p, .e, .en').forEach(i => i.addEventListener('input', () => onEdit(l, tr)));
    tr.querySelector('.pd').onchange = async e => {
      if (e.target.checked) { e.target.checked = false; openPay([l.id]); return; }
      if (!confirm('¿Quitar la marca de pagado de este día? El dinero registrado en el pago se conserva.')) { e.target.checked = true; return; }
      const s = await call({action: 'unpay_line', id: l.id});
      if (s) { state = s; render(); }
    };
    const x = tr.querySelector('.x');
    if (x) x.onclick = async () => { const s = await call({action: 'delete_line', id: l.id}); if (s) { state = s; render(); } };
    tb.appendChild(tr);
  }
}

// Recalcula en vivo y guarda con un pequeño retardo tras dejar de escribir
function onEdit(l, tr) {
  l.hours = parseFloat(tr.querySelector('.h').value) || 0;
  l.rate = parseFloat(tr.querySelector('.p').value) || 0;
  l.extra = parseFloat(tr.querySelector('.e').value) || 0;
  l.extra_note = tr.querySelector('.en').value;
  updateTotals();
  clearTimeout(timers[l.id]);
  timers[l.id] = setTimeout(async () => {
    const s = await call({action: 'update_line', id: l.id, hours: l.hours, rate: l.rate, extra: l.extra, extra_note: l.extra_note});
    if (s) { state.total = s.total; updateTotals(); toast('Guardado ✓'); }
  }, 400);
}

function updateTotals() {
  let total = 0, hours = 0;
  document.querySelectorAll('#lines tbody tr').forEach(tr => {
    const l = state.lines.find(x => x.id == tr.dataset.id);
    const amt = amtOf(l);
    tr.querySelector('.amt').textContent = fmt(amt) + ' €';
    total += amt; hours += l.hours;
  });
  $('total').textContent = fmt(state.status === 'closed' ? state.total : total);
  const due = state.status === 'closed' ? state.total : total;
  $('due-total').textContent = fmt(due) + ' €';
  $('paid-real').textContent = fmt(state.paid_real) + ' €';
  const carry = state.carry_in;
  $('carry-in').textContent = fmt(carry) + ' €';
  $('pending-total').textContent = fmt(carry + due - state.paid_real) + ' €';
  $('carry-note').textContent = Math.abs(carry) < 0.005 ? '' : carry < 0
    ? `El arrastre de ${fmt(-carry)} € viene de meses anteriores, donde se pagó de más: se descuenta de este mes.`
    : `El arrastre de ${fmt(carry)} € viene de meses anteriores, donde quedó algo sin pagar: se suma a este mes.`;
  const b = state.balance_all, pend = b.pending;
  $('balance-all').innerHTML = `<b>Saldo acumulado (todos los meses):</b> ` + (Math.abs(pend) < 0.005
    ? 'estás al día ✓'
    : pend > 0 ? `quedan <b>${fmt(pend)} €</b> por pagar` : `has pagado <b>${fmt(-pend)} € de más</b> (se descontará del siguiente pago)`) +
    ` <span class="mut">· debido ${fmt(b.due)} € · pagado ${fmt(b.paid)} €</span>`;
  $('payments').innerHTML = state.payments.map(p => `<li data-id="${p.id}"><span>${p.date.split('-').reverse().join('/')}</span>
    <span class="nm">${fmt(p.amount)} € <small>${p.days} día${p.days === 1 ? '' : 's'}${p.note ? ' · ' + p.note.replace(/</g, '&lt;') : ''}</small></span>
    <button class="x" title="Eliminar pago">✕</button></li>`).join('');
  document.querySelectorAll('#payments li').forEach(li => li.querySelector('.x').onclick = async () => {
    if (!confirm('¿Eliminar este pago? Los días que cubría volverán a quedar pendientes.')) return;
    const s = await call({action: 'delete_payment', id: +li.dataset.id});
    if (s) { state = s; render(); }
  });
  $('pay-btn').disabled = false;
  $('sum-days').textContent = state.lines.length;
  $('days-word').textContent = state.lines.length === 1 ? 'día' : 'días';
  $('sum-hours').textContent = hours.toLocaleString('es-ES');
}

$('close-btn').onclick = async () => {
  if (state.status === 'open' && !confirm(`¿Cerrar el pedido de ${MONTHS[month-1]}? Quedará bloqueado con un total de ${fmt(state.lines.reduce((a,l)=>a+amtOf(l),0))} €.`)) return;
  const s = await call({action: state.status === 'open' ? 'close' : 'reopen'});
  if (s) { state = s; render(); }
};
// --- Registro de pagos ---
const modal = $('modal'), pf = $('pay-form');
const lineAmt = amtOf;
function selectedDue() {
  return [...document.querySelectorAll('#pay-lines input:checked')].reduce((a, c) => a + lineAmt(state.lines.find(l => l.id == c.value)), 0);
}
function payRefresh(resetAmount) {
  const due = selectedDue();
  $('pay-due').textContent = fmt(due) + ' €';
  if (resetAmount) pf.amount.value = due.toFixed(2);
  const diff = (parseFloat(pf.amount.value) || 0) - due;
  $('pay-diff').textContent = Math.abs(diff) < 0.005 ? 'Pago exacto.' :
    diff > 0 ? `Pagas ${fmt(diff)} € de más: quedará a tu favor en el siguiente pago.` : `Pagas ${fmt(-diff)} € de menos: quedará pendiente.`;
}
function openPay(ids) {
  const pending = state.lines.filter(l => !l.paid);
  $('pay-lines').innerHTML = pending.length ? pending.map(l => `<label class="chk"><input type="checkbox" value="${l.id}" ${ids ? (ids.includes(l.id) ? 'checked' : '') : 'checked'}>
      ${new Date(l.date + 'T00:00').toLocaleDateString('es-ES', {weekday: 'short', day: 'numeric'})} — ${fmt(lineAmt(l))} €</label>`).join('')
    : '<p class="hint">No hay días pendientes en este mes; se registrará como pago a cuenta.</p>';
  pf.date.value = new Date().toLocaleDateString('sv-SE');
  pf.note.value = '';
  document.querySelectorAll('#pay-lines input').forEach(c => c.onchange = () => payRefresh(true));
  payRefresh(true);
  modal.hidden = false;
}
$('pay-btn').onclick = () => openPay(null);
$('pay-cancel').onclick = () => modal.hidden = true;
pf.amount.oninput = () => payRefresh(false);
pf.onsubmit = async e => {
  e.preventDefault();
  const s = await call({action: 'register_payment', amount: +pf.amount.value, date: pf.date.value, note: pf.note.value,
    line_ids: [...document.querySelectorAll('#pay-lines input:checked')].map(c => +c.value)});
  if (s) { modal.hidden = true; state = s; render(); toast('Pago registrado ✓'); }
};
$('prev').onclick = () => { if (--month < 1) { month = 12; year--; } load(); };
$('next').onclick = () => { if (++month > 12) { month = 1; year++; } load(); };

load();
