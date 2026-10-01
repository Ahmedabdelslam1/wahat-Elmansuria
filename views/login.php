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
    :root{
      --primary:#ff3b30; --primary-dark:#c0281f; --accent2:#ffb020; --gold:#ffd54a;
    }
    html, body { overflow-x: hidden; }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      min-height: 100vh;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      padding: 20px;
      background: linear-gradient(120deg, #ff3b30, #ff7a1a, #ffb020, #ff3b30);
      background-size: 300% 300%;
      animation: bgShift 14s ease-in-out infinite;
    }
    @keyframes bgShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }
    body::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(circle at 25% 15%, rgba(255,255,255,0.22) 0%, transparent 45%),
                  radial-gradient(circle at 80% 85%, rgba(21,21,31,0.35) 0%, transparent 55%);
      pointer-events: none;
    }
    /* أطباق طائرة متحركة في الخلفية */
    .float-food {
      position: absolute;
      font-size: 34px;
      filter: drop-shadow(0 6px 10px rgba(0,0,0,0.25));
      opacity: 0.85;
      animation: floatY 6s ease-in-out infinite;
      pointer-events: none;
      user-select: none;
      z-index: 1;
    }
    .float-food.f2 { animation-duration: 7.5s; }
    .float-food.f3 { animation-duration: 5.2s; }
    .float-food.f4 { animation-duration: 8s; }
    .float-food.f5 { animation-duration: 6.6s; }
    .float-food.f6 { animation-duration: 7s; }
    @keyframes floatY {
      0%, 100% { transform: translateY(0) rotate(-6deg); }
      50% { transform: translateY(-22px) rotate(6deg); }
    }
    .ff1 { top: 8%;  left: 8%;  }
    .ff2 { top: 14%; right: 10%; }
    .ff3 { top: 46%; left: 4%;  font-size: 28px; }
    .ff4 { bottom: 12%; right: 6%; }
    .ff5 { bottom: 8%;  left: 12%; font-size: 30px; }
    .ff6 { top: 68%; right: 18%; font-size: 26px; }
    @media (max-width: 480px) {
      .float-food { font-size: 26px; }
      .ff3, .ff5, .ff6 { display: none; }
    }

    .back-fab {
      position: fixed; top: 16px; right: 16px; z-index: 5;
      width: 42px; height: 42px; border-radius: 50%;
      background: rgba(0,0,0,0.22); border: 1px solid rgba(255,255,255,0.3);
      display: flex; align-items: center; justify-content: center;
      color: #fff; text-decoration: none;
      backdrop-filter: blur(6px);
    }
    .back-fab svg { width: 18px; height: 18px; }

    .card {
      position: relative; z-index: 2;
      background: rgba(21,21,31,0.72);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,0.16);
      border-radius: 26px;
      padding: 42px 28px 34px;
      width: 100%;
      max-width: 380px;
      box-shadow: 0 25px 70px rgba(0,0,0,0.45), 0 0 0 1px rgba(255,176,32,0.15);
      text-align: center;
    }
    .logo-ring {
      width: 92px; height: 92px; border-radius: 50%; margin: 0 auto 16px;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--primary), var(--accent2));
      padding: 4px;
      animation: pulseRing 2.6s ease-in-out infinite;
    }
    @keyframes pulseRing {
      0%, 100% { box-shadow: 0 0 0 0 rgba(255,176,32,0.55); }
      50% { box-shadow: 0 0 0 12px rgba(255,176,32,0); }
    }
    .logo-circle {
      width: 100%; height: 100%; border-radius: 50%;
      background: #fff;
      display: flex; align-items: center; justify-content: center; overflow: hidden;
    }
    .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
    .logo {
      font-size: 21px; color: #fff; font-weight: 800; margin-bottom: 4px;
      background: linear-gradient(90deg, #fff, var(--gold));
      -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
    }
    .subtitle { color: var(--accent2); font-size: 12.5px; margin-bottom: 20px; font-weight: 700; }
    .badge {
      display: inline-block;
      background: linear-gradient(90deg, rgba(255,176,32,0.22), rgba(255,59,48,0.22));
      border: 1px solid rgba(255,176,32,0.35);
      color: var(--gold);
      padding: 6px 18px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 800;
      margin-bottom: 26px;
    }
    input {
      width: 100%;
      padding: 15px 16px;
      margin-bottom: 14px;
      border: 1px solid rgba(255,255,255,0.14);
      border-radius: 13px;
      background: rgba(0,0,0,0.28);
      color: #fff;
      font-family: inherit;
      font-size: 15px;
      outline: none;
      transition: border 0.2s, box-shadow 0.2s;
    }
    input::placeholder { color: #b9b9c8; }
    input:focus { border-color: var(--accent2); box-shadow: 0 0 0 3px rgba(255,176,32,0.18); }
    button.submit {
      width: 100%;
      padding: 15px;
      background: linear-gradient(90deg, var(--primary), var(--accent2));
      background-size: 200% auto;
      color: #fff;
      border: none;
      border-radius: 13px;
      font-family: inherit;
      font-size: 15.5px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 6px;
      box-shadow: 0 12px 28px -6px rgba(255,59,48,0.55);
      transition: transform 0.15s, background-position 0.4s;
    }
    button.submit:hover { background-position: 100% center; }
    button.submit:active { transform: scale(0.97); }
    button.submit:disabled { opacity: 0.6; cursor: wait; }
    .error {
      background: rgba(255,59,48,0.22);
      border: 1px solid rgba(255,59,48,0.4);
      color: #ffd7d2;
      padding: 10px;
      border-radius: 10px;
      margin-bottom: 15px;
      display: none;
      font-size: 13px;
      font-weight: 700;
    }
    .guest { margin-top: 20px; font-size: 13px; color: #d8d8e2; }
    .guest a { color: var(--gold); text-decoration: none; font-weight: 800; }
    .roles-hint { margin-top: 18px; font-size: 10.5px; color: #9a9aad; line-height: 1.7; }
      .live-clock-bar { text-align:center; font-size:10px; color:var(--muted); padding:4px 0; background:var(--surface); border-bottom:1px solid var(--border); font-weight:700; letter-spacing:.2px; position:sticky; top:0; z-index:60; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="live-clock-bar" id="liveClockBar">—</div>
  <span class="float-food ff1 f1">🍖</span>
  <span class="float-food ff2 f2">🍚</span>
  <span class="float-food ff3 f3">🥙</span>
  <span class="float-food ff4 f4">🍗</span>
  <span class="float-food ff5 f5">🌶️</span>
  <span class="float-food ff6 f6">🔥</span>

  <a class="back-fab" href="?page=home" title="رجوع للرئيسية">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
  </a>
  <div class="card">
    <div class="logo-ring"><div class="logo-circle"><img src="assets/icons/icon-512.png" alt="شعار <?= e(APP_NAME) ?>"></div></div>
    <div class="logo"><?= e(APP_NAME) ?></div>
    <div class="subtitle">أصل المندي والمشوي 🔥</div>
    <div class="badge">دخول المستخدمين والأدمن</div>

    <div class="error" id="errorMsg"></div>

    <input type="text" id="username" placeholder="اسم المستخدم" autocomplete="username">
    <input type="password" id="password" placeholder="كلمة المرور" autocomplete="current-password">
    <button class="submit" id="loginBtn" onclick="doLogin()">تسجيل الدخول</button>

    <div class="guest">
      أو <a href="?page=menu">تصفح المنيو كزائر</a> · <a href="?page=home">الرئيسية</a>
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
  <script>
    function updateLiveClock() {
      var el = document.getElementById('liveClockBar');
      if (!el) return;
      var now = new Date();
      var d = now.toLocaleDateString('ar-EG', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      var t = now.toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      el.textContent = d + ' — ' + t;
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);
  </script>
</body>
</html>
