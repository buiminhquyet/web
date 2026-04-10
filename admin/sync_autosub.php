<?php
/**
 * Autosub.vn Full Dynamic Sync
 * Path: admin/sync_autosub.php
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/lib/smm_api.php';
require_once __DIR__ . '/../includes/functions.php';

$db = new Database();

// Settings
$markup = 1.30; // 30% Profit
$exchange_rate = 25000; // USD to VND Standard Rate

// 1. Fetch Services
$api_key = get_setting('smm_api_key');
$api_url = get_setting('smm_api_url');
if (!$api_key || !$api_url) {
    die("API settings missing.");
}

$api = new SMMApi($api_key, $api_url);
$services = $api->getServices();

if (!$services || !is_array($services)) {
    die("Failed to fetch services: " . json_encode($services));
}

// 2. Clear ONLY API-related Data
$db->query("DELETE FROM packages WHERE api_service_id IS NOT NULL");
// We stop deleting products/categories to preserve manual entries like Netflix/Canva.

echo "Old data cleared. Syncing " . count($services) . " services...\n";

// 3. Sync
$platforms = [];
$products = [];

foreach ($services as $srv) {
    $id = $srv['service'];
    $name = $srv['name'];
    $cat_name = $srv['category'];
    $rate = floatval($srv['rate']);
    $type = $srv['type'] ?? 'Default';


    // Detect Platform
    $plt = 'Other';
    if (stripos($cat_name, 'Facebook') !== false || stripos($name, 'Facebook') !== false || stripos($cat_name, 'fb') !== false) $plt = 'Facebook';
    elseif (stripos($cat_name, 'TikTok') !== false || stripos($name, 'TikTok') !== false) $plt = 'TikTok';
    elseif (stripos($cat_name, 'Instagram') !== false || stripos($name, 'Instagram') !== false) $plt = 'Instagram';
    elseif (stripos($cat_name, 'Youtube') !== false || stripos($name, 'Youtube') !== false) $plt = 'Youtube';
    elseif (stripos($cat_name, 'Telegram') !== false || stripos($name, 'Telegram') !== false) $plt = 'Telegram';
    elseif (stripos($cat_name, 'Twitter') !== false || stripos($cat_name, ' X ') !== false) $plt = 'Twitter';
    elseif (stripos($cat_name, 'Thread') !== false) $plt = 'Thread';
    elseif (stripos($cat_name, 'Google') !== false) $plt = 'Google';
    elseif (stripos($cat_name, 'Shopee') !== false) $plt = 'Shopee';

    // Create Category (Platform) if not exists
    if (!isset($platforms[$plt])) {
        $icon = 'fas fa-share-alt';
        if ($plt === 'Facebook') $icon = 'fab fa-facebook';
        elseif ($plt === 'TikTok') $icon = 'fab fa-tiktok';
        elseif ($plt === 'Instagram') $icon = 'fab fa-instagram';
        elseif ($plt === 'Youtube') $icon = 'fab fa-youtube';
        elseif ($plt === 'Telegram') $icon = 'fab fa-telegram';
        elseif ($plt === 'Twitter') $icon = 'fab fa-twitter';
        elseif ($plt === 'Google') $icon = 'fab fa-google';
        
        $db->query("INSERT INTO categories (name, icon) VALUES ('$plt', '$icon') ON DUPLICATE KEY UPDATE name='$plt'");
        $platforms[$plt] = $db->insert_id() ?: $db->fetch("SELECT id FROM categories WHERE name='$plt'")['id'];
    }
    $cat_id = $platforms[$plt];

    // Clean Product Name (Use API Category as Product Name for better grouping)
    $prod_name = $cat_name; 
    
    // Create Product if not exists
    $prod_key = $cat_id . '_' . $prod_name;
    if (!isset($products[$prod_key])) {
        $db->query("INSERT INTO products (category_id, name, status) VALUES ($cat_id, '" . $db->escape($prod_name) . "', 'active')");
        $products[$prod_key] = $db->insert_id() ?: $db->fetch("SELECT id FROM products WHERE category_id=$cat_id AND name='" . $db->escape($prod_name) . "'")['id'];
    }
    $prod_id = $products[$prod_key];

    // Insert Package
    $price = round($rate * $exchange_rate * $markup, 0); // Round to nearest VND
    $safe_name = $db->escape($name);
    $db->query("INSERT INTO packages (product_id, name, price, api_service_id, service_type, original_price, stock) VALUES 
    ($prod_id, '$safe_name', $price, $id, '$type', $rate, 9999999)");

}

echo "Sync completed successfully!";
