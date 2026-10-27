<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Личный кабинет тренера — спортивное программирование</title>
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
  .user-badge .avatar{width:34px;height:34px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:13px;background:#2563eb}
  .user-badge .info{display:flex;flex-direction:column;line-height:1.25} .user-badge .name{font-weight:600;font-size:13px} .user-badge .role{font-size:11px;color:var(--muted)}
  .logout-btn{padding:6px 10px;border:1px solid var(--border);background:#fff;border-radius:8px;font:inherit;font-size:12px;cursor:pointer;color:var(--muted)}
  .tabs{display:flex;gap:4px;background:var(--card);padding:4px;border-radius:12px;box-shadow:var(--shadow);overflow-x:auto}
  .tab{padding:10px 18px;border-radius:9px;border:none;background:transparent;cursor:pointer;font:inherit;font-weight:500;color:var(--muted);white-space:nowrap;transition:.15s;display:flex;align-items:center;gap:6px}
  .tab.active{background:var(--primary);color:#fff}
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow)}
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap}
  .badge.success{background:#dcfce7;color:#15803d} .badge.warn{background:#fef3c7;color:#b45309} .badge.danger{background:#fee2e2;color:#b91c1c} .badge.info{background:#dbeafe;color:#1d4ed8} .badge.gray{background:#f1f5f9;color:#64748b}
  .btn-primary{padding:9px 16px;background:var(--primary);color:#fff;border:none;border-radius:10px;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s}
  .btn-primary:hover{background:#1d4ed8} .btn-secondary{padding:9px 16px;background:#fff;color:var(--text);border:1px solid var(--border);border-radius:10px;font:inherit;font-size:13px;cursor:pointer}
  .btn-sm{padding:6px 12px;font-size:12px;border-radius:8px}
  .card{border-radius:12px;padding:16px;border:1px solid var(--border);background:#fff}
  .card.info{background:#eff6ff;border-color:#bfdbfe}
  .flex-between{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:11px 12px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle}
  th{font-weight:600;color:var(--muted);font-size:12px;text-transform:uppercase;letter-spacing:.3px;background:#f8fafc;white-space:nowrap}
  tbody tr:last-child td{border-bottom:none} tbody tr:hover{background:#f8fafc} .right{text-align:right} .center{text-align:center} .empty{text-align:center;padding:40px;color:var(--muted)}
  .form-group{margin-bottom:14px} .form-group > label{display:block;font-weight:500;margin-bottom:6px;font-size:13px}
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
  .login-option .avatar{width:44px;height:44px;border-radius:50%;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;background:#2563eb}
  .toast{position:fixed;bottom:24px;right:24px;padding:12px 18px;background:#0f172a;color:#fff;border-radius:10px;font-size:13px;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none;z-index:1200} .toast.show{opacity:1;transform:translateY(0)}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  .info-item{background:#fff;padding:12px;border-radius:8px;border:1px solid var(--border)}
  .info-item .lbl{font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;margin-bottom:4px}
  .info-item .val{font-size:14px}
</style>
</head>
<body>

<div class="wrap">
  <div class="topbar">
    <div><h1>Личный кабинет тренера</h1><div class="muted">Федерация спортивного программирования</div></div>
    <div class="user-badge" id="userBadge" style="display:none"></div>
  </div>
  <nav class="tabs" id="tabs"></nav>
  <div class="panel" id="panelProfile" style="display:none"></div>
  <div class="panel" id="panelAthletes" style="display:none"></div>
  <div class="panel" id="panelApplications" style="display:none"></div>
  <div class="panel" id="panelRanks" style="display:none"></div>
  <div class="panel" id="panelResults" style="display:none"></div>
</div>

<div class="login-overlay" id="loginOverlay">
  <div class="login-card">
    <h2>Вход в кабинет</h2><div class="sub">Выберите тренера</div>
    <div class="login-grid" id="loginGrid"></div>
  </div>
</div>

<div class="modal-overlay" id="bindModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Привязка спортсмена</h2><button class="modal-close" data-close="bindModal">✕</button></div>
    <div class="modal-body" id="bindModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="bindModal">Отмена</button><button class="btn-primary" id="bindSaveBtn">Отправить запрос</button></div>
  </div>
</div>

<div class="modal-overlay" id="compModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Заявка на соревнование</h2><button class="modal-close" data-close="compModal">✕</button></div>
    <div class="modal-body" id="compModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="compModal">Отмена</button><button class="btn-primary" id="compSaveBtn">Подать</button></div>
  </div>
</div>

<div class="modal-overlay" id="rankModal">
  <div class="modal-card">
    <div class="modal-head"><h2>Заявка на разряд</h2><button class="modal-close" data-close="rankModal">✕</button></div>
    <div class="modal-body" id="rankModalBody"></div>
    <div class="modal-foot"><button class="btn-secondary" data-close="rankModal">Отмена</button><button class="btn-primary" id="rankSaveBtn">Подать</button></div>
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
    { id:1, name:'Иванов Иван Иванович', birthDate:'2008-05-15', muni:'г. Оренбург', org:'Губернаторский лицей', disciplines:['Алгоритмическое программирование'], coaches:['Петров А.С.'], rank:'1 разряд', rankExpires:'2028-09-01', antidoping:{passed:true}, legalRep:null },
    { id:2, name:'Петрова Анна Сергеевна', birthDate:'2010-03-20', muni:'г. Оренбург', org:'Лицей №1', disciplines:['Алгоритмическое программирование'], coaches:['Смирнов В.В.'], rank:'КМС', rankExpires:'2029-01-01', antidoping:{passed:true}, legalRep:{name:'Петрова М.И.', consentDate:'2025-09-01'} },
    { id:3, name:'Сидоров Олег Павлович', birthDate:'2012-08-10', muni:'г. Орск', org:'Гимназия №1', disciplines:['Спортивная робототехника'], coaches:['Петров А.С.'], rank:'2 разряд', rankExpires:'2026-12-01', antidoping:{passed:false}, legalRep:{name:'Сидорова Е.П.', consentDate:'2025-10-10'} },
    { id:4, name:'Кузнецова Мария Ильинична', birthDate:'2009-11-05', muni:'г. Бузулук', org:'IT-куб', disciplines:['Искусственный интеллект'], coaches:['Смирнов В.В.'], rank:'1 юношеский', rankExpires:'2026-11-25', antidoping:{passed:true}, legalRep:{name:'Кузнецов И.А.', consentDate:'2024-11-05'} },
    { id:5, name:'Морозов Дмитрий Андреевич', birthDate:'2015-06-01', muni:'г. Новотроицк', org:'Кванториум', disciplines:['Спортивная робототехника'], coaches:[], rank:'3 юношеский', rankExpires:'2028-11-15', antidoping:{passed:false}, legalRep:{name:'Морозова О.С.', consentDate:'2025-06-01'} }
  ],
  competitions: [
    { id:1, name:'Областной этап ВсОШ', discipline:'Алгоритмическое программирование', startDate:'2026-11-05', endDate:'2026-11-07', ageGroups:['15-16 лет','17-18 лет'], deadline:'2026-11-03', status:'applications_open' },
    { id:2, name:'Кубок области', discipline:'Алгоритмическое программирование', startDate:'2026-11-21', endDate:'2026-11-21', ageGroups:['13-14 лет','15-16 лет','17-18 лет','19+'], deadline:'2026-11-15', status:'applications_open' },
    { id:3, name:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', startDate:'2025-09-15', endDate:'2025-09-15', ageGroups:['17-18 лет','19+'], deadline:'2025-09-01', status:'completed' }
  ],
  applications: [], 
  rankApplications: [], 
  results: [ { id:1, athleteId:1, competitionId:3, competitionName:'Кубок федерации по ИИ', discipline:'Искусственный интеллект', date:'2025-09-15', place:2, score:8, status:'official' } ],
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
  { id:'coach-1', name:'Петров Алексей Сергеевич', short:'ПА', role:'coach', roleLabel:'Тренер', coachId:1, email:'petrov@example.com' },
  { id:'coach-2', name:'Смирнов Виктор Владимирович', short:'СВ', role:'coach', roleLabel:'Тренер', coachId:2, email:'smirnov@example.com' }
];


// блок-помощник (REG_KEY / getRegisteredByRole / makeRegUser)

function getAllUsers() {
  return [
    ...DEMO_USERS,
    ...getRegisteredByRole('coach').map(r => makeRegUser(r, 'coach'))
  ];
}

/* ================= УТИЛИТЫ ================= */
const pad = n => String(n).padStart(2,'0');
const iso = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
const today = () => { const d = new Date(); d.setHours(0,0,0,0); return d; };
const parseISO = s => { const [y,m,d] = s.split('-').map(Number); return new Date(y, m-1, d); };
const daysBetween = (a,b) => Math.round((parseISO(b) - parseISO(a)) / 86400000);
const fmtDate = s => { if(!s) return '—'; const d = parseISO(s); return `${pad(d.getDate())}.${pad(d.getMonth()+1)}.${d.getFullYear()}`; };

function appStatusBadge(s) { return {'pending':'<span class="badge warn">На рассмотрении</span>','accepted':'<span class="badge success">Принята</span>','approved':'<span class="badge success">Одобрена</span>','rejected':'<span class="badge danger">Отклонена</span>'}[s] || s; }
function toast(msg) { const el = document.getElementById('toast'); el.textContent = msg; el.classList.add('show'); setTimeout(() => el.classList.remove('show'), 2500); }

const STATE = { user: null, tab: 'profile' };

/* ================= РЕНДЕР ================= */
function renderUserBadge() {
  const el = document.getElementById('userBadge');
  if (!STATE.user) { el.style.display = 'none'; return; }
  el.style.display = 'flex';
  el.innerHTML = `<div class="avatar">${STATE.user.short}</div><div class="info"><div class="name">${STATE.user.name}</div><div class="role">Тренер</div></div><button class="logout-btn" id="logoutBtn">Выйти</button>`;
  document.getElementById('logoutBtn').addEventListener('click', () => { STATE.user = null; sessionClear(); document.getElementById('loginOverlay').classList.add('show'); buildLoginGrid(); renderAll(); });
}

function renderTabs() {
  const tabs = [
    { key:'profile', label:'👤 Профиль' },
    { key:'athletes', label:'👥 Мои спортсмены' },
    { key:'applications', label:'📝 Заявки на соревнования' },
    { key:'ranks', label:'🏅 Разряды подопечных' },
    { key:'results', label:'🏆 Результаты подопечных' }
  ];
  document.getElementById('tabs').innerHTML = tabs.map(t => `<button class="tab ${STATE.tab === t.key ? 'active' : ''}" data-tab="${t.key}">${t.label}</button>`).join('');
}

function renderProfile() {
  const panel = document.getElementById('panelProfile');
  panel.style.display = STATE.tab === 'profile' ? '' : 'none'; if (STATE.tab !== 'profile') return;
  panel.innerHTML = `
    <h2>Профиль тренера</h2>
    <div class="card info">
      <div class="info-grid">
        <div class="info-item"><div class="lbl">ФИО</div><div class="val"><b>${STATE.user.name}</b></div></div>
        <div class="info-item"><div class="lbl">Роль</div><div class="val">${STATE.user.roleLabel}</div></div>
        <div class="info-item full" style="grid-column: 1 / -1"><div class="lbl">Email для уведомлений</div><div class="val"><b>${STATE.user.email}</b></div></div>
      </div>
      <div style="font-size:12px;color:var(--muted);margin-top:12px">На этот адрес будут приходить уведомления о статусах заявок, дедлайнах и изменениях в расписании соревнований.</div>
    </div>
  `;
}

function getMyAthletes() {
  const db = getDB();
  const links = db.coachLinks.filter(l => l.coachId === STATE.user.coachId && l.status === 'active');
  return links.map(l => ({ ...db.athletes.find(a => a.id === l.athleteId), disciplineLink: l.discipline })).filter(a => a.id);
}

function renderAthletes() {
  const panel = document.getElementById('panelAthletes');
  panel.style.display = STATE.tab === 'athletes' ? '' : 'none'; if (STATE.tab !== 'athletes') return;
  const myAth = getMyAthletes();

  panel.innerHTML = `
    <div class="flex-between" style="margin-bottom:16px">
      <h2>Мои спортсмены</h2>
      <button class="btn-primary" id="btnFindAthlete">+ Найти спортсмена</button>
    </div>
    ${myAth.length === 0 ? `<div class="empty">У вас пока нет привязанных спортсменов</div>` : `
    <table>
      <thead><tr><th>ФИО</th><th>Организация</th><th>Дисциплина (связь)</th><th>Разряд</th><th>Статус связи</th></tr></thead>
      <tbody>
        ${myAth.map(a => `<tr>
          <td><b>${a.name}</b><br><small style="color:var(--muted)">${a.muni}</small></td>
          <td>${a.org}</td>
          <td><span class="badge info">${a.disciplineLink}</span></td>
          <td><span class="badge gray">${a.rank}</span></td>
          <td><span class="badge success">Активна</span></td>
        </tr>`).join('')}
      </tbody>
    </table>`}
  `;
  document.getElementById('btnFindAthlete').addEventListener('click', openBindModal);
}

function renderApplications() {
  const panel = document.getElementById('panelApplications');
  panel.style.display = STATE.tab === 'applications' ? '' : 'none'; if (STATE.tab !== 'applications') return;
  const db = getDB();
  const myAth = getMyAthletes();
  const comps = db.competitions.filter(c => c.status === 'applications_open');

  panel.innerHTML = `
    <h2>Подача заявок на соревнования</h2>
    ${comps.length === 0 ? `<div class="empty">Нет соревнований с открытым приёмом заявок</div>` : `
    <table>
      <thead><tr><th>Соревнование</th><th>Дисциплина</th><th>Срок подачи</th><th>Действия</th></tr></thead>
      <tbody>
        ${comps.map(c => `<tr>
          <td><b>${c.name}</b></td>
          <td>${c.discipline}</td>
          <td>${fmtDate(c.deadline)}</td>
          <td><button class="btn-primary btn-sm" onclick="openCompModal(${c.id})">Подать заявку</button></td>
        </tr>`).join('')}
      </tbody>
    </table>`}
    <div class="section-title" style="margin-top:20px;font-weight:600;font-size:14px">Мои поданные заявки</div>
    <table>
      <thead><tr><th>Спортсмен</th><th>Соревнование</th><th>Дата подачи</th><th>Статус</th></tr></thead>
      <tbody>
        ${db.applications.filter(app => myAth.some(a => a.id === app.athleteId)).map(app => {
          const ath = db.athletes.find(a => a.id === app.athleteId);
          const comp = db.competitions.find(c => c.id === app.competitionId);
          return `<tr><td>${ath.name}</td><td>${comp.name}</td><td>${fmtDate(app.submittedDate)}</td><td>${appStatusBadge(app.status)}</td></tr>`;
        }).join('') || `<tr><td colspan="4" class="empty">Нет заявок</td></tr>`}
      </tbody>
    </table>
  `;
}

function renderRanks() {
  const panel = document.getElementById('panelRanks');
  panel.style.display = STATE.tab === 'ranks' ? '' : 'none'; if (STATE.tab !== 'ranks') return;
  const db = getDB();
  const myAth = getMyAthletes();

  panel.innerHTML = `
    <h2>Разряды подопечных</h2>
    <table>
      <thead><tr><th>Спортсмен</th><th>Текущий разряд</th><th>Действует до</th><th>Статус</th><th>Действия</th></tr></thead>
      <tbody>
        ${myAth.map(a => {
          const left = daysBetween(iso(today()), a.rankExpires);
          const isExp = left < 0; const isWarn = left <= 60 && left >= 0;
          return `<tr>
            <td><b>${a.name}</b></td>
            <td><span class="badge info">${a.rank}</span></td>
            <td>${fmtDate(a.rankExpires)}</td>
            <td>${isExp ? '<span class="badge danger">Просрочен</span>' : isWarn ? `<span class="badge warn">Истекает (${left} дн.)</span>` : '<span class="badge success">Действует</span>'}</td>
            <td><button class="btn-secondary btn-sm" onclick="openRankModal(${a.id})">Подать на разряд</button></td>
          </tr>`;
        }).join('')}
      </tbody>
    </table>
    <div class="section-title" style="margin-top:20px;font-weight:600;font-size:14px">История заявок на разряды</div>
    <table>
      <thead><tr><th>Спортсмен</th><th>Тип</th><th>Разряд</th><th>Основание</th><th>Статус</th></tr></thead>
      <tbody>
        ${db.rankApplications.filter(r => myAth.some(a => a.id === r.athleteId)).map(r => {
          const ath = db.athletes.find(a => a.id === r.athleteId);
          return `<tr><td>${ath.name}</td><td>${r.type==='assign'?'Присвоение':'Подтверждение'}</td><td>${r.requestedRank}</td><td>${r.competitionName||'—'}</td><td>${appStatusBadge(r.status)}</td></tr>`;
        }).join('') || `<tr><td colspan="5" class="empty">Нет заявок</td></tr>`}
      </tbody>
    </table>
  `;
}

function renderResults() {
  const panel = document.getElementById('panelResults');
  panel.style.display = STATE.tab === 'results' ? '' : 'none'; if (STATE.tab !== 'results') return;
  const db = getDB();
  const myAth = getMyAthletes();

  panel.innerHTML = `<h2>Результаты подопечных</h2>`;
  myAth.forEach(a => {
    const results = db.results.filter(r => r.athleteId === a.id);
    if (results.length > 0) {
      panel.innerHTML += `
        <h3 style="font-size:14px;margin:16px 0 8px;color:var(--muted)">${a.name}</h3>
        <table style="margin-bottom:16px">
          <thead><tr><th>Дата</th><th>Соревнование</th><th>Место</th><th>Баллы</th><th>Статус</th></tr></thead>
          <tbody>
            ${results.map(r => `<tr><td>${fmtDate(r.date)}</td><td>${r.competitionName}</td><td class="center"><b>${r.place}</b></td><td>${r.score}</td><td>${r.status === 'official' ? '<span class="badge success">Официальный</span>' : '<span class="badge warn">Черновик</span>'}</td></tr>`).join('')}
          </tbody>
        </table>`;
    }
  });
  if (!panel.innerHTML.includes('<table')) panel.innerHTML += `<div class="empty">Нет результатов</div>`;
}

function renderAll() {
  renderUserBadge();
  if (!STATE.user) { document.getElementById('tabs').innerHTML = ''; ['panelProfile','panelAthletes','panelApplications','panelRanks','panelResults'].forEach(id => document.getElementById(id).style.display = 'none'); return; }
  renderTabs(); renderProfile(); renderAthletes(); renderApplications(); renderRanks(); renderResults();
}

/* ================= ДЕЙСТВИЯ ================= */
function openBindModal() {
  const db = getDB();
  const available = db.athletes.filter(a => !db.coachLinks.some(l => l.coachId === STATE.user.coachId && l.athleteId === a.id && l.status === 'active'));
  document.getElementById('bindModalBody').innerHTML = `
    <div class="form-group">
      <label>Спортсмен</label>
      <select id="bind-athlete">${available.map(a => `<option value="${a.id}">${a.name} (${a.org})</option>`).join('')}</select>
    </div>
    <div class="form-group">
      <label>Дисциплина привязки</label>
      <select id="bind-disc"><option>Алгоритмическое программирование</option><option>Спортивная робототехника</option><option>Искусственный интеллект</option></select>
    </div>
  `;
  document.getElementById('bindModal').classList.add('show');
}

document.getElementById('bindSaveBtn').onclick = () => {
  const athId = +document.getElementById('bind-athlete').value;
  const disc = document.getElementById('bind-disc').value;
  const db = getDB();
  db.coachLinks.push({ id: Date.now(), coachId: STATE.user.coachId, athleteId: athId, discipline: disc, status: 'pending', date: iso(today()) });
  saveDB(db); closeModal('bindModal'); toast('Запрос отправлен спортсмену'); renderAll();
};

function openCompModal(compId) {
  const comp = getDB().competitions.find(c => c.id === compId);
  const myAth = getMyAthletes().filter(a => a.disciplineLink === comp.discipline);
  document.getElementById('compModalBody').innerHTML = `
    <div class="form-group">
      <label>Выберите спортсмена (Дисциплина: ${comp.discipline})</label>
      <select id="app-athlete">
        ${myAth.length ? myAth.map(a => `<option value="${a.id}">${a.name}</option>`).join('') : '<option value="">Нет подходящих спортсменов</option>'}
      </select>
    </div>
    <input type="hidden" id="app-comp" value="${compId}">
  `;
  document.getElementById('compModal').classList.add('show');
}

document.getElementById('compSaveBtn').onclick = () => {
  const athId = +document.getElementById('app-athlete').value;
  const compId = +document.getElementById('app-comp').value;
  if (!athId) { toast('Выберите спортсмена'); return; }
  const db = getDB();
  if (db.applications.find(x => x.athleteId === athId && x.competitionId === compId)) { toast('Заявка уже подана'); return; }
  db.applications.push({ id: Date.now(), athleteId: athId, competitionId: compId, submittedDate: iso(today()), status: 'pending' });
  saveDB(db); closeModal('compModal'); toast('Заявка подана'); renderAll();
};

function openRankModal(athId) {
  const a = getDB().athletes.find(x => x.id === athId);
  document.getElementById('rankModalBody').innerHTML = `
    <div class="form-group">
      <label>Тип заявки</label>
      <select id="rk-type"><option value="confirm">Подтверждение (${a.rank})</option><option value="assign">Присвоение следующего</option></select>
    </div>
    <div class="form-group">
      <label>Соревнование (основание)</label>
      <select id="rk-comp"><option value="">— не указано —</option>${getDB().competitions.filter(c=>c.status==='completed').map(c => `<option value="${c.id}">${c.name}</option>`).join('')}</select>
    </div>
    <input type="hidden" id="rk-athlete" value="${athId}">
  `;
  document.getElementById('rankModal').classList.add('show');
}

document.getElementById('rankSaveBtn').onclick = () => {
  const athId = +document.getElementById('rk-athlete').value;
  const type = document.getElementById('rk-type').value;
  const compId = +document.getElementById('rk-comp').value;
  const db = getDB(); const a = db.athletes.find(x => x.id === athId); const comp = db.competitions.find(c => c.id === compId);
  db.rankApplications.push({ id: Date.now(), athleteId: athId, type, requestedRank: type==='confirm'?a.rank:'КМС', competitionId: compId, competitionName: comp ? comp.name : null, submittedDate: iso(today()), status: 'pending', submittedBy: 'coach' });
  saveDB(db); closeModal('rankModal'); toast('Заявка подана'); renderAll();
};

function closeModal(id) { document.getElementById(id).classList.remove('show'); }
document.addEventListener('click', e => { if (e.target.closest('[data-close]')) closeModal(e.target.closest('[data-close]').dataset.close); });

/* ================= СТАРТ ================= */
function buildLoginGrid() {
  const users = getAllUsers();
  document.getElementById('loginGrid').innerHTML = users.map(u => `
    <button class="login-option" onclick="loginAs('${u.id}')">
      <div class="avatar">${u.short}</div>
      <div>
        <div style="font-weight:600">${u.name}</div>
        <div style="font-size:12px;color:var(--muted)">
          Тренер${u.isRegistered ? ' · зарегистрирован' : ''}
        </div>
      </div>
    </button>`).join('');
}

function loginAs(id) {
  const user = getAllUsers().find(u => u.id === id);
  if (!user) return;
  if (user.isRegistered) ensureCoachRecord(user);
  STATE.user = user;
  sessionSave(user);
  STATE.tab = 'profile';
  document.getElementById('loginOverlay').classList.remove('show');
  renderAll();
}

buildLoginGrid();
const saved = sessionLoad();
if (saved && saved.role === 'coach') {
  if (saved.isRegistered) ensureCoachRecord(saved);   // ← задаёт стабильный coachId
  STATE.user = saved;
  renderAll();
} else {
  document.getElementById('loginOverlay').classList.add('show');
  renderAll();
}

function ensureCoachRecord(user) {
  if (user.coachId) return user;
  // стабильный хэш по email — чтобы между сессиями был один и тот же coachId
  let h = 0;
  for (const c of (user.email || '')) h = ((h << 5) - h + c.charCodeAt(0)) | 0;
  user.coachId = 1000 + Math.abs(h % 9000); // 1000..9999 — вне демо-1/2
  return user;
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