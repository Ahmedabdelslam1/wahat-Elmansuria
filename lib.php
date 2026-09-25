<?php
/**
 * =====================================================
 *  واحة المنصورية - نظام إدارة المطعم (نسخة PHP)
 *  PDO: يدعم MySQL (استضافة) و SQLite (محلي) - بدون إعداد خارجي
 *  الأدوار: admin | cashier | customer
 * =====================================================
 */

date_default_timezone_set('Africa/Cairo');
mb_internal_encoding('UTF-8');

define('APP_NAME', 'واحة المنصورية');
require_once __DIR__ . '/config.php';

// ===================== DB (PDO) =====================
function db() {
    static $db = null;
    if ($db !== null) return $db;

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if (DB_DRIVER === 'mysql') {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS, $opts
        );
        $db->exec("SET NAMES utf8mb4");
    } else {
        $dir = __DIR__ . '/data';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $db = new PDO('sqlite:' . $dir . '/wahat.sqlite', null, null, $opts);
        $db->exec('PRAGMA busy_timeout = 5000');
        $db->exec('PRAGMA journal_mode = WAL');
    }

    init_db($db);
    return $db;
}

/** استعلام ترجع قيمة واحدة (العمود الأول من أول صف) */
function db_scalar(PDO $db, string $sql, array $params = []) {
    $st = $db->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
}

/** عدد الصفوف المتأثرة بآخر أمر */
function db_changes(PDO $db) {
    return (int)db_scalar($db, (DB_DRIVER === 'mysql')
        ? 'SELECT ROW_COUNT()'
        : 'SELECT changes()');
}

function init_db(PDO $db) {
    if (DB_DRIVER === 'mysql') {
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                name VARCHAR(150) NOT NULL,
                role VARCHAR(20) NOT NULL DEFAULT 'customer',
                active TINYINT UNSIGNED NOT NULL DEFAULT 1,
                phone VARCHAR(30) DEFAULT '',
                email VARCHAR(150) DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS menu (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                category VARCHAR(100) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                descr VARCHAR(500) DEFAULT '',
                active TINYINT UNSIGNED NOT NULL DEFAULT 1,
                image VARCHAR(300) DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS orders (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                order_id VARCHAR(50) UNIQUE NOT NULL,
                created_at VARCHAR(19) NOT NULL,
                customer_name VARCHAR(150) NOT NULL DEFAULT 'ضيف',
                phone VARCHAR(30) DEFAULT '',
                items_json MEDIUMTEXT NOT NULL,
                total DECIMAL(12,2) NOT NULL DEFAULT 0,
                status VARCHAR(30) NOT NULL DEFAULT 'جديد',
                notes VARCHAR(1000) DEFAULT '',
                created_by VARCHAR(100) DEFAULT 'guest',
                INDEX idx_orders_created (created_at),
                INDEX idx_orders_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS settings (
                `key` VARCHAR(100) PRIMARY KEY,
                `value` VARCHAR(1000) DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } else {
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
    }

    // بيانات أولية (مرة واحدة)
    if ((int)db_scalar($db, 'SELECT COUNT(*) FROM users') === 0) {
        $seedUsers = [
            ['admin', 'admin123', 'المدير', 'admin'],
            ['cashier', 'cash123', 'الكاشير', 'cashier'],
            ['customer', '1234', 'عميل', 'customer'],
        ];
        $st = $db->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
        foreach ($seedUsers as $u) {
            $st->execute([$u[0], password_hash($u[1], PASSWORD_DEFAULT), $u[2], $u[3]]);
        }
    }

    if ((int)db_scalar($db, 'SELECT COUNT(*) FROM menu') === 0) {
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
        $st = $db->prepare('INSERT INTO menu (name, category, price, descr, active) VALUES (?, ?, ?, ?, 1)');
        foreach ($seedMenu as $m) $st->execute($m);
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
    $st = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)');
    foreach ($defaults as $k => $v) {
        $exists = db_scalar($db, 'SELECT COUNT(*) FROM settings WHERE `key` = ?', [$k]);
        if (!$exists) $st->execute([$k, $v]);
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
    $row = $db->prepare('SELECT `value` FROM settings WHERE `key` = ?');
    $row->execute([$key]);
    $v = $row->fetchColumn();
    return ($v !== false && $v !== '') ? (string)$v : $default;
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
    $st = $db->prepare('SELECT id, username, name, role, active FROM users WHERE id = ?');
    $st->execute([(int)$_SESSION['uid']]);
    $row = $st->fetch();
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
    $st = $db->prepare('SELECT * FROM users WHERE username = ? AND active = 1');
    $st->execute([trim((string)($data['username'] ?? ''))]);
    $u = $st->fetch();
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
    while ($row = $res->fetch()) {
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
    $st = $db->prepare('SELECT name, price FROM menu WHERE id = ? AND active = 1');
    foreach ($inputItems as $it) {
        $id = (int)($it['id'] ?? 0);
        $qty = max(1, (int)($it['qty'] ?? 1));
        $st->execute([$id]);
        $m = $st->fetch();
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
    $stmt->execute([
        $orderId,
        date('Y-m-d H:i:s'),
        $customerName,
        $phone,
        json_encode($items, JSON_UNESCAPED_UNICODE),
        $total,
        'جديد',
        $notes,
        $user ? $user['username'] : 'guest',
    ]);

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
    $stmt->execute($params);
    $orders = [];
    while ($row = $stmt->fetch()) {
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
    $stmt->execute([$status, $orderId]);
    if ((int)$stmt->rowCount()) json_out(['success' => true, 'message' => 'تم تحديث الحالة']);
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
        $stmt->execute([$name, $category, $price, $descr, $id]);
        if ((int)$stmt->rowCount()) json_out(['success' => true, 'message' => 'تم التحديث']);
        json_out(['success' => false, 'message' => 'الصنف غير موجود']);
    }
    $stmt = $db->prepare('INSERT INTO menu (name, category, price, descr, active) VALUES (?, ?, ?, ?, 1)');
    $stmt->execute([$name, $category, $price, $descr]);
    json_out(['success' => true, 'message' => 'تم الإضافة', 'id' => (int)$db->lastInsertId()]);
}

function api_toggle_item($data) {
    require_role(['admin']);
    $db = db();
    $stmt = $db->prepare('UPDATE menu SET active = ? WHERE id = ?');
    $stmt->execute([!empty($data['active']) ? 1 : 0, (int)($data['id'] ?? 0)]);
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

    $exists = db_scalar($db, 'SELECT COUNT(*) FROM users WHERE username = ?', [$username]);
    if ($exists) json_out(['success' => false, 'message' => 'اسم المستخدم موجود بالفعل']);

    $stmt = $db->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $name, $role]);
    json_out(['success' => true, 'message' => 'تم إضافة المستخدم']);
}

function api_report($data) {
    require_role(['admin']);
    $db = db();
    $date = (string)($data['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

    $stmt = $db->prepare('SELECT items_json, total, status FROM orders WHERE SUBSTR(created_at, 1, 10) = ?');
    $stmt->execute([$date]);

    $totalOrders = 0;
    $totalSales = 0.0;
    $byStatus = [];
    $itemsCount = [];
    while ($row = $stmt->fetch()) {
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
    $stmt->execute([(string)($data['orderId'] ?? '')]);
    $order = $stmt->fetch();
    if (!$order) json_out(['success' => false, 'message' => 'الطلب غير موجود']);
    $order['items'] = json_decode($order['items_json'], true) ?: [];
    $order['total'] = (float)$order['total'];
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
}
