<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>لوحة المدير - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif; background: #f4f5fa; color: #212121; }
    .header {
      background: linear-gradient(160deg, #15151f, #1f1f2c);
      color: #ff3b30;
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .header h1 { font-size: 18px; }
    .tabs {
      display: flex;
      background: #fff;
      border-bottom: 1px solid #eee;
      position: sticky;
      top: 0;
      z-index: 40;
    }
    .tab {
      flex: 1;
      padding: 12px;
      text-align: center;
      border: none;
      background: none;
      font-size: 13px;
      cursor: pointer;
      color: #757575;
      border-bottom: 3px solid transparent;
    }
    .tab.active { color: #ff3b30; border-bottom-color: #ff3b30; font-weight: bold; }
    .panel { padding: 16px; display: none; }
    .panel.active { display: block; }
    .card {
      background: #fff;
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .card h3 { font-size: 14px; margin-bottom: 6px; }
    .form-row { margin-bottom: 10px; }
    .form-row label { display: block; font-size: 12px; color: #757575; margin-bottom: 4px; }
    .form-row input, .form-row select {
      width: 100%;
      padding: 10px;
      border: 1px solid #ececf2;
      border-radius: 8px;
      font-size: 14px;
    }
    button.primary {
      background: #ff3b30;
      color: #15151f;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
      font-size: 13px;
    }
    button.primary:disabled { opacity: 0.6; cursor: wait; }
    .user-row, .menu-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #ececf2;
      font-size: 13px;
    }
    .menu-row button { font-size: 11px; padding: 4px 10px; border-radius: 6px; border: 1px solid #ff3b30; background: #fff; color: #3a3a4d; cursor: pointer; }
    .logout {
      background: transparent;
      border: 1px solid #ff3b30;
      color: #ff3b30;
      padding: 5px 12px;
      border-radius: 8px;
      font-size: 12px;
      cursor: pointer;
    }
    .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
    .stat-card {
      background: #fff;
      border-radius: 16px;
      padding: 16px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .stat-card .num { font-size: 24px; font-weight: bold; color: #ff3b30; }
    .stat-card .label { font-size: 12px; color: #757575; margin-top: 4px; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>⚙️ لوحة المدير</h1>
      <div style="font-size:12px;color:#ffb199"><?= e($user['name']) ?></div>
    </div>
    <div>
      <a href="?page=cashier" style="color:#ff3b30;font-size:12px;text-decoration:none;margin-left:10px">لوحة الكاشير</a>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="showPanel('dashboard', this)">نظرة عامة</button>
    <button class="tab" onclick="showPanel('menu', this)">المنيو</button>
    <button class="tab" onclick="showPanel('users', this)">المستخدمين</button>
    <button class="tab" onclick="showPanel('orders', this)">الطلبات</button>
    <button class="tab" onclick="showPanel('reports', this)">التقارير</button>
  </div>

  <!-- Dashboard -->
  <div class="panel active" id="panel-dashboard">
    <div class="stats">
      <div class="stat-card"><div class="num" id="statOrders">-</div><div class="label">إجمالي الطلبات</div></div>
      <div class="stat-card"><div class="num" id="statNew">-</div><div class="label">طلبات جديدة</div></div>
    </div>
    <div class="card">
      <h3>روابط سريعة</h3>
      <p style="font-size:13px;margin-top:8px">
        <a href="?page=cashier" style="color:#ff3b30">← فتح لوحة الكاشير</a><br>
        <a href="?page=menu" style="color:#ff3b30">← فتح منيو العملاء</a>
      </p>
    </div>
  </div>

  <!-- Menu Management -->
  <div class="panel" id="panel-menu">
    <div class="card">
      <h3>إضافة صنف</h3>
      <div class="form-row"><label>الاسم</label><input id="itemName"></div>
      <div class="form-row"><label>القسم</label><input id="itemCat" placeholder="مثل: دجاج مندي"></div>
      <div class="form-row"><label>السعر</label><input id="itemPrice" type="number"></div>
      <div class="form-row"><label>الوصف</label><input id="itemDesc"></div>
      <button class="primary" id="saveItemBtn" onclick="saveItem()">حفظ الصنف</button>
    </div>
    <div id="menuList" class="card" style="display:none"></div>
  </div>

  <!-- Users -->
  <div class="panel" id="panel-users">
    <div class="card">
      <h3>إضافة مستخدم جديد</h3>
      <div class="form-row"><label>اسم المستخدم</label><input id="newUsername"></div>
      <div class="form-row"><label>كلمة المرور</label><input id="newPassword" type="password"></div>
      <div class="form-row"><label>الاسم الظاهر</label><input id="newName"></div>
      <div class="form-row">
        <label>الدور</label>
        <select id="newRole">
          <option value="admin">مدير</option>
          <option value="cashier">كاشير</option>
          <option value="customer">عميل</option>
        </select>
      </div>
      <button class="primary" id="addUserBtn" onclick="addNewUser()">إضافة</button>
    </div>
  </div>

  <!-- Orders quick view -->
  <div class="panel" id="panel-orders">
    <div id="adminOrders" class="card">جاري التحميل...</div>
  </div>

  <!-- Reports -->
  <div class="panel" id="panel-reports">
    <div class="card">
      <h3>تقرير المبيعات اليومي</h3>
      <div class="form-row">
        <label>التاريخ</label>
        <input type="date" id="reportDate">
      </div>
      <button class="primary" onclick="loadDailyReport()">عرض التقرير</button>
    </div>
    <div id="reportResult"></div>
  </div>

  <script>
    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function showPanel(id, btn) {
      document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.getElementById('panel-' + id).classList.add('active');
      btn.classList.add('active');
      if (id === 'menu') loadMenuAdmin();
      if (id === 'orders') loadAdminOrders();
      if (id === 'dashboard') loadStats();
      if (id === 'reports') {
        document.getElementById('reportDate').valueAsDate = new Date();
        loadDailyReport();
      }
    }

    async function loadDailyReport() {
      const date = document.getElementById('reportDate').value;
      try {
        const res = await api('report', { date });
        if (!res.success) {
          document.getElementById('reportResult').innerHTML = '<div class="card">' + esc(res.message) + '</div>';
          return;
        }
        const r = res.report;
        const html = `
          <div class="stats">
            <div class="stat-card"><div class="num">${r.totalOrders}</div><div class="label">عدد الطلبات</div></div>
            <div class="stat-card"><div class="num">${r.totalSales}</div><div class="label">إجمالي المبيعات (جنيه)</div></div>
          </div>
          <div class="card"><h3>حسب الحالة</h3>
            ${Object.entries(r.byStatus || {}).map(([s,c]) => `<div class="user-row"><span>${esc(s)}</span><strong>${c}</strong></div>`).join('') || '<p>لا توجد بيانات</p>'}
          </div>
          <div class="card"><h3>الأصناف الأكثر مبيعًا</h3>
            ${(r.topItems || []).map(i => `<div class="user-row"><span>${esc(i.name)}</span><strong>× ${i.qty}</strong></div>`).join('') || '<p>لا توجد بيانات</p>'}
          </div>
        `;
        document.getElementById('reportResult').innerHTML = html;
      } catch (e) {
        document.getElementById('reportResult').innerHTML = '<div class="card">خطأ في الاتصال بالسيرفر</div>';
      }
    }

    async function loadStats() {
      try {
        const res = await api('get_orders', { status: '' });
        if (res.success) {
          document.getElementById('statOrders').textContent = res.orders.length;
          document.getElementById('statNew').textContent = res.orders.filter(o => o.status === 'جديد').length;
        }
      } catch (e) {}
    }

    async function loadMenuAdmin() {
      try {
        const res = await api('menu');
        if (!res.success) return;
        const container = document.getElementById('menuList');
        container.style.display = 'block';
        container.innerHTML = '';
        res.items.forEach(i => {
          const row = document.createElement('div');
          row.className = 'menu-row';
          const left = document.createElement('div');
          const strong = document.createElement('strong');
          strong.textContent = i.name;
          const sub = document.createElement('span');
          sub.style.cssText = 'color:#757575;font-size:12px';
          sub.textContent = i.category + ' - ' + i.price;
          left.append(strong, document.createElement('br'), sub);
          row.appendChild(left);
          container.appendChild(row);
        });
      } catch (e) {}
    }

    async function saveItem() {
      const item = {
        name: document.getElementById('itemName').value.trim(),
        category: document.getElementById('itemCat').value.trim(),
        price: Number(document.getElementById('itemPrice').value),
        desc: document.getElementById('itemDesc').value.trim()
      };
      if (!item.name || !item.price) { alert('أدخل الاسم والسعر'); return; }
      const btn = document.getElementById('saveItemBtn');
      btn.disabled = true;
      try {
        const res = await api('save_item', item);
        btn.disabled = false;
        alert(res.message || 'تم');
        if (res.success) {
          document.getElementById('itemName').value = '';
          document.getElementById('itemPrice').value = '';
          loadMenuAdmin();
        }
      } catch (e) {
        btn.disabled = false;
        alert('خطأ: ' + (e.message || ''));
      }
    }

    async function addNewUser() {
      const user = {
        username: document.getElementById('newUsername').value.trim(),
        password: document.getElementById('newPassword').value,
        name: document.getElementById('newName').value.trim(),
        role: document.getElementById('newRole').value
      };
      if (!user.username || !user.password || !user.name) {
        alert('أكمل جميع الحقول');
        return;
      }
      const btn = document.getElementById('addUserBtn');
      btn.disabled = true;
      try {
        const res = await api('add_user', user);
        btn.disabled = false;
        alert(res.message || 'تم');
        if (res.success) {
          document.getElementById('newUsername').value = '';
          document.getElementById('newPassword').value = '';
          document.getElementById('newName').value = '';
        }
      } catch (e) {
        btn.disabled = false;
        alert('خطأ: ' + (e.message || ''));
      }
    }

    async function loadAdminOrders() {
      try {
        const res = await api('get_orders', { status: '' });
        if (!res.success) {
          document.getElementById('adminOrders').innerHTML = esc(res.message);
          return;
        }
        document.getElementById('adminOrders').innerHTML = res.orders.slice(0, 20).map(o => `
          <div class="card">
            <strong>${esc(o.order_id)}</strong> - ${esc(o.status)}<br>
            <span style="font-size:12px">${esc(o.customer_name)} | ${esc(o.total)} جنيه | ${esc(o.created_at)}</span>
          </div>
        `).join('') || '<div class="card">لا توجد طلبات</div>';
      } catch (e) {
        document.getElementById('adminOrders').innerHTML = 'خطأ في الاتصال';
      }
    }

    async function doLogout() {
      try { await api('logout'); } catch (e) {}
      window.location.href = '?page=login';
    }

    loadStats();
  </script>
</body>
</html>
