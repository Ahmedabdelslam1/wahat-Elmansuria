<?php
/**
 * واحة المنصورية - نظام إدارة المطعم (PHP)
 * نقطة الدخول: التوجيه + حماية الصفحات + استقبال طلبات API
 */
require_once __DIR__ . '/lib.php';
boot_session();

// ملف ثابت: مكتبة الاتصال بالـ API
if (($_GET['asset'] ?? '') === 'api.js') {
    header('Content-Type: application/javascript; charset=utf-8');
    readfile(__DIR__ . '/assets/api.js');
    exit;
}

// ===================== API =====================
if (isset($_GET['api'])) {
    $action = $_GET['api'];
    $data = json_input();

    // حماية CSRF لكل الطلبات المغيرة للبيانات (ما عدا الدخول)
    if (in_array($action, ['logout', 'place_order', 'update_status', 'save_item', 'toggle_item', 'add_user', 'report'], true)) {
        $hdr = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf'], (string)$hdr)) {
            json_out(['success' => false, 'message' => 'انتهت الجلسة، أعد تحميل الصفحة']);
        }
    }

    switch ($action) {
        case 'login':          api_login($data); break;
        case 'logout':         api_logout(); break;
        case 'current_user':   api_current_user(); break;
        case 'menu':           api_menu(); break;
        case 'place_order':    api_place_order($data); break;
        case 'get_orders':     api_get_orders($data); break;
        case 'update_status':  api_update_status($data); break;
        case 'save_item':      api_save_item($data); break;
        case 'toggle_item':    api_toggle_item($data); break;
        case 'add_user':       api_add_user($data); break;
        case 'report':         api_report($data); break;
        case 'get_invoice':    api_get_invoice($data); break;
        default:               json_out(['success' => false, 'message' => 'طلب غير معروف']);
    }
}

// ===================== PAGES =====================
$page = strtolower(trim((string)($_GET['page'] ?? 'login')));
$user = current_user();

$publicPages   = ['login', 'menu', 'cart', 'invoice'];
$staffPages    = ['cashier'];           // كاشير + مدير
$adminPages    = ['admin'];             // مدير فقط

if (!in_array($page, array_merge($publicPages, $staffPages, $adminPages), true)) {
    $page = 'login';
}
if (in_array($page, $staffPages, true) && (!$user || !in_array($user['role'], ['cashier', 'admin'], true))) {
    http_response_code(403);
    $page = 'denied';
}
if (in_array($page, $adminPages, true) && (!$user || $user['role'] !== 'admin')) {
    http_response_code(403);
    $page = 'denied';
}

$BASE = '?page=';
require __DIR__ . '/views/' . $page . '.php';
