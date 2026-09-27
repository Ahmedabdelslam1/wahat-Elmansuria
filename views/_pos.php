<?php
/**
 * =====================================================
 *  🧾 شاشة طلب جديد (POS) - جزء مشترك
 *  يُضمَّن في صفحة الكاشير ولوحة المدير معًا.
 *  المتغير $posActive (اختياري): هل هي اللوحة الافتراضية.
 * =====================================================
 */
$posActive = $posActive ?? false;
$posAllowCompany = $posAllowCompany ?? false;
?>
<style>
    .pos-wrap { display:flex; flex-direction:column; }
    .pos-search { padding:10px 12px 4px; }
    .pos-search input { width:100%; padding:9px 13px; border:1px solid var(--border); border-radius:12px; font-family:inherit; font-size:13px; background:var(--bg); }
    .pos-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:7px; padding:8px 12px; }
    @media (min-width:600px) { .pos-grid { grid-template-columns:repeat(4,1fr); } }
    .pos-item { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; cursor:pointer; text-align:center; transition:transform .1s; }
    .pos-item:active { transform:scale(0.95); }
    .pos-item .th { aspect-ratio:1.2/1; overflow:hidden; background:var(--bg); }
    .pos-item .th img { width:100%; height:100%; object-fit:cover; display:block; }
    .pos-item .b { padding:5px 5px 7px; }
    .pos-item h4 { font-size:9.5px; font-weight:800; min-height:24px; line-height:1.25; margin-bottom:2px; }
    .pos-item .p { font-size:10.5px; font-weight:800; color:var(--primary); }
    .pos-cart {
      position:sticky; bottom:0; background:var(--surface); border-top:1px solid var(--border);
      box-shadow: 0 -6px 20px -8px rgba(20,20,30,0.15);
      padding: 10px 14px 14px; z-index: 40;
    }
    .pos-cart-list { max-height: 160px; overflow-y:auto; margin-bottom:8px; }
    .pos-cart-row { display:flex; align-items:center; justify-content:space-between; padding:6px 0; border-bottom:1px dashed var(--border); font-size:12px; }
    .qty-ctl { display:flex; align-items:center; gap:8px; }
    .qty-ctl button { width:24px; height:24px; border-radius:8px; border:none; background:var(--bg); font-weight:800; cursor:pointer; }
    .pos-fields { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:8px; }
    .pos-fields input { padding:9px 11px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:12.5px; background:var(--bg); }
    .pos-address { display:none; margin-bottom:8px; }
    .pos-address input { width:100%; padding:9px 11px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:12.5px; background:var(--bg); }
    .pos-address.show { display:block; }
    .fee-rows { display:flex; justify-content:space-between; font-size:11px; color:var(--muted); font-weight:700; padding:2px 0 6px; }
    .fee-rows.delivery-on { color:#ef6c00; }
    .type-toggle { display:flex; gap:8px; margin-bottom:8px; }
    .type-toggle button {
      flex:1; padding:10px; border-radius:10px; border:1px solid var(--border); background:var(--bg);
      font-family:inherit; font-weight:800; font-size:12.5px; cursor:pointer; color:var(--text);
    }
    .type-toggle button.active.dinein { background:#e5f0ff; border-color:#007aff; color:#007aff; }
    .type-toggle button.active.delivery { background:#fff3e0; border-color:#ff9500; color:#ff9500; }
    .type-toggle button.active.company { background:#e7f8ec; border-color:#34c759; color:#34c759; }
    .pos-company { display:none; margin-bottom:8px; }
    .pos-company.show { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
    .pos-company input { padding:9px 11px; border:1px solid var(--border); border-radius:10px; font-family:inherit; font-size:12.5px; background:var(--bg); }
    .pos-submit-row { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .pos-total { font-size:16px; font-weight:800; color:var(--primary); }
    .pos-submit { flex:1; background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:#fff; border:none; padding:13px; border-radius:12px; font-weight:800; font-size:13.5px; cursor:pointer; font-family:inherit; }
    .pos-submit:disabled { opacity:.5; }
    .pos-empty { padding:14px; font-size:12.5px; color:var(--muted); }
</style>

<div class="panel<?= $posActive ? ' active' : '' ?>" id="panel-pos">
  <div class="pos-wrap">
    <div class="pos-search"><input id="posSearch" placeholder="بحث عن صنف..." oninput="renderPosGrid()"></div>
    <div class="pos-grid" id="posGrid"><div class="pos-empty">جاري تحميل الأصناف...</div></div>
  </div>
  <div class="pos-cart">
    <div class="pos-cart-list" id="posCartList"><div class="pos-empty">لم تُضف أي أصناف بعد</div></div>
    <div class="type-toggle">
      <button class="dinein active" id="btnDinein" onclick="setOrderType('صالة')">🍽️ صالة</button>
      <button class="delivery" id="btnDelivery" onclick="setOrderType('دليفري')">🛵 دليفري</button>
      <?php if ($posAllowCompany): ?><button class="company" id="btnCompany" onclick="setOrderType('شركات')">🏢 شركات</button><?php endif; ?>
    </div>
    <?= $posAllowCompany ? '<div class="pos-company" id="posCompanyWrap"><input id="posCompany" placeholder="🏢 اسم الشركة (إجباري)"><input id="posDepartment" placeholder="🏬 القسم (اختياري)"></div>' : '' ?>
    <div class="pos-fields">
      <input id="posCustomer" placeholder="اسم العميل (اختياري)">
      <input id="posPhone" placeholder="رقم الهاتف (لواتساب)">
    </div>
    <div class="pos-fields" style="grid-template-columns:1fr">
      <input id="posNotes" placeholder="ملاحظات (اختياري)">
    </div>
    <div class="pos-address" id="posAddressWrap">
      <input id="posAddress" placeholder="📍 عنوان التوصيل (إجباري للدليفري)">
    </div>
    <div id="feeRows"></div>
    <div class="pos-submit-row">
      <span class="pos-total" id="posTotal">0 ج.م</span>
      <button class="pos-submit" id="posSubmitBtn" onclick="submitPosOrder()">تأكيد الطلب</button>
    </div>
  </div>
</div>

<script>
(function () {
  if (window.__posBooted) return;
  window.__posBooted = true;

  var posCart = [];
  var posOrderType = 'صالة';
  var posDeliveryFee = 0;
  var posMenuData = [];
  var posReady = false;

  function pEsc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  window.renderPosGrid = function () {
    var term = (document.getElementById('posSearch').value || '').trim().toLowerCase();
    var items = term ? posMenuData.filter(function (i) { return i.name.toLowerCase().indexOf(term) !== -1; }) : posMenuData;
    if (!items.length) {
      document.getElementById('posGrid').innerHTML = '<div class="pos-empty">لا توجد أصناف مطابقة</div>';
      return;
    }
    document.getElementById('posGrid').innerHTML = items.map(function (i) {
      return '<div class="pos-item" onclick=\'posAdd(' + JSON.stringify(i).replace(/'/g, '&#39;') + ')\'>'
        + '<div class="th"><img src="' + pEsc(i.image || '') + '" alt="" loading="lazy"></div>'
        + '<div class="b"><h4>' + pEsc(i.name) + '</h4><div class="p">' + pEsc(i.price) + ' ج.م</div></div>'
        + '</div>';
    }).join('');
  };

  window.posAdd = function (item) {
    var exist = posCart.find(function (c) { return String(c.id) === String(item.id); });
    if (exist) exist.qty++;
    else posCart.push({ id: item.id, name: item.name, price: Number(item.price) || 0, qty: 1 });
    renderPosCart();
  };

  window.posChangeQty = function (id, delta) {
    var it = posCart.find(function (c) { return String(c.id) === String(id); });
    if (!it) return;
    it.qty += delta;
    if (it.qty <= 0) posCart = posCart.filter(function (c) { return String(c.id) !== String(id); });
    renderPosCart();
  };

  window.setOrderType = function (type) {
    posOrderType = type;
    document.getElementById('btnDinein').classList.toggle('active', type === 'صالة');
    document.getElementById('btnDelivery').classList.toggle('active', type === 'دليفري');
    var btnCo = document.getElementById('btnCompany');
    if (btnCo) btnCo.classList.toggle('active', type === 'شركات');
    document.getElementById('posAddressWrap').classList.toggle('show', type === 'دليفري');
    var coWrap = document.getElementById('posCompanyWrap');
    if (coWrap) coWrap.classList.toggle('show', type === 'شركات');
    renderPosCart();
  };

  window.renderPosCart = function () {
    var list = document.getElementById('posCartList');
    if (!posCart.length) {
      list.innerHTML = '<div class="pos-empty">لم تُضف أي أصناف بعد</div>';
    } else {
      list.innerHTML = posCart.map(function (c) {
        return '<div class="pos-cart-row"><span>' + pEsc(c.name) + '</span>'
          + '<div class="qty-ctl">'
          + '<button onclick="posChangeQty(' + c.id + ', -1)">−</button>'
          + '<b>' + c.qty + '</b>'
          + '<button onclick="posChangeQty(' + c.id + ', 1)">+</button>'
          + '<span style="min-width:50px;text-align:left;font-weight:700">' + (c.qty * c.price) + '</span>'
          + '</div></div>';
      }).join('');
    }
    var subtotal = posCart.reduce(function (s, c) { return s + c.qty * c.price; }, 0);
    var isDelivery = posOrderType === 'دليفري';
    var fee = isDelivery ? posDeliveryFee : 0;
    var total = subtotal + fee;
    document.getElementById('feeRows').innerHTML = isDelivery
      ? '<div class="fee-rows delivery-on"><span>المجموع: ' + subtotal + ' ج.م</span><span>🛵 التوصيل: ' + fee + ' ج.م</span></div>'
      : '';
    document.getElementById('posTotal').textContent = total + ' ج.م';
  };

  window.submitPosOrder = async function () {
    if (!posCart.length) { alert('أضف أصنافًا أولًا'); return; }
    var address = document.getElementById('posAddress').value.trim();
    var companyName = '', department = '';
    var coEl = document.getElementById('posCompany');
    if (coEl) { companyName = coEl.value.trim(); department = (document.getElementById('posDepartment').value || '').trim(); }
    if (posOrderType === 'شركات' && !companyName) { alert('أدخل اسم الشركة'); return; }
    if (posOrderType === 'دليفري') {
      var dphone = document.getElementById('posPhone').value.trim();
      if (!dphone || !address) { alert('طلبات الدليفري تتطلب رقم الهاتف والعنوان'); return; }
    }
    var btn = document.getElementById('posSubmitBtn');
    btn.disabled = true;
    try {
      var res = await api('place_order', {
        items: posCart.map(function (c) { return { id: c.id, qty: c.qty }; }),
        customerName: document.getElementById('posCustomer').value.trim(),
        phone: document.getElementById('posPhone').value.trim(),
        notes: document.getElementById('posNotes').value.trim(),
        address: address,
        orderType: posOrderType,
        companyName: companyName,
        department: department,
      });
      btn.disabled = false;
      if (res.success) {
        posCart = [];
        renderPosCart();
        ['posCustomer', 'posPhone', 'posNotes', 'posAddress', 'posCompany', 'posDepartment'].forEach(function (id) { var el = document.getElementById(id); if (el) el.value = ''; });
        if (typeof window.__posOrderDone === 'function') {
          __posOrderDone(res);
        } else {
          alert('تم إنشاء الطلب: ' + res.orderId);
          if (confirm('فتح فاتورة الطلب؟')) window.open('?page=invoice&id=' + encodeURIComponent(res.orderId), '_blank');
        }
        if (typeof window.posAfterSubmit === 'function') window.posAfterSubmit(res);
      } else alert(res.message || 'خطأ');
    } catch (e) { btn.disabled = false; alert('خطأ في الاتصال'); }
  };

  window.posInit = async function () {
    try {
      var m = await api('menu');
      if (m.success) posMenuData = m.items;
    } catch (e) {}
    try {
      var st = await api('public_settings');
      if (st.success && st.settings) posDeliveryFee = Number(st.settings.deliveryFee) || 0;
    } catch (e) {}
    posReady = true;
    renderPosGrid();
    renderPosCart();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.posInit);
  } else {
    window.posInit();
  }
})();
</script>
