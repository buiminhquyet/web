<?php
ob_start();
/**
 * QUYETDEV Shop Configuration
 */
header('Content-Type: text/html; charset=utf-8');

// Load Security Layer
require_once __DIR__ . '/security.php';

// Load Environment Variables
require_once __DIR__ . '/env_helper.php';

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'webfl'));

// App Configuration
define('SITE_PATH', dirname(__DIR__));

// Start Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Global Referral Capture
if (!empty($_GET['ref'])) {
    $ref_user = trim($_GET['ref']);
    // Store in cookie for 30 days
    setcookie('ref_user', $ref_user, time() + (86400 * 30), "/");
}

/**
 * Autoload function or regular includes
 */
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

// Load Global Settings
$settings = [];
$db = new Database();
$res = $db->query("SELECT * FROM settings");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $k = isset($row['s_key']) ? $row['s_key'] : (isset($row['key']) ? $row['key'] : null);
        $v = isset($row['s_value']) ? $row['s_value'] : (isset($row['value']) ? $row['value'] : null);
        if ($k) $settings[$k] = $v;
    }
}

// Define SITE_URL dynamically (fallback or from DB)
$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!empty($settings['site_url'])) {
    $site_url = $settings['site_url'];
} else {
    $site_url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $http_host;
    // If we are on localhost, add the subdirectory if it exists
    if ($http_host == 'localhost') $site_url .= '/webfl';
}

define('SITE_URL', rtrim($site_url, '/'));
?>
