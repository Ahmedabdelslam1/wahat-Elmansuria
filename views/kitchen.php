<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>شاشة المطبخ - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#0e0e14; --surface:#1a1a24; --text:#f4f4f8; --muted:#9a9aad;
      --primary:#ff3b30; --accent2:#ff9500; --new:#ff3b30; --prep:#ff9500; --ready:#34c759;
      --border:#2b2b38;
    }
    body { font-family:'Tajawal','Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); padding-bottom:20px; }
    .header {
      background: linear-gradient(135deg, #15151f, #23233a);
      padding: 14px 18px;
      display: flex; justify-content: space-between; align-items: center;
      position: sticky; top: 0; z-index: 50;
      border-bottom: 1px solid var(--border);
    }
    .header h1 { font-size: 18px; font-weight: 800; }
    .header .user { font-size: 11.5px; color: var(--accent2); }
    .logout {
      background: transparent; border: 1px solid rgba(255,255,255,0.2); color: #fff;
      padding: 7px 14px; border-radius: 10px; font-size: 12px; cursor: pointer; font-family: inherit; font-weight:700;
    }
    .clock { font-size: 13px; color: var(--muted); font-weight: 700; direction: ltr; }

    .board {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
      padding: 14px;
    }
    @media (min-width: 760px) { .board { grid-template-columns: 1fr 1fr 1fr; } }

    .col-head {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 4px; margin-bottom: 8px;
      font-weight: 800; font-size: 14px;
    }
    .col-head .count {
      background: rgba(255,255,255,0.08); padding: 2px 10px; border-radius: 20px; font-size: 12px;
    }
    .col.new .col-head { color: var(--new); }
    .col.prep .col-head { color: var(--prep); }
    .col.ready .col-head { color: var(--ready); }

    .ticket {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 12px;
    }
    .col.new .ticket { border-right: 5px solid var(--new); }
    .col.prep .ticket { border-right: 5px solid var(--prep); }
    .col.ready .ticket { border-right: 5px solid var(--ready); }

    .ticket .top { display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px; }
    .ticket .oid { font-weight: 800; font-size: 15px; }
    .ticket .time { font-size: 11px; color: var(--muted); }
    .ticket ul { list-style:none; margin-bottom: 12px; }
    .ticket li {
      display: flex; justify-content: space-between;
      font-size: 16px; font-weight: 700;
      padding: 7px 0; border-bottom: 1px dashed var(--border);
    }
    .ticket li .qty {
      background: var(--primary); color:#fff; font-size:13px; font-weight:800;
      min-width: 30px; text-align:center; border-radius: 8px; padding: 2px 6px;
    }
    .ticket .notes { font-size: 12px; color: var(--accent2); margin-bottom: 10px; }
    .ticket button {
      width: 100%; padding: 13px; border: none; border-radius: 12px;
      font-family: inherit; font-weight: 800; font-size: 14px; cursor: pointer;
    }
    .btn-prep { background: var(--prep); color: #1a1200; }
    .btn-ready { background: var(--ready); color: #05220f; }
    .btn-done { background: #2b2b38; color: #fff; }
    .empty { text-align:center; padding: 30px 10px; color: var(--muted); font-size: 13px; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <div>
      <h1>👨‍🍳 شاشة المطبخ</h1>
      <div class="user"><?= e($user['name']) ?></div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <div class="clock" id="clock">--:--:--</div>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="board">
    <div class="col new">
      <div class="col-head"><span>🔴 جديد</span><span class="count" id="cNew">0</span></div>
      <div id="listNew"></div>
    </div>
    <div class="col prep">
      <div class="col-head"><span>🟠 قيد التحضير</span><span class="count" id="cPrep">0</span></div>
      <div id="listPrep"></div>
    </div>
    <div class="col ready">
      <div class="col-head"><span>🟢 جاهز للتسليم</span><span class="count" id="cReady">0</span></div>
      <div id="listReady"></div>
    </div>
  </div>

  <script>
    function esc(s) {
      return String(s == null ? '' : s)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
    function timeOnly(dt) {
      if (!dt) return '';
      const p = dt.split(' ')[1] || dt;
      return p.slice(0, 5);
    }

    function ticketHtml(o, nextLabel, nextStatus, btnClass) {
      const items = (o.items || []).map(i =>
        `<li><span>${esc(i.name)}</span><span class="qty">× ${esc(i.qty)}</span></li>`
      ).join('');
      return `
        <div class="ticket">
          <div class="top"><span class="oid">${esc(o.order_id.replace('ORD-',''))}</span><span class="time">${timeOnly(o.created_at)}</span></div>
          <ul>${items}</ul>
          ${o.notes ? '<div class="notes">📝 ' + esc(o.notes) + '</div>' : ''}
          ${nextLabel ? `<button class="${btnClass}" onclick="updateStatus('${esc(o.order_id)}', '${nextStatus}')">${nextLabel}</button>` : ''}
        </div>
      `;
    }

    async function loadOrders() {
      try {
        const res = await api('get_orders', { status: '' });
        if (!res.success) {
          if (res.message === 'غير مصرح') { window.location.href = '?page=login'; return; }
          return;
        }
        const orders = res.orders || [];
        const news = orders.filter(o => o.status === 'جديد');
        const preps = orders.filter(o => o.status === 'قيد التحضير');
        const readys = orders.filter(o => o.status === 'جاهز');

        document.getElementById('cNew').textContent = news.length;
        document.getElementById('cPrep').textContent = preps.length;
        document.getElementById('cReady').textContent = readys.length;

        document.getElementById('listNew').innerHTML = news.length
          ? news.map(o => ticketHtml(o, 'بدء التحضير', 'قيد التحضير', 'btn-prep')).join('')
          : '<div class="empty">لا توجد طلبات جديدة</div>';
        document.getElementById('listPrep').innerHTML = preps.length
          ? preps.map(o => ticketHtml(o, 'جاهز للتسليم', 'جاهز', 'btn-ready')).join('')
          : '<div class="empty">لا يوجد طلبات قيد التحضير</div>';
        document.getElementById('listReady').innerHTML = readys.length
          ? readys.map(o => ticketHtml(o, 'تم التسليم', 'تم التسليم', 'btn-done')).join('')
          : '<div class="empty">لا توجد طلبات جاهزة</div>';
      } catch (e) {}
    }

    async function updateStatus(orderId, status) {
      try {
        const res = await api('update_status', { orderId, status });
        if (res.success) loadOrders();
      } catch (e) {}
    }

    async function doLogout() {
      try { await api('logout'); } catch (e) {}
      window.location.href = '?page=login';
    }

    function tickClock() {
      document.getElementById('clock').textContent = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Africa/Cairo', hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit'
      }).format(new Date());
    }
    tickClock();
    setInterval(tickClock, 1000);

    loadOrders();
    setInterval(loadOrders, 8000);
  </script>
</body>
</html>
