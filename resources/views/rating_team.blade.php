<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Рейтинг спортсменов и формирование сборной</title>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --primary-soft:#dbeafe; --success:#16a34a; --warning:#d97706; --danger:#dc2626;
    --radius:12px; --shadow:0 1px 3px rgba(15,23,42,.08);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1360px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  
  .topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
  h1{font-size:22px;margin:0;font-weight:600} h2{font-size:16px;margin:0 0 14px;font-weight:600} .muted{color:var(--muted);font-size:13px}
  
  /* Панель фильтров */
  .toolbar{display:flex;gap:12px;flex-wrap:wrap;align-items:center;background:var(--card);padding:16px;border-radius:var(--radius);box-shadow:var(--shadow)}
  .toolbar select, .toolbar input{padding:9px 12px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px;background:#fff;outline:none}
  .toolbar select:focus, .toolbar input:focus{border-color:var(--primary)}
  .btn{padding:9px 16px;border-radius:8px;border:none;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:6px}
  .btn-primary{background:var(--primary);color:#fff} .btn-primary:hover{background:#1d4ed8}
  .btn-secondary{background:#fff;border:1px solid var(--border);color:var(--text)} .btn-secondary:hover{background:#f8fafc}
  .btn-success{background:var(--success);color:#fff} .btn-success:hover{background:#15803d}
  .btn:disabled{opacity:0.5;cursor:not-allowed}
  
  /* Таблица лидеров */
  .panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;overflow:hidden}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border)}
  th{font-weight:600;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.5px;background:#f8fafc}
  tbody tr:hover{background:#f8fafc}
  .rank-col{width:50px;text-align:center;font-weight:700;font-size:16px}
  .score-col{text-align:right;font-weight:700;font-size:16px;color:var(--primary)}
  .checkbox-col{width:40px;text-align:center}
  input[type="checkbox"]{width:18px;height:18px;cursor:pointer;accent-color:var(--primary)}
  
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600}
  .badge.gold{background:#fef3c7;color:#b45309}
  .badge.info{background:#dbeafe;color:#1d4ed8}
  .badge.danger{background:#fee2e2;color:#b91c1c}
  .badge.success{background:#dcfce7;color:#15803d}
  
  .details-btn{background:none;border:none;color:var(--primary);cursor:pointer;font-size:13px;text-decoration:underline}
  
  /* Справочная таблица баллов */
  .points-legend{background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px;font-size:13px;margin-bottom:16px}
  .points-legend table{font-size:12px;margin-top:8px}
  .points-legend th, .points-legend td{border-bottom:1px solid #fde68a;padding:6px 10px}

  /* Модальные окна */
  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;z-index:1000;padding:20px;overflow-y:auto}
  .modal-overlay.show{display:flex}
  .modal-card{background:#fff;border-radius:12px;max-width:800px;width:100%;padding:24px;margin-top:40px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
  .modal-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--border);padding-bottom:12px}
  .modal-head h3{margin:0;font-size:18px}
  .modal-close{background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted)}
  
  @media(max-width:768px){
    .toolbar{flex-direction:column;align-items:stretch}
  }
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div>
      <h1>Рейтинг спортсменов и формирование сборной</h1>
      <div class="muted">Оренбургская область · Спортивное программирование</div>
    </div>
    <div style="display:flex;gap:10px">
      <button class="btn btn-secondary" onclick="exportXLSX('rating')">📥 Скачать рейтинг (.xlsx)</button>
      <button class="btn btn-primary" id="btnCreateTeam" onclick="openTeamModal()">🏆 Сформировать сборную <span id="selectedCount" style="background:rgba(255,255,255,0.3);padding:2px 8px;border-radius:10px;margin-left:6px">0</span></button>
    </div>
  </div>

  <!-- Фильтры -->
  <div class="toolbar">
    <select id="filter-disc" onchange="render()">
      <option value="Алгоритмическое программирование">Алгоритмическое программирование</option>
      <option value="Спортивная робототехника">Спортивная робототехника</option>
      <option value="Информационная безопасность">Информационная безопасность</option>
      <option value="Искусственный интеллект">Искусственный интеллект</option>
    </select>
    <select id="filter-age" onchange="render()">
      <option value="">Все возрастные группы</option>
      <option value="13-14 лет">13-14 лет</option>
      <option value="15-16 лет">15-16 лет</option>
      <option value="17-18 лет">17-18 лет</option>
      <option value="19+">19+</option>
    </select>
    <select id="filter-rank" onchange="render()">
      <option value="">Любой разряд</option>
      <option value="КМС">КМС</option>
      <option value="МС">МС</option>
      <option value="1 разряд">1 разряд</option>
      <option value="2 разряд">2 разряд</option>
      <option value="3 разряд">3 разряд</option>
    </select>
  </div>

  <div class="panel">
    <div class="points-legend">
      <b>⚙️ Настройки начисления баллов (сезон 2026):</b>
      <table>
        <tr><th>Уровень соревнования</th><th>1 место</th><th>2 место</th><th>3 место</th></tr>
        <tr><td>Чемпионат России</td><td>100</td><td>80</td><td>60</td></tr>
        <tr><td>Первенство России</td><td>80</td><td>60</td><td>40</td></tr>
        <tr><td>Межрегиональные</td><td>60</td><td>45</td><td>30</td></tr>
        <tr><td>Региональные</td><td>40</td><td>30</td><td>20</td></tr>
      </table>
      <div style="margin-top:8px;color:#b45309">
        * Коэффициент стабильности ×1.2 применяется, если спортсмен занял призовое место (1–3) минимум на двух соревнованиях сезона.<br>
        * Спортсмены с дисквалификацией в рейтинг не попадают.
      </div>
    </div>

    <table>
      <thead>
        <tr>
          <th class="checkbox-col"></th>
          <th class="rank-col">#</th>
          <th>Спортсмен</th>
          <th>Возраст / Разряд</th>
          <th>Дисциплина</th>
          <th class="score-col">Баллы</th>
          <th style="text-align:center">Детали</th>
        </tr>
      </thead>
      <tbody id="ratingBody"></tbody>
    </table>
  </div>
</div>

<!-- Модалка расшифровки баллов -->
<div class="modal-overlay" id="detailsModal">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="detailsTitle">Расшифровка рейтинга</h3>
      <button class="modal-close" onclick="closeModal('detailsModal')">✕</button>
    </div>
    <div id="detailsBody"></div>
  </div>
</div>

<!-- Модалка сборной / приказа -->
<div class="modal-overlay" id="teamModal">
  <div class="modal-card">
    <div class="modal-head">
      <h3>Проект приказа о формировании сборной</h3>
      <button class="modal-close" onclick="closeModal('teamModal')">✕</button>
    </div>
    <div style="margin-bottom:16px;display:flex;gap:10px">
      <button class="btn btn-secondary" onclick="printOrder()">🖨 Печать</button>
      <button class="btn btn-success" onclick="exportXLSX('order')">📥 Скачать (.xlsx)</button>
    </div>
    <div id="orderBody" style="background:#f8fafc;padding:20px;border:1px solid var(--border);border-radius:8px;font-family:serif;line-height:1.6;"></div>
  </div>
</div>

<script>
/* ================= БАЗА ДАННЫХ И СЕЗОНЫ ================= */
const DB_VERSION = '7.0';
const DB_KEY = 'fsp_database';
const CURRENT_SEASON = '2026';
const API_URL = 'http://127.0.0.1:8000/api';

// Базовые баллы
const POINTS_TABLE = {
  'Чемпионат России': [100, 80, 60],
  'Первенство России': [80, 60, 40],
  'Межрегиональные': [60, 45, 30],
  'Региональные': [40, 30, 20]
};

const RANK_WEIGHT = { '3 юношеский':0, '2 юношеский':1, '1 юношеский':2, '3 разряд':3, '2 разряд':4, '1 разряд':5, 'КМС':6, 'МС':7 };

const INITIAL_DB = {
  athletes: [
    { id:1, name:'Иванов Иван', birthDate:'2008-05-15', ageGroup:'15-16 лет', rank:'1 разряд', disc:'Алгоритмическое программирование', isDisqualified:false },
    { id:2, name:'Петрова Анна', birthDate:'2009-03-20', ageGroup:'15-16 лет', rank:'КМС', disc:'Алгоритмическое программирование', isDisqualified:false },
    { id:3, name:'Сидоров Олег', birthDate:'2008-08-10', ageGroup:'17-18 лет', rank:'1 разряд', disc:'Алгоритмическое программирование', isDisqualified:false },
    { id:4, name:'Кузнецова Мария', birthDate:'2010-11-05', ageGroup:'15-16 лет', rank:'КМС', disc:'Искусственный интеллект', isDisqualified:false },
    { id:5, name:'Морозов Дмитрий', birthDate:'2011-06-01', ageGroup:'13-14 лет', rank:'1 разряд', disc:'Спортивная робототехника', isDisqualified:false },
    { id:6, name:'Волков Артём', birthDate:'2007-02-15', ageGroup:'17-18 лет', rank:'МС', disc:'Алгоритмическое программирование', isDisqualified:true }, // Дисквалифицирован
    { id:7, name:'Смирнов Никита', birthDate:'2009-09-30', ageGroup:'15-16 лет', rank:'1 разряд', disc:'Алгоритмическое программирование', isDisqualified:false }
  ],
  competitions: [
    { id:101, name:'Чемпионат России 2026', level:'Чемпионат России', season:'2026' },
    { id:102, name:'Первенство России 2026', level:'Первенство России', season:'2026' },
    { id:103, name:'Кубок ФСП 2026', level:'Межрегиональные', season:'2026' },
    { id:104, name:'Кубок Оренбургской области 2026', level:'Региональные', season:'2026' },
    { id:105, name:'Открытый чемпионат Оренбурга 2026', level:'Региональные', season:'2026' },
    { id:106, name:'Турнир ИТ-куб 2026', level:'Региональные', season:'2026' }
  ],
  results: [
    // Иванов (участвовал в 3-х соревнованиях, дважды призер -> коэффициент 1.2)
    { athleteId:1, competitionId:101, place:1 }, // 100
    { athleteId:1, competitionId:104, place:2 }, // 30
    { athleteId:1, competitionId:105, place:4 }, // 0 (не призер)
    
    // Петрова (участвовала в 2-х, оба раза призер -> коэффициент 1.2)
    { athleteId:2, competitionId:102, place:2 }, // 60
    { athleteId:2, competitionId:103, place:3 }, // 30
    
    // Сидоров (один раз призер)
    { athleteId:3, competitionId:104, place:1 }, // 40
    
    // Кузнецова (ИИ - нет соревнований в этом сезоне, 0 баллов)
    
    // Морозов (робототехника - один раз призер)
    { athleteId:5, competitionId:106, place:1 }, // 40
    
    // Смирнов (дважды призер)
    { athleteId:7, competitionId:103, place:1 }, // 60
    { athleteId:7, competitionId:104, place:3 }  // 20
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

/* ================= ЛОГИКА РЕЙТИНГА ================= */
function calculateRating() {
  const db = getDB();
  const disc = document.getElementById('filter-disc').value;
  const ageGroup = document.getElementById('filter-age').value;
  const minRank = document.getElementById('filter-rank').value;

  // 1. Фильтруем спортсменов (по дисциплине, возрасту, разряду и без дисквалификации)
  let filteredAthletes = db.athletes.filter(a => {
    if (a.isDisqualified) return false;
    if (a.disc !== disc) return false;
    if (ageGroup && a.ageGroup !== ageGroup) return false;
    if (minRank && RANK_WEIGHT[a.rank] < RANK_WEIGHT[minRank]) return false;
    return true;
  });

  // 2. Считаем баллы
  const rating = filteredAthletes.map(ath => {
    const athResults = db.results.filter(r => r.athleteId === ath.id);
    let totalBasePoints = 0;
    let prizeCount = 0;
    let detailedResults = [];

    athResults.forEach(res => {
      const comp = db.competitions.find(c => c.id === res.competitionId);
      if (!comp || comp.season !== CURRENT_SEASON) return;

      const pointsArray = POINTS_TABLE[comp.level] || [0, 0, 0];
      const points = res.place <= 3 ? pointsArray[res.place - 1] : 0;
      
      totalBasePoints += points;
      if (res.place >= 1 && res.place <= 3) prizeCount++;

      detailedResults.push({
        compName: comp.name,
        level: comp.level,
        place: res.place,
        points: points
      });
    });

    // 3. Применяем коэффициент стабильности
    const hasStabilityBonus = prizeCount >= 2;
    const finalScore = hasStabilityBonus ? Math.round(totalBasePoints * 1.2) : totalBasePoints;

    return {
      ...ath,
      basePoints: totalBasePoints,
      finalScore: finalScore,
      hasBonus: hasStabilityBonus,
      details: detailedResults
    };
  });

  // 4. Сортируем по убыванию
  return rating.sort((a, b) => b.finalScore - a.finalScore);
}

/* ================= РЕНДЕР ================= */
function render() {
  const rating = calculateRating();
  const tbody = document.getElementById('ratingBody');
  
  if (!rating.length) {
    tbody.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--muted)">Нет спортсменов, подходящих под фильтры</td></tr>`;
    return;
  }

  tbody.innerHTML = rating.map((ath, index) => {
    // Цвет медали для топ-3
    let rankDisplay = index + 1;
    let rankStyle = "";
    if (rankDisplay === 1) rankStyle = "color:#b45309";
    if (rankDisplay === 2) rankStyle = "color:#475569";
    if (rankDisplay === 3) rankStyle = "color:#9a3412";

    return `<tr>
      <td class="checkbox-col">
        <input type="checkbox" class="ath-checkbox" value="${ath.id}" onchange="updateSelectedCount()">
      </td>
      <td class="rank-col" style="${rankStyle}">${rankDisplay}</td>
      <td><b>${ath.name}</b></td>
      <td>
        <span class="badge info">${ath.ageGroup}</span> 
        <span class="badge gold">${ath.rank}</span>
      </td>
      <td>${ath.disc}</td>
      <td class="score-col">
        ${ath.finalScore}
        ${ath.hasBonus ? '<span class="badge success" style="margin-left:6px" title="Коэффициент стабильности ×1.2">×1.2</span>' : ''}
      </td>
      <td style="text-align:center">
        <button class="details-btn" onclick='showDetails(${JSON.stringify(ath).replace(/'/g, "&#39;")})'>Посмотреть</button>
      </td>
    </tr>`;
  }).join('');
  
  updateSelectedCount();
}

/* ================= ПРОЗРАЧНОСТЬ (ДЕТАЛИ) ================= */
function showDetails(ath) {
  document.getElementById('detailsTitle').textContent = `Рейтинг: ${ath.name}`;
  
  let html = `
    <p><b>Дисциплина:</b> ${ath.disc} | <b>Возрастная группа:</b> ${ath.ageGroup} | <b>Разряд:</b> ${ath.rank}</p>
    <table style="width:100%; border-collapse:collapse; font-size:13px; margin-top:10px;">
      <thead>
        <tr style="background:#f8fafc;">
          <th style="padding:8px; border-bottom:1px solid #e2e8f0;">Соревнование</th>
          <th style="padding:8px; border-bottom:1px solid #e2e8f0;">Уровень</th>
          <th style="padding:8px; border-bottom:1px solid #e2e8f0;">Место</th>
          <th style="padding:8px; border-bottom:1px solid #e2e8f0; text-align:right;">Баллы</th>
        </tr>
      </thead>
      <tbody>
  `;

  if (ath.details.length === 0) {
    html += `<tr><td colspan="4" style="padding:10px; text-align:center; color:#64748b;">Нет результатов в текущем сезоне</td></tr>`;
  } else {
    ath.details.forEach(d => {
      html += `<tr>
        <td style="padding:8px; border-bottom:1px solid #e2e8f0;">${d.compName}</td>
        <td style="padding:8px; border-bottom:1px solid #e2e8f0;">${d.level}</td>
        <td style="padding:8px; border-bottom:1px solid #e2e8f0;">${d.place}</td>
        <td style="padding:8px; border-bottom:1px solid #e2e8f0; text-align:right;">${d.points}</td>
      </tr>`;
    });
  }

  html += `
      </tbody>
    </table>
    <div style="margin-top:16px; padding:12px; background:#f8fafc; border-radius:8px;">
      <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
        <span>Сумма базовых баллов:</span> <b>${ath.basePoints}</b>
      </div>
      <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:${ath.hasBonus ? '#16a34a' : '#64748b'};">
        <span>Коэффициент стабильности (×1.2):</span> <b>${ath.hasBonus ? 'Применён' : 'Нет'}</b>
      </div>
      <div style="display:flex; justify-content:space-between; font-size:16px; margin-top:8px; border-top:1px solid #e2e8f0; padding-top:8px;">
        <span><b>Итоговый балл:</b></span> <b style="color:#2563eb;">${ath.finalScore}</b>
      </div>
    </div>
  `;

  document.getElementById('detailsBody').innerHTML = html;
  document.getElementById('detailsModal').classList.add('show');
}

/* ================= СБОРНАЯ И ПРИКАЗ ================= */
function updateSelectedCount() {
  const count = document.querySelectorAll('.ath-checkbox:checked').length;
  document.getElementById('selectedCount').textContent = count;
  document.getElementById('btnCreateTeam').disabled = count === 0;
}

function openTeamModal() {
  const checkboxes = document.querySelectorAll('.ath-checkbox:checked');
  const selectedIds = Array.from(checkboxes).map(cb => +cb.value);
  const db = getDB();
  const disc = document.getElementById('filter-disc').value;
  const ageGroup = document.getElementById('filter-age').value;

  const selectedAthletes = db.athletes.filter(a => selectedIds.includes(a.id));

  let html = `
    <div style="text-align:center; margin-bottom:20px;">
      <h2 style="margin:0; font-size:20px;">ФЕДЕРАЦИЯ СПОРТИВНОГО ПРОГРАММИРОВАНИЯ</h2>
      <h2 style="margin:0; font-size:20px;">ОРЕНБУРГСКОЙ ОБЛАСТИ</h2>
      <br>
      <h3 style="margin:0; font-size:18px;">ПРОЕКТ ПРИКАЗА</h3>
      <p style="margin:5px 0;">от ${new Date().toLocaleDateString('ru-RU')} № ___</p>
      <br>
      <p style="text-align:left;"><b>О формировании сборной команды Оренбургской области по дисциплине «${disc}» ${ageGroup ? 'в возрастной группе ' + ageGroup : ''}</b></p>
      <br>
    </div>
    
    <p>В соответствии с Положением о рейтинге спортсменов, ПРИКАЗЫВАЮ:</p>
    <p>1. Утвердить состав сборной команды Оренбургской области в следующем составе:</p>
    
    <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:14px;">
      <thead>
        <tr>
          <th style="border:1px solid #000; padding:6px; text-align:center;">№</th>
          <th style="border:1px solid #000; padding:6px;">ФИО спортсмена</th>
          <th style="border:1px solid #000; padding:6px; text-align:center;">Разряд</th>
          <th style="border:1px solid #000; padding:6px; text-align:center;">Рейтинг</th>
        </tr>
      </thead>
      <tbody>
  `;

  const rating = calculateRating();
  selectedAthletes.forEach((ath, i) => {
    const ratingData = rating.find(r => r.id === ath.id);
    html += `
      <tr>
        <td style="border:1px solid #000; padding:6px; text-align:center;">${i + 1}</td>
        <td style="border:1px solid #000; padding:6px;">${ath.name}</td>
        <td style="border:1px solid #000; padding:6px; text-align:center;">${ath.rank}</td>
        <td style="border:1px solid #000; padding:6px; text-align:center;">${ratingData ? ratingData.finalScore : 0}</td>
      </tr>
    `;
  });

  html += `
      </tbody>
    </table>
    <br>
    <p>2. Контроль за исполнением настоящего приказа возложить на главного тренера сборной команды.</p>
    <br>
    <div style="text-align:right;">
      <p><b>Президент Федерации</b> _________________ / _______________ /</p>
    </div>
  `;

  document.getElementById('orderBody').innerHTML = html;
  document.getElementById('teamModal').classList.add('show');
}

function printOrder() {
  const printContent = document.getElementById('orderBody').innerHTML;
  const originalContent = document.body.innerHTML;
  document.body.innerHTML = `<div style="padding:40px; font-family:serif; font-size:14px; max-width:800px; margin:0 auto;">${printContent}</div>`;
  window.print();
  document.body.innerHTML = originalContent;
  location.reload(); // Перезагрузка для восстановления обработчиков
}

/* ================= ЭКСПОРТ В XLSX ================= */
function exportXLSX(type) {
  const wb = XLSX.utils.book_new();

  if (type === 'rating') {
    const rating = calculateRating();
    const rows = [["Место", "ФИО", "Возрастная группа", "Разряд", "Дисциплина", "Сумма баллов", "Коэф. 1.2", "Итоговый балл"]];
    
    rating.forEach((r, i) => {
      rows.push([
        i + 1, r.name, r.ageGroup, r.rank, r.disc, r.basePoints, r.hasBonus ? 'Да' : 'Нет', r.finalScore
      ]);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Рейтинг");
    XLSX.writeFile(wb, `Рейтинг_${new Date().toISOString().slice(0,10)}.xlsx`);
  } 
  
  else if (type === 'order') {
    const checkboxes = document.querySelectorAll('.ath-checkbox:checked');
    const selectedIds = Array.from(checkboxes).map(cb => +cb.value);
    const db = getDB();
    const rating = calculateRating();
    const disc = document.getElementById('filter-disc').value;
    
    const rows = [
      ["ПРОЕКТ ПРИКАЗА О ФОРМИРОВАНИИ СБОРНОЙ"],
      [`Дисциплина: ${disc}`],
      [],
      ["№", "ФИО спортсмена", "Разряд", "Рейтинг"]
    ];

    db.athletes.filter(a => selectedIds.includes(a.id)).forEach((ath, i) => {
      const ratingData = rating.find(r => r.id === ath.id);
      rows.push([i + 1, ath.name, ath.rank, ratingData ? ratingData.finalScore : 0]);
    });

    const ws = XLSX.utils.aoa_to_sheet(rows);
    XLSX.utils.book_append_sheet(wb, ws, "Приказ");
    XLSX.writeFile(wb, `Приказ_Сборная_${new Date().toISOString().slice(0,10)}.xlsx`);
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}

// Старт
render();
</script>
</body>
</html>