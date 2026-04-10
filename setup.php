<?php
/**
 * QUYETDEV - Full Setup Script
 * Run this once to setup everything correctly
 * URL: http://localhost/webfl/setup.php
 */
require_once 'includes/config.php';

$log = [];
$errors = [];

function step($msg, &$log) { $log[] = ['type' => 'ok', 'msg' => $msg]; }
function warn($msg, &$log) { $log[] = ['type' => 'warn', 'msg' => $msg]; }
function err($msg, &$log, &$errors) { $log[] = ['type' => 'err', 'msg' => $msg]; $errors[] = $msg; }

// ===================================================
// 1. ENSURE SETTINGS TABLE HAS ALL KEYS
// ===================================================
$defaults = [
    'site_name'     => 'QUYETDEV',
    'site_logo'     => 'QUYETDEV',
    'announcement'  => '🔥 QUYETDEV - Hệ thống tài khoản Premium #1 Việt Nam | Bảo hành uy tín - Giao hàng 30 giây!',
    'contact_phone' => '0869226687',
    'contact_email' => 'admin@quyetdev.com',
    'facebook_link' => '#',
    'telegram_link' => 'https://t.me/quyetdev',
    'zalo_link'     => 'https://zalo.me/0869226687',
    'smm_api_url'   => 'https://autosub.vn/api/v2',
    'smm_api_key'   => '09ebed9981c95936cc721c1e728a27b5fa5e5066ac7974e6b36cdccd3d9efe86',
];

foreach ($defaults as $k => $v) {
    $db->query("INSERT INTO settings (s_key, s_value) VALUES ('$k', '" . $db->escape($v) . "') 
                ON DUPLICATE KEY UPDATE s_value = IF(s_value IS NULL OR s_value = '', '" . $db->escape($v) . "', s_value)");
}
step("✅ Settings defaults ensured", $log);

// ===================================================
// 2. ENSURE REQUIRED COLUMNS EXIST
// ===================================================
$alterations = [
    ['packages', 'api_service_id', 'ALTER TABLE packages ADD COLUMN api_service_id INT DEFAULT NULL'],
    ['packages', 'original_price', 'ALTER TABLE packages ADD COLUMN original_price DECIMAL(15,2) DEFAULT 0.00'],
    ['orders',   'api_order_id',   'ALTER TABLE orders ADD COLUMN api_order_id VARCHAR(100) DEFAULT NULL'],
    ['orders',   'link',           'ALTER TABLE orders ADD COLUMN link TEXT DEFAULT NULL'],
    ['orders',   'quantity',       'ALTER TABLE orders ADD COLUMN quantity INT DEFAULT 0'],
];

foreach ($alterations as [$table, $col, $sql]) {
    $check = $db->fetch("SELECT COUNT(*) as cnt FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$table' AND COLUMN_NAME = '$col'");
    if ($check['cnt'] == 0) {
        $db->query($sql);
        step("✅ Added column: $table.$col", $log);
    } else {
        step("☑️ Column exists: $table.$col", $log);
    }
}

// ===================================================
// 3. ENSURE PRODUCT CATEGORIES
// ===================================================
$categories_to_ensure = [
    ['Giải Trí',      'fas fa-play-circle'],
    ['Làm Việc',      'fas fa-briefcase'],
    ['Học Tập',       'fas fa-graduation-cap'],
    ['Công Cụ AI',    'fas fa-robot'],
    ['Dịch vụ MXH',   'fab fa-facebook'],
    ['Dịch vụ TikTok','fab fa-tiktok'],
];

$cat_ids = [];
foreach ($categories_to_ensure as [$name, $icon]) {
    $db->query("INSERT INTO categories (name, icon) VALUES ('" . $db->escape($name) . "', '$icon') 
                ON DUPLICATE KEY UPDATE icon='$icon'");
    $existing = $db->fetch("SELECT id FROM categories WHERE name = '" . $db->escape($name) . "'");
    $cat_ids[$name] = $existing['id'];
    step("✅ Category: $name (ID: {$existing['id']})", $log);
}

$cat_mxh    = $cat_ids['Dịch vụ MXH'];
$cat_tiktok = $cat_ids['Dịch vụ TikTok'];
$cat_ent    = $cat_ids['Giải Trí'];
$cat_ai     = $cat_ids['Công Cụ AI'];

// ===================================================
// 4. ENSURE PREMIUM PRODUCTS (for homepage display)
// ===================================================
$premium_products = [
    ['category_id' => $cat_ent, 'name' => 'Netflix Premium', 'desc' => 'Xem phim không giới hạn 4K UHD, không quảng cáo', 'img' => 'netflix.png',  'featured' => 1],
    ['category_id' => $cat_ent, 'name' => 'Spotify Premium', 'desc' => 'Nghe nhạc chất lượng cao không quảng cáo', 'img' => 'spotify.png',  'featured' => 1],
    ['category_id' => $cat_ent, 'name' => 'Youtube Premium', 'desc' => 'Xem YouTube không quảng cáo + YouTube Music', 'img' => 'youtube.png', 'featured' => 1],
    ['category_id' => $cat_ai,  'name' => 'ChatGPT Plus',    'desc' => 'Truy cập GPT-4o và tính năng mới nhất của OpenAI', 'img' => 'chatgpt.png', 'featured' => 1],
    ['category_id' => $cat_ent, 'name' => 'Disney+ Premium', 'desc' => 'Stream phim Disney, Marvel, Star Wars 4K', 'img' => 'disney.png',  'featured' => 1],
    ['category_id' => $cat_ent, 'name' => 'Adobe Creative Cloud', 'desc' => 'Truy cập toàn bộ ứng dụng Adobe chuyên nghiệp', 'img' => 'adobe.png',   'featured' => 1],
    ['category_id' => $cat_ai,  'name' => 'Canva Pro',        'desc' => 'Thiết kế đồ họa chuyên nghiệp không giới hạn', 'img' => 'canva.png',  'featured' => 1],
    ['category_id' => $cat_ent, 'name' => 'Microsoft 365',   'desc' => 'Word, Excel, PowerPoint, Teams và nhiều hơn', 'img' => 'microsoft.png','featured' => 1],
];

$product_ids = [];
foreach ($premium_products as $p) {
    $db->query("INSERT INTO products (category_id, name, description, image, status, is_featured)
        VALUES ({$p['category_id']}, '" . $db->escape($p['name']) . "', 
                '" . $db->escape($p['desc']) . "', 
                '{$p['img']}', 'active', {$p['featured']})
        ON DUPLICATE KEY UPDATE status='active', is_featured={$p['featured']}");
    $existing = $db->fetch("SELECT id FROM products WHERE name = '" . $db->escape($p['name']) . "'");
    $product_ids[$p['name']] = $existing['id'];
}
step("✅ Premium products ensured: " . count($premium_products) . " products", $log);

// Add packages for premium products if missing
$pkg_defaults = [
    'Netflix Premium'      => [['1 Tháng - Chia sẻ', 65000], ['1 Tháng - Riêng', 140000], ['1 Năm - Chia sẻ', 600000]],
    'Spotify Premium'      => [['1 Tháng', 25000], ['3 Tháng', 65000], ['1 Năm', 230000]],
    'Youtube Premium'      => [['1 Tháng - Gia đình', 35000], ['1 Năm - Gia đình', 350000]],
    'ChatGPT Plus'         => [['1 Tháng', 450000]],
    'Disney+ Premium'      => [['1 Tháng - Chia sẻ', 55000], ['1 Tháng - Riêng', 110000]],
    'Adobe Creative Cloud' => [['1 Năm - All Apps', 350000]],
    'Canva Pro'            => [['1 Năm - Team', 150000], ['Vĩnh Viễn', 280000]],
    'Microsoft 365'        => [['1 Năm - Personal', 180000], ['1 Năm - Family', 320000]],
];

foreach ($pkg_defaults as $prod_name => $packages) {
    if (!isset($product_ids[$prod_name])) continue;
    $pid = $product_ids[$prod_name];
    $check = $db->fetch("SELECT COUNT(*) as cnt FROM packages WHERE product_id = $pid");
    if ($check['cnt'] == 0) {
        foreach ($packages as [$pkg_name, $price]) {
            $db->query("INSERT INTO packages (product_id, name, price, stock) 
                        VALUES ($pid, '" . $db->escape($pkg_name) . "', $price, 999)");
        }
        step("✅ Added packages for: $prod_name", $log);
    }
}

// ===================================================
// 5. ENSURE MXH (Facebook) PRODUCTS
// ===================================================
$fb_products = [
    ['Tăng LIKE Bài Viết', 'Tăng lượt thích cho bài viết Facebook nhanh chóng',   [
        ['Like bài viết - Việt (Siêu Ổn Định)', 12.0, 64],
        ['Like bài viết - Tây Bot (Giá Rẻ)',    6.0,  66],
    ]],
    ['Tăng FOLLOW Cá Nhân', 'Tăng người theo dõi trang cá nhân Facebook', [
        ['Follow cá nhân - ✨VIP2✨ Nguồn Việt', 25.0, 21],
        ['Follow cá nhân - ✨VIP3✨ Nhanh Tây',  18.0, 23],
    ]],
    ['Tăng LIKE Fanpage', 'Tăng lượt thích trang Fanpage Facebook', [
        ['Like Fanpage - Việt Nam (Ổn định)',    20.0, 101],
        ['Like Fanpage - Mix (Tốc độ cao)',      10.0, 103],
    ]],
    ['Tăng MEMBER Group', 'Thêm thành viên vào nhóm Facebook', [
        ['Member Group - Nhanh (Bot Mix)',        8.0, 150],
        ['Member Group - VIP (Nguồn Việt)',      22.0, 152],
    ]],
    ['Tăng SHARE Bài Viết', 'Tăng lượt chia sẻ cho bài viết Facebook', [
        ['Share bài viết - Việt (Siêu tốc)',    15.0, 170],
        ['Share bài viết - Mix Rẻ',              5.0, 172],
    ]],
    ['Tăng REVIEW Fanpage', 'Tăng đánh giá sao cho Fanpage Facebook', [
        ['Review 5 sao - Bot Mix',              30.0, 200],
        ['Review 5 sao - Tài khoản Việt',       55.0, 202],
    ]],
];

foreach ($fb_products as [$name, $desc, $packages]) {
    $db->query("INSERT INTO products (category_id, name, description, image, status)
        VALUES ($cat_mxh, '" . $db->escape($name) . "', '" . $db->escape($desc) . "', 'facebook.png', 'active')
        ON DUPLICATE KEY UPDATE category_id=$cat_mxh, status='active'");
    $prod = $db->fetch("SELECT id FROM products WHERE name = '" . $db->escape($name) . "' AND category_id = $cat_mxh");

    if ($prod) {
        $pid = $prod['id'];
        $check = $db->fetch("SELECT COUNT(*) as cnt FROM packages WHERE product_id = $pid");
        if ($check['cnt'] == 0) {
            foreach ($packages as [$pname, $price, $api_id]) {
                $db->query("INSERT INTO packages (product_id, name, price, stock, api_service_id, original_price)
                    VALUES ($pid, '" . $db->escape($pname) . "', $price, 999999, $api_id, " . round($price * 0.6) . ")");
            }
        }
        step("✅ FB Product: $name", $log);
    }
}

// ===================================================
// 6. ENSURE TIKTOK PRODUCTS
// ===================================================
$tk_products = [
    ['Tăng FOLLOW TikTok', 'Tăng sub/follow cho tài khoản TikTok', [
        ['Follow TikTok - HQ Global',  45.0, 72],
        ['Follow TikTok - Mix Rẻ',     20.0, 75],
    ]],
    ['Tăng LIKE TikTok', 'Tăng tim/thích cho video TikTok', [
        ['Like TikTok - SV1 (Ổn định)', 15.0, 74],
        ['Like TikTok - HQ Nhanh',      25.0, 76],
    ]],
    ['Tăng VIEW TikTok', 'Tăng lượt xem cho video TikTok', [
        ['View TikTok - Bot Nhanh',     2.5,  80],
        ['View TikTok - Thực Việt',     8.0,  82],
    ]],
    ['Tăng BÌNH LUẬN TikTok', 'Tăng lượt bình luận cho video TikTok', [
        ['Comment TikTok - Mix',       35.0,  88],
    ]],
];

foreach ($tk_products as [$name, $desc, $packages]) {
    $db->query("INSERT INTO products (category_id, name, description, image, status)
        VALUES ($cat_tiktok, '" . $db->escape($name) . "', '" . $db->escape($desc) . "', 'tiktok.png', 'active')
        ON DUPLICATE KEY UPDATE category_id=$cat_tiktok, status='active'");
    $prod = $db->fetch("SELECT id FROM products WHERE name = '" . $db->escape($name) . "' AND category_id = $cat_tiktok");

    if ($prod) {
        $pid = $prod['id'];
        $check = $db->fetch("SELECT COUNT(*) as cnt FROM packages WHERE product_id = $pid");
        if ($check['cnt'] == 0) {
            foreach ($packages as [$pname, $price, $api_id]) {
                $db->query("INSERT INTO packages (product_id, name, price, stock, api_service_id, original_price)
                    VALUES ($pid, '" . $db->escape($pname) . "', $price, 999999, $api_id, " . round($price * 0.6) . ")");
            }
        }
        step("✅ TikTok Product: $name", $log);
    }
}

// ===================================================
// 7. ENSURE ADMIN USER
// ===================================================
$admin = $db->fetch("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
if (!$admin) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("INSERT INTO users (username, email, password, role, balance) 
                VALUES ('admin', 'admin@quyetdev.com', '$pass', 'admin', 1000000)");
    step("✅ Admin user created (admin / admin123)", $log);
} else {
    step("☑️ Admin user already exists (ID: {$admin['id']})", $log);
}

// ===================================================
// 8. VERIFY API CONNECTION
// ===================================================
require_once 'includes/lib/smm_api.php';
try {
    $api = new SMMApi(
        '09ebed9981c95936cc721c1e728a27b5fa5e5066ac7974e6b36cdccd3d9efe86',
        'https://autosub.vn/api/v2'
    );
    $balance_data = $api->getBalance();
    if (isset($balance_data['balance'])) {
        step("✅ Autosub API: OK! Số dư = " . number_format($balance_data['balance']) . " VNĐ", $log);
    } else {
        $err_msg = $balance_data['error'] ?? json_encode($balance_data);
        warn("⚠️ Autosub API: Chưa kết nối - $err_msg", $log);
    }
} catch (Exception $e) {
    warn("⚠️ API Exception: " . $e->getMessage(), $log);
}

// Summary
$ok_count = count(array_filter($log, fn($l) => $l['type'] === 'ok'));
$warn_count = count(array_filter($log, fn($l) => $l['type'] === 'warn'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>QUYETDEV Setup</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Courier New',monospace; background:#0d0f18; color:#e2e8f0; padding:40px; line-height:1.6; }
h1 { color:#a855f7; font-size:1.8rem; margin-bottom:8px; }
.sub { color:#64748b; margin-bottom:30px; font-size:0.9rem; }
.log-item { padding:10px 18px; margin-bottom:6px; border-radius:8px; font-size:0.88rem; border-left:3px solid; }
.ok   { background:rgba(34,197,94,0.06);  border-color:#22c55e; color:#86efac; }
.warn { background:rgba(245,158,11,0.06); border-color:#f59e0b; color:#fde68a; }
.err  { background:rgba(239,68,68,0.06);  border-color:#ef4444; color:#fca5a5; }
.summary { 
    margin-top:30px; padding:24px 28px; 
    background:rgba(168,85,247,0.08); 
    border:1px solid rgba(168,85,247,0.25); border-radius:12px;
    font-size:0.95rem;
}
.summary h2 { color:#a855f7; margin-bottom:15px; font-size:1.1rem; }
.links { display:flex; gap:15px; flex-wrap:wrap; margin-top:20px; }
.links a { 
    padding:10px 22px; border-radius:8px; 
    background:rgba(168,85,247,0.15); color:#c084fc;
    text-decoration:none; font-weight:700; border:1px solid rgba(168,85,247,0.3);
    transition:all 0.2s;
}
.links a:hover { background:rgba(168,85,247,0.3); }
.badge { display:inline-block; padding:2px 8px; border-radius:4px; font-size:0.75rem; font-weight:700; margin-left:8px; }
.b-ok  { background:rgba(34,197,94,0.2);  color:#4ade80; }
.b-warn{ background:rgba(245,158,11,0.2); color:#fbbf24; }
</style>
</head>
<body>
<h1>⚡ QUYETDEV — Full Setup</h1>
<div class="sub">Database setup, product seeding & API verification complete</div>

<?php foreach ($log as $item): ?>
<div class="log-item <?php echo $item['type']; ?>"><?php echo htmlspecialchars($item['msg']); ?></div>
<?php endforeach; ?>

<div class="summary">
    <h2>📊 Setup Summary</h2>
    <span class="badge b-ok"><?php echo $ok_count; ?> OK</span>
    <span class="badge b-warn"><?php echo $warn_count; ?> WARN</span>
    <?php if ($errors): ?>
    <br><br><span style="color:#f87171;">❌ Errors: <?php echo implode(', ', $errors); ?></span>
    <?php else: ?>
    <br><br><span style="color:#4ade80;">✅ Tất cả đã sẵn sàng! Website hoạt động đầy đủ.</span>
    <?php endif; ?>

    <div class="links">
        <a href="index.php">🏠 Trang Chủ</a>
        <a href="social.php">📊 Dịch Vụ MXH</a>
        <a href="products.php">🛒 Sản Phẩm</a>
        <a href="admin/index.php">⚙️ Admin Panel</a>
        <a href="login.php">🔐 Đăng Nhập</a>
    </div>
</div>
</body>
</html>
