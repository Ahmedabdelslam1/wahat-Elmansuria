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

  <div class="items" id="cartItems"></div>

  <div class="form-section" id="orderForm" style="display:none">
    <input type="text" id="customerName" placeholder="الاسم">
    <input type="tel" id="phone" placeholder="رقم الهاتف">
    <textarea id="notes" rows="2" placeholder="ملاحظات على الطلب (اختياري)"></textarea>
  </div>

  <div class="summary" id="summary" style="display:none">
    <div class="summary-row"><span>المجموع الفرعي</span><span id="subtotal">0</span></div>
    <div class="summary-row total"><span>الإجمالي</span><span id="total">0 ج.م</span></div>
    <button class="order-btn" id="orderBtn" onclick="placeOrder()">تأكيد الطلب الآن</button>
  </div>

  <div class="empty" id="emptyMsg">السلة فارغة<br><br><a href="?page=menu">تصفح المنيو</a></div>

  <script>
    let cart = JSON.parse(localStorage.getItem('wahat_cart') || '[]');

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
      document.getElementById('subtotal').textContent = sub + ' ج.م';
      document.getElementById('total').textContent = sub + ' ج.م';
    }

    function changeQty(idx, delta) {
      cart[idx].qty += delta;
      if (cart[idx].qty <= 0) cart.splice(idx, 1);
      localStorage.setItem('wahat_cart', JSON.stringify(cart));
      render();
    }

    async function placeOrder() {
      if (cart.length === 0) return;
      const name = document.getElementById('customerName').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const notes = document.getElementById('notes').value.trim();

      if (!name || !phone) {
        alert('يرجى إدخال الاسم ورقم الهاتف');
        return;
      }

      const btn = document.getElementById('orderBtn');
      btn.disabled = true;

      try {
        const res = await api('place_order', {
          items: cart,
          customerName: name,
          phone: phone,
          notes: notes
        });
        btn.disabled = false;
        if (res.success) {
          localStorage.removeItem('wahat_cart');
          cart = [];
          alert(res.message);
          window.location.href = '?page=menu';
        } else {
          alert(res.message || 'حدث خطأ');
        }
      } catch (e) {
        btn.disabled = false;
        alert('خطأ: ' + (e.message || 'فشل الاتصال بالسيرفر'));
      }
    }

    render();
  </script>
</body>
</html>
