<?php
/**
 * =====================================================
 *  واحة المنصورية - نظام إدارة المطعم (نسخة PHP)
 *  PHP + SQLite - بدون أي إعداد خارجي
 *  الأدوار: admin | cashier | customer
 * =====================================================
 */

date_default_timezone_set('Africa/Cairo');
mb_internal_encoding('UTF-8');

define('APP_NAME', 'واحة المنصورية');

// ===================== DB =====================
function db() {
    static $db = null;
    if ($db !== null) return $db;

    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) mkdir($dir, 0775, true);
    $path = $dir . '/wahat.sqlite';

    // حماية: منع تحميل ملف قاعدة البيانات مباشرة إن مر عبر الويب
    $db = new SQLite3($path);
    $db->busyTimeout(5000);
    $db->exec('PRAGMA journal_mode = WAL');
    init_db($db);
    return $db;
}

function init_db(SQLite3 $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            name TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'customer',
            active INTEGER NOT NULL DEFAULT 1,
            phone TEXT DEFAULT '',
            email TEXT DEFAULT ''
        );
        CREATE TABLE IF NOT EXISTS menu (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT NOT NULL,
            price REAL NOT NULL DEFAULT 0,
            descr TEXT DEFAULT '',
            active INTEGER NOT NULL DEFAULT 1,
            image TEXT DEFAULT ''
        );
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_id TEXT UNIQUE NOT NULL,
            created_at TEXT NOT NULL,
            customer_name TEXT NOT NULL DEFAULT 'ضيف',
            phone TEXT DEFAULT '',
            items_json TEXT NOT NULL DEFAULT '[]',
            total REAL NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'جديد',
            notes TEXT DEFAULT '',
            created_by TEXT DEFAULT 'guest'
        );
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT DEFAULT ''
        );
        CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);
        CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
    ");

    // بيانات أولية (مرة واحدة)
    $count = (int)$db->querySingle('SELECT COUNT(*) FROM users');
    if ($count === 0) {
        $seedUsers = [
            ['admin', 'admin123', 'المدير', 'admin'],
            ['cashier', 'cash123', 'الكاشير', 'cashier'],
            ['customer', '1234', 'عميل', 'customer'],
        ];
        $stmt = $db->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
        foreach ($seedUsers as $u) {
            $stmt->bindValue(1, $u[0], SQLITE3_TEXT);
            $stmt->bindValue(2, password_hash($u[1], PASSWORD_DEFAULT), SQLITE3_TEXT);
            $stmt->bindValue(3, $u[2], SQLITE3_TEXT);
            $stmt->bindValue(4, $u[3], SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    $count = (int)$db->querySingle('SELECT COUNT(*) FROM menu');
    if ($count === 0) {
        $seedMenu = [
            ['فرخة مندي + أرز', 'دجاج مندي', 400, 'دجاج طازج مع أرز بسمتي'],
            ['نصف فرخة مندي + أرز', 'دجاج مندي', 200, ''],
            ['ربع فرخة مندي + أرز', 'دجاج مندي', 100, ''],
            ['نصف فرخة كبسة + أرز', 'دجاج مندي', 200, ''],
            ['نفر لحم مندي أو كبسة', 'لحم مندي', 450, ''],
            ['ك كفتة + سلطات + عيش', 'مشويات', 440, ''],
            ['كباب + سلطات + عيش', 'مشويات', 1000, ''],
            ['طاجن بامية باللحمة + أرز', 'طواجن', 250, ''],
            ['مكرونة مبكبكة لحم', 'مطبخ', 250, ''],
            ['ساندوتش كفتة', 'ساندوتشات', 40, ''],
            ['نصف فرخة + نصف كفتة + أرز (السفرة)', 'صواني', 450, ''],
            ['فرخة + كيلو كفتة + صينية أرز', 'صواني', 850, ''],
        ];
        $stmt = $db->prepare('INSERT INTO menu (name, category, price, descr, active) VALUES (?, ?, ?, ?, 1)');
        foreach ($seedMenu as $m) {
            $stmt->bindValue(1, $m[0], SQLITE3_TEXT);
            $stmt->bindValue(2, $m[1], SQLITE3_TEXT);
            $stmt->bindValue(3, $m[2], SQLITE3_FLOAT);
            $stmt->bindValue(4, $m[3], SQLITE3_TEXT);
            $stmt->execute();
        }
    }

    $defaults = [
        'restaurantName' => 'واحة المنصورية',
        'phone' => '01153431728',
        'address' => 'أول مدخل المنصورية بجوار مسجد عبدالرحيم زيدان',
        'adminEmail' => '',
        'telegramBotToken' => '',
        'telegramChatId' => '',
        'whatsappNumber' => '201153431728',
    ];
    foreach ($defaults as $k => $v) {
        $exists = $db->querySingle('SELECT COUNT(*) FROM settings WHERE key = ' . "'" . $db->escapeString($k) . "'");
        if (!$exists) {
            $stmt = $db->prepare('INSERT INTO settings (key, value) VALUES (?, ?)');
            $stmt->bindValue(1, $k, SQLITE3_TEXT);
            $stmt->bindValue(2, $v, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
}

// ===================== HELPERS =====================
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function json_input() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function json_out($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_setting($key, $default = '') {
    $db = db();
    $stmt = $db->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->bindValue(1, $key, SQLITE3_TEXT);
    $res = $stmt->execute();
    $row = $res->fetchArray(SQLITE3_ASSOC);
    return ($row && $row['value'] !== '') ? $row['value'] : $default;
}

// ===================== SESSION / AUTH =====================
function boot_session() {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        // 'secure' => true, // فعّلها عند استخدام HTTPS
    ]);
    session_start();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

function current_user() {
    if (empty($_SESSION['uid'])) return null;
    $db = db();
    $stmt = $db->prepare('SELECT id, username, name, role, active FROM users WHERE id = ?');
    $stmt->bindValue(1, (int)$_SESSION['uid'], SQLITE3_INTEGER);
    $row = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$row || !(int)$row['active']) return null;
    return $row;
}

function require_role(array $roles) {
    $u = current_user();
    if (!$u || !in_array($u['role'], $roles, true)) {
        json_out(['success' => false, 'message' => 'غير مصرح']);
    }
    return $u;
}

// ===================== API ACTIONS =====================
function api_login($data) {
    $db = db();
    $stmt = $db->prepare('SELECT * FROM users WHERE username = ? AND active = 1');
    $stmt->bindValue(1, trim((string)($data['username'] ?? '')), SQLITE3_TEXT);
    $u = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$u || !password_verify((string)($data['password'] ?? ''), $u['password_hash'])) {
        json_out(['success' => false, 'message' => 'اسم المستخدم أو كلمة المرور غير صحيحة']);
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    json_out([
        'success' => true,
        'user' => ['name' => $u['name'], 'role' => $u['role'], 'username' => $u['username']],
        'redirect' => $u['role'] === 'admin' ? 'admin' : ($u['role'] === 'cashier' ? 'cashier' : 'menu'),
    ]);
}

function api_logout() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    json_out(['success' => true]);
}

function api_current_user() {
    $u = current_user();
    json_out(['success' => true, 'user' => $u ? ['name' => $u['name'], 'role' => $u['role'], 'username' => $u['username']] : null]);
}

function api_menu() {
    $db = db();
    $res = $db->query('SELECT id, name, category, price, descr FROM menu WHERE active = 1 ORDER BY id');
    $items = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['price'] = (float)$row['price'];
        $row['desc'] = $row['descr'];
        unset($row['descr']);
        $items[] = $row;
    }
    json_out(['success' => true, 'items' => $items]);
}

function api_place_order($data) {
    $db = db();
    $inputItems = is_array($data['items'] ?? null) ? $data['items'] : [];
    if (!$inputItems) json_out(['success' => false, 'message' => 'السلة فارغة']);

    // إعادة حساب الإجمالي من أسعار المنيو (لا نثق بسعر العميل)
    $items = [];
    $total = 0.0;
    foreach ($inputItems as $it) {
        $id = (int)($it['id'] ?? 0);
        $qty = max(1, (int)($it['qty'] ?? 1));
        $stmt = $db->prepare('SELECT name, price FROM menu WHERE id = ? AND active = 1');
        $stmt->bindValue(1, $id, SQLITE3_INTEGER);
        $m = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!$m) continue;
        $price = (float)$m['price'];
        $total += $price * $qty;
        $items[] = ['id' => $id, 'name' => $m['name'], 'price' => $price, 'qty' => $qty];
    }
    if (!$items) json_out(['success' => false, 'message' => 'لا توجد أصناف صالحة في السلة']);

    $user = current_user();
    $orderId = 'ORD-' . date('Ymd-His');
    $customerName = trim((string)($data['customerName'] ?? '')) ?: ($user ? $user['name'] : 'ضيف');
    $phone = trim((string)($data['phone'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));

    $stmt = $db->prepare('INSERT INTO orders (order_id, created_at, customer_name, phone, items_json, total, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bindValue(1, $orderId, SQLITE3_TEXT);
    $stmt->bindValue(2, date('Y-m-d H:i:s'), SQLITE3_TEXT);
    $stmt->bindValue(3, $customerName, SQLITE3_TEXT);
    $stmt->bindValue(4, $phone, SQLITE3_TEXT);
    $stmt->bindValue(5, json_encode($items, JSON_UNESCAPED_UNICODE), SQLITE3_TEXT);
    $stmt->bindValue(6, $total, SQLITE3_FLOAT);
    $stmt->bindValue(7, 'جديد', SQLITE3_TEXT);
    $stmt->bindValue(8, $notes, SQLITE3_TEXT);
    $stmt->bindValue(9, $user ? $user['username'] : 'guest', SQLITE3_TEXT);
    $stmt->execute();

    send_order_notification($orderId, $customerName, $phone, $notes, $items, $total);

    json_out(['success' => true, 'orderId' => $orderId, 'total' => $total, 'message' => 'تم استلام طلبك بنجاح! رقم الطلب: ' . $orderId]);
}

function api_get_orders($data) {
    require_role(['admin', 'cashier']);
    $db = db();
    $filter = trim((string)($data['status'] ?? ''));
    $sql = 'SELECT * FROM orders';
    $params = [];
    if ($filter && $filter !== 'all') {
        $sql .= ' WHERE status = ?';
        $params[] = $filter;
    }
    $sql .= ' ORDER BY id DESC LIMIT 500';
    $stmt = $db->prepare($sql);
    foreach ($params as $i => $v) $stmt->bindValue($i + 1, $v, SQLITE3_TEXT);
    $res = $stmt->execute();
    $orders = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $row['items'] = json_decode($row['items_json'], true) ?: [];
        $row['total'] = (float)$row['total'];
        unset($row['items_json']);
        $orders[] = $row;
    }
    json_out(['success' => true, 'orders' => $orders]);
}

function api_update_status($data) {
    require_role(['admin', 'cashier']);
    $db = db();
    $orderId = (string)($data['orderId'] ?? '');
    $status = (string)($data['status'] ?? '');
    $allowed = ['جديد', 'قيد التحضير', 'جاهز', 'تم التسليم'];
    if (!in_array($status, $allowed, true)) json_out(['success' => false, 'message' => 'حالة غير صحيحة']);
    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
    $stmt->bindValue(1, $status, SQLITE3_TEXT);
    $stmt->bindValue(2, $orderId, SQLITE3_TEXT);
    $stmt->execute();
    if ($db->changes()) json_out(['success' => true, 'message' => 'تم تحديث الحالة']);
    json_out(['success' => false, 'message' => 'الطلب غير موجود']);
}

function api_save_item($data) {
    require_role(['admin']);
    $db = db();
    $name = trim((string)($data['name'] ?? ''));
    $category = trim((string)($data['category'] ?? ''));
    $price = (float)($data['price'] ?? 0);
    $descr = trim((string)($data['desc'] ?? ''));
    if ($name === '' || $price <= 0) json_out(['success' => false, 'message' => 'أدخل الاسم والسعر']);

    $id = (int)($data['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare('UPDATE menu SET name = ?, category = ?, price = ?, descr = ? WHERE id = ?');
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $category, SQLITE3_TEXT);
        $stmt->bindValue(3, $price, SQLITE3_FLOAT);
        $stmt->bindValue(4, $descr, SQLITE3_TEXT);
        $stmt->bindValue(5, $id, SQLITE3_INTEGER);
        $stmt->execute();
        if ($db->changes()) json_out(['success' => true, 'message' => 'تم التحديث']);
        json_out(['success' => false, 'message' => 'الصنف غير موجود']);
    }
    $stmt = $db->prepare('INSERT INTO menu (name, category, price, descr, active) VALUES (?, ?, ?, ?, 1)');
    $stmt->bindValue(1, $name, SQLITE3_TEXT);
    $stmt->bindValue(2, $category, SQLITE3_TEXT);
    $stmt->bindValue(3, $price, SQLITE3_FLOAT);
    $stmt->bindValue(4, $descr, SQLITE3_TEXT);
    $stmt->execute();
    json_out(['success' => true, 'message' => 'تم الإضافة', 'id' => (int)$db->lastInsertRowID()]);
}

function api_toggle_item($data) {
    require_role(['admin']);
    $db = db();
    $stmt = $db->prepare('UPDATE menu SET active = ? WHERE id = ?');
    $stmt->bindValue(1, !empty($data['active']) ? 1 : 0, SQLITE3_INTEGER);
    $stmt->bindValue(2, (int)($data['id'] ?? 0), SQLITE3_INTEGER);
    $stmt->execute();
    json_out(['success' => true, 'message' => !empty($data['active']) ? 'تم تفعيل الصنف' : 'تم إخفاء الصنف']);
}

function api_add_user($data) {
    require_role(['admin']);
    $db = db();
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $name = trim((string)($data['name'] ?? ''));
    $role = (string)($data['role'] ?? 'customer');
    if (!in_array($role, ['admin', 'cashier', 'customer'], true)) $role = 'customer';
    if ($username === '' || $password === '' || $name === '') json_out(['success' => false, 'message' => 'أكمل جميع الحقول']);

    $exists = $db->querySingle('SELECT COUNT(*) FROM users WHERE username = ' . "'" . $db->escapeString($username) . "'");
    if ($exists) json_out(['success' => false, 'message' => 'اسم المستخدم موجود بالفعل']);

    $stmt = $db->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
    $stmt->bindValue(1, $username, SQLITE3_TEXT);
    $stmt->bindValue(2, password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);
    $stmt->bindValue(3, $name, SQLITE3_TEXT);
    $stmt->bindValue(4, $role, SQLITE3_TEXT);
    $stmt->execute();
    json_out(['success' => true, 'message' => 'تم إضافة المستخدم']);
}

function api_report($data) {
    require_role(['admin']);
    $db = db();
    $date = (string)($data['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

    $stmt = $db->prepare('SELECT items_json, total, status FROM orders WHERE substr(created_at, 1, 10) = ?');
    $stmt->bindValue(1, $date, SQLITE3_TEXT);
    $res = $stmt->execute();

    $totalOrders = 0;
    $totalSales = 0.0;
    $byStatus = [];
    $itemsCount = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $totalOrders++;
        $totalSales += (float)$row['total'];
        $byStatus[$row['status']] = ($byStatus[$row['status']] ?? 0) + 1;
        foreach ((json_decode($row['items_json'], true) ?: []) as $it) {
            $itemsCount[$it['name']] = ($itemsCount[$it['name']] ?? 0) + max(1, (int)($it['qty'] ?? 1));
        }
    }
    arsort($itemsCount);
    $top = [];
    $i = 0;
    foreach ($itemsCount as $n => $q) {
        if ($i++ >= 10) break;
        $top[] = ['name' => $n, 'qty' => $q];
    }
    json_out(['success' => true, 'report' => [
        'date' => $date,
        'totalOrders' => $totalOrders,
        'totalSales' => $totalSales,
        'byStatus' => $byStatus,
        'topItems' => $top,
    ]]);
}

function api_get_invoice($data) {
    $db = db();
    $stmt = $db->prepare('SELECT * FROM orders WHERE order_id = ?');
    $stmt->bindValue(1, (string)($data['orderId'] ?? ''), SQLITE3_TEXT);
    $order = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if (!$order) json_out(['success' => false, 'message' => 'الطلب غير موجود']);
    $order['items'] = json_decode($order['items_json'], true) ?: [];
    unset($order['items_json']);
    json_out(['success' => true, 'order' => $order, 'restaurant' => [
        'name' => get_setting('restaurantName', 'واحة المنصورية'),
        'phone' => get_setting('phone', '01153431728'),
        'address' => get_setting('address', ''),
    ]]);
}

// ===================== NOTIFICATIONS =====================
function send_order_notification($orderId, $customerName, $phone, $notes, $items, $total) {
    $lines = [];
    foreach ($items as $i) $lines[] = "• {$i['name']} × {$i['qty']}";
    $itemsTxt = implode("\n", $lines);

    // تليجرام (لو مُعد)
    $token = get_setting('telegramBotToken');
    $chatId = get_setting('telegramChatId');
    if ($token && $chatId && function_exists('curl_init')) {
        $msg = "🔔 <b>طلب جديد - " . APP_NAME . "</b>\n\n" .
               "📋 رقم الطلب: <code>{$orderId}</code>\n" .
               "👤 العميل: {$customerName}\n" .
               "📞 الهاتف: " . ($phone ?: '-') . "\n" .
               "💰 الإجمالي: <b>{$total} جنيه</b>\n\n" .
               "الأصناف:\n" . $itemsTxt . "\n\n📝 " . ($notes ?: '');
        $ch = curl_init('https://api.telegram.org/bot' . $token . '/sendMessage');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['chat_id' => $chatId, 'text' => $msg, 'parse_mode' => 'HTML']),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    // واتساب: رابط جاهز (يحتاج فتحه يدويًا)
    $wa = get_setting('whatsappNumber');
    if ($wa) {
        // نحتفظ بالرابط في السجل إن أردت استخدامه لاحقًا
    }
}
