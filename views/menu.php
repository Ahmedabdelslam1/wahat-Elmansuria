<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <base target="_top">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= e($_SESSION['csrf']) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
  <title>المنيو - <?= e(APP_NAME) ?></title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    :root{
      --bg:#f4f5fa; --surface:#ffffff; --text:#181822; --muted:#8b8b9a;
      --primary:#ff3b30; --primary-dark:#d32f2f; --accent2:#ff9500;
      --dark:#15151f; --dark2:#1f1f2c; --border:#ececf2;
      --radius:18px;
    }
    body {
      font-family: 'Tajawal', 'Segoe UI', Tahoma, sans-serif;
      background: var(--bg);
      color: var(--text);
      padding-bottom: 100px;
    }
    .topbar {
      background: linear-gradient(135deg, var(--dark), var(--dark2));
      padding: 14px 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 4px 18px rgba(0,0,0,0.25);
    }
    .icon-btn {
      width: 40px; height: 40px;
      border-radius: 50%;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.14);
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      text-decoration: none;
      flex-shrink: 0;
      position: relative;
    }
    .icon-btn svg { width: 19px; height: 19px; }
    .icon-btn .dot {
      position: absolute; top: -3px; left: -3px;
      background: var(--primary); color: #fff;
      font-size: 10px; font-weight: 800;
      width: 17px; height: 17px; border-radius: 50%;
      display: none; align-items: center; justify-content: center;
      border: 2px solid var(--dark);
    }
    .brand { flex: 1; text-align: center; }
    .brand-name { color: #fff; font-size: 17px; font-weight: 800; }
    .brand-sub { color: var(--accent2); font-size: 10px; margin-top: 1px; }

    .search-wrap { padding: 14px 16px 6px; }
    .search-box {
      display: flex; align-items: center; gap: 8px;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 11px 14px;
      box-shadow: 0 2px 10px rgba(20,20,30,0.05);
    }
    .search-box svg { width: 16px; height: 16px; color: var(--muted); flex-shrink:0; }
    .search-box input {
      border: none; outline: none; background: transparent;
      font-family: inherit; font-size: 14px; width: 100%; color: var(--text);
    }

    .cats {
      display: flex;
      gap: 14px;
      padding: 14px 16px 6px;
      overflow-x: auto;
    }
    .cats::-webkit-scrollbar{ display:none; }
    .cat-item { flex-shrink: 0; text-align: center; cursor: pointer; width: 62px; }
    .cat-circle {
      width: 54px; height: 54px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 24px;
      margin: 0 auto 6px;
      border: 2px solid transparent;
      transition: transform .15s;
    }
    .cat-item.active .cat-circle { border-color: #fff; transform: scale(1.08); }
    .cat-label { font-size: 10.5px; color: var(--muted); font-weight: 700; white-space: nowrap; }
    .cat-item.active .cat-label { color: var(--text); }

    .grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      padding: 10px 16px 16px;
    }
    .card {
      background: var(--surface);
      border-radius: var(--radius);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      border: 1px solid var(--border);
      transition: transform .15s;
    }
    .card:active { transform: scale(0.98); }
    .thumb {
      aspect-ratio: 1.15/1;
      display: flex; align-items: center; justify-content: center;
      font-size: 46px;
      position: relative;
    }
    .thumb .cat-chip {
      position: absolute; top: 8px; right: 8px;
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(2px);
      font-size: 9.5px; font-weight: 800; padding: 3px 8px;
      border-radius: 20px; color: var(--dark);
    }
    .card .body { padding: 10px 12px 12px; flex: 1; display: flex; flex-direction: column; }
    .card h3 { font-size: 13.5px; font-weight: 800; line-height: 1.35; margin-bottom: 4px; min-height: 36px; }
    .card .desc { font-size: 10.5px; color: var(--muted); margin-bottom: 8px; line-height: 1.3; flex: 1; }
    .card .price-row { display: flex; align-items: center; justify-content: space-between; gap: 6px; }
    .card .price { font-size: 15px; font-weight: 800; color: var(--text); }
    .card .price small { font-size: 10px; font-weight: 700; color: var(--muted); }
    .card .add-btn {
      border: none; color: #fff; font-weight: 800; font-size: 11.5px;
      padding: 8px 14px; border-radius: 10px; cursor: pointer;
      display: flex; align-items: center; gap: 4px;
    }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--muted); grid-column: 1 / -1; }

    .fab-cart {
      position: fixed; bottom: 18px; left: 16px; right: 16px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: #fff; border: none; border-radius: 16px;
      padding: 15px 18px;
      font-family: inherit; font-weight: 800; font-size: 14px;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: 0 10px 30px -6px rgba(255,59,48,0.55);
      z-index: 90;
    }
    .fab-cart .count-badge {
      background: rgba(255,255,255,0.25);
      width: 24px; height: 24px; border-radius: 50%;
      display: inline-flex; align-items: center; justify-content: center;
      font-size: 12px; margin-left: 8px;
    }
  </style>
<script src="?asset=api.js"></script>
</head>
<body>
  <div class="topbar">
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
    // ===== ألوان وأيقونات الفئات (تحدد تلقائيًا حسب اسم القسم) =====
    const PALETTE = [
      { bg: '#FF3B30', soft: '#FFEBE9' }, // أحمر
      { bg: '#FF9500', soft: '#FFF3E0' }, // برتقالي
      { bg: '#34C759', soft: '#E7F8EC' }, // أخضر
      { bg: '#5AC8FA', soft: '#E7F6FE' }, // أزرق فاتح
      { bg: '#AF52DE', soft: '#F5E9FB' }, // بنفسجي
      { bg: '#FF2D55', soft: '#FFE7ED' }, // وردي
      { bg: '#007AFF', soft: '#E5F0FF' }, // أزرق
    ];
    const EMOJI_MAP = [
      { keys: ['دجاج','فرخ'], icon: '🍗' },
      { keys: ['لحم'], icon: '🥩' },
      { keys: ['مشوي','كباب','كفتة'], icon: '🍢' },
      { keys: ['طاجن','طواجن'], icon: '🍲' },
      { keys: ['مطبخ','مكرونة'], icon: '🍝' },
      { keys: ['ساندوتش'], icon: '🥙' },
      { keys: ['صيني','صواني','سفرة'], icon: '🍛' },
    ];
    function hashStr(s) {
      let h = 0;
      for (let i = 0; i < s.length; i++) h = (h * 31 + s.charCodeAt(i)) >>> 0;
      return h;
    }
    function colorFor(cat) { return PALETTE[hashStr(cat || '') % PALETTE.length]; }
    function emojiFor(cat) {
      const c = cat || '';
      for (const m of EMOJI_MAP) if (m.keys.some(k => c.includes(k))) return m.icon;
      return '🍽️';
    }
    function hexToRgba(hex, a) {
      const v = hex.replace('#','');
      const r = parseInt(v.substr(0,2),16), g = parseInt(v.substr(2,2),16), b = parseInt(v.substr(4,2),16);
      return `rgba(${r},${g},${b},${a})`;
    }

    let allItems = [];
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
      const mk = (label, key, emoji, color) => {
        const wrap = document.createElement('div');
        wrap.className = 'cat-item' + (key === activeCat ? ' active' : '');
        const circle = document.createElement('div');
        circle.className = 'cat-circle';
        circle.style.background = key === 'all' ? 'linear-gradient(135deg,#15151f,#3a3a4d)' : color.bg;
        circle.textContent = emoji;
        const label_ = document.createElement('div');
        label_.className = 'cat-label';
        label_.textContent = label;
        wrap.append(circle, label_);
        wrap.onclick = () => { activeCat = key; renderCategories(); renderItems(); };
        container.appendChild(wrap);
      };
      mk('الكل', 'all', '🌟', null);
      cats.forEach(c => mk(c, c, emojiFor(c), colorFor(c)));
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
        card.style.boxShadow = `0 10px 26px -8px ${hexToRgba(color.bg, 0.55)}`;

        const thumb = document.createElement('div');
        thumb.className = 'thumb';
        thumb.style.background = `linear-gradient(150deg, ${hexToRgba(color.bg,0.16)}, ${hexToRgba(color.bg,0.32)})`;
        thumb.textContent = emojiFor(item.category);
        const chip = document.createElement('span');
        chip.className = 'cat-chip';
        chip.textContent = item.category;
        thumb.appendChild(chip);

        const body = document.createElement('div');
        body.className = 'body';
        const h3 = document.createElement('h3');
        h3.textContent = item.name;
        const desc = document.createElement('div');
        desc.className = 'desc';
        desc.textContent = item.desc || item.category;
        const priceRow = document.createElement('div');
        priceRow.className = 'price-row';
        const price = document.createElement('div');
        price.className = 'price';
        price.innerHTML = item.price + ' <small>ج.م</small>';
        const btn = document.createElement('button');
        btn.className = 'add-btn';
        btn.style.background = color.bg;
        btn.textContent = '+ أضف';
        btn.onclick = () => addToCart(item, btn);
        priceRow.append(price, btn);

        body.append(h3, desc, priceRow);
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
      btn.textContent = '✓ تمت';
      setTimeout(() => btn.textContent = old, 700);
    }

    loadMenu();
  </script>
</body>
</html>
