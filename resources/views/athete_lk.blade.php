<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Личный кабинет спортсмена — спортивное программирование</title>
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
  .user-badge{display:flex;align-items:center;gap:10px;padding:6px 12px;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow)}
  .user-badge .avatar{width:34px;height:34px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;background:#16a34a}
  .user-badge .info{display:flex;flex-direction:column;line-height:1.25} .user-badge .name{font-weight:600;font-size:13px} .user-badge .role{font-size:11px;color:var(--muted)}
  .logout-btn{padding:6px 10px;border:1px solid var(--border);background:#fff;border-radius:8px;font:inherit;font-size:12px;cursor:pointer;color:var(--muted)}
  .logout-btn:hover{background:var(--danger-soft);color:var(--danger);border-color:#fecaca}
  .tabs{display:flex;gap:4px;background:var(--card);padding:4px;border-radius:12px;box-shadow:var(--shadow);overflow-x:auto}
  .tab{padding:10px 18px;border-radius:9px;border:none;background:transparent;cursor:pointer;font:inherit;font-weight:500;color:var(--muted);white-space:nowrap;transition:.15s;display:flex;align-items:center;gap:6px}
  .tab:hover{color:var(--text);background:#f1f5f9}
  .tab.active{background:var(--primary);color:#fff}
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow)}
  .info-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin-bottom:16px}
  .info-item{background:#f8fafc;padding:12px 14px;border-radius:10px}
  .info-item .lbl{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;font-weight:600;margin-bottom:4px}
  .info-item .val{font-size:14px} .info-item.full{grid-column:1 / -1}
  .section-title{font-size:14px;font-weight:600;margin:20px 0 12px;padding-top:16px;border-top:1px solid var(--border)}
  .section-title:first-of-type{border-top:none;padding-top:0;margin-top:0}
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap}
  .badge.success{background:#dcfce7;color:#15803d} .badge.warn{background:#fef3c7;color:#b45309} .badge.danger{background:#fee2e2;color:#b91c1c} .badge.info{background:#dbeafe;color:#1d4ed8} .badge.gray{background:#f1f5f9;color:#64748b}
  .btn-primary{padding:9px 16px;background:var(--primary);color:#fff;border:none;border-radius:10px;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s}
  .btn-primary:hover{background:#1d4ed8} .btn-secondary{padding:9px 16px;background:#fff;color:var(--text);border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;cursor:pointer}
  .btn-secondary:hover{background:#f8fafc} .btn-sm{padding:6px 12px;font-size:12px;border-radius:8px}
  .card{border-radius:12px;padding:16px;border:1px solid var(--border);background:#fff}
  .card.success{background:#f0fdf4;border-color:#bbf7d0} .card.warn{background:#fffbeb;border-color:#fde68a} .card.danger{background:#fef2f2;border-color:#fecaca} .card.info{background:#eff6ff;border-color:#bfdbfe}
  .flex-between{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
  .doc-item{display:flex;align-items:center;gap:14px;padding:12px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px}
  .doc-icon{width:36px;height:36px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
  .doc-item .body{flex:1;min-width:0} .doc-item .title{font-weight:600;font-size:13px} .doc-item .meta{font-size:12px;color:var(--muted);margin-top:2px}
  .comp-item{padding:14px;border:1px solid var(--border);border-radius:10px;margin-bottom:10px}
  .comp-item .head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:8px}
  .comp-item .title{font-weight:600;font-size:14px} .comp-item .meta{display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted);margin-bottom:8px} .comp-item .actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:11px 12px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle}
  th{font-weight:600;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.3px;background:#f8fafc;white-space:nowrap}
  tbody tr:last-child td{border-bottom:none} tbody tr:hover{background:#f8fafc} .right{text-align:right} .center{text-align:center} .empty{text-align:center;padding:40px;color:var(--muted)}
  .form-group{margin-bottom:14px} .form-group > label{display:block;font-weight:500;margin-bottom:6px;font-size:13px} .form-group .req{color:var(--danger)}
  .form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;outline:none}
  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;z-index:1000;padding:20px}
  .modal-overlay.show{display:flex} .modal-card{background:#fff;border-radius:20px;max-width:600px;width:100%;display:flex;flex-direction:column}
  .modal-head{display:flex;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border)} .modal-head h2{margin:0;font-size:18px}
  .modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted)}
  .modal-body{padding:20px 24px} .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}
  .login-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1100;padding:20px}
  .login-overlay.show{display:flex} .login-card{background:#fff;border-radius:20px;padding:32px;max-width:640px;width:100%}
  .login-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:20px}
  .login-option{display:flex;gap:14px;align-items:center;padding:14px;border:1px solid var(--border);border-radius:14px;cursor:pointer;background:#fff;text-align:left;transition:.15s}
  .login-option:hover{border-color:var(--primary);background:#eff6ff}
  .login-option .avatar{width:44px;height:44px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;background:#16a34a}
  .toast{position:fixed;bottom:24px;right:24px;padding:12px 18px;background:#0f172a;color:#fff;border-radius:10px;font-size:13px;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none;z-index:1200} .toast.show{opacity:1;transform:translateY(0)}
  .ad-hero{padding:24px;border-radius:14px;margin-bottom:16px;display:flex;gap:20px;align-items:flex-start}
  .ad-hero.success{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0} .ad-hero.warn{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a}
  .ad-hero .icon{font-size:32px;flex-shrink:0} .ad-hero .body{flex:1} .ad-hero h3{margin:0 0 4px;font-size:16px}
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div><h1>Личный кабинет спортсмена</h1><div class="muted">Федерация спортивного программирования</div></div>
    <div class="user-badge" id="userBadge" style="display:none"></div>
  </div>
  <nav class="tabs" id="tabs"></nav>
  <div class="panel" id="panelProfile" style="display:none"></div>
  <div class="panel" id="panelCoaches" style="display:none"></div>
  <div class="panel" id="panelAntidoping" style="display:none"></div>
  <div class="panel" id="panelCalendar" style="display:none"></div>
  <div class="panel" id="panelResults" style="display:none"></div>
  <div class="panel" id="panelRank" style="display:none"></div>
  <div class="panel" id="panelDocuments" style="display:none"></div>
</div>

<div class="login-overlay" id="loginOverlay">
  <div class="login-card">
    <h2>Вход в кабинет</h2><div class="sub">Выберите спортсмена</div>
    <div class="login-grid" id="loginGrid"></div>
  </div>
</div>

<div class="modal-overlay" id="adModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Сертификат РУСАДА</h2><button class="modal-close" data-close="adModal">✕</button></div>
    <div class="modal-body" id="adModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="adModal">Отмена</button><button class="btn-primary" id="adSaveBtn">Сохранить</button></div>
  </div>
</div>
<div class="modal-overlay" id="rankModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Заявка на разряд</h2><button class="modal-close" data-close="rankModal">✕</button></div>
    <div class="modal-body" id="rankModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="rankModal">Отмена</button><button class="btn-primary" id="rankSaveBtn">Подать заявку</button></div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ================= БАЗА ДАННЫХ ================= */
const DB_VERSION = '5.1'; // Обновлено для добавления Email
const DB_KEY = 'fsp_database';
const SESSION_KEY = 'fsp_user';

const INITIAL_DB = {
  athletes: [
    { id:1, name:'Иванов Иван Иванович', email:'ivanov@example.com', birthDate:'2008-05-15', muni:'г. Оренбург', org:'Губернаторский лицей', disciplines:['Алгоритмическое программирование'], yearStart:2018, coaches:['Петров А.С.'], rank:'1 разряд', rankExpires:'2028-09-01', antidoping:{passed:true, date:'2026-05-10', url:'https://rusada.ru/cert/123'}, legalRep: null },
    { id:2, name:'Петрова Анна Сергеевна', email:'petrova@example.com', birthDate:'2010-03-20', muni:'г. Оренбург', org:'Лицей №1', disciplines:['Алгоритмическое программирование'], yearStart:2019, coaches:['Смирнов В.В.'], rank:'КМС', rankExpires:'2029-01-01', antidoping:{passed:true, date:'2026-01-15', url:'https://rusada.ru/cert/456'}, legalRep: { name:'Петрова Мария Ивановна', phone:'+7 912 345-67-89', email:'petrova_m@example.com', consentDate:'2025-09-01' } },
    { id:3, name:'Сидоров Олег Павлович', email:'sidorov@example.com', birthDate:'2012-08-10', muni:'г. Орск', org:'Гимназия №1', disciplines:['Спортивная робототехника'], yearStart:2020, coaches:['Петров А.С.'], rank:'2 разряд', rankExpires:'2026-12-01', antidoping:{passed:false}, legalRep: { name:'Сидорова Елена Петровна', phone:'+7 912 111-22-33', email:'sidorova@example.com', consentDate:'2025-10-10' } },
    { id:4, name:'Кузнецова Мария Ильинична', email:'kuznetsova@example.com', birthDate:'2009-11-05', muni:'г. Бузулук', org:'IT-куб', disciplines:['Искусственный интеллект'], yearStart:2017, coaches:['Смирнов В.В.'], rank:'1 юношеский', rankExpires:'2026-11-25', antidoping:{passed:true, date:'2026-07-01', url:'https://rusada.ru/cert/789'}, legalRep: { name:'Кузнецов Илья Андреевич', phone:'+7 912 444-55-66', email:'kuznetsov@example.com', consentDate:'2024-11-05' } },
    { id:5, name:'Морозов Дмитрий Андреевич', email:'morozov@example.com', birthDate:'2015-06-01', muni:'г. Новотроицк', org:'Кванториум', disciplines:['Спортивная робототехника'], yearStart:2022, coaches:[], rank:'3 юношеский', rankExpires:'2028-11-15', antidoping:{passed:false}, legalRep: { name:'Морозова Ольга Сергеевна', phone:'+7 912 777-88-99', email:'morozova@example.com', consentDate:'2025-06-01' } }
  ],
  competitions: [
    { id:1, name:'Областной этап ВсОШ', discipline:'Алгоритмическое программирование', startDate:'2026-11-05', endDate:'2026-11-07', ageGroups:['15-16 лет','17-18 лет'], deadline:'2026-11-03', status:'applications_open', positionUrl:'#' },
    { id:2, name:'Кубок области', discipline:'Алгоритмическое программирование', startDate:'2026-11-21', endDate:'2026-11-21', ageGroups:['13-14 лет','15-16 лет','17-18 лет','19+'], deadline:'2026-11-15', status:'applications_open', positionUrl:'#' },
    { id:3, name:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', startDate:'2025-09-15', endDate:'2025-09-15', ageGroups:['17-18 лет','19+'], deadline:'2025-09-01', status:'completed', positionUrl:'#' }
  ],
  applications: [], 
  rankApplications: [], 
  results: [ { id:1, athleteId:1, competitionId:3, competitionName:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', date:'2025-09-15', place:2, score:8, status:'official', protocolUrl:'#' } ],
  reports: [], judges: [], judgeApplications: [], judgeAssignments: [],
  coachLinks: [
    { id:1, coachId:1, athleteId:1, discipline:'Алгоритмическое программирование', status:'active', date:'2024-01-10' },
    { id:2, coachId:1, athleteId:3, discipline:'Спортивная робототехника', status:'active', date:'2024-02-15' },
    { id:3, coachId:2, athleteId:2, discipline:'Алгоритмическое программирование', status:'active', date:'2024-03-01' },
    { id:4, coachId:2, athleteId:4, discipline:'Искусственный интеллект', status:'active', date:'2024-03-10' }
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
function saveDB(db) { localStorage.setItem(DB_KEY, JSON.stringify(db)); }

const sessionSave = (u) => localStorage.setItem(SESSION_KEY, JSON.stringify({ id: u.id }));
const sessionLoad = () => {
  try {
    const r = JSON.parse(localStorage.getItem(SESSION_KEY));
    return r ? getAllUsers().find(u => u.id === r.id) || null : null;
  } catch (e) { return null; }
};
const sessionClear = () => localStorage.removeItem(SESSION_KEY);

const DEMO_USERS = [
  { id:'athlete-1', name:'Иванов Иван Иванович', short:'ИИ', role:'athlete', roleLabel:'Спортсмен', athleteId:1 },
  { id:'athlete-2', name:'Петрова Анна Сергеевна', short:'ПА', role:'athlete', roleLabel:'Спортсмен', athleteId:2 },
  { id:'athlete-3', name:'Сидоров Олег Павлович', short:'СО', role:'athlete', roleLabel:'Спортсмен', athleteId:3 },
  { id:'athlete-4', name:'Кузнецова Мария Ильинична', short:'КМ', role:'athlete', roleLabel:'Спортсмен', athleteId:4 },
  { id:'athlete-5', name:'Морозов Дмитрий Андреевич', short:'МД', role:'athlete', roleLabel:'Спортсмен', athleteId:5 }
];

// см. блок-помощник выше (REG_KEY / getRegisteredByRole / makeRegUser)

function getAllUsers() {
  return [
    ...DEMO_USERS,
    ...getRegisteredByRole('athlete').map(r => makeRegUser(r, 'athlete'))
  ];
}


/* ================= УТИЛИТЫ ================= */
const pad = n => String(n).padStart(2,'0');
const iso = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const today = () => { const d = new Date(); d.setHours(0,0,0,0); return d; };
const parseISO = s => { const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); };
const daysBetween = (a,b) => Math.round((parseISO(b) - parseISO(a)) / 86400000);
const fmtDate = s => { if(!s) return '—'; const d = parseISO(s); return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()}`; };
const daysWord = n => { const a = Math.abs(n) % 100, b = a % 10; if (a > 10 && a < 20) return 'дней'; if (b > 1 && b < 5) return 'дня'; if (b === 1) return 'день'; return 'дней'; };

function calcAge(birthDate) { const bd = parseISO(birthDate); const now = new Date(); let age = now.getFullYear() - bd.getFullYear(); const m = now.getMonth() - bd.getMonth(); if (m < 0 || (m === 0 && now.getDate() < bd.getDate())) age--; return age; }
function ageGroupOf(age) { if (age <= 8) return '6-8 лет'; if (age <= 10) return '9-10 лет'; if (age <= 12) return '11-12 лет'; if (age <= 14) return '13-14 лет'; if (age <= 16) return '15-16 лет'; if (age <= 18) return '17-18 лет'; return '19+'; }
function appStatusBadge(s) { return {'pending':'<span class="badge warn">На рассмотрении</span>','accepted':'<span class="badge success">Принята</span>','approved':'<span class="badge success">Одобрена</span>','rejected':'<span class="badge danger">Отклонена</span>'}[s] || s; }
function toast(msg) { const el = document.getElementById('toast'); el.textContent = msg; el.classList.add('show'); setTimeout(() => el.classList.remove('show'), 2500); }

const STATE = { user: null, tab: 'profile', filter: { onlyMine:false } };
const currentAthlete = () => {
  const db = getDB();
  if (STATE.user?.isRegistered) {
    let ath = db.athletes.find(a => a.id === STATE.user.athleteId);
    if (!ath) ath = ensureAthleteRecord(STATE.user);
    return ath;
  }
  return db.athletes.find(a => a.id === STATE.user?.athleteId);
};

/* ================= РЕНДЕР ================= */
function renderUserBadge() {
  const el = document.getElementById('userBadge');
  if (!STATE.user) { el.style.display = 'none'; return; }
  el.style.display = 'flex';
  el.innerHTML = `<div class="avatar">${STATE.user.short}</div><div class="info"><div class="name">${STATE.user.name}</div><div class="role">Спортсмен</div></div><button class="logout-btn" id="logoutBtn">Выйти</button>`;
  document.getElementById('logoutBtn').addEventListener('click', () => { STATE.user = null; sessionClear(); document.getElementById('loginOverlay').classList.add('show'); buildLoginGrid(); renderAll(); });
}

function renderTabs() {
  const db = getDB();
  const hasPendingLinks = db.coachLinks.some(l => l.athleteId === STATE.user.athleteId && l.status === 'pending');
  const tabs = [
    { key:'profile', label:'👤 Профиль' },
    { key:'coaches', label:`🤝 Мои тренеры ${hasPendingLinks ? '<span class="badge danger" style="margin-left:4px">!</span>' : ''}` },
    { key:'antidoping', label:'💊 Антидопинг' },
    { key:'calendar', label:'📅 Календарь' },
    { key:'results', label:'🏆 Результаты' },
    { key:'rank', label:'🏅 Разряд' },
    { key:'documents', label:'📄 Документы' }
  ];
  document.getElementById('tabs').innerHTML = tabs.map(t => `<button class="tab ${STATE.tab === t.key ? 'active' : ''}" data-tab="${t.key}">${t.label}</button>`).join('');
}

function renderProfile() {
  const panel = document.getElementById('panelProfile');
  panel.style.display = STATE.tab === 'profile' ? '' : 'none'; if (STATE.tab !== 'profile') return;
  const a = currentAthlete(); if (!a) return;
  const age = calcAge(a.birthDate); const isMinor = age < 18;
  
  let html = `
    <h2>Профиль спортсмена</h2>
    <div class="section-title">Личные данные</div>
    <div class="info-grid">
      <div class="info-item full"><div class="lbl">ФИО</div><div class="val"><b>${a.name}</b></div></div>
      <div class="info-item"><div class="lbl">Дата рождения</div><div class="val">${fmtDate(a.birthDate)} (${age} лет)</div></div>
      <div class="info-item"><div class="lbl">Email для уведомлений</div><div class="val"><b>${a.email || '—'}</b></div></div>
      <div class="info-item"><div class="lbl">Муниципалитет</div><div class="val">${a.muni}</div></div>
      <div class="info-item"><div class="lbl">Организация</div><div class="val">${a.org || 'Без организации'}</div></div>
      <div class="info-item"><div class="lbl">Год начала занятий</div><div class="val">${a.yearStart}</div></div>
      <div class="info-item full"><div class="lbl">Дисциплины</div><div class="val">${a.disciplines.join(', ')}</div></div>
    </div>
    <div class="section-title">Тренеры</div>
    <div class="info-grid">${a.coaches.length ? a.coaches.map(c => `<div class="info-item"><div class="lbl">Тренер</div><div class="val">${c}</div></div>`).join('') : `<div class="info-item full"><div class="lbl">Тренер</div><div class="val"><span class="muted">Без тренера</span> (можно подавать заявки на разряд самостоятельно)</div></div>`}</div>
    <div class="section-title">Спортивный разряд</div>
    <div class="info-grid">
      <div class="info-item"><div class="lbl">Текущий разряд</div><div class="val"><span class="badge info">${a.rank}</span></div></div>
      <div class="info-item"><div class="lbl">Действует до</div><div class="val">${fmtDate(a.rankExpires)}</div></div>
    </div>`;
  
  if (isMinor && a.legalRep) {
    html += `
      <div class="section-title">Законный представитель</div>
      <div class="card info">
        <div class="info-grid" style="margin:0">
          <div class="info-item"><div class="lbl">ФИО</div><div class="val">${a.legalRep.name}</div></div>
          <div class="info-item"><div class="lbl">Телефон</div><div class="val">${a.legalRep.phone}</div></div>
          <div class="info-item"><div class="lbl">Email</div><div class="val">${a.legalRep.email}</div></div>
          <div class="info-item"><div class="lbl">Согласие на ПД</div><div class="val"><span class="badge success">Подписано ${fmtDate(a.legalRep.consentDate)}</span></div></div>
        </div>
      </div>`;
  }
  panel.innerHTML = html;
}

function renderCoaches() {
  const panel = document.getElementById('panelCoaches');
  panel.style.display = STATE.tab === 'coaches' ? '' : 'none'; if (STATE.tab !== 'coaches') return;
  const db = getDB();
  const myLinks = db.coachLinks.filter(l => l.athleteId === STATE.user.athleteId);
  const pending = myLinks.filter(l => l.status === 'pending');
  const active = myLinks.filter(l => l.status === 'active');

  panel.innerHTML = `
    <h2>Мои тренеры</h2>
    ${pending.length > 0 ? `
      <div class="card warn" style="margin-bottom:16px">
        <h3 style="margin-top:0;font-size:14px;color:#b45309">Входящие запросы на привязку</h3>
        <table>
          <tbody>
            ${pending.map(l => {
              const coachName = l.coachId === 1 ? 'Петров А.С.'
              : l.coachId === 2 ? 'Смирнов В.В.'
              : 'Тренер #' + l.coachId;
              return `<tr>
                <td><b>${coachName}</b></td>
                <td>Дисциплина: <b>${l.discipline}</b></td>
                <td>
                  <button class="btn-primary btn-sm" onclick="confirmLink(${l.id})">Подтвердить</button>
                  <button class="btn-secondary btn-sm" onclick="rejectLink(${l.id})">Отклонить</button>
                </td>
              </tr>`;
            }).join('')}
          </tbody>
        </table>
      </div>
    ` : ''}

    <div class="section-title">Активные связи</div>
    ${active.length > 0 ? `
      <table>
        <thead><tr><th>Тренер</th><th>Дисциплина</th><th>Дата привязки</th><th>Статус</th></tr></thead>
        <tbody>
          ${active.map(l => {
            const coachName = l.coachId === 1 ? 'Петров Алексей Сергеевич' : 'Смирнов Виктор Владимирович';
            return `<tr><td><b>${coachName}</b></td><td><span class="badge info">${l.discipline}</span></td><td>${fmtDate(l.date)}</td><td><span class="badge success">Активна</span></td></tr>`;
          }).join('')}
        </tbody>
      </table>
    ` : `<div class="empty">У вас пока нет активных тренеров</div>`}
  `;
}

function renderAntidoping() {
  const panel = document.getElementById('panelAntidoping');
  panel.style.display = STATE.tab === 'antidoping' ? '' : 'none'; if (STATE.tab !== 'antidoping') return;
  const a = currentAthlete(); if (!a) return;
  const ad = a.antidoping || { passed:false };
  const hero = ad.passed 
    ? `<div class="ad-hero success"><div class="icon">✅</div><div class="body"><h3>Курс РУСАДА пройден</h3><div class="sub">Дата: <b>${fmtDate(ad.date)}</b></div><div class="sub">Сертификат: <a href="${ad.url}" target="_blank">${ad.url}</a></div></div></div>`
    : `<div class="ad-hero warn"><div class="icon">⚠️</div><div class="body"><h3>Курс не пройден</h3><div class="sub">Пройдите курс на сайте РУСАДА и отметьте результат.</div><button class="btn-primary btn-sm" id="markPassedBtn" style="margin-top:10px">Отметить как пройденный</button></div></div>`;
  
  panel.innerHTML = `<h2>Антидопинг</h2>${hero}
    <div class="section-title">Официальные ресурсы РУСАДА</div>
    <div class="info-grid">
      <div class="info-item full"><div class="lbl">Сайт РУСАДА</div><div class="val"><a href="https://rusada.ru" target="_blank">rusada.ru</a> — антидопинговые правила, курсы и горячая линия</div></div>
      <div class="info-item full"><div class="lbl">Онлайн-обучение</div><div class="val"><a href="https://rusada.ru/education/" target="_blank">rusada.ru/education/</a> — курсы для спортсменов и тренеров</div></div>
    </div>`;
  if (!ad.passed) document.getElementById('markPassedBtn').addEventListener('click', openAdModal);
}

function renderCalendar() {
  const panel = document.getElementById('panelCalendar');
  panel.style.display = STATE.tab === 'calendar' ? '' : 'none'; if (STATE.tab !== 'calendar') return;
  const a = currentAthlete(); if (!a) return;
  const db = getDB();
  const ageGroup = ageGroupOf(calcAge(a.birthDate));
  
  let comps = db.competitions.filter(c => c.status === 'applications_open');
  if (STATE.filter.onlyMine) comps = comps.filter(c => c.ageGroups.includes(ageGroup));

  panel.innerHTML = `
    <h2>Календарь соревнований</h2>
    <div style="margin-bottom:16px">
      <label><input type="checkbox" id="calOnlyMine" ${STATE.filter.onlyMine?'checked':''}> Только подходящие по возрасту (${ageGroup})</label>
    </div>
    <div id="compList">${comps.map(c => {
      const hasApp = db.applications.find(x => x.athleteId === a.id && x.competitionId === c.id);
      const matches = c.ageGroups.includes(ageGroup);
      let action = '';
      if (hasApp) action = appStatusBadge(hasApp.status);
      else if (!matches) action = `<span class="badge gray">Не подходит по возрасту</span>`;
      else action = `<button class="btn-primary btn-sm" data-apply="${c.id}">Подать заявку</button>`;
      
      return `<div class="comp-item"><div class="head"><div class="title">${c.name}</div></div>
        <div class="meta"><span>📅 ${fmtDate(c.startDate)}</span><span>🏷 ${c.discipline}</span><span>👥 ${c.ageGroups.join(', ')}</span><span>⏳ до ${fmtDate(c.deadline)}</span></div>
        <div class="actions">
          <a class="btn-secondary btn-sm" href="${c.positionUrl}" target="_blank">📥 Скачать положение</a>
          ${action}
        </div></div>`;
    }).join('')}</div>`;

  document.getElementById('calOnlyMine').addEventListener('change', e => { STATE.filter.onlyMine = e.target.checked; renderCalendar(); });
  panel.querySelectorAll('button[data-apply]').forEach(b => b.addEventListener('click', () => applyComp(+b.dataset.apply)));
}

function renderResults() {
  const panel = document.getElementById('panelResults');
  panel.style.display = STATE.tab === 'results' ? '' : 'none'; if (STATE.tab !== 'results') return;
  const a = currentAthlete(); if (!a) return;
  const myRes = getDB().results.filter(r => r.athleteId === a.id);
  if (!myRes.length) { panel.innerHTML = `<h2>Результаты</h2><div class="empty">Нет результатов</div>`; return; }
  panel.innerHTML = `<h2>История выступлений</h2>
    <table><thead><tr><th>Дата</th><th>Соревнование</th><th>Дисциплина</th><th>Место</th><th>Баллы</th><th>Статус</th><th>Протокол</th></tr></thead>
    <tbody>${myRes.map(r => `<tr>
      <td>${fmtDate(r.date)}</td>
      <td>${r.competitionName}</td>
      <td>${r.discipline}</td>
      <td class="center"><b>${r.place}</b></td>
      <td class="right">${r.score}</td>
      <td class="center">${r.status === 'official' ? '<span class="badge success">Официальный</span>' : '<span class="badge warn">Черновик</span>'}</td>
      <td class="right"><a href="${r.protocolUrl || '#'}" target="_blank" class="btn-secondary btn-sm">Открыть</a></td>
    </tr>`).join('')}</tbody></table>`;
}

function renderRank() {
  const panel = document.getElementById('panelRank');
  panel.style.display = STATE.tab === 'rank' ? '' : 'none'; if (STATE.tab !== 'rank') return;
  const a = currentAthlete(); if (!a) return;
  const daysLeft = daysBetween(iso(today()), a.rankExpires);
  const isExp = daysLeft < 0;
  const isWarn = daysLeft <= 60 && daysLeft >= 0;
  
  const myApps = getDB().rankApplications.filter(x => x.athleteId === a.id);
  const noCoach = a.coaches.length === 0;

  panel.innerHTML = `<h2>Разряд</h2>
    <div class="card ${isExp?'danger':isWarn?'warn':'success'}" style="margin-bottom:16px">
      <div class="flex-between"><div><div style="font-size:22px;font-weight:700">${a.rank}</div></div><div style="text-align:right"><div class="muted">Действует до</div><div style="font-size:18px;font-weight:600">${fmtDate(a.rankExpires)}</div></div></div>
      <div style="margin-top:10px">${isExp ? '<span class="badge danger">Просрочен</span>' : isWarn ? `<span class="badge warn">Истекает (${daysLeft} дн.)</span>` : '<span class="badge success">Действует</span>'}</div>
    </div>
    <div class="section-title">Мои заявки</div>
    ${myApps.length ? `<table><thead><tr><th>Тип</th><th>Разряд</th><th>Основание</th><th>Статус</th></tr></thead><tbody>${myApps.map(r => `<tr><td>${r.type==='assign'?'Присвоение':'Подтверждение'}</td><td>${r.requestedRank}</td><td>${r.competitionName||'—'}</td><td>${appStatusBadge(r.status)}</td></tr>`).join('')}</tbody></table>` : `<div class="empty">Нет заявок</div>`}
    <div class="section-title">Подача заявки</div>
    ${noCoach ? `<div class="card info"><div style="margin-bottom:10px">У вас нет тренера. Вы можете подать заявку самостоятельно.</div><button class="btn-primary" id="btnRankModal">+ Подать заявку</button></div>` : `<div class="card">Заявки подаёт ваш тренер <b>${a.coaches[0]}</b></div>`}
  `;
  if (noCoach) document.getElementById('btnRankModal').addEventListener('click', openRankModal);
}

function renderDocuments() {
  const panel = document.getElementById('panelDocuments');
  panel.style.display = STATE.tab === 'documents' ? '' : 'none'; if (STATE.tab !== 'documents') return;
  const a = currentAthlete();
  let docsHtml = `<div class="doc-item"><div class="doc-icon">📄</div><div class="body"><div class="title">Согласие на обработку ПД</div><div class="meta">Подписано спортсменом</div></div><button class="btn-secondary btn-sm" onclick="alert('Документ скачан')">Скачать</button></div>`;
  if (a.legalRep) {
    docsHtml += `<div class="doc-item"><div class="doc-icon">📄</div><div class="body"><div class="title">Согласие законного представителя</div><div class="meta">Подписано: ${a.legalRep.name}</div></div><button class="btn-secondary btn-sm" onclick="alert('Документ скачан')">Скачать</button></div>`;
  }
  panel.innerHTML = `<h2>Документы</h2>${docsHtml}`;
}

function renderAll() {
  renderUserBadge();
  if (!STATE.user) { document.getElementById('tabs').innerHTML = ''; ['panelProfile','panelCoaches','panelAntidoping','panelCalendar','panelResults','panelRank','panelDocuments'].forEach(id => document.getElementById(id).style.display = 'none'); return; }
  renderTabs(); 
  renderProfile(); 
  renderCoaches();
  renderAntidoping(); 
  renderCalendar(); 
  renderResults(); 
  renderRank(); 
  renderDocuments();
}

/* ================= ДЕЙСТВИЯ ================= */
function confirmLink(linkId) {
  const db = getDB();
  const link = db.coachLinks.find(l => l.id === linkId);
  if(link) link.status = 'active';
  
  const coachName = link.coachId === 1 ? 'Петров А.С.' : 'Смирнов В.В.';
  const ath = db.athletes.find(a => a.id === link.athleteId);
  if (ath && !ath.coaches.includes(coachName)) ath.coaches.push(coachName);
  
  saveDB(db); toast('Связь подтверждена'); renderAll();
}

function rejectLink(linkId) {
  const db = getDB();
  const link = db.coachLinks.find(l => l.id === linkId);
  if(link) link.status = 'rejected';
  saveDB(db); toast('Запрос отклонён'); renderAll();
}

function applyComp(compId) {
  const a = currentAthlete(); const db = getDB();
  if (db.applications.find(x => x.athleteId === a.id && x.competitionId === compId)) return;
  db.applications.push({ id: Date.now(), athleteId: a.id, competitionId: compId, submittedDate: iso(today()), status: 'pending' });
  saveDB(db); toast('Заявка подана'); renderCalendar();
}

function openAdModal() {
  document.getElementById('adModalBody').innerHTML = `<div class="form-group"><label>Дата</label><input type="date" id="ad-date" value="${iso(today())}"></div><div class="form-group"><label>Ссылка на сертификат</label><input type="url" id="ad-url" placeholder="https://..."></div>`;
  document.getElementById('adModal').classList.add('show');
}
document.getElementById('adSaveBtn').addEventListener('click', () => {
  const date = document.getElementById('ad-date').value; const url = document.getElementById('ad-url').value;
  if (!date || !url) { toast('Заполните поля'); return; }
  const db = getDB(); const a = db.athletes.find(x => x.id === STATE.user.athleteId); a.antidoping = { passed:true, date, url };
  saveDB(db); closeModal('adModal'); renderAntidoping(); toast('Сохранено');
});

function openRankModal() {
  const a = currentAthlete();
  document.getElementById('rankModalBody').innerHTML = `<div class="form-group"><label>Тип заявки</label><select id="rk-type"><option value="confirm">Подтверждение (${a.rank})</option><option value="assign">Присвоение следующего</option></select></div><div class="form-group"><label>Соревнование (основание)</label><select id="rk-comp"><option value="">— не указано —</option>${getDB().competitions.filter(c=>c.status==='completed').map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</select></div><div class="form-group"><label>Комментарий</label><textarea id="rk-comment" rows="2"></textarea></div>`;
  document.getElementById('rankModal').classList.add('show');
}
document.getElementById('rankSaveBtn').addEventListener('click', () => {
  const type = document.getElementById('rk-type').value; const compId = +document.getElementById('rk-comp').value; const comment = document.getElementById('rk-comment').value;
  const db = getDB(); const a = currentAthlete(); const comp = db.competitions.find(c => c.id === compId);
  db.rankApplications.push({ id: Date.now(), athleteId: a.id, type, requestedRank: type==='confirm'?a.rank:'КМС', competitionId: compId, competitionName: comp ? comp.name : null, submittedDate: iso(today()), status: 'pending', comment });
  saveDB(db); closeModal('rankModal'); renderRank(); toast('Заявка подана');
});

function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.addEventListener('click', e => { if (e.target.closest('[data-close]')) closeModal(e.target.closest('[data-close]').dataset.close); });

/* ================= АВТОРИЗАЦИЯ И СТАРТ ================= */
function buildLoginGrid() {
  const users = getAllUsers();
  document.getElementById('loginGrid').innerHTML = users.map(u => `
    <button class="login-option" onclick="loginAs('${u.id}')">
      <div class="avatar">${u.short}</div>
      <div>
        <div style="font-weight:600">${u.name}</div>
        <div style="font-size:12px;color:var(--muted)">
          ${u.roleLabel}${u.isRegistered ? ' · зарегистрирован' : ''}
        </div>
      </div>
    </button>`).join('');
}

function loginAs(id) {
  const user = getAllUsers().find(u => u.id === id);
  if (!user) return;
  if (user.isRegistered) ensureAthleteRecord(user);
  STATE.user = user;
  sessionSave(user);
  STATE.tab = 'profile';
  document.getElementById('loginOverlay').classList.remove('show');
  renderAll();
}

buildLoginGrid();
const saved = sessionLoad();
if (saved && saved.role === 'athlete') { STATE.user = saved; renderAll(); } 
else { document.getElementById('loginOverlay').classList.add('show'); renderAll(); }

function ensureAthleteRecord(user) {
  const db = getDB();
  let ath = db.athletes.find(a => a.email && a.email.toLowerCase() === user.email);
  if (!ath) {
    const reg = getRegisteredByRole('athlete').find(r => r.id === user.regId) || {};
    ath = {
      id: Math.max(0, ...db.athletes.map(x => x.id)) + 1,
      name: user.name,
      email: user.email,
      birthDate: reg.birthdate || '2005-01-01',
      muni: '—',
      org: '—',
      disciplines: [],
      yearStart: new Date().getFullYear(),
      coaches: [],
      rank: 'Без разряда',
      rankExpires: '2099-12-31',
      antidoping: { passed: false },
      legalRep: null
    };
    db.athletes.push(ath);
    saveDB(db);
  }
  user.athleteId = ath.id;
  return ath;
}

/* ====== ИНТЕГРАЦИЯ С РЕГИСТРАЦИЕЙ (index_reg) ====== */
const REG_KEY = 'fsp_registered_users';
const ROLE_LABELS = {
  athlete: 'Спортсмен', coach: 'Тренер',
  judge: 'Судья', federation: 'Член Федерации'
};

function getRegisteredByRole(role) {
  try {
    const arr = JSON.parse(localStorage.getItem(REG_KEY) || '[]');
    return arr.filter(u => Array.isArray(u.roles) && u.roles.includes(role));
  } catch (e) { return []; }
}

function makeRegUser(reg, role) {
  const short = (((reg.firstName || '')[0] || '') + ((reg.lastName || '')[0] || '')).toUpperCase() || '??';
  return {
    id: 'reg-' + reg.id + '-' + role,
    regId: reg.id,
    name: [reg.lastName, reg.firstName, reg.patronymic].filter(Boolean).join(' ') || reg.email,
    short,
    role,
    roleLabel: ROLE_LABELS[role] || role,
    email: reg.email,
    isRegistered: true
  };
}
</script>
</body>
</html>