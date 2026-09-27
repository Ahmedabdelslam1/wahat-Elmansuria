<?php
/**
 * =====================================================
 *  🔁 التحديث التلقائي - واحة المنصورية
 *
 *  زيارة هذا الرابط مرة واحدة تُحدّث الموقع كله تلقائيًا
 *  بأحدث نسخة من GitHub (بدون رفع ملفات يدويًا):
 *
 *  https://موقعك.com/update.php?key=cc8743966443dbc2e9afae52
 *
 *  ⚠️ الملفات المحمية التي لا تُمس أبدًا: config.php وملف قاعدة SQLite
 *  احذف هذا الملف لو لا تريد خاصية التحديث بنقرة واحدة.
 * =====================================================
 */

$UPDATE_KEY = 'cc8743966443dbc2e9afae52';
$REPO_ZIP   = 'https://codeload.github.com/Ahmedabdelslam1/wahat-Elmansuria/zip/refs/heads/main';
$PROTECTED  = ['config.php', 'data']; // ملفات لا تُستبدل أبدًا

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تحديث النظام</title><style>body{font-family:Tahoma,Arial,sans-serif;background:#f4f5fa;padding:24px;max-width:640px;margin:0 auto;direction:rtl}.box{background:#fff;border-radius:16px;padding:20px;border:2px solid #ff3b30;box-shadow:0 4px 20px rgba(0,0,0,.08)}h1{font-size:18px;margin:0 0 12px}p,li{font-size:13.5px;line-height:1.8;color:#333}.ok{color:#2e7d32;font-weight:bold}.err{color:#d32f2f;font-weight:bold}code{background:#eee;padding:2px 6px;border-radius:6px;direction:ltr;display:inline-block}</style></head><body><div class="box">';

$key = (string)($_GET['key'] ?? '');
if (!hash_equals($UPDATE_KEY, $key)) {
    http_response_code(403);
    echo '<h1>⛔ غير مصرح</h1><p>رابط التحديث غير صحيح.</p></div></body></html>';
    exit;
}
echo '<h1>🔄 تحديث نظام ' . htmlspecialchars('واحة المنصورية') . '</h1>';

// 1) تنزيل أحدث نسخة من GitHub
$zipData = false;
if (function_exists('curl_init')) {
    $ch = curl_init($REPO_ZIP);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $zipData = curl_exec($ch);
    curl_close($ch);
}
if ($zipData === false && ini_get('allow_url_fopen')) {
    $ctx = stream_context_create(['http' => ['timeout' => 60]]);
    $zipData = @file_get_contents($REPO_ZIP, false, $ctx);
}
if ($zipData === false || strlen($zipData) < 1000) {
    echo '<p class="err">❌ فشل تنزيل النسخة من GitHub. تأكد أن الاستضافة تسمح بالاتصال الخارجي.</p></div></body></html>';
    exit;
}

$tmpZip = sys_get_temp_dir() . '/wahat_update_' . time() . '.zip';
file_put_contents($tmpZip, $zipData);

// 2) فك الضغط في مجلد مؤقت
$zip = new ZipArchive();
if ($zip->open($tmpZip) !== true) {
    echo '<p class="err">❌ ملف التحديث تالف.</p></div></body></html>';
    @unlink($tmpZip);
    exit;
}
$tmpDir = sys_get_temp_dir() . '/wahat_update_' . time();
mkdir($tmpDir, 0755, true);
$zip->extractTo($tmpDir);
$zip->close();
@unlink($tmpZip);

// المجلد داخل الأرشيف: wahat-Elmansuria-main/
$src = $tmpDir . '/wahat-Elmansuria-main';
if (!is_dir($src)) {
    foreach (glob($tmpDir . '/*', GLOB_ONLYDIR) as $d) { $src = $d; break; }
}
if (!is_dir($src)) {
    echo '<p class="err">❌ بنية الملف المضغوط غير متوقعة.</p></div></body></html>';
    exit;
}

// 3) نسخ الملفات فوق الموجودة (مع حماية config.php)
$updated = 0;
$skipped = 0;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
$root = __DIR__;
foreach ($it as $file) {
    $rel = ltrim(str_replace($src, '', $file->getPathname()), '/\\');
    $top = explode('/', $rel)[0];
    if (in_array($top, $PROTECTED, true)) { $skipped++; continue; }
    $dest = $root . '/' . $rel;
    if ($file->isDir()) {
        if (!is_dir($dest)) @mkdir($dest, 0755, true);
        continue;
    }
    @mkdir(dirname($dest), 0755, true);
    if (@copy($file->getPathname(), $dest)) $updated++;
}

// 4) تنظيف
foreach (glob($tmpDir . '/wahat_update_*', GLOB_ONLYDIR) as $d) {
    $ri = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $f) { $f->isDir() ? @rmdir($f->getPathname()) : @unlink($f->getPathname()); }
    @rmdir($d);
}

echo '<p class="ok">✅ تم التحديث بنجاح!</p>';
echo '<p>📦 عدد الملفات المحدثة: <strong>' . (int)$updated . '</strong>' . ($skipped ? ' (تم حماية ' . (int)$skipped . ' عنصر: config.php وقاعدة البيانات)' : '') . '</p>';
echo '<p>👉 <a href="?page=home" style="font-weight:bold">الرجوع للصفحة الرئيسية</a></p>';
echo '<p style="font-size:12px;color:#888">لأي تحديث مستقبلي: فقط افتح نفس هذا الرابط مرة أخرى وسيتم تحديث كل شيء تلقائيًا من GitHub.</p>';
echo '</div></body></html>';
