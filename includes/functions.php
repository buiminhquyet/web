<?php
/**
 * Global Functions
 */

function redirect($url) {
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    
    global $db;
    $user_id = $_SESSION['user_id'];
    $u = $db->fetch("SELECT role FROM users WHERE id = '$user_id'");
    
    if ($u && $u['role'] === 'admin') {
        $_SESSION['role'] = 'admin'; // Sync session
        return true;
    }
    
    return false;
}

function format_currency($amount) {
    return number_format($amount, 0, ',', '.') . ' ₫';
}

function clean($str) {
    global $db;
    return $db->escape(trim($str));
}

function get_user($user_id) {
    global $db;
    return $db->fetch("SELECT * FROM users WHERE id = '$user_id'");
}

function alert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type, // success, error, info
        'message' => $message
    ];
}

function get_setting($key) {
    global $db;
    $res = $db->fetch("SELECT s_value FROM settings WHERE s_key = '$key'");
    return $res ? $res['s_value'] : null;
}

function detectPlatform($prod_name, $cat_name) {
    $n = strtolower($prod_name . ' ' . $cat_name);
    if (strpos($n, 'facebook') !== false || strpos($n, 'fb') !== false) {
        if (strpos($n, 'vip') !== false) return 'Facebook VIP';
        return 'Facebook';
    }
    if (strpos($n, 'tiktok') !== false) return 'TikTok';
    if (strpos($n, 'instagram') !== false) return 'Instagram';
    if (strpos($n, 'youtube') !== false) return 'Youtube';
    if (strpos($n, 'shopee') !== false) return 'Shopee';
    if (strpos($n, 'telegram') !== false) return 'Telegram';
    if (strpos($n, 'twitter') !== false || strpos($n, 'x.com') !== false) return 'Twitter';
    if (strpos($n, 'thread') !== false) return 'Thread';
    if (strpos($n, 'bigo') !== false) return 'Bigo Live';
    if (strpos($n, 'google') !== false) return 'Google';
    return $cat_name;
}

/**
 * Renders a product image with an automatic letter-based fallback if the file is missing.
 */
function render_product_image($image_name, $product_name, $size = '70px', $font_size = '1.8rem') {
    // Determine the relative and absolute paths for checking file existence
    $img_path = $image_name;
    
    // Check in both the subdirectory and the main assets/images folder
    $abs_path = SITE_PATH . '/assets/images/' . $image_name;
    $img_url = SITE_URL . '/assets/images/' . $image_name;
    
    if (!empty($image_name) && file_exists($abs_path)) {
        return '<img src="' . $img_url . '" alt="' . htmlspecialchars($product_name) . '" style="width:100%;height:100%;object-fit:cover;">';
    } else {
        // High-quality fallback to the new default_product.png
        $default_url = SITE_URL . '/assets/images/default_product.png';
        $default_path = SITE_PATH . '/assets/images/default_product.png';
        
        if (file_exists($default_path)) {
            return '<img src="' . $default_url . '" alt="Default" style="width:100%;height:100%;object-fit:cover;opacity:0.8;">';
        }
        
        // Final fallback to letter icon if even the default is missing
        $letter = !empty($product_name) ? strtoupper(substr($product_name, 0, 1)) : '?';
        return '<div class="logo-icon" style="width: ' . $size . '; height: ' . $size . '; font-size: ' . $font_size . ';">' . $letter . '</div>';
    }
}

function sendTelegramNotification($message) {
    global $db;
    $token = get_setting('telegram_bot_token');
    $chat_id = get_setting('telegram_chat_id');
    
    if (empty($token) || empty($chat_id)) return false;
    
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

function sendEmail($to, $subject, $body) {
    global $db;
    require_once 'SmtpClient.php';
    
    $host = get_setting('smtp_host');
    $port = get_setting('smtp_port');
    $user = get_setting('smtp_user');
    $pass = get_setting('smtp_pass');
    $secure = get_setting('smtp_secure');
    
    if (!$host || !$user || !$pass) return "SMTP Not Configured";
    
    $site_name = get_setting('site_name') ?? 'QUYETDEV Shop';
    $mailer = new SmtpClient($host, $port, $user, $pass, $secure);
    return $mailer->send($to, $subject, $body, $site_name);
}

/**
 * Formats raw service data (often JSON) into a human-readable and professional format.
 */
function format_service_data($data) {
    if (empty($data)) return '---';
    $json = json_decode($data, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
        if (isset($json['link'])) return '<a href="'.$json['link'].'" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 700;"><i class="fas fa-external-link-alt"></i> Link mục tiêu</a>';
        if (isset($json['url'])) return '<a href="'.$json['url'].'" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 700;"><i class="fas fa-external-link-alt"></i> Link mục tiêu</a>';
        
        // Format other JSON objects elegantly
        $parts = [];
        foreach ($json as $k => $v) {
            if (is_array($v)) continue;
            $parts[] = '<span style="opacity: 0.5; font-size: 0.75rem;">'.ucfirst($k).':</span> ' . htmlspecialchars($v);
        }
        return implode('<br>', $parts);
    }
    return htmlspecialchars($data);
}
?>
