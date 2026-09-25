<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
  <title>لوحة المدير - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2;
    }
    body { font-family:'Tajawal','Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); padding-bottom: 30px; }
    .header {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      color: #fff; padding: 14px 18px;
      display: flex; justify-content: space-between; align-items: center;
    }
    .header h1 { font-size: 17px; font-weight: 800; }
    .header .user { font-size: 11.5px; color: var(--accent2); }
    .logout { background: transparent; border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 6px 13px; border-radius: 10px; font-size: 12px; cursor: pointer; font-family:inherit; font-weight:700; }
    .tabs { display: flex; background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 40; overflow-x:auto; }
    .tab { flex-shrink:0; min-width: 90px; padding: 12px 10px; text-align: center; border: none; background: none; font-size: 12.5px; font-family:inherit; font-weight:700; cursor: pointer; color: var(--muted); border-bottom: 3px solid transparent; }
    .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
    .panel { padding: 16px; display: none; }
    .panel.active { display: block; }
    .card { background: var(--surface); border-radius: 18px; padding: 16px; margin-bottom: 14px; box-shadow: 0 2px 10px rgba(20,20,30,0.05); border: 1px solid var(--border); }
    .card h3 { font-size: 14px; font-weight: 800; margin-bottom: 10px; }
    .form-row { margin-bottom: 10px; }
    .form-row label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 4px; font-weight:700; }
    .form-row input, .form-row select {
      width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px; font-size: 14px; font-family: inherit; background: var(--bg);
    }
    button.primary { background: linear-gradient(90deg,var(--primary),var(--primary-dark)); color: #fff; border: none; padding: 11px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; font-size: 13px; font-family: inherit; box-shadow:0 8px 20px -6px rgba(255,59,48,0.4); }
    button.primary:disabled { opacity: 0.6; cursor: wait; }
    .user-row, .menu-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); font-size: 13px; gap:10px; flex-wrap:wrap; }
    .menu-row button, .user-row button { font-size: 11px; padding: 5px 11px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); cursor: pointer; font-family:inherit; font-weight:700; }
    .logout { }

    /* KPI */
    .kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    @media (min-width:640px) { .kpis { grid-template-columns: repeat(4,1fr); } }
    .kpi { background: var(--surface); border-radius: 16px; padding: 14px; box-shadow: 0 2px 10px rgba(20,20,30,0.05); border: 1px solid var(--border); position:relative; overflow:hidden; }
    .kpi .icon { width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;margin-bottom:8px; }
    .kpi .num { font-size: 21px; font-weight: 800; }
    .kpi .label { font-size: 11px; color: var(--muted); margin-top: 2px; font-weight:700; }
    .kpi.red .icon { background:#ffebe9;color:#ff3b30; }
    .kpi.orange .icon { background:#fff3e0;color:#ff9500; }
    .kpi.green .icon { background:#e7f8ec;color:#34c759; }
    .kpi.blue .icon { background:#e5f0ff;color:#007aff; }

    .chart-wrap { position: relative; height: 220px; }
    .status-legend { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .status-legend span { font-size:11px; padding:4px 10px; border-radius:20px; font-weight:700; background:var(--bg); }
    .top-item-row { display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border); }
    .top-item-row .bar { flex:1; height:8px; border-radius:6px; background:var(--bg); overflow:hidden; }
    .top-item-row .bar-fill { height:100%; background:linear-gradient(90deg,var(--primary),var(--accent2)); border-radius:6px; }
    .top-item-row .name { font-size:12.5px; font-weight:700; width:130px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .top-item-row .qty { font-size:12px; color:var(--muted); font-weight:700; width:44px; text-align:left; }

    .perm-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
    .perm-chip { display:flex; align-items:center; gap:5px; font-size:11px; padding:4px 9px; border-radius:14px; background:var(--bg); border:1px solid var(--border); cursor:pointer; font-weight:700; }
    .perm-chip input { accent-color: var(--primary); }
    .role-badge { font-size:10px; padding:2px 9px; border-radius:12px; font-weight:800; }
    .role-badge.admin { background:#ffebe9;color:#ff3b30; }
    .role-badge.cashier { background:#fff3e0;color:#ff9500; }
    .role-badge.kitchen { background:#e7f8ec;color:#34c759; }
    .role-badge.customer { background:#e5f0ff;color:#007aff; }
    .menu-thumb { width:44px;height:44px;border-radius:10px;object-fit:cover;flex-shrink:0; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>⚙️ لوحة المدير</h1>
      <div class="user"><?= e($user['name']) ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
      <a href="?page=cashier" style="color:#ff9500;font-size:12px;text-decoration:none;font-weight:700">الكاشير</a>
      <a href="?page=kitchen" style="color:#34c759;font-size:12px;text-decoration:none;font-weight:700">المطبخ</a>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="tabs">
    <button class="tab active" onclick="showPanel('dashboard', this)">📊 نظرة عامة</button>
    <button class="tab" onclick="showPanel('menu', this)">🍽️ المنيو</button>
    <button class="tab" onclick="showPanel('users', this)">👥 المستخدمين</button>
    <button class="tab" onclick="showPanel('orders', this)">📋 الطلبات</button>
    <button class="tab" onclick="showPanel('reports', this)">📈 التقارير</button>
  </div>

  <!-- Dashboard -->
  <div class="panel active" id="panel-dashboard">
    <div class="kpis">
      <div class="kpi red"><div class="icon">💰</div><div class="num" id="kpiTodaySales">-</div><div class="label">مبيعات اليوم (ج.م)</div></div>
      <div class="kpi orange"><div class="icon">🧾</div><div class="num" id="kpiTodayOrders">-</div><div class="label">طلبات اليوم</div></div>
      <div class="kpi green"><div class="icon">📅</div><div class="num" id="kpiWeekSales">-</div><div class="label">مبيعات آخر 7 أيام</div></div>
      <div class="kpi blue"><div class="icon">⭐</div><div class="num" id="kpiAvgOrder">-</div><div class="label">متوسط قيمة الطلب</div></div>
    </div>

    <div class="card">
      <h3>مبيعات آخر 7 أيام</h3>
      <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
    </div>

    <div class="card">
      <h3>حالة الطلبات</h3>
      <div class="chart-wrap" style="height:180px"><canvas id="statusChart"></canvas></div>
    </div>

    <div class="card">
      <h3>الأصناف الأكثر طلبًا (آخر 60 يوم)</h3>
      <div id="topItemsBox"></div>
    </div>

    <div class="card">
      <h3>روابط سريعة</h3>
      <p style="font-size:13px;margin-top:8px;line-height:2">
        <a href="?page=cashier" style="color:var(--primary);font-weight:700">← فتح لوحة الكاشير</a><br>
        <a href="?page=kitchen" style="color:var(--primary);font-weight:700">← فتح شاشة المطبخ</a><br>
        <a href="?page=menu" style="color:var(--primary);font-weight:700">← فتح منيو العملاء</a>
      </p>
    </div>
  </div>

  <!-- Menu Management -->
  <div class="panel" id="panel-menu">
    <div class="card">
      <h3 id="itemFormTitle">إضافة صنف جديد</h3>
      <div class="form-row"><label>الاسم</label><input id="itemName"></div>
      <div class="form-row"><label>القسم</label><input id="itemCat" placeholder="مثل: المشويات بالكيلو"></div>
      <div class="form-row"><label>السعر (ج.م)</label><input id="itemPrice" type="number"></div>
      <div class="form-row"><label>الوصف (اختياري)</label><input id="itemDesc"></div>
      <div class="form-row"><label>رابط صورة حقيقية (اختياري - يُستخدم صورة القسم تلقائيًا إن تُرك فارغًا)</label><input id="itemImage" placeholder="https://..."></div>
      <input type="hidden" id="itemId" value="">
      <button class="primary" id="saveItemBtn" onclick="saveItem()">حفظ الصنف</button>
      <button class="primary" id="cancelEditBtn" style="background:#eee;color:#555;display:none;margin-right:6px" onclick="cancelEditItem()">إلغاء</button>
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
          <option value="cashier" selected>كاشير</option>
          <option value="kitchen">مطبخ</option>
          <option value="customer">عميل</option>
        </select>
      </div>
      <div class="form-row">
        <label>صلاحيات فتح صفحات إضافية (اختياري - غير الأدوار الافتراضية)</label>
        <div class="perm-chips" id="newUserPerms">
          <label class="perm-chip"><input type="checkbox" value="cashier">الكاشير</label>
          <label class="perm-chip"><input type="checkbox" value="kitchen">المطبخ</label>
          <label class="perm-chip"><input type="checkbox" value="admin">لوحة المدير</label>
          <label class="perm-chip"><input type="checkbox" value="invoice">الفواتير</label>
        </div>
      </div>
      <button class="primary" id="addUserBtn" onclick="addNewUser()">إضافة</button>
    </div>
    <div class="card">
      <h3>المستخدمون الحاليون</h3>
      <div id="usersList">جاري التحميل...</div>
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

    let trendChart = null, statusChart = null;
    const PAGE_LABELS = { admin: 'لوحة المدير', cashier: 'الكاشير', kitchen: 'المطبخ', menu: 'المنيو', cart: 'السلة', invoice: 'الفواتير' };

    function showPanel(id, btn) {
      document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.getElementById('panel-' + id).classList.add('active');
      btn.classList.add('active');
      if (id === 'menu') loadMenuAdmin();
      if (id === 'orders') loadAdminOrders();
      if (id === 'dashboard') loadDashboard();
      if (id === 'users') loadUsers();
      if (id === 'reports') {
        document.getElementById('reportDate').valueAsDate = new Date();
        loadDailyReport();
      }
    }

    // ===== Dashboard =====
    function cssVar(name) { return getComputedStyle(document.body).getPropertyValue(name).trim(); }

    async function loadDashboard() {
      try {
        const res = await api('dashboard');
        if (!res.success) return;
        const d = res.dashboard;
        document.getElementById('kpiTodaySales').textContent = Math.round(d.today.sales);
        document.getElementById('kpiTodayOrders').textContent = d.today.orders;
        document.getElementById('kpiWeekSales').textContent = Math.round(d.week.sales);
        document.getElementById('kpiAvgOrder').textContent = d.avgOrder;

        const labels = d.trend.map(t => t.date.slice(5));
        const sales = d.trend.map(t => t.sales);
        const ctx1 = document.getElementById('trendChart').getContext('2d');
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(ctx1, {
          type: 'bar',
          data: { labels, datasets: [{ label: 'مبيعات', data: sales, backgroundColor: '#ff3b30', borderRadius: 6, borderWidth: 0 }] },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: '#8b8b9a' }, grid: { display: false } },
              y: { ticks: { color: '#8b8b9a' }, grid: { color: '#ececf2' } }
            }
          }
        });

        const statusLabels = Object.keys(d.byStatus);
        const statusData = Object.values(d.byStatus);
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(ctx2, {
          type: 'doughnut',
          data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#ff3b30','#ff9500','#34c759','#007aff','#af52de'], borderWidth: 0 }] },
          options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: '#181822', font: { family: 'Tajawal' } } } } }
        });

        const maxQty = Math.max(1, ...d.topItems.map(i => i.qty));
        document.getElementById('topItemsBox').innerHTML = d.topItems.length ? d.topItems.map(i => `
          <div class="top-item-row">
            <span class="name">${esc(i.name)}</span>
            <span class="bar"><span class="bar-fill" style="width:${(i.qty/maxQty*100)}%"></span></span>
            <span class="qty">× ${i.qty}</span>
          </div>
        `).join('') : '<p style="font-size:13px;color:var(--muted)">لا توجد بيانات كافية بعد</p>';
      } catch (e) {}
    }

    // ===== Reports =====
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
          <div class="kpis">
            <div class="kpi red"><div class="icon">🧾</div><div class="num">${r.totalOrders}</div><div class="label">عدد الطلبات</div></div>
            <div class="kpi green"><div class="icon">💰</div><div class="num">${r.totalSales}</div><div class="label">إجمالي المبيعات (ج.م)</div></div>
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

    // ===== Orders quick view =====
    async function loadAdminOrders() {
      try {
        const res = await api('get_orders', { status: '' });
        const box = document.getElementById('adminOrders');
        if (!res.success) { box.innerHTML = 'خطأ'; return; }
        box.innerHTML = res.orders.slice(0, 30).map(o => `
          <div class="user-row">
            <span>${esc(o.order_id)} — ${esc(o.customer_name)}</span>
            <strong>${o.status} · ${o.total} ج.م</strong>
          </div>
        `).join('') || '<p>لا توجد طلبات</p>';
      } catch (e) {}
    }

    // ===== Menu management =====
    let editingItemId = null;
    async function loadMenuAdmin() {
      try {
        const res = await api('menu');
        if (!res.success) return;
        const container = document.getElementById('menuList');
        container.style.display = 'block';
        container.innerHTML = '<h3 style="margin-bottom:10px">الأصناف الحالية (' + res.items.length + ')</h3>';
        res.items.forEach(i => {
          const row = document.createElement('div');
          row.className = 'menu-row';
          row.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:180px">
              <img class="menu-thumb" src="${i.image}" alt="">
              <div>
                <strong>${esc(i.name)}</strong><br>
                <span style="color:var(--muted);font-size:12px">${esc(i.category)} — ${i.price} ج.م</span>
              </div>
            </div>
            <button onclick='editItem(${JSON.stringify(i).replace(/'/g, "&#39;")})'>تعديل</button>
          `;
          container.appendChild(row);
        });
      } catch (e) {}
    }

    function editItem(item) {
      editingItemId = item.id;
      document.getElementById('itemFormTitle').textContent = 'تعديل صنف: ' + item.name;
      document.getElementById('itemId').value = item.id;
      document.getElementById('itemName').value = item.name;
      document.getElementById('itemCat').value = item.category;
      document.getElementById('itemPrice').value = item.price;
      document.getElementById('itemDesc').value = item.desc || '';
      document.getElementById('itemImage').value = item.image || '';
      document.getElementById('cancelEditBtn').style.display = 'inline-block';
      document.getElementById('saveItemBtn').textContent = 'حفظ التعديل';
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function cancelEditItem() {
      editingItemId = null;
      document.getElementById('itemFormTitle').textContent = 'إضافة صنف جديد';
      ['itemId','itemName','itemCat','itemPrice','itemDesc','itemImage'].forEach(id => document.getElementById(id).value = '');
      document.getElementById('cancelEditBtn').style.display = 'none';
      document.getElementById('saveItemBtn').textContent = 'حفظ الصنف';
    }

    async function saveItem() {
      const item = {
        id: editingItemId || 0,
        name: document.getElementById('itemName').value.trim(),
        category: document.getElementById('itemCat').value.trim(),
        price: parseFloat(document.getElementById('itemPrice').value) || 0,
        desc: document.getElementById('itemDesc').value.trim(),
        image: document.getElementById('itemImage').value.trim(),
      };
      if (!item.name || !item.price) { alert('أدخل الاسم والسعر'); return; }
      const btn = document.getElementById('saveItemBtn');
      btn.disabled = true;
      try {
        const res = await api('save_item', item);
        btn.disabled = false;
        if (res.success) { cancelEditItem(); loadMenuAdmin(); }
        else alert(res.message || 'خطأ');
      } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
    }

    // ===== Users management =====
    async function loadUsers() {
      try {
        const res = await api('list_users');
        const box = document.getElementById('usersList');
        if (!res.success) { box.innerHTML = 'غير مصرح'; return; }
        box.innerHTML = res.users.map(u => {
          const allPages = ['cashier','kitchen','admin','invoice'];
          const chips = allPages.map(p => `
            <label class="perm-chip">
              <input type="checkbox" data-uid="${u.id}" value="${p}" ${u.pages.includes(p) ? 'checked' : ''} ${u.role==='admin' ? 'disabled' : ''}>
              ${PAGE_LABELS[p]}
            </label>
          `).join('');
          return `
            <div class="user-row" style="flex-direction:column;align-items:stretch">
              <div style="display:flex;justify-content:space-between;align-items:center">
                <span><strong>${esc(u.name)}</strong> (${esc(u.username)}) <span class="role-badge ${u.role}">${u.role}</span></span>
                <button onclick="saveUserPerms(${u.id})">حفظ</button>
              </div>
              <div class="perm-chips" id="perms-${u.id}">${u.role === 'admin' ? '<span style="font-size:11px;color:var(--muted)">المدير يملك كل الصفحات دائمًا</span>' : chips}</div>
            </div>
          `;
        }).join('');
      } catch (e) {}
    }

    async function saveUserPerms(uid) {
      const boxes = document.querySelectorAll(`#perms-${uid} input[type=checkbox]`);
      const permissions = Array.from(boxes).filter(b => b.checked).map(b => b.value);
      try {
        const res = await api('update_permissions', { id: uid, permissions });
        if (!res.success) alert(res.message || 'خطأ');
      } catch (e) { alert('خطأ في الاتصال'); }
    }

    async function addNewUser() {
      const permissions = Array.from(document.querySelectorAll('#newUserPerms input:checked')).map(b => b.value);
      const user = {
        username: document.getElementById('newUsername').value.trim(),
        password: document.getElementById('newPassword').value,
        name: document.getElementById('newName').value.trim(),
        role: document.getElementById('newRole').value,
        permissions,
      };
      if (!user.username || !user.password || !user.name) { alert('أكمل جميع الحقول'); return; }
      const btn = document.getElementById('addUserBtn');
      btn.disabled = true;
      try {
        const res = await api('add_user', user);
        btn.disabled = false;
        if (res.success) {
          ['newUsername','newPassword','newName'].forEach(id => document.getElementById(id).value = '');
          document.querySelectorAll('#newUserPerms input').forEach(b => b.checked = false);
          loadUsers();
        } else alert(res.message || 'خطأ');
      } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
    }

    async function doLogout() {
      try { await api('logout'); } catch (e) {}
      window.location.href = '?page=login';
    }

    loadDashboard();
  </script>
</body>
</html>
