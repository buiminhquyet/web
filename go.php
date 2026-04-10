<?php
/**
 * Secure Redirect Handler (QUYETDEV Defense)
 * Masks sensitive URLs from the frontend
 */
require_once 'includes/config.php';

$to = $_GET['to'] ?? '';
if (empty($to)) {
    header("Location: " . SITE_URL);
    exit();
}

// Decode the target
$target = base64_decode($to);
$final_url = "";

switch ($target) {
    case 'zalo':
        $raw = get_setting('zalo_link');
        $final_url = !empty($raw) && !filter_var($raw, FILTER_VALIDATE_URL) ? 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $raw) : $raw;
        break;
    case 'facebook':
        $raw = get_setting('facebook_link');
        if (!empty($raw) && !filter_var($raw, FILTER_VALIDATE_URL)) {
            $final_url = 'https://facebook.com/' . $raw;
        } else {
            $final_url = $raw;
        }
        break;
    case 'telegram':
        $final_url = get_setting('telegram_link');
        break;
    case 'website':
        $final_url = get_setting('website_redirect_url');
        break;
    default:
        // Attempt to treat as a direct URL if safe
        if (filter_var($target, FILTER_VALIDATE_URL)) {
            $final_url = $target;
        } else {
            $final_url = SITE_URL;
        }
        break;
}

if (empty($final_url) || $final_url == '#') {
    $final_url = SITE_URL;
}

// SMART PROTOCOL FIX: Ensure URLs have http/https to prevent relative path errors
if ($final_url !== SITE_URL && !preg_match("~^(?:f|ht)tps?://~i", $final_url)) {
    $final_url = "https://" . $final_url;
}

// High-level security: No-referer header
header("Referrer-Policy: no-referrer");
header("Location: " . $final_url);
exit();
?>
