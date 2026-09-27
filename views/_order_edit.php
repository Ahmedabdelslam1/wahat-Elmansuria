<?php
/**
 * =====================================================
 *  ✏️ تعديل / إضافة أصناف / إلغاء / حذف الطلبات
 *  جزء مشترك يُضمَّن في صفحة الكاشير ولوحة المدير.
 *  الصفحة المضيفة تعرّف window.orderActionsRefresh()
 *  لتحديث القوائم بعد أي عملية.
 * =====================================================
 */
?>
<style>
    .oe-overlay { display:none; position:fixed; inset:0; background:rgba(15,15,25,0.55); z-index:999; align-items:flex; justify-content:center; padding:14px; }
    .oe-overlay.show { display:flex; }
    .oe-modal { background:var(--surface); border-radius:16px; width:100%; max-width:420px; max-height:88vh; overflow-y:auto; padding:16px; direction:rtl; }
    .oe-modal h3 { font-size:15px; font-weight:800; margin-bottom:4px; }
    .oe-modal .oe-id { font-size:11.5px; color:var(--muted); margin-bottom:12px; font-weight:700; }
    .oe-modal label { display:block; font-size:11.5px; color:var(--muted); font-weight:700; margin-bottom:4px; }
    .oe-modal input, .oe-modal select, .oe-modal textarea {
      width:100%; padding:9px 11px; border:1px solid var(--border); border-radius:10px;
      font-family:inherit; font-size:12.5px; margin-bottom:10px; background:var(--bg); color:var(--text);
    }
    .oe-type-row { display:flex; gap:6px; margin-bottom:10px; }
    .oe-type-row button {
      flex:1; padding:8px; border-radius:10px; border:1px solid var(--border); background:var(--bg);
      font-family:inherit; font-weight:800; font-size:11.5px; cursor:pointer; color:var(--text);
    }
    .oe-type-row button.active.dinein { background:#e5f0ff; border-color:#007aff; color:#007aff; }
    .oe-type-row button.active.delivery { background:#fff3e0; border-color:#ff9500; color:#ff9500; }
    .oe-type-row button.active.company { background:#e7f8ec; border-color:#34c759; color:#34c759; }
    .oe-items { border:1px dashed var(--border); border-radius:10px; padding:8px 10px; margin-bottom:10px; }
    .oe-item-row { display:flex; align-items:center; justify-content:space-between; padding:5px 0; border-bottom:1px dashed var(--border); font-size:12px; gap:6px; }
    .oe-item-row:last-child { border-bottom:none; }
    .oe-item-row .oe-qty { display:flex; align-items:center; gap:6px; }
    .oe-item-row .oe-qty button { width:22px; height:22px; border-radius:7px; border:none; background:var(--bg); font-weight:800; cursor:pointer; }
    .oe-add-row { display:flex; gap:6px; margin-bottom:10px; }
    .oe-add-row select { flex:1; }
    .oe-add-row button { padding:9px 12px; border-radius:10px; border:none; background:var(--primary); color:#fff; font-weight:800; cursor:pointer; font-family:inherit; font-size:12px; }
    .oe-actions { display:flex; gap:8px; margin-top:6px; }
    .oe-actions button { flex:1; padding:11px; border-radius:10px; border:none; font-weight:800; cursor:pointer; font-family:inherit; font-size:12.5px; }
    .oe-save { background:linear-gradient(90deg,var(--primary),var(--primary-dark)); color:#fff; }
    .oe-cancel-btn { background:#fff3e0; color:#ef6c00; }
    .oe-delete-btn { background:#ffebe9; color:#ff3b30; }
    .oe-total-row { display:flex; justify-content:space-between; font-size:13px; font-weight:800; padding:8px 0 2px; color:var(--primary); }
    .act-btn { border:none; border-radius:8px; padding:5px 9px; font-size:10.5px; font-weight:700; cursor:pointer; font-family:inherit; }
    .act-edit { background:#e5f0ff; color:#007aff; }
    .act-add { background:#e7f8ec; color:#34c759; }
    .act-cancel { background:#fff3e0; color:#ef6c00; }
    .act-del { background:#ffebe9; color:#ff3b30; }
    .order-status.cancelled { background:#f0f0f2; color:#8b8b9a; }
    .order-card.cancelled { opacity:.55; }
</style>

<div class="oe-overlay" id="oeOverlay" onclick="if (event.target === this) closeOrderEdit()">
  <div class="oe-modal">
    <h3>✏️ تعديل الطلب</h3>
    <div class="oe-id" id="oeOrderId"></div>
    <div id="oeCompanyFields" style="display:none">
      <label>اسم الشركة (إجباري)</label>
      <input id="oeCompanyName" placeholder="🏢 اسم الشركة">
      <label>القسم</label>
      <input id="oeDepartment" placeholder="🏬 القسم (اختياري)">
    </div>
    <label>اسم العميل</label>
    <input id="oeCustomerName" placeholder="اسم العميل">
    <label>رقم الهاتف</label>
    <input id="oePhone" placeholder="رقم الهاتف">
    <label id="oeAddressLabel" style="display:none">عنوان التوصيل</label>
    <input id="oeAddress" placeholder="📍 عنوان التوصيل" style="display:none">
    <label>نوع الطلب</label>
    <div class="oe-type-row">
      <button id="oeBtnDinein" onclick="oeSetType('صالة')">🍽️ صالة</button>
      <button id="oeBtnDelivery" onclick="oeSetType('دليفري')">🛵 دليفري</button>
      <button id="oeBtnCompany" onclick="oeSetType('شركات')">🏢 شركات</button>
    </div>
    <label>ملاحظات</label>
    <textarea id="oeNotes" rows="2" placeholder="ملاحظات (اختياري)"></textarea>
    <label>أصناف الطلب</label>
    <div class="oe-items" id="oeItems"></div>
    <div class="oe-total-row"><span>الإجمالي الجديد</span><span id="oeTotal">0 ج.م</span></div>
    <label style="margin-top:10px">➕ إضافة صنف للطلب</label>
    <div class="oe-add-row">
      <select id="oeAddSelect"></select>
      <button onclick="oeAddItem()">إضافة</button>
    </div>
    <div class="oe-actions">
      <button class="oe-save" onclick="oeSaveOrder()">💾 حفظ التعديلات</button>
      <button class="oe-cancel-btn" onclick="oeCancelOrder()">✖ إلغاء الطلب</button>
      <button class="oe-delete-btn" onclick="oeDeleteOrder()">🗑 حذف</button>
    </div>
  </div>
</div>

<script>
(function () {
  if (window.__oeBooted) return;
  window.__oeBooted = true;

  var oeMenu = [];
  var oeCart = [];
  var oeType = 'صالة';
  var oeOrderId = '';
  var oeDeliveryFee = 0;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  async function oeLoadMenu() {
    if (oeMenu.length) return;
    try {
      var res = await api('menu');
      if (res.success) {
        oeMenu = res.items;
        var sel = document.getElementById('oeAddSelect');
        sel.innerHTML = oeMenu.map(function (i) {
          return '<option value="' + esc(i.id) + '">' + esc(i.name) + ' (' + esc(i.price) + ' ج.م)</option>';
        }).join('');
      }
    } catch (e) {}
    try {
      var st = await api('public_settings');
      if (st.success && st.settings) oeDeliveryFee = Number(st.settings.deliveryFee) || 0;
    } catch (e) {}
  }

  function oeSetType(t) {
    oeType = t;
    document.getElementById('oeBtnDinein').classList.toggle('active', t === 'صالة');
    document.getElementById('oeBtnDelivery').classList.toggle('active', t === 'دليفري');
    document.getElementById('oeBtnCompany').classList.toggle('active', t === 'شركات');
    document.getElementById('oeAddress').style.display = t === 'دليفري' ? 'block' : 'none';
    document.getElementById('oeAddressLabel').style.display = t === 'دليفري' ? 'block' : 'none';
    document.getElementById('oeCompanyFields').style.display = t === 'شركات' ? 'block' : 'none';
    oeRenderItems();
  }
  window.oeSetType = oeSetType;

  function oeRenderItems() {
    var box = document.getElementById('oeItems');
    if (!oeCart.length) { box.innerHTML = '<div style="font-size:12px;color:var(--muted)">لا توجد أصناف</div>'; }
    else {
      box.innerHTML = oeCart.map(function (c, idx) {
        return '<div class="oe-item-row"><span>' + esc(c.name) + '</span>'
          + '<span class="oe-qty">'
          + '<button onclick="oeChangeQty(' + idx + ', -1)">−</button>'
          + '<b>' + c.qty + '</b>'
          + '<button onclick="oeChangeQty(' + idx + ', 1)">+</button>'
          + '<span style="min-width:46px;text-align:left;font-weight:700">' + (c.qty * c.price) + '</span>'
          + '<button onclick="oeRemoveItem(' + idx + ')" title="حذف" style="background:#ffebe9;color:#ff3b30">✕</button>'
          + '</span></div>';
      }).join('');
    }
    var subtotal = oeCart.reduce(function (s, c) { return s + c.qty * c.price; }, 0);
    var total = subtotal + (oeType === 'دليفري' ? oeDeliveryFee : 0);
    document.getElementById('oeTotal').textContent = total + ' ج.م';
  }

  window.oeChangeQty = function (idx, delta) {
    oeCart[idx].qty += delta;
    if (oeCart[idx].qty <= 0) oeCart.splice(idx, 1);
    oeRenderItems();
  };
  window.oeRemoveItem = function (idx) { oeCart.splice(idx, 1); oeRenderItems(); };

  window.oeAddItem = function () {
    var id = document.getElementById('oeAddSelect').value;
    var item = oeMenu.find(function (m) { return String(m.id) === String(id); });
    if (!item) return;
    var exist = oeCart.find(function (c) { return String(c.id) === String(item.id); });
    if (exist) exist.qty++;
    else oeCart.push({ id: item.id, name: item.name, price: Number(item.price) || 0, qty: 1 });
    oeRenderItems();
  };

  window.openOrderEdit = async function (order) {
    oeOrderId = order.order_id;
    await oeLoadMenu();
    oeCart = (order.items || []).map(function (i) { return { id: i.id, name: i.name, price: Number(i.price) || 0, qty: Number(i.qty) || 1 }; });
    document.getElementById('oeOrderId').textContent = 'رقم الطلب: ' + order.order_id + (order.company_name ? ' — 🏢 ' + order.company_name : '');
    document.getElementById('oeCustomerName').value = order.customer_name || '';
    document.getElementById('oePhone').value = order.phone || '';
    document.getElementById('oeAddress').value = order.address || '';
    document.getElementById('oeNotes').value = order.notes || '';
    document.getElementById('oeCompanyName').value = order.company_name || '';
    document.getElementById('oeDepartment').value = order.department || '';
    oeSetType(order.order_type || 'صالة');
    document.getElementById('oeOverlay').classList.add('show');
  };

  window.closeOrderEdit = function () {
    document.getElementById('oeOverlay').classList.remove('show');
  };

  window.oeSaveOrder = async function () {
    if (!oeCart.length) { alert('الطلب بدون أصناف'); return; }
    if (oeType === 'شركات' && !document.getElementById('oeCompanyName').value.trim()) { alert('أدخل اسم الشركة'); return; }
    if (oeType === 'دليفري') {
      var ph = document.getElementById('oePhone').value.trim();
      var ad = document.getElementById('oeAddress').value.trim();
      if (!ph || !ad) { alert('الدليفري يتطلب الهاتف والعنوان'); return; }
    }
    try {
      var res = await api('update_order', {
        orderId: oeOrderId,
        items: oeCart.map(function (c) { return { id: c.id, qty: c.qty }; }),
        customerName: document.getElementById('oeCustomerName').value.trim(),
        phone: document.getElementById('oePhone').value.trim(),
        address: document.getElementById('oeAddress').value.trim(),
        notes: document.getElementById('oeNotes').value.trim(),
        orderType: oeType,
        companyName: document.getElementById('oeCompanyName').value.trim(),
        department: document.getElementById('oeDepartment').value.trim(),
      });
      if (res.success) {
        closeOrderEdit();
        alert('تم تعديل الطلب بنجاح');
        if (typeof window.orderActionsRefresh === 'function') window.orderActionsRefresh();
      } else alert(res.message || 'خطأ');
    } catch (e) { alert('خطأ في الاتصال'); }
  };

  window.oeCancelOrder = async function () {
    if (!confirm('إلغاء الطلب ' + oeOrderId + '؟ (لن يُحسب في المبيعات)')) return;
    try {
      var res = await api('cancel_order', { orderId: oeOrderId });
      if (res.success) {
        closeOrderEdit();
        alert('تم إلغاء الطلب');
        if (typeof window.orderActionsRefresh === 'function') window.orderActionsRefresh();
      } else alert(res.message || 'خطأ');
    } catch (e) { alert('خطأ في الاتصال'); }
  };

  window.oeDeleteOrder = async function () {
    if (!confirm('حذف الطلب ' + oeOrderId + ' نهائيًا؟ لا يمكن التراجع!')) return;
    try {
      var res = await api('delete_order', { orderId: oeOrderId });
      if (res.success) {
        closeOrderEdit();
        alert('تم حذف الطلب');
        if (typeof window.orderActionsRefresh === 'function') window.orderActionsRefresh();
      } else alert(res.message || 'خطأ');
    } catch (e) { alert('خطأ في الاتصال'); }
  };
})();
</script>
