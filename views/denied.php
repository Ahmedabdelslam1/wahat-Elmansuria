<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>غير مصرح - <?= e(APP_NAME) ?></title>
  <style>
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      text-align: center;
      padding: 60px 24px;
      direction: rtl;
      background: radial-gradient(circle at 30% 0%, #23233a 0%, #15151f 55%);
      color: #ff9500;
      min-height: 100vh;
    }
    h1 { font-size: 22px; margin-bottom: 10px; }
    p { color: #cfcfe0; margin-bottom: 20px; }
    a {
      display: inline-block;
      color: #fff;
      background: linear-gradient(90deg,#ff3b30,#d32f2f);
      padding: 12px 28px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
      box-shadow: 0 10px 24px -6px rgba(255,59,48,0.5);
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <h1>🚫 غير مصرح بالدخول</h1>
  <p>ليس لديك صلاحية لعرض هذه الصفحة (أو انتهت الجلسة)</p>
  <a href="?page=login">تسجيل الدخول</a>
</body>
</html>
