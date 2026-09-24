<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <title>السلة - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #fff8e1;
      color: #212121;
      padding-bottom: 140px;
    }
    .header {
      background: linear-gradient(160deg, #1a0f08, #3e2723);
      color: #d4af37;
      padding: 18px 20px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 50;
    }
    .header h1 { font-size: 20px; }
    .items { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .item {
      background: #fff;
      border-radius: 14px;
      padding: 14px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .item h3 { font-size: 14px; margin-bottom: 4px; }
    .item .price { color: #e65100; font-weight: bold; }
    .qty { display: flex; align-items: center; gap: 10px; }
    .qty button {
      width: 30px; height: 30px;
      border-radius: 50%;
      border: 1px solid #d4af37;
      background: #fff;
      color: #3e2723;
      font-size: 16px;
      cursor: pointer;
    }
    .summary {
      position: fixed;
      bottom: 60px;
      left: 0; right: 0;
      background: #fff;
      padding: 16px 20px;
      border-top: 2px solid #d4af37;
      box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
    }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
    .summary-row.total { font-size: 18px; font-weight: bold; color: #e65100; margin-top: 8px; }
    .order-btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(90deg, #d4af37, #f5c542);
      color: #1a0f08;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 12px;
    }
    .order-btn:disabled { opacity: 0.6; cursor: wait; }
    .empty { text-align: center; padding: 60px 20px; color: #8d6e63; }
    .form-section { padding: 0 16px 16px; }
    .form-section input, .form-section textarea {
      width: 100%;
      padding: 12px;
      margin-bottom: 10px;
      border: 1px solid #e0d5c0;
      border-radius: 10px;
      font-size: 14px;
      background: #fff;
    }
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0; right: 0;
      background: #1a0f08;
      display: flex;
      justify-content: space-around;
      padding: 10px 0 14px;
      border-top: 2px solid #d4af37;
      z-index: 100;
    }
    .nav-item { text-align: center; color: #a1887f; font-size: 11px; text-decoration: none; flex: 1; }
    .nav-item.active { color: #d4af37; }
    .nav-item .icon { font-size: 20px; display: block; }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <h1>🛒 السلة</h1>
  </div>

  <div class="items" id="cartItems"></div>

  <div class="form-section" id="orderForm" style="display:none">
    <input type="text" id="customerName" placeholder="الاسم">
    <input type="tel" id="phone" placeholder="رقم الهاتف">
    <textarea id="notes" rows="2" placeholder="ملاحظات على الطلب (اختياري)"></textarea>
  </div>

  <div class="summary" id="summary" style="display:none">
    <div class="summary-row"><span>المجموع الفرعي</span><span id="subtotal">0</span></div>
    <div class="summary-row total"><span>الإجمالي</span><span id="total">0</span></div>
    <button class="order-btn" id="orderBtn" onclick="placeOrder()">تأكيد الطلب الآن</button>
  </div>

  <div class="empty" id="emptyMsg">السلة فارغة<br><a href="?page=menu" style="color:#d4af37">تصفح المنيو</a></div>

  <div class="bottom-nav">
    <a class="nav-item" href="?page=menu"><span class="icon">🏠</span>الرئيسية</a>
    <a class="nav-item active" href="?page=cart"><span class="icon">🛒</span>السلة</a>
    <a class="nav-item" href="?page=login"><span class="icon">👤</span>حسابي</a>
  </div>

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
        price.textContent = item.price + ' × ' + item.qty + ' = ' + (item.price * item.qty);
        info.append(h3, price);

        const qty = document.createElement('div');
        qty.className = 'qty';
        const minus = document.createElement('button');
        minus.textContent = '−';
        minus.onclick = () => changeQty(idx, -1);
        const count = document.createElement('span');
        count.textContent = item.qty;
        const plus = document.createElement('button');
        plus.textContent = '+';
        plus.onclick = () => changeQty(idx, 1);
        qty.append(minus, count, plus);

        div.append(info, qty);
        container.appendChild(div);
      });

      const sub = cart.reduce((s, i) => s + i.price * i.qty, 0);
      document.getElementById('subtotal').textContent = sub;
      document.getElementById('total').textContent = sub;
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
