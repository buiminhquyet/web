<?php
/**
 * Smart Automated 24/7 Price Sync for Autosub.vn
 * Path: cron/sync.php
 * Use: php cron/sync.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/lib/smm_api.php';
require_once __DIR__ . '/../includes/functions.php';

$db = new Database();

// Config (Can be moved to DB settings later)
$markup = 1.30; // 30% Profit
$exchange_rate = 25000; // API Rate is usually in a base unit, this converts it to VND

// Fetch API Settings
$api_key = get_setting('smm_api_key');
$api_url = get_setting('smm_api_url') ?: 'https://autosub.vn/api/v2';

if (!$api_key) {
    die("Error: SMM API Key is not configured in settings.\n");
}

echo "[" . date('Y-m-d H:i:s') . "] Starting automated sync...\n";

$api = new SMMApi($api_key, $api_url);
$services = $api->getServices();

if (!$services || !is_array($services)) {
    die("Error: Failed to fetch services from API. Response: " . json_encode($services) . "\n");
}

$updated_count = 0;
$new_count = 0;
$platforms = [];
$products_map = []; // Store product IDs by platform + name

// Pre-load existing categories and products into memory for speed
$cat_res = $db->query("SELECT id, name FROM categories");
while($c = $cat_res->fetch_assoc()) $platforms[$c['name']] = $c['id'];

$prod_res = $db->query("SELECT id, category_id, name FROM products");
while($p = $prod_res->fetch_assoc()) $products_map[$p['category_id'] . '_' . $p['name']] = $p['id'];

foreach ($services as $srv) {
    if (!isset($srv['service'])) continue;

    $api_id = $srv['service'];
    $name = $srv['name'];
    $cat_name = $srv['category'];
    $rate = floatval($srv['rate']);
    $type = $srv['type'] ?? 'Default';
    
    // Calculate your selling price
    $new_price = round($rate * $exchange_rate * $markup, 0);

    // Check if this package exists
    $existing = $db->fetch("SELECT id, original_price, price FROM packages WHERE api_service_id = '$api_id'");

    if ($existing) {
        // UPDATE Existing
        if ($existing['original_price'] != $rate) {
            $db->query("UPDATE packages SET 
                        original_price = '$rate', 
                        price = '$new_price', 
                        status = 'active' 
                        WHERE id = '" . $existing['id'] . "'");
            $updated_count++;
        }
    } else {
        // INSERT New
        
        // 1. Determine Platform (Category)
        $plt = 'Other';
        if (stripos($cat_name, 'Facebook') !== false || stripos($name, 'Facebook') !== false) $plt = 'Facebook';
        elseif (stripos($cat_name, 'TikTok') !== false || stripos($name, 'TikTok') !== false) $plt = 'TikTok';
        elseif (stripos($cat_name, 'Instagram') !== false || stripos($name, 'Instagram') !== false) $plt = 'Instagram';
        elseif (stripos($cat_name, 'Youtube') !== false || stripos($name, 'Youtube') !== false) $plt = 'Youtube';
        elseif (stripos($cat_name, 'Telegram') !== false || stripos($name, 'Telegram') !== false) $plt = 'Telegram';
        
        if (!isset($platforms[$plt])) {
            $icon = 'fas fa-share-alt';
            if ($plt === 'Facebook') $icon = 'fab fa-facebook';
            elseif ($plt === 'TikTok') $icon = 'fab fa-tiktok';
            elseif ($plt === 'Instagram') $icon = 'fab fa-instagram';
            $db->query("INSERT INTO categories (name, icon) VALUES ('$plt', '$icon')");
            $platforms[$plt] = $db->insert_id();
        }
        $cat_id = $platforms[$plt];

        // 2. Determine Product grouping
        $prod_name = $cat_name;
        $prod_key = $cat_id . '_' . $prod_name;
        
        if (!isset($products_map[$prod_key])) {
            $db->query("INSERT INTO products (category_id, name, status) VALUES ($cat_id, '" . $db->escape($prod_name) . "', 'active')");
            $products_map[$prod_key] = $db->insert_id();
        }
        $prod_id = $products_map[$prod_key];

        // 3. Insert Package
        $db->query("INSERT INTO packages (product_id, name, price, api_service_id, service_type, original_price, stock, status) VALUES 
                    ($prod_id, '" . $db->escape($name) . "', '$new_price', '$api_id', '$type', '$rate', 999999, 'active')");
        $new_count++;
    }
}

echo "Sync Finished!\n";
echo "- Updated: $updated_count packages\n";
echo "- New Added: $new_count packages\n";
echo "----------------------------------------\n";
?>
