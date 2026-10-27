<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Личный кабинет судьи — спортивное программирование</title>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --primary-soft:#dbeafe; --success:#16a34a; --success-soft:#dcfce7;
    --warning:#d97706; --warning-soft:#fef3c7; --danger:#dc2626;  --danger-soft:#fee2e2;
    --purple:#7c3aed;
    --radius:14px; --shadow:0 1px 2px rgba(15,23,42,.06),0 4px 12px rgba(15,23,42,.04);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1360px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  h1{font-size:22px;margin:0;font-weight:600} h2{font-size:16px;margin:0 0 14px;font-weight:600} .muted{color:var(--muted);font-size:13px}
  
  .topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
  .user-badge{display:flex;align-items:center;gap:10px;padding:6px 12px;background:#fff;border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow)}
  .user-badge .avatar{width:34px;height:34px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;background:#0891b2}
  .user-badge .info{display:flex;flex-direction:column;line-height:1.25} .user-badge .name{font-weight:600;font-size:13px} .user-badge .role{font-size:11px;color:var(--muted)}
  .logout-btn{padding:6px 10px;border:1px solid var(--border);background:#fff;border-radius:8px;font:inherit;font-size:12px;cursor:pointer;color:var(--muted)}
  .logout-btn:hover{background:var(--danger-soft);color:var(--danger);border-color:#fecaca}
  
  .tabs{display:flex;gap:4px;background:var(--card);padding:4px;border-radius:12px;box-shadow:var(--shadow);overflow-x:auto}
  .tab{padding:10px 18px;border-radius:9px;border:none;background:transparent;cursor:pointer;font:inherit;font-weight:500;color:var(--muted);white-space:nowrap;transition:.15s;display:flex;align-items:center;gap:6px}
  .tab.active{background:var(--primary);color:#fff}
  
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow)}
  
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap}
  .badge.success{background:#dcfce7;color:#15803d} .badge.warn{background:#fef3c7;color:#b45309} .badge.danger{background:#fee2e2;color:#b91c1c} .badge.info{background:#dbeafe;color:#1d4ed8} .badge.gray{background:#f1f5f9;color:#64748b} .badge.purple{background:#ede9fe;color:#6d28d9}
  
  .card{border-radius:12px;padding:16px;border:1px solid var(--border);background:#fff}
  .card.success{background:#f0fdf4;border-color:#bbf7d0} .card.warn{background:#fffbeb;border-color:#fde68a} .card.danger{background:#fef2f2;border-color:#fecaca} .card.info{background:#eff6ff;border-color:#bfdbfe}
  .flex-between{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
  
  .btn-primary{padding:9px 16px;background:var(--primary);color:#fff;border:none;border-radius:10px;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s}
  .btn-primary:hover{background:#1d4ed8} .btn-secondary{padding:9px 16px;background:#fff;color:var(--text);border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;cursor:pointer}
  .btn-sm{padding:6px 12px;font-size:12px;border-radius:8px}
  
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:11px 12px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle}
  th{font-weight:600;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.3px;background:#f8fafc;white-space:nowrap}
  tbody tr:last-child td{border-bottom:none} tbody tr:hover{background:#f8fafc} .empty{text-align:center;padding:40px;color:var(--muted)}
  
  .form-group{margin-bottom:14px} .form-group > label{display:block;font-weight:500;margin-bottom:6px;font-size:13px}
  .form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;outline:none}
  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:flex-start;justify-content:center;z-index:1000;padding:20px}
  .modal-overlay.show{display:flex} .modal-card{background:#fff;border-radius:20px;max-width:500px;width:100%;display:flex;flex-direction:column;margin-top:50px}
  .modal-head{display:flex;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--border)} .modal-head h2{margin:0;font-size:18px}
  .modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted)}
  .modal-body{padding:20px 24px} .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}
  
  .login-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;z-index:1100;padding:20px}
  .login-overlay.show{display:flex} .login-card{background:#fff;border-radius:20px;padding:32px;max-width:640px;width:100%}
  .login-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:20px}
  .login-option{display:flex;gap:14px;align-items:center;padding:14px;border:1px solid var(--border);border-radius:14px;cursor:pointer;background:#fff;text-align:left;transition:.15s}
  .login-option:hover{border-color:var(--primary);background:#eff6ff}
  .login-option .avatar{width:44px;height:44px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;background:#0891b2}
  
  .toast{position:fixed;bottom:24px;right:24px;padding:12px 18px;background:#0f172a;color:#fff;border-radius:10px;font-size:13px;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none;z-index:1200} .toast.show{opacity:1;transform:translateY(0)}
  .cat-hero{padding:24px;border-radius:14px;margin-bottom:16px;display:flex;gap:20px;align-items:flex-start}
  .cat-hero.success{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #bbf7d0}
  .cat-hero.warn{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a}
  .cat-hero.danger{background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1px solid #fecaca}
  .cat-hero .icon{font-size:32px;flex-shrink:0}
  .cat-hero .body{flex:1}
  .cat-hero h3{margin:0 0 4px;font-size:16px}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .info-item{background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)}
  .info-item .lbl{font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:4px}
  .info-item .val{font-size:14px}
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div><h1>Личный кабинет судьи</h1><div class="muted">Федерация спортивного программирования</div></div>
    <div class="user-badge" id="userBadge" style="display:none"></div>
  </div>
  
  <nav class="tabs" id="tabs"></nav>
  
  <div class="panel" id="panelProfile" style="display:none"></div>
  <div class="panel" id="panelCategory" style="display:none"></div>
  <div class="panel" id="panelApplications" style="display:none"></div>
  <div class="panel" id="panelAssignments" style="display:none"></div>
  <div class="panel" id="panelHistory" style="display:none"></div>
</div>

<div class="login-overlay" id="loginOverlay">
  <div class="login-card">
    <h2>Вход в кабинет</h2><div class="sub">Выберите судью</div>
    <div class="login-grid" id="loginGrid"></div>
  </div>
</div>

<div class="modal-overlay" id="appModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Заявка на судейскую категорию</h2><button class="modal-close" data-close="appModal">✕</button></div>
    <div class="modal-body" id="appModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="appModal">Отмена</button><button class="btn-primary" id="appSaveBtn">Подать заявку</button></div>
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
    { id:1, name:'Иванов Иван Иванович', birthDate:'2008-05-15', muni:'г. Оренбург', org:'Губернаторский лицей', disciplines:['Алгоритмическое программирование'], yearStart:2018, coaches:['Петров А.С.'], rank:'1 разряд', rankExpires:'2028-09-01', antidoping:{passed:true}, legalRep:null },
    { id:2, name:'Петрова Анна Сергеевна', birthDate:'2010-03-20', muni:'г. Оренбург', org:'Лицей №1', disciplines:['Алгоритмическое программирование'], yearStart:2019, coaches:['Смирнов В.В.'], rank:'КМС', rankExpires:'2029-01-01', antidoping:{passed:true}, legalRep:{name:'Петрова М.И.', consentDate:'2025-09-01'} },
    { id:3, name:'Сидоров Олег Павлович', birthDate:'2012-08-10', muni:'г. Орск', org:'Гимназия №1', disciplines:['Спортивная робототехника'], yearStart:2020, coaches:['Петров А.С.'], rank:'2 разряд', rankExpires:'2026-12-01', antidoping:{passed:false}, legalRep:{name:'Сидорова Е.П.', consentDate:'2025-10-10'} },
    { id:4, name:'Кузнецова Мария Ильинична', birthDate:'2009-11-05', muni:'г. Бузулук', org:'IT-куб', disciplines:['Искусственный интеллект'], yearStart:2017, coaches:['Смирнов В.В.'], rank:'1 юношеский', rankExpires:'2026-11-25', antidoping:{passed:true}, legalRep:{name:'Кузнецов И.А.', consentDate:'2024-11-05'} },
    { id:5, name:'Морозов Дмитрий Андреевич', birthDate:'2015-06-01', muni:'г. Новотроицк', org:'Кванториум', disciplines:['Спортивная робототехника'], yearStart:2022, coaches:[], rank:'3 юношеский', rankExpires:'2028-11-15', antidoping:{passed:false}, legalRep:{name:'Морозова О.С.', consentDate:'2025-06-01'} }
  ],
  competitions: [
    { id:1, name:'Областной этап ВсОШ', discipline:'Алгоритмическое программирование', startDate:'2026-11-05', endDate:'2026-11-07', ageGroups:['15-16 лет','17-18 лет'], deadline:'2026-11-03', status:'applications_open' },
    { id:2, name:'Кубок области', discipline:'Алгоритмическое программирование', startDate:'2026-11-21', endDate:'2026-11-21', ageGroups:['13-14 лет','15-16 лет','17-18 лет','19+'], deadline:'2026-11-15', status:'applications_open' },
    { id:3, name:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', startDate:'2025-09-15', endDate:'2025-09-15', ageGroups:['17-18 лет','19+'], deadline:'2025-09-01', status:'completed' },
    { id:4, name:'Турнир по ИБ', discipline:'Информационная безопасность', startDate:'2025-06-10', endDate:'2025-06-10', ageGroups:['15-16 лет','17-18 лет','19+'], deadline:'2025-06-01', status:'completed' }
  ],
  applications: [], 
  rankApplications: [], 
  results: [ { id:1, athleteId:1, competitionId:3, competitionName:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', date:'2025-09-15', place:2, score:8, status:'official' } ],
  reports: [], 
  coachLinks: [
    { id:1, coachId:1, athleteId:1, discipline:'Алгоритмическое программирование', status:'active', date:'2024-01-10' },
    { id:3, coachId:2, athleteId:2, discipline:'Алгоритмическое программирование', status:'active', date:'2024-03-01' }
  ],
  judges: [
    { id:1, name:'Смирнов Алексей Алексеевич', category:'Всероссийская', assignedDate:'2023-11-15', expiresDate:'2026-11-15' },
    { id:2, name:'Волкова Марина Игоревна', category:'Первая', assignedDate:'2022-05-10', expiresDate:'2025-05-10' },
    { id:3, name:'Орлова Елена Николаевна', category:'Вторая', assignedDate:'2024-01-20', expiresDate:'2027-01-20' }
  ],
  judgeApplications: [
    { id:1, judgeId:2, type:'confirm', targetCategory:'Первая', submittedDate:'2025-04-01', status:'pending', comment:'Прошу подтвердить категорию' }
  ],
  judgeAssignments: [
    { id:1, judgeId:1, competitionId:1, position:'Главный судья' },
    { id:2, judgeId:1, competitionId:2, position:'Член жюри' },
    { id:3, judgeId:3, competitionId:1, position:'Секретарь' },
    { id:4, judgeId:1, competitionId:3, position:'Главный судья' },
    { id:5, judgeId:2, competitionId:4, position:'Судья' }
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
  { id:'judge-1', name:'Смирнов Алексей Алексеевич', short:'СА', role:'judge', roleLabel:'Судья', judgeId:1, email:'smirnov_judge@example.com' },
  { id:'judge-2', name:'Волкова Марина Игоревна', short:'ВМ', role:'judge', roleLabel:'Судья', judgeId:2, email:'volkova@example.com' },
  { id:'judge-3', name:'Орлова Елена Николаевна', short:'ОЕ', role:'judge', roleLabel:'Судья', judgeId:3, email:'orlova@example.com' }
];

// блок-помощник

function getAllUsers() {
  return [
    ...DEMO_USERS,
    ...getRegisteredByRole('judge').map(r => makeRegUser(r, 'judge'))
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

function appStatusBadge(s) { return {'pending':'<span class="badge warn">На рассмотрении</span>','approved':'<span class="badge success">Одобрена</span>','rejected':'<span class="badge danger">Отклонена</span>'}[s] || s; }
function toast(msg) { const el = document.getElementById('toast'); el.textContent = msg; el.classList.add('show'); setTimeout(() => el.classList.remove('show'), 2500); }

const STATE = { user: null, tab: 'profile' };
const currentJudge = () => {
  const db = getDB();
  if (STATE.user?.isRegistered) {
    let j = db.judges.find(x => x.id === STATE.user.judgeId);
    if (!j) j = ensureJudgeRecord(STATE.user);
    return j;
  }
  return db.judges.find(j => j.id === STATE.user?.judgeId);
};

/* ================= РЕНДЕР ================= */
function renderUserBadge() {
  const el = document.getElementById('userBadge');
  if (!STATE.user) { el.style.display = 'none'; return; }
  el.style.display = 'flex';
  el.innerHTML = `<div class="avatar">${STATE.user.short}</div><div class="info"><div class="name">${STATE.user.name}</div><div class="role">Судья</div></div><button class="logout-btn" id="logoutBtn">Выйти</button>`;
  document.getElementById('logoutBtn').addEventListener('click', () => { STATE.user = null; sessionClear(); document.getElementById('loginOverlay').classList.add('show'); buildLoginGrid(); renderAll(); });
}

function renderTabs() {
  const tabs = [
    { key:'profile', label:'👤 Профиль' },
    { key:'category', label:'⚖️ Моя категория' },
    { key:'applications', label:'📝 Заявки на категорию' },
    { key:'assignments', label:'📌 Назначения' },
    { key:'history', label:'📜 Мои соревнования' }
  ];
  document.getElementById('tabs').innerHTML = tabs.map(t => `<button class="tab ${STATE.tab === t.key ? 'active' : ''}" data-tab="${t.key}">${t.label}</button>`).join('');
}

function renderProfile() {
  const panel = document.getElementById('panelProfile');
  panel.style.display = STATE.tab === 'profile' ? '' : 'none'; if (STATE.tab !== 'profile') return;
  panel.innerHTML = `
    <h2>Профиль судьи</h2>
    <div class="card info">
      <div class="info-grid">
        <div class="info-item"><div class="lbl">ФИО</div><div class="val"><b>${STATE.user.name}</b></div></div>
        <div class="info-item"><div class="lbl">Роль</div><div class="val">${STATE.user.roleLabel}</div></div>
        <div class="info-item full" style="grid-column: 1 / -1"><div class="lbl">Email для уведомлений</div><div class="val"><b>${STATE.user.email}</b></div></div>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-top:12px">На этот адрес будут приходить уведомления о назначениях на соревнования, смене статуса категории и напоминания.</div>
    </div>
  `;
}

function renderCategory() {
  const panel = document.getElementById('panelCategory');
  panel.style.display = STATE.tab === 'category' ? '' : 'none'; if (STATE.tab !== 'category') return;
  const j = currentJudge(); if (!j) return;
  // Судья, только что зарегистрировавшийся, ещё не имеет категории
if (!j.expiresDate) {
  panel.innerHTML = `
    <h2>Судейская категория</h2>
    <div class="cat-hero warn">
      <div class="icon">📝</div>
      <div class="body">
        <h3>Категория ещё не присвоена</h3>
        <div class="muted" style="color:#475569;margin-bottom:8px">
          Подайте заявку на присвоение судейской категории — федерация её рассмотрит.
        </div>
        <button class="btn-primary btn-sm"
                onclick="document.querySelector('[data-tab=\\'applications\\']').click()">
          Перейти к заявкам
        </button>
      </div>
    </div>`;
  return;
}
  
  const daysLeft = daysBetween(iso(today()), j.expiresDate);
  const isExpired = daysLeft < 0;
  const isExpiring = daysLeft >= 0 && daysLeft <= 60;
  
  let heroClass = 'success';
  let icon = '✅';
  let statusText = 'Категория действует';
  let subText = `До окончания срока осталось ${daysLeft} ${daysWord(daysLeft)}.`;
  
  if (isExpired) {
    heroClass = 'danger'; icon = '⚠️';
    statusText = 'Категория просрочена!';
    subText = `Срок действия истёк ${-daysLeft} ${daysWord(daysLeft)} назад. Необходимо срочно подать заявку на подтверждение.`;
  } else if (isExpiring) {
    heroClass = 'warn'; icon = '⏰';
    statusText = 'Категория истекает';
    subText = `До окончания срока осталось ${daysLeft} ${daysWord(daysLeft)}. Рекомендуем подать заявку на подтверждение.`;
  }

  panel.innerHTML = `
    <h2>Судейская категория</h2>
    <div class="cat-hero ${heroClass}">
      <div class="icon">${icon}</div>
      <div class="body">
        <h3>${statusText}</h3>
        <div class="muted" style="color:#475569;margin-bottom:8px">${subText}</div>
        ${isExpired || isExpiring ? `<button class="btn-primary btn-sm" onclick="document.querySelector('[data-tab=\\'applications\\']').click()">Перейти к заявкам</button>` : ''}
      </div>
    </div>
    
    <div class="info-grid">
      <div class="card info">
        <div class="lbl" style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:4px">Текущая категория</div>
        <div style="font-size:20px;font-weight:700;color:#1d4ed8">${j.category}</div>
      </div>
      <div class="card info">
        <div class="lbl" style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:4px">Действует до</div>
        <div style="font-size:20px;font-weight:700;color:#1d4ed8">${fmtDate(j.expiresDate)}</div>
      </div>
    </div>
  `;
}

function renderApplications() {
  const panel = document.getElementById('panelApplications');
  panel.style.display = STATE.tab === 'applications' ? '' : 'none'; if (STATE.tab !== 'applications') return;
  const db = getDB(); const j = currentJudge(); if (!j) return;
  
  const myApps = db.judgeApplications.filter(a => a.judgeId === j.id);
  const hasPending = myApps.some(a => a.status === 'pending');

  panel.innerHTML = `
    <div class="flex-between" style="margin-bottom:16px">
      <h2>Заявки на судейскую категорию</h2>
      ${!hasPending ? `<button class="btn-primary" onclick="openAppModal()">+ Подать заявку</button>` : `<span class="badge warn">У вас есть заявка на рассмотрении</span>`}
    </div>
    
    <table>
      <thead><tr><th>Дата подачи</th><th>Тип</th><th>Запрашиваемая категория</th><th>Комментарий</th><th>Статус</th></tr></thead>
      <tbody>
        ${myApps.length ? myApps.map(a => `<tr>
          <td>${fmtDate(a.submittedDate)}</td>
          <td>${a.type === 'assign' ? 'Присвоение' : 'Подтверждение'}</td>
          <td><span class="badge info">${a.targetCategory}</span></td>
          <td style="color:var(--muted);font-size:12px">${a.comment || '—'}</td>
          <td>${appStatusBadge(a.status)}</td>
        </tr>`).join('') : `<tr><td colspan="5" class="empty">Заявок пока нет</td></tr>`}
      </tbody>
    </table>
  `;
}

function renderAssignments() {
  const panel = document.getElementById('panelAssignments');
  panel.style.display = STATE.tab === 'assignments' ? '' : 'none'; if (STATE.tab !== 'assignments') return;
  const db = getDB(); const j = currentJudge(); if (!j) return;
  
  const todayStr = iso(today());
  const assignments = db.judgeAssignments
    .filter(a => a.judgeId === j.id)
    .map(a => ({ ...a, comp: db.competitions.find(c => c.id === a.competitionId) }))
    .filter(a => a.comp && a.comp.endDate >= todayStr)
    .sort((a,b) => a.comp.startDate.localeCompare(b.comp.startDate));

  panel.innerHTML = `
    <h2>Предстоящие назначения</h2>
    <table>
      <thead><tr><th>Даты</th><th>Соревнование</th><th>Дисциплина</th><th>Ваша должность</th></tr></thead>
      <tbody>
        ${assignments.length ? assignments.map(a => `<tr>
          <td><b>${fmtDate(a.comp.startDate)}</b></td>
          <td>${a.comp.name}</td>
          <td>${a.comp.discipline}</td>
          <td><span class="badge purple">${a.position}</span></td>
        </tr>`).join('') : `<tr><td colspan="4" class="empty">Нет предстоящих назначений</td></tr>`}
      </tbody>
    </table>
  `;
}

function renderHistory() {
  const panel = document.getElementById('panelHistory');
  panel.style.display = STATE.tab === 'history' ? '' : 'none'; if (STATE.tab !== 'history') return;
  const db = getDB(); const j = currentJudge(); if (!j) return;
  
  const todayStr = iso(today());
  const history = db.judgeAssignments
    .filter(a => a.judgeId === j.id)
    .map(a => ({ ...a, comp: db.competitions.find(c => c.id === a.competitionId) }))
    .filter(a => a.comp && a.comp.endDate < todayStr)
    .sort((a,b) => b.comp.startDate.localeCompare(a.comp.startDate));

  panel.innerHTML = `
    <h2>История соревнований</h2>
    <table>
      <thead><tr><th>Даты</th><th>Соревнование</th><th>Дисциплина</th><th>Ваша должность</th></tr></thead>
      <tbody>
        ${history.length ? history.map(a => `<tr>
          <td><b>${fmtDate(a.comp.startDate)}</b></td>
          <td>${a.comp.name}</td>
          <td>${a.comp.discipline}</td>
          <td><span class="badge gray">${a.position}</span></td>
        </tr>`).join('') : `<tr><td colspan="4" class="empty">История пуста</td></tr>`}
      </tbody>
    </table>
  `;
}

function renderAll() {
  renderUserBadge();
  if (!STATE.user) { document.getElementById('tabs').innerHTML = ''; ['panelProfile','panelCategory','panelApplications','panelAssignments','panelHistory'].forEach(id => document.getElementById(id).style.display = 'none'); return; }
  renderTabs(); 
  renderProfile();
  renderCategory(); 
  renderApplications(); 
  renderAssignments(); 
  renderHistory();
}

/* ================= ДЕЙСТВИЯ ================= */
function openAppModal() {
  const j = currentJudge();
  const nextCat = j.category === 'Третья' ? 'Вторая' : j.category === 'Вторая' ? 'Первая' : j.category === 'Первая' ? 'Всероссийская' : null;
  
  document.getElementById('appModalBody').innerHTML = `
    <div class="form-group">
      <label>Тип заявки</label>
      <select id="app-type" onchange="toggleCategorySelect()">
        <option value="confirm">Подтверждение текущей (${j.category})</option>
        ${nextCat ? `<option value="assign">Присвоение следующей (${nextCat})</option>` : ''}
      </select>
    </div>
    <div class="form-group">
      <label>Категория</label>
      <select id="app-target">
        <option value="${j.category}" selected>${j.category}</option>
        ${nextCat ? `<option value="${nextCat}">${nextCat}</option>` : ''}
      </select>
    </div>
    <div class="form-group">
      <label>Комментарий</label>
      <textarea id="app-comment" rows="2" placeholder="Например: прошу подтвердить категорию"></textarea>
    </div>
  `;
  document.getElementById('appModal').classList.add('show');
}

function toggleCategorySelect() {
  const type = document.getElementById('app-type').value;
  const sel = document.getElementById('app-target');
  if (type === 'confirm') sel.selectedIndex = 0;
  else sel.selectedIndex = 1;
}

document.getElementById('appSaveBtn').onclick = () => {
  const type = document.getElementById('app-type').value;
  const target = document.getElementById('app-target').value;
  const comment = document.getElementById('app-comment').value;
  
  const db = getDB(); const j = currentJudge();
  db.judgeApplications.push({
    id: Date.now(), judgeId: j.id, type, targetCategory: target, submittedDate: iso(today()), status: 'pending', comment
  });
  saveDB(db); closeModal('appModal'); toast('Заявка отправлена в федерацию'); renderApplications();
};

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
          Судья${u.isRegistered ? ' · зарегистрирован' : ''}
        </div>
      </div>
    </button>`).join('');
}

function loginAs(id) {
  const user = getAllUsers().find(u => u.id === id);
  if (!user) return;
  if (user.isRegistered) ensureJudgeRecord(user);
  STATE.user = user;
  sessionSave(user);
  STATE.tab = 'profile';
  document.getElementById('loginOverlay').classList.remove('show');
  renderAll();
}

buildLoginGrid();
const saved = sessionLoad();
if (saved && saved.role === 'judge') { STATE.user = saved; renderAll(); } 
else { document.getElementById('loginOverlay').classList.add('show'); renderAll(); }

function ensureJudgeRecord(user) {
  const db = getDB();
  let j = db.judges.find(x => x.email && x.email.toLowerCase() === user.email);
  if (!j) {
    j = {
      id: Math.max(0, ...db.judges.map(x => x.id)) + 1,
      name: user.name,
      email: user.email,
      category: 'Не присвоена',
      assignedDate: null,
      expiresDate: null
    };
    db.judges.push(j);
    saveDB(db);
  }
  user.judgeId = j.id;
  return j;
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