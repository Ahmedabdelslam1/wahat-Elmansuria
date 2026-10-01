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
    .header-logo { width: 34px; height: 34px; border-radius: 10px; object-fit: cover; background:#fff; flex-shrink:0; }
    .header-brand { display:flex; align-items:center; gap:8px; }
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
    .tab.active { color: var(--primary); border-bottom-color: var(--primary); text-shadow: 0 0 10px rgba(255,59,48,0.55); }
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
    .type-tag.company { background:#e7f8ec; color:#34c759; }
    .order-meta { font-size: 11.5px; color: var(--muted); margin-bottom: 6px; }
    .order-items { font-size: 12.5px; margin-bottom: 8px; line-height: 1.5; }
    .order-total { font-weight: 800; color: var(--primary); font-size: 14px; margin-bottom: 8px; }
    .actions { display: flex; gap: 7px; flex-wrap: wrap; }
    .actions button { padding: 7px 12px; border-radius: 10px; border: none; font-size: 11.5px; cursor: pointer; font-weight: 700; font-family: inherit; }
    .btn-prep { background: #fff3e0; color: #ef6c00; }
    .btn-ready { background: #e7f8ec; color: #2e7d32; }
    .btn-done { background: #e5f0ff; color: #007aff; }
    .empty { text-align: center; padding: 40px; color: var(--muted); }

    /* ===== طلبات الشركات: تصميم جديد ===== */
    .cof-card { background: var(--surface); border-radius: 16px; padding: 14px; margin: 12px; box-shadow: 0 2px 12px rgba(20,20,30,0.06); border: 1px solid var(--border); }
    .cof-title { font-size: 14px; font-weight: 800; margin-bottom: 10px; }
    .cof-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px; }
    .cof-field label { display: block; font-size: 11px; font-weight: 800; color: var(--muted); margin-bottom: 4px; }
    .cof-field input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 13px; background: var(--bg); }
    .cof-items-label { font-size: 12px; font-weight: 800; color: var(--muted); display: flex; justify-content: space-between; align-items: center; margin: 4px 0 6px; }
    .cof-add { border: none; background: #e7f8ec; color: #2e7d32; border-radius: 10px; padding: 6px 12px; font-weight: 800; font-size: 12px; cursor: pointer; font-family: inherit; }
    .cof-head-row { display: grid; grid-template-columns: 1fr 62px 78px 34px; gap: 6px; font-size: 10.5px; font-weight: 800; color: var(--muted); padding: 0 2px 4px; }
    .cof-item { display: grid; grid-template-columns: 1fr 62px 78px 34px; gap: 6px; margin-bottom: 8px; align-items: center; }
    .cof-item select, .cof-item input { width: 100%; padding: 9px 10px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 12.5px; background: var(--bg); }
    .cof-del { border: none; background: #ffebe9; color: #ff3b30; border-radius: 10px; height: 34px; cursor: pointer; font-weight: 800; }
    #coNotes { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 12.5px; background: var(--bg); margin-bottom: 10px; }
    .co-live-total { display: flex; justify-content: space-between; background: #fff8ec; border: 1px solid #ffe3b3; color: #8a5a00; border-radius: 12px; padding: 10px 14px; font-weight: 800; font-size: 13.5px; margin-bottom: 10px; }
    #coLiveTotal { font-size: 16px; }
    .co-submit { width: 100%; padding: 13px; border: none; border-radius: 13px; background: linear-gradient(90deg, #34c759, #2e7d32); color: #fff; font-family: inherit; font-size: 14.5px; font-weight: 800; cursor: pointer; box-shadow: 0 8px 20px -6px rgba(52,199,89,0.55); }
    .co-grand { display: flex; justify-content: space-between; background: var(--surface); margin: 12px; border-radius: 14px; padding: 12px 16px; font-weight: 800; font-size: 13.5px; border: 1px solid var(--border); }
    .co-card { background: var(--surface); border-radius: 16px; margin: 10px 12px; overflow: hidden; border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(20,20,30,0.05); }
    .co-head { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; cursor: pointer; gap: 8px; flex-wrap: wrap; }
    .co-head .cname { font-weight: 800; font-size: 13.5px; color: #2e7d32; }
    .co-head .ctotal { font-size: 11.5px; color: var(--muted); font-weight: 800; }
    .co-body { display: none; padding: 0 10px 10px; }
    .co-body.show { display: block; }
    .co-row { display: flex; justify-content: space-between; gap: 8px; align-items: center; font-size: 12px; padding: 9px 6px; border-top: 1px dashed var(--border); flex-wrap: wrap; }
    .act-btn { border: none; border-radius: 8px; padding: 5px 9px; font-size: 10.5px; font-weight: 800; cursor: pointer; font-family: inherit; }
    .act-edit { background: #e5f0ff; color: #007aff; }
    .act-cancel { background: #fff3e0; color: #ef6c00; }
    .act-del { background: #ffebe9; color: #ff3b30; }

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

      .live-clock-bar { text-align:center; font-size:10px; color:var(--muted); padding:4px 0; background:var(--surface); border-bottom:1px solid var(--border); font-weight:700; letter-spacing:.2px; position:sticky; top:0; z-index:60; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="live-clock-bar" id="liveClockBar">—</div>
  <div class="header">
    <div class="header-brand">
      <img class="header-logo" src="assets/icons/icon-512.png" alt="شعار <?= e(APP_NAME) ?>">
      <div>
        <h1>🧾 لوحة الكاشير</h1>
        <div class="user"><?= e($user['name']) ?> (<?= e($user['role']) ?>)</div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
      <?php if ($user['role'] === 'admin'): ?>
        <a href="?page=admin" style="color:#ff9500;font-size:11.5px;text-decoration:none;font-weight:700">لوحة المدير</a>
      <?php endif; ?>
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

  <!-- POS: طلب جديد (شاشة مشتركة مع لوحة المدير) -->
  <?php $posActive = true; $posAllowCompany = false; include __DIR__ . '/_pos.php'; ?>

  <!-- الطلبات -->
  <div class="panel" id="panel-orders">
    <div class="filters">
      <button class="filter-btn active" onclick="loadOrders('all', this)">الكل</button>
      <button class="filter-btn" onclick="loadOrders('جديد', this)">جديد</button>
      <button class="filter-btn" onclick="loadOrders('قيد التحضير', this)">قيد التحضير</button>
      <button class="filter-btn" onclick="loadOrders('جاهز', this)">جاهز</button>
      <button class="filter-btn" onclick="loadOrders('تم التسليم', this)">تم التسليم</button>
      <button class="filter-btn" onclick="loadOrders('ملغي', this)">ملغي</button>
    </div>
    <div class="orders" id="ordersList"><div class="empty">جاري التحميل...</div></div>
  </div>

  <!-- المنيو -->
  <div class="panel" id="panel-menu">
    <div class="menu-search"><input id="menuSearch" placeholder="بحث في المنيو..." oninput="renderMenu()"></div>
    <div class="menu-grid" id="menuGrid"></div>
  </div>

  <!-- طلبات الشركات (تصميم جديد: بنود متعددة + اقتراح تلقائي) -->
  <div class="panel" id="panel-companies">
    <div class="cof-card">
      <div class="cof-title">🏢 طلب شركة</div>
      <div class="cof-row2">
        <div class="cof-field">
          <label>اسم الشركة</label>
          <input id="coCompany" list="coCompaniesList" placeholder="اكتب أو اختر من القائمة">
        </div>
        <div class="cof-field">
          <label>القسم / الموقع</label>
          <input id="coDepartment" list="coDepartmentsList" placeholder="اختياري">
        </div>
      </div>
      <datalist id="coCompaniesList"></datalist>
      <datalist id="coDepartmentsList"></datalist>
      <div class="cof-items-label">
        <span>🍽️ بنود الطلب</span>
        <button class="cof-add" onclick="coAddItemRow()">➕ إضافة بند</button>
      </div>
      <div class="cof-head-row"><span>البند</span><span>العدد</span><span>سعر الوحدة</span><span></span></div>
      <div id="coItemsWrap"></div>
      <input id="coNotes" placeholder="📝 ملاحظات — تتسجل تلقائيًا مع الطلب">
      <div class="co-live-total"><span>إجمالي الطلب</span><span id="coLiveTotal">0 ج.م</span></div>
      <button class="co-submit" id="coSubmitBtn" onclick="submitCompanyOrder()">💾 حفظ طلب الشركة</button>
      <button class="co-submit" id="coEditCancelBtn" style="display:none;background:#eee;color:#555" onclick="coResetForm()">✖ خروج من وضع التعديل</button>
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
    let knownOrderIds = new Set();

    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function showTab(id, btn) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('panel-' + id).classList.add('active');
      if (id === 'menu' && cashierMenu.length === 0) loadMenu();
      if (id === 'companies') { loadCompaniesToday(); loadCompaniesList(); }
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

    // بعد تسجيل طلب من شاشة POS: تحديث قائمة الطلبات والعدادات
    window.posAfterSubmit = function () { loadOrders(currentFilter); loadTodaySummary(); };

    // بعد تعديل/إلغاء/حذف طلب: تحديث القائمة والعدادات
    window.orderActionsRefresh = function () { loadOrders(currentFilter); };
    let ordersCache = {};
    function openOrderEditById(oid) { openOrderEdit(ordersCache[oid]); }

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
      }
      knownOrderIds = ids;
    }

    function todayStr() {
      return new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());
    }
    function updateTodayChips(orders) {
      const today = todayStr();
      const todays = orders.filter(o => (o.created_at || '').slice(0, 10) === today && o.status !== 'ملغي');
      const sales = todays.reduce((s, o) => s + Number(o.total || 0), 0);
      document.getElementById('tdOrders').textContent = todays.length;
      document.getElementById('tdSales').textContent = Math.round(sales);
      document.getElementById('tdNew').textContent = orders.filter(o => o.status === 'جديد').length;
      document.getElementById('tdReady').textContent = orders.filter(o => o.status === 'جاهز').length;
    }
    // ===== طلبات الشركات (فورم جديد: بنود متعددة + اقتراح تلقائي) =====
    let coItemOptionsHtml = '';

    async function coEnsureMenu() {
      if (!cashierMenu.length) await loadMenu();
      coItemOptionsHtml = '';
    }

    function coBuildItemOptions() {
      if (!coItemOptionsHtml) {
        coItemOptionsHtml = '<option value="">— اختر البند —</option>' + cashierMenu.map(i =>
          '<option value="' + esc(i.name) + '" data-price="' + esc(i.price) + '">' + esc(i.name) + ' (' + esc(i.price) + ' ج.م)</option>'
        ).join('');
      }
      return coItemOptionsHtml;
    }

    async function coAddItemRow(itemName, qty, price) {
      await coEnsureMenu();
      const wrap = document.getElementById('coItemsWrap');
      const row = document.createElement('div');
      row.className = 'cof-item';
      row.innerHTML =
        '<select class="cof-item-sel" onchange="coItemChanged(this)">' + coBuildItemOptions() + '</select>' +
        '<input type="number" class="cof-qty" min="1" value="' + (qty || 1) + '" placeholder="العدد" oninput="coUpdateTotal()">' +
        '<input type="number" class="cof-price" min="0" step="0.5" value="' + (price != null ? price : '') + '" placeholder="ج.م" oninput="coUpdateTotal()">' +
        '<button class="cof-del" onclick="coRemoveItemRow(this)" title="حذف البند">✖</button>';
      wrap.appendChild(row);
      const sel = row.querySelector('.cof-item-sel');
      if (itemName) {
        if (![...sel.options].some(o => o.value === itemName)) {
          const opt = document.createElement('option');
          opt.value = itemName;
          opt.dataset.price = price != null ? price : '';
          opt.textContent = itemName + (price != null ? ' (' + price + ' ج.م)' : '');
          sel.appendChild(opt);
        }
        sel.value = itemName;
      }
      coUpdateTotal();
      return row;
    }

    function coRemoveItemRow(btn) {
      btn.closest('.cof-item').remove();
      if (!document.querySelectorAll('#coItemsWrap .cof-item').length) coAddItemRow();
      coUpdateTotal();
    }

    function coItemChanged(sel) {
      const opt = sel.options[sel.selectedIndex];
      if (opt && opt.dataset.price) sel.closest('.cof-item').querySelector('.cof-price').value = opt.dataset.price;
      coUpdateTotal();
    }

    function coUpdateTotal() {
      let total = 0;
      document.querySelectorAll('#coItemsWrap .cof-item').forEach(r => {
        const qty = parseInt(r.querySelector('.cof-qty').value) || 0;
        const price = parseFloat(r.querySelector('.cof-price').value) || 0;
        total += qty * price;
      });
      document.getElementById('coLiveTotal').textContent = Math.round(total) + ' ج.م';
    }

    let editCoId = null;

    function coResetForm() {
      editCoId = null;
      document.getElementById('coSubmitBtn').textContent = '💾 حفظ طلب الشركة';
      document.getElementById('coEditCancelBtn').style.display = 'none';
      ['coCompany', 'coDepartment', 'coNotes'].forEach(id => document.getElementById(id).value = '');
      document.getElementById('coItemsWrap').innerHTML = '';
      coAddItemRow();
    }

    async function coEditRow(id) {
      const o = coRowsCache[id];
      if (!o) return;
      editCoId = id;
      document.getElementById('coCompany').value = o.company_name || '';
      document.getElementById('coDepartment').value = o.department || '';
      document.getElementById('coNotes').value = o.notes || '';
      const label = o.package === 'من المنيو' && o.item_name ? o.item_name : o.package;
      document.getElementById('coItemsWrap').innerHTML = '';
      await coAddItemRow(label, o.meals, o.price);
      document.getElementById('coSubmitBtn').textContent = '💾 حفظ التعديلات';
      document.getElementById('coEditCancelBtn').style.display = 'block';
      document.querySelector('.cof-card').scrollIntoView({ behavior: 'smooth' });
    }

    async function coCancelRow(id) {
      if (!confirm('إلغاء هذا البند؟ لن يُحسب في تقارير الشركات')) return;
      try {
        const res = await api('cancel_company_order', { id });
        if (res.success) loadCompaniesToday(); else alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ في الاتصال'); }
    }

    async function coDeleteRow(id) {
      if (!confirm('حذف هذا البند نهائيًا؟ لا يمكن التراجع!')) return;
      try {
        const res = await api('delete_company_order', { id });
        if (res.success) { if (editCoId === id) coResetForm(); loadCompaniesToday(); } else alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ في الاتصال'); }
    }

    async function submitCompanyOrder() {
      const companyName = document.getElementById('coCompany').value.trim();
      const department = document.getElementById('coDepartment').value.trim();
      const notes = document.getElementById('coNotes').value.trim();
      if (!companyName) { alert('أدخل اسم الشركة'); return; }
      const items = [];
      document.querySelectorAll('#coItemsWrap .cof-item').forEach(r => {
        const name = r.querySelector('.cof-item-sel').value.trim();
        const qty = parseInt(r.querySelector('.cof-qty').value) || 0;
        const price = parseFloat(r.querySelector('.cof-price').value) || 0;
        if (name) items.push({ name, qty, price });
      });
      if (!items.length) { alert('أضف بندًا واحدًا على الأقل'); return; }
      if (items.some(i => i.qty <= 0 || i.price <= 0)) { alert('تحقق من العدد والسعر لكل بند'); return; }
      const btn = document.getElementById('coSubmitBtn');
      btn.disabled = true;
      try {
        const res = editCoId
          ? await api('update_company_order', { id: editCoId, companyName, department, package: 'من المنيو', itemName: items[0].name, meals: items[0].qty, price: items[0].price, notes })
          : await api('save_company_order', { companyName, department, notes, items });
        btn.disabled = false;
        if (res.success) {
          alert(res.message || 'تم الحفظ بنجاح');
          coResetForm();
          loadCompaniesToday();
        } else alert(res.message || 'خطأ');
      } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
    }

    async function loadCompaniesList() {
      try {
        const res = await api('companies_list');
        if (res.success) {
          document.getElementById('coCompaniesList').innerHTML = (res.companies || []).map(c => '<option value="' + esc(c) + '">').join('');
          document.getElementById('coDepartmentsList').innerHTML = (res.departments || []).map(d => '<option value="' + esc(d) + '">').join('');
        }
      } catch (e) {}
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

    let coRowsCache = {};

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
            ${(() => { g.orders.forEach(o => { coRowsCache[o.id] = o; }); return ''; })()}
            ${g.orders.map(o => {
              const cancelled = o.status === 'ملغي';
              const label = o.package === 'من المنيو' && o.item_name ? o.item_name : o.package;
              return `
              <div class="co-row" ${cancelled ? 'style="opacity:.5"' : ''}>
                <span>${cancelled ? '<span style="background:#ffebe9;color:#ff3b30;border-radius:6px;padding:1px 6px;font-size:10px;font-weight:800">ملغي</span> ' : ''}<b>${esc(label)}</b> × ${o.meals} وجبة · ${o.price} ج.م للوجبة${o.order_id ? ' <span class="muted">(طلب ' + esc(o.order_id) + ')</span>' : ''}</span>
                <span style="display:flex;gap:5px;align-items:center">
                  <span style="text-align:left"><b>${Math.round(o.total)} ج.م</b><br><span class="muted">${esc(o.created_at)}${o.department ? ' · ' + esc(o.department) : ''}</span></span>
                  ${!cancelled ? `<button class="act-btn act-edit" onclick="coEditRow(${o.id})">✏️ تعديل</button>` : ''}
                  ${!cancelled ? `<button class="act-btn act-cancel" onclick="coCancelRow(${o.id})">✖ إلغاء</button>` : ''}
                  <button class="act-btn act-del" onclick="coDeleteRow(${o.id})">🗑 حذف</button>
                </span>
              </div>`;
            }).join('')}
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
      if (status === 'ملغي') return 'cancelled';
      return '';
    }

    function renderOrders(orders) {
      const list = document.getElementById('ordersList');
      if (!orders || orders.length === 0) {
        list.innerHTML = '<div class="empty">لا توجد طلبات</div>';
        return;
      }
      ordersCache = {};
      orders.forEach(o => { ordersCache[o.order_id] = o; });
      list.innerHTML = orders.map(o => {
        const sCls = statusClass(o.status);
        const isDelivery = o.order_type === 'دليفري';
        const isCompany = o.order_type === 'شركات';
        const tagCls = isCompany ? 'company' : (isDelivery ? 'delivery' : 'dinein');
        const tagLabel = isCompany ? '🏢 شركات' : (isDelivery ? '🛵 دليفري' : '🍽️ صالة');
        const itemsHtml = (o.items || []).map(i => esc(i.name) + ' × ' + esc(i.qty)).join('<br>');
        const date = o.created_at || '';
        const canEdit = ['جديد', 'قيد التحضير', 'جاهز'].includes(o.status);
        return `
          <div class="order-card ${sCls}">
            <div class="order-header">
              <span class="order-id">${esc(o.order_id)}</span>
              <span class="type-tag ${tagCls}">${tagLabel}</span>
              <span class="order-status ${sCls}">${esc(o.status)}</span>
            </div>
            ${isCompany && o.company_name ? '<div class="order-meta" style="color:#34c759;font-weight:800">🏢 ' + esc(o.company_name) + (o.department ? ' — ' + esc(o.department) : '') + '</div>' : ''}
            <div class="order-meta">${esc(o.customer_name)} | ${esc(o.phone || '-')} | ${esc(date)}</div>
            ${isDelivery && o.address ? '<div class="order-meta" style="color:#ef6c00">📍 ' + esc(o.address) + '</div>' : ''}
            <div class="order-items">${itemsHtml}</div>
            <div class="order-total">${esc(o.total)} جنيه</div>
            ${o.notes ? '<div style="font-size:11.5px;color:#8b8b9a;margin-bottom:8px">ملاحظات: ' + esc(o.notes) + '</div>' : ''}
            <div class="actions">
              ${o.status === 'جديد' ? '<button class="btn-prep" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'قيد التحضير\')">بدء التحضير</button>' : ''}
              ${o.status === 'قيد التحضير' ? '<button class="btn-ready" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'جاهز\')">جاهز</button>' : ''}
              ${o.status === 'جاهز' ? '<button class="btn-done" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'تم التسليم\')">تم التسليم</button>' : ''}
              ${canEdit ? '<button class="act-btn act-edit" onclick="openOrderEditById(\'' + esc(o.order_id) + '\')">✏️ تعديل</button>' : ''}
              ${canEdit ? '<button class="act-btn act-add" onclick="openOrderEditById(\'' + esc(o.order_id) + '\')">➕ إضافة</button>' : ''}
              ${(canEdit && !isCompany) ? '<button class="act-btn act-cancel" onclick="if (confirm(\'إلغاء الطلب؟\')) cancelOrderDirect(\'' + esc(o.order_id) + '\')">✖ إلغاء</button>' : ''}
              ${(canEdit && isCompany) ? '<button class="act-btn act-cancel" title="إلغاء بنود الشركة من تبويب الشركات" onclick="var t=document.querySelectorAll(\'.tab\')[3]; if(t) showTab(\'companies\', t)">✖ إلغاء (من الشركات)</button>' : ''}
              <button class="act-btn act-del" onclick="if (confirm(\'حذف الطلب نهائيًا؟\')) deleteOrderDirect(\'' + esc(o.order_id) + '\')">🗑 حذف</button>
              <button class="btn-prep" style="background:#e5f0ff;color:#007aff" onclick="window.open(\'?page=invoice&id=${encodeURIComponent(o.order_id)}\', \'_blank\')">🖨️ فاتورة</button>
              ${window.waRestLink ? `<a class="act-btn" style="background:#128C7E;color:#fff;text-decoration:none;padding:5px 9px;border-radius:8px;font-size:10.5px;font-weight:700" href="${waRestLink(o)}" target="_blank">📤 واتس</a>` : ''}
              ${(window.waClientLink && o.phone) ? `<a class="act-btn" style="background:#e5f0ff;color:#007aff;text-decoration:none;padding:5px 9px;border-radius:8px;font-size:10.5px;font-weight:700" href="${waClientLink(o)}" target="_blank">📲 العميل</a>` : ''}
            </div>
          </div>
        `;
      }).join('');
    }

    async function cancelOrderDirect(orderId) {
      try {
        const res = await api('cancel_order', { orderId });
        if (res.success) loadOrders(currentFilter); else alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ في الاتصال'); }
    }

    async function deleteOrderDirect(orderId) {
      try {
        const res = await api('delete_order', { orderId });
        if (res.success) loadOrders(currentFilter); else alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ في الاتصال'); }
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

    loadOrders('all');
    setInterval(() => loadOrders(currentFilter), 12000);
    coResetForm();
  </script>
  <?php include __DIR__ . '/_order_edit.php'; ?>
  <?php include __DIR__ . '/_wa.php'; ?>
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
