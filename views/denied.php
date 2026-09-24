<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>غير مصرح - <?= e(APP_NAME) ?></title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      text-align: center;
      padding: 60px;
      direction: rtl;
      background: #1a0f08;
      color: #FFD700;
      min-height: 100vh;
    }
    a { color: #FFD700; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <h1>🚫 غير مصرح بالدخول</h1>
  <p>ليس لديك صلاحية لعرض هذه الصفحة (أو انتهت الجلسة)</p>
  <a href="?page=login">تسجيل الدخول</a>
</body>
</html>
