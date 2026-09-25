<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>تسجيل الدخول - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{ --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500; --dark:#15151f; --dark2:#1f1f2c; }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: radial-gradient(circle at 30% 0%, #23233a 0%, var(--dark) 55%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      padding: 20px;
    }
    .back-fab {
      position: fixed; top: 16px; right: 16px;
      width: 40px; height: 40px; border-radius: 50%;
      background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);
      display: flex; align-items: center; justify-content: center;
      color: #fff; text-decoration: none;
    }
    .back-fab svg { width: 18px; height: 18px; }
    .card {
      background: var(--dark2);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 22px;
      padding: 40px 30px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.5);
      text-align: center;
    }
    .logo-circle {
      width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 14px;
      background: linear-gradient(135deg, var(--primary), var(--accent2));
      display: flex; align-items: center; justify-content: center; font-size: 28px;
      box-shadow: 0 10px 26px -6px rgba(255,59,48,0.6);
    }
    .logo { font-size: 20px; color: #fff; font-weight: 800; margin-bottom: 4px; }
    .subtitle { color: var(--accent2); font-size: 12.5px; margin-bottom: 22px; }
    .badge {
      display: inline-block;
      background: rgba(255,149,0,0.15);
      color: var(--accent2);
      padding: 5px 16px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 800;
      margin-bottom: 25px;
    }
    input {
      width: 100%;
      padding: 14px 16px;
      margin-bottom: 14px;
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 12px;
      background: #14141c;
      color: #fff;
      font-family: inherit;
      font-size: 15px;
      outline: none;
      transition: border 0.2s;
    }
    input::placeholder { color: #6b6b7a; }
    input:focus { border-color: var(--primary); }
    button.submit {
      width: 100%;
      padding: 14px;
      background: linear-gradient(90deg, var(--primary), var(--primary-dark));
      color: #fff;
      border: none;
      border-radius: 12px;
      font-family: inherit;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 6px;
      box-shadow: 0 10px 24px -6px rgba(255,59,48,0.5);
      transition: transform 0.15s;
    }
    button.submit:active { transform: scale(0.97); }
    button.submit:disabled { opacity: 0.6; cursor: wait; }
    .error {
      background: rgba(255,59,48,0.15);
      color: #ff8a80;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      display: none;
      font-size: 13px;
    }
    .guest { margin-top: 18px; font-size: 13px; color: #9a9aad; }
    .guest a { color: var(--accent2); text-decoration: none; font-weight: 800; }
    .roles-hint { margin-top: 18px; font-size: 10.5px; color: #6b6b7a; line-height: 1.7; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <a class="back-fab" href="?page=menu" title="رجوع للمنيو">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
  </a>
  <div class="card">
    <div class="logo-circle">🌴</div>
    <div class="logo"><?= e(APP_NAME) ?></div>
    <div class="subtitle">أصل المندي والمشوي</div>
    <div class="badge">دخول المستخدمين والأدمن</div>

    <div class="error" id="errorMsg"></div>

    <input type="text" id="username" placeholder="اسم المستخدم" autocomplete="username">
    <input type="password" id="password" placeholder="كلمة المرور" autocomplete="current-password">
    <button class="submit" id="loginBtn" onclick="doLogin()">تسجيل الدخول</button>

    <div class="guest">
      أو <a href="?page=menu">تصفح المنيو كزائر</a>
    </div>
  </div>

  <script>
    async function doLogin() {
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value;
      const err = document.getElementById('errorMsg');
      const btn = document.getElementById('loginBtn');
      err.style.display = 'none';

      if (!username || !password) {
        err.textContent = 'أدخل اسم المستخدم وكلمة المرور';
        err.style.display = 'block';
        return;
      }

      btn.disabled = true;
      try {
        const res = await api('login', { username, password });
        btn.disabled = false;
        if (res.success) {
          window.location.href = '?page=' + res.redirect;
        } else {
          err.textContent = res.message || 'فشل تسجيل الدخول';
          err.style.display = 'block';
        }
      } catch (e) {
        btn.disabled = false;
        err.textContent = 'خطأ في الاتصال';
        err.style.display = 'block';
      }
    }

    const enterLogin = e => { if (e.key === 'Enter') doLogin(); };
    document.getElementById('password').addEventListener('keypress', enterLogin);
    document.getElementById('username').addEventListener('keypress', enterLogin);
  </script>
</body>
</html>
