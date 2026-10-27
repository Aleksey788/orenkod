<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Реестр и динамика численности — спортивное программирование</title>
<!-- Подключение библиотеки для работы с XLSX -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --primary-soft:#dbeafe; --success:#16a34a; --success-soft:#dcfce7;
    --warning:#d97706; --warning-soft:#fef3c7; --danger:#dc2626;  --danger-soft:#fee2e2;
    --radius:14px; --shadow:0 1px 2px rgba(15,23,42,.06),0 4px 12px rgba(15,23,42,.04);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1360px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  h1{font-size:22px;margin:0;font-weight:600} h2{font-size:16px;margin:0 0 14px;font-weight:600} .muted{color:var(--muted);font-size:13px}
  
  .topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
  .btn-primary{padding:10px 18px;background:var(--primary);color:#fff;border:none;border-radius:10px;font:inherit;font-size:14px;font-weight:500;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:8px}
  .btn-primary:hover{background:#1d4ed8}
  
  /* Панель фильтров */
  .filters-panel{background:var(--card);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow);display:flex;gap:12px;flex-wrap:wrap;align-items:center}
  .filters-panel select{padding:9px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;background:#fff;color:var(--text);outline:none;min-width:150px}
  .filters-panel select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(37,99,235,.12)}
  
  /* KPI */
  .kpi-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  .kpi{background:#fff;border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);position:relative;overflow:hidden;border:1px solid var(--border)}
  .kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--primary)}
  .kpi.c2::before{background:var(--success)} .kpi.c3::before{background:var(--warning)}
  .kpi .label{color:var(--muted);font-size:13px;margin-bottom:6px;font-weight:500}
  .kpi .value{font-size:32px;font-weight:700;line-height:1.1}
  .kpi .sub{color:var(--muted);font-size:12px;margin-top:6px;display:flex;align-items:center;gap:4px}
  .trend-up{color:var(--success);font-weight:600} .trend-down{color:var(--danger);font-weight:600}
  
  /* Чарт */
  .chart-container{display:flex;align-items:flex-end;gap:20px;height:200px;padding:20px 0 10px 0;border-bottom:1px solid var(--border)}
  .bar-wrap{flex:1;display:flex;flex-direction:column;align-items:center;gap:8px;justify-content:flex-end;height:100%}
  .bar{width:60%;background:linear-gradient(180deg, #60a5fa, #2563eb);border-radius:6px 6px 0 0;transition:height .4s;position:relative;min-height:4px}
  .bar:hover{opacity:0.85}
  .bar-value{font-weight:600;font-size:13px;color:var(--text)}
  .bar-label{font-size:12px;color:var(--muted);font-weight:500}
  
  /* Таблицы разрезов */
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow)}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border)}
  th{font-weight:600;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.3px;background:#f8fafc;white-space:nowrap}
  tbody tr:last-child td{border-bottom:none}
  tbody tr:hover{background:#f8fafc}
  .right{text-align:right}
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;background:#f1f5f9;color:#475569}
  .badge.blue{background:#dbeafe;color:#1d4ed8}
  
  @media(max-width:900px){
    .kpi-grid{grid-template-columns:1fr}
    .grid-2{grid-template-columns:1fr}
  }
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div>
      <h1>Численность занимающихся спортивным программированием</h1>
      <div class="muted">Оренбургская область · Динамика и разрезы</div>
    </div>
    <button class="btn-primary" onclick="exportXLSX()">
      <span>📥</span> Выгрузить отчёт (.xlsx)
    </button>
  </div>

  <!-- Фильтры -->
  <div class="filters-panel">
    <div style="font-weight:600;margin-right:auto">Фильтры:</div>
    <select id="filter-year" onchange="updateFilters()">
      <option value="2026">2026 год</option>
      <option value="2025">2025 год</option>
      <option value="2024">2024 год</option>
    </select>
    <select id="filter-muni" onchange="updateFilters()">
      <option value="">Все муниципалитеты</option>
    </select>
    <select id="filter-disc" onchange="updateFilters()">
      <option value="">Все дисциплины</option>
    </select>
    <select id="filter-org" onchange="updateFilters()">
      <option value="">Все организации</option>
    </select>
  </div>

  <!-- KPI -->
  <div class="kpi-grid" id="kpiContainer"></div>

  <!-- Динамика по годам -->
  <div class="panel">
    <h2>Динамика численности по годам</h2>
    <div class="chart-container" id="chartContainer"></div>
  </div>

  <!-- Разрезы -->
  <div class="grid-2">
    <div class="panel">
      <h2>По организациям</h2>
      <table><thead><tr><th>Организация</th><th class="right">Кол-во</th></tr></thead><tbody id="tableOrgs"></tbody></table>
    </div>
    <div class="panel">
      <h2>По дисциплинам</h2>
      <table><thead><tr><th>Дисциплина</th><th class="right">Кол-во</th></tr></thead><tbody id="tableDiscs"></tbody></table>
    </div>
    <div class="panel">
      <h2>По возрастным группам</h2>
      <table><thead><tr><th>Возраст</th><th class="right">Кол-во</th></tr></thead><tbody id="tableAges"></tbody></table>
    </div>
    <div class="panel">
      <h2>По муниципалитетам</h2>
      <table><thead><tr><th>Муниципалитет</th><th class="right">Кол-во</th></tr></thead><tbody id="tableMunis"></tbody></table>
    </div>
  </div>

  <!-- Полный реестр -->
  <div class="panel">
    <h2>Реестр занимающихся <span class="muted" id="registryCount" style="font-size:13px;font-weight:400"></span></h2>
    <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>ФИО</th>
            <th>Год начала</th>
            <th>Организация</th>
            <th>Дисциплина</th>
            <th>Возрастная группа</th>
            <th>Муниципалитет</th>
            <th>Этап подготовки</th>
          </tr>
        </thead>
        <tbody id="registryTable"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
/* ================= ГЕНЕРАЦИЯ ДАННЫХ ================= */
const ORGS = ['Губернаторский лицей', 'Лицей №1', 'Гимназия №1', 'IT-куб', 'Кванториум'];
const MUNIS = ['г. Оренбург', 'г. Орск', 'г. Бузулук', 'г. Новотроицк'];
const DISCS = ['Алгоритмическое программирование', 'Спортивная робототехника', 'Информационная безопасность', 'Искусственный интеллект'];
const AGES = ['6-8 лет', '9-10 лет', '11-12 лет', '13-14 лет', '15-16 лет', '17-18 лет', '19+'];
const STAGES = ['ЭНП', 'ТЭ', 'ЭССМ', 'ВСМ', 'Нет этапа'];

const NAMES = ['Иванов И.И.', 'Петрова А.С.', 'Сидоров О.П.', 'Кузнецова М.И.', 'Морозов Д.А.', 'Волков А.С.', 'Смирнова Е.А.', 'Орлов П.С.', 'Белов Н.И.', 'Краснова Д.О.', 'Никитин М.Д.', 'Соколова В.А.'];
const SUFFIX = ['', ' (2)', ' (3)', ' (4)', ' (5)', ' (6)', ' (7)', ' (8)'];

let ATHLETES = [];

// Генерируем ~250 записей с распределением по годам
for (let i = 0; i < 250; i++) {
  const startYear = 2022 + Math.floor(Math.random() * 5); // 2022-2026
  const ageGroup = AGES[Math.floor(Math.random() * AGES.length)];
  const stage = STAGES[Math.floor(Math.random() * STAGES.length)];
  
  ATHLETES.push({
    id: i + 1,
    name: NAMES[Math.floor(Math.random() * NAMES.length)] + SUFFIX[Math.floor(Math.random() * SUFFIX.length)],
    startYear: startYear,
    org: ORGS[Math.floor(Math.random() * ORGS.length)],
    muni: MUNIS[Math.floor(Math.random() * MUNIS.length)],
    disc: DISCS[Math.floor(Math.random() * DISCS.length)],
    age: ageGroup,
    stage: stage
  });
}

/* ================= СОСТОЯНИЕ ================= */
const STATE = {
  year: 2026,
  muni: '',
  disc: '',
  org: ''
};

/* ================= ЛОГИКА ================= */
function initFilters() {
  const muniSel = document.getElementById('filter-muni');
  MUNIS.forEach(m => muniSel.innerHTML += `<option value="${m}">${m}</option>`);
  
  const discSel = document.getElementById('filter-disc');
  DISCS.forEach(d => discSel.innerHTML += `<option value="${d}">${d}</option>`);
  
  const orgSel = document.getElementById('filter-org');
  ORGS.forEach(o => orgSel.innerHTML += `<option value="${o}">${o}</option>`);
}

function updateFilters() {
  STATE.year = +document.getElementById('filter-year').value;
  STATE.muni = document.getElementById('filter-muni').value;
  STATE.disc = document.getElementById('filter-disc').value;
  STATE.org = document.getElementById('filter-org').value;
  render();
}

// Получить отфильтрованный список на выбранный год
function getFilteredData(year) {
  return ATHLETES.filter(a => {
    // Занимается в этом году, если начал в этом или раньше
    if (a.startYear > year) return false;
    if (STATE.muni && a.muni !== STATE.muni) return false;
    if (STATE.disc && a.disc !== STATE.disc) return false;
    if (STATE.org && a.org !== STATE.org) return false;
    return true;
  });
}

function groupBy(arr, key) {
  return arr.reduce((acc, obj) => {
    const val = obj[key];
    acc[val] = (acc[val] || 0) + 1;
    return acc;
  }, {});
}

/* ================= РЕНДЕР ================= */
function render() {
  const dataCurrent = getFilteredData(STATE.year);
  const dataPrev = getFilteredData(STATE.year - 1);
  
  // KPI
  const diff = dataCurrent.length - dataPrev.length;
  const diffPct = dataPrev.length ? Math.round((diff / dataPrev.length) * 100) : 0;
  const trendClass = diff >= 0 ? 'trend-up' : 'trend-down';
  const trendIcon = diff >= 0 ? '▲' : '▼';
  
  document.getElementById('kpiContainer').innerHTML = `
    <div class="kpi">
      <div class="label">Всего занимающихся в ${STATE.year} г.</div>
      <div class="value">${dataCurrent.length}</div>
      <div class="sub">в выбранных фильтрах</div>
    </div>
    <div class="kpi c2">
      <div class="label">Динамика к ${STATE.year - 1} г.</div>
      <div class="value ${trendClass}">${diff > 0 ? '+' : ''}${diff}</div>
      <div class="sub"><span class="${trendClass}">${trendIcon} ${Math.abs(diffPct)}%</span> к прошлому году</div>
    </div>
    <div class="kpi c3">
      <div class="label">Организаций-участников</div>
      <div class="value">${new Set(dataCurrent.map(a => a.org)).size}</div>
      <div class="sub">в ${STATE.year} году</div>
    </div>
  `;
  
  // Чарт (динамика за 3 года)
  const years = [STATE.year - 2, STATE.year - 1, STATE.year];
  const chartData = years.map(y => ({ year: y, count: getFilteredData(y).length }));
  const maxCount = Math.max(...chartData.map(d => d.count), 1);
  
  document.getElementById('chartContainer').innerHTML = chartData.map(d => `
    <div class="bar-wrap">
      <div class="bar-value">${d.count}</div>
      <div class="bar" style="height: ${(d.count / maxCount) * 100}%"></div>
      <div class="bar-label">${d.year}</div>
    </div>
  `).join('');

  // Таблицы разрезов
  const byOrg = groupBy(dataCurrent, 'org');
  document.getElementById('tableOrgs').innerHTML = Object.entries(byOrg)
    .sort((a, b) => b[1] - a[1])
    .map(([k, v]) => `<tr><td>${k}</td><td class="right"><b>${v}</b></td></tr>`).join('') || '<tr><td colspan="2" class="muted">Нет данных</td></tr>';

  const byDisc = groupBy(dataCurrent, 'disc');
  document.getElementById('tableDiscs').innerHTML = Object.entries(byDisc)
    .sort((a, b) => b[1] - a[1])
    .map(([k, v]) => `<tr><td>${k}</td><td class="right"><b>${v}</b></td></tr>`).join('') || '<tr><td colspan="2" class="muted">Нет данных</td></tr>';

  const byAge = groupBy(dataCurrent, 'age');
  document.getElementById('tableAges').innerHTML = AGES.map(age => {
    const count = byAge[age] || 0;
    return count > 0 ? `<tr><td>${age}</td><td class="right"><b>${count}</b></td></tr>` : '';
  }).join('') || '<tr><td colspan="2" class="muted">Нет данных</td></tr>';

  const byMuni = groupBy(dataCurrent, 'muni');
  document.getElementById('tableMunis').innerHTML = Object.entries(byMuni)
    .sort((a, b) => b[1] - a[1])
    .map(([k, v]) => `<tr><td>${k}</td><td class="right"><b>${v}</b></td></tr>`).join('') || '<tr><td colspan="2" class="muted">Нет данных</td></tr>';

  // Полный реестр
  document.getElementById('registryCount').textContent = `(Найдено: ${dataCurrent.length})`;
  document.getElementById('registryTable').innerHTML = dataCurrent.map(a => `
    <tr>
      <td><b>${a.name}</b></td>
      <td>${a.startYear}</td>
      <td>${a.org}</td>
      <td><span class="badge blue">${a.disc}</span></td>
      <td>${a.age}</td>
      <td>${a.muni}</td>
      <td><span class="badge">${a.stage}</span></td>
    </tr>
  `).join('') || '<tr><td colspan="7" class="muted" style="text-align:center;padding:20px">Нет данных по выбранным фильтрам</td></tr>';
}

/* ================= ЭКСПОРТ В XLSX ================= */
function exportXLSX() {
  const wb = XLSX.utils.book_new();
  const dataCurrent = getFilteredData(STATE.year);
  
  // Лист 1: Сводка
  const summaryData = [
    ["Отчет: Численность занимающихся спортивным программированием"],
    ["Регион:", "Оренбургская область"],
    ["Год:", STATE.year],
    ["Фильтры:", `Муниципалитет: ${STATE.muni || 'Все'}, Дисциплина: ${STATE.disc || 'Все'}, Организация: ${STATE.org || 'Все'}`],
    [],
    ["Показатель", "Значение"],
    ["Всего занимающихся", dataCurrent.length],
    ["Организаций", new Set(dataCurrent.map(a => a.org)).size]
  ];
  const wsSummary = XLSX.utils.aoa_to_sheet(summaryData);
  XLSX.utils.book_append_sheet(wb, wsSummary, "Сводка");

  // Лист 2: По организациям
  const byOrg = groupBy(dataCurrent, 'org');
  const orgRows = [["Организация", "Количество"]].concat(Object.entries(byOrg).sort((a,b)=>b[1]-a[1]));
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(orgRows), "По организациям");

  // Лист 3: По дисциплинам
  const byDisc = groupBy(dataCurrent, 'disc');
  const discRows = [["Дисциплина", "Количество"]].concat(Object.entries(byDisc).sort((a,b)=>b[1]-a[1]));
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(discRows), "По дисциплинам");

  // Лист 4: По возрастам
  const byAge = groupBy(dataCurrent, 'age');
  const ageRows = [["Возрастная группа", "Количество"]].concat(AGES.filter(a => byAge[a]).map(a => [a, byAge[a]]));
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(ageRows), "По возрастам");

  // Лист 5: По муниципалитетам
  const byMuni = groupBy(dataCurrent, 'muni');
  const muniRows = [["Муниципалитет", "Количество"]].concat(Object.entries(byMuni).sort((a,b)=>b[1]-a[1]));
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(muniRows), "По муниципалитетам");

  // Лист 6: Детальный реестр
  const registryRows = [["ФИО", "Год начала", "Организация", "Дисциплина", "Возрастная группа", "Муниципалитет", "Этап подготовки"]];
  dataCurrent.forEach(a => {
    registryRows.push([a.name, a.startYear, a.org, a.disc, a.age, a.muni, a.stage]);
  });
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(registryRows), "Реестр");

  // Сохранение файла
  XLSX.writeFile(wb, `Численность_СП_${STATE.year}.xlsx`);
}

/* ================= СТАРТ ================= */
initFilters();
render();
</script>
</body>
</html>