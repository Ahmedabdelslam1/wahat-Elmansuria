<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <meta name="theme-color" content="#15151f">
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" href="assets/icons/icon-180.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>المنيو - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2;
      --radius:12px;
    }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: var(--bg);
      color: var(--text);
      padding-bottom: 100px;
    }
    .topbar {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      padding: 12px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 6px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 18px rgba(0,0,0,0.25);
    }
    .icon-btn {
      width: 34px; height: 34px;
      border-radius: 50%;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.14);
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      text-decoration: none;
      flex-shrink: 0;
      position: relative;
    }
    .icon-btn svg { width: 16px; height: 16px; }
    .icon-btn .dot {
      position: absolute; top: -3px; left: -3px;
      background: var(--primary); color: #fff;
      font-size: 9.5px; font-weight: 800;
      width: 15px; height: 15px; border-radius: 50%;
      display: none; align-items: center; justify-content: center;
      border: 2px solid var(--dark);
    }
    .brand { flex: 1; text-align: center; }
    .brand-name { color: #fff; font-size: 14.5px; font-weight: 800; }
    .brand-sub { color: var(--accent2); font-size: 9px; margin-top: 1px; }

    .search-wrap { padding: 10px 14px 4px; }
    .search-box {
      display: flex; align-items: center; gap: 8px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 8px 12px;
      box-shadow: 0 2px 10px rgba(20,20,30,0.05);
    }
    .search-box svg { width: 14px; height: 14px; color: var(--muted); flex-shrink:0; }
    .search-box input {
      border: none; outline: none; background: transparent;
      font-family: inherit; font-size: 13px; width: 100%; color: var(--text);
    }

    .cats {
      display: flex;
      gap: 10px;
      padding: 10px 14px 2px;
      overflow-x: auto;
    }
    .cats::-webkit-scrollbar{ display:none; }
    .cat-item { flex-shrink: 0; text-align: center; cursor: pointer; width: 48px; }
    .cat-circle {
      width: 40px; height: 40px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px;
      margin: 0 auto 4px;
      border: 2px solid transparent;
      transition: transform .15s;
      overflow: hidden;
    }
    .cat-circle img { width: 100%; height: 100%; object-fit: cover; }
    .cat-item.active .cat-circle { border-color: var(--primary); transform: scale(1.1); box-shadow: 0 4px 14px -3px rgba(255,59,48,0.5); }
    .cat-label { font-size: 9px; color: var(--muted); font-weight: 700; white-space: nowrap; }
    .cat-item.active .cat-label { color: var(--text); }

    /* ===== كروت صغيرة مضيئة، 3 أعمدة على الموبايل ===== */
    .grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      padding: 8px 12px 16px;
    }
    @media (min-width: 420px) { .grid { grid-template-columns: repeat(3, 1fr); gap: 10px; padding: 10px 14px 16px; } }
    @media (min-width: 640px) { .grid { grid-template-columns: repeat(4, 1fr); } }
    @media (min-width: 900px) { .grid { grid-template-columns: repeat(5, 1fr); } }
    .card {
      background: var(--surface);
      border-radius: var(--radius);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      border: 1px solid var(--border);
      transition: transform .15s;
    }
    .card:active { transform: scale(0.96); }
    .thumb {
      aspect-ratio: 1.1/1;
      position: relative;
      overflow: hidden;
    }
    .thumb img {
      width: 100%; height: 100%;
      object-fit: cover; display: block;
    }
    .thumb .cat-chip {
      position: absolute; top: 4px; right: 4px;
      background: rgba(21,21,31,0.72);
      backdrop-filter: blur(2px);
      font-size: 7px; font-weight: 800; padding: 2px 5px;
      border-radius: 20px; color: #fff;
      max-width: calc(100% - 8px); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .card .body { padding: 6px 7px 8px; flex: 1; display: flex; flex-direction: column; }
    .card h3 { font-size: 10.5px; font-weight: 800; line-height: 1.3; margin-bottom: 3px; min-height: 27px; }
    .card .price-row { display: flex; align-items: center; justify-content: space-between; gap: 4px; margin-top: auto; }
    .card .price { font-size: 11.5px; font-weight: 800; color: var(--text); }
    .card .price small { font-size: 8px; font-weight: 700; color: var(--muted); }
    .card .add-btn {
      border: none; color: #fff; font-weight: 800; font-size: 9.5px;
      padding: 5px 8px; border-radius: 8px; cursor: pointer;
      display: flex; align-items: center; gap: 3px;
    }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); grid-column: 1 / -1; }

    .fab-cart {
      position: fixed; bottom: 16px; left: 16px; right: 16px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff; border: none; border-radius: 16px;
      padding: 14px 18px;
      font-family: inherit; font-weight: 800; font-size: 13.5px;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 10px 30px -6px rgba(255,59,48,0.55);
      z-index: 90;
    }
    .fab-cart .count-badge {
      background: rgba(255,255,255,0.25);
      width: 22px; height: 22px; border-radius: 50%;
      display: inline-flex; align-items: center; justify-content: center;
      font-size: 11px; margin-left: 8px;
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="topbar">
    <a class="icon-btn" href="?page=home" title="الرئيسية">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <a class="icon-btn" href="?page=login" title="دخول المستخدمين والأدمن">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
    </a>
    <div class="brand">
      <div class="brand-name">🌴 <?= e(APP_NAME) ?></div>
      <div class="brand-sub">أصل المندي والمشوي</div>
    </div>
    <a class="icon-btn" href="?page=cart" title="السلة">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
      <span class="dot" id="cartDot">0</span>
    </a>
  </div>

  <div class="search-wrap">
    <div class="search-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
      <input id="searchInput" placeholder="بحث في المنيو..." oninput="onSearch()">
    </div>
  </div>

  <div class="cats" id="categories"></div>
  <div class="grid" id="itemsList"></div>

  <button class="fab-cart" id="fabCart" style="display:none" onclick="location.href='?page=cart'">
    <span><span class="count-badge" id="fabCount">0</span>عرض السلة</span>
    <span id="fabTotal">0 ج.م</span>
  </button>

  <script>
    // ===== ألوان الفئات (تُستخدم للتوهج والشرائط) =====
    const PALETTE = [
      { bg: '#FF3B30' }, { bg: '#FF9500' }, { bg: '#34C759' },
      { bg: '#5AC8FA' }, { bg: '#AF52DE' }, { bg: '#FF2D55' }, { bg: '#007AFF' },
    ];
    const CAT_ICONS = [
      { keys: ['دجاج','فرخ','برياني','كبسة','مندي'], icon: '🍗' },
      { keys: ['لحم','جدي','ضاني'], icon: '🥩' },
      { keys: ['مشوي','كباب','كفتة','طاووق','سيخ','ريش'], icon: '🍢' },
      { keys: ['طاجن','طواجن','مطبخ','بامية','ملوخية'], icon: '🍲' },
      { keys: ['مكرونة'], icon: '🍝' },
      { keys: ['ساندوتش'], icon: '🥙' },
      { keys: ['صيني','صواني','سفرة','وليمة','مولد'], icon: '🍛' },
      { keys: ['وجبات','ميكس','فردية'], icon: '🍽️' },
    ];
    function hashStr(s) {
      let h = 0;
      for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
      return h;
    }
    function colorFor(cat) { return PALETTE[hashStr(cat || '') % PALETTE.length]; }
    function emojiFor(cat) {
      const c = cat || '';
      for (const m of CAT_ICONS) if (m.keys.some(k => c.includes(k))) return m.icon;
      return '🍽️';
    }
    function hexToRgba(hex, a) {
      const v = hex.replace('#','');
      const r = parseInt(v.substr(0,2),16), g = parseInt(v.substr(2,2),16), b = parseInt(v.substr(4,2),16);
      return `rgba(${r},${g},${b},${a})`;
    }

    let allItems = [];
    let catImages = {};
    let activeCat = 'all';
    let searchTerm = '';
    let cart = JSON.parse(localStorage.getItem('wahat_cart') || '[]');

    function updateCartUI() {
      const totalQty = cart.reduce((s, i) => s + i.qty, 0);
      const totalPrice = cart.reduce((s, i) => s + i.qty * i.price, 0);
      const dot = document.getElementById('cartDot');
      dot.style.display = totalQty > 0 ? 'flex' : 'none';
      dot.textContent = totalQty;
      const fab = document.getElementById('fabCart');
      fab.style.display = totalQty > 0 ? 'flex' : 'none';
      document.getElementById('fabCount').textContent = totalQty;
      document.getElementById('fabTotal').textContent = totalPrice + ' ج.م';
    }

    async function loadMenu() {
      try {
        const res = await api('menu');
        if (!res.success) throw new Error(res.message || 'خطأ');
        allItems = res.items;
        allItems.forEach(i => { if (!catImages[i.category]) catImages[i.category] = i.image; });
        renderCategories();
        renderItems();
        updateCartUI();
      } catch (e) {
        document.getElementById('itemsList').innerHTML = '<div class="empty-state">خطأ في تحميل المنيو</div>';
      }
    }

    function renderCategories() {
      const cats = [...new Set(allItems.map(i => i.category))];
      const container = document.getElementById('categories');
      container.innerHTML = '';
      const mk = (label, key, img) => {
        const wrap = document.createElement('div');
        wrap.className = 'cat-item' + (key === activeCat ? ' active' : '');
        const circle = document.createElement('div');
        circle.className = 'cat-circle';
        if (key === 'all') {
          circle.style.background = 'linear-gradient(135deg,#15151f,#3a3a4d)';
          circle.style.color = '#fff';
          circle.textContent = '🌟';
        } else {
          const im = document.createElement('img');
          im.src = img; im.alt = label; im.loading = 'lazy';
          circle.appendChild(im);
        }
        const label_ = document.createElement('div');
        label_.className = 'cat-label';
        label_.textContent = label;
        wrap.append(circle, label_);
        wrap.onclick = () => { activeCat = key; renderCategories(); renderItems(); };
        container.appendChild(wrap);
      };
      mk('الكل', 'all', null);
      cats.forEach(c => mk(c, c, catImages[c]));
    }

    function onSearch() {
      searchTerm = document.getElementById('searchInput').value.trim().toLowerCase();
      renderItems();
    }

    function renderItems() {
      let items = activeCat === 'all' ? allItems : allItems.filter(i => i.category === activeCat);
      if (searchTerm) items = items.filter(i => i.name.toLowerCase().includes(searchTerm));

      const list = document.getElementById('itemsList');
      list.innerHTML = '';
      if (items.length === 0) {
        list.innerHTML = '<div class="empty-state">لا توجد أصناف مطابقة</div>';
        return;
      }
      items.forEach(item => {
        const color = colorFor(item.category);
        const card = document.createElement('div');
        card.className = 'card';
        card.style.boxShadow = `0 6px 16px -6px ${hexToRgba(color.bg, 0.5)}`;

        const thumb = document.createElement('div');
        thumb.className = 'thumb';
        const img = document.createElement('img');
        img.src = item.image;
        img.alt = item.name;
        img.loading = 'lazy';
        thumb.appendChild(img);
        const chip = document.createElement('span');
        chip.className = 'cat-chip';
        chip.textContent = emojiFor(item.category);
        thumb.appendChild(chip);

        const body = document.createElement('div');
        body.className = 'body';
        const h3 = document.createElement('h3');
        h3.textContent = item.name;
        const priceRow = document.createElement('div');
        priceRow.className = 'price-row';
        const price = document.createElement('div');
        price.className = 'price';
        price.innerHTML = item.price + ' <small>ج.م</small>';
        const btn = document.createElement('button');
        btn.className = 'add-btn';
        btn.style.background = color.bg;
        btn.textContent = '+';
        btn.onclick = () => addToCart(item, btn);
        priceRow.append(price, btn);

        body.append(h3, priceRow);
        card.append(thumb, body);
        list.appendChild(card);
      });
    }

    function addToCart(item, btn) {
      const exist = cart.find(c => String(c.id) === String(item.id));
      if (exist) exist.qty++;
      else cart.push({ id: item.id, name: item.name, price: Number(item.price) || 0, qty: 1 });
      localStorage.setItem('wahat_cart', JSON.stringify(cart));
      updateCartUI();
      const old = btn.textContent;
      btn.textContent = '✓';
      setTimeout(() => btn.textContent = old, 700);
    }

    loadMenu();

    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => { navigator.serviceWorker.register('sw.js').catch(() => {}); });
    }
  </script>
</body>
</html>
