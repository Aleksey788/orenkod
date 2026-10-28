/* ============================================================
   Регистрация + вход с паролем (с интеграцией бэкенда Laravel).
   ============================================================ */
(function () {
  const API_URL     = 'http://127.0.0.1:8000/api';
  const REG_KEY     = 'fsp_registered_users';
  const SESSION_KEY = 'fsp_user';
  const TOKEN_KEY   = 'fsp_auth_token'; // Ключ для токена Laravel Sanctum
  const PW_SALT     = 'fsp-salt-v1::';
  const PW_MIN      = 6;

  const CABINETS = {
    athlete:    { url: '/athlete',         label: 'Спортсмен' },
    coach:      { url: '/coach',          label: 'Тренер' },
    judge:      { url: '/judge',          label: 'Судья' },
    federation: { url: '/federation', label: 'Член Федерации' }
  };
  const ROLE_PRIORITY = ['athlete', 'coach', 'judge', 'federation'];

  /* ===================== УТИЛИТЫ ===================== */
  const readUsers = () => {
    try { return JSON.parse(localStorage.getItem(REG_KEY) || '[]'); }
    catch (e) { return []; }
  };
  const writeUsers = (arr) => localStorage.setItem(REG_KEY, JSON.stringify(arr));

  const findUserByEmail = (email) =>
    readUsers().find(u => (u.email || '').toLowerCase() === String(email).trim().toLowerCase());

  const updateUser = (user) => {
    const all = readUsers();
    const i = all.findIndex(u => u.id === user.id);
    if (i >= 0) all[i] = user;
    writeUsers(all);
  };

  const setSession   = (id) => localStorage.setItem(SESSION_KEY, JSON.stringify({ id }));
  const clearSession = ()   => {
    localStorage.removeItem(SESSION_KEY);
    localStorage.removeItem(TOKEN_KEY);
  };
  const getSession   = () => {
    try { return JSON.parse(localStorage.getItem(SESSION_KEY)); }
    catch (e) { return null; }
  };

  const sessionIdFor = (regUser, role) => 'reg-' + regUser.id + '-' + role;

  const parseSession = () => {
    const s = getSession();
    if (!s || !s.id) return null;
    if (!s.id.startsWith('reg-')) return null;
    const parts  = s.id.split('-');
    const role   = parts[parts.length - 1];
    const regId  = s.id.slice(4, s.id.length - role.length - 1);
    const regUser = readUsers().find(u => u.id === regId);
    if (!regUser) return null;
    return { regUser, role, cabinet: CABINETS[role] };
  };

  const pickRole = (regUser) => {
    if (!regUser.roles || !regUser.roles.length) return null;
    return [...regUser.roles].sort(
      (a, b) => ROLE_PRIORITY.indexOf(a) - ROLE_PRIORITY.indexOf(b)
    )[0];
  };

  const goToCabinet = (regUser, role) => {
    setSession(sessionIdFor(regUser, role));
    window.location.href = CABINETS[role].url;
  };

  /* ===================== ПАРОЛЬ ===================== */
  async function hashPassword(pw) {
    const raw = PW_SALT + String(pw);
    if (window.crypto && crypto.subtle && crypto.subtle.digest) {
      try {
        const buf = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(raw));
        return 'sha256:' + Array.from(new Uint8Array(buf))
          .map(b => b.toString(16).padStart(2, '0')).join('');
      } catch (e) { /* падать в fallback */ }
    }
    let h = 5381;
    for (let i = 0; i < raw.length; i++) h = ((h << 5) + h) ^ raw.charCodeAt(i);
    return 'fb:' + (h >>> 0).toString(16);
  }

  const hashEqual = (a, b) => a && b && a === b;

  /* ===================== DOM ===================== */
  const form      = document.getElementById('registerForm');
  const mainRole  = document.getElementById('mainRole');
  const multiBlk  = document.getElementById('multipleRolesBlock');
  const terms     = document.getElementById('terms');
  const emailIn   = document.getElementById('email');
  const pwIn      = document.getElementById('Password');
  const pw2In     = document.getElementById('PasswordConfirm');

  const banner     = document.getElementById('sessionBanner');
  const bannerText = document.getElementById('sessionBannerText');
  const bannerGo   = document.getElementById('sessionGoBtn');
  const bannerOut  = document.getElementById('sessionLogoutBtn');

  function renderSessionBanner() {
    if (!banner) return;
    const parsed = parseSession();
    if (!parsed) { banner.classList.add('hidden'); return; }
    const { regUser, cabinet } = parsed;
    const name = [regUser.lastName, regUser.firstName].filter(Boolean).join(' ') || regUser.email;
    bannerText.textContent = `Вы уже вошли как ${name} (${cabinet.label}).`;
    banner.classList.remove('hidden');
    bannerGo.onclick  = () => { window.location.href = cabinet.url; };
    bannerOut.onclick = () => { clearSession(); renderSessionBanner(); };
  }

  if (mainRole) {
    mainRole.addEventListener('change', () => {
      if (mainRole.value === 'multiple') {
        multiBlk.classList.remove('hidden');
      } else {
        multiBlk.classList.add('hidden');
        document.querySelectorAll('.multi-role-cb').forEach(cb => (cb.checked = false));
      }
    });
  }

  /* ===================== РЕГИСТРАЦИЯ С ОТПРАВКОЙ НА БЭКЕНД ===================== */
  if (form) form.addEventListener('submit', async (e) => {
    e.preventDefault();

    let roles = [];
    if (mainRole.value === 'multiple') {
      roles = Array.from(document.querySelectorAll('.multi-role-cb:checked')).map(cb => cb.value);
      if (roles.length < 2) { alert('Для варианта «несколько ролей» отметьте минимум 2 роли.'); return; }
    } else if (mainRole.value) {
      roles = [mainRole.value];
    } else {
      alert('Выберите роль.');
      return;
    }

    if (!terms.checked) {
      alert('Необходимо согласие на обработку персональных данных.');
      return;
    }

    const password  = pwIn.value;
    const password2 = pw2In.value;
    if (password.length < PW_MIN) {
      alert('Пароль должен содержать не менее ' + PW_MIN + ' символов.');
      pwIn.focus();
      return;
    }
    if (password !== password2) {
      alert('Пароли не совпадают.');
      pw2In.focus();
      return;
    }

    const email = emailIn.value.trim().toLowerCase();
    const lastName   = document.getElementById('SecondName').value.trim();
    const firstName  = document.getElementById('Name').value.trim();
    const patronymic = document.getElementById('Patronymic').value.trim();
    const birthdate  = document.getElementById('Birthdate').value;

    const existing = findUserByEmail(email);
    if (existing) {
      openLogin(existing.email);
      return;
    }

    // 1. Отправляем запрос на реальный бэкенд Laravel
    // Карта соответствия строковых ролей из формы к ID ролей в базе данных Laravel
    const roleMapping = {
      athlete: 1,
      coach: 2,
      judge: 3,
      federation: 4
    };

    // Если выбрано «несколько ролей», собираем их ID, иначе берем ID главной роли
    let roleIdsArray = [];
    if (mainRole.value === 'multiple') {
      roleIdsArray = roles.map(r => roleMapping[r]).filter(Boolean);
    } else {
      roleIdsArray = [roleMapping[roles[0]] || 1];
    }

    // 1. Отправляем запрос на реальный бэкенд Laravel
    try {
      const response = await fetch(`${API_URL}/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          name: `${lastName} ${firstName}`.trim(),
          email: email,
          password: password,
          password_confirmation: password2,
          role_ids: roleIdsArray // Передаем корректный массив ID ролей для бэкенда
        })
      });
      
      const resData = await response.json();
      if (resData.token) {
        localStorage.setItem(TOKEN_KEY, resData.token); // Сохраняем токен Sanctum
      }
    } catch (err) {
      console.warn('Бэкенд недоступен, работаем через localStorage:', err);
    }

    // 2. Сохраняем локально для бесперебойной работы интерфейса
    const passwordHash = await hashPassword(password);
    const newUser = {
      id: Date.now().toString(36) + Math.random().toString(36).slice(2, 6),
      lastName, firstName, patronymic,
      email, passwordHash, birthdate, roles,
      registeredAt: new Date().toISOString()
    };

    const all = readUsers();
    all.push(newUser);
    writeUsers(all);

    const goNow = confirm(
      'Регистрация успешна!\n' +
      'Роли: ' + roles.map(r => (CABINETS[r] && CABINETS[r].label) || r).join(', ') + '\n\n' +
      'Перейти в личный кабинет сейчас?'
    );

    if (goNow) {
      goToCabinet(newUser, pickRole(newUser));
    } else {
      form.reset();
      multiBlk.classList.add('hidden');
      document.querySelectorAll('.multi-role-cb').forEach(cb => (cb.checked = false));
    }
  });

  /* ===================== ВХОД С БЭКЕНДОМ ===================== */
  const loginOverlay   = document.getElementById('loginOverlay');
  const loginEmail     = document.getElementById('loginEmail');
  const loginPassword  = document.getElementById('loginPassword');
  const loginError     = document.getElementById('loginError');
  const loginHint      = document.getElementById('loginHint');
  const loginSubmitBtn = document.getElementById('loginSubmitBtn');
  const loginCancelBtn = document.getElementById('loginCancelBtn');
  const rolePicker     = document.getElementById('rolePicker');
  const rolePickerList = document.getElementById('rolePickerList');

  function hideHints() {
    loginError.classList.add('hidden');
    loginHint.classList.add('hidden');
  }
  function showError(msg) {
    loginError.textContent = msg;
    loginError.classList.remove('hidden');
    loginHint.classList.add('hidden');
  }
  function showHint(msg) {
    loginHint.textContent = msg;
    loginHint.classList.remove('hidden');
    loginError.classList.add('hidden');
  }

  function openLogin(prefillEmail) {
    hideHints();
    rolePicker.classList.add('hidden');
    loginSubmitBtn.classList.remove('hidden');
    if (prefillEmail) loginEmail.value = prefillEmail;
    loginOverlay.classList.add('show');
    setTimeout(() => {
      if (!loginEmail.value) loginEmail.focus();
      else loginPassword.focus();
    }, 50);
  }
  function closeLogin() { loginOverlay.classList.remove('show'); }

  const openLoginLink = document.getElementById('openLoginLink');
  if (openLoginLink) {
    openLoginLink.addEventListener('click', (e) => { e.preventDefault(); openLogin(); });
  }

  function showRolePicker(regUser) {
    loginSubmitBtn.classList.add('hidden');
    rolePicker.classList.remove('hidden');
    rolePickerList.innerHTML = '';
    regUser.roles.forEach(r => {
      const c = CABINETS[r];
      if (!c) return;
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn-role';
      btn.textContent = c.label;
      btn.onclick = () => goToCabinet(regUser, r);
      rolePickerList.appendChild(btn);
    });
  }

  async function tryLogin() {
    hideHints();
    rolePicker.classList.add('hidden');
    loginSubmitBtn.classList.remove('hidden');

    const email    = loginEmail.value.trim().toLowerCase();
    const password = loginPassword.value;

    if (!email)    { showError('Введите email.'); return; }
    if (!password) { showError('Введите пароль.'); return; }

    // Пробуем авторизоваться через реальный API Laravel
    try {
      const res = await fetch(`${API_URL}/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ email, password })
      });
      const data = await res.json();
      if (data.token) {
        localStorage.setItem(TOKEN_KEY, data.token);
      }
    } catch (e) {
      console.warn('Сервер недоступен, проверяем локально');
    }

    // Проверка локального пользователя
    const user = findUserByEmail(email);
    if (!user) {
      showError('Аккаунт с таким email не найден. Зарегистрируйтесь.');
      return;
    }
    if (!user.roles || !user.roles.length) {
      showError('У аккаунта нет ролей. Обратитесь в поддержку.');
      return;
    }

    if (!user.passwordHash) {
      if (password.length < PW_MIN) {
        showHint('Аккаунт без пароля. Придумайте пароль не короче ' + PW_MIN + ' символов и нажмите «Войти».');
        return;
      }
      user.passwordHash = await hashPassword(password);
      updateUser(user);
    } else {
      const h = await hashPassword(password);
      if (!hashEqual(h, user.passwordHash)) {
        showError('Неверный пароль.');
        return;
      }
    }

    if (user.roles.length === 1) {
      goToCabinet(user, user.roles[0]);
    } else {
      showRolePicker(user);
    }
  }

  if (loginSubmitBtn) loginSubmitBtn.onclick = tryLogin;

  if (loginPassword) {
    loginPassword.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); tryLogin(); }
    });
  }
  if (loginEmail) {
    loginEmail.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); loginPassword.focus(); }
    });
  }

  if (loginCancelBtn) loginCancelBtn.onclick = closeLogin;
  if (loginOverlay) {
    loginOverlay.addEventListener('click', (e) => {
      if (e.target === loginOverlay) closeLogin();
    });
  }

  renderSessionBanner();
})();