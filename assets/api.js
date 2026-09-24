// وكيل الاتصال بالـ API من الواجهة
async function api(action, data) {
  const csrf = document.querySelector('meta[name="csrf"]') ? document.querySelector('meta[name="csrf"]').content : '';
  let res;
  try {
    res = await fetch('?api=' + action, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
      body: JSON.stringify(data || {})
    });
  } catch (e) {
    return { success: false, message: 'خطأ في الاتصال بالسيرفر' };
  }
  try {
    return await res.json();
  } catch (e) {
    return { success: false, message: 'رد غير صالح من السيرفر' };
  }
}
