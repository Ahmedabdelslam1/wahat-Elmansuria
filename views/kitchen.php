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
      --bg:#ffffff; --surface:#f7f7fa; --text:#221a20; --muted:#7a707a;
      --primary:#ff3b30; --accent2:#ff9500; --new:#ff3b30; --prep:#e08600; --ready:#1f9e46;
      --border:#e7e3e8;
    }
    body { font-family:'Tajawal','Segoe UI',Tahoma,sans-serif; background:var(--bg); color:var(--text); padding-bottom:20px; }
    .header {
      background: linear-gradient(135deg, #ff3b30, #ff9500);
      padding: 12px 16px;
      display: flex; justify-content: space-between; align-items: center;
      position: sticky; top: 0; z-index: 50;
      border-bottom: 1px solid var(--border);
      flex-wrap: wrap; gap:8px;
      box-shadow: 0 2px 10px rgba(255,59,48,0.25);
    }
    .header h1 { font-size: 17px; font-weight: 800; color:#fff; }
    .header-logo { width: 34px; height: 34px; border-radius: 10px; object-fit: cover; background:#fff; flex-shrink:0; }
    .header-brand { display:flex; align-items:center; gap:8px; }
    .header .user { font-size: 11px; color: #fff; opacity:.9; }
    .logout, .sound-btn {
      background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.4); color: #fff;
      padding: 7px 13px; border-radius: 10px; font-size: 11.5px; cursor: pointer; font-family: inherit; font-weight:700;
    }
    .sound-btn.on { background:#1f9e46; border-color:#1f9e46; color:#fff; }
    .clock { font-size: 12.5px; color: #fff; opacity:.9; font-weight: 700; direction: ltr; }

    .stats-bar { display:flex; gap:10px; padding:10px 14px; overflow-x:auto; }
    .stat-chip { flex-shrink:0; background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:8px 16px; text-align:center; min-width:100px; }
    .stat-chip b { display:block; font-size:16px; font-weight:800; }
    .co-section { margin:14px 16px 4px; }
    .co-section-head { display:flex; align-items:center; justify-content:space-between; cursor:pointer; user-select:none; gap:8px; }
    .co-section-head h3 { font-size:14px; font-weight:800; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .co-badge { background:#fff3e0; color:#c77700; border-radius:20px; padding:3px 11px; font-size:11.5px; font-weight:800; }
    .co-toggle { width:26px; height:26px; border-radius:50%; border:1px solid var(--border); background:#fff3e0; color:#c77700; display:flex; align-items:center; justify-content:center; font-size:12px; flex-shrink:0; transition:transform .2s, box-shadow .2s; box-shadow:0 0 8px -1px rgba(255,149,0,0.4); }
    .co-section.expanded .co-toggle { transform:rotate(180deg); box-shadow:0 0 12px 0 rgba(255,149,0,0.7); }
    .co-body { display:none; margin-top:10px; }
    .co-section.expanded .co-body { display:block; }
    .co-companies { display:flex; gap:10px; overflow-x:auto; padding-bottom:6px; }
    .co-company-card { flex:0 0 auto; min-width:250px; max-width:340px; background:#fff; border:1px solid var(--border); border-radius:14px; padding:12px; box-shadow:0 2px 8px rgba(20,20,30,0.05); }
    .co-company-card .co-cname { font-weight:800; font-size:13px; margin-bottom:8px; color:#1f9e46; }
    .co-table { width:100%; border-collapse:collapse; font-size:11.5px; }
    .co-table th { text-align:right; font-size:10px; font-weight:800; color:var(--muted); padding:4px 4px; border-bottom:2px solid var(--border); }
    .co-table td { padding:6px 4px; border-bottom:1px dashed var(--border); vertical-align:top; }
    .co-table tr:last-of-type td { border-bottom:none; }
    .co-table .co-dept-cell { color:#7a707a; font-weight:700; white-space:nowrap; }
    .co-table .co-meal-cell { font-weight:700; }
    .co-table .co-count-cell { font-weight:800; color:#1f9e46; text-align:center; white-space:nowrap; }
    .co-table .co-notes-cell { font-size:10.5px; color:#c77700; font-weight:700; }
    .co-company-card .co-sum { margin-top:8px; padding-top:8px; border-top:2px solid var(--border); font-size:12px; font-weight:800; display:flex; justify-content:space-between; }
    .co-date-nav, .toolbar-row { display:flex; align-items:center; gap:6px; margin-bottom:10px; flex-wrap:wrap; }
    .co-date-nav button, .toolbar-btn { border:1px solid var(--border); background:#fff; color:var(--text); font-family:inherit; font-weight:700; font-size:11px; padding:6px 9px; border-radius:8px; cursor:pointer; }
    .co-date-nav input[type=date] { border:1px solid var(--border); background:#fff; color:var(--text); font-family:inherit; font-size:11.5px; padding:5px 7px; border-radius:8px; }
    .co-date-nav #coTodayBtn { background:#fff3e0; color:#c77700; border-color:#fff3e0; }
    .toolbar-btn.wa-btn { background:#e7f8ec; color:#1f9e46; border-color:#e7f8ec; }
    .toolbar-row { padding:0 16px 8px; margin-bottom:0; }
    .stat-chip span { font-size:10px; color:var(--muted); font-weight:700; }

    .orders-wrap { padding: 6px 14px 14px; }
    .orders-tbl {
      width:100%; border-collapse:collapse; background:var(--surface);
      border-radius:14px; overflow:hidden; box-shadow:0 2px 8px rgba(20,20,30,0.05);
    }
    .orders-tbl th { background:#f4f4f4; font-size:11px; padding:9px 6px; text-align:right; white-space:nowrap; }
    .orders-tbl td { font-size:12px; padding:8px 6px; border-top:1px solid var(--border); vertical-align:middle; }
    .orders-tbl .qty-cell { font-weight:800; color:var(--primary); white-space:nowrap; }
    .orders-tbl .phone-cell { white-space:nowrap; direction:ltr; text-align:right; font-size:11px; }
    .orders-tbl .items-cell { font-weight:700; }
    .orders-tbl .notes-cell { font-size:10.5px; color:#c77700; font-weight:700; max-width:140px; }
    .type-tag { font-size:9px; font-weight:800; padding:2px 7px; border-radius:12px; white-space:nowrap; }
    .type-tag.dinein { background:#e8f2ff; color:#1c6fd9; }
    .type-tag.delivery { background:#fff3e0; color:#c77700; }
    .type-tag.company { background:#e7f8ec; color:#1f9e46; }
    .status-tag { font-size:9px; font-weight:800; padding:2px 7px; border-radius:12px; white-space:nowrap; }
    .status-tag.st-new { background:rgba(255,59,48,0.12); color:var(--new); }
    .status-tag.st-prep { background:rgba(224,134,0,0.12); color:var(--prep); }
    .status-tag.st-ready { background:rgba(31,158,70,0.12); color:var(--ready); }
    .status-tag.st-done { background:rgba(58,53,64,0.1); color:#3a3540; }
    .row-new { background:rgba(255,59,48,0.05); }
    .row-prep { background:rgba(224,134,0,0.05); }
    .row-ready { background:rgba(31,158,70,0.05); }
    .row-new td:first-child { border-right:4px solid var(--new); }
    .row-prep td:first-child { border-right:4px solid var(--prep); }
    .row-ready td:first-child { border-right:4px solid var(--ready); }
    .row-done { opacity:.55; }
    .row-done td:first-child { border-right:4px solid #3a3540; }
    .act-icons { display:flex; gap:4px; justify-content:flex-end; }
    .mini-icon-btn { width:26px; height:26px; border-radius:8px; border:1px solid var(--border); background:#e7f8ec; cursor:pointer; font-size:12px; display:flex; align-items:center; justify-content:center; padding:0; }
    .mini-icon-btn.next-btn { border:none; color:#fff; }
    .next-btn.ic-new { background:var(--prep); }
    .next-btn.ic-prep { background:var(--ready); }
    .next-btn.ic-ready { background:#3a3540; }
    .empty { text-align:center; padding: 30px 10px; color: var(--muted); font-size: 13px; }
    .co-toggle, .stat-chip, .ticket { color: var(--text); }
  </style>
<script src="?asset=api.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
</head>
<body>
  <div class="header">
    <div class="header-brand">
      <img class="header-logo" src="assets/icons/icon-512.png" alt="شعار <?= e(APP_NAME) ?>">
      <div>
        <h1>👨‍🍳 شاشة المطبخ</h1>
        <div class="user"><?= e($user['name']) ?></div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <div class="clock" id="clock">--:--:--</div>
      <button class="logout" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="stats-bar">
    <div class="stat-chip"><b id="stNew" style="color:var(--new)">0</b><span>جديد</span></div>
    <div class="stat-chip"><b id="stPrep" style="color:var(--prep)">0</b><span>قيد التحضير</span></div>
    <div class="stat-chip"><b id="stReady" style="color:var(--ready)">0</b><span>جاهز</span></div>
    <div class="stat-chip"><b id="stDoneToday" style="color:#5ab0ff">0</b><span>تم تسليمه اليوم</span></div>
  </div>

  <div class="co-section" id="coSection" style="border:1px solid var(--border);border-radius:14px;padding:12px;background:var(--surface)">
    <div class="co-section-head" onclick="toggleCoSection()">
      <h3 id="coTitle">🏢 طلبات الشركات اليوم
        <span class="co-badge" id="coCount">0 شركة</span>
        <span class="co-badge" id="coMealsBadge">0 وجبة</span>
        <span class="co-badge" id="coPkgBadge" style="background:#e8f2ff;color:#1c6fd9"></span>
      </h3>
      <div class="co-toggle" id="coToggleIcon">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
      </div>
    </div>
    <div class="co-body" id="coBody">
      <div class="co-date-nav" onclick="event.stopPropagation()">
        <button onclick="coChangeDay(-1)">◀ يوم سابق</button>
        <input type="date" id="coDateInput" onchange="coDateChanged()">
        <button onclick="coChangeDay(1)">يوم تالي ▶</button>
        <button id="coTodayBtn" onclick="coGoToday()">اليوم</button>
        <button class="toolbar-btn" onclick="coPrintTable()">🖨️ طباعة</button>
        <button class="toolbar-btn wa-btn" onclick="coSendPdf('share')">📤 إرسال PDF</button>
      </div>
      <div class="co-companies" id="coCompanies"><div style="font-size:12px;color:var(--muted)">لا توجد طلبات شركات اليوم</div></div>
    </div>
  </div>

  <div class="toolbar-row">
    <button class="toolbar-btn" onclick="ordersPrintTable()">🖨️ طباعة جدول الطلبات</button>
    <button class="toolbar-btn wa-btn" onclick="ordersSendPdf('share')">📤 إرسال جدول الطلبات PDF</button>
  </div>

  <div class="orders-wrap">
    <table class="orders-tbl">
      <thead>
        <tr><th>الطلب</th><th>العدد</th><th>النوع</th><th>الحالة</th><th>رقم الهاتف</th><th>ملاحظات</th><th>أوامر</th></tr>
      </thead>
      <tbody id="ordersBody">
        <tr><td colspan="7" class="empty">لا توجد طلبات حالية</td></tr>
      </tbody>
    </table>
  </div>

  <script>
    let knownNewIds = new Set();
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
    function todayStr() {
      return new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date());
    }

    function statusTagHtml(status) {
      const map = {
        'جديد': ['st-new', '🔴 جديد'],
        'قيد التحضير': ['st-prep', '🟠 قيد التحضير'],
        'جاهز': ['st-ready', '🟢 جاهز'],
        'تم التسليم': ['st-done', '⚪ تم التسليم']
      };
      const m = map[status] || ['st-new', esc(status || '')];
      return `<span class="status-tag ${m[0]}">${m[1]}</span>`;
    }

    function nextStatusOf(status) {
      if (status === 'جديد') return ['قيد التحضير', '▶️', 'بدء التحضير', 'ic-new'];
      if (status === 'قيد التحضير') return ['جاهز', '✅', 'جاهز للتسليم', 'ic-prep'];
      if (status === 'جاهز') return ['تم التسليم', '📦', 'تسليم الطلب', 'ic-ready'];
      return null;
    }
    window.nextStatusOf = nextStatusOf;

    function orderRowHtml(o) {
      const isDelivery = o.order_type === 'دليفري';
      const isCompany = o.order_type === 'شركات';
      const itemsTxt = (o.items || []).map(i => esc(i.name) + ' <b style="color:var(--primary)">×' + esc(i.qty) + '</b>').join('، ');
      const rowCls = o.status === 'جديد' ? 'row-new' : (o.status === 'قيد التحضير' ? 'row-prep' : (o.status === 'جاهز' ? 'row-ready' : 'row-done'));
      const nx = nextStatusOf(o.status);
      const totalQty = (o.items || []).reduce((sum, i) => sum + (Number(i.qty) || 0), 0);
      return `<tr class="${rowCls}">
        <td class="items-cell">${itemsTxt}</td>
        <td class="qty-cell">${totalQty}</td>
        <td><span class="type-tag ${isCompany ? 'company' : (isDelivery ? 'delivery' : 'dinein')}">${isCompany ? '🏢 شركات' : (isDelivery ? '🛵 دليفري' : '🍽️ صالة')}</span></td>
        <td>${statusTagHtml(o.status)}</td>
        <td class="phone-cell">${o.phone ? esc(o.phone) : '—'}</td>
        <td class="notes-cell">${o.notes ? '📝 ' + esc(o.notes) : '—'}</td>
        <td><div class="act-icons">
          ${nx ? `<button class="mini-icon-btn next-btn ${nx[3]}" title="${esc(nx[2])}" onclick="updateStatus('${esc(o.order_id)}', '${nx[0]}')">${nx[1]}</button>` : `<button class="mini-icon-btn" title="إعادة فتح الطلب" onclick="updateStatus('${esc(o.order_id)}', 'قيد التحضير')">↩️</button>`}
          <button class="mini-icon-btn" title="إرسال الطلب PDF على واتساب" onclick="orderSharePdf('${esc(o.order_id)}')">📤</button>
        </div></td>
      </tr>`;
    }

    function printHtmlDoc(title, bodyHtml, extraBtns) {
      const w = window.open('', '_blank');
      if (!w) { alert('يرجى السماح بالنوافذ المنبثقة للطباعة'); return; }
      w.document.write(`<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>${title}</title>
        <style>
          body{font-family:Tahoma,Arial,sans-serif;padding:16px;color:#221a20}
          h2{font-size:16px;margin-bottom:10px}
          h3{font-size:13px;margin:14px 0 6px;color:#1f9e46}
          table{width:100%;border-collapse:collapse;margin-bottom:10px;font-size:12px}
          th,td{border:1px solid #ccc;padding:6px 8px;text-align:right}
          th{background:#f4f4f4}
          .sum{font-weight:800;margin-top:4px}
          .pbtn{margin-top:14px;padding:10px 16px;border:none;border-radius:8px;font-weight:800;cursor:pointer;font-size:13px}
          .pbtn.pr{background:#ff3b30;color:#fff}
          .pbtn.pdf{background:#1f9e46;color:#fff}
          .btns{display:flex;gap:8px;flex-wrap:wrap}
          @media print { .btns{display:none} }
        </style></head><body>
        <h2>${title}</h2>${bodyHtml}
        <div class="btns">
          <button class="pbtn pr" onclick="window.print()">🖨️ طباعة</button>
          ${extraBtns || ''}
        </div>
        </body></html>`);
      w.document.close();
    }

    function buildCoTableHtml(groups) {
      let html = '';
      groups.forEach(g => {
        html += `<h3 style="font-size:13px;margin:14px 0 6px;color:#1f9e46">🏢 ${esc(g.company)}</h3><table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:12px"><thead><tr><th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">القسم</th><th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">الوجبة</th><th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">العدد</th><th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">ملاحظات</th></tr></thead><tbody>`;
        g.orders.forEach(o => {
          const label = o.package === 'من المنيو' && o.item_name ? o.item_name : o.package;
          html += `<tr><td style="border:1px solid #ccc;padding:6px 8px">${o.department ? esc(o.department) : '—'}</td><td style="border:1px solid #ccc;padding:6px 8px">${esc(label)}</td><td style="border:1px solid #ccc;padding:6px 8px">${o.meals}</td><td style="border:1px solid #ccc;padding:6px 8px">${o.notes ? esc(o.notes) : '—'}</td></tr>`;
        });
        html += `</tbody></table><div style="font-weight:800;margin-top:4px">إجمالي عدد الوجبات: ${g.totalMeals} وجبة</div>`;
      });
      return html;
    }

    function coPrintTable() {
      const groups = window.__lastCoGroups || [];
      if (!groups.length) { alert('لا توجد بيانات لهذا اليوم'); return; }
      const btns = '<button class="pbtn pdf" onclick="window.opener.coSendPdf(\'share\')">📤 إرسال PDF واتساب</button>'
        + '<button class="pbtn pdf" onclick="window.opener.coSendPdf(\'download\')">⬇️ تنزيل PDF</button>';
      printHtmlDoc('طلبات الشركات - ' + (window.__lastCoDate || ''), buildCoTableHtml(groups), btns);
    }
    window.coPrintTable = coPrintTable;

    async function coSendPdf(mode) {
      const groups = window.__lastCoGroups || [];
      if (!groups.length) { alert('لا توجد بيانات لهذا اليوم'); return; }
      const title = '🏢 طلبات الشركات - ' + (window.__lastCoDate || '');
      const blob = await tableToPdfBlob(title, buildCoTableHtml(groups));
      await sendPdfBlob(blob, 'company-orders-' + (window.__lastCoDate || '') + '.pdf', mode === 'download');
    }
    window.coSendPdf = coSendPdf;

    function buildOrdersTableHtml(active) {
      let html = '<table style="width:100%;border-collapse:collapse;margin-bottom:10px;font-size:12px"><thead><tr>'
        + '<th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">رقم الطلب</th>'
        + '<th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">الوقت</th>'
        + '<th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">النوع</th>'
        + '<th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">الحالة</th>'
        + '<th style="border:1px solid #ccc;padding:6px 8px;background:#f4f4f4;text-align:right">الأصناف</th></tr></thead><tbody>';
      active.forEach(o => {
        const itemsTxt = (o.items || []).map(i => i.name + ' ×' + i.qty).join('، ');
        html += `<tr><td style="border:1px solid #ccc;padding:6px 8px">${esc(o.order_id.replace('ORD-',''))}</td><td style="border:1px solid #ccc;padding:6px 8px">${timeOnly(o.created_at)}</td><td style="border:1px solid #ccc;padding:6px 8px">${esc(o.order_type || '')}</td><td style="border:1px solid #ccc;padding:6px 8px">${esc(o.status)}</td><td style="border:1px solid #ccc;padding:6px 8px">${esc(itemsTxt)}</td></tr>`;
      });
      html += '</tbody></table>';
      return html;
    }

    function todayShownOrders() {
      const today = todayStr();
      return (window.__lastOrders || []).filter(o => (o.created_at || '').slice(0, 10) === today);
    }

    function ordersPrintTable() {
      const shown = todayShownOrders();
      if (!shown.length) { alert('لا توجد طلبات اليوم'); return; }
      const btns = '<button class="pbtn pdf" onclick="window.opener.ordersSendPdf(\'share\')">📤 إرسال PDF واتساب</button>'
        + '<button class="pbtn pdf" onclick="window.opener.ordersSendPdf(\'download\')">⬇️ تنزيل PDF</button>';
      printHtmlDoc('📋 جدول طلبات اليوم ' + todayStr(), buildOrdersTableHtml(shown), btns);
    }
    window.ordersPrintTable = ordersPrintTable;

    async function ordersSendPdf(mode) {
      const shown = todayShownOrders();
      if (!shown.length) { alert('لا توجد طلبات اليوم'); return; }
      const blob = await tableToPdfBlob('📋 جدول طلبات اليوم ' + todayStr(), buildOrdersTableHtml(shown));
      await sendPdfBlob(blob, 'orders-table-' + todayStr() + '.pdf', mode === 'download');
    }
    window.ordersSendPdf = ordersSendPdf;

    // ===== تحويل جدول HTML إلى ملف PDF (لإرساله على واتساب) =====
    async function htmlToCanvas(title, bodyHtml) {
      if (typeof html2canvas === 'undefined') { alert('تعذر تحميل مكتبة الرسم، تأكد من الاتصال بالإنترنت'); return null; }
      const box = document.createElement('div');
      box.style.cssText = 'position:fixed;left:-9999px;top:0;width:420px;background:#fff;padding:16px;font-family:Tajawal,Tahoma,Arial,sans-serif;direction:rtl;color:#221a20;z-index:-1';
      box.innerHTML = '<h2 style="font-size:15px;margin-bottom:10px">' + title + '</h2>' + bodyHtml;
      document.body.appendChild(box);
      try {
        await new Promise(r => setTimeout(r, 60));
        return await html2canvas(box, { scale: 2, backgroundColor: '#ffffff' });
      } catch (e) {
        alert('حدث خطأ أثناء إنشاء الملف');
        return null;
      } finally {
        box.remove();
      }
    }

    async function tableToPdfBlob(title, bodyHtml) {
      if (typeof window.jspdf === 'undefined') { alert('تعذر تحميل مكتبة PDF، تأكد من الاتصال بالإنترنت'); return null; }
      const canvas = await htmlToCanvas(title, bodyHtml);
      if (!canvas) return null;
      try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF({ orientation: canvas.width > canvas.height ? 'landscape' : 'portrait', unit: 'pt', format: 'a4' });
        const pw = pdf.internal.pageSize.getWidth();
        const ph = pdf.internal.pageSize.getHeight();
        const r = Math.min(pw / canvas.width, ph / canvas.height);
        const w = canvas.width * r, h = canvas.height * r;
        pdf.addImage(canvas.toDataURL('image/png'), 'PNG', (pw - w) / 2, (ph - h) / 2, w, h);
        return pdf.output('blob');
      } catch (e) {
        alert('حدث خطأ أثناء إنشاء ملف PDF');
        return null;
      }
    }

    async function sendPdfBlob(blob, filename, forceDownload) {
      if (!blob) return;
      if (!forceDownload) {
        const file = new File([blob], filename, { type: 'application/pdf' });
        if (navigator.canShare && navigator.canShare({ files: [file] })) {
          try { await navigator.share({ files: [file], title: filename }); return; } catch (e) { /* fall through */ }
        }
      }
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = filename; document.body.appendChild(a); a.click(); a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 5000);
      alert('تم تنزيل ملف PDF على جهازك. افتح واتساب وأرفقه لإرساله.');
    }

    async function orderSharePdf(orderId) {
      const o = (window.__lastOrders || []).find(x => x.order_id === orderId);
      if (!o) { alert('لم يتم العثور على الطلب'); return; }
      const isDelivery = o.order_type === 'دليفري';
      const isCompany = o.order_type === 'شركات';
      const typeTxt = isCompany ? '🏢 شركات' : (isDelivery ? '🛵 دليفري' : '🍽️ صالة');
      const B = 'border:1px solid #ccc;padding:6px 8px';
      let html = '<table style="width:100%;border-collapse:collapse;font-size:12px">'
        + `<tr><td style="${B};font-weight:800;width:110px">رقم الطلب</td><td style="${B}">${esc(o.order_id.replace('ORD-',''))}</td></tr>`
        + `<tr><td style="${B};font-weight:800">الوقت</td><td style="${B}">${timeOnly(o.created_at)}</td></tr>`
        + `<tr><td style="${B};font-weight:800">النوع</td><td style="${B}">${typeTxt}</td></tr>`
        + `<tr><td style="${B};font-weight:800">الحالة</td><td style="${B}">${esc(o.status)}</td></tr>`;
      (o.items || []).forEach(i => {
        html += `<tr><td style="${B}">${esc(i.name)}</td><td style="${B};font-weight:800">× ${esc(i.qty)}</td></tr>`;
      });
      html += `<tr><td style="${B};font-weight:800">الإجمالي</td><td style="${B};font-weight:800">${esc(o.total)} ج.م</td></tr>`;
      if (o.notes) html += `<tr><td style="${B};font-weight:800">ملاحظات</td><td style="${B}">${esc(o.notes)}</td></tr>`;
      html += '</table>';
      const blob = await tableToPdfBlob('🧾 تفاصيل الطلب ' + o.order_id.replace('ORD-',''), html);
      await sendPdfBlob(blob, 'order-' + o.order_id.replace('ORD-','') + '.pdf');
    }
    window.orderSharePdf = orderSharePdf;

    async function loadOrders() {
      try {
        const res = await api('get_orders', { status: '' });
        if (!res.success) {
          if (res.message === 'غير مصرح') { window.location.href = '?page=login'; return; }
          return;
        }
        const orders = res.orders || [];
        window.__lastOrders = orders;
        const news = orders.filter(o => o.status === 'جديد');
        const preps = orders.filter(o => o.status === 'قيد التحضير');
        const readys = orders.filter(o => o.status === 'جاهز');
        const today = todayStr();
        const doneToday = orders.filter(o => o.status === 'تم التسليم' && (o.created_at || '').slice(0, 10) === today);

        // تنبيه صوتي عند وصول طلب جديد
        const ids = new Set(news.map(o => o.order_id));
        if (knownNewIds.size > 0) {
          let hasNew = false;
          ids.forEach(id => { if (!knownNewIds.has(id)) hasNew = true; });
          if (hasNew && window.__sirenAlert) __sirenAlert('🚨 طلب جديد!');
        }
        knownNewIds = ids;

        document.getElementById('stNew').textContent = news.length;
        document.getElementById('stPrep').textContent = preps.length;
        document.getElementById('stReady').textContent = readys.length;
        document.getElementById('stDoneToday').textContent = doneToday.length;

        const shown = orders.filter(o => (o.created_at || '').slice(0, 10) === today);
        document.getElementById('ordersBody').innerHTML = shown.length
          ? shown.map(o => orderRowHtml(o)).join('')
          : '<tr><td colspan="7" class="empty">لا توجد طلبات اليوم</td></tr>';
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

    let knownCoIds = new Set();
    let coFirstLoad = true;
    let coExpanded = false;
    function coTodayStr() { return new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(new Date()); }
    let coSelectedDate = coTodayStr();

    function toggleCoSection() {
      coExpanded = !coExpanded;
      document.getElementById('coSection').classList.toggle('expanded', coExpanded);
    }
    window.toggleCoSection = toggleCoSection;

    function coUpdateTitle() {
      const isToday = coSelectedDate === coTodayStr();
      document.getElementById('coTitle').childNodes[0].textContent = isToday ? '🏢 طلبات الشركات اليوم ' : ('🏢 طلبات الشركات — ' + coSelectedDate + ' ');
      document.getElementById('coTodayBtn').style.display = isToday ? 'none' : 'inline-block';
    }

    function coChangeDay(delta) {
      const d = new Date(coSelectedDate + 'T12:00:00');
      d.setDate(d.getDate() + delta);
      coSelectedDate = new Intl.DateTimeFormat('en-CA', { timeZone: 'Africa/Cairo' }).format(d);
      document.getElementById('coDateInput').value = coSelectedDate;
      coUpdateTitle();
      loadCompanyOrders();
    }
    window.coChangeDay = coChangeDay;

    function coDateChanged() {
      const v = document.getElementById('coDateInput').value;
      if (v) coSelectedDate = v;
      coUpdateTitle();
      loadCompanyOrders();
    }
    window.coDateChanged = coDateChanged;

    function coGoToday() {
      coSelectedDate = coTodayStr();
      document.getElementById('coDateInput').value = coSelectedDate;
      coUpdateTitle();
      loadCompanyOrders();
    }
    window.coGoToday = coGoToday;

    async function loadCompanyOrders() {
      try {
        const res = await api('company_today', { date: coSelectedDate });
        const box = document.getElementById('coCompanies');
        if (!res.success) return;
        const active = res.groups
          .map(g => ({ ...g, orders: g.orders.filter(o => o.status !== 'ملغي') }))
          .filter(g => g.orders.length);
        window.__lastCoGroups = active;
        window.__lastCoDate = res.date || coSelectedDate;
        // إنذار عند وصول طلب شركات جديد (فقط عند عرض اليوم الحالي)
        if (coSelectedDate === coTodayStr()) {
          const ids = new Set();
          (res.groups || []).forEach(g => g.orders.forEach(o => ids.add(o.id)));
          if (!coFirstLoad && knownCoIds.size > 0) {
            let hasNewCo = false;
            ids.forEach(id => { if (!knownCoIds.has(id)) hasNewCo = true; });
            if (hasNewCo && window.__sirenAlert) __sirenAlert('🚨 طلب شركات جديد!');
          }
          knownCoIds = ids;
        }
        coFirstLoad = false;
        const totalMealsAll = active.reduce((s, g) => s + g.totalMeals, 0);
        document.getElementById('coCount').textContent = active.length + ' شركة';
        document.getElementById('coMealsBadge').textContent = totalMealsAll + ' وجبة';
        const pkgTotals = {};
        active.forEach(g => g.orders.forEach(o => {
          const label = o.package === 'من المنيو' && o.item_name ? o.item_name : o.package;
          pkgTotals[label] = (pkgTotals[label] || 0) + (Number(o.meals) || 0);
        }));
        document.getElementById('coPkgBadge').textContent = Object.keys(pkgTotals).map(k => k + ': ' + pkgTotals[k]).join(' · ') || '';
        if (!active.length) {
          box.innerHTML = '<div style="font-size:12px;color:var(--muted)">لا توجد طلبات شركات في هذا اليوم</div>';
          return;
        }
        box.innerHTML = active.map(g => `
          <div class="co-company-card">
            <div class="co-cname">🏢 ${esc(g.company)}</div>
            <table class="co-table">
              <thead><tr><th>القسم</th><th>الوجبة</th><th>العدد</th><th>ملاحظات</th></tr></thead>
              <tbody>
                ${g.orders.map(o => {
                  const label = o.package === 'من المنيو' && o.item_name ? o.item_name : o.package;
                  return `<tr>
                    <td class="co-dept-cell">${o.department ? esc(o.department) : '—'}</td>
                    <td class="co-meal-cell">${esc(label)}</td>
                    <td class="co-count-cell">${o.meals}</td>
                    <td class="co-notes-cell">${o.notes ? esc(o.notes) : '—'}</td>
                  </tr>`;
                }).join('')}
              </tbody>
            </table>
            <div class="co-sum"><span>إجمالي عدد الوجبات</span><span>${g.totalMeals} وجبة</span></div>
          </div>
        `).join('');
      } catch (e) {}
    }

    document.getElementById('coDateInput').value = coSelectedDate;
    coUpdateTitle();
    loadOrders();
    setInterval(loadOrders, 8000);
    loadCompanyOrders();
    setInterval(function () { if (coSelectedDate === coTodayStr()) loadCompanyOrders(); }, 8000);
  </script>
  <?php include __DIR__ . '/_alerts.php'; ?>
</body>
</html>
