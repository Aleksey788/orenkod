<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="{{ asset('css/registration.css') }}">
    <style>
        .hidden { display: none !important; }

        .session-banner {
            display: flex; justify-content: space-between; align-items: center;
            gap: 12px; padding: 12px 16px; margin-bottom: 16px;
            background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px;
            font-size: 14px; color: #1d4ed8; flex-wrap: wrap;
        }
        .btn-inline {
            padding: 6px 12px; border: 1px solid #2563eb; background: #2563eb; color: #fff;
            border-radius: 8px; font: inherit; font-size: 12px; cursor: pointer;
        }
        .btn-inline.btn-ghost { background: #fff; color: #2563eb; }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(15,23,42,.55);
            backdrop-filter: blur(4px); display: none; align-items: center;
            justify-content: center; z-index: 1100; padding: 20px;
        }
        .modal-overlay.show { display: flex; }
        .modal-card {
            background: #fff; border-radius: 20px; padding: 24px;
            max-width: 440px; width: 100%;
        }
        .modal-card h3 { margin: 0 0 6px; font-size: 18px; }
        .modal-sub { color: #64748b; font-size: 13px; margin: 0 0 14px; }
        .modal-card input {
            width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0;
            border-radius: 10px; font: inherit; font-size: 14px;
            margin-bottom: 10px; outline: none;
        }
        .modal-card input:focus { border-color: #2563eb; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
        .btn-primary {
            padding: 9px 16px; background: #2563eb; color: #fff; border: none;
            border-radius: 10px; font: inherit; font-size: 13px;
            font-weight: 500; cursor: pointer;
        }
        .btn-ghost {
            padding: 9px 16px; background: #fff; color: #0f172a;
            border: 1px solid #e2e8f0; border-radius: 10px;
            font: inherit; font-size: 13px; cursor: pointer;
        }
        .login-error { color: #b91c1c; font-size: 12px; margin-bottom: 8px; }
        .login-hint  { color: #64748b; font-size: 12px; margin-bottom: 8px; }
        .role-picker { margin-top: 6px; }
        .role-picker p { margin: 0 0 8px; font-size: 13px; color: #64748b; }
        .btn-role {
            display: block; width: 100%; text-align: left;
            padding: 10px 14px; background: #fff;
            border: 1px solid #e2e8f0; border-radius: 10px;
            font: inherit; cursor: pointer; margin-bottom: 8px;
        }
        .btn-role:hover { border-color: #2563eb; background: #eff6ff; }
        .pw-hint { display: block; color: #64748b; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>

    <div class="register-container">

        <div id="sessionBanner" class="session-banner hidden">
            <span id="sessionBannerText"></span>
            <div>
                <button type="button" id="sessionGoBtn" class="btn-inline">Перейти в кабинет</button>
                <button type="button" id="sessionLogoutBtn" class="btn-inline btn-ghost">Выйти</button>
            </div>
        </div>

        <form id="registerForm">
            <h2>Регистрация</h2>

            <div class="input-group">
                <label for="mainRole">Кто вы?</label>
                <select id="mainRole" name="mainRole" required>
                    <option value="" disabled selected>-- Выберите вашу роль --</option>
                    <option value="athlete">Я - Спортсмен</option>
                    <option value="coach">Я - Тренер</option>
                    <option value="judge">Я - Судья</option>
                    <option value="federation">Я - Член Федерации</option>
                    <option value="multiple">У меня несколько ролей</option>
                </select>
            </div>

            <div id="multipleRolesBlock" class="hidden multi-role-box">
                <p>Отметьте все подходящие роли:</p>
                <label><input type="checkbox" class="multi-role-cb" value="athlete"> Спортсмен</label>
                <label><input type="checkbox" class="multi-role-cb" value="coach"> Тренер</label>
                <label><input type="checkbox" class="multi-role-cb" value="judge"> Судья</label>
                <label><input type="checkbox" class="multi-role-cb" value="federation"> Член Федерации</label>
            </div>

            <div class="input-group">
                <label for="SecondName">Фамилия</label>
                <input type="text" id="SecondName" name="SecondName" required autocomplete="family-name">
            </div>

            <div class="input-group">
                <label for="Name">Имя</label>
                <input type="text" id="Name" name="Name" required autocomplete="given-name">
            </div>

            <div class="input-group" id="patronymicGroup">
                <label for="Patronymic">Отчество (при наличии)</label>
                <input type="text" id="Patronymic" name="Patronymic" autocomplete="additional-name">
            </div>

            <div class="input-group">
                <label for="email">Электронная почта</label>
                <input type="email" id="email" name="email" required autocomplete="email">
            </div>

            <!-- НОВОЕ: пароль -->
            <div class="input-group">
                <label for="Password">Пароль <span class="req">*</span></label>
                <input type="password" id="Password" name="Password" required minlength="6"
                       autocomplete="new-password" placeholder="Минимум 6 символов">
                <small class="pw-hint">Не менее 6 символов. Рекомендуем буквы и цифры.</small>
            </div>

            <div class="input-group">
                <label for="PasswordConfirm">Повторите пароль <span class="req">*</span></label>
                <input type="password" id="PasswordConfirm" name="PasswordConfirm" required minlength="6"
                       autocomplete="new-password" placeholder="Ещё раз тот же пароль">
            </div>

            <div class="input-group" id="birthdateGroup">
                <label for="Birthdate">Дата рождения</label>
                <input type="date" id="Birthdate" name="Birthdate" required min="1900-01-01" max="2026-12-31">
            </div>

            <div class="checkbox-group">
                <input type="checkbox" id="terms" required>
                <label for="terms">Я согласен с обработкой персональных данных</label>
            </div>

            <button type="submit" class="registerbtn">Зарегистрироваться</button>

            <div class="signin-link">
                Уже есть аккаунт? <a href="#" id="openLoginLink">Войти</a>
            </div>
        </form>
    </div>

    <!-- ===== Модалка входа ===== -->
    <div id="loginOverlay" class="modal-overlay">
        <div class="modal-card">
            <h3>Вход в личный кабинет</h3>
            <p class="modal-sub">Введите email и пароль, указанные при регистрации.</p>

            <input type="email" id="loginEmail" placeholder="email@example.com" autocomplete="email">
            <!-- НОВОЕ: пароль -->
            <input type="password" id="loginPassword" placeholder="Пароль" autocomplete="current-password">

            <div id="loginHint"  class="login-hint hidden"></div>
            <div id="loginError" class="login-error hidden"></div>

            <div id="rolePicker" class="role-picker hidden">
                <p>У аккаунта несколько ролей. Куда войти?</p>
                <div id="rolePickerList"></div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-ghost"   id="loginCancelBtn">Отмена</button>
                <button type="button" class="btn-primary" id="loginSubmitBtn">Войти</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/Registr.js') }}"></script>
</body>
</html>