<?php
/**
 * =====================================================
 *  واحة المنصورية - نظام إدارة المطعم (نسخة PHP)
 *  PDO: يدعم MySQL (استضافة) و SQLite (محلي) - بدون إعداد خارجي
 *  الأدوار: admin | cashier | kitchen | customer
 *  admin: كل الصفحات دائمًا. باقي الأدوار: صفحات افتراضية + صلاحيات إضافية مخصصة.
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

function column_exists(PDO $db, string $table, string $col) {
    if (DB_DRIVER === 'mysql') {
        $st = $db->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
        $st->execute([$table, $col]);
        return (int)$st->fetchColumn() > 0;
    }
    $st = $db->query("PRAGMA table_info($table)");
    foreach ($st->fetchAll() as $row) {
        if (strcasecmp($row['name'], $col) === 0) return true;
    }
    return false;
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
                email VARCHAR(150) DEFAULT '',
                permissions VARCHAR(500) DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS menu (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                category VARCHAR(100) NOT NULL,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                descr VARCHAR(500) DEFAULT '',
                active TINYINT UNSIGNED NOT NULL DEFAULT 1,
                image VARCHAR(500) DEFAULT ''
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
                order_type VARCHAR(20) NOT NULL DEFAULT 'صالة',
                notes VARCHAR(1000) DEFAULT '',
                address VARCHAR(500) DEFAULT '',
                delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
                created_by VARCHAR(100) DEFAULT 'guest',
                INDEX idx_orders_created (created_at),
                INDEX idx_orders_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS settings (
                `key` VARCHAR(100) PRIMARY KEY,
                `value` VARCHAR(1000) DEFAULT ''
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS company_orders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                company_name VARCHAR(200) NOT NULL,
                department VARCHAR(200) DEFAULT '',
                package VARCHAR(50) NOT NULL,
                meals INT NOT NULL DEFAULT 0,
                price DECIMAL(10,2) NOT NULL DEFAULT 0,
                total DECIMAL(10,2) NOT NULL DEFAULT 0,
                notes VARCHAR(500) DEFAULT '',
                order_date DATE NOT NULL,
                created_at DATETIME NOT NULL,
                created_by VARCHAR(100) DEFAULT '',
                INDEX idx_co_company (company_name),
                INDEX idx_co_date (order_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        if (!column_exists($db, 'users', 'permissions')) {
            $db->exec("ALTER TABLE users ADD COLUMN permissions VARCHAR(500) DEFAULT ''");
        }
        if (!column_exists($db, 'menu', 'image')) {
            $db->exec("ALTER TABLE menu ADD COLUMN image VARCHAR(500) DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'order_type')) {
            $db->exec("ALTER TABLE orders ADD COLUMN order_type VARCHAR(20) NOT NULL DEFAULT 'صالة'");
        }
        if (!column_exists($db, 'orders', 'address')) {
            $db->exec("ALTER TABLE orders ADD COLUMN address VARCHAR(500) DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'delivery_fee')) {
            $db->exec("ALTER TABLE orders ADD COLUMN delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0");
        }
        if (!column_exists($db, 'orders', 'company_name')) {
            $db->exec("ALTER TABLE orders ADD COLUMN company_name VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'department')) {
            $db->exec("ALTER TABLE orders ADD COLUMN department VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'item_name')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN item_name VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'order_id')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN order_id VARCHAR(50) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'status')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN status VARCHAR(20) DEFAULT 'جديد'");
        }
        $db->exec("
            CREATE TABLE IF NOT EXISTS companies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(200) NOT NULL,
                created_at DATETIME NOT NULL,
                created_by VARCHAR(100) DEFAULT '',
                UNIQUE KEY uq_company_name (name)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            CREATE TABLE IF NOT EXISTS audit_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(100) NOT NULL,
                action VARCHAR(50) NOT NULL,
                entity VARCHAR(50) NOT NULL DEFAULT '',
                entity_id VARCHAR(50) DEFAULT '',
                details VARCHAR(500) DEFAULT '',
                created_at DATETIME NOT NULL,
                INDEX idx_audit_date (created_at),
                INDEX idx_audit_user (username)
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
                email TEXT DEFAULT '',
                permissions TEXT DEFAULT ''
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
                order_type TEXT NOT NULL DEFAULT 'صالة',
                notes TEXT DEFAULT '',
                address TEXT DEFAULT '',
                delivery_fee REAL NOT NULL DEFAULT 0,
                created_by TEXT DEFAULT 'guest'
            );
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT DEFAULT ''
            );
            CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);
            CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
            CREATE TABLE IF NOT EXISTS company_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                company_name TEXT NOT NULL,
                department TEXT DEFAULT '',
                package TEXT NOT NULL,
                meals INTEGER NOT NULL DEFAULT 0,
                price REAL NOT NULL DEFAULT 0,
                total REAL NOT NULL DEFAULT 0,
                notes TEXT DEFAULT '',
                order_date TEXT NOT NULL,
                created_at TEXT NOT NULL,
                created_by TEXT DEFAULT ''
            );
            CREATE INDEX IF NOT EXISTS idx_co_company ON company_orders(company_name);
            CREATE INDEX IF NOT EXISTS idx_co_date ON company_orders(order_date);
        ");
        if (!column_exists($db, 'users', 'permissions')) {
            $db->exec("ALTER TABLE users ADD COLUMN permissions TEXT DEFAULT ''");
        }
        if (!column_exists($db, 'menu', 'image')) {
            $db->exec("ALTER TABLE menu ADD COLUMN image TEXT DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'order_type')) {
            $db->exec("ALTER TABLE orders ADD COLUMN order_type TEXT NOT NULL DEFAULT 'صالة'");
        }
        if (!column_exists($db, 'orders', 'address')) {
            $db->exec("ALTER TABLE orders ADD COLUMN address TEXT DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'delivery_fee')) {
            $db->exec("ALTER TABLE orders ADD COLUMN delivery_fee REAL NOT NULL DEFAULT 0");
        }
        if (!column_exists($db, 'orders', 'company_name')) {
            $db->exec("ALTER TABLE orders ADD COLUMN company_name VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'orders', 'department')) {
            $db->exec("ALTER TABLE orders ADD COLUMN department VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'item_name')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN item_name VARCHAR(200) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'order_id')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN order_id VARCHAR(50) DEFAULT ''");
        }
        if (!column_exists($db, 'company_orders', 'status')) {
            $db->exec("ALTER TABLE company_orders ADD COLUMN status VARCHAR(20) DEFAULT 'جديد'");
        }
        $db->exec("
            CREATE TABLE IF NOT EXISTS companies (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL,
                created_by TEXT DEFAULT ''
            );
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS audit_log (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                action TEXT NOT NULL,
                entity TEXT DEFAULT '',
                entity_id TEXT DEFAULT '',
                details TEXT DEFAULT '',
                created_at TEXT NOT NULL
            );
            CREATE INDEX IF NOT EXISTS idx_audit_date ON audit_log(created_at);
            CREATE INDEX IF NOT EXISTS idx_audit_user ON audit_log(username);
        ");
    }

    // بيانات أولية (مرة واحدة)
    if ((int)db_scalar($db, 'SELECT COUNT(*) FROM users') === 0) {
        $seedUsers = [
            ['admin', 'admin123', 'المدير', 'admin'],
            ['cashier', 'cash123', 'الكاشير', 'cashier'],
            ['kitchen', 'kit123', 'المطبخ', 'kitchen'],
            ['customer', '1234', 'عميل', 'customer'],
        ];
        $st = $db->prepare('INSERT INTO users (username, password_hash, name, role) VALUES (?, ?, ?, ?)');
        foreach ($seedUsers as $u) {
            $st->execute([$u[0], password_hash($u[1], PASSWORD_DEFAULT), $u[2], $u[3]]);
        }
    }

    if ((int)db_scalar($db, 'SELECT COUNT(*) FROM menu') === 0) {
        seed_menu_items($db);
    }

    $defaults = [
        'restaurantName' => 'واحة المنصورية',
        'phone' => '01153431728',
        'address' => 'أول مدخل المنصورية بجوار مسجد عبدالرحيم زيدان',
        'adminEmail' => '',
        'telegramBotToken' => '',
        'telegramChatId' => '',
        'whatsappNumber' => '201153431728',
        'whatsappToken' => '',
        'whatsappPhoneId' => '',
        'ordersWhatsapp' => '',
        'deliveryFee' => '25',
        'logo' => '',
    ];
    $st = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)');
    foreach ($defaults as $k => $v) {
        $exists = db_scalar($db, 'SELECT COUNT(*) FROM settings WHERE `key` = ?', [$k]);
        if (!$exists) $st->execute([$k, $v]);
    }

    migrate_offers_v2($db);
}

/** ترحيل لمرة واحدة: توحيد قسم "صواني الواحة والعروض الخاصة" القديم إلى قسم "العروض" الرسمي الجديد */
function migrate_offers_v2(PDO $db) {
    if (get_setting_raw($db, 'offers_v2_migrated') === '1') return;
    $db->exec("DELETE FROM menu WHERE category = 'صواني الواحة والعروض الخاصة'");
    $offers = [
        ['صينية السفرة: نصف فرخة + نصف كفتة + أرز', 450, ''],
        ['صينية الأكل: نصف فرخة + ربع طرب + أرز', 350, ''],
        ['صينية العيلة: فرخة مشوية + نصف فرخة + أرز', 600, ''],
        ['صينية الشلة: كيلو كفتة + نصف فرخة + أرز', 650, ''],
        ['صينية الأصحاب: فرختين + نصف فرخة + أرز', 1000, ''],
        ['صينية العمدة: فرخة + كيلو كفتة طرب + أرز', 1500, ''],
        ['صينية الكبير: ثمن جدي + كيلو كفتة + صينية أرز', 1300, ''],
        ['صينية المعلم: ربع جدي + فرخة + كيلو كفتة + أرز', 2650, ''],
        ['صينية 1: نصف كفتة + نصف فرخة طرب + صينية أرز', 850, ''],
        ['صينية 2: نصف كفتة + نصف فرخة طرب + صينية أرز', 800, ''],
        ['صينية 3: كيلو كفتة + نصف فرخة معمر + صينية أرز', 1000, ''],
        ['صينية 4: كيلو كفتة + نصف فرخة معمر + صينية أرز', 1050, ''],
        ['صينية الملوك: ربع جدي + كيلو كفتة + معمر + ورق عنب', 2600, ''],
        ['صينية الوليمة: بطة + جوز حمام + طاجن معمر + ورق عنب', 1750, ''],
        ['العرض الخاص: ربع جدي + نصف كفتة + نصف طرب + ربع معمر + صينية أرز', 2750, '🔥 عرض لا يُفوّت'],
    ];
    $st = $db->prepare('INSERT INTO menu (name, category, price, descr, active, image) VALUES (?, ?, ?, ?, 1, ?)');
    foreach ($offers as $o) {
        $st->execute([$o[0], 'العروض', $o[1], $o[2], category_image('العروض')]);
    }
    $up = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)');
    $exists = db_scalar($db, "SELECT COUNT(*) FROM settings WHERE `key` = 'offers_v2_migrated'");
    if ($exists) {
        $db->prepare("UPDATE settings SET `value` = '1' WHERE `key` = 'offers_v2_migrated'")->execute();
    } else {
        $up->execute(['offers_v2_migrated', '1']);
    }
}

/** قراءة إعداد مباشرة من اتصال قاعدة بيانات معين (تُستخدم أثناء init_db قبل جهوزية db()) */
function get_setting_raw(PDO $db, $key) {
    $st = $db->prepare('SELECT `value` FROM settings WHERE `key` = ?');
    $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? '' : (string)$v;
}

/** صورة تمثيلية لكل قسم (صور حقيقية مولّدة لأصناف المطعم) */
function category_image($cat) {
    $map = [
        'المطبخ والطواجن' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/874539e25_generated_image.png',
        'وجبات فردية وميكسات' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/a47e4af67_generated_image.png',
        'العروض' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/1bea8941c_generated_image.png',
        'دجاج ولحم مندي وكبسة وبرياني' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/de636673c_generated_image.png',
        'المشويات بالكيلو' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/c1aebe49f_generated_image.png',
        'ساندوتشات وسلطات' => 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/bac04e273_generated_image.png',
    ];
    return $map[$cat] ?? 'https://media.base44.com/images/public/69f55aeb618a96592fa36b04/dee9595a9_generated_image.png';
}

function seed_menu_items(PDO $db) {
    // القائمة الكاملة كما في لوحة منيو المطعم (بالفئات الست الأصلية)
    $seedMenu = [
        // ركن المطبخ والطواجن
        ['أرز شعرية', 'المطبخ والطواجن', 40],
        ['أرز بسمتي', 'المطبخ والطواجن', 50],
        ['بطاطس فارم فرايت', 'المطبخ والطواجن', 50],
        ['مكرونة بشاميل', 'المطبخ والطواجن', 75],
        ['طبق مميز', 'المطبخ والطواجن', 100],
        ['سمبوسة', 'المطبخ والطواجن', 100],
        ['فرد سمان مشوي', 'المطبخ والطواجن', 225],
        ['فرد حمام محشي', 'المطبخ والطواجن', 225],
        ['طاجن سجق شرقي + أرز', 'المطبخ والطواجن', 225],
        ['مكرونة مبكبكة فراخ', 'المطبخ والطواجن', 200],
        ['مكرونة مبكبكة لحم', 'المطبخ والطواجن', 250],
        ['ورقة لحمة + أرز', 'المطبخ والطواجن', 250],
        ['طاجن لسان باللحمة', 'المطبخ والطواجن', 250],
        ['طاجن بامية باللحمة', 'المطبخ والطواجن', 250],
        ['طاجن خضار باللحمة', 'المطبخ والطواجن', 250],
        ['موزة ضاني + أرز', 'المطبخ والطواجن', 450],
        ['كيلو كبدة ضان', 'المطبخ والطواجن', 1000],
        ['ملوخية', 'المطبخ والطواجن', 30],
        ['خضار مشكل / بامية', 'المطبخ والطواجن', 30],

        // وجبات فردية وميكسات (تقدم مع عيش وسلطات)
        ['ثمن فرخة + أرز', 'وجبات فردية وميكسات', 80],
        ['ربع فرخة (بدون أرز)', 'وجبات فردية وميكسات', 110],
        ['ربع فرخة + أرز', 'وجبات فردية وميكسات', 130],
        ['ربع فرخة + قطعة كفتة + أرز', 'وجبات فردية وميكسات', 140],
        ['ربع فرخة + مكرونة بشاميل', 'وجبات فردية وميكسات', 140],
        ['نصف فرخة + مكرونة بشاميل', 'وجبات فردية وميكسات', 180],
        ['نصف فرخة + أرز', 'وجبات فردية وميكسات', 200],
        ['ثمن فرخة + نصف كفتة + أرز', 'وجبات فردية وميكسات', 210],
        ['نصف فرخة + نصف كفتة + أرز', 'وجبات فردية وميكسات', 160],
        ['نصف فرخة + ربع كفتة + أرز', 'وجبات فردية وميكسات', 200],
        ['نصف فرخة + ربع طرب + أرز', 'وجبات فردية وميكسات', 180],
        ['نصف فرخة + نصف كباب + أرز', 'وجبات فردية وميكسات', 300],
        ['فرد سمان + أرز', 'وجبات فردية وميكسات', 125],
        ['مكرونة مبكبكة (وجبة)', 'وجبات فردية وميكسات', 250],

        // دجاج ولحم مندي وكبسة وبرياني
        ['ربع فرخة مندي + أرز', 'دجاج ولحم مندي وكبسة وبرياني', 80],
        ['نصف فرخة مندي / برياني', 'دجاج ولحم مندي وكبسة وبرياني', 200],
        ['ربع فرخة مندي / كبسة / بخاري + أرز', 'دجاج ولحم مندي وكبسة وبرياني', 400],
        ['فرخة مندي + أرز', 'دجاج ولحم مندي وكبسة وبرياني', 400],
        ['نفر لحم مندي أو كبسة', 'دجاج ولحم مندي وكبسة وبرياني', 900],
        ['ربع جدي + أرز أو كبسة', 'دجاج ولحم مندي وكبسة وبرياني', 1800],
        ['نصف جدي + أرز', 'دجاج ولحم مندي وكبسة وبرياني', 3600],

        // المشويات بالكيلو (تقدم مع العيش والسلطات)
        ['كيلو كفتة', 'المشويات بالكيلو', 440],
        ['كيلو طاووق', 'المشويات بالكيلو', 450],
        ['كيلو سيخ مشوي', 'المشويات بالكيلو', 450],
        ['كيلو ريش بانيه (بدون أرز)', 'المشويات بالكيلو', 600],
        ['كيلو مشكل مشويات', 'المشويات بالكيلو', 800],
        ['كيلو لحم ستيك بقري', 'المشويات بالكيلو', 1000],
        ['كيلو ريش', 'المشويات بالكيلو', 1000],

        // ساندوتشات وسلطات
        ['ساندوتش كفتة', 'ساندوتشات وسلطات', 40],
        ['ساندوتش بانيه', 'ساندوتشات وسلطات', 50],
        ['ساندوتش شيش', 'ساندوتشات وسلطات', 60],
        ['ساندوتش حواوشي', 'ساندوتشات وسلطات', 75],
        ['ساندوتش طرب', 'ساندوتشات وسلطات', 125],
        ['طحينة / مخلل / سلطة', 'ساندوتشات وسلطات', 10],
    ];
    $st = $db->prepare('INSERT INTO menu (name, category, price, descr, active, image) VALUES (?, ?, ?, ?, 1, ?)');
    foreach ($seedMenu as $m) {
        $st->execute([$m[0], $m[1], $m[2], '', category_image($m[1])]);
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

// ===================== الأدوار والصلاحيات =====================
/** الصفحات الافتراضية لكل دور (تُستخدم عند عدم وجود صلاحيات مخصصة) */
function default_pages_for_role($role) {
    switch ($role) {
        case 'admin':    return ['admin', 'cashier', 'kitchen', 'menu', 'cart', 'invoice'];
        case 'cashier':  return ['cashier', 'menu', 'invoice'];
        case 'kitchen':  return ['kitchen'];
        default:         return ['menu', 'cart'];
    }
}

/** الصفحات المسموحة فعليًا لمستخدم معيّن (دور admin يرى كل شيء دائمًا) */
function user_allowed_pages($user) {
    if (!$user) return [];
    if ($user['role'] === 'admin') return ['admin', 'cashier', 'kitchen', 'menu', 'cart', 'invoice'];
    $custom = trim((string)($user['permissions'] ?? ''));
    if ($custom !== '') {
        $decoded = json_decode($custom, true);
        if (is_array($decoded) && $decoded) return array_values(array_unique(array_merge($decoded, ['menu', 'cart'])));
    }
    return default_pages_for_role($user['role']);
}

// ===================== SESSION / AUTH =====================
function boot_session() {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    // تخزين الجلسات في مجلد data/ الخاص بنا (نفس مكان قاعدة البيانات، مضمون قابل للكتابة)
    // بعض الاستضافات المجانية (مثل InfinityFree) تمسح أو تقيّد مجلد الجلسات الافتراضي للسيرفر،
    // فيفقد المستخدم جلسته فجأة أثناء العمل (يظهر "انتهت الجلسة" عند أي إجراء مثل الحذف/الإلغاء).
    // تخزينها هنا بشكل صريح يحل هذه المشكلة نهائيًا.
    $sessDir = __DIR__ . '/data/sessions';
    if (!is_dir($sessDir)) @mkdir($sessDir, 0775, true);
    if (is_dir($sessDir) && is_writable($sessDir)) {
        session_save_path($sessDir);
    }

    // عمر جلسة طويل (12 ساعة) — يوم عمل كامل على شاشة الكاشير/المطبخ بدون قطع
    $lifetime = 12 * 3600;
    ini_set('session.gc_maxlifetime', (string)$lifetime);
    session_set_cookie_params([
        'lifetime' => $lifetime,
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
    $st = $db->prepare('SELECT id, username, name, role, active, permissions FROM users WHERE id = ?');
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

/** يسمح لأي مستخدم لديه صفحة $page ضمن صلاحياته (وليس فقط دوره) */
function require_page_access($page) {
    $u = current_user();
    if (!$u || !in_array($page, user_allowed_pages($u), true)) {
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
    $pages = user_allowed_pages($u);
    $redirect = 'menu';
    if ($u['role'] === 'admin') $redirect = 'admin';
    elseif (in_array('cashier', $pages, true)) $redirect = 'cashier';
    elseif (in_array('kitchen', $pages, true)) $redirect = 'kitchen';
    json_out([
        'success' => true,
        'user' => ['name' => $u['name'], 'role' => $u['role'], 'username' => $u['username'], 'pages' => $pages],
        'redirect' => $redirect,
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
    json_out(['success' => true, 'user' => $u ? [
        'name' => $u['name'], 'role' => $u['role'], 'username' => $u['username'], 'pages' => user_allowed_pages($u),
    ] : null]);
}

function api_menu() {
    $db = db();
    $res = $db->query('SELECT id, name, category, price, descr, image FROM menu WHERE active = 1 ORDER BY id');
    $items = [];
    while ($row = $res->fetch()) {
        $row['price'] = (float)$row['price'];
        $row['desc'] = $row['descr'];
        unset($row['descr']);
        if (empty($row['image'])) $row['image'] = category_image($row['category']);
        $items[] = $row;
    }
    json_out(['success' => true, 'items' => $items]);
}

/** تسجيل عملية في سجل المستخدمين (إضافة/تعديل/حذف/إلغاء...) */
function log_activity($action, $entity, $entityId, $details = '') {
    try {
        $db = db();
        $u = current_user();
        $username = $u ? $u['username'] : 'guest';
        $st = $db->prepare('INSERT INTO audit_log (username, action, entity, entity_id, details, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $st->execute([$username, (string)$action, (string)$entity, (string)$entityId, mb_substr((string)$details, 0, 480), date('Y-m-d H:i:s')]);
    } catch (Exception $e) {}
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
    $address = trim((string)($data['address'] ?? ''));
    $orderType = (string)($data['orderType'] ?? 'صالة');

    // طلبات الموقع (غير الكاشير/المدير) = دليفري فقط
    $isStaff = $user && in_array($user['role'], ['admin', 'cashier'], true);
    if (!$isStaff) $orderType = 'دليفري';
    if (!in_array($orderType, ['صالة', 'دليفري', 'شركات'], true)) $orderType = 'صالة';

    // بيانات الشركة (لنوع شركات)
    $companyName = trim((string)($data['companyName'] ?? ''));
    $department = trim((string)($data['department'] ?? ''));
    if ($orderType === 'شركات' && $companyName === '') {
        json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);
    }
    if ($orderType === 'دليفري' && ($phone === '' || $address === '')) {
        json_out(['success' => false, 'message' => 'طلبات الدليفري تتطلب رقم الهاتف والعنوان']);
    }

    // قيمة التوصيل تُضاف على الطلب في نوع دليفري فقط
    $deliveryFee = 0.0;
    if ($orderType === 'دليفري') {
        $deliveryFee = (float)get_setting('deliveryFee', '0');
        $total += $deliveryFee;
    }

    $stmt = $db->prepare('INSERT INTO orders (order_id, created_at, customer_name, phone, items_json, total, status, order_type, notes, address, delivery_fee, created_by, company_name, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $orderId,
        date('Y-m-d H:i:s'),
        $customerName,
        $phone,
        json_encode($items, JSON_UNESCAPED_UNICODE),
        $total,
        'جديد',
        $orderType,
        $notes,
        $address,
        $deliveryFee,
        $user ? $user['username'] : 'guest',
        $companyName,
        $department,
    ]);

    // طلب شركة: تسجيل الأصناف في جدول الشركات لتظهر في تقارير الشركات
    if ($orderType === 'شركات') {
        $co = $db->prepare('INSERT INTO company_orders (company_name, department, package, item_name, meals, price, total, notes, order_date, created_at, created_by, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($items as $it) {
            $co->execute([$companyName, $department, 'من المنيو', $it['name'], $it['qty'], $it['price'], $it['price'] * $it['qty'], $notes, date('Y-m-d'), date('Y-m-d H:i:s'), $user ? $user['username'] : 'guest', $orderId]);
        }
    }
    log_activity('إضافة طلب', 'طلب', $orderId, ($orderType === 'شركات' ? 'شركة: ' . $companyName . ' — ' : '') . count($items) . ' صنف بإجمالي ' . $total . ' ج.م (' . $orderType . ')');

    send_order_notification($orderId, $customerName, $phone, $notes, $items, $total, $orderType);
    // إرسال تفاصيل الطلب لرقم واتساب المطعم المخصص لاستقبال الطلبات
    $ordersWa = get_setting('ordersWhatsapp', '');
    if ($ordersWa) {
        send_whatsapp_message($ordersWa, restaurant_order_message($orderId, $customerName, $phone, $address, $items, $deliveryFee, $total, $orderType, $notes, $user ? $user['name'] : 'موقع'));
    }
    if ($phone) send_whatsapp_message($phone, whatsapp_order_message($orderId, $customerName, $items, $total, 'جديد', $orderType));

    // روابط wa.me (بدون توكن): إرسال بنقرة واحدة للمطعم وللعميل — تعمل دائمًا كخطة بديلة أو أساسية
    $waRestTarget = $ordersWa !== '' ? $ordersWa : get_setting('whatsappNumber', '');
    $waRest = $waRestTarget !== '' ? wa_me_link($waRestTarget, restaurant_order_message($orderId, $customerName, $phone, $address, $items, $deliveryFee, $total, $orderType, $notes, $user ? $user['name'] : 'موقع')) : '';
    $waClient = $phone ? wa_me_link($phone, "مرحبًا 👋 من *واحة المنصورة*\nتم استلام طلبك رقم " . $orderId . " بنجاح ✅\nالإجمالي: " . $total . " ج.م\nجاري تحضير طلبك الآن 🍽️") : '';

    json_out(['success' => true, 'orderId' => $orderId, 'total' => $total, 'message' => 'تم استلام طلبك بنجاح! رقم الطلب: ' . $orderId, 'wa_restaurant' => $waRest, 'wa_customer' => $waClient]);
}

/** تعديل طلب موجود: البيانات + الأصناف + إعادة حساب الإجمالي */
function api_update_order($data) {
    require_role(['admin', 'cashier']);
    $db = db();
    $orderId = (string)($data['orderId'] ?? '');
    $old = $db->prepare('SELECT * FROM orders WHERE order_id = ?');
    $old->execute([$orderId]);
    $o = $old->fetch();
    if (!$o) json_out(['success' => false, 'message' => 'الطلب غير موجود']);

    $inputItems = is_array($data['items'] ?? null) ? $data['items'] : [];
    if (!$inputItems) json_out(['success' => false, 'message' => 'السلة فارغة']);

    $items = [];
    $total = 0.0;
    $st = $db->prepare('SELECT name, price FROM menu WHERE id = ?');
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
    if (!$items) json_out(['success' => false, 'message' => 'لا توجد أصناف صالحة']);

    $orderType = (string)($data['orderType'] ?? $o['order_type']);
    if (!in_array($orderType, ['صالة', 'دليفري', 'شركات'], true)) $orderType = 'صالة';
    $deliveryFee = 0.0;
    if ($orderType === 'دليفري') {
        $deliveryFee = (float)get_setting('deliveryFee', '0');
        $total += $deliveryFee;
    }
    $companyName = trim((string)($data['companyName'] ?? ($o['company_name'] ?? '')));
    $department = trim((string)($data['department'] ?? ($o['department'] ?? '')));
    if ($orderType === 'شركات' && $companyName === '') json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);

    $stmt = $db->prepare('UPDATE orders SET customer_name = ?, phone = ?, items_json = ?, total = ?, order_type = ?, notes = ?, address = ?, delivery_fee = ?, company_name = ?, department = ? WHERE order_id = ?');
    $stmt->execute([
        trim((string)($data['customerName'] ?? $o['customer_name'])),
        trim((string)($data['phone'] ?? $o['phone'])),
        json_encode($items, JSON_UNESCAPED_UNICODE),
        $total,
        $orderType,
        trim((string)($data['notes'] ?? $o['notes'])),
        trim((string)($data['address'] ?? $o['address'])),
        $deliveryFee,
        $companyName,
        $department,
        $orderId,
    ]);

    // مزامنة أصناف الشركة المرتبطة بالطلب
    $del = $db->prepare('DELETE FROM company_orders WHERE order_id = ?');
    $del->execute([$orderId]);
    if ($orderType === 'شركات') {
        $u = current_user();
        $co = $db->prepare('INSERT INTO company_orders (company_name, department, package, item_name, meals, price, total, notes, order_date, created_at, created_by, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($items as $it) {
            $co->execute([$companyName, $department, 'من المنيو', $it['name'], $it['qty'], $it['price'], $it['price'] * $it['qty'], '', date('Y-m-d'), date('Y-m-d H:i:s'), $u['username'], $orderId]);
        }
    }
    log_activity('تعديل طلب', 'طلب', $orderId, 'إجمالي قديم: ' . $o['total'] . ' ج.م → جديد: ' . $total . ' ج.م');
    json_out(['success' => true, 'message' => 'تم تعديل الطلب', 'total' => $total]);
}

/** إلغاء طلب (حالة ملغي — لا يُحسب في المبيعات) */
function api_cancel_order($data) {
    require_role(['admin', 'cashier']);
    $db = db();
    $orderId = (string)($data['orderId'] ?? '');
    $stmt = $db->prepare("UPDATE orders SET status = 'ملغي' WHERE order_id = ?");
    $stmt->execute([$orderId]);
    if ((int)$stmt->rowCount()) {
        // مزامنة: إلغاء بنود الشركة المرتبطة بهذا الطلب حتى لا تُحسب في الشاشات والتقارير
        $co = $db->prepare("UPDATE company_orders SET status = 'ملغي' WHERE order_id = ?");
        $co->execute([$orderId]);
        log_activity('إلغاء طلب', 'طلب', $orderId, 'تم إلغاء الطلب وبنود الشركات المرتبطة');
        json_out(['success' => true, 'message' => 'تم إلغاء الطلب']);
    }
    json_out(['success' => false, 'message' => 'الطلب غير موجود']);
}

/** حذف طلب نهائيًا */
function api_delete_order($data) {
    require_role(['admin', 'cashier']);
    $db = db();
    $orderId = (string)($data['orderId'] ?? '');
    $stmt = $db->prepare('DELETE FROM orders WHERE order_id = ?');
    $stmt->execute([$orderId]);
    if ((int)$stmt->rowCount()) {
        $del = $db->prepare('DELETE FROM company_orders WHERE order_id = ?');
        $del->execute([$orderId]);
        log_activity('حذف طلب', 'طلب', $orderId, 'حُذف الطلب نهائيًا من النظام');
        json_out(['success' => true, 'message' => 'تم حذف الطلب']);
    }
    json_out(['success' => false, 'message' => 'الطلب غير موجود']);
}

/** تقرير سجل عمليات المستخدمين خلال فترة */
function api_audit_report($data) {
    require_role(['admin']);
    $db = db();
    $from = date('Y-m-d', strtotime((string)($data['from'] ?? date('Y-m-d'))));
    $to = date('Y-m-d', strtotime((string)($data['to'] ?? date('Y-m-d'))));
    if ($from > $to) { $t = $from; $from = $to; $to = $t; }
    $fromDt = $from . ' 00:00:00';
    $toDt = $to . ' 23:59:59';
    $stmt = $db->prepare('SELECT * FROM audit_log WHERE created_at >= ? AND created_at <= ? ORDER BY id DESC LIMIT 1000');
    $stmt->execute([$fromDt, $toDt]);
    $rows = [];
    while ($row = $stmt->fetch()) $rows[] = $row;
    json_out(['success' => true, 'from' => $from, 'to' => $to, 'entries' => $rows, 'count' => count($rows)]);
}

/** تصفير الحسابات بالكامل: حذف كل الطلبات وطلبات الشركات نهائيًا (بداية جديدة) — للمدير فقط */
function api_reset_accounts($data) {
    $user = require_role(['admin']);
    $db = db();
    if ((string)($data['confirm'] ?? '') !== 'تصفير') {
        json_out(['success' => false, 'message' => 'تأكيد غير صحيح']);
    }
    $ordersCount = (int)$db->query('SELECT COUNT(*) AS c FROM orders')->fetch()['c'];
    $coCount = (int)$db->query('SELECT COUNT(*) AS c FROM company_orders')->fetch()['c'];
    $db->exec('DELETE FROM orders');
    $db->exec('DELETE FROM company_orders');
    log_activity('تصفير الحسابات', 'النظام', 'الكل', 'حذف ' . $ordersCount . ' طلب و ' . $coCount . ' بند شركة نهائيًا بواسطة ' . $user['username']);
    json_out(['success' => true, 'message' => 'تم تصفير الحسابات بالكامل', 'deletedOrders' => $ordersCount, 'deletedCompanyItems' => $coCount]);
}

function api_get_orders($data) {
    require_role(['admin', 'cashier', 'kitchen']);
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
    require_role(['admin', 'cashier', 'kitchen']);
    $db = db();
    $orderId = (string)($data['orderId'] ?? '');
    $status = (string)($data['status'] ?? '');
    $allowed = ['جديد', 'قيد التحضير', 'جاهز', 'تم التسليم'];
    if (!in_array($status, $allowed, true)) json_out(['success' => false, 'message' => 'حالة غير صحيحة']);
    $stmt = $db->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
    $stmt->execute([$status, $orderId]);
    if ((int)$stmt->rowCount()) {
        log_activity('تحديث حالة', 'طلب', $orderId, 'الحالة الجديدة: ' . $status);
        $ord = $db->prepare('SELECT * FROM orders WHERE order_id = ?');
        $ord->execute([$orderId]);
        $o = $ord->fetch();
        if ($o && !empty($o['phone'])) {
            $items = json_decode($o['items_json'], true) ?: [];
            send_whatsapp_message($o['phone'], whatsapp_order_message($orderId, $o['customer_name'], $items, (float)$o['total'], $status, $o['order_type'] ?? 'صالة'));
        }
        json_out(['success' => true, 'message' => 'تم تحديث الحالة']);
    }
    json_out(['success' => false, 'message' => 'الطلب غير موجود']);
}

function api_save_item($data) {
    require_role(['admin']);
    $db = db();
    $name = trim((string)($data['name'] ?? ''));
    $category = trim((string)($data['category'] ?? ''));
    $price = (float)($data['price'] ?? 0);
    $descr = trim((string)($data['desc'] ?? ''));
    $image = trim((string)($data['image'] ?? ''));
    if ($name === '' || $price <= 0) json_out(['success' => false, 'message' => 'أدخل الاسم والسعر']);
    if ($image === '') $image = category_image($category);

    $id = (int)($data['id'] ?? 0);
    if ($id > 0) {
        $stmt = $db->prepare('UPDATE menu SET name = ?, category = ?, price = ?, descr = ?, image = ? WHERE id = ?');
        $stmt->execute([$name, $category, $price, $descr, $image, $id]);
        if ((int)$stmt->rowCount()) {
            log_activity('تعديل صنف', 'منيو', (string)$id, $name . ' — ' . $price . ' ج.م');
            json_out(['success' => true, 'message' => 'تم التحديث']);
        }
        json_out(['success' => false, 'message' => 'الصنف غير موجود']);
    }
    $stmt = $db->prepare('INSERT INTO menu (name, category, price, descr, active, image) VALUES (?, ?, ?, ?, 1, ?)');
    $stmt->execute([$name, $category, $price, $descr, $image]);
    $newId = (int)$db->lastInsertId();
    log_activity('إضافة صنف', 'منيو', (string)$newId, $name . ' — ' . $price . ' ج.م');
    json_out(['success' => true, 'message' => 'تم الإضافة', 'id' => $newId]);
}

function api_toggle_item($data) {
    require_role(['admin']);
    $db = db();
    $stmt = $db->prepare('UPDATE menu SET active = ? WHERE id = ?');
    $stmt->execute([!empty($data['active']) ? 1 : 0, (int)($data['id'] ?? 0)]);
    log_activity(!empty($data['active']) ? 'تفعيل صنف' : 'إخفاء صنف', 'منيو', (string)($data['id'] ?? ''), '');
    json_out(['success' => true, 'message' => !empty($data['active']) ? 'تم تفعيل الصنف' : 'تم إخفاء الصنف']);
}

function api_add_user($data) {
    require_role(['admin']);
    $db = db();
    $username = trim((string)($data['username'] ?? ''));
    $password = (string)($data['password'] ?? '');
    $name = trim((string)($data['name'] ?? ''));
    $role = (string)($data['role'] ?? 'customer');
    if (!in_array($role, ['admin', 'cashier', 'kitchen', 'customer'], true)) $role = 'customer';
    $permissions = is_array($data['permissions'] ?? null) ? array_values(array_intersect($data['permissions'], ['admin','cashier','kitchen','menu','cart','invoice'])) : [];
    if ($username === '' || $password === '' || $name === '') json_out(['success' => false, 'message' => 'أكمل جميع الحقول']);

    $exists = db_scalar($db, 'SELECT COUNT(*) FROM users WHERE username = ?', [$username]);
    if ($exists) json_out(['success' => false, 'message' => 'اسم المستخدم موجود بالفعل']);

    $stmt = $db->prepare('INSERT INTO users (username, password_hash, name, role, permissions) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $name, $role, $permissions ? json_encode($permissions) : '']);
    log_activity('إضافة مستخدم', 'مستخدم', $username, $name . ' (' . $role . ')');
    json_out(['success' => true, 'message' => 'تم إضافة المستخدم']);
}

function api_list_users() {
    require_role(['admin']);
    $db = db();
    $res = $db->query('SELECT id, username, name, role, active, permissions FROM users ORDER BY id');
    $users = [];
    while ($row = $res->fetch()) {
        $row['active'] = (int)$row['active'];
        $row['pages'] = user_allowed_pages($row);
        $users[] = $row;
    }
    json_out(['success' => true, 'users' => $users]);
}

function api_update_permissions($data) {
    require_role(['admin']);
    $db = db();
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) json_out(['success' => false, 'message' => 'مستخدم غير صحيح']);
    $allowedPages = ['admin','cashier','kitchen','menu','cart','invoice'];
    $permissions = is_array($data['permissions'] ?? null) ? array_values(array_intersect($data['permissions'], $allowedPages)) : [];
    $fields = ['permissions = ?'];
    $params = [$permissions ? json_encode($permissions) : ''];
    if (isset($data['role']) && in_array($data['role'], ['admin','cashier','kitchen','customer'], true)) {
        $fields[] = 'role = ?';
        $params[] = $data['role'];
    }
    if (isset($data['active'])) {
        $fields[] = 'active = ?';
        $params[] = !empty($data['active']) ? 1 : 0;
    }
    $params[] = $id;
    $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
    $uq = $db->prepare('SELECT username FROM users WHERE id = ?');
    $uq->execute([$id]);
    $uu = $uq->fetch();
    log_activity('تعديل صلاحيات', 'مستخدم', $uu ? $uu['username'] : (string)$id, implode(', ', array_merge($permissions, isset($data['role']) ? ['دور: ' . $data['role']] : [])));
    json_out(['success' => true, 'message' => 'تم تحديث صلاحيات المستخدم']);
}

function api_get_settings() {
    require_role(['admin']);
    $db = db();
    $keys = ['restaurantName','phone','address','adminEmail','telegramBotToken','telegramChatId','whatsappNumber','whatsappToken','whatsappPhoneId','ordersWhatsapp','deliveryFee','logo'];
    $out = [];
    foreach ($keys as $k) $out[$k] = get_setting($k, '');
    json_out(['success' => true, 'settings' => $out]);
}

function api_update_settings($data) {
    require_role(['admin']);
    $db = db();
    $allowed = ['restaurantName','phone','address','adminEmail','telegramBotToken','telegramChatId','whatsappNumber','whatsappToken','whatsappPhoneId','ordersWhatsapp','deliveryFee','logo'];
    $st = $db->prepare('UPDATE settings SET `value` = ? WHERE `key` = ?');
    $ins = $db->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?)');
    foreach ($allowed as $k) {
        if (!array_key_exists($k, $data)) continue;
        $v = trim((string)$data[$k]);
        $exists = db_scalar($db, 'SELECT COUNT(*) FROM settings WHERE `key` = ?', [$k]);
        if ($exists) $st->execute([$v, $k]);
        else $ins->execute([$k, $v]);
    }
    log_activity('تعديل إعدادات', 'إعدادات', '', 'تم حفظ إعدادات المطعم');
    json_out(['success' => true, 'message' => 'تم حفظ الإعدادات']);
}

function api_report($data) {
    require_role(['admin']);
    $db = db();
    $date = (string)($data['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');

    $stmt = $db->prepare("SELECT items_json, total, status FROM orders WHERE SUBSTR(created_at, 1, 10) = ? AND status != 'ملغي'");
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

/** لوحة مؤشرات الأداء (KPIs) للمدير */
function api_dashboard() {
    require_role(['admin']);
    $db = db();
    $today = date('Y-m-d');

    // كل الطلبات (آخر 60 يوم كحد أقصى لتفادي بيانات ضخمة)
    $stmt = $db->prepare("SELECT created_at, items_json, total, status FROM orders WHERE created_at >= ? ORDER BY id DESC");
    $stmt->execute([date('Y-m-d H:i:s', strtotime('-60 days'))]);
    $rows = $stmt->fetchAll();

    $todaySales = 0.0; $todayOrders = 0;
    $weekSales = 0.0; $weekOrders = 0;
    $monthSales = 0.0; $monthOrders = 0;
    $byStatus = ['جديد' => 0, 'قيد التحضير' => 0, 'جاهز' => 0, 'تم التسليم' => 0];
    $itemsCount = [];
    $trend = []; // آخر 7 أيام: تاريخ => [count, sales]
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $trend[$d] = ['date' => $d, 'orders' => 0, 'sales' => 0.0];
    }
    $weekStart = date('Y-m-d', strtotime('-6 days'));
    $monthStart = date('Y-m-d', strtotime('-29 days'));

    foreach ($rows as $row) {
        $d = substr($row['created_at'], 0, 10);
        $total = (float)$row['total'];
        if ($d === $today) { $todaySales += $total; $todayOrders++; }
        if ($d >= $weekStart) { $weekSales += $total; $weekOrders++; }
        if ($d >= $monthStart) { $monthSales += $total; $monthOrders++; }
        if (isset($byStatus[$row['status']])) $byStatus[$row['status']]++;
        else $byStatus[$row['status']] = 1;
        if (isset($trend[$d])) { $trend[$d]['orders']++; $trend[$d]['sales'] += $total; }
        foreach ((json_decode($row['items_json'], true) ?: []) as $it) {
            $itemsCount[$it['name']] = ($itemsCount[$it['name']] ?? 0) + max(1, (int)($it['qty'] ?? 1));
        }
    }
    arsort($itemsCount);
    $topItems = [];
    $i = 0;
    foreach ($itemsCount as $n => $q) {
        if ($i++ >= 6) break;
        $topItems[] = ['name' => $n, 'qty' => $q];
    }

    $totalMenuItems = (int)db_scalar($db, 'SELECT COUNT(*) FROM menu WHERE active = 1');
    $totalUsers = (int)db_scalar($db, 'SELECT COUNT(*) FROM users');
    $avgOrder = $monthOrders > 0 ? round($monthSales / $monthOrders, 2) : 0;

    json_out(['success' => true, 'dashboard' => [
        'today' => ['sales' => $todaySales, 'orders' => $todayOrders],
        'week' => ['sales' => $weekSales, 'orders' => $weekOrders],
        'month' => ['sales' => $monthSales, 'orders' => $monthOrders],
        'avgOrder' => $avgOrder,
        'byStatus' => $byStatus,
        'trend' => array_values($trend),
        'topItems' => $topItems,
        'totalMenuItems' => $totalMenuItems,
        'totalUsers' => $totalUsers,
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
    $order['delivery_fee'] = (float)($order['delivery_fee'] ?? 0);
    $subtotal = 0.0;
    foreach ($order['items'] as $it) $subtotal += (float)$it['price'] * (int)($it['qty'] ?? 1);
    $order['subtotal'] = $subtotal;
    // اسم المستخدم الذي أنشأ الطلب (يُطبع على الإيصال)
    $cashierName = db_scalar($db, 'SELECT name FROM users WHERE username = ?', [(string)$order['created_by']]);
    unset($order['items_json']);
    json_out(['success' => true, 'order' => $order, 'restaurant' => [
        'name' => get_setting('restaurantName', 'واحة المنصورية'),
        'phone' => get_setting('phone', '01153431728'),
        'address' => get_setting('address', ''),
        'logo' => get_setting('logo', ''),
    ], 'cashierName' => $cashierName ?: (string)$order['created_by']]);
}

// ===================== NOTIFICATIONS =====================
function send_order_notification($orderId, $customerName, $phone, $notes, $items, $total, $orderType = 'صالة') {
    $lines = [];
    foreach ($items as $i) $lines[] = "• {$i['name']} × {$i['qty']}";
    $itemsTxt = implode("\n", $lines);

    // تليجرام (لو مُعد)
    $token = get_setting('telegramBotToken');
    $chatId = get_setting('telegramChatId');
    if ($token && $chatId && function_exists('curl_init')) {
        $msg = "🔔 <b>طلب جديد (" . e($orderType) . ") - " . APP_NAME . "</b>\n\n" .
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

/** رسالة الطلب الجديد المرسلة لرقم واتساب المطعم المخصص لاستقبال الطلبات */
function restaurant_order_message($orderId, $customerName, $phone, $address, $items, $deliveryFee, $total, $orderType, $notes, $cashierName = '') {
    $lines = [];
    $subtotal = 0.0;
    foreach ($items as $i) {
        $lines[] = "• {$i['name']} × {$i['qty']} = " . ((float)$i['price'] * (int)$i['qty']) . " ج";
        $subtotal += (float)$i['price'] * (int)$i['qty'];
    }
    $itemsTxt = implode("\n", $lines);
    $typeTxt = $orderType === 'دليفري' ? '🛵 دليفري' : '🍽️ صالة';
    $msg = "🆕 *طلب جديد* - " . APP_NAME . "\n"
         . "━━━━━━━━━━━━\n"
         . "📋 رقم الطلب: {$orderId}\n"
         . "{$typeTxt}\n"
         . "👤 العميل: {$customerName}\n"
         . "📞 الهاتف: " . ($phone ?: '-') . "\n";
    if ($orderType === 'دليفري') $msg .= "📍 العنوان: " . ($address ?: '-') . "\n";
    $msg .= "━━━━━━━━━━━━\n"
         . "🍽️ الأصناف:\n{$itemsTxt}\n"
         . "━━━━━━━━━━━━\n"
         . "💰 المجموع: {$subtotal} ج\n";
    if ($deliveryFee > 0) $msg .= "🛵 التوصيل: {$deliveryFee} ج\n";
    $msg .= "💰 *الإجمالي: {$total} ج*\n";
    if ($notes) $msg .= "📝 ملاحظات: {$notes}\n";
    if ($cashierName) $msg .= "🧾 بواسطة: {$cashierName}\n";
    return $msg;
}

/** إعدادات عامة للصفحات (بدون تسجيل دخول): قيمة التوصيل وبيانات المطعم */
function api_public_settings() {
    json_out(['success' => true, 'settings' => [
        'restaurantName' => get_setting('restaurantName', APP_NAME),
        'phone' => get_setting('phone', ''),
        'address' => get_setting('address', ''),
        'logo' => get_setting('logo', ''),
        'deliveryFee' => (float)get_setting('deliveryFee', '0'),
        'ordersWhatsapp' => get_setting('ordersWhatsapp', ''),
    ]]);
}

function api_update_user($data) {
    require_role(['admin']);
    $db = db();
    $id = (int)($data['id'] ?? 0);
    if ($id <= 0) json_out(['success' => false, 'message' => 'مستخدم غير صحيح']);
    $exists = db_scalar($db, 'SELECT COUNT(*) FROM users WHERE id = ?', [$id]);
    if (!$exists) json_out(['success' => false, 'message' => 'المستخدم غير موجود']);

    $username = trim((string)($data['username'] ?? ''));
    $name = trim((string)($data['name'] ?? ''));
    if ($username === '' || $name === '') json_out(['success' => false, 'message' => 'اسم المستخدم والاسم الظاهر مطلوبان']);

    $dup = db_scalar($db, 'SELECT COUNT(*) FROM users WHERE username = ? AND id <> ?', [$username, $id]);
    if ($dup) json_out(['success' => false, 'message' => 'اسم المستخدم موجود بالفعل لمستخدم آخر']);

    $fields = ['username = ?', 'name = ?'];
    $params = [$username, $name];

    $password = (string)($data['password'] ?? '');
    if ($password !== '') {
        $fields[] = 'password_hash = ?';
        $params[] = password_hash($password, PASSWORD_DEFAULT);
    }

    if (isset($data['role']) && in_array($data['role'], ['admin','cashier','kitchen','customer'], true)) {
        $fields[] = 'role = ?';
        $params[] = $data['role'];
    }
    if (isset($data['active'])) {
        $fields[] = 'active = ?';
        $params[] = !empty($data['active']) ? 1 : 0;
    }

    $params[] = $id;
    $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
    $stmt->execute($params);
    $changes = [$name];
    if ($password !== '') $changes[] = 'كلمة سر جديدة';
    if (isset($data['role'])) $changes[] = 'دور: ' . $data['role'];
    log_activity('تعديل مستخدم', 'مستخدم', $username, implode(' — ', $changes));
    json_out(['success' => true, 'message' => 'تم تحديث بيانات المستخدم']);
}

// ===================== طلبات وجبات الشركات =====================
function api_add_company_order($data) {
    $user = require_page_access('cashier');
    $db = db();
    $company = trim((string)($data['companyName'] ?? ''));
    $department = trim((string)($data['department'] ?? ''));
    $package = (string)($data['package'] ?? 'جافة ×50');
    if (!in_array($package, ['جافة ×50', 'جافة ×100', 'من المنيو'], true)) $package = 'جافة ×50';
    $itemName = trim((string)($data['itemName'] ?? ''));
    if ($package === 'من المنيو' && $itemName === '') json_out(['success' => false, 'message' => 'اختر الصنف من المنيو']);
    $meals = (int)($data['meals'] ?? 0);
    $price = (float)($data['price'] ?? 0);
    $notes = trim((string)($data['notes'] ?? ''));

    if ($company === '') json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);
    if ($meals <= 0) json_out(['success' => false, 'message' => 'أدخل عدد الوجبات']);
    if ($price <= 0) json_out(['success' => false, 'message' => 'أدخل قيمة الوجبة']);

    $total = $meals * $price;
    $label = $package === 'من المنيو' ? $itemName : $package;

    // إنشاء طلب فعلي مرتبط في جدول orders ليظهر كـ"طلب جديد" في شاشتي الكاشير والمطبخ،
    // تمامًا مثل باقي الطلبات (صالة/دليفري)، مع بقاء تفاصيله في جدول الشركات للتقارير.
    $orderId = 'ORD-' . date('Ymd-His') . '-' . substr((string)mt_rand(1000, 9999), 0, 4);
    $itemsJson = json_encode([['id' => 0, 'name' => $label, 'price' => $price, 'qty' => $meals]], JSON_UNESCAPED_UNICODE);
    $ordStmt = $db->prepare('INSERT INTO orders (order_id, created_at, customer_name, phone, items_json, total, status, order_type, notes, address, delivery_fee, created_by, company_name, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $ordStmt->execute([
        $orderId, date('Y-m-d H:i:s'), $company, '', $itemsJson, $total, 'جديد', 'شركات',
        $notes, '', 0, $user['username'], $company, $department,
    ]);

    $stmt = $db->prepare('INSERT INTO company_orders (company_name, department, package, item_name, meals, price, total, notes, order_date, created_at, created_by, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$company, $department, $package, $label, $meals, $price, $total, $notes, date('Y-m-d'), date('Y-m-d H:i:s'), $user['username'], $orderId]);
    log_activity('إضافة طلب شركة', 'طلب شركة', $company, $package . ($package === 'من المنيو' ? ' (' . $itemName . ')' : '') . ' × ' . $meals . ' = ' . $total . ' ج.م');
    json_out(['success' => true, 'message' => 'تم تسجيل طلب الشركة بنجاح', 'total' => $total, 'orderId' => $orderId]);
}

/** قائمة الشركات المعتمدة + الأسماء السابقة + الأقسام السابقة (للاقتراح التلقائي) */
function api_companies_list() {
    $u = current_user();
    if (!$u || !array_intersect(['cashier', 'admin', 'kitchen'], user_allowed_pages($u))) {
        json_out(['success' => false, 'message' => 'غير مصرح']);
    }
    $db = db();
    $master = [];
    $res = $db->query('SELECT name FROM companies ORDER BY name');
    while ($r = $res->fetch()) $master[] = (string)$r['name'];
    $used = [];
    $res = $db->query('SELECT DISTINCT company_name FROM company_orders ORDER BY company_name');
    while ($r = $res->fetch()) $used[] = (string)$r['company_name'];
    $depts = [];
    $res = $db->query("SELECT DISTINCT department FROM company_orders WHERE department != '' ORDER BY department");
    while ($r = $res->fetch()) $depts[] = (string)$r['department'];
    json_out(['success' => true, 'companies' => array_values(array_unique(array_merge($master, $used))), 'departments' => $depts]);
}

/** قائمة الشركات المعتمدة (للإدارة في لوحة التحكم) */
function api_manage_company_list() {
    $user = require_page_access('admin');
    $db = db();
    $res = $db->query('SELECT id, name, created_at FROM companies ORDER BY name');
    $companies = [];
    while ($r = $res->fetch()) {
        $companies[] = ['id' => (int)$r['id'], 'name' => (string)$r['name'], 'created_at' => (string)$r['created_at']];
    }
    json_out(['success' => true, 'companies' => $companies]);
}

/** إدارة قائمة الشركات المعتمدة (لوحة التحكم) */
function api_manage_company($data) {
    $user = require_page_access('admin');
    $db = db();
    $op = (string)($data['op'] ?? '');
    if ($op === 'add') {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);
        $exists = db_scalar($db, 'SELECT COUNT(*) FROM companies WHERE name = ?', [$name]);
        if ($exists) json_out(['success' => false, 'message' => 'الشركة موجودة بالفعل']);
        $st = $db->prepare('INSERT INTO companies (name, created_at, created_by) VALUES (?, ?, ?)');
        $st->execute([$name, date('Y-m-d H:i:s'), $user['username']]);
        log_activity('إضافة شركة', 'قائمة الشركات', $name, '');
        json_out(['success' => true, 'message' => 'تمت إضافة الشركة']);
    }
    if ($op === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $st = $db->prepare('DELETE FROM companies WHERE id = ?');
        $st->execute([$id]);
        log_activity('حذف شركة', 'قائمة الشركات', (string)$id, 'حذف من القائمة المعتمدة');
        json_out(['success' => true, 'message' => 'تم حذف الشركة من القائمة']);
    }
    json_out(['success' => false, 'message' => 'عملية غير معروفة']);
}

/** حفظ طلب شركة متعدد البنود: يُضاف على الطلب الموجود لنفس الشركة والقسم في نفس اليوم */
function api_save_company_order($data) {
    $user = require_page_access('cashier');
    $db = db();
    $company = trim((string)($data['companyName'] ?? ''));
    $department = trim((string)($data['department'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));
    $inputItems = is_array($data['items'] ?? null) ? $data['items'] : [];
    if ($company === '') json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);
    if (!$inputItems) json_out(['success' => false, 'message' => 'أضف بندًا واحدًا على الأقل']);

    $items = [];
    foreach ($inputItems as $it) {
        $name = trim((string)($it['name'] ?? ''));
        $qty = (int)($it['qty'] ?? 0);
        $price = (float)($it['price'] ?? 0);
        if ($name === '' || $qty <= 0 || $price <= 0) continue;
        $items[] = ['name' => $name, 'qty' => $qty, 'price' => $price];
    }
    if (!$items) json_out(['success' => false, 'message' => 'تحقق من البنود: الاسم والعدد والسعر مطلوبة']);

    $today = date('Y-m-d');
    // البحث عن طلب نشط موجود لنفس الشركة والقسم اليوم للإضافة عليه
    $orderId = '';
    $find = $db->prepare("SELECT order_id FROM company_orders WHERE company_name = ? AND department = ? AND order_date = ? AND status != 'ملغي' AND order_id != '' ORDER BY id DESC LIMIT 1");
    $find->execute([$company, $department, $today]);
    $row = $find->fetchColumn();
    if ($row) {
        $chk = $db->prepare('SELECT order_id FROM orders WHERE order_id = ?');
        $chk->execute([(string)$row]);
        if ($chk->fetchColumn()) $orderId = (string)$row;
    }

    $newTotal = 0.0;
    foreach ($items as $it) $newTotal += $it['price'] * $it['qty'];
    $appended = $orderId !== '';

    if ($appended) {
        // قراءة الطلب الموجود ودمج البنود الجديدة + إرفاق الملاحظات تلقائيًا
        $q = $db->prepare('SELECT items_json, notes, status FROM orders WHERE order_id = ?');
        $q->execute([$orderId]);
        $ord = $q->fetch();
        $oldItems = json_decode((string)$ord['items_json'], true);
        if (!is_array($oldItems)) $oldItems = [];
        foreach ($items as $it) $oldItems[] = ['id' => 0, 'name' => $it['name'], 'price' => $it['price'], 'qty' => $it['qty']];
        $total = 0.0;
        foreach ($oldItems as $it) $total += (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 1);
        $mergedNotes = trim((string)$ord['notes']);
        if ($notes !== '') $mergedNotes = ($mergedNotes === '' ? $notes : $mergedNotes . ' | ' . $notes);
        $newStatus = ($ord['status'] === 'تم التسليم') ? 'جديد' : (string)$ord['status'];
        $up = $db->prepare('UPDATE orders SET items_json = ?, total = ?, notes = ?, status = ? WHERE order_id = ?');
        $up->execute([json_encode($oldItems, JSON_UNESCAPED_UNICODE), $total, $mergedNotes, $newStatus, $orderId]);
    } else {
        $orderId = 'ORD-' . date('Ymd-His') . '-' . substr((string)mt_rand(1000, 9999), 0, 4);
        $itemsJson = json_encode(array_map(function ($it) {
            return ['id' => 0, 'name' => $it['name'], 'price' => $it['price'], 'qty' => $it['qty']];
        }, $items), JSON_UNESCAPED_UNICODE);
        $ordStmt = $db->prepare('INSERT INTO orders (order_id, created_at, customer_name, phone, items_json, total, status, order_type, notes, address, delivery_fee, created_by, company_name, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ordStmt->execute([
            $orderId, date('Y-m-d H:i:s'), $company, '', $itemsJson, $newTotal, 'جديد', 'شركات',
            $notes, '', 0, $user['username'], $company, $department,
        ]);
    }

    // تسجيل كل بند في جدول الشركات (للتقارير) مرتبطًا بنفس الطلب
    $st = $db->prepare('INSERT INTO company_orders (company_name, department, package, item_name, meals, price, total, notes, order_date, created_at, created_by, order_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($items as $it) {
        $st->execute([$company, $department, 'من المنيو', $it['name'], $it['qty'], $it['price'], $it['price'] * $it['qty'], $notes, $today, date('Y-m-d H:i:s'), $user['username'], $orderId, 'جديد']);
    }

    // إضافة الشركة لقائمة الشركات تلقائيًا إن لم تكن موجودة
    $exists = db_scalar($db, 'SELECT COUNT(*) FROM companies WHERE name = ?', [$company]);
    if (!$exists) {
        $add = $db->prepare('INSERT INTO companies (name, created_at, created_by) VALUES (?, ?, ?)');
        $add->execute([$company, date('Y-m-d H:i:s'), $user['username']]);
    }

    $itemsDesc = implode(' + ', array_map(function ($it) { return $it['name'] . ' × ' . $it['qty']; }, $items));
    log_activity($appended ? 'إضافة بنود على طلب شركة' : 'تسجيل طلب شركة', 'طلب شركة', $company, $itemsDesc . ' = ' . $newTotal . ' ج.م' . ($department ? ' — قسم: ' . $department : '') . ($notes ? ' — ملاحظات: ' . $notes : ''));
    json_out([
        'success' => true,
        'message' => $appended ? 'تمت الإضافة على طلب الشركة الموجود بنجاح' : 'تم تسجيل طلب الشركة بنجاح',
        'total' => $newTotal,
        'orderId' => $orderId,
        'appended' => $appended,
    ]);
}

/** تتبع حالة طلب للعميل (عام برقم الطلب) */
function api_order_status($data) {
    $orderId = trim((string)($data['orderId'] ?? ''));
    if ($orderId === '') json_out(['success' => false, 'message' => 'أدخل رقم الطلب']);
    $db = db();
    $st = $db->prepare('SELECT order_id, created_at, status, total, items_json, order_type FROM orders WHERE order_id = ?');
    $st->execute([$orderId]);
    $o = $st->fetch();
    if (!$o) json_out(['success' => false, 'message' => 'الطلب غير موجود']);
    $items = json_decode((string)$o['items_json'], true);
    if (!is_array($items)) $items = [];
    json_out([
        'success' => true,
        'order' => [
            'orderId' => (string)$o['order_id'],
            'createdAt' => (string)$o['created_at'],
            'status' => (string)$o['status'],
            'total' => (float)$o['total'],
            'type' => (string)$o['order_type'],
            'items' => array_map(function ($it) { return ['name' => (string)($it['name'] ?? ''), 'qty' => (int)($it['qty'] ?? 1), 'price' => (float)($it['price'] ?? 0)]; }, $items),
        ],
    ]);
}

/** طلبات شركات اليوم: مجمعة باسم الشركة مع الإجماليات والتفاصيل */
function api_company_today($data = []) {
    // الكاشير أو المطبخ: المطبخ يرى وجبات الشركات وعددها (مع إمكانية استرجاع تاريخ سابق)
    $u = current_user();
    if (!$u || !array_intersect(['cashier', 'kitchen'], user_allowed_pages($u))) {
        json_out(['success' => false, 'message' => 'غير مصرح']);
    }
    $date = (string)($data['date'] ?? '');
    $ts = $date !== '' ? strtotime($date) : false;
    $date = $ts !== false ? date('Y-m-d', $ts) : date('Y-m-d');
    $db = db();
    $res = $db->query('SELECT * FROM company_orders WHERE order_date = ' . $db->quote($date) . ' ORDER BY created_at DESC');
    $orders = [];
    while ($row = $res->fetch()) {
        $row['meals'] = (int)$row['meals'];
        $row['price'] = (float)$row['price'];
        $row['total'] = (float)$row['total'];
        $orders[] = $row;
    }
    $groups = [];
    foreach ($orders as $o) {
        $k = $o['company_name'];
        if (!isset($groups[$k])) $groups[$k] = ['company' => $k, 'orders' => [], 'totalMeals' => 0, 'total' => 0.0];
        $groups[$k]['orders'][] = $o;
        // الملغي لا يُحسب في الإجماليات
        if (($o['status'] ?? 'نشط') === 'ملغي') continue;
        $groups[$k]['totalMeals'] += $o['meals'];
        $groups[$k]['total'] += $o['total'];
    }
    $g = array_values($groups);
    usort($g, function($a, $b) { return $b['total'] <=> $a['total']; });
    json_out(['success' => true, 'date' => $date, 'groups' => $g, 'grandTotal' => array_sum(array_column($g, 'total')), 'grandMeals' => array_sum(array_column($g, 'totalMeals'))]);
}

/** تعديل بند طلب شركة */
function api_update_company_order($data) {
    $user = require_page_access('cashier');
    $db = db();
    $id = (int)($data['id'] ?? 0);
    $q = $db->prepare('SELECT * FROM company_orders WHERE id = ?');
    $q->execute([$id]);
    $o = $q->fetch();
    if (!$o) json_out(['success' => false, 'message' => 'البند غير موجود']);

    $company = trim((string)($data['companyName'] ?? $o['company_name']));
    $department = trim((string)($data['department'] ?? $o['department']));
    $package = (string)($data['package'] ?? $o['package']);
    if (!in_array($package, ['جافة ×50', 'جافة ×100', 'من المنيو'], true)) $package = 'جافة ×50';
    $itemName = trim((string)($data['itemName'] ?? ''));
    if ($package === 'من المنيو' && $itemName === '') json_out(['success' => false, 'message' => 'اختر الصنف من المنيو']);
    $meals = (int)($data['meals'] ?? $o['meals']);
    $price = (float)($data['price'] ?? $o['price']);
    $notes = trim((string)($data['notes'] ?? $o['notes']));
    if ($company === '') json_out(['success' => false, 'message' => 'أدخل اسم الشركة']);
    if ($meals <= 0) json_out(['success' => false, 'message' => 'أدخل عدد الوجبات']);
    if ($price <= 0) json_out(['success' => false, 'message' => 'أدخل قيمة الوجبة']);

    $total = $meals * $price;
    $newLabel = $package === 'من المنيو' ? $itemName : $package;
    $oldLabel = $o['item_name'] !== '' ? $o['item_name'] : $o['package'];

    // مزامنة التعديل مع الطلب الأصلي المرتبط (orders.items_json) ليظهر التغيير في شاشة المطبخ فورًا
    $orderId = (string)($o['order_id'] ?? '');
    if ($orderId !== '') {
        $ordSt = $db->prepare('SELECT items_json FROM orders WHERE order_id = ?');
        $ordSt->execute([$orderId]);
        $ord = $ordSt->fetch();
        if ($ord) {
            $items = json_decode((string)$ord['items_json'], true);
            if (!is_array($items)) $items = [];
            $matched = false;
            foreach ($items as $k => $it) {
                if (!$matched && (string)($it['name'] ?? '') === (string)$oldLabel && (float)($it['price'] ?? 0) === (float)$o['price']) {
                    $items[$k] = ['id' => 0, 'name' => $newLabel, 'price' => $price, 'qty' => $meals];
                    $matched = true;
                }
            }
            if ($matched) {
                $newTotal = 0.0;
                foreach ($items as $it) { $newTotal += (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 1); }
                $updOrd = $db->prepare('UPDATE orders SET items_json = ?, total = ?, customer_name = ?, department = ? WHERE order_id = ?');
                $updOrd->execute([json_encode($items, JSON_UNESCAPED_UNICODE), $newTotal, $company, $department, $orderId]);
            }
        }
    }

    $st = $db->prepare('UPDATE company_orders SET company_name = ?, department = ?, package = ?, item_name = ?, meals = ?, price = ?, total = ?, notes = ? WHERE id = ?');
    $st->execute([$company, $department, $package, $newLabel, $meals, $price, $total, $notes, $id]);
    log_activity('تعديل طلب شركة', 'طلب شركة', $company, 'بند #' . $id . ': ' . $package . ($package === 'من المنيو' ? ' (' . $itemName . ')' : '') . ' × ' . $meals . ' = ' . $total . ' ج.م');
    json_out(['success' => true, 'message' => 'تم تعديل بند الشركة', 'total' => $total]);
}

/** إلغاء بند طلب شركة (لا يُحسب في التقارير) */
function api_cancel_company_order($data) {
    $user = require_page_access('cashier');
    $db = db();
    $id = (int)($data['id'] ?? 0);
    $st = $db->prepare("UPDATE company_orders SET status = 'ملغي' WHERE id = ?");
    $st->execute([$id]);
    if ((int)$st->rowCount()) {
        $q = $db->prepare('SELECT company_name, package, item_name, meals, total FROM company_orders WHERE id = ?');
        $q->execute([$id]);
        $o = $q->fetch();
        log_activity('إلغاء طلب شركة', 'طلب شركة', $o ? $o['company_name'] : (string)$id, 'بند #' . $id . ' (' . ($o ? (($o['package'] === 'من المنيو' && $o['item_name'] ? $o['item_name'] : $o['package']) . ' × ' . $o['meals']) : '') . ')');
        json_out(['success' => true, 'message' => 'تم إلغاء البند']);
    }
    json_out(['success' => false, 'message' => 'البند غير موجود']);
}

/** حذف بند طلب شركة نهائيًا */
function api_delete_company_order($data) {
    $user = require_page_access('cashier');
    $db = db();
    $id = (int)($data['id'] ?? 0);
    $q = $db->prepare('SELECT company_name, package, item_name, price, meals, total, order_id FROM company_orders WHERE id = ?');
    $q->execute([$id]);
    $o = $q->fetch();
    if (!$o) json_out(['success' => false, 'message' => 'البند غير موجود']);

    $st = $db->prepare('DELETE FROM company_orders WHERE id = ?');
    $st->execute([$id]);
    if (!(int)$st->rowCount()) json_out(['success' => false, 'message' => 'البند غير موجود']);

    // مزامنة الحذف مع الطلب الأصلي (orders.items_json) حتى لا يعود البند بعد أي تعديل لاحق على الطلب
    $orderId = (string)($o['order_id'] ?? '');
    if ($orderId !== '') {
        $ordSt = $db->prepare('SELECT items_json, order_type FROM orders WHERE order_id = ?');
        $ordSt->execute([$orderId]);
        $ord = $ordSt->fetch();
        if ($ord) {
            $items = json_decode((string)$ord['items_json'], true);
            if (!is_array($items)) $items = [];
            $removed = false;
            foreach ($items as $k => $it) {
                if (!$removed && (string)($it['name'] ?? '') === (string)$o['item_name'] && (float)($it['price'] ?? 0) === (float)$o['price']) {
                    unset($items[$k]);
                    $removed = true;
                }
            }
            $items = array_values($items);
            if (empty($items)) {
                // آخر بند في الطلب: يُحذف الطلب نهائيًا بالكامل
                $delOrd = $db->prepare('DELETE FROM orders WHERE order_id = ?');
                $delOrd->execute([$orderId]);
                $delCo = $db->prepare('DELETE FROM company_orders WHERE order_id = ?');
                $delCo->execute([$orderId]);
            } else {
                $newTotal = 0.0;
                foreach ($items as $it) { $newTotal += (float)($it['price'] ?? 0) * (int)($it['qty'] ?? 1); }
                $updOrd = $db->prepare('UPDATE orders SET items_json = ?, total = ? WHERE order_id = ?');
                $updOrd->execute([json_encode($items, JSON_UNESCAPED_UNICODE), $newTotal, $orderId]);
            }
        }
    }

    log_activity('حذف طلب شركة', 'طلب شركة', $o['company_name'] ?: (string)$id, 'بند #' . $id . ' حُذف نهائيًا بشكل دائم (متزامن مع الطلب الأصلي)');
    json_out(['success' => true, 'message' => 'تم حذف البند نهائيًا']);
}

/** تحديث حالة بند طلب شركة (تم / جديد) — يستخدمها الكاشير والمطبخ لتتبع التحضير، لا يمس حالة الإلغاء */
function api_set_company_item_status($data) {
    $u = current_user();
    if (!$u || !array_intersect(['cashier', 'kitchen', 'admin'], user_allowed_pages($u))) {
        json_out(['success' => false, 'message' => 'غير مصرح']);
    }
    $db = db();
    $id = (int)($data['id'] ?? 0);
    $status = (string)($data['status'] ?? '');
    if (!in_array($status, ['تم', 'جديد'], true)) json_out(['success' => false, 'message' => 'حالة غير صحيحة']);

    $q = $db->prepare('SELECT company_name, status FROM company_orders WHERE id = ?');
    $q->execute([$id]);
    $o = $q->fetch();
    if (!$o) json_out(['success' => false, 'message' => 'البند غير موجود']);
    if (($o['status'] ?? '') === 'ملغي') json_out(['success' => false, 'message' => 'البند ملغي']);

    $dbStatus = $status === 'تم' ? 'تم' : null;
    $st = $db->prepare('UPDATE company_orders SET status = ? WHERE id = ?');
    $st->execute([$dbStatus, $id]);
    json_out(['success' => true, 'message' => 'تم التحديث', 'status' => $status]);
}

/** تقرير الشركات خلال فترة: مجمعة باسم الشركة + تفاصيل كل طلب */
function api_company_report($data) {
    require_role(['admin']);
    $db = db();
    $from = date('Y-m-d', strtotime((string)($data['from'] ?? date('Y-m-d'))));
    $to = date('Y-m-d', strtotime((string)($data['to'] ?? date('Y-m-d'))));
    if ($from === false || $to === false) json_out(['success' => false, 'message' => 'تاريخ غير صحيح']);
    if ($from === $to) {
        $cond = 'order_date = ' . $db->quote($from);
    } else {
        if ($from > $to) { $t = $from; $from = $to; $to = $t; }
        $cond = 'order_date >= ' . $db->quote($from) . ' AND order_date <= ' . $db->quote($to);
    }
    $res = $db->query("SELECT * FROM company_orders WHERE " . $cond . " AND (status IS NULL OR status != 'ملغي') ORDER BY company_name ASC, order_date ASC, created_at ASC");
    $orders = [];
    while ($row = $res->fetch()) {
        $row['meals'] = (int)$row['meals'];
        $row['price'] = (float)$row['price'];
        $row['total'] = (float)$row['total'];
        $orders[] = $row;
    }
    $groups = [];
    foreach ($orders as $o) {
        $k = $o['company_name'];
        if (!isset($groups[$k])) $groups[$k] = ['company' => $k, 'orders' => [], 'ordersCount' => 0, 'totalMeals' => 0, 'total' => 0.0];
        $groups[$k]['orders'][] = $o;
        $groups[$k]['ordersCount']++;
        $groups[$k]['totalMeals'] += $o['meals'];
        $groups[$k]['total'] += $o['total'];
    }
    $g = array_values($groups);
    usort($g, function($a, $b) { return $b['total'] <=> $a['total']; });
    json_out(['success' => true, 'from' => $from, 'to' => $to, 'groups' => $g,
        'grandTotal' => array_sum(array_column($g, 'total')),
        'grandMeals' => array_sum(array_column($g, 'totalMeals'))]);
}

/** نص رسالة واتساب لتحديث حالة الطلب أو تأكيد استلامه */
function whatsapp_order_message($orderId, $customerName, $items, $total, $status, $orderType = 'صالة') {
    $lines = [];
    foreach ($items as $i) $lines[] = "• {$i['name']} × {$i['qty']}";
    $itemsTxt = implode("\n", $lines);
    $statusEmoji = [
        'جديد' => '🆕', 'قيد التحضير' => '👨‍🍳', 'جاهز' => '✅', 'تم التسليم' => '🎉',
    ][$status] ?? '📦';
    $typeTxt = $orderType === 'دليفري' ? '🛵 دليفري' : '🍽️ صالة';
    return "{$statusEmoji} " . APP_NAME . "\n" .
           "رقم الطلب: {$orderId}\n" .
           "مرحبًا {$customerName}، حالة طلبك الآن: *{$status}*\n" .
           "نوع الطلب: {$typeTxt}\n\n" .
           "الأصناف:\n{$itemsTxt}\n\n" .
           "الإجمالي: {$total} جنيه\n" .
           "شكرًا لطلبك من " . APP_NAME . " 🌴";
}

/** توحيد الأرقام المصرية لأي صيغة: +2010.. / 002010.. / 010.. / 10.. → 2010.. */
function normalize_wa_phone($phone) {
    $d = preg_replace('/\D/', '', (string)$phone);
    if ($d === '') return '';
    if (strpos($d, '00') === 0) $d = substr($d, 2);            // 002012.. → 2012..
    if (strlen($d) === 10 && $d[0] === '1') $d = '2' . $d;      // 1001234567 → 21001234567
    if (strlen($d) === 11 && $d[0] === '0') $d = '2' . $d;      // 01001234567 → 201001234567
    return $d;
}

/** رابط wa.me جاهز بالإرسال بنقرة واحدة (بدون توكن) */
function wa_me_link($phone, $text) {
    $digits = normalize_wa_phone($phone);
    if ($digits === '') return '';
    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($text);
}

/**
 * إرسال رسالة واتساب تلقائيًا عبر WhatsApp Cloud API الرسمي من ميتا (بدون فتح أي رابط أو تدخل يدوي).
 * يتطلب توكن ورقم من WhatsApp Business Platform (Meta for Developers) يتم إدخالهما من لوحة المدير → الإعدادات.
 * لو لم يتم إدخال البيانات، لا تُرسل أي رسالة (بدون كسر باقي النظام).
 */
function send_whatsapp_message($phone, $text) {
    $token = get_setting('whatsappToken');
    $phoneId = get_setting('whatsappPhoneId');
    if (!$token || !$phoneId || !$phone || !function_exists('curl_init')) return false;

    // تطبيع الرقم لصيغة دولية بدون + أو أصفار بداية (مصر: يبدأ ب 20)
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (substr($clean, 0, 1) === '0') $clean = '2' . $clean; // 01xxxxxxxxx -> 201xxxxxxxxx
    elseif (substr($clean, 0, 2) !== '20') $clean = '20' . $clean;

    $ch = curl_init("https://graph.facebook.com/v20.0/{$phoneId}/messages");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $clean,
            'type' => 'text',
            'text' => ['body' => $text],
        ], JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
    return true;
}
