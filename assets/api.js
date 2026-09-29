// وكيل الاتصال بالـ API من الواجهة — موحّد وآمن
async function api(action, data) {
  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
  const controller = new AbortController();
  const timer = setTimeout(() => controller.abort(), 20000);
  let res;
  try {
    res = await fetch('?api=' + encodeURIComponent(action), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': csrf,
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin',
      cache: 'no-store',
      signal: controller.signal,
      body: JSON.stringify(data || {})
    });
  } catch (e) {
    clearTimeout(timer);
    return { success: false, message: e?.name === 'AbortError' ? 'انتهت مهلة الاتصال بالسيرفر' : 'خطأ في الاتصال بالسيرفر' };
  }
  clearTimeout(timer);
  let payload;
  try {
    payload = await res.json();
  } catch (e) {
    return { success: false, message: 'رد غير صالح من السيرفر', http_status: res.status };
  }
  if (!res.ok && payload.success !== true) {
    payload.success = false;
    payload.http_status = res.status;
  }
  return payload;
}