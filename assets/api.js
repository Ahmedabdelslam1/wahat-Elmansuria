// وكيل الاتصال بالـ API من الواجهة
function __apiCsrf() {
  var m = document.querySelector('meta[name="csrf"]');
  return m ? m.content : '';
}

// تجديد رمز الحماية (CSRF) بصمت دون إعادة تحميل الصفحة — يقرأ الصفحة الحالية ويسحب الرمز الجديد
async function __refreshCsrf() {
  try {
    var r = await fetch(window.location.pathname + window.location.search, { credentials: 'same-origin', cache: 'no-store' });
    var html = await r.text();
    var m = html.match(/name="csrf" content="([^"]+)"/);
    if (m && m[1]) {
      var meta = document.querySelector('meta[name="csrf"]');
      if (meta) meta.content = m[1];
      return m[1];
    }
  } catch (e) {}
  return null;
}

async function api(action, data) {
  let res, json;
  try {
    res = await fetch('?api=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': __apiCsrf() },
      body: JSON.stringify(data || {})
    });
  } catch (e) {
    return { success: false, message: 'خطأ في الاتصال بالسيرفر' };
  }
  try {
    json = await res.json();
  } catch (e) {
    return { success: false, message: 'رد غير صالح من السيرفر' };
  }

  // "انتهت الجلسة": نجدد رمز الحماية بصمت من الصفحة الحالية ونعيد الطلب مرة واحدة فقط تلقائيًا
  // (يمنع فشل عمليات مثل الحذف/الإلغاء بسبب انقطاع جلسة مؤقت على السيرفر، دون أن يشعر المستخدم)
  if (json && json.success === false && String(json.message || '').indexOf('انتهت الجلسة') !== -1 && !window.__csrfRetrying) {
    window.__csrfRetrying = true;
    const fresh = await __refreshCsrf();
    window.__csrfRetrying = false;
    if (fresh) {
      try {
        const res2 = await fetch('?api=' + action, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': fresh },
          body: JSON.stringify(data || {})
        });
        return await res2.json();
      } catch (e) {}
    }
  }
  return json;
}
