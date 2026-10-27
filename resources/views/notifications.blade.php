<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Центр уведомлений — ФСП</title>
<style>
  :root{
    --bg:#f3f5f9; --card:#fff; --text:#0f172a; --muted:#64748b; --border:#e2e8f0;
    --primary:#2563eb; --success:#16a34a; --warning:#d97706; --danger:#dc2626;
    --radius:14px; --shadow:0 1px 3px rgba(15,23,42,.08);
  }
  *{box-sizing:border-box}
  body{margin:0;padding:24px;background:var(--bg);color:var(--text);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.5}
  .wrap{max-width:1200px;margin:0 auto;display:flex;flex-direction:column;gap:18px}
  
  h1{font-size:22px;margin:0;font-weight:600}
  .muted{color:var(--muted);font-size:13px}
  
  /* Панели */
  .panel{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px}
  .panel h2{font-size:16px;margin:0 0 16px;font-weight:600;display:flex;align-items:center;gap:8px}
  
  /* Сетка */
  .grid{display:grid;grid-template-columns:320px 1fr;gap:20px;align-items:start}
  
  /* Настройки каналов */
  .channel-item{display:flex;align-items:center;justify-content:space-between;padding:12px;border:1px solid var(--border);border-radius:10px;margin-bottom:10px;cursor:pointer;transition:.15s}
  .channel-item:hover{background:#f8fafc}
  .channel-item.active{border-color:var(--primary);background:#eff6ff}
  .channel-item .info{display:flex;flex-direction:column}
  .channel-item .name{font-weight:600;font-size:14px}
  .channel-item .desc{font-size:12px;color:var(--muted)}
  
  /* Переключатель */
  .switch{position:relative;width:40px;height:22px;flex-shrink:0}
  .switch input{opacity:0;width:0;height:0}
  .slider{position:absolute;cursor:pointer;top:0;left:0;right:0;bottom:0;background-color:#cbd5e1;transition:.4s;border-radius:22px}
  .slider:before{position:absolute;content:"";height:16px;width:16px;left:3px;bottom:3px;background-color:white;transition:.4s;border-radius:50%}
  input:checked + .slider{background-color:var(--primary)}
  input:checked + .slider:before{transform:translateX(18px)}
  
  /* Список уведомлений */
  .notif-list{display:flex;flex-direction:column;gap:12px;max-height: 600px; overflow-y: auto; padding-right: 8px;}
  .notif-item{display:flex;gap:16px;padding:16px;border:1px solid var(--border);border-radius:12px;background:#fff;transition:.15s;position:relative}
  .notif-item.unread{background:#eff6ff;border-color:#bfdbfe}
  .notif-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
  .notif-icon.info{background:#dbeafe;color:#1d4ed8}
  .notif-icon.warn{background:#fef3c7;color:#b45309}
  .notif-icon.success{background:#dcfce7;color:#15803d}
  .notif-icon.danger{background:#fee2e2;color:#b91c1c}
  .notif-body{flex:1}
  .notif-title{font-weight:600;font-size:14px;margin-bottom:4px}
  .notif-desc{font-size:13px;color:#334155;margin-bottom:6px}
  .notif-meta{font-size:11px;color:var(--muted);display:flex;gap:12px;align-items:center}
  .notif-channels{display:flex;gap:6px;margin-top:8px}
  .badge-ch{background:#f1f5f9;color:#475569;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:600}
  
  /* Кнопки */
  .btn{padding:8px 14px;border-radius:8px;border:none;font:inherit;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:6px;justify-content:center}
  .btn-primary{background:var(--primary);color:#fff}
  .btn-primary:hover{background:#1d4ed8}
  .btn-outline{background:#fff;border:1px solid var(--border);color:var(--text)}
  .btn-outline:hover{background:#f8fafc}
  .btn-sm{padding:4px 10px;font-size:12px}
  
  /* Тестовая панель */
  .test-panel{background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px;margin-top:20px}
  .test-panel h3{font-size:14px;margin:0 0 12px;color:#b45309}
  .test-buttons{display:flex;gap:8px;flex-wrap:wrap}
  
  /* Тосты */
  .toast-container{position:fixed;bottom:24px;right:24px;display:flex;flex-direction:column;gap:10px;z-index:9999}
  .toast{background:#0f172a;color:#fff;padding:12px 18px;border-radius:10px;font-size:13px;box-shadow:0 8px 24px rgba(0,0,0,.2);display:flex;gap:10px;align-items:center;animation:slideIn 0.3s}
  @keyframes slideIn{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
</style>
</head>
<body>

<div class="wrap">
  <div>
    <h1>Центр уведомлений</h1>
    <div class="muted">Настройка каналов доставки и история событий</div>
  </div>

  <div class="grid">
    <!-- Левая колонка: Настройки -->
    <div>
      <div class="panel">
        <h2>📡 Каналы доставки</h2>
        <div class="channel-item active" onclick="toggleChannel(this, 'inapp')">
          <div class="info">
            <div class="name">🔔 Личный кабинет</div>
            <div class="desc">Внутренние уведомления</div>
          </div>
          <label class="switch"><input type="checkbox" checked disabled><span class="slider"></span></label>
        </div>
        
        <div class="channel-item" onclick="toggleChannel(this, 'email')">
          <div class="info">
            <div class="name">📧 Электронная почта</div>
            <div class="desc">ivanov@example.com</div>
          </div>
          <label class="switch"><input type="checkbox" id="ch-email"><span class="slider"></span></label>
        </div>
        
        <div class="channel-item" onclick="toggleChannel(this, 'telegram')">
          <div class="info">
            <div class="name">📱 Telegram-бот</div>
            <div class="desc">@fsp_orenburg_bot</div>
          </div>
          <label class="switch"><input type="checkbox" id="ch-telegram"><span class="slider"></span></label>
        </div>
        
        <div class="channel-item" onclick="toggleChannel(this, 'max')">
          <div class="info">
            <div class="name">💬 Бот в MAX</div>
            <div class="desc">ФСП Оренбург (MAX)</div>
          </div>
          <label class="switch"><input type="checkbox" id="ch-max"><span class="slider"></span></label>
        </div>
      </div>

      <!-- Панель тестирования -->
      <div class="panel test-panel">
        <h3>🧪 Тестирование событий</h3>
        <div class="test-buttons">
          <button class="btn btn-outline btn-sm" onclick="simulateEvent('reg')">Открытие регистрации</button>
          <button class="btn btn-outline btn-sm" onclick="simulateEvent('status')">Смена статуса</button>
          <button class="btn btn-outline btn-sm" onclick="simulateEvent('protocol')">Публикация протокола</button>
          <button class="btn btn-outline btn-sm" onclick="simulateEvent('rank')">Заявка на разряд</button>
          <button class="btn btn-outline btn-sm" onclick="simulateEvent('deadline')">Приближение срока</button>
        </div>
      </div>
    </div>

    <!-- Правая колонка: Список уведомлений -->
    <div class="panel">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h2 style="margin:0">📥 Лента уведомлений</h2>
        <button class="btn btn-outline btn-sm" onclick="markAllRead()">Прочитать все</button>
      </div>
      <div class="notif-list" id="notifList">
        <!-- Уведомления рендерятся здесь -->
      </div>
    </div>
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
/* ================= СОСТОЯНИЕ ================= */
const STATE = {
  channels: { inapp: true, email: false, telegram: false, max: false },
  notifications: []
};

/* ================= УТИЛИТЫ ================= */
function getTimeNow() {
  return new Date().toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
}

function showToast(msg) {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<span>🔔</span> <div>${msg}</div>`;
  container.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 4000);
}

/* ================= НАСТРОЙКИ КАНАЛОВ ================= */
function toggleChannel(el, channelKey) {
  if (channelKey === 'inapp') return; // In-app нельзя отключить
  
  const checkbox = el.querySelector('input[type="checkbox"]');
  checkbox.checked = !checkbox.checked;
  STATE.channels[channelKey] = checkbox.checked;
  
  if (checkbox.checked) {
    el.classList.add('active');
  } else {
    el.classList.remove('active');
  }
}

/* ================= ГЕНЕРАЦИЯ УВЕДОМЛЕНИЙ ================= */
const EVENTS = {
  reg: {
    icon: '📝', type: 'info', title: 'Открыта регистрация',
    desc: 'Открыт приём заявок на соревнование «Кубок Оренбургской области по спортивному программированию».',
    link: 'schedule'
  },
  status: {
    icon: '🔄', type: 'warn', title: 'Статус заявки изменён',
    desc: 'Ваша заявка на участие в «Областном этапе ВсОШ» переведена в статус «Принята».',
    link: 'applications'
  },
  protocol: {
    icon: '📄', type: 'success', title: 'Опубликован протокол',
    desc: 'Опубликованы результаты соревнования «Чемпионат России 2026». Проверьте свои баллы и место.',
    link: 'results'
  },
  rank: {
    icon: '🏅', type: 'info', title: 'Новая заявка на разряд',
    desc: 'Тренер Петров А.С. подал заявку на присвоение 1 разряда для спортсмена Иванова И.И.',
    link: 'grades'
  },
  deadline: {
    icon: '⏰', type: 'danger', title: 'Приближается дедлайн',
    desc: 'До окончания приёма заявок на соревнование «Турнир по ИБ» осталось 24 часа. Успейте подать заявку!',
    link: 'calendar'
  }
};

function simulateEvent(eventKey) {
  const event = EVENTS[eventKey];
  if (!event) return;

  // Создаем объект уведомления
  const notif = {
    id: Date.now(),
    icon: event.icon,
    type: event.type,
    title: event.title,
    desc: event.desc,
    time: getTimeNow(),
    read: false,
    channels: []
  };

  // Проверяем, в какие каналы нужно отправить
  if (STATE.channels.inapp) notif.channels.push('Кабинет');
  
  if (STATE.channels.email) {
    notif.channels.push('Email');
    console.log(`[EMAIL API] Отправка письма: ${event.title}`);
  }
  if (STATE.channels.telegram) {
    notif.channels.push('Telegram');
    // В реальном приложении здесь будет fetch к вашему бэкенду для отправки через Telegram Bot API
    console.log(`[TELEGRAM API] Отправка сообщения: ${event.title}`);
  }
  if (STATE.channels.max) {
    notif.channels.push('MAX');
    console.log(`[MAX API] Отправка сообщения: ${event.title}`);
  }

  // Добавляем в начало списка
  STATE.notifications.unshift(notif);
  
  // Показываем всплывающее уведомление (имитация push)
  showToast(`<b>${event.title}</b><br><span style="font-size:12px;opacity:0.8">${event.desc}</span>`);

  renderNotifications();
}

/* ================= РЕНДЕР ЛЕНТЫ ================= */
function renderNotifications() {
  const container = document.getElementById('notifList');
  
  if (STATE.notifications.length === 0) {
    container.innerHTML = `<div style="text-align:center;padding:40px;color:#64748b">Нет уведомлений. Нажмите кнопку в блоке «Тестирование», чтобы сгенерировать событие.</div>`;
    return;
  }

  container.innerHTML = STATE.notifications.map(n => {
    // Формируем бейджи каналов
    const chBadges = n.channels.map(ch => `<span class="badge-ch">${ch}</span>`).join('');

    return `<div class="notif-item ${n.read ? '' : 'unread'}" id="notif-${n.id}">
      <div class="notif-icon ${n.type}">${n.icon}</div>
      <div class="notif-body">
        <div class="notif-title">${n.title}</div>
        <div class="notif-desc">${n.desc}</div>
        <div class="notif-meta">
          <span>${n.time}</span>
          ${!n.read ? `<span style="color:var(--primary);cursor:pointer;font-weight:600" onclick="markRead(${n.id})">Отметить прочитанным</span>` : ''}
        </div>
        ${chBadges ? `<div class="notif-channels">${chBadges}</div>` : ''}
      </div>
    </div>`;
  }).join('');
}

function markRead(id) {
  const notif = STATE.notifications.find(n => n.id === id);
  if (notif) {
    notif.read = true;
    renderNotifications();
  }
}

function markAllRead() {
  STATE.notifications.forEach(n => n.read = true);
  renderNotifications();
}

/* ================= СТАРТ ================= */
// Генерируем пару уведомлений при загрузке для примера
window.onload = () => {
  STATE.channels = { inapp: true, email: true, telegram: false, max: false };
  
  // Восстанавливаем UI чекбоксов
  document.getElementById('ch-email').checked = true;
  document.querySelectorAll('.channel-item')[1].classList.add('active');
  
  simulateEvent('reg');
  setTimeout(() => simulateEvent('protocol'), 1000);
};
</script>
</body>
</html>