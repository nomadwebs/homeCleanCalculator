document.getElementById('settings-form').addEventListener('submit', async e => {
  e.preventDefault();
  const f = Object.fromEntries(new FormData(e.target));
  const r = await fetch('api.php', {method: 'POST', body: JSON.stringify({action: 'save_settings', ...f})});
  const d = await r.json();
  toast(r.ok ? 'Guardado ✓' : (d.error || 'Error'), !r.ok);
  if (r.ok) loadHolidays();
});

// --- Festivos ---
const yearSel = document.getElementById('hol-year'), list = document.getElementById('hol-list');
const thisYear = new Date().getFullYear();
for (let y = thisYear - 1; y <= thisYear + 2; y++) yearSel.add(new Option(y, y, false, y === thisYear));
const post = body => fetch('api.php', {method: 'POST', body: JSON.stringify(body)}).then(async r => ({ok: r.ok, d: await r.json()}));

function showHolidays(rows) {
  list.innerHTML = rows.length ? '' : '<li class="hint">Sin festivos para este año.</li>';
  for (const h of rows) {
    const [y, m, d] = h.date.split('-').map(Number);
    const wd = new Date(y, m - 1, d).toLocaleDateString('es-ES', {weekday: 'short', day: 'numeric', month: 'short'});
    const li = document.createElement('li');
    li.innerHTML = `<span>${wd}</span><span class="nm"></span><em>${h.source === 'manual' ? 'manual' : ''}</em><button class="x" title="Eliminar">✕</button>`;
    li.querySelector('.nm').textContent = h.name;
    li.querySelector('.x').onclick = async () => { await post({action: 'delete_holiday', id: h.id}); loadHolidays(); };
    list.appendChild(li);
  }
}
async function loadHolidays() {
  const d = await (await fetch('api.php?action=holidays&year=' + yearSel.value)).json();
  showHolidays(d.holidays);
}
yearSel.onchange = loadHolidays;
document.getElementById('hol-import').onclick = async () => {
  const {ok, d} = await post({action: 'import_holidays', year: +yearSel.value});
  toast(ok ? `Descargados ${d.imported} festivos ✓` : d.error, !ok);
  if (ok) showHolidays(d.holidays);
};
document.getElementById('hol-form').onsubmit = async e => {
  e.preventDefault();
  const f = Object.fromEntries(new FormData(e.target));
  const {ok, d} = await post({action: 'add_holiday', ...f});
  toast(ok ? 'Festivo añadido ✓' : d.error, !ok);
  if (ok) { e.target.reset(); yearSel.value = f.date.slice(0, 4); loadHolidays(); }
};
function toast(msg, err) {
  const t = document.getElementById('toast'); t.textContent = msg; t.className = 'show' + (err ? ' err' : '');
  setTimeout(() => t.className = '', 2500);
}
loadHolidays();

// --- Borrar todos los datos (doble confirmación) ---
const rm = document.getElementById('reset-modal'), rf = document.getElementById('reset-form'), go = document.getElementById('reset-go');
const norm = t => t.trim().toLowerCase();
const step1 = document.getElementById('reset-step1');
document.getElementById('reset-btn').onclick = () => { rf.reset(); go.disabled = true; step1.hidden = false; rf.hidden = true; rm.hidden = false; };
document.getElementById('reset-next').onclick = () => { step1.hidden = true; rf.hidden = false; rf.phrase.focus(); };
document.querySelectorAll('.reset-cancel').forEach(b => b.onclick = () => rm.hidden = true);
rf.phrase.oninput = () => go.disabled = !['sí borrar', 'si borrar'].includes(norm(rf.phrase.value));
rf.onsubmit = async e => {
  e.preventDefault();
  const {ok, d} = await post({action: 'reset_data', confirm: rf.phrase.value, everything: rf.everything.checked});
  rm.hidden = true;
  toast(ok ? 'Datos borrados ✓' : d.error, !ok);
  if (ok) setTimeout(() => location.reload(), 900);
};
