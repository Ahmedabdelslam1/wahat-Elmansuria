<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <title>تسجيل الدخول - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: linear-gradient(160deg, #1a0f08 0%, #3e2723 50%, #5d4037 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
    }
    .card {
      background: #2c1810;
      border: 2px solid #d4af37;
      border-radius: 20px;
      padding: 40px 30px;
      width: 90%;
      max-width: 380px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.5);
      text-align: center;
    }
    .logo { font-size: 28px; color: #d4af37; font-weight: bold; margin-bottom: 6px; }
    .subtitle { color: #ffe082; font-size: 13px; margin-bottom: 25px; }
    .badge {
      display: inline-block;
      background: #d4af37;
      color: #1a0f08;
      padding: 6px 18px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      margin-bottom: 25px;
    }
    input {
      width: 100%;
      padding: 14px 16px;
      margin-bottom: 14px;
      border: 1px solid #5d4037;
      border-radius: 12px;
      background: #1a0f08;
      color: #fff;
      font-size: 15px;
      outline: none;
      transition: border 0.2s;
    }
    input:focus { border-color: #d4af37; }
    button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(90deg, #d4af37, #f5c542);
      color: #1a0f08;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 8px;
      transition: transform 0.15s;
    }
    button:active { transform: scale(0.97); }
    button:disabled { opacity: 0.6; cursor: wait; }
    .error {
      background: #b71c1c;
      color: #fff;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 15px;
      display: none;
      font-size: 13px;
    }
    .guest { margin-top: 18px; font-size: 13px; color: #ffe082; }
    .guest a { color: #d4af37; text-decoration: none; font-weight: bold; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="card">
    <div class="logo">🌴 <?= e(APP_NAME) ?></div>
    <div class="subtitle">أصل المندي والمشوي</div>
    <div class="badge">فاتورتك إلكترونية</div>

    <div class="error" id="errorMsg"></div>

    <input type="text" id="username" placeholder="اسم المستخدم" autocomplete="username">
    <input type="password" id="password" placeholder="كلمة المرور" autocomplete="current-password">
    <button id="loginBtn" onclick="doLogin()">تسجيل الدخول</button>

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
