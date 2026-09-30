<?php
// ===== واتساب بدون توكن (wa.me — نقرة واحدة) — مشترك بين الكاشير ولوحة المدير =====
$_waNum = preg_replace('/\D/', '', get_setting('ordersWhatsapp', '') ?: get_setting('whatsappNumber', ''));
?>
<script>
  window.WA_ORDERS_NUMBER = <?= json_encode($_waNum) ?>;

  // توحيد الأرقام المصرية: +2010.. / 002010.. / 010.. / 10.. → 2010..
  window.waDigits = function (phone) {
    var d = String(phone || '').replace(/\D/g, '');
    if (!d) return '';
    if (d.indexOf('00') === 0) d = d.slice(2);
    if (d.length === 10 && d[0] === '1') d = '2' + d;
    if (d.length === 11 && d[0] === '0') d = '2' + d;
    return d;
  };
  window.waLink = function (phone, text) {
    var digits = waDigits(phone);
    if (!digits) return '';
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
    links += '<a href="?page=invoice&id=' + encodeURIComponent(res.orderId || '') + '" target="_blank" rel="noopener" style="text-decoration:none;background:#e7f8ec;color:#34c759;border:none;border-radius:12px;padding:12px 18px;font-size:13px;font-weight:800;text-align:center">🖨️ فتح الفاتورة</a>';
    links += '<button onclick="document.getElementById(\'posDoneOverlay\').remove()" style="background:#eee;color:#555;border:none;border-radius:12px;padding:10px 18px;font-size:13px;font-weight:800;cursor:pointer;font-family:inherit">إغلاق</button>';
    links += '</div>';
    d.innerHTML = '<div style="background:#fff;border-radius:16px;padding:22px;max-width:420px;width:100%;text-align:center;font-family:inherit;box-shadow:0 18px 50px rgba(0,0,0,.35)">'
      + '<div style="font-size:36px">✅</div>'
      + '<div style="font-weight:800;font-size:15px;margin:8px 0 4px">تم تسجيل الطلب رقم ' + (res.orderId || '') + '</div>'
      + '<div style="color:#8b8b9a;font-size:12.5px;margin-bottom:14px">الإجمالي ' + (res.total || '') + ' ج.م</div>'
      + links
      + '</div>';
    document.body.appendChild(d);
  };
</script>
