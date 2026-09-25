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
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: #f4f5fa;
      color: #212121;
      padding-bottom: 20px;
    }
    .header {
      background: linear-gradient(160deg, #15151f, #1f1f2c);
      color: #ff3b30;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
    }
    .header h1 { font-size: 18px; }
    .header .user { font-size: 12px; color: #ffb199; }
    .filters {
      display: flex;
      gap: 8px;
      padding: 12px 16px;
      overflow-x: auto;
      background: #fff;
      border-bottom: 1px solid #eee;
    }
    .filter-btn {
      flex-shrink: 0;
      padding: 7px 14px;
      border-radius: 20px;
      border: none;
      background: #eee;
      font-size: 13px;
      cursor: pointer;
    }
    .filter-btn.active { background: #ff3b30; color: #15151f; font-weight: bold; }
    .orders { padding: 12px 16px; display: flex; flex-direction: column; gap: 12px; }
    .order-card {
      background: #fff;
      border-radius: 18px;
      padding: 14px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
      border-right: 4px solid #ff3b30;
    }
    .order-card.new { border-right-color: #e53935; }
    .order-card.preparing { border-right-color: #fb8c00; }
    .order-card.ready { border-right-color: #43a047; }
    .order-card.done { border-right-color: #1565c0; }
    .order-header { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .order-id { font-weight: bold; color: #1f1f2c; font-size: 14px; }
    .order-status { font-size: 11px; padding: 3px 10px; border-radius: 16px; background: #eee; }
    .order-status.new { background: #ffebee; color: #c62828; }
    .order-status.preparing { background: #fff3e0; color: #ef6c00; }
    .order-status.ready { background: #e8f5e9; color: #2e7d32; }
    .order-status.done { background: #e3f2fd; color: #1565c0; }
    .order-meta { font-size: 12px; color: #757575; margin-bottom: 8px; }
    .order-items { font-size: 13px; margin-bottom: 10px; line-height: 1.5; }
    .order-total { font-weight: bold; color: #e8590c; font-size: 15px; margin-bottom: 10px; }
    .actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .actions button {
      padding: 7px 12px;
      border-radius: 8px;
      border: none;
      font-size: 12px;
      cursor: pointer;
      font-weight: 600;
    }
    .btn-prep { background: #fff3e0; color: #ef6c00; }
    .btn-ready { background: #e8f5e9; color: #2e7d32; }
    .btn-done { background: #e3f2fd; color: #1565c0; }
    .empty { text-align: center; padding: 40px; color: #8b8b9a; }
    .logout {
      background: transparent;
      border: 1px solid #ff3b30;
      color: #ff3b30;
      padding: 5px 12px;
      border-radius: 8px;
      font-size: 12px;
      cursor: pointer;
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>🧾 لوحة الكاشير</h1>
      <div class="user"><?= e($user['name']) ?> (<?= e($user['role']) ?>)</div>
    </div>
    <div>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="?page=admin" style="color:#ff3b30;font-size:12px;text-decoration:none;margin-left:10px">لوحة المدير</a>
      <?php endif; ?>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="filters">
    <button class="filter-btn active" onclick="loadOrders('all', this)">الكل</button>
    <button class="filter-btn" onclick="loadOrders('جديد', this)">جديد</button>
    <button class="filter-btn" onclick="loadOrders('قيد التحضير', this)">قيد التحضير</button>
    <button class="filter-btn" onclick="loadOrders('جاهز', this)">جاهز</button>
    <button class="filter-btn" onclick="loadOrders('تم التسليم', this)">تم التسليم</button>
  </div>

  <div class="orders" id="ordersList">
    <div class="empty">جاري التحميل...</div>
  </div>

  <script>
    let currentFilter = 'all';

    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
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
            ${o.notes ? '<div style="font-size:12px;color:#757575;margin-bottom:8px">ملاحظات: ' + esc(o.notes) + '</div>' : ''}
            <div class="actions">
              ${o.status === 'جديد' ? '<button class="btn-prep" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'قيد التحضير\')">بدء التحضير</button>' : ''}
              ${o.status === 'قيد التحضير' ? '<button class="btn-ready" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'جاهز\')">جاهز</button>' : ''}
              ${o.status === 'جاهز' ? '<button class="btn-done" onclick="updateStatus(\'' + esc(o.order_id) + '\', \'تم التسليم\')">تم التسليم</button>' : ''}
              <button class="btn-prep" style="background:#e3f2fd;color:#1565c0" onclick="window.open('?page=invoice&id=${encodeURIComponent(o.order_id)}', '_blank')">🖨️ فاتورة</button>
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

    // تحديث تلقائي كل 30 ثانية
    loadOrders('all');
    setInterval(() => loadOrders(currentFilter), 30000);
  </script>
</body>
</html>
