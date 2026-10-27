<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Журнал действий и безопасность — ФСП</title>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --success:#16a34a; --warning:#d97706; --danger:#dc2626;
    --radius:14px; --shadow:0 1px 3px rgba(15,23,42,.08);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1360px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  
  h1{font-size:22px;margin:0;font-weight:600}
  .muted{color:var(--muted);font-size:13px}
  
  /* Баннер */
  .warning-banner{background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;padding:16px;border-radius:12px;display:flex;gap:12px;align-items:flex-start}
  .warning-banner .icon{font-size:24px;flex-shrink:0}
  .warning-banner b{display:block;margin-bottom:4px;font-size:14px}
  
  /* Вкладки */
  .tabs{display:flex;gap:4px;background:var(--card);padding:4px;border-radius:12px;box-shadow:var(--shadow);overflow-x:auto}
  .tab{padding:10px 18px;border-radius:9px;border:none;background:transparent;cursor:pointer;font:inherit;font-weight:500;color:var(--muted);white-space:nowrap;transition:.15s}
  .tab.active{background:var(--primary);color:#fff}
  
  /* Контент */
  .panel{background:var(--card);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:none}
  .panel.active{display:block}
  
  /* Панель фильтров */
  .toolbar{display:flex;gap:12px;margin-bottom:16px;align-items:center;flex-wrap:wrap}
  .toolbar select, .toolbar input{padding:9px 12px;border:1px solid var(--border);border-radius:8px;font:inherit;font-size:13px;outline:none}
  
  /* Таблицы */
  table{width:100%;border-collapse:collapse;font-size:13px}
  th,td{padding:12px;text-align:left;border-bottom:1px solid var(--border);vertical-align:middle}
  th{font-weight:600;color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.5px;background:#f8fafc;white-space:nowrap}
  tbody tr:hover{background:#f8fafc}
  
  /* Бейджи */
  .badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600}
  .badge.admin{background:#ede9fe;color:#6d28d9}
  .badge.coach{background:#dbeafe;color:#1d4ed8}
  .badge.athlete{background:#dcfce7;color:#15803d}
  .badge.success{background:#dcfce7;color:#15803d}
  .badge.danger{background:#fee2e2;color:#b91c1c}
  .badge.warn{background:#fef3c7;color:#b45309}
  
  /* Кнопки */
  .btn{padding:8px 14px;border-radius:8px;border:none;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:6px}
  .btn-primary{background:var(--primary);color:#fff} .btn-primary:hover{background:#1d4ed8}
  .btn-outline{background:#fff;border:1px solid var(--border);color:var(--text)} .btn-outline:hover{background:#f8fafc}
  .btn-sm{padding:4px 8px;font-size:11px;border-radius:6px}
  
  .empty{text-align:center;padding:40px;color:var(--muted)}
</style>
</head>
<body>

<div class="wrap">
  <div>
    <h1>Журнал действий и безопасность</h1>
    <div class="muted">Контроль доступа, логирование и обработка персональных данных</div>
  </div>

  <div class="warning-banner">
    <div class="icon">🛡️</div>
    <div>
      <b>Демонстрационный режим работы</b>
      Все персональные данные в данной системе (ФИО, даты рождения, телефоны, результаты) являются <b>фиктивными</b> и сгенерированы исключительно для тестирования интерфейса. Система не собирает и не передает реальные данные третьим лицам. В соответствии с ФЗ-152, при работе с реальными данными требуется обязательное согласие субъекта ПД.
    </div>
  </div>

  <nav class="tabs">
    <button class="tab active" onclick="switchTab('log')">📋 Журнал действий</button>
    <button class="tab" onclick="switchTab('consents')">📝 Согласия на обработку ПД</button>
  </nav>

  <!-- ВКЛАДКА 1: ЖУРНАЛ -->
  <div class="panel active" id="panel-log">
    <div class="toolbar">
      <select id="filter-role" onchange="renderLog()">
        <option value="">Все роли</option>
        <option value="Сотрудник федерации">Сотрудник федерации</option>
        <option value="Тренер">Тренер</option>
        <option value="Судья">Судья</option>
        <option value="Спортсмен">Спортсмен</option>
      </select>
      <button class="btn btn-outline" onclick="simulateAction()" style="margin-left:auto">
        ⚡ Сымитировать действие
      </button>
    </div>
    
    <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>Дата и время</th>
            <th>Пользователь</th>
            <th>Роль</th>
            <th>Действие</th>
            <th>Объект / Детали</th>
          </tr>
        </thead>
        <tbody id="logBody"></tbody>
      </table>
    </div>
  </div>

  <!-- ВКЛАДКА 2: СОГЛАСИЯ -->
  <div class="panel" id="panel-consents">
    <h2 style="font-size:16px;margin:0 0 16px">Согласия на обработку персональных данных</h2>
    <div style="overflow-x:auto">
      <table>
        <thead>
          <tr>
            <th>ФИО</th>
            <th>Дата рождения</th>
            <th>Статус согласия</th>
            <th>Кто подписал</th>
            <th>Дата подписания</th>
            <th>Действия</th>
          </tr>
        </thead>
        <tbody id="consentsBody"></tbody>
      </table>
    </div>
  </div>
</div>

<script>
/* ================= БАЗА ДАННЫХ ================= */
const DB_VERSION = '12.0';
const DB_KEY = 'fsp_database';

const INITIAL_DB = {
  // Спортсмены с согласиями
  athletes: [
    { id:1, name:'Иванов Иван Иванович', birthDate:'2008-05-15', isMinor:false, consent: { signed:true, date:'2025-09-01', signedBy:'Сам спортсмен' } },
    { id:2, name:'Петрова Анна Сергеевна', birthDate:'2010-03-20', isMinor:true, consent: { signed:true, date:'2025-09-05', signedBy:'Законный представитель (Петрова М.И.)' } },
    { id:3, name:'Сидоров Олег Павлович', birthDate:'2012-08-10', isMinor:true, consent: { signed:false, date:null, signedBy:null } },
    { id:4, name:'Кузнецова Мария Ильинична', birthDate:'2009-11-05', isMinor:true, consent: { signed:true, date:'2024-11-05', signedBy:'Законный представитель (Кузнецов И.А.)' } },
    { id:5, name:'Морозов Дмитрий Андреевич', birthDate:'2015-06-01', isMinor:true, consent: { signed:false, date:null, signedBy:null } }
  ],
  
  // Журнал действий
  actionLog: [
    { id: 1, timestamp: '2026-10-27T10:15:00', user: 'Иванова Мария', role: 'Сотрудник федерации', action: 'Создание', details: 'Создано соревнование "Чемпионат России 2026"' },
    { id: 2, timestamp: '2026-10-27T10:20:00', user: 'Иванова Мария', role: 'Сотрудник федерации', action: 'Изменение', details: 'Изменен статус заявки Петровой А.С. на "Принята"' },
    { id: 3, timestamp: '2026-10-27T11:05:00', user: 'Петров Алексей', role: 'Тренер', action: 'Создание', details: 'Подана заявка на соревнование за Иванова И.И.' },
    { id: 4, timestamp: '2026-10-27T11:30:00', user: 'Смирнов Алексей', role: 'Судья', action: 'Ввод результатов', details: 'Внесены результаты соревнования "Кубок ФСП 2026"' },
    { id: 5, timestamp: '2026-10-27T12:00:00', user: 'Иванов Иван', role: 'Спортсмен', action: 'Обновление профиля', details: 'Загружен сертификат РУСАДА' }
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

function saveDB(db) {
  localStorage.setItem(DB_KEY, JSON.stringify(db));
}

/* ================= УТИЛИТЫ ================= */
function formatDateTime(isoString) {
  if (!isoString) return '—';
  const d = new Date(isoString);
  return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function formatDate(isoString) {
  if (!isoString) return '—';
  const [y, m, d] = isoString.split('-');
  return `${d}.${m}.${y}`;
}

/* ================= РЕНДЕР ЖУРНАЛА ================= */
function renderLog() {
  const db = getDB();
  const filterRole = document.getElementById('filter-role').value;
  const tbody = document.getElementById('logBody');

  let logs = db.actionLog;
  if (filterRole) {
    logs = logs.filter(l => l.role === filterRole);
  }

  // Сортируем от новых к старым
  logs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));

  if (logs.length === 0) {
    tbody.innerHTML = `<tr><td colspan="5" class="empty">Нет записей в журнале</td></tr>`;
    return;
  }

  tbody.innerHTML = logs.map(l => {
    let roleBadge = 'athlete';
    if (l.role === 'Сотрудник федерации') roleBadge = 'admin';
    if (l.role === 'Тренер') roleBadge = 'coach';
    if (l.role === 'Судья') roleBadge = 'athlete'; // просто цвет

    return `<tr>
      <td style="white-space:nowrap; color:var(--muted)">${formatDateTime(l.timestamp)}</td>
      <td><b>${l.user}</b></td>
      <td><span class="badge ${roleBadge}">${l.role}</span></td>
      <td><b>${l.action}</b></td>
      <td style="color:var(--muted)">${l.details}</td>
    </tr>`;
  }).join('');
}

/* ================= РЕНДЕР СОГЛАСИЙ ================= */
function renderConsents() {
  const db = getDB();
  const tbody = document.getElementById('consentsBody');

  tbody.innerHTML = db.athletes.map(a => {
    const isSigned = a.consent.signed;
    const badgeClass = isSigned ? 'success' : 'danger';
    const badgeText = isSigned ? 'Подписано' : 'Отсутствует';
    
    let signer = a.consent.signedBy || '—';
    let date = a.consent.date ? formatDate(a.consent.date) : '—';

    return `<tr>
      <td><b>${a.name}</b> ${a.isMinor ? '<span class="badge warn" style="margin-left:4px">Несовершеннолетний</span>' : ''}</td>
      <td>${formatDate(a.birthDate)}</td>
      <td><span class="badge ${badgeClass}">${badgeText}</span></td>
      <td>${signer}</td>
      <td>${date}</td>
      <td>
        ${!isSigned ? `<button class="btn btn-primary btn-sm" onclick="signConsent(${a.id})">Подписать</button>` : `<button class="btn btn-outline btn-sm" onclick="revokeConsent(${a.id})">Отозвать</button>`}
      </td>
    </tr>`;
  }).join('');
}

/* ================= ДЕЙСТВИЯ ================= */
function switchTab(tabName) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
  
  if (tabName === 'log') {
    document.querySelectorAll('.tab')[0].classList.add('active');
    document.getElementById('panel-log').classList.add('active');
    renderLog();
  } else {
    document.querySelectorAll('.tab')[1].classList.add('active');
    document.getElementById('panel-consents').classList.add('active');
    renderConsents();
  }
}

// Имитация действия для проверки работы журнала
function simulateAction() {
  const db = getDB();
  const actions = [
    { user: 'Иванова Мария', role: 'Сотрудник федерации', action: 'Изменение', details: 'Изменен приказ о присвоении разряда' },
    { user: 'Петров Алексей', role: 'Тренер', action: 'Удаление', details: 'Удалена заявка на соревнование' },
    { user: 'Смирнов Алексей', role: 'Судья', action: 'Смена статуса', details: 'Назначен на должность Главного судьи' },
    { user: 'Иванов Иван', role: 'Спортсмен', action: 'Обновление', details: 'Обновлены контактные данные' }
  ];

  const randomAction = actions[Math.floor(Math.random() * actions.length)];
  
  const newLog = {
    id: Date.now(),
    timestamp: new Date().toISOString(),
    ...randomAction
  };

  db.actionLog.push(newLog);
  saveDB(db);
  renderLog();
}

// Подписание и отзыв согласия (демо)
function signConsent(athleteId) {
  const db = getDB();
  const ath = db.athletes.find(a => a.id === athleteId);
  if (ath) {
    ath.consent = {
      signed: true,
      date: new Date().toISOString().split('T')[0],
      signedBy: ath.isMinor ? 'Законный представитель' : 'Сам спортсмен'
    };
    
    // Логируем действие
    db.actionLog.push({
      id: Date.now(),
      timestamp: new Date().toISOString(),
      user: 'Система',
      role: 'Сотрудник федерации',
      action: 'Подписание согласия',
      details: `Согласие на обработку ПД для ${ath.name} подписано`
    });

    saveDB(db);
    renderConsents();
    renderLog();
  }
}

function revokeConsent(athleteId) {
  const db = getDB();
  const ath = db.athletes.find(a => a.id === athleteId);
  if (ath) {
    ath.consent = { signed: false, date: null, signedBy: null };
    
    // Логируем действие
    db.actionLog.push({
      id: Date.now(),
      timestamp: new Date().toISOString(),
      user: 'Система',
      role: 'Сотрудник федерации',
      action: 'Отзыв согласия',
      details: `Согласие на обработку ПД для ${ath.name} отозвано`
    });

    saveDB(db);
    renderConsents();
    renderLog();
  }
}

/* ================= СТАРТ ================= */
renderLog();
</script>
</body>
</html>