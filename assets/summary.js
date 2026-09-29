const MONTHS = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fmt = n => n.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
const $ = id => document.getElementById(id);
const q = new URLSearchParams(location.search);
const now = new Date();
const year = +q.get('year') || now.getFullYear(), month = +q.get('month') || now.getMonth() + 1;

(async () => {
  const s = await (await fetch(`api.php?action=state&year=${year}&month=${month}`)).json();
  const closed = s.status === 'closed';
  $('s-title').textContent = `${MONTHS[month-1]} ${year}`;
  $('s-back').href = `index.php?year=${year}&month=${month}`;
  const st = $('s-status'); st.textContent = closed ? 'Cerrado' : 'Abierto (provisional)'; st.className = 'badge ' + s.status;
  const hol = Object.fromEntries(s.holidays.map(h => [h.date, h.name]));
  const amt = l => Math.round(l.hours * l.rate * 100) / 100;
  $('s-lines').style.display = s.lines.length ? '' : 'none';
  $('s-empty').style.display = s.lines.length ? 'none' : '';
  $('s-lines').querySelector('tbody').innerHTML = s.lines.map(l => {
    const [y, m, d] = l.date.split('-').map(Number);
    const wd = new Date(y, m-1, d).toLocaleDateString('es-ES', {weekday: 'long', day: 'numeric'});
    return `<tr><td>${wd}${hol[l.date] ? ' <em title="Festivo">(festivo)</em>' : ''}</td><td class="r">${l.hours.toLocaleString('es-ES')}</td>
      <td class="r">${fmt(l.rate)}</td><td class="r">${fmt(amt(l))}</td><td>${l.paid ? '✔ Pagado' : 'Pendiente'}</td></tr>`;
  }).join('');
  const hours = s.lines.reduce((a, l) => a + l.hours, 0);
  const total = closed ? s.total : s.lines.reduce((a, l) => a + amt(l), 0);
  const paid = s.paid_real;
  $('s-lines').querySelector('tfoot').innerHTML = `<tr><td>${s.lines.length} día${s.lines.length === 1 ? '' : 's'}</td><td class="r">${hours.toLocaleString('es-ES')}</td><td></td><td class="r">${fmt(total)}</td><td></td></tr>`;
  const carry = s.carry_in;
  $('s-boxes').innerHTML = `<div><span>Arrastre anterior</span><b>${fmt(carry)}</b></div><div><span>Total del mes</span><b>${fmt(total)}</b></div>
    <div><span>Pagado realmente</span><b>${fmt(paid)}</b></div>
    <div><span>Pendiente (saldo)</span><b class="acc">${fmt(carry + total - paid)}</b></div>`;
  $('s-payments').innerHTML = s.payments.length ? '<h3>Pagos del mes</h3>' + s.payments.map(p =>
    `<div class="prow"><span>${p.date.split('-').reverse().join('/')}</span><span>${p.days} día${p.days === 1 ? '' : 's'}${p.note ? ' · ' + p.note.replace(/</g, '&lt;') : ''}</span><b>${fmt(p.amount)}</b></div>`).join('') : '';
  $('s-notes').textContent = closed && s.closed_at ? `Pedido cerrado el ${new Date(s.closed_at.replace(' ', 'T')).toLocaleDateString('es-ES')}.` : '';
})();
$('s-print').onclick = () => print();
