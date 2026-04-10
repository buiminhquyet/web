<?php
/**
 * Global Security Layer (QUYETDEV Defense)
 * Handles Rate Limiting, Session Security, and Global Filters
 */

// 1. Disable Error Display for Users (Log to file instead)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 2. Session Security & Stability (1-Hour Timeout)
// Set session lifetime to 3600 seconds (1 hour)
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure User Agent is tracked but don't destroy session aggressively
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

// 3. Rate Limiting - Disabled (Handled by Cloudflare)
/** 
 * Rate limiting logic removed to optimize performance and prevent accidental logouts. 
 * The system now relies on Cloudflare's high-level security infrastructure.
 */

// 4. Global Input Filtering (XSS Protection)
function xss_clean($data) {
    if (is_array($data)) {
        return array_map('xss_clean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// 5. Secure Redirect Middleman
function secure_redirect($type) {
    return SITE_URL . "/go.php?to=" . urlencode(base64_encode($type));
}
?>
