const MONTHS = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fmt = n => n.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
const $ = id => document.getElementById(id);
let year = new Date().getFullYear();

async function load() {
  const d = await (await fetch('api.php?action=history&year=' + year)).json();
  $('year-title').textContent = year;
  const ms = Object.entries(d.months).map(([m, v]) => ({m: +m, ...v}));
  const sum = k => ms.reduce((a, x) => a + x[k], 0);
  const total = sum('total'), paid = sum('paid');
  $('y-total').textContent = fmt(total);
  $('y-paid').textContent = fmt(paid);
  $('y-pending').textContent = fmt(total - paid);
  $('y-days').textContent = `${sum('days')} · ${sum('hours').toLocaleString('es-ES')} h`;
  const max = Math.max(...ms.map(x => x.total), 1);
  $('bars').innerHTML = ms.map(x => `<a href="summary.php?year=${year}&month=${x.m}" title="${MONTHS[x.m-1]}: ${fmt(x.total)}">
      <div class="bar"><i style="height:${x.total / max * 100}%"></i></div><small>${MONTHS[x.m-1].slice(0,3)}</small></a>`).join('');
  $('months').querySelector('tbody').innerHTML = ms.map(x => `<tr class="${x.status ? '' : 'none'}">
      <td><a href="summary.php?year=${year}&month=${x.m}">${MONTHS[x.m-1]}</a></td>
      <td class="r">${x.days}</td><td class="r">${x.hours.toLocaleString('es-ES')}</td>
      <td class="r">${x.status ? fmt(x.total) : '—'}</td><td class="r">${x.status ? fmt(x.paid) : '—'}</td>
      <td class="r">${x.status ? fmt(x.total - x.paid) : '—'}</td>
      <td>${x.status ? `<span class="badge ${x.status}">${x.status === 'closed' ? 'Cerrado' : 'Abierto'}</span>` : ''}</td></tr>`).join('');
  $('months').querySelector('tfoot').innerHTML = `<tr><td>Total</td><td class="r">${sum('days')}</td><td class="r">${sum('hours').toLocaleString('es-ES')}</td>
      <td class="r">${fmt(total)}</td><td class="r">${fmt(paid)}</td><td class="r">${fmt(total - paid)}</td><td></td></tr>`;
}
$('prev').onclick = () => { year--; load(); };
$('next').onclick = () => { year++; load(); };
load();
