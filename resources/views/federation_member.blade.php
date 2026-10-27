<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Личный кабинет члена Федерации — спортивное программирование</title>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --primary-soft:#dbeafe; --success:#16a34a; --success-soft:#dcfce7;
    --warning:#d97706; --warning-soft:#fef3c7; --danger:#dc2626;  --danger-soft:#fee2e2;
    --radius:14px; --shadow:0 1px 2px rgba(15,23,42,.06),0 4px 12px rgba(15,23,42,.04);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);
    font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    font-size:14px;line-height:1.5}
  .wrap{max-width:1360px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  h1{font-size:22px;margin:0;font-weight:600} h2{font-size:16px;margin:0 0 14px;font-weight:600}
  .muted{color:var(--muted);font-size:13px}
  .topbar{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
  .user-badge{display:flex;align-items:center;gap:10px;padding:6px 12px;background:#fff;
    border:1px solid var(--border);border-radius:12px;box-shadow:var(--shadow)}
  .user-badge .avatar{width:34px;height:34px;border-radius:50%;color:#fff;display:flex;
    align-items:center;justify-content:center;font-weight:600;font-size:13px;background:#7c3aed}
  .user-badge .info{display:flex;flex-direction:column;line-height:1.25}
  .user-badge .name{font-weight:600;font-size:13px}
  .user-badge .role{font-size:11px;color:var(--muted)}
  .logout-btn{padding:6px 10px;border:1px solid var(--border);background:#fff;border-radius:8px;
    font:inherit;font-size:12px;cursor:pointer;color:var(--muted)}
  .logout-btn:hover{background:var(--danger-soft);color:var(--danger);border-color:#fecaca}
  .tabs{display:flex;gap:4px;background:var(--card);padding:4px;border-radius:12px;
    box-shadow:var(--shadow);overflow-x:auto}
  .tab{padding:10px 18px;border-radius:9px;border:none;background:transparent;cursor:pointer;
    font:inherit;font-weight:500;color:var(--muted);white-space:nowrap;transition:.15s;
    display:flex;align-items:center;gap:6px}
  .tab.active{background:var(--primary);color:#fff}
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow)}
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;
    font-weight:600;white-space:nowrap}
  .badge.success{background:#dcfce7;color:#15803d} .badge.warn{background:#fef3c7;color:#b45309}
  .badge.danger{background:#fee2e2;color:#b91c1c} .badge.info{background:#dbeafe;color:#1d4ed8}
  .badge.gray{background:#f1f5f9;color:#64748b} .badge.purple{background:#ede9fe;color:#6d28d9}
  .card{border-radius:12px;padding:16px;border:1px solid var(--border);background:#fff}
  .card.info{background:#eff6ff;border-color:#bfdbfe}
  .flex-between{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:11px 12px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle}
  th{font-weight:600;color:var(--muted);font-size:12px;text-transform:uppercase;
    letter-spacing:.3px;background:#f8fafc;white-space:nowrap}
  tbody tr:last-child td{border-bottom:none} tbody tr:hover{background:#f8fafc}
  .empty{text-align:center;padding:40px;color:var(--muted)}
  .btn-primary{padding:9px 16px;background:var(--primary);color:#fff;border:none;border-radius:10px;
    font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s}
  .btn-primary:hover{background:#1d4ed8}
  .btn-secondary{padding:9px 16px;background:#fff;color:var(--text);border:1px solid var(--border);
    border-radius:10px;font:inherit;font-size:13px;cursor:pointer}
  .btn-sm{padding:6px 12px;font-size:12px;border-radius:8px}
  .btn-success{background:#16a34a;color:#fff;border:none}
  .btn-success:hover{background:#15803d}
  .btn-danger{background:#dc2626;color:#fff;border:none}
  .btn-danger:hover{background:#b91c1c}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .info-item{background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)}
  .info-item .lbl{font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:4px}
  .info-item .val{font-size:14px}
  .login-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(4px);
    display:none;align-items:center;justify-content:center;z-index:1100;padding:20px}
  .login-overlay.show{display:flex}
  .login-card{background:#fff;border-radius:20px;padding:32px;max-width:640px;width:100%}
  .login-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:20px}
  .login-option{display:flex;gap:14px;align-items:center;padding:14px;border:1px solid var(--border);
    border-radius:14px;cursor:pointer;background:#fff;text-align:left;transition:.15s}
  .login-option:hover{border-color:var(--primary);background:#eff6ff}
  .login-option .avatar{width:44px;height:44px;border-radius:50%;color:#fff;display:flex;
    align-items:center;justify-content:center;font-weight:700;background:#7c3aed}
  .toast{position:fixed;bottom:24px;right:24px;padding:12px 18px;background:#0f172a;color:#fff;
    border-radius:10px;font-size:13px;opacity:0;transform:translateY(10px);
    transition:.2s;pointer-events:none;z-index:1200}
  .toast.show{opacity:1;transform:translateY(0)}
  .stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px}
  .stat{background:#f8fafc;border:1px solid var(--border);border-radius:12px;padding:16px}
  .stat .num{font-size:24px;font-weight:700;color:var(--primary)}
  .stat .lbl{font-size:12px;color:var(--muted);margin-top:2px}
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div><h1>Личный кабинет члена Федерации</h1>
         <div class="muted">Федерация спортивного программирования</div></div>
    <div class="user-badge" id="userBadge" style="display:none"></div>
  </div>
  <nav class="tabs" id="tabs"></nav>
  <div class="panel" id="panelProfile"     style="display:none"></div>
  <div class="panel" id="panelStats"       style="display:none"></div>
  <div class="panel" id="panelApplications" style="display:none"></div>
  <div class="panel" id="panelRanks"       style="display:none"></div>
  <div class="panel" id="panelJudgeApps"   style="display:none"></div>
  <div class="panel" id="panelCompetitions" style="display:none"></div>
</div>

<div class="login-overlay" id="loginOverlay">
  <div class="login-card">
    <h2>Вход в кабинет</h2>
    <div class="muted" style="margin-bottom:8px">Выберите члена Федерации</div>
    <div class="login-grid" id="loginGrid"></div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
/* ================= БАЗА ДАННЫХ ================= */
const DB_VERSION = '4.1';
const DB_KEY = 'fsp_database';
const SESSION_KEY = 'fsp_user';

const INITIAL_DB = {
  athletes: [
    { id:1, name:'Иванов Иван Иванович', email:'ivanov@example.com', birthDate:'2008-05-15', muni:'г. Оренбург', org:'Губернаторский лицей', disciplines:['Алгоритмическое программирование'], coaches:['Петров А.С.'], rank:'1 разряд', rankExpires:'2028-09-01', antidoping:{passed:true}, legalRep:null },
    { id:2, name:'Петрова Анна Сергеевна', email:'petrova@example.com', birthDate:'2010-03-20', muni:'г. Оренбург', org:'Лицей №1', disciplines:['Алгоритмическое программирование'], coaches:['Смирнов В.В.'], rank:'КМС', rankExpires:'2029-01-01', antidoping:{passed:true}, legalRep:null },
    { id:3, name:'Сидоров Олег Павлович', email:'sidorov@example.com', birthDate:'2012-08-10', muni:'г. Орск', org:'Гимназия №1', disciplines:['Спортивная робототехника'], coaches:['Петров А.С.'], rank:'2 разряд', rankExpires:'2026-12-01', antidoping:{passed:false}, legalRep:null }
  ],
  competitions: [
    { id:1, name:'Областной этап ВсОШ', discipline:'Алгоритмическое программирование', startDate:'2026-11-05', endDate:'2026-11-07', ageGroups:['15-16 лет','17-18 лет'], deadline:'2026-11-03', status:'applications_open' },
    { id:2, name:'Кубок области', discipline:'Алгоритмическое программирование', startDate:'2026-11-21', endDate:'2026-11-21', ageGroups:['13-14 лет','15-16 лет','17-18 лет','19+'], deadline:'2026-11-15', status:'applications_open' },
    { id:3, name:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', startDate:'2025-09-15', endDate:'2025-09-15', ageGroups:['17-18 лет','19+'], deadline:'2025-09-01', status:'completed' }
  ],
  applications: [],
  rankApplications: [],
  results: [],
  coachLinks: [
    { id:1, coachId:1, athleteId:1, discipline:'Алгоритмическое программирование', status:'active', date:'2024-01-10' },
    { id:3, coachId:2, athleteId:2, discipline:'Алгоритмическое программирование', status:'active', date:'2024-03-01' }
  ],
  judges: [
    { id:1, name:'Смирнов Алексей Алексеевич', email:'smirnov_judge@example.com', category:'Всероссийская', assignedDate:'2023-11-15', expiresDate:'2026-11-15' },
    { id:2, name:'Волкова Марина Игоревна', email:'volkova@example.com', category:'Первая', assignedDate:'2022-05-10', expiresDate:'2025-05-10' }
  ],
  judgeApplications: [
    { id:1, judgeId:2, type:'confirm', targetCategory:'Первая', submittedDate:'2025-04-01', status:'pending', comment:'Прошу подтвердить категорию' }
  ],
  judgeAssignments: []
};

function getDB() {
  const v = localStorage.getItem('fsp_db_version');
  const db = localStorage.getItem(DB_KEY);
  if (!db || v !== DB_VERSION) {
    localStorage.setItem('fsp_db_version', DB_VERSION);
    localStorage.setItem(DB_KEY, JSON.stringify(INITIAL_DB));
    return INITIAL_DB;
  }
  return JSON.parse(db);
}
function saveDB(db) { localStorage.setItem(DB_KEY, JSON.stringify(db)); }

/* ====== ИНТЕГРАЦИЯ С РЕГИСТРАЦИЕЙ (index_reg) ====== */
const REG_KEY = 'fsp_registered_users';
const ROLE_LABELS = {
  athlete:'Спортсмен', coach:'Тренер', judge:'Судья', federation:'Член Федерации'
};
function getRegisteredByRole(role) {
  try {
    const arr = JSON.parse(localStorage.getItem(REG_KEY) || '[]');
    return arr.filter(u => Array.isArray(u.roles) && u.roles.includes(role));
  } catch(e) { return []; }
}
function makeRegUser(reg, role) {
  const short = (((reg.firstName||'')[0]||'') + ((reg.lastName||'')[0]||'')).toUpperCase() || '??';
  return {
    id: 'reg-' + reg.id + '-' + role,
    regId: reg.id,
    name: [reg.lastName, reg.firstName, reg.patronymic].filter(Boolean).join(' ') || reg.email,
    short, role, roleLabel: ROLE_LABELS[role] || role,
    email: reg.email, isRegistered: true
  };
}

const DEMO_USERS = [
  { id:'fed-1', name:'Фёдорова Ольга Петровна', short:'ФО', role:'federation', roleLabel:'Член Федерации', email:'fed@example.com' }
];

function getAllUsers() {
  return [
    ...DEMO_USERS,
    ...getRegisteredByRole('federation').map(r => makeRegUser(r, 'federation'))
  ];
}

const sessionSave = (u) => localStorage.setItem(SESSION_KEY, JSON.stringify({ id: u.id }));
const sessionLoad = () => {
  try {
    const r = JSON.parse(localStorage.getItem(SESSION_KEY));
    return r ? getAllUsers().find(u => u.id === r.id) || null : null;
  } catch(e) { return null; }
};
const sessionClear = () => localStorage.removeItem(SESSION_KEY);

/* ================= УТИЛИТЫ ================= */
const pad = n => String(n).padStart(2,'0');
const fmtDate = s => { if (!s) return '—'; const [y,m,d] = s.split('-'); return `${d}.${m}.${y}`; };
function appStatusBadge(s) {
  return {'pending':'<span class="badge warn">На рассмотрении</span>',
          'approved':'<span class="badge success">Одобрена</span>',
          'rejected':'<span class="badge danger">Отклонена</span>'}[s] || s;
}
function toast(msg){ const el=document.getElementById('toast'); el.textContent=msg;
  el.classList.add('show'); setTimeout(()=>el.classList.remove('show'),2500); }

const STATE = { user:null, tab:'profile' };

/* ================= РЕНДЕР ================= */
function renderUserBadge() {
  const el = document.getElementById('userBadge');
  if (!STATE.user) { el.style.display='none'; return; }
  el.style.display='flex';
  el.innerHTML = `<div class="avatar">${STATE.user.short}</div>
    <div class="info"><div class="name">${STATE.user.name}</div>
    <div class="role">${STATE.user.roleLabel}</div></div>
    <button class="logout-btn" id="logoutBtn">Выйти</button>`;
  document.getElementById('logoutBtn').onclick = () => {
    STATE.user = null; sessionClear();
    document.getElementById('loginOverlay').classList.add('show');
    buildLoginGrid(); renderAll();
  };
}

function renderTabs() {
  const db = getDB();
  const pendingA = db.applications.filter(a => a.status === 'pending').length;
  const pendingR = db.rankApplications.filter(a => a.status === 'pending').length;
  const pendingJ = db.judgeApplications.filter(a => a.status === 'pending').length;
  const tabs = [
    { key:'profile',      label:'👤 Профиль' },
    { key:'stats',        label:'📊 Сводка' },
    { key:'applications', label:`📝 Заявки на соревнования${pendingA?' <span class="badge warn">'+pendingA+'</span>':''}` },
    { key:'ranks',        label:`🏅 Разряды${pendingR?' <span class="badge warn">'+pendingR+'</span>':''}` },
    { key:'judgeApps',    label:`⚖️ Категории судей${pendingJ?' <span class="badge warn">'+pendingJ+'</span>':''}` },
    { key:'competitions', label:'📅 Соревнования' }
  ];
  document.getElementById('tabs').innerHTML = tabs.map(t =>
    `<button class="tab ${STATE.tab===t.key?'active':''}" data-tab="${t.key}">${t.label}</button>`).join('');
}

function renderProfile() {
  const p = document.getElementById('panelProfile');
  p.style.display = STATE.tab === 'profile' ? '' : 'none';
  if (STATE.tab !== 'profile') return;
  p.innerHTML = `
    <h2>Профиль члена Федерации</h2>
    <div class="card info">
      <div class="info-grid">
        <div class="info-item"><div class="lbl">ФИО</div><div class="val"><b>${STATE.user.name}</b></div></div>
        <div class="info-item"><div class="lbl">Роль</div><div class="val">${STATE.user.roleLabel}</div></div>
        <div class="info-item full" style="grid-column:1/-1">
          <div class="lbl">Email для уведомлений</div>
          <div class="val"><b>${STATE.user.email || '—'}</b></div>
        </div>
      </div>
    </div>`;
}

function renderStats() {
  const p = document.getElementById('panelStats');
  p.style.display = STATE.tab === 'stats' ? '' : 'none';
  if (STATE.tab !== 'stats') return;
  const db = getDB();
  const stat = (n, l) => `<div class="stat"><div class="num">${n}</div><div class="lbl">${l}</div></div>`;
  p.innerHTML = `
    <h2>Сводка по обращениям</h2>
    <div class="stat-grid">
      ${stat(db.applications.filter(a=>a.status==='pending').length, 'Заявок на соревнования в работе')}
      ${stat(db.rankApplications.filter(a=>a.status==='pending').length, 'Заявок на разряды в работе')}
      ${stat(db.judgeApplications.filter(a=>a.status==='pending').length, 'Заявок на категорию в работе')}
      ${stat(db.athletes.length, 'Спортсменов в системе')}
      ${stat(db.judges.length, 'Судей в системе')}
      ${stat(db.competitions.filter(c=>c.status==='applications_open').length, 'Соревнований с открытым приёмом')}
    </div>`;
}

function renderApplications() {
  const p = document.getElementById('panelApplications');
  p.style.display = STATE.tab === 'applications' ? '' : 'none';
  if (STATE.tab !== 'applications') return;
  const db = getDB();
  p.innerHTML = `
    <h2>Заявки спортсменов на соревнования</h2>
    <table>
      <thead><tr><th>Спортсмен</th><th>Соревнование</th><th>Дата</th><th>Статус</th><th>Действия</th></tr></thead>
      <tbody>
        ${db.applications.length ? db.applications.map(a => {
          const ath = db.athletes.find(x=>x.id===a.athleteId);
          const comp = db.competitions.find(c=>c.id===a.competitionId);
          return `<tr>
            <td>${ath?ath.name:'—'}</td>
            <td>${comp?comp.name:'—'}</td>
            <td>${fmtDate(a.submittedDate)}</td>
            <td>${appStatusBadge(a.status)}</td>
            <td>${a.status==='pending' ? `
              <button class="btn-primary btn-sm btn-success" onclick="setAppStatus(${a.id},'approved')">Одобрить</button>
              <button class="btn-primary btn-sm btn-danger"  onclick="setAppStatus(${a.id},'rejected')">Отклонить</button>` : '—'}
            </td></tr>`;
        }).join('') : '<tr><td colspan="5" class="empty">Заявок нет</td></tr>'}
      </tbody>
    </table>`;
}

function renderRanks() {
  const p = document.getElementById('panelRanks');
  p.style.display = STATE.tab === 'ranks' ? '' : 'none';
  if (STATE.tab !== 'ranks') return;
  const db = getDB();
  p.innerHTML = `
    <h2>Заявки на разряды</h2>
    <table>
      <thead><tr><th>Спортсмен</th><th>Тип</th><th>Разряд</th><th>Основание</th><th>Статус</th><th>Действия</th></tr></thead>
      <tbody>
        ${db.rankApplications.length ? db.rankApplications.map(r => {
          const ath = db.athletes.find(x=>x.id===r.athleteId);
          return `<tr>
            <td>${ath?ath.name:'—'}</td>
            <td>${r.type==='assign'?'Присвоение':'Подтверждение'}</td>
            <td>${r.requestedRank}</td>
            <td>${r.competitionName||'—'}</td>
            <td>${appStatusBadge(r.status)}</td>
            <td>${r.status==='pending' ? `
              <button class="btn-primary btn-sm btn-success" onclick="setRankStatus(${r.id},'approved')">Одобрить</button>
              <button class="btn-primary btn-sm btn-danger"  onclick="setRankStatus(${r.id},'rejected')">Отклонить</button>` : '—'}
            </td></tr>`;
        }).join('') : '<tr><td colspan="6" class="empty">Заявок нет</td></tr>'}
      </tbody>
    </table>`;
}

function renderJudgeApps() {
  const p = document.getElementById('panelJudgeApps');
  p.style.display = STATE.tab === 'judgeApps' ? '' : 'none';
  if (STATE.tab !== 'judgeApps') return;
  const db = getDB();
  p.innerHTML = `
    <h2>Заявки судей на категорию</h2>
    <table>
      <thead><tr><th>Судья</th><th>Тип</th><th>Категория</th><th>Дата</th><th>Статус</th><th>Действия</th></tr></thead>
      <tbody>
        ${db.judgeApplications.length ? db.judgeApplications.map(r => {
          const j = db.judges.find(x=>x.id===r.judgeId);
          return `<tr>
            <td>${j?j.name:'—'}</td>
            <td>${r.type==='assign'?'Присвоение':'Подтверждение'}</td>
            <td>${r.targetCategory}</td>
            <td>${fmtDate(r.submittedDate)}</td>
            <td>${appStatusBadge(r.status)}</td>
            <td>${r.status==='pending' ? `
              <button class="btn-primary btn-sm btn-success" onclick="setJudgeStatus(${r.id},'approved')">Одобрить</button>
              <button class="btn-primary btn-sm btn-danger"  onclick="setJudgeStatus(${r.id},'rejected')">Отклонить</button>` : '—'}
            </td></tr>`;
        }).join('') : '<tr><td colspan="6" class="empty">Заявок нет</td></tr>'}
      </tbody>
    </table>`;
}

function renderCompetitions() {
  const p = document.getElementById('panelCompetitions');
  p.style.display = STATE.tab === 'competitions' ? '' : 'none';
  if (STATE.tab !== 'competitions') return;
  const db = getDB();
  p.innerHTML = `
    <h2>Соревнования</h2>
    <table>
      <thead><tr><th>Название</th><th>Дисциплина</th><th>Даты</th><th>Статус</th></tr></thead>
      <tbody>
        ${db.competitions.map(c => `<tr>
          <td><b>${c.name}</b></td>
          <td>${c.discipline}</td>
          <td>${fmtDate(c.startDate)} — ${fmtDate(c.endDate)}</td>
          <td>${c.status==='applications_open'
              ? '<span class="badge success">Приём заявок</span>'
              : '<span class="badge gray">Завершено</span>'}</td>
        </tr>`).join('')}
      </tbody>
    </table>`;
}

function renderAll() {
  renderUserBadge();
  if (!STATE.user) {
    document.getElementById('tabs').innerHTML = '';
    ['panelProfile','panelStats','panelApplications','panelRanks','panelJudgeApps','panelCompetitions']
      .forEach(id => document.getElementById(id).style.display = 'none');
    return;
  }
  renderTabs();
  renderProfile(); renderStats(); renderApplications();
  renderRanks(); renderJudgeApps(); renderCompetitions();
}

/* ================= ДЕЙСТВИЯ ================= */
function setAppStatus(id, status) {
  const db = getDB(); const a = db.applications.find(x => x.id === id);
  if (a) { a.status = status; saveDB(db); toast('Статус обновлён'); renderAll(); }
}
function setRankStatus(id, status) {
  const db = getDB(); const r = db.rankApplications.find(x => x.id === id);
  if (r) { r.status = status; saveDB(db); toast('Статус обновлён'); renderAll(); }
}
function setJudgeStatus(id, status) {
  const db = getDB(); const r = db.judgeApplications.find(x => x.id === id);
  if (r) {
    r.status = status;
    if (status === 'approved') {
      const j = db.judges.find(x => x.id === r.judgeId);
      if (j && r.type === 'assign') { j.category = r.targetCategory; }
      if (j && r.type === 'confirm') {
        const d = new Date(); d.setFullYear(d.getFullYear() + 4);
        j.expiresDate = d.toISOString().slice(0,10);
      }
    }
    saveDB(db); toast('Статус обновлён'); renderAll();
  }
}

/* ================= АВТОРИЗАЦИЯ ================= */
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
  STATE.user = user; sessionSave(user); STATE.tab = 'profile';
  document.getElementById('loginOverlay').classList.remove('show');
  renderAll();
}

document.getElementById('tabs').addEventListener('click', e => {
  const btn = e.target.closest('.tab');
  if (btn) { STATE.tab = btn.dataset.tab; renderAll(); }
});

buildLoginGrid();
const saved = sessionLoad();
if (saved && saved.role === 'federation') { STATE.user = saved; renderAll(); }
else { document.getElementById('loginOverlay').classList.add('show'); renderAll(); }
</script>
</body>
</html>