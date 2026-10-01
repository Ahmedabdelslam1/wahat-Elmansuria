<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <meta name="theme-color" content="#15151f">
  <link rel="manifest" href="manifest.json">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>السلة - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2; --radius:16px;
    }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: var(--bg);
      color: var(--text);
      padding-bottom: 220px;
    }
    .topbar {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      padding: 14px 16px;
      display: flex; align-items: center; justify-content: space-between; gap: 10px;
      position: sticky; top: 0; z-index: 50;
      box-shadow: 0 4px 18px rgba(0,0,0,0.25);
    }
    .icon-btn {
      width: 40px; height: 40px; border-radius: 50%;
      background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14);
      display: flex; align-items: center; justify-content: center;
      color: #fff; text-decoration: none; flex-shrink: 0;
    }
    .icon-btn svg { width: 18px; height: 18px; }
    .topbar h1 { color: #fff; font-size: 16px; font-weight: 800; flex: 1; text-align: center; }

    .items { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .item {
      background: var(--surface);
      border-radius: var(--radius);
      padding: 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: 1px solid var(--border);
      box-shadow: 0 2px 10px rgba(20,20,30,0.04);
    }
    .item h3 { font-size: 14px; margin-bottom: 4px; font-weight: 800; }
    .item .price { color: var(--muted); font-size: 12.5px; }
    .qty { display: flex; align-items: center; gap: 10px; }
    .qty button {
      width: 30px; height: 30px;
      border-radius: 50%;
      border: none;
      background: var(--bg);
      color: var(--text);
      font-size: 16px;
      font-weight: 800;
      cursor: pointer;
    }
    .qty button.plus { background: var(--primary); color: #fff; }
    .summary {
      position: fixed;
      bottom: 0; left: 0; right: 0;
      background: var(--surface);
      padding: 16px 18px calc(16px + env(safe-area-inset-bottom));
      border-top: 1px solid var(--border);
      border-radius: 20px 20px 0 0;
      box-shadow: 0 -8px 30px rgba(0,0,0,0.08);
    }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 13.5px; color: var(--muted); }
    .summary-row.total { font-size: 18px; font-weight: 800; color: var(--text); margin-top: 8px; }
    .order-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(90deg, var(--primary), var(--primary-dark));
      color: #fff;
      border: none;
      border-radius: 14px;
      font-family: inherit;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 12px;
      box-shadow: 0 10px 24px -6px rgba(255,59,48,0.5);
    }
    .order-btn:disabled { opacity: 0.6; cursor: wait; }
    .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
    .empty a { color: var(--primary); font-weight: 800; text-decoration: none; }
    .form-section { padding: 0 16px 16px; }
    .form-section .type-row { display:flex; gap:10px; margin-bottom:10px; }
    .form-section .type-row button {
      flex:1; padding:12px; border-radius:12px; border:1px solid var(--border);
      background: var(--surface); font-family:inherit; font-weight:800; font-size:13.5px; cursor:pointer; color:var(--text);
    }
    .form-section .type-row button.active-dinein { background:#e5f0ff; border-color:#007aff; color:#007aff; }
    .form-section .type-row button.active-delivery { background:#fff3e0; border-color:#ff9500; color:#ef6c00; }
    .form-section input, .form-section textarea {
      width: 100%;
      padding: 12px 14px;
      margin-bottom: 10px;
      border: 1px solid var(--border);
      border-radius: 12px;
      font-family: inherit;
      font-size: 14px;
      background: var(--surface);
    }
    /* ===== متابعة الطلبات ===== */
    .track-card { background: var(--surface); border-radius: 16px; padding: 14px; margin: 14px 16px 4px; border: 1px solid var(--border); box-shadow: 0 2px 10px rgba(20,20,30,0.04); }
    .track-title { font-size: 14px; font-weight: 800; display: flex; align-items: center; gap: 6px; margin-bottom: 10px; }
    .track-filters { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .tfilter { border: 1px solid var(--border); background: var(--surface); border-radius: 20px; padding: 6px 12px; font-size: 11.5px; font-weight: 800; cursor: pointer; font-family: inherit; color: var(--text); }
    .tfilter.active { background: var(--primary); color: #fff; border-color: var(--primary); }
    .track-filters input[type="date"] { border: 1px solid var(--border); border-radius: 20px; padding: 5px 10px; font-family: inherit; font-size: 11.5px; }
    .track-list { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; }
    .torder { border: 1px solid var(--border); border-radius: 14px; padding: 10px 12px; background: var(--bg); }
    .torder-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
    .torder-id { font-weight: 800; font-size: 12.5px; direction: ltr; }
    .torder-date { color: var(--muted); font-size: 11px; }
    .tstatus { font-size: 10.5px; font-weight: 800; padding: 3px 10px; border-radius: 16px; }
    .tstatus.new { background: #ffebe9; color: #ff3b30; }
    .tstatus.preparing { background: #fff3e0; color: #ef6c00; }
    .tstatus.ready { background: #e7f8ec; color: #2e7d32; }
    .tstatus.done { background: #e5f0ff; color: #007aff; }
    .tstatus.cancelled { background: #f0f0f5; color: #8b8b9a; }
    .tstatus.unknown { background: #f0f0f5; color: #8b8b9a; }
    .torder-items { font-size: 12px; color: var(--muted); margin-top: 6px; line-height: 1.6; }
    .torder-total { font-weight: 800; font-size: 13px; color: var(--primary); margin-top: 4px; }
    .track-search { display: flex; gap: 8px; margin-top: 10px; }
    .track-search input { flex: 1; padding: 10px 12px; border: 1px solid var(--border); border-radius: 12px; font-family: inherit; font-size: 12.5px; }
    .track-search button { border: none; background: linear-gradient(90deg, var(--primary), var(--primary-dark)); color: #fff; border-radius: 12px; padding: 10px 16px; font-family: inherit; font-weight: 800; font-size: 12.5px; cursor: pointer; }
    .track-empty { color: var(--muted); font-size: 12.5px; text-align: center; padding: 14px; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="topbar">
    <a class="icon-btn" href="?page=menu" title="رجوع للمنيو">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
    </a>
    <h1>🛒 السلة</h1>
    <a class="icon-btn" href="?page=login" title="دخول المستخدمين والأدمن">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
    </a>
  </div>

  <div class="track-card" id="trackCard">
    <div class="track-title">🚚 متابعة طلباتي</div>
    <div class="track-filters">
      <button class="tfilter active" onclick="setTrackFilter('all', this)">الكل</button>
      <button class="tfilter" onclick="setTrackFilter('day', this)">اليوم</button>
      <button class="tfilter" onclick="setTrackFilter('month', this)">هذا الشهر</button>
      <button class="tfilter" onclick="setTrackFilter('year', this)">هذه السنة</button>
      <input type="date" id="trackDate" onchange="setTrackFilter('date', document.getElementById('tfDateBtn'))" title="فلترة بتاريخ محدد">
      <button class="tfilter" id="tfDateBtn" style="display:none" onclick="setTrackFilter('date', this)">التاريخ المحدد</button>
    </div>
    <div class="track-search">
      <input type="text" id="trackOrderId" placeholder="🔍 تتبع طلب برقمه (من أي جهاز)">
      <button onclick="trackByOrderId()">تتبع</button>
    </div>
    <div class="track-list" id="trackList"></div>
  </div>

  <div class="items" id="cartItems"></div>

  <div class="form-section" id="orderForm" style="display:none">
    <input type="text" id="customerName" placeholder="الاسم">
    <input type="tel" id="phone" placeholder="رقم الهاتف">
    <input type="text" id="address" placeholder="📍 عنوان التوصيل">
    <textarea id="notes" rows="2" placeholder="ملاحظات على الطلب (اختياري)"></textarea>
  </div>

  <div class="summary" id="summary" style="display:none">
    <div class="summary-row"><span>المجموع الفرعي</span><span id="subtotal">0</span></div>
    <div class="summary-row" id="deliveryFeeRow" style="display:none"><span>🛵 التوصيل</span><span id="deliveryFeeVal">0 ج.م</span></div>
    <div class="summary-row total"><span>الإجمالي</span><span id="total">0 ج.م</span></div>
    <button class="order-btn" id="orderBtn" onclick="placeOrder()">تأكيد الطلب الآن</button>
  </div>

  <div class="empty" id="emptyMsg">السلة فارغة<br><br><a href="?page=menu">تصفح المنيو</a></div>

  <script>
    let cart = JSON.parse(localStorage.getItem('wahat_cart') || '[]');
    let cartType = 'دليفري';
    let cartDeliveryFee = 0;

    api('public_settings').then(res => {
      if (res.success && res.settings) {
        cartDeliveryFee = Number(res.settings.deliveryFee) || 0;
        render();
      }
    }).catch(() => {});

    // الطلب أونلاين = دليفري دائمًا (تم إلغاء خيار الصالة)
    function setCartType(t) { cartType = 'دليفري'; render(); }

    function render() {
      const container = document.getElementById('cartItems');
      const empty = document.getElementById('emptyMsg');
      const summary = document.getElementById('summary');
      const form = document.getElementById('orderForm');

      if (cart.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        summary.style.display = 'none';
        form.style.display = 'none';
        return;
      }
      empty.style.display = 'none';
      summary.style.display = 'block';
      form.style.display = 'block';

      container.innerHTML = '';
      cart.forEach((item, idx) => {
        const div = document.createElement('div');
        div.className = 'item';

        const info = document.createElement('div');
        const h3 = document.createElement('h3');
        h3.textContent = item.name;
        const price = document.createElement('div');
        price.className = 'price';
        price.textContent = item.price + ' × ' + item.qty + ' = ' + (item.price * item.qty) + ' ج.م';
        info.append(h3, price);

        const qty = document.createElement('div');
        qty.className = 'qty';
        const minus = document.createElement('button');
        minus.textContent = '−';
        minus.onclick = () => changeQty(idx, -1);
        const count = document.createElement('span');
        count.style.fontWeight = '800';
        count.textContent = item.qty;
        const plus = document.createElement('button');
        plus.className = 'plus';
        plus.textContent = '+';
        plus.onclick = () => changeQty(idx, 1);
        qty.append(minus, count, plus);

        div.append(info, qty);
        container.appendChild(div);
      });

      const sub = cart.reduce((s, i) => s + i.price * i.qty, 0);
      const fee = cartType === 'دليفري' ? cartDeliveryFee : 0;
      document.getElementById('subtotal').textContent = sub + ' ج.م';
      document.getElementById('deliveryFeeRow').style.display = fee > 0 ? 'flex' : 'none';
      document.getElementById('deliveryFeeVal').textContent = fee + ' ج.م';
      document.getElementById('total').textContent = (sub + fee) + ' ج.م';
    }

    function changeQty(idx, delta) {
      cart[idx].qty += delta;
      if (cart[idx].qty <= 0) cart.splice(idx, 1);
      localStorage.setItem('wahat_cart', JSON.stringify(cart));
      render();
    }

    function esc(s) {
      return String(s ?? '').replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }

    // تنبيه هادئ داخل الصفحة (بلا صوت نظام أو نافذة alert المزعجة)
    function showToast(msg, isError) {
      var old = document.getElementById('cartToast');
      if (old) old.remove();
      var t = document.createElement('div');
      t.id = 'cartToast';
      t.textContent = msg;
      t.style.cssText = 'position:fixed;left:16px;right:16px;bottom:18px;z-index:99999;background:'
        + (isError ? '#ff3b30' : '#1f1f2c') + ';color:#fff;padding:13px 16px;border-radius:14px;font-size:13.5px;'
        + 'font-weight:700;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,.3);font-family:inherit';
      document.body.appendChild(t);
      setTimeout(function () { t.remove(); }, 3500);
    }

    async function placeOrder() {
      if (cart.length === 0) return;
      const name = document.getElementById('customerName').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const notes = document.getElementById('notes').value.trim();
      const address = document.getElementById('address').value.trim();

      if (!name || !phone) {
        showToast('يرجى إدخال الاسم ورقم الهاتف', true);
        return;
      }
      if (cartType === 'دليفري' && !address) {
        showToast('يرجى إدخال عنوان التوصيل', true);
        return;
      }

      const btn = document.getElementById('orderBtn');
      btn.disabled = true;

      try {
        const res = await api('place_order', {
          items: cart,
          customerName: name,
          phone: phone,
          notes: notes,
          address: address,
          orderType: cartType
        });
        btn.disabled = false;
        if (res.success) {
          // الاحتفاظ بالطلب في السلة + تسجيله في قائمة طلباتى للمتابعة
          const myOrders = JSON.parse(localStorage.getItem('wahat_my_orders') || '[]');
          myOrders.unshift({
            orderId: res.orderId || '',
            createdAt: new Date().toISOString(),
            items: cart.map(i => ({ name: i.name, qty: i.qty, price: i.price })),
            total: res.total,
            status: 'جديد'
          });
          localStorage.setItem('wahat_my_orders', JSON.stringify(myOrders));
          renderMyOrders();
          showOrderDone(res);
        } else {
          showToast(res.message || 'حدث خطأ', true);
        }
      } catch (e) {
        btn.disabled = false;
        showToast('خطأ: ' + (e.message || 'فشل الاتصال بالسيرفر'), true);
      }
    }

    render();

    // ===== متابعة الطلبات (فلتر تاريخ / شهر / سنة + بحث برقم الطلب) =====
    let trackFilter = 'all';
    const STATUS_CLASS = { 'جديد': 'new', 'قيد التحضير': 'preparing', 'جاهز': 'ready', 'تم التسليم': 'done', 'ملغي': 'cancelled' };
    const FINAL_STATUSES = ['تم التسليم', 'ملغي'];

    function getMyOrders() { return JSON.parse(localStorage.getItem('wahat_my_orders') || '[]'); }

    function setTrackFilter(f, btn) {
      trackFilter = f;
      document.querySelectorAll('.tfilter').forEach(b => b.classList.remove('active'));
      if (btn) btn.classList.add('active');
      document.getElementById('tfDateBtn').style.display = f === 'date' ? 'inline-block' : 'none';
      renderMyOrders();
    }

    function statusClassOf(st) { return STATUS_CLASS[st] || 'unknown'; }

    function renderMyOrders() {
      const list = document.getElementById('trackList');
      let orders = getMyOrders();
      const now = new Date();
      const today = now.toISOString().slice(0, 10);
      const month = today.slice(0, 7);
      const year = today.slice(0, 4);
      const picked = document.getElementById('trackDate').value;
      if (trackFilter === 'day') orders = orders.filter(o => (o.createdAt || '').slice(0, 10) === today);
      if (trackFilter === 'month') orders = orders.filter(o => (o.createdAt || '').slice(0, 7) === month);
      if (trackFilter === 'year') orders = orders.filter(o => (o.createdAt || '').slice(0, 4) === year);
      if (trackFilter === 'date' && picked) orders = orders.filter(o => (o.createdAt || '').slice(0, 10) === picked);
      orders = orders.slice(0, 50);
      if (!orders.length) {
        list.innerHTML = '<div class="track-empty">' + (trackFilter === 'all' ? 'لا توجد طلبات محفوظة على هذا الجهاز — اطلب وسيظهر هنا مع حالته' : 'لا توجد طلبات في هذا النطاق') + '</div>';
        return;
      }
      list.innerHTML = orders.map((o, idx) =>
        '<div class="torder">'
        + '<div class="torder-head">'
        + '<span class="torder-id">' + esc(o.orderId) + '</span>'
        + '<span class="tstatus ' + statusClassOf(o.status) + '" id="tst-' + idx + '">' + esc(o.status) + '</span>'
        + '</div>'
        + '<div class="torder-date">' + esc((o.createdAt || '').replace('T', ' ').slice(0, 16)) + '</div>'
        + '<div class="torder-items">' + (o.items || []).map(i => esc(i.name) + ' × ' + i.qty).join(' · ') + '</div>'
        + '<div class="torder-total">' + esc(o.total ?? '') + ' ج.م</div>'
        + '</div>'
      ).join('');
    }

    async function refreshActiveOrders() {
      const orders = getMyOrders();
      const activeIdx = orders.map((o, i) => FINAL_STATUSES.includes(o.status) ? -1 : i).filter(i => i >= 0);
      for (const idx of activeIdx.slice(0, 10)) {
        try {
          const res = await api('order_status', { orderId: orders[idx].orderId });
          if (res.success && res.order && res.order.status !== orders[idx].status) {
            orders[idx].status = res.order.status;
            localStorage.setItem('wahat_my_orders', JSON.stringify(orders));
          }
        } catch (e) {}
      }
      renderMyOrders();
    }

    async function trackByOrderId() {
      const oid = document.getElementById('trackOrderId').value.trim();
      if (!oid) { showToast('أدخل رقم الطلب', true); return; }
      try {
        const res = await api('order_status', { orderId: oid });
        if (!res.success) { showToast(res.message || 'الطلب غير موجود', true); return; }
        const o = res.order;
        const orders = getMyOrders();
        const exist = orders.findIndex(x => x.orderId === o.orderId);
        const entry = {
          orderId: o.orderId,
          createdAt: (o.createdAt || '').replace(' ', 'T'),
          items: o.items || [],
          total: o.total,
          status: o.status
        };
        if (exist >= 0) orders[exist] = entry; else orders.unshift(entry);
        localStorage.setItem('wahat_my_orders', JSON.stringify(orders));
        setTrackFilter('all', document.querySelector('.tfilter'));
        showToast('تم العثور على الطلب — الحالة: ' + o.status, false);
      } catch (e) { showToast('خطأ في الاتصال', true); }
    }

    document.getElementById('trackDate').addEventListener('input', function () {
      if (this.value) setTrackFilter('date', document.getElementById('tfDateBtn'));
    });
    renderMyOrders();
    setInterval(refreshActiveOrders, 10000);

    // إشعار نجاح الطلب برقم الطلب + إرسال على واتس المطعم بنقرة واحدة (بدون توكن)
    function showOrderDone(res) {
      var d = document.createElement('div');
      d.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99998;display:flex;align-items:center;justify-content:center;padding:16px';
      d.innerHTML = '<div style="background:#fff;border-radius:16px;padding:22px;max-width:420px;width:100%;text-align:center;font-family:inherit;box-shadow:0 18px 50px rgba(0,0,0,.35)">'
        + '<div style="font-size:36px">✅</div>'
        + '<div style="font-weight:800;font-size:15px;margin:8px 0 4px">' + esc(res.message || 'تم استلام طلبك بنجاح!') + '</div>'
        + '<div style="background:#f4f5fa;border-radius:12px;padding:10px;margin-bottom:10px">'
        + '<div style="color:#8b8b9a;font-size:11px;font-weight:700">رقم الطلب</div>'
        + '<div style="font-size:19px;font-weight:800;letter-spacing:.3px">' + esc(res.orderId || '') + '</div>'
        + '</div>'
        + '<div style="color:#8b8b9a;font-size:12.5px;margin-bottom:14px">الإجمالي ' + esc(res.total ?? '') + ' ج.م — سنتواصل معك قريبًا 🛵</div>'
        + (res.wa_restaurant ? '<a href="' + res.wa_restaurant + '" target="_blank" rel="noopener" style="text-decoration:none;display:block;background:#128C7E;color:#fff;border-radius:12px;padding:12px 18px;font-size:14px;font-weight:800;margin-bottom:8px">📤 إرسال الطلب على واتس المطعم (نقرة واحدة)</a>' : '')
        + '<button onclick="window.location.href=\'?page=menu\'" style="background:#eee;color:#555;border:none;border-radius:12px;padding:10px 18px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit;width:100%">متابعة الطلب والمينيو</button>'
        + '</div>';
      document.body.appendChild(d);
    }
  </script>
</body>
</html>
