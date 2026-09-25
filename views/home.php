<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title><?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2; --radius:18px;
    }
    body { font-family:'Tajawal','Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); padding-bottom:30px; }

    .topbar {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      padding: 12px 16px;
      display:flex; align-items:center; justify-content:flex-end; gap:10px;
      position:sticky; top:0; z-index:100;
      box-shadow:0 4px 18px rgba(0,0,0,0.25);
    }
    .icon-btn {
      width:38px; height:38px; border-radius:50%;
      background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.14);
      display:flex; align-items:center; justify-content:center;
      color:#fff; text-decoration:none; flex-shrink:0; position:relative;
    }
    .icon-btn svg { width:18px; height:18px; }
    .icon-btn .tip {
      position:absolute; top:44px; left:50%; transform:translateX(-50%);
      background:var(--dark2); color:#fff; font-size:10px; font-weight:700;
      padding:3px 8px; border-radius:6px; white-space:nowrap;
      opacity:0; pointer-events:none; transition:opacity .2s;
    }
    .icon-btn:hover .tip { opacity:1; }

    /* ===== البطل + الساعة ===== */
    .hero {
      background: radial-gradient(circle at 50% -20%, #2d2d44 0%, var(--dark) 60%);
      padding: 28px 16px 90px;
      text-align:center;
      position:relative;
    }
    .hero .logo-circle {
      width:66px; height:66px; border-radius:50%; margin:0 auto 12px;
      background:linear-gradient(135deg,var(--primary),var(--accent2));
      display:flex; align-items:center; justify-content:center; font-size:30px;
      box-shadow:0 10px 26px -6px rgba(255,59,48,0.6);
    }
    .hero h1 { color:#fff; font-size:24px; font-weight:800; }
    .hero .sub { color:var(--accent2); font-size:13px; margin-top:2px; }

    .clock-card {
      position:absolute; bottom:-70px; left:50%; transform:translateX(-50%);
      background:var(--surface);
      border-radius:22px;
      padding:14px 22px 16px;
      box-shadow:0 18px 40px -10px rgba(0,0,0,0.28);
      width:min(320px, calc(100% - 32px));
      text-align:center;
      border:1px solid var(--border);
    }
    .analog { width:110px; height:110px; margin:0 auto 8px; position:relative; }
    .analog svg { width:100%; height:100%; }
    .digital { font-size:22px; font-weight:800; letter-spacing:1px; direction:ltr; }
    .today { font-size:12px; color:var(--muted); margin-top:2px; font-weight:700; }

    .bellies { text-align:center; padding:86px 16px 6px; }
    .section-title { font-size:16px; font-weight:800; text-align:center; margin-bottom:12px; }

    /* ===== زر المنيو ===== */
    .menu-cta {
      display:flex; align-items:center; justify-content:center; gap:10px;
      background:linear-gradient(90deg,var(--primary),var(--primary-dark));
      color:#fff; text-decoration:none;
      margin:0 auto 14px; width:fit-content;
      padding:14px 34px; border-radius:16px;
      font-weight:800; font-size:15px;
      box-shadow:0 10px 26px -6px rgba(255,59,48,0.55);
      animation:pulse 2.2s infinite;
    }
    @keyframes pulse {
      0%,100% { transform:scale(1); }
      50% { transform:scale(1.04); }
    }

    /* ===== شريط الصور المتحرك ===== */
    .strip-wrap { overflow:hidden; position:relative; padding:10px 0 4px; }
    .strip {
      display:flex; gap:12px;
      width:max-content;
      animation: slide 30s linear infinite;
    }
    .strip-wrap:hover .strip { animation-play-state:paused; }
    @keyframes slide { to { transform:translateX(50%); } }
    .strip .photo {
      width:180px; height:130px; border-radius:14px; overflow:hidden;
      flex-shrink:0; border:1px solid var(--border);
      box-shadow:0 8px 22px -8px rgba(20,20,30,0.35);
      position:relative;
    }
    .strip .photo img { width:100%; height:100%; object-fit:cover; display:block; }
    .strip .photo span {
      position:absolute; bottom:0; right:0; left:0;
      background:linear-gradient(0deg,rgba(0,0,0,0.65),transparent);
      color:#fff; font-size:11px; font-weight:700; padding:14px 8px 6px; text-align:center;
    }

    /* ===== الأقسام ===== */
    .section { padding:18px 16px; }
    .card-box {
      background:var(--surface); border:1px solid var(--border);
      border-radius:var(--radius); padding:16px;
      box-shadow:0 2px 10px rgba(20,20,30,0.04);
      margin-bottom:12px;
    }
    .card-box h2 { font-size:15px; font-weight:800; margin-bottom:10px; display:flex; align-items:center; gap:6px; }
    .card-box h2 .ico { width:28px; height:28px; border-radius:8px; background:var(--bg); display:inline-flex; align-items:center; justify-content:center; font-size:14px; }

    .contact-row { display:flex; align-items:center; gap:10px; padding:7px 0; font-size:13.5px; }
    .contact-row .ci { width:34px; height:34px; border-radius:10px; background:#fff2f0; display:flex; align-items:center; justify-content:center; font-size:15px; flex-shrink:0; }
    .contact-row a { color:var(--text); text-decoration:none; font-weight:700; }

    .about-grid { display:grid; grid-template-columns:110px 1fr; gap:12px; align-items:start; }
    .about-grid img { width:110px; height:110px; object-fit:cover; border-radius:14px; }
    .about-grid p { font-size:13px; line-height:1.8; color:#4a4a58; }

    .feature-chips { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .chip { background:var(--bg); border:1px solid var(--border); color:var(--text); font-size:11.5px; font-weight:700; padding:6px 12px; border-radius:20px; }

    /* ===== آراء المستخدمين ===== */
    .reviews { display:flex; gap:10px; overflow-x:auto; padding:4px 2px 10px; }
    .reviews::-webkit-scrollbar{ display:none; }
    .review {
      flex-shrink:0; width:230px;
      background:var(--surface); border:1px solid var(--border);
      border-radius:16px; padding:14px;
      box-shadow:0 2px 10px rgba(20,20,30,0.04);
    }
    .review .stars { color:var(--accent2); font-size:12px; letter-spacing:1px; margin-bottom:6px; }
    .review p { font-size:12px; color:#4a4a58; line-height:1.7; margin-bottom:10px; }
    .review .who { display:flex; align-items:center; gap:8px; }
    .review .avatar {
      width:30px; height:30px; border-radius:50%;
      background:linear-gradient(135deg,var(--primary),var(--accent2));
      color:#fff; font-size:12px; font-weight:800;
      display:flex; align-items:center; justify-content:center;
    }
    .review .who b { font-size:12px; }
    .review .who small { display:block; color:var(--muted); font-size:10px; }

    .footer {
      text-align:center; padding:24px 16px 10px;
      color:var(--muted); font-size:11.5px; line-height:1.8;
    }
    .footer .heart { color:var(--primary); }
  </style>
</head>
<body>
  <div class="topbar">
    <a class="icon-btn" href="?page=login" title="دخول الأدمن والكاشير">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
      <span class="tip">دخول الأدمن / الكاشير</span>
    </a>
  </div>

  <div class="hero">
    <div class="logo-circle">🌴</div>
    <h1><?= e(APP_NAME) ?></h1>
    <div class="sub">أصل المندي والمشوي • المنصورية</div>

    <div class="clock-card">
      <div class="analog">
        <svg viewBox="0 0 100 100">
          <circle cx="50" cy="50" r="48" fill="#15151f"/>
          <circle cx="50" cy="50" r="48" fill="none" stroke="#ececf2" stroke-width="2"/>
          <g fill="#cfcfe0" font-size="9" font-family="Tajawal" font-weight="700" text-anchor="middle">
            <text x="50" y="16">١٢</text>
            <text x="86" y="53">٣</text>
            <text x="50" y="90">٦</text>
            <text x="14" y="53">٩</text>
          </g>
          <g stroke="#5a5a70" stroke-width="1.4">
            <line x1="50" y1="50" x2="50" y2="10" transform="rotate(30 50 50)" opacity="0"/>
          </g>
          <g id="ticks"></g>
          <line id="hourHand" x1="50" y1="50" x2="50" y2="29" stroke="#ffffff" stroke-width="4" stroke-linecap="round"/>
          <line id="minuteHand" x1="50" y1="50" x2="50" y2="19" stroke="#cfcfe0" stroke-width="2.6" stroke-linecap="round"/>
          <line id="secondHand" x1="50" y1="54" x2="50" y2="17" stroke="#ff3b30" stroke-width="1.4" stroke-linecap="round"/>
          <circle cx="50" cy="50" r="3" fill="#ff3b30"/>
          <circle cx="50" cy="50" r="1.4" fill="#fff"/>
        </svg>
      </div>
      <div class="digital" id="digital">--:--:--</div>
      <div class="today" id="todayDate">جاري تحميل التاريخ...</div>
    </div>
  </div>

  <div class="bellies">
    <a class="menu-cta" href="?page=menu">
      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path><path d="M7 2v20"></path><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"></path></svg>
      استعرض المنيو واطلب الآن
    </a>
  </div>

  <div class="section-title">🔥 من مطبخنا... مشويات وصواني على ذوقكم</div>
  <div class="strip-wrap">
    <div class="strip" id="photoStrip"></div>
  </div>

  <div class="section">
    <div class="card-box">
      <h2><span class="ico">📞</span> معلومات الاتصال</h2>
      <div class="contact-row"><span class="ci">📱</span><a href="tel:01153431728" dir="ltr">01153431728</a></div>
      <div class="contact-row"><span class="ci">💬</span><a href="https://wa.me/201153431728" target="_blank" rel="noopener">واتساب المطعم</a></div>
      <div class="contact-row"><span class="ci">📍</span><span>أول مدخل المنصورية بجوار مسجد عبدالرحيم زيدان</span></div>
      <div class="contact-row"><span class="ci">🕐</span><span>يوميًا من ١٢ ظهرًا حتى ١٢ منتصف الليل</span></div>
    </div>

    <div class="card-box">
      <h2><span class="ico">🌴</span> عن المطعم</h2>
      <div class="about-grid">
        <img src="https://media.base44.com/images/public/69f55aeb618a96592fa36b04/b3375988b_generated_image.png" alt="أجواء المطعم">
        <p>واحة المنصورية مطعم مصري شعبي متخصص في أصول المندي والكباب والكفتة والصواني العائلية. لحومنا طازجة يوميًا، ونحضّر طلبات المناسبات والسفور بأي حجم تطلبه.</p>
      </div>
      <div class="feature-chips">
        <span class="chip">✅ لحوم طازجة يوميًا</span>
        <span class="chip">🔥 مندي على الجمر</span>
        <span class="chip">🍱 سفور ومناسبات</span>
        <span class="chip">⚡ خدمة سريعة</span>
        <span class="chip">🧾 فاتورة إلكترونية</span>
      </div>
    </div>

    <div class="card-box" style="padding-bottom:6px">
      <h2><span class="ico">⭐</span> آراء عملائنا</h2>
      <div class="reviews">
        <div class="review">
          <div class="stars">★★★★★</div>
          <p>"أحلى فرخة مندي في المنصورية، الطعم زي بيتنا بالظبط والخدمة سريعة جدًا."</p>
          <div class="who"><span class="avatar">م</span><span><b>محمود س.</b><small>عميل دائم</small></span></div>
        </div>
        <div class="review">
          <div class="stars">★★★★★</div>
          <p>"طلبت صينية عزيمة للعيلة، الكمية كبيرة والكفتة تحفة. الفاتورة الإلكترونية فكرة جميلة."</p>
          <div class="who"><span class="avatar">أ</span><span><b>أحمد ع.</b><small>طلب سفرة</small></span></div>
        </div>
        <div class="review">
          <div class="stars">★★★★☆</div>
          <p>"الكباب مشوي على أصوله، والأسعار مناسبة جدًا مقابل الكمية. مكان يستاهل الزيارة."</p>
          <div class="who"><span class="avatar">ك</span><span><b>كريم ف.</b><small>زائر جديد</small></span></div>
        </div>
        <div class="review">
          <div class="stars">★★★★★</div>
          <p>"طاجن البامية لذيذ والمكرونة المبكبكة مستوى تاني خالص. بقى مطعمنا الثابت أيام الجمعة."</p>
          <div class="who"><span class="avatar">ه</span><span><b>هالة م.</b><small>عميلة</small></span></div>
        </div>
      </div>
    </div>
  </div>

  <div class="footer">
    <?= e(APP_NAME) ?> • أول مدخل المنصورية بجوار مسجد عبدالرحيم زيدان<br>
    صُنع بـ <span class="heart">♥</span> لعشاق المندي والمشويات
  </div>

  <script>
    // ===== علامات الساعة =====
    (function(){
      const g = document.getElementById('ticks');
      for (let i = 0; i < 60; i++) {
        const big = i % 5 === 0;
        const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
        const len = big ? 6 : 2.5;
        line.setAttribute('x1', 50); line.setAttribute('y1', 6);
        line.setAttribute('x2', 50); line.setAttribute('y2', 6 + len);
        line.setAttribute('stroke', big ? '#9a9aad' : '#4a4a58');
        line.setAttribute('stroke-width', big ? 2 : 1);
        line.setAttribute('transform', `rotate(${i * 6} 50 50)`);
        g.appendChild(line);
      }
    })();

    // ===== تحديث الساعة (توقيت القاهرة) =====
    const timeFmt = new Intl.DateTimeFormat('en-GB', {
      timeZone: 'Africa/Cairo', hour12: false,
      hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
    const dateFmt = new Intl.DateTimeFormat('ar-EG', {
      timeZone: 'Africa/Cairo',
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });

    function tick() {
      const parts = timeFmt.formatToParts(new Date());
      const get = t => parseInt(parts.find(p => p.type === t).value, 10);
      let h = get('hour') % 12, m = get('minute'), s = get('second');
      document.getElementById('hourHand').setAttribute('transform', `rotate(${h * 30 + m * 0.5} 50 50)`);
      document.getElementById('minuteHand').setAttribute('transform', `rotate(${m * 6 + s * 0.1} 50 50)`);
      document.getElementById('secondHand').setAttribute('transform', `rotate(${s * 6} 50 50)`);
      document.getElementById('digital').textContent =
        String(get('hour')).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }
    tick();
    setInterval(tick, 1000);
    document.getElementById('todayDate').textContent = dateFmt.format(new Date());

    // ===== شريط الصور المتحرك =====
    const PHOTOS = [
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/a47e4af67_generated_image.png', label: 'فرخة مندي' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/dee9595a9_generated_image.png', label: 'كفتة وكباب مشوي' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/84cd4359d_generated_image.png', label: 'مندي لحم' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/1bea8941c_generated_image.png', label: 'صواني وسفور' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/874539e25_generated_image.png', label: 'طاجن بامية' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/bac04e273_generated_image.png', label: 'ساندوتش كفتة' },
      { src: 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/bbec758ec_generated_image.png', label: 'مكرونة مبكبكة' },
    ];
    const strip = document.getElementById('photoStrip');
    // نكرر الصور مرتين لعمل حركة لانهائية سلسة
    [...PHOTOS, ...PHOTOS].forEach(p => {
      const a = document.createElement('div');
      a.className = 'photo';
      const img = document.createElement('img');
      img.src = p.src; img.alt = p.label; img.loading = 'lazy';
      const span = document.createElement('span');
      span.textContent = p.label;
      a.append(img, span);
      strip.appendChild(a);
    });
  </script>
</body>
</html>
