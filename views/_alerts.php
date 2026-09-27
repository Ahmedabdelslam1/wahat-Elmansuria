<?php
// ===== إنذار تلقائي عالي الصوت + واتس بدون توكن (مشترك لكل الشاشات) =====
// يعمل على: الكاشير، المطبخ، لوحة المدير
$_waNum = preg_replace('/\D/', '', get_setting('ordersWhatsapp', '') ?: get_setting('whatsappNumber', ''));
?>
<script>
  window.WA_ORDERS_NUMBER = <?= json_encode($_waNum) ?>;

  // ================= الإنذار الصوتي =================
  window.__siren = {
    ctx: null,
    armed: false,
    enabled: (localStorage.getItem('wahat_siren') ?? 'on') === 'on',
    seen: new Set(),
    firstCheck: true,
    flashing: null
  };

  function __armSiren(label) {
    if (!window.__siren) return;
    __siren.armed = true;
    __updateSoundBtn(label);
  }

  function __tryInitCtx() {
    try {
      if (!__siren.ctx) __siren.ctx = new (window.AudioContext || window.webkitAudioContext)();
      if (__siren.ctx.state === 'suspended') __siren.ctx.resume().then(function () { if (__siren.ctx.state === 'running') __armSiren('🚨 الإنذار مُسلّط تلقائيًا'); }).catch(function () {});
      if (__siren.ctx.state === 'running') __armSiren('🚨 الإنذار مُسلّط تلقائيًا');
    } catch (e) {}
  }

  // تسليح تلقائي: المتصفح يسمح بالصوت بعد أول لمسة/ضغطة على الصفحة
  __tryInitCtx();
  ['pointerdown', 'touchstart', 'keydown'].forEach(function (evt) {
    document.addEventListener(evt, function () { __tryInitCtx(); }, { once: true, passive: true });
  });

  function __updateSoundBtn(label) {
    var b = document.getElementById('soundBtn');
    if (!b) return;
    b.classList.toggle('on', __siren.enabled);
    b.classList.toggle('off', !__siren.enabled);
    b.textContent = !__siren.enabled
      ? '🔕 الإنذار موقوف'
      : (label || (__siren.armed ? '🚨 الإنذار مُسلّط تلقائيًا' : '🚨 الإنذار: أول لمسة تُفعّل الصوت'));
  }
  __updateSoundBtn();

  window.__sirenToggle = function () {
    __siren.enabled = !__siren.enabled;
    localStorage.setItem('wahat_siren', __siren.enabled ? 'on' : 'off');
    __tryInitCtx();
    __updateSoundBtn();
  };
  window.__sirenToggleBtn = function () { __sirenToggle(); };

  // إنذار مرتفع: نغمتان متبادلتان × 10 دورات + اهتزاز + لافتة حمراء وميض
  window.__sirenAlert = function (text) {
    try { if (navigator.vibrate) navigator.vibrate([400, 150, 400, 150, 400, 150, 400]); } catch (e) {}
    __showAlertBanner(text || '🔔 طلب جديد!');
    if (!__siren.enabled || !__siren.ctx || __siren.ctx.state !== 'running') return;
    var ctx = __siren.ctx;
    var now = ctx.currentTime;
    var master = ctx.createGain();
    master.gain.value = 0.9;
    master.connect(ctx.destination);
    var cycle = 0.38;
    for (var i = 0; i < 20; i++) {
      var t0 = now + i * cycle;
      var hi = i % 2 === 0;
      var freq = hi ? 1020 : 740;
      var osc = ctx.createOscillator();
      var osc2 = ctx.createOscillator();
      var gain = ctx.createGain();
      osc.type = 'square';
      osc2.type = 'sawtooth';
      osc.frequency.value = freq;
      osc2.frequency.value = freq * 0.5;
      gain.gain.setValueAtTime(0.0001, t0);
      gain.gain.exponentialRampToValueAtTime(0.95, t0 + 0.015);
      gain.gain.setValueAtTime(0.95, t0 + cycle - 0.03);
      gain.gain.exponentialRampToValueAtTime(0.0001, t0 + cycle);
      osc.connect(gain); osc2.connect(gain);
      gain.connect(master);
      osc.start(t0); osc.stop(t0 + cycle + 0.02);
      osc2.start(t0); osc2.stop(t0 + cycle + 0.02);
    }
  };

  // كشف الطلبات الجديدة: يستدعيه كل صفحة من دورة التحديث الخاصة بها
  window.__sirenCheck = function (orders, onlyNew) {
    if (!Array.isArray(orders)) return;
    var found = null;
    for (var i = 0; i < orders.length; i++) {
      var o = orders[i];
      var id = o.order_id || o.id;
      if (!id || __siren.seen.has(id)) continue;
      __siren.seen.add(id);
      if (__siren.firstCheck) continue;
      if (onlyNew && o.status && o.status !== 'جديد') continue;
      if (!found) found = id;
    }
    __siren.firstCheck = false;
    if (found) __sirenAlert('🚨 طلب جديد: ' + found);
  };

  function __showAlertBanner(text) {
    var b = document.getElementById('sirenBanner');
    if (!b) {
      b = document.createElement('div');
      b.id = 'sirenBanner';
      b.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;text-align:center;font-weight:800;font-size:15px;padding:14px;color:#fff;background:linear-gradient(90deg,#c62828,#ff5252,#c62828);cursor:pointer;box-shadow:0 6px 22px rgba(255,0,0,.55);font-family:inherit';
      document.body.appendChild(b);
    }
    b.textContent = text + ' — اضغط للإخفاء';
    b.style.display = 'block';
    b.onclick = function () { b.style.display = 'none'; clearInterval(__siren.flashing); };
    clearInterval(__siren.flashing);
    var on = true;
    __siren.flashing = setInterval(function () {
      on = !on;
      b.style.opacity = on ? '1' : '0.45';
    }, 350);
    var oldTitle = document.title;
    var flash = setInterval(function () { document.title = document.title === oldTitle ? '🚨 ' + text : oldTitle; }, 700);
    setTimeout(function () { document.title = oldTitle; clearInterval(flash); clearInterval(__siren.flashing); b.style.display = 'none'; }, 15000);
  }

  // ================= واتساب بدون توكن (wa.me — نقرة واحدة) =================
  window.waLink = function (phone, text) {
    var digits = String(phone || '').replace(/\D/g, '');
    return 'https://wa.me/' + digits + '?text=' + encodeURIComponent(text);
  };

  window.waRestLink = function (o) {
    var lines = ['*🆕 طلب جديد — واحة المنصورة*', 'رقم الطلب: ' + (o.order_id || o.id || ''), 'النوع: ' + (o.order_type || '')];
    if (o.company_name) lines.push('الشركة: ' + o.company_name + (o.department ? ' — ' + o.department : ''));
    if (o.customer_name) lines.push('العميل: ' + o.customer_name);
    if (o.phone) lines.push('الهاتف: ' + o.phone);
    if (o.address) lines.push('العنوان: ' + o.address);
    if (o.notes) lines.push('ملاحظات: ' + o.notes);
    lines.push('—————');
    var total = 0;
    (o.items || []).forEach(function (i) {
      var line = (i.qty || 1) + '× ' + i.name + ' = ' + ((parseFloat(i.price) || 0) * (parseInt(i.qty) || 1)) + ' ج.م';
      lines.push('• ' + line);
      total += (parseFloat(i.price) || 0) * (parseInt(i.qty) || 1);
    });
    if (o.delivery_fee) { lines.push('التوصيل: ' + o.delivery_fee + ' ج.م'); total += parseFloat(o.delivery_fee) || 0; }
    lines.push('—————');
    lines.push('الإجمالي: ' + (o.total != null ? o.total : Math.round(total)) + ' ج.م');
    lines.push('الحالة: ' + (o.status || 'جديد'));
    return waLink(window.WA_ORDERS_NUMBER, lines.join('\n'));
  };

  window.waClientLink = function (o) {
    var text = 'مرحبًا 👋 من *واحة المنصورة*\nطلبك رقم ' + (o.order_id || o.id) + ' حالته الآن: *' + (o.status || '') + '*\n'
      + 'الإجمالي: ' + (o.total || '') + ' ج.م\nشكرًا لثقتكم بنا 🌟';
    return waLink(o.phone, text);
  };

  // زر الواتس في زر الصوت (لو موجود) + ربط تلقائي
  document.querySelectorAll('#soundBtn').forEach(function (b) {
    b.removeAttribute('onclick');
    b.addEventListener('click', function (e) { e.preventDefault(); __sirenToggle(); });
  });

  // إشعار نجاح الطلب من POS: واتس بدون توكن + فاتورة
  window.__posOrderDone = function (res) {
    var old = document.getElementById('posDoneOverlay');
    if (old) old.remove();
    var d = document.createElement('div');
    d.id = 'posDoneOverlay';
    d.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:99998;display:flex;align-items:center;justify-content:center;padding:16px';
    var links = '<div style="display:flex;flex-direction:column;gap:10px;margin-top:12px">';
    if (res.wa_restaurant) links += '<a href="' + res.wa_restaurant + '" target="_blank" rel="noopener" style="text-decoration:none;background:#128C7E;color:#fff;border:none;border-radius:12px;padding:12px 18px;font-size:14px;font-weight:800;text-align:center">📤 إرسال الطلب على واتس المطعم (نقرة واحدة بدون توكن)</a>';
    if (res.wa_customer) links += '<a href="' + res.wa_customer + '" target="_blank" rel="noopener" style="text-decoration:none;background:#e5f0ff;color:#007aff;border:none;border-radius:12px;padding:12px 18px;font-size:13px;font-weight:800;text-align:center">📲 إرسال تأكيد الطلب للعميل على واتس</a>';
    links += '<a href="?page=invoice&id=' + encodeURIComponent(res.orderId || '') + '" target="_blank" rel="noopener" style="text-decoration:none;background:#e7f8ec;color:#34c759;border:none;border-radius:12px;padding:12px 18px;font-size:13px;font-weight:800;text-align:center">🖨️ فتح فاتورة الطلب</a>'
      + '<button id="posDoneClose" style="background:#eee;color:#555;border:none;border-radius:12px;padding:10px 18px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit">إغلاق</button></div>';
    d.innerHTML = '<div style="background:#fff;border-radius:16px;padding:22px;max-width:420px;width:100%;text-align:center;font-family:inherit;box-shadow:0 18px 50px rgba(0,0,0,.35)">'
      + '<div style="font-size:34px">✅</div>'
      + '<div style="font-weight:800;font-size:15px;margin:8px 0 2px">تم إنشاء الطلب: ' + (res.orderId || '') + '</div>'
      + '<div style="color:#8b8b9a;font-size:12px">الإجمالي ' + (res.total || '') + ' ج.م</div>'
      + links + '</div>';
    document.body.appendChild(d);
    document.getElementById('posDoneClose').onclick = function () { d.remove(); };
  };
</script>
