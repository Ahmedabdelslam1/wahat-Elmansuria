<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <title>المنيو - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background: #fff8e1;
      color: #212121;
      padding-bottom: 80px;
    }
    .header {
      background: linear-gradient(160deg, #1a0f08, #3e2723);
      color: #d4af37;
      padding: 16px 20px 12px;
      text-align: center;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    .header h1 { font-size: 22px; margin-bottom: 2px; }
    .header .sub { font-size: 12px; color: #ffe082; }
    .badge {
      display: inline-block;
      background: #d4af37;
      color: #1a0f08;
      padding: 4px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: bold;
      margin-top: 8px;
    }
    .cats {
      display: flex;
      gap: 8px;
      padding: 12px 16px;
      overflow-x: auto;
      background: #fff;
      border-bottom: 1px solid #eee;
      position: sticky;
      top: 78px;
      z-index: 90;
    }
    .cat-btn {
      flex-shrink: 0;
      padding: 8px 16px;
      border-radius: 20px;
      border: none;
      background: #f5f5f5;
      color: #5d4037;
      font-size: 13px;
      cursor: pointer;
      font-weight: 600;
    }
    .cat-btn.active { background: #d4af37; color: #1a0f08; }
    .items { padding: 12px 16px; display: flex; flex-direction: column; gap: 12px; }
    .item-card {
      background: #fff;
      border-radius: 16px;
      padding: 14px 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 12px rgba(0,0,0,0.06);
      border: 1px solid #f0e6d3;
    }
    .item-info h3 { font-size: 15px; color: #3e2723; margin-bottom: 4px; }
    .item-info .cat { font-size: 11px; color: #8d6e63; }
    .item-price { font-size: 17px; font-weight: bold; color: #e65100; margin-left: 12px; }
    .add-btn {
      background: #d4af37;
      color: #1a0f08;
      border: none;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      font-size: 20px;
      font-weight: bold;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #1a0f08;
      display: flex;
      justify-content: space-around;
      padding: 10px 0 14px;
      border-top: 2px solid #d4af37;
      z-index: 100;
    }
    .nav-item { text-align: center; color: #a1887f; font-size: 11px; text-decoration: none; flex: 1; }
    .nav-item.active { color: #d4af37; }
    .nav-item .icon { font-size: 20px; display: block; margin-bottom: 2px; }
    .cart-badge {
      position: absolute;
      top: -4px;
      right: 20%;
      background: #e53935;
      color: #fff;
      font-size: 10px;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: none;
      align-items: center;
      justify-content: center;
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="header">
    <h1>🌴 <?= e(APP_NAME) ?></h1>
    <div class="sub">أصل المندي والمشوي</div>
    <div class="badge">فاتورتك إلكترونية</div>
  </div>

  <div class="cats" id="categories"></div>
  <div class="items" id="itemsList"></div>

  <div class="bottom-nav">
    <a class="nav-item active" href="?page=menu"><span class="icon">🏠</span>الرئيسية</a>
    <a class="nav-item" href="?page=cart" style="position:relative">
      <span class="icon">🛒</span>السلة
      <span class="cart-badge" id="cartCount">0</span>
    </a>
    <a class="nav-item" href="?page=login"><span class="icon">👤</span>حسابي</a>
  </div>

  <script>
    let allItems = [];
    let cart = JSON.parse(localStorage.getItem('wahat_cart') || '[]');

    function updateCartBadge() {
      const total = cart.reduce((s, i) => s + i.qty, 0);
      const badge = document.getElementById('cartCount');
      badge.textContent = total;
      badge.style.display = total > 0 ? 'flex' : 'none';
    }

    async function loadMenu() {
      try {
        const res = await api('menu');
        if (!res.success) throw new Error(res.message || 'خطأ');
        allItems = res.items;
        renderCategories(allItems);
        renderItems(allItems);
        updateCartBadge();
      } catch (e) {
        document.getElementById('itemsList').textContent = 'خطأ في تحميل المنيو: ' + (e.message || '');
      }
    }

    function renderCategories(items) {
      const cats = [...new Set(items.map(i => i.category))];
      const container = document.getElementById('categories');
      container.innerHTML = '';
      const mkBtn = (label, cat) => {
        const b = document.createElement('button');
        b.className = 'cat-btn';
        b.textContent = label;
        b.onclick = () => filterCat(cat, b);
        container.appendChild(b);
      };
      mkBtn('الكل', 'all');
      cats.forEach(c => mkBtn(c, c));
      if (container.firstChild) container.firstChild.classList.add('active');
    }

    function filterCat(cat, btn) {
      document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const filtered = cat === 'all' ? allItems : allItems.filter(i => i.category === cat);
      renderItems(filtered);
    }

    function renderItems(items) {
      const list = document.getElementById('itemsList');
      list.innerHTML = '';
      items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'item-card';

        const info = document.createElement('div');
        info.className = 'item-info';
        const h3 = document.createElement('h3');
        h3.textContent = item.name;
        const catDiv = document.createElement('div');
        catDiv.className = 'cat';
        catDiv.textContent = item.category;
        info.append(h3, catDiv);

        const right = document.createElement('div');
        right.style.cssText = 'display:flex;align-items:center;gap:10px';
        const price = document.createElement('div');
        price.className = 'item-price';
        price.textContent = item.price;
        const btn = document.createElement('button');
        btn.className = 'add-btn';
        btn.textContent = '+';
        btn.onclick = () => addToCart(item, btn);
        right.append(price, btn);

        card.append(info, right);
        list.appendChild(card);
      });
    }

    function addToCart(item, btn) {
      const exist = cart.find(c => String(c.id) === String(item.id));
      if (exist) exist.qty++;
      else cart.push({ id: item.id, name: item.name, price: Number(item.price) || 0, qty: 1 });
      localStorage.setItem('wahat_cart', JSON.stringify(cart));
      updateCartBadge();
      btn.textContent = '✓';
      setTimeout(() => btn.textContent = '+', 600);
    }

    loadMenu();
  </script>
</body>
</html>
