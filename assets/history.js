const MONTHS = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const fmt = n => n.toLocaleString('es-ES', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
const $ = id => document.getElementById(id);
let year = new Date().getFullYear();

async function load() {
  const d = await (await fetch('api.php?action=history&year=' + year + '&worker=' + HCC.worker.id)).json();
  $('year-title').textContent = `${year} · ${HCC.worker.name}`;
  const ms = Object.entries(d.months).map(([m, v]) => ({m: +m, ...v}));
  const sum = k => ms.reduce((a, x) => a + x[k], 0);
  const total = sum('total'), paid = sum('paid');
  let run = d.carry_in;                       // saldo acumulado, empezando con lo que se arrastra de años anteriores
  ms.forEach(x => { if (x.status) run = Math.round((run + x.total - x.paid) * 100) / 100; x.balance = x.status ? run : null; });
  $('y-total').textContent = fmt(total);
  $('y-paid').textContent = fmt(paid);
  $('y-pending').textContent = fmt(run);
  $('y-days').textContent = `${sum('days')} · ${sum('hours').toLocaleString('es-ES')} h`;
  const max = Math.max(...ms.map(x => x.total), 1);
  $('bars').innerHTML = ms.map(x => `<a href="summary.php?year=${year}&month=${x.m}" title="${MONTHS[x.m-1]}: ${fmt(x.total)}">
      <div class="bar"><i style="height:${x.total / max * 100}%"></i></div><small>${MONTHS[x.m-1].slice(0,3)}</small></a>`).join('');
  $('months').querySelector('tbody').innerHTML = ms.map(x => `<tr class="${x.status ? '' : 'none'}">
      <td><a href="summary.php?year=${year}&month=${x.m}">${MONTHS[x.m-1]}</a></td>
      <td class="r">${x.days}</td><td class="r">${x.hours.toLocaleString('es-ES')}</td>
      <td class="r">${x.status ? fmt(x.total) : '—'}</td><td class="r">${x.status ? fmt(x.paid) : '—'}</td>
      <td class="r">${x.status ? fmt(x.total - x.paid) : '—'}</td>
      <td class="r"><b>${x.status ? fmt(x.balance) : '—'}</b></td>
      <td>${x.status ? `<span class="badge ${x.status}">${x.status === 'closed' ? 'Cerrado' : 'Abierto'}</span>` : ''}</td></tr>`).join('');
  const pt = d.people.reduce((a, p) => ({t: a.t + p.total, p: a.p + p.paid}), {t: 0, p: 0});
  $('people').querySelector('tbody').innerHTML = d.people.map(p => `<tr><td><i class="dot" style="background:${p.color}"></i> ${p.name.replace(/</g, '&lt;')}</td>
      <td class="r">${fmt(p.total)}</td><td class="r">${fmt(p.paid)}</td><td class="r">${fmt(p.total - p.paid)}</td></tr>`).join('');
  $('people').querySelector('tfoot').innerHTML = `<tr><td>Todas las personas</td><td class="r">${fmt(pt.t)}</td><td class="r">${fmt(pt.p)}</td><td class="r">${fmt(pt.t - pt.p)}</td></tr>`;
  $('months').querySelector('tfoot').innerHTML = `<tr><td>Total</td><td class="r">${sum('days')}</td><td class="r">${sum('hours').toLocaleString('es-ES')}</td>
      <td class="r">${fmt(total)}</td><td class="r">${fmt(paid)}</td><td class="r">${fmt(total - paid)}</td><td class="r">${fmt(run)}</td><td></td></tr>`;
}
$('prev').onclick = () => { year--; load(); };
$('next').onclick = () => { year++; load(); };
load();
