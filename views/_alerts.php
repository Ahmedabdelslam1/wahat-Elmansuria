<?php
// ===== إنذار صوتي خلفي — شاشة المطبخ فقط =====
// يعمل تلقائيًا في الخلفية دون أي أيقونة أو لافتة ظاهرة على الشاشة.
// يُشغَّل صوتيًا فقط عند استلام طلب جديد (صالة/دليفري/شركات) في المطبخ.
?>
<script>
  window.__siren = { ctx: null, armed: false, seen: new Set(), firstCheck: true };

  function __tryInitCtx() {
    try {
      if (!__siren.ctx) __siren.ctx = new (window.AudioContext || window.webkitAudioContext)();
      if (__siren.ctx.state === 'suspended') __siren.ctx.resume().catch(function () {});
      if (__siren.ctx.state === 'running') __siren.armed = true;
    } catch (e) {}
  }

  // تسليح صامت: أول تفاعل من طاقم المطبخ مع الشاشة (لمسة/ضغطة) يفعّل الصوت تلقائيًا في الخلفية
  __tryInitCtx();
  var __lastArmTry = 0;
  function __armTick() {
    var now = Date.now();
    if (now - __lastArmTry < 1500) return;
    __lastArmTry = now;
    __tryInitCtx();
  }
  ['pointerdown', 'touchstart', 'touchend', 'click', 'keydown'].forEach(function (evt) {
    document.addEventListener(evt, __armTick, { passive: true });
  });
  document.addEventListener('visibilitychange', function () { if (!document.hidden) __armTick(); });
  window.addEventListener('focus', __armTick);

  // صفارة صوتية فقط (بدون لافتة/أيقونة/اهتزاز مرئي) — نغمتان متبادلتان
  window.__sirenAlert = function () {
    try { if (navigator.vibrate) navigator.vibrate([400, 150, 400, 150, 400, 150, 400]); } catch (e) {}
    if (!__siren.ctx || __siren.ctx.state !== 'running') { __tryInitCtx(); return; }
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

  // كشف الطلبات الجديدة: يستدعيه المطبخ من دورة التحديث الخاصة به
  window.__sirenCheck = function (orders, onlyNew) {
    if (!Array.isArray(orders)) return;
    var found = false;
    for (var i = 0; i < orders.length; i++) {
      var o = orders[i];
      var id = o.order_id || o.id;
      if (!id || __siren.seen.has(id)) continue;
      __siren.seen.add(id);
      if (__siren.firstCheck) continue; // لا تنبيه عن طلبات كانت موجودة قبل تحميل الشاشة
      if (onlyNew && o.status && o.status !== 'جديد') continue;
      found = true;
    }
    __siren.firstCheck = false;
    if (found) __sirenAlert();
  };
</script>
