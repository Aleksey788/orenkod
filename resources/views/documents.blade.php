<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Документы и отчёты — ФСП</title>
<!-- Библиотеки для экспорта -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html-docx-js/0.3.1/html-docx.min.js"></script>

<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --radius:14px; --shadow:0 1px 3px rgba(15,23,42,.08);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1200px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  
  h1{font-size:22px;margin:0;font-weight:600}
  .muted{color:var(--muted);font-size:13px}
  
  /* Сетка карточек */
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
  .card{background:#fff;border-radius:var(--radius);padding:24px;box-shadow:var(--shadow);display:flex;flex-direction:column}
  .card h2{font-size:16px;margin:0 0 8px;font-weight:600}
  .card p{color:var(--muted);font-size:13px;margin:0 0 16px;flex:1}
  
  /* Формы */
  .form-group{margin-bottom:12px}
  .form-group label{display:block;font-size:12px;font-weight:600;margin-bottom:4px;color:var(--muted)}
  .form-group select{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px;outline:none}
  
  /* Кнопки */
  .btn-group{display:flex;gap:8px;margin-top:auto;flex-wrap:wrap}
  .btn{padding:8px 14px;border-radius:8px;border:none;font:inherit;font-size:13px;font-weight:500;cursor:pointer;display:flex;align-items:center;gap:6px;transition:.15s}
  .btn-outline{background:#fff;border:1px solid var(--border);color:var(--text)}
  .btn-outline:hover{background:#f8fafc;border-color:var(--primary);color:var(--primary)}
  .btn-primary{background:var(--primary);color:#fff}
  .btn-primary:hover{background:#1d4ed8}
  
  /* Модальное окно предпросмотра */
  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.6);display:none;align-items:center;justify-content:center;z-index:1000;padding:20px;overflow-y:auto}
  .modal-overlay.show{display:flex}
  .modal-card{background:#f3f5f9;border-radius:12px;width:100%;max-width:900px;display:flex;flex-direction:column;max-height:95vh}
  .modal-head{background:#fff;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border);border-radius:12px 12px 0 0}
  .modal-body{padding:24px;overflow-y:auto;background:#525659;display:flex;justify-content:center}
  .modal-foot{background:#fff;padding:16px 24px;display:flex;justify-content:flex-end;gap:12px;border-top:1px solid var(--border);border-radius:0 0 12px 12px}
  
  /* Лист А4 внутри предпросмотра */
  #previewContent {
    width: 210mm;
    min-height: 297mm;
    padding: 20mm;
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    color: #000;
    line-height: 1.4;
    box-sizing: border-box;
  }
  #previewContent h2 { font-size: 16pt; text-align: center; text-transform: uppercase; margin-bottom: 20px; }
  #previewContent h3 { font-size: 14pt; margin-top: 20px; margin-bottom: 10px; }
  #previewContent table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt; }
  #previewContent th, #previewContent td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
  #previewContent th { text-align: center; background-color: #f2f2f2; font-weight: bold; }
  #previewContent .center { text-align: center; }
  #previewContent .right { text-align: right; }
  #previewContent .signatures { margin-top: 40px; display: flex; justify-content: space-between; }
  
  @media(max-width:768px){
    .grid{grid-template-columns:1fr}
    .modal-card{max-width:100%}
    #previewContent { width: 100%; padding: 10mm; min-height: auto; }
  }
</style>
</head>
<body>

<div class="wrap">
  <div>
    <h1>Документы, отчёты и справки</h1>
    <div class="muted">Выберите параметры, затем сформируйте документ для предпросмотра и скачивания</div>
  </div>

  <div class="grid">
    <!-- 1. Протокол соревнования -->
    <div class="card">
      <h2>📄 Протокол соревнования</h2>
      <p>Официальный протокол с результатами и местами участников соревнования.</p>
      <div class="form-group">
        <label>Выберите соревнование:</label>
        <select id="sel-protocol"></select>
      </div>
      <div class="btn-group">
        <button class="btn btn-primary" onclick="prepareDoc('protocol')">👁 Сформировать протокол</button>
      </div>
    </div>

    <!-- 2. Сборная и Приказ -->
    <div class="card">
      <h2>🏆 Список сборной и проект приказа</h2>
      <p>Формирование официального приказа о составе сборной команды на основе рейтинга.</p>
      <div class="form-group">
        <label>Дисциплина:</label>
        <select id="sel-team-disc">
          <option value="Алгоритмическое программирование">Алгоритмическое программирование</option>
          <option value="Спортивная робототехника">Спортивная робототехника</option>
        </select>
      </div>
      <div class="btn-group">
        <button class="btn btn-primary" onclick="prepareDoc('team')">👁 Сформировать приказ</button>
      </div>
    </div>

    <!-- 3. Отчёт по спортсмену -->
    <div class="card">
      <h2>🏃‍♂️ Отчёт о выступлениях спортсмена</h2>
      <p>Справка о всех соревнованиях, в которых участвовал выбранный спортсмен, с результатами.</p>
      <div class="form-group">
        <label>Выберите спортсмена:</label>
        <select id="sel-athlete"></select>
      </div>
      <div class="btn-group">
        <button class="btn btn-primary" onclick="prepareDoc('athlete')">👁 Сформировать отчет</button>
      </div>
    </div>

    <!-- 4. Отчёт о численности -->
    <div class="card">
      <h2>📊 Отчёт о численности занимающихся</h2>
      <p>Сводный статистический отчёт с разрезами по годам, организациям и дисциплинам.</p>
      <div class="form-group">
        <label>Отчётный год:</label>
        <select id="sel-registry-year">
          <option value="2026">2026 год</option>
          <option value="2025">2025 год</option>
          <option value="2024">2024 год</option>
        </select>
      </div>
      <div class="btn-group">
        <button class="btn btn-outline" style="width:100%;justify-content:center" onclick="exportRegistryXLSX()">📗 Скачать Excel (много листов)</button>
      </div>
    </div>
  </div>
</div>

<!-- Модальное окно предпросмотра -->
<div class="modal-overlay" id="previewModal">
  <div class="modal-card">
    <div class="modal-head">
      <h2 style="margin:0;font-size:18px" id="previewTitle">Предпросмотр документа</h2>
      <button style="background:none;border:none;font-size:24px;cursor:pointer" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div id="previewContent"></div>
    </div>
    <div class="modal-foot">
      <button class="btn btn-outline" onclick="window.print()">🖨 Печать</button>
      <button class="btn btn-outline" onclick="downloadPDF()">📕 Скачать PDF</button>
      <button class="btn btn-primary" onclick="downloadDOCX()">📘 Скачать Word</button>
    </div>
  </div>
</div>

<script>
/* ================= БАЗА ДАННЫХ ================= */
const DB_VERSION = '11.0'; 
const DB_KEY = 'fsp_database';

const INITIAL_DB = {
  athletes: [
    { id:1, name:'Иванов Иван', birthYear:2008, ageGroup:'15-16 лет', rank:'1 разряд', disc:'Алгоритмическое программирование', org:'Губернаторский лицей', muni:'г. Оренбург', startYear:2018, isDisq:false },
    { id:2, name:'Петрова Анна', birthYear:2009, ageGroup:'15-16 лет', rank:'КМС', disc:'Алгоритмическое программирование', org:'Лицей №1', muni:'г. Оренбург', startYear:2019, isDisq:false },
    { id:3, name:'Сидоров Олег', birthYear:2008, ageGroup:'17-18 лет', rank:'1 разряд', disc:'Алгоритмическое программирование', org:'Гимназия №1', muni:'г. Орск', startYear:2020, isDisq:false },
    { id:4, name:'Кузнецова Мария', birthYear:2010, ageGroup:'15-16 лет', rank:'КМС', disc:'Искусственный интеллект', org:'IT-куб', muni:'г. Бузулук', startYear:2017, isDisq:false },
    { id:5, name:'Морозов Дмитрий', birthYear:2011, ageGroup:'13-14 лет', rank:'1 разряд', disc:'Спортивная робототехника', org:'Кванториум', muni:'г. Новотроицк', startYear:2022, isDisq:false },
    { id:6, name:'Волков Артём', birthYear:2007, ageGroup:'17-18 лет', rank:'МС', disc:'Алгоритмическое программирование', org:'Губернаторский лицей', muni:'г. Оренбург', startYear:2015, isDisq:true }
  ],
  competitions: [
    { id:1, name:'Чемпионат России 2026', level:'Чемпионат России', season:'2026', startDate:'2026-04-10', endDate:'2026-04-12' },
    { id:2, name:'Первенство России 2026', level:'Первенство России', season:'2026', startDate:'2026-05-15', endDate:'2026-05-17' },
    { id:3, name:'Кубок ФСП 2026', level:'Межрегиональные', season:'2026', startDate:'2026-06-20', endDate:'2026-06-21' },
    { id:4, name:'Кубок Оренбургской области 2026', level:'Региональные', season:'2026', startDate:'2026-09-01', endDate:'2026-09-02' },
    { id:5, name:'Открытый чемпионат Оренбурга 2026', level:'Региональные', season:'2026', startDate:'2026-10-05', endDate:'2026-10-05' }
  ],
  results: [
    { athleteId:1, competitionId:1, place:1, score:100 },
    { athleteId:1, competitionId:4, place:2, score:80 },
    { athleteId:1, competitionId:5, place:4, score:50 },
    { athleteId:2, competitionId:2, place:2, score:90 },
    { athleteId:2, competitionId:3, place:3, score:70 },
    { athleteId:3, competitionId:4, place:1, score:100 },
    { athleteId:4, competitionId:3, place:1, score:100 },
    { athleteId:5, competitionId:5, place:1, score:95 }
  ]
};

function getDB() {
  let v = localStorage.getItem('fsp_db_version');
  let db = localStorage.getItem(DB_KEY);
  if (!db || v !== DB_VERSION) {
    localStorage.setItem('fsp_db_version', DB_VERSION);
    localStorage.setItem(DB_KEY, JSON.stringify(INITIAL_DB));
    return INITIAL_DB;
  }
  return JSON.parse(db);
}

/* ================= УТИЛИТЫ ================= */
const fmtDate = s => { if(!s) return '—'; const [y,m,d] = s.split('-'); return `${d}.${m}.${y}`; };
const POINTS_TABLE = { 'Чемпионат России': [100, 80, 60], 'Первенство России': [80, 60, 40], 'Межрегиональные': [60, 45, 30], 'Региональные': [40, 30, 20] };

/* ================= ИНИЦИАЛИЗАЦИЯ ================= */
function initSelects() {
  const db = getDB();
  const selComp = document.getElementById('sel-protocol');
  selComp.innerHTML = '';
  db.competitions.forEach(c => selComp.innerHTML += `<option value="${c.id}">${c.name}</option>`);
  
  const selAth = document.getElementById('sel-athlete');
  selAth.innerHTML = '';
  db.athletes.forEach(a => selAth.innerHTML += `<option value="${a.id}">${a.name} (${a.org})</option>`);
}

/* ================= ПОДГОТОВКА И ПРЕДПРОСМОТР ================= */
let currentDocTitle = "Документ";

function prepareDoc(type) {
  const db = getDB();
  const preview = document.getElementById('previewContent');
  
  if (type === 'protocol') {
    const compId = +document.getElementById('sel-protocol').value;
    const comp = db.competitions.find(c => c.id === compId);
    currentDocTitle = `Протокол_${comp.name.replace(/\s+/g, '_')}`;
    
    let results = db.results.filter(r => r.competitionId === compId).map(r => {
      const ath = db.athletes.find(a => a.id === r.athleteId) || { name: 'Неизвестен', org: '-' };
      return { place: r.place, name: ath.name, org: ath.org, score: r.score };
    }).sort((a,b) => a.place - b.place);

    preview.innerHTML = `
      <h2>ПРОТОКОЛ<br>${comp.name}</h2>
      <p style="text-align:center">Даты проведения: ${fmtDate(comp.startDate)} — ${fmtDate(comp.endDate)}<br>Уровень: ${comp.level}</p>
      <table>
        <tr><th>Место</th><th>ФИО</th><th>Организация</th><th>Баллы</th></tr>
        ${results.length ? results.map(r => `<tr><td class="center">${r.place}</td><td>${r.name}</td><td>${r.org}</td><td class="center">${r.score}</td></tr>`).join('') : '<tr><td colspan="4" class="center">Нет данных</td></tr>'}
      </table>
      <div class="signatures">
        <div>Главный судья: _______________</div>
        <div>Главный секретарь: _______________</div>
      </div>
    `;
  } 
  
  else if (type === 'team') {
    const disc = document.getElementById('sel-team-disc').value;
    currentDocTitle = `Приказ_Сборная_${disc.replace(/\s+/g, '_')}`;
    
    let filtered = db.athletes.filter(a => a.isDisq !== true && a.disc === disc);
    let rating = filtered.map(ath => {
      let base = 0, prizes = 0;
      db.results.filter(r => r.athleteId === ath.id).forEach(r => {
        const c = db.competitions.find(x => x.id === r.competitionId);
        if (r.place <= 3) { base += (POINTS_TABLE[c.level] || [0,0,0])[r.place - 1]; prizes++; }
      });
      return { ...ath, final: prizes >= 2 ? Math.round(base * 1.2) : base };
    }).sort((a,b) => b.final - a.final);

    preview.innerHTML = `
      <h2>ПРОЕКТ ПРИКАЗА<br>О формировании сборной команды</h2>
      <p>Дисциплина: «${disc}»</p>
      <p>1. Утвердить состав сборной команды Оренбургской области в следующем составе:</p>
      <table>
        <tr><th>№</th><th>ФИО спортсмена</th><th>Организация</th><th>Разряд</th><th>Рейтинг</th></tr>
        ${rating.length ? rating.map((a, i) => `<tr><td class="center">${i+1}</td><td>${a.name}</td><td>${a.org}</td><td class="center">${a.rank}</td><td class="center">${a.final}</td></tr>`).join('') : '<tr><td colspan="5" class="center">Нет данных</td></tr>'}
      </table>
      <p>2. Контроль за исполнением настоящего приказа возложить на главного тренера.</p>
      <div class="signatures">
        <div style="width:100%;text-align:right">Президент Федерации _______________ / _______________ /</div>
      </div>
    `;
  }
  
  else if (type === 'athlete') {
    const athId = +document.getElementById('sel-athlete').value;
    const ath = db.athletes.find(a => a.id === athId);
    currentDocTitle = `Отчет_${ath.name.replace(/\s+/g, '_')}`;
    
    const myRes = db.results.filter(r => r.athleteId === athId).map(r => {
      const c = db.competitions.find(x => x.id === r.competitionId) || { name: 'Неизвестное соревнование', startDate: '2026-01-01' };
      return { ...r, compName: c.name, date: c.startDate };
    }).sort((a,b) => b.date.localeCompare(a.date));

    preview.innerHTML = `
      <h2>СПРАВКА О ВЫСТУПЛЕНИЯХ</h2>
      <p><b>Спортсмен:</b> ${ath.name}<br>
         <b>Организация:</b> ${ath.org}<br>
         <b>Разряд:</b> ${ath.rank} | <b>Дисциплина:</b> ${ath.disc}</p>
      <table>
        <tr><th>Дата</th><th>Соревнование</th><th>Место</th><th>Баллы</th></tr>
        ${myRes.length ? myRes.map(r => `<tr><td>${fmtDate(r.date)}</td><td>${r.compName}</td><td class="center">${r.place}</td><td class="center">${r.score}</td></tr>`).join('') : '<tr><td colspan="4" class="center">Нет результатов</td></tr>'}
      </table>
      <div class="signatures">
        <div style="width:100%;text-align:right">Секретарь федерации _______________</div>
      </div>
    `;
  }

  document.getElementById('previewTitle').textContent = currentDocTitle.replace(/_/g, ' ');
  document.getElementById('previewModal').classList.add('show');
}

function closeModal() {
  document.getElementById('previewModal').classList.remove('show');
}

/* ================= ЭКСПОРТ ИЗ ПРЕДПРОСМОТРА ================= */
function downloadPDF() {
  const element = document.getElementById('previewContent');
  const opt = {
    margin: 0,
    filename: `${currentDocTitle}.pdf`,
    image: { type: 'jpeg', quality: 0.98 },
    html2canvas: { scale: 2, useCORS: true, scrollY: 0 },
    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
  };
  html2pdf().set(opt).from(element).save();
}

function downloadDOCX() {
  const content = document.getElementById('previewContent').innerHTML;
  // Важно: передаем полный HTML документ с корректной кодировкой
  const htmlString = `
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; }
        h2 { text-align: center; font-size: 16pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        .center { text-align: center; }
      </style>
    </head>
    <body>${content}</body>
    </html>
  `;

  // Используем правильный MIME-тип и BOM для корректного отображения кириллицы
  const blob = new Blob(['\ufeff', htmlString], { type: 'application/msword' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `${currentDocTitle}.doc`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

/* ================= ЭКСПОРТ EXCEL (Численность) ================= */
function exportRegistryXLSX() {
  const db = getDB();
  const year = +document.getElementById('sel-registry-year').value;
  const data = db.athletes.filter(a => a.startYear <= year);
  
  const wb = XLSX.utils.book_new();
  const rows = [["ФИО", "Год начала", "Организация", "Дисциплина", "Возраст", "Муниципалитет"]].concat(
    data.map(a => [a.name, a.startYear, a.org, a.disc, a.ageGroup, a.muni])
  );
  
  XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet(rows), "Реестр");
  XLSX.writeFile(wb, `Численность_${year}.xlsx`);
}

/* ================= СТАРТ ================= */
initSelects();
</script>
</body>
</html>