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
      color: #fff;
      padding: 14px 18px;
      display: flex; justify-content: space-between; align-items: center;
      position: sticky; top: 0; z-index: 50;
    }
    .header h1 { font-size: 17px; font-weight: 800; }
    .header .user { font-size: 11.5px; color: var(--accent2); }
    .logout {
      background: transparent; border: 1px solid rgba(255,255,255,0.25); color: #fff;
      padding: 6px 13px; border-radius: 10px; font-size: 12px; cursor: pointer; font-family:inherit; font-weight:700;
    }
    .tabs {
      display: flex; background: var(--surface); border-bottom: 1px solid var(--border);
      position: sticky; top: 58px; z-index: 45;
    }
    .tab {
      flex: 1; padding: 12px; text-align: center; border: none; background: none;
      font-size: 13px; font-family: inherit; font-weight: 700; cursor: pointer;
      color: var(--muted); border-bottom: 3px solid transparent;
    }
    .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
    .panel { display: none; }
    .panel.active { display: block; }

    .filters {
      display: flex; gap: 8px; padding: 12px 16px; overflow-x: auto; background: var(--surface); border-bottom: 1px solid var(--border);
    }
    .filter-btn {
      flex-shrink: 0; padding: 7px 14px; border-radius: 20px; border: none;
      background: var(--bg); font-size: 12.5px; font-family: inherit; font-weight: 700; cursor: pointer; color: var(--text);
    }
    .filter-btn.active { background: var(--primary); color: #fff; }
    .orders { padding: 12px 16px; display: flex; flex-direction: column; gap: 12px; }
    .order-card { background: var(--surface); border-radius: 18px; padding: 14px; box-shadow: 0 2px 10px rgba(20,20,30,0.05); border-right: 4px solid var(--primary); }
    .order-card.new { border-right-color: #ff3b30; }
    .order-card.preparing { border-right-color: #ff9500; }
    .order-card.ready { border-right-color: #34c759; }
    .order-card.done { border-right-color: #007aff; }
    .order-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .order-id { font-weight: 800; font-size: 14px; }
    .order-status { font-size: 11px; padding: 3px 10px; border-radius: 16px; background: var(--bg); font-weight:700; }
    .order-status.new { background: #ffebe9; color: #ff3b30; }
    .order-status.preparing { background: #fff3e0; color: #ef6c00; }
    .order-status.ready { background: #e7f8ec; color: #2e7d32; }
    .order-status.done { background: #e5f0ff; color: #007aff; }
    .order-meta { font-size: 12px; color: var(--muted); margin-bottom: 8px; }
    .order-items { font-size: 13px; margin-bottom: 10px; line-height: 1.5; }
    .order-total { font-weight: 800; color: var(--primary); font-size: 15px; margin-bottom: 10px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .actions button { padding: 8px 13px; border-radius: 10px; border: none; font-size: 12px; cursor: pointer; font-weight: 700; font-family: inherit; }
    .btn-prep { background: #fff3e0; color: #ef6c00; }
    .btn-ready { background: #e7f8ec; color: #2e7d32; }
    .btn-done { background: #e5f0ff; color: #007aff; }
    .empty { text-align: center; padding: 40px; color: var(--muted); }

    /* المنيو (عرض فقط) */
    .menu-search { padding: 12px 16px 4px; }
    .menu-search input {
      width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 12px;
      font-family: inherit; font-size: 13.5px; background: var(--surface);
    }
    .menu-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 10px 16px 20px; }
    @media (min-width:600px) { .menu-grid { grid-template-columns: repeat(3,1fr); } }
    .mcard { background: var(--surface); border-radius: 14px; overflow: hidden; border: 1px solid var(--border); }
    .mcard .th { aspect-ratio: 1.3/1; overflow: hidden; }
    .mcard .th img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .mcard .b { padding: 8px 10px; }
    .mcard h4 { font-size: 12px; font-weight: 800; margin-bottom: 4px; min-height: 30px; }
    .mcard .p { font-size: 13px; font-weight: 800; color: var(--primary); }
    .mcard .c { font-size: 9.5px; color: var(--muted); }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>🧾 لوحة الكاشير</h1>
      <div class="user"><?= e($user['name']) ?> (<?= e($user['role']) ?>)</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <?php if ($user['role'] === 'admin'): ?>
        <a href="?page=admin" style="color:#ff9500;font-size:12px;text-decoration:none;font-weight:700">لوحة المدير</a>
      <?php endif; ?>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="showTab('orders', this)">📋 الطلبات</button>
    <button class="tab" onclick="showTab('menu', this)">🍽️ المنيو</button>
  </div>

  <div class="panel active" id="panel-orders">
    <div class="filters">
      <button class="filter-btn active" onclick="loadOrders('all', this)">الكل</button>
      <button class="filter-btn" onclick="loadOrders('جديد', this)">جديد</button>
      <button class="filter-btn" onclick="loadOrders('قيد التحضير', this)">قيد التحضير</button>
      <button class="filter-btn" onclick="loadOrders('جاهز', this)">جاهز</button>
      <button class="filter-btn" onclick="loadOrders('تم التسليم', this)">تم التسليم</button>
    </div>
    <div class="orders" id="ordersList"><div class="empty">جاري التحميل...</div></div>
  </div>

  <div class="panel" id="panel-menu">
    <div class="menu-search"><input id="menuSearch" placeholder="بحث في المنيو..." oninput="renderMenu()"></div>
    <div class="menu-grid" id="menuGrid"></div>
  </div>

  <script>
    let currentFilter = 'all';
    let cashierMenu = [];

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
      } catch (e) {
        document.getElementById('ordersList').innerHTML = '<div class="empty">خطأ في الاتصال بالسيرفر</div>';
      }
    }

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
        const itemsHtml = (o.items || []).map(i => esc(i.name) + ' × ' + esc(i.qty)).join('<br>');
        const date = o.created_at || '';
        return `
          <div class="order-card ${sCls}">
            <div class="order-header">
              <span class="order-id">${esc(o.order_id)}</span>
              <span class="order-status ${sCls}">${esc(o.status)}</span>
            </div>
            <div class="order-meta">${esc(o.customer_name)} | ${esc(o.phone || '-')} | ${esc(date)}</div>
            <div class="order-items">${itemsHtml}</div>
            <div class="order-total">${esc(o.total)} جنيه</div>
            ${o.notes ? '<div style="font-size:12px;color:#8b8b9a;margin-bottom:8px">ملاحظات: ' + esc(o.notes) + '</div>' : ''}
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

    loadOrders('all');
    setInterval(() => loadOrders(currentFilter), 30000);
  </script>
</body>
</html>
