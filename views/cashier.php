<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>الكاشير - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2;
    }
    body { font-family:'Tajawal','Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); padding-bottom:20px; }
    .header {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      color: #fff; padding: 12px 16px;
      display: flex; justify-content: space-between; align-items: center;
      position: sticky; top: 0; z-index: 50; flex-wrap:wrap; gap:8px;
    }
    .header h1 { font-size: 15.5px; font-weight: 800; }
    .header .user { font-size: 11px; color: var(--accent2); }
    .logout, .sound-btn {
      background: transparent; border: 1px solid rgba(255,255,255,0.25); color: #fff;
      padding: 6px 12px; border-radius: 10px; font-size: 11.5px; cursor: pointer; font-family:inherit; font-weight:700;
    }
    .sound-btn.on { background:#34c759; border-color:#34c759; color:#05220f; }

    .today-bar {
      display:flex; gap:8px; padding:10px 12px; background:var(--surface); border-bottom:1px solid var(--border);
      overflow-x:auto;
    }
    .today-chip { flex-shrink:0; background:var(--bg); border-radius:12px; padding:8px 14px; text-align:center; min-width:88px; }
    .today-chip b { display:block; font-size:15px; font-weight:800; color:var(--primary); }
    .today-chip span { font-size:10px; color:var(--muted); font-weight:700; }

    .tabs {
      display: flex; background: var(--surface); border-bottom: 1px solid var(--border);
      position: sticky; top: 0; z-index: 45; overflow-x:auto;
    }
    .tab {
      flex-shrink:0; min-width:33.33%; padding: 11px 8px; text-align: center; border: none; background: none;
      font-size: 12px; font-family: inherit; font-weight: 700; cursor: pointer;
      color: var(--muted); border-bottom: 3px solid transparent;
    }
    .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
    .panel { display: none; }
    .panel.active { display: block; }

    .filters {
      display: flex; gap: 8px; padding: 10px 14px; overflow-x: auto; background: var(--surface); border-bottom: 1px solid var(--border);
    }
    .filter-btn {
      flex-shrink: 0; padding: 6px 13px; border-radius: 20px; border: none;
      background: var(--bg); font-size: 12px; font-family: inherit; font-weight: 700; cursor: pointer; color: var(--text);
    }
    .filter-btn.active { background: var(--primary); color: #fff; }
    .orders { padding: 10px 12px; display: flex; flex-direction: column; gap: 10px; }
    .order-card { background: var(--surface); border-radius: 16px; padding: 12px; box-shadow: 0 2px 10px rgba(20,20,30,0.05); border-right: 4px solid var(--primary); }
    .order-card.new { border-right-color: #ff3b30; }
    .order-card.preparing { border-right-color: #ff9500; }
    .order-card.ready { border-right-color: #34c759; }
    .order-card.done { border-right-color: #007aff; }
    .order-header { display: flex; justify-content: space-between; align-items:center; margin-bottom: 6px; flex-wrap:wrap; gap:6px; }
    .order-id { font-weight: 800; font-size: 13.5px; }
    .order-status { font-size: 10.5px; padding: 3px 9px; border-radius: 16px; background: var(--bg); font-weight:700; }
    .order-status.new { background: #ffebe9; color: #ff3b30; }
    .order-status.preparing { background: #fff3e0; color: #ef6c00; }
    .order-status.ready { background: #e7f8ec; color: #2e7d32; }
    .order-status.done { background: #e5f0ff; color: #007aff; }
    .type-tag { font-size:10px; font-weight:800; padding:3px 9px; border-radius:16px; }
    .type-tag.dinein { background:#e5f0ff; color:#007aff; }
    .type-tag.delivery { background:#fff3e0; color:#ff9500; }
    .order-meta { font-size: 11.5px; color: var(--muted); margin-bottom: 6px; }
    .order-items { font-size: 12.5px; margin-bottom: 8px; line-height: 1.5; }
    .order-total { font-weight: 800; color: var(--primary); font-size: 14px; margin-bottom: 8px; }
    .actions { display: flex; gap: 7px; flex-wrap: wrap; }
    .actions button { padding: 7px 12px; border-radius: 10px; border: none; font-size: 11.5px; cursor: pointer; font-weight: 700; font-family: inherit; }
    .btn-prep { background: #fff3e0; color: #ef6c00; }
    .btn-ready { background: #e7f8ec; color: #2e7d32; }
    .btn-done { background: #e5f0ff; color: #007aff; }
    .empty { text-align: center; padding: 40px; color: var(--muted); }

    /* المنيو (عرض فقط) */
    .menu-search { padding: 10px 14px 4px; }
    .menu-search input {
      width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 12px;
      font-family: inherit; font-size: 13px; background: var(--surface);
    }
    .menu-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 8px 12px 20px; }
    @media (min-width:600px) { .menu-grid { grid-template-columns: repeat(4,1fr); } }
    .mcard { background: var(--surface); border-radius: 12px; overflow: hidden; border: 1px solid var(--border); }
    .mcard .th { aspect-ratio: 1.2/1; overflow: hidden; }
    .mcard .th img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mcard .b { padding: 6px 7px; }
    .mcard h4 { font-size: 10.5px; font-weight: 800; margin-bottom: 3px; min-height: 27px; line-height:1.3; }
    .mcard .p { font-size: 11.5px; font-weight: 800; color: var(--primary); }
    .mcard .c { font-size: 8.5px; color: var(--muted); }

    /* ===== POS: طلب جديد ===== */
    .pos-wrap { display:flex; flex-direction:column; }
    .pos-search { padding:10px 12px 4px; }
    .pos-search input { width:100%; padding:9px 13px; border:1px solid var(--border); border-radius:12px; font-family:inherit; font-size:13px; }
    .pos-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; padding:8px 12px; }
    @media (min-width:600px) { .pos-grid { grid-template-columns:repeat(4,1fr); } }
    .pos-item { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; cursor:pointer; text-align:center; transition:transform .1s; }
    .pos-item:active { transform:scale(0.95); }
    .pos-item .th { aspect-ratio:1.2/1; overflow:hidden; }
    .pos-item .th img { width:100%; height:100%; object-fit:cover; display:block; }
    .pos-item .b { padding:5px 5px 7px; }
    .pos-item h4 { font-size:9.5px; font-weight:800; min-height:24px; line-height:1.25; margin-bottom:2px; }
    .pos-item .p { font-size:10.5px; font-weight:800; color:var(--primary); }

    .pos-cart {
      position:sticky; bottom:0; background:var(--surface); border-top:1px solid var(--border);
      box-shadow: 0 -6px 20px -8px rgba(20,20,30,0.15);
      padding: 10px 14px 14px; z-index: 40;
    }
    .pos-cart-list { max-height: 160px; overflow-y:auto; margin-bottom:8px; }
    .pos-cart-row { display:flex; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px dashed var(--border); font-size:12px; }
    .qty-ctl { display:flex; align-items:center; gap:8px; }
    .qty-ctl button { width:24px; height:24px; border-radius:8px; border:none; background:var(--bg); font-weight:800; cursor:pointer; }
    .pos-fields { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px; }
    .pos-fields input { padding:9px 11px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:12.5px; }
    .pos-address { display:none; margin-bottom:8px; }
    .pos-address input { width:100%; padding:9px 11px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:12.5px; }
    .pos-address.show { display:block; }
    .fee-rows { display:flex; justify-content:space-between; font-size:11px; color:var(--muted); font-weight:700; padding:2px 0 6px; }
    .fee-rows.delivery-on { color:#ef6c00; }
    .type-toggle { display:flex; gap:8px; margin-bottom:8px; }
    .type-toggle button {
      flex:1; padding:10px; border-radius:10px; border:1px solid var(--border); background:var(--bg);
      font-family:inherit; font-weight:800; font-size:12.5px; cursor:pointer; color:var(--text);
    }
    .type-toggle button.active.dinein { background:#e5f0ff; border-color:#007aff; color:#007aff; }
    .type-toggle button.active.delivery { background:#fff3e0; border-color:#ff9500; color:#ff9500; }
    .pos-submit-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }

    /* ===== طلبات الشركات ===== */
    .co-form { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:12px; }
    .co-form .co-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .co-form input, .co-form select {
      width:100%; padding:10px 11px; border:1px solid var(--border); border-radius:10px;
      font-family:inherit; font-size:12.5px; background:var(--bg);
    }
    .co-form input.wide { grid-column:1 / -1; }
    .co-form select { font-weight:800; }
    .co-live-total {
      grid-column:1 / -1; background:#fff3e0; border:1px dashed #ff9500; border-radius:10px;
      padding:8px 12px; font-size:12.5px; font-weight:800; color:#ef6c00;
      display:flex; justify-content:space-between; align-items:center;
    }
    .co-submit {
      grid-column:1 / -1; background:linear-gradient(90deg,#ef6c00,#e65100); color:#fff;
      border:none; padding:13px; border-radius:12px; font-weight:800; font-size:13.5px;
      cursor:pointer; font-family:inherit;
    }
    .co-submit:disabled { opacity:.5; cursor:wait; }
    .co-grand {
      display:flex; justify-content:space-between; background:var(--dark); color:#fff;
      border-radius:12px; padding:10px 14px; margin-bottom:10px; font-size:12.5px; font-weight:800;
    }
    .co-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; margin-bottom:8px; overflow:hidden; }
    .co-head { display:flex; justify-content:space-between; align-items:center; padding:11px 12px; cursor:pointer; gap:8px; flex-wrap:wrap; }
    .co-head .cname { font-weight:800; font-size:13.5px; color:var(--primary); text-decoration:underline; }
    .co-head .ctotal { font-weight:800; font-size:13px; }
    .co-body { display:none; border-top:1px dashed var(--border); padding:8px 12px; font-size:12px; }
    .co-body.show { display:block; }
    .co-row { display:flex; justify-content:space-between; gap:8px; padding:6px 0; border-bottom:1px dashed var(--border); flex-wrap:wrap; }
    .co-row:last-child { border-bottom:none; }
    .co-row .muted { color:var(--muted); font-size:11px; }
    .pos-total { font-size:16px; font-weight:800; color:var(--primary); }
    .pos-submit { flex:1; background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:#fff; border:none; padding:13px; border-radius:12px; font-weight:800; font-size:13.5px; cursor:pointer; font-family:inherit; }
    .pos-submit:disabled { opacity:.5; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>🧾 لوحة الكاشير</h1>
      <div class="user"><?= e($user['name']) ?> (<?= e($user['role']) ?>)</div>
    </div>
    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
      <?php if ($user['role'] === 'admin'): ?>
        <a href="?page=admin" style="color:#ff9500;font-size:11.5px;text-decoration:none;font-weight:700">لوحة المدير</a>
      <?php endif; ?>
      <button class="sound-btn" id="soundBtn" onclick="enableSound()">🔔 تفعيل التنبيه</button>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="today-bar">
    <div class="today-chip"><b id="tdOrders">-</b><span>طلبات اليوم</span></div>
    <div class="today-chip"><b id="tdSales">-</b><span>مبيعات اليوم (ج.م)</span></div>
    <div class="today-chip"><b id="tdNew">-</b><span>طلبات جديدة</span></div>
    <div class="today-chip"><b id="tdReady">-</b><span>جاهزة للتسليم</span></div>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="showTab('pos', this)">🧾 طلب جديد</button>
    <button class="tab" onclick="showTab('orders', this)">📋 الطلبات</button>
    <button class="tab" onclick="showTab('menu', this)">🍽️ المنيو</button>
    <button class="tab" onclick="showTab('companies', this)">🏢 شركات</button>
  </div>

  <!-- POS: طلب جديد -->
  <div class="panel active" id="panel-pos">
    <div class="pos-wrap">
      <div class="pos-search"><input id="posSearch" placeholder="بحث عن صنف..." oninput="renderPosGrid()"></div>
      <div class="pos-grid" id="posGrid"></div>
    </div>
    <div class="pos-cart">
      <div class="pos-cart-list" id="posCartList"><div class="empty" style="padding:14px">لم تُضف أي أصناف بعد</div></div>
      <div class="type-toggle">
        <button class="dinein active" id="btnDinein" onclick="setOrderType('صالة')">🍽️ صالة</button>
        <button class="delivery" id="btnDelivery" onclick="setOrderType('دليفري')">🛵 دليفري</button>
      </div>
      <div class="pos-fields">
        <input id="posCustomer" placeholder="اسم العميل (اختياري)">
        <input id="posPhone" placeholder="رقم الهاتف (لواتساب)">
      </div>
      <div class="pos-fields" style="grid-template-columns:1fr">
        <input id="posNotes" placeholder="ملاحظات (اختياري)">
      </div>
      <div class="pos-address" id="posAddressWrap">
        <input id="posAddress" placeholder="📍 عنوان التوصيل (إجباري للدليفري)">
      </div>
      <div id="feeRows"></div>
      <div class="pos-submit-row">
        <span class="pos-total" id="posTotal">0 ج.م</span>
        <button class="pos-submit" id="posSubmitBtn" onclick="submitPosOrder()">تأكيد الطلب</button>
      </div>
    </div>
  </div>

  <!-- الطلبات -->
  <div class="panel" id="panel-orders">
    <div class="filters">
      <button class="filter-btn active" onclick="loadOrders('all', this)">الكل</button>
      <button class="filter-btn" onclick="loadOrders('جديد', this)">جديد</button>
      <button class="filter-btn" onclick="loadOrders('قيد التحضير', this)">قيد التحضير</button>
      <button class="filter-btn" onclick="loadOrders('جاهز', this)">جاهز</button>
      <button class="filter-btn" onclick="loadOrders('تم التسليم', this)">تم التسليم</button>
    </div>
    <div class="orders" id="ordersList"><div class="empty">جاري التحميل...</div></div>
  </div>

  <!-- المنيو -->
  <div class="panel" id="panel-menu">
    <div class="menu-search"><input id="menuSearch" placeholder="بحث في المنيو..." oninput="renderMenu()"></div>
    <div class="menu-grid" id="menuGrid"></div>
  </div>

  <!-- طلبات الشركات: وجبات جافة ×50 / ×100 -->
  <div class="panel" id="panel-companies">
    <div class="co-form">
      <div class="co-grid">
        <input class="wide" id="coCompany" placeholder="🏢 اسم الشركة" list="coCompaniesList">
        <input class="wide" id="coDepartment" placeholder="🏬 القسم / الموقع (اختياري)">
        <select id="coPackage" onchange="coUpdateTotal()">
          <option value="جافة ×50">وجبة جافة ×50</option>
          <option value="جافة ×100">وجبة جافة ×100</option>
        </select>
        <input id="coMeals" type="number" min="1" placeholder="عدد الوجبات" oninput="coUpdateTotal()">
        <input id="coPrice" type="number" min="0" step="0.5" placeholder="قيمة الوجبة (ج.م)" oninput="coUpdateTotal()">
        <input id="coNotes" placeholder="ملاحظات (اختياري)">
        <div class="co-live-total"><span>إجمالي طلب الشركة</span><span id="coLiveTotal">0 ج.م</span></div>
        <button class="co-submit" id="coSubmitBtn" onclick="submitCompanyOrder()">🏢 تسجيل طلب الشركة</button>
      </div>
      <datalist id="coCompaniesList"></datalist>
    </div>
    <div class="co-grand" id="coGrand" style="display:none">
      <span>إجمالي شركات اليوم: <span id="coGrandMeals">0</span> وجبة</span>
      <span id="coGrandTotal">0 ج.م</span>
    </div>
    <div id="coGroupsList"></div>
  </div>

  <script>
    let currentFilter = 'all';
    let cashierMenu = [];
    let posDeliveryFee = 0;
    let posCart = [];
    let posOrderType = 'صالة';
    let knownOrderIds = new Set();
    let soundEnabled = false;
    let audioCtx = null;

    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    // ===== تنبيه صوتي: إنذار صفارة عالي يعمل تلقائيًا عند أي طلب جديد =====
    function markSoundReady(label) {
      soundEnabled = true;
      const btn = document.getElementById('soundBtn');
      if (btn) { btn.textContent = label || '🚨 الإنذار مُسلّط تلقائيًا'; btn.classList.add('on'); }
    }
    function enableSound() {
      try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        markSoundReady();
      } catch (e) {}
    }
    // تسليح تلقائي: المتصفح يسمح بالصوت بعد أول لمسة/ضغطة على الصفحة
    (function autoArm() {
      try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'running') { markSoundReady('🚨 الإنذار مُسلّط تلقائيًا'); return; }
        const arm = () => {
          if (!audioCtx) return;
          audioCtx.resume().then(() => markSoundReady()).catch(() => {});
        };
        ['pointerdown', 'touchstart', 'keydown'].forEach(evt =>
          document.addEventListener(evt, arm, { once: true, passive: true }));
      } catch (e) {
        // متصفح قديم: يظل الزر اليدوي متاحًا
      }
    })();

    let sirenPlaying = false;
    function playSiren() {
      if (!soundEnabled || !audioCtx || audioCtx.state !== 'running' || sirenPlaying) return;
      sirenPlaying = true;
      if (navigator.vibrate) { try { navigator.vibrate([300, 150, 300, 150, 300]); } catch (e) {} }
      const now = audioCtx.currentTime;
      // إنذار مرتفع: نغمتان متبادلتان (شرطة طويلة + نقرة) × 4 دورات
      const master = audioCtx.createGain();
      master.gain.value = 0.55;
      master.connect(audioCtx.destination);
      const cycle = 0.75;
      for (let i = 0; i < 8; i++) {
        const t0 = now + i * cycle * 0.5;
        const hi = i % 2 === 0;
        const freq = hi ? 980 : 720;
        const osc = audioCtx.createOscillator();
        const osc2 = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'square';
        osc2.type = 'sawtooth';
        osc.frequency.value = freq;
        osc2.frequency.value = freq * 0.5;
        const dur = cycle * 0.5;
        gain.gain.setValueAtTime(0.0001, t0);
        gain.gain.exponentialRampToValueAtTime(0.85, t0 + 0.02);
        gain.gain.setValueAtTime(0.85, t0 + dur - 0.04);
        gain.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);
        osc.connect(gain); osc2.connect(gain);
        gain.connect(master);
        osc.start(t0); osc.stop(t0 + dur + 0.02);
        osc2.start(t0); osc2.stop(t0 + dur + 0.02);
      }
      setTimeout(() => { sirenPlaying = false; }, 3200);
    }

    function showTab(id, btn) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('panel-' + id).classList.add('active');
      if (id === 'menu' && cashierMenu.length === 0) loadMenu();
      if (id === 'pos' && cashierMenu.length === 0) loadMenu().then(renderPosGrid);
      else if (id === 'pos') renderPosGrid();
      if (id === 'companies') loadCompaniesToday();
    }

    async function loadMenu() {
      try {
        const res = await api('menu');
        if (res.success) { cashierMenu = res.items; renderMenu(); }
      } catch (e) {}
    }

    function renderMenu() {
      const term = document.getElementById('menuSearch').value.trim().toLowerCase();
      const items = term ? cashierMenu.filter(i => i.name.toLowerCase().includes(term)) : cashierMenu;
      document.getElementById('menuGrid').innerHTML = items.map(i => `
        <div class="mcard">
          <div class="th"><img src="${i.image}" alt="${esc(i.name)}" loading="lazy"></div>
          <div class="b">
            <h4>${esc(i.name)}</h4>
            <div class="c">${esc(i.category)}</div>
            <div class="p">${i.price} ج.م</div>
          </div>
        </div>
      `).join('') || '<div class="empty">لا توجد أصناف</div>';
    }

    // ===== POS =====
    function renderPosGrid() {
      const term = document.getElementById('posSearch').value.trim().toLowerCase();
      const items = term ? cashierMenu.filter(i => i.name.toLowerCase().includes(term)) : cashierMenu;
      document.getElementById('posGrid').innerHTML = items.map(i => `
        <div class="pos-item" onclick='posAdd(${JSON.stringify(i)})'>
          <div class="th"><img src="${i.image}" alt="" loading="lazy"></div>
          <div class="b"><h4>${esc(i.name)}</h4><div class="p">${i.price} ج.م</div></div>
        </div>
      `).join('');
    }

    function posAdd(item) {
      const exist = posCart.find(c => String(c.id) === String(item.id));
      if (exist) exist.qty++;
      else posCart.push({ id: item.id, name: item.name, price: Number(item.price) || 0, qty: 1 });
      renderPosCart();
    }
    function posChangeQty(id, delta) {
      const it = posCart.find(c => String(c.id) === String(id));
      if (!it) return;
      it.qty += delta;
      if (it.qty <= 0) posCart = posCart.filter(c => String(c.id) !== String(id));
      renderPosCart();
    }
    function setOrderType(type) {
      posOrderType = type;
      document.getElementById('btnDinein').classList.toggle('active', type === 'صالة');
      document.getElementById('btnDelivery').classList.toggle('active', type === 'دليفري');
      document.getElementById('posAddressWrap').classList.toggle('show', type === 'دليفري');
      renderPosCart();
    }
    function renderPosCart() {
      const list = document.getElementById('posCartList');
      if (!posCart.length) {
        list.innerHTML = '<div class="empty" style="padding:14px">لم تُضف أي أصناف بعد</div>';
      } else {
        list.innerHTML = posCart.map(c => `
          <div class="pos-cart-row">
            <span>${esc(c.name)}</span>
            <div class="qty-ctl">
              <button onclick="posChangeQty(${c.id}, -1)">−</button>
              <b>${c.qty}</b>
              <button onclick="posChangeQty(${c.id}, 1)">+</button>
              <span style="min-width:50px;text-align:left;font-weight:700">${c.qty * c.price}</span>
            </div>
          </div>
        `).join('');
      }
      const subtotal = posCart.reduce((s, c) => s + c.qty * c.price, 0);
      const isDelivery = posOrderType === 'دليفري';
      const fee = isDelivery ? posDeliveryFee : 0;
      const total = subtotal + fee;
      document.getElementById('feeRows').innerHTML = isDelivery
        ? `<div class="fee-rows delivery-on"><span>المجموع: ${subtotal} ج.م</span><span>🛵 التوصيل: ${fee} ج.م</span></div>`
        : '';
      document.getElementById('posTotal').textContent = total + ' ج.م';
    }

    async function submitPosOrder() {
      if (!posCart.length) { alert('أضف أصنافًا أولًا'); return; }
      const address = document.getElementById('posAddress').value.trim();
      if (posOrderType === 'دليفري') {
        const dphone = document.getElementById('posPhone').value.trim();
        if (!dphone || !address) { alert('طلبات الدليفري تتطلب رقم الهاتف والعنوان'); return; }
      }
      const btn = document.getElementById('posSubmitBtn');
      btn.disabled = true;
      try {
        const res = await api('place_order', {
          items: posCart.map(c => ({ id: c.id, qty: c.qty })),
          customerName: document.getElementById('posCustomer').value.trim(),
          phone: document.getElementById('posPhone').value.trim(),
          notes: document.getElementById('posNotes').value.trim(),
          address: address,
          orderType: posOrderType,
        });
        btn.disabled = false;
        if (res.success) {
          posCart = [];
          renderPosCart();
          document.getElementById('posCustomer').value = '';
          document.getElementById('posPhone').value = '';
          document.getElementById('posNotes').value = '';
          document.getElementById('posAddress').value = '';
          alert('تم إنشاء الطلب: ' + res.orderId);
          if (confirm('فتح فاتورة الطلب؟')) window.open('?page=invoice&id=' + encodeURIComponent(res.orderId), '_blank');
          loadOrders(currentFilter);
          loadTodaySummary();
        } else alert(res.message || 'خطأ');
      } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
    }

    // ===== الطلبات =====
    async function loadOrders(filter, btn) {
      if (btn) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
      }
      currentFilter = filter || 'all';
      try {
        const res = await api('get_orders', { status: currentFilter === 'all' ? '' : currentFilter });
        if (!res.success) {
          if (res.message === 'غير مصرح') { window.location.href = '?page=login'; return; }
          document.getElementById('ordersList').innerHTML = '<div class="empty">' + esc(res.message || 'خطأ') + '</div>';
          return;
        }
        renderOrders(res.orders);
        checkNewOrders(res.orders);
        updateTodayChips(res.orders);
      } catch (e) {
        document.getElementById('ordersList').innerHTML = '<div class="empty">خطأ في الاتصال بالسيرفر</div>';
      }
    }

    function checkNewOrders(orders) {
      const news = orders.filter(o => o.status === 'جديد');
      const ids = new Set(news.map(o => o.order_id));
      if (knownOrderIds.size > 0) {
        let hasNew = false;
        ids.forEach(id => { if (!knownOrderIds.has(id)) hasNew = true; });
        if (hasNew) playSiren();
      }
      knownOrderIds = ids;
    }

    function todayStr() {
      return new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());
    }
    function updateTodayChips(orders) {
      const today = todayStr();
      const todays = orders.filter(o => (o.created_at || '').slice(0, 10) === today);
      const sales = todays.reduce((s, o) => s + Number(o.total || 0), 0);
      document.getElementById('tdOrders').textContent = todays.length;
      document.getElementById('tdSales').textContent = Math.round(sales);
      document.getElementById('tdNew').textContent = orders.filter(o => o.status === 'جديد').length;
      document.getElementById('tdReady').textContent = orders.filter(o => o.status === 'جاهز').length;
    }
    // ===== طلبات الشركات =====
    function coUpdateTotal() {
      const meals = parseInt(document.getElementById('coMeals').value) || 0;
      const price = parseFloat(document.getElementById('coPrice').value) || 0;
      document.getElementById('coLiveTotal').textContent = (meals * price) + ' ج.م';
    }

    async function submitCompanyOrder() {
      const companyName = document.getElementById('coCompany').value.trim();
      const department = document.getElementById('coDepartment').value.trim();
      const packageType = document.getElementById('coPackage').value;
      const meals = parseInt(document.getElementById('coMeals').value) || 0;
      const price = parseFloat(document.getElementById('coPrice').value) || 0;
      const notes = document.getElementById('coNotes').value.trim();
      if (!companyName) { alert('أدخل اسم الشركة'); return; }
      if (meals <= 0) { alert('أدخل عدد الوجبات'); return; }
      if (price <= 0) { alert('أدخل قيمة الوجبة'); return; }
      const btn = document.getElementById('coSubmitBtn');
      btn.disabled = true;
      try {
        const res = await api('add_company_order', { companyName, department, package: packageType, meals, price, notes });
        btn.disabled = false;
        if (res.success) {
          ['coDepartment', 'coMeals', 'coPrice', 'coNotes'].forEach(id => document.getElementById(id).value = '');
          coUpdateTotal();
          loadCompaniesToday();
        } else alert(res.message || 'خطأ');
      } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
    }

    async function loadCompaniesToday() {
      try {
        const res = await api('company_today');
        if (!res.success) {
          document.getElementById('coGroupsList').innerHTML = '<div class="empty">' + esc(res.message || 'خطأ') + '</div>';
          return;
        }
        renderCompanyGroups(res.groups, res.grandTotal, res.grandMeals);
      } catch (e) {
        document.getElementById('coGroupsList').innerHTML = '<div class="empty">خطأ في الاتصال بالسيرفر</div>';
      }
    }

    function renderCompanyGroups(groups, grandTotal, grandMeals) {
      const grand = document.getElementById('coGrand');
      if (!groups.length) {
        grand.style.display = 'none';
        document.getElementById('coGroupsList').innerHTML = '<div class="empty">لا توجد طلبات شركات اليوم بعد</div>';
        return;
      }
      grand.style.display = 'flex';
      document.getElementById('coGrandMeals').textContent = grandMeals;
      document.getElementById('coGrandTotal').textContent = Math.round(grandTotal) + ' ج.م';

      document.getElementById('coGroupsList').innerHTML = groups.map((g, gi) => `
        <div class="co-card">
          <div class="co-head" onclick="toggleCoBody(${gi}, event)">
            <span class="cname">${esc(g.company)}</span>
            <span class="ctotal">${g.totalMeals} وجبة · ${Math.round(g.total)} ج.م ▾</span>
          </div>
          <div class="co-body" id="coBody-${gi}">
            ${g.orders.map(o => `
              <div class="co-row">
                <span><b>${esc(o.package)}</b> × ${o.meals} وجبة · ${o.price} ج.م للوجبة</span>
                <span><b>${Math.round(o.total)} ج.م</b><br><span class="muted">${esc(o.created_at)}${o.department ? ' · ' + esc(o.department) : ''}</span></span>
              </div>
            `).join('')}
          </div>
        </div>
      `).join('');
    }

    function toggleCoBody(gi, ev) {
      ev.stopPropagation();
      document.getElementById('coBody-' + gi).classList.toggle('show');
    }

    async function loadTodaySummary() { loadOrders(currentFilter); }

    function statusClass(status) {
      if (status === 'جديد') return 'new';
      if (status === 'قيد التحضير') return 'preparing';
      if (status === 'جاهز') return 'ready';
      if (status === 'تم التسليم') return 'done';
      return '';
    }

    function renderOrders(orders) {
      const list = document.getElementById('ordersList');
      if (!orders || orders.length === 0) {
        list.innerHTML = '<div class="empty">لا توجد طلبات</div>';
        return;
      }
      list.innerHTML = orders.map(o => {
        const sCls = statusClass(o.status);
        const isDelivery = o.order_type === 'دليفري';
        const itemsHtml = (o.items || []).map(i => esc(i.name) + ' × ' + esc(i.qty)).join('<br>');
        const date = o.created_at || '';
        return `
          <div class="order-card ${sCls}">
            <div class="order-header">
              <span class="order-id">${esc(o.order_id)}</span>
              <span class="type-tag ${isDelivery ? 'delivery' : 'dinein'}">${isDelivery ? '🛵 دليفري' : '🍽️ صالة'}</span>
              <span class="order-status ${sCls}">${esc(o.status)}</span>
            </div>
            <div class="order-meta">${esc(o.customer_name)} | ${esc(o.phone || '-')} | ${esc(date)}</div>
            ${isDelivery && o.address ? '<div class="order-meta" style="color:#ef6c00">📍 ' + esc(o.address) + '</div>' : ''}
            <div class="order-items">${itemsHtml}</div>
            <div class="order-total">${esc(o.total)} جنيه</div>
            ${o.notes ? '<div style="font-size:11.5px;color:#8b8b9a;margin-bottom:8px">ملاحظات: ' + esc(o.notes) + '</div>' : ''}
            <div class="actions">
              ${o.status === 'جديد' ? '<button class="btn-prep" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'قيد التحضير\')">بدء التحضير</button>' : ''}
              ${o.status === 'قيد التحضير' ? '<button class="btn-ready" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'جاهز\')">جاهز</button>' : ''}
              ${o.status === 'جاهز' ? '<button class="btn-done" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'تم التسليم\')">تم التسليم</button>' : ''}
              <button class="btn-prep" style="background:#e5f0ff;color:#007aff" onclick="window.open('?page=invoice&id=${encodeURIComponent(o.order_id)}', '_blank')">🖨️ فاتورة</button>
            </div>
          </div>
        `;
      }).join('');
    }

    async function updateStatus(orderId, status) {
      try {
        const res = await api('update_status', { orderId, status });
        if (res.success) loadOrders(currentFilter);
        else alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ: ' + (e.message || '')); }
    }

    async function doLogout() {
      try { await api('logout'); } catch (e) {}
      window.location.href = '?page=login';
    }

    api('public_settings').then(res => {
      if (res.success && res.settings) posDeliveryFee = Number(res.settings.deliveryFee) || 0;
    }).catch(() => {});
    loadMenu().then(renderPosGrid);
    loadOrders('all');
    setInterval(() => loadOrders(currentFilter), 12000);
  </script>
</body>
</html>
