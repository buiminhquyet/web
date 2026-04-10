<?php
ob_start(); // Start output buffering
error_reporting(E_ALL); // Show all errors for debugging (internal)
ini_set('display_errors', 0); // Hide from output

require_once '../config.php';
require_once '../database.php';
require_once '../functions.php';
require_once '../lib/smm_api.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear any accidental output
ob_clean();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng đăng nhập để tiếp tục.']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $db->fetch("SELECT * FROM users WHERE id = '$user_id'");

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'Người dùng không tồn tại.']);
    exit;
}

// Get POST data
$package_id = isset($_POST['package_id']) ? clean($_POST['package_id']) : null;
$link = isset($_POST['link']) ? clean($_POST['link']) : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
$reaction = isset($_POST['reaction']) ? clean($_POST['reaction']) : null;
$comment = isset($_POST['comment']) ? clean($_POST['comment']) : null;



if (!$package_id || empty($link) || $quantity <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Vui lòng điền đầy đủ thông tin.']);
    exit;
}

// Fetch package details
$package = $db->fetch("SELECT p.*, pr.name as product_name FROM packages p JOIN products pr ON p.product_id = pr.id WHERE p.id = '$package_id'");

if (!$package) {
    echo json_encode(['status' => 'error', 'message' => 'Gói dịch vụ không hợp lệ.']);
    exit;
}

$total_price = ceil(($package['price'] * $quantity) / 1000);

// Check user balance
if ($user['balance'] < $total_price) {
    echo json_encode(['status' => 'error', 'message' => 'Số dư không đủ. Vui lòng nạp thêm tiền.']);
    exit;
}

// Place order via API
$api_order_id = null;
$api_log = "Manual process";

if (!empty($package['api_service_id'])) {
    $api_key = get_setting('smm_api_key');
    $api_url = get_setting('smm_api_url');
    
    if ($api_key && $api_url) {
        $api = new SMMApi($api_key, $api_url);
        
        $extraParams = [];
        if ($reaction) {
            $extraParams['reaction'] = $reaction;
        }
        if ($comment) {
            $extraParams['comments'] = $comment;
        }
        
        $response = $api->addOrder($package['api_service_id'], $link, $quantity, $extraParams);


        
        if (isset($response['order'])) {
            $api_order_id = $response['order'];
            $api_log = "API Order Success: " . $api_order_id;
        } else {
            // Handle API error
            $api_error = isset($response['error']) ? $response['error'] : (isset($response['errors']) ? implode(', ', (array)$response['errors']) : 'Unknown API error');
            echo json_encode(['status' => 'error', 'message' => 'Lỗi từ nhà cung cấp: ' . $api_error]);
            exit;
        }
    }
}

// Deduct balance (Atomic SQL to prevent overspending)
$db->query("UPDATE users SET balance = balance - $total_price WHERE id = '$user_id' AND balance >= $total_price");

if ($db->affected_rows() === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Số dư tài khoản không đủ hoặc xảy ra lỗi giao dịch.']);
    exit;
}

$service_data = json_encode([
    'link' => $link,
    'quantity' => $quantity,
    'reaction' => $reaction,
    'comment' => $comment,
    'api_log' => $api_log


], JSON_UNESCAPED_UNICODE);

$db->query("INSERT INTO orders (user_id, package_id, service_data, total_price, status, link, quantity, api_order_id) VALUES 
('$user_id', '$package_id', '$service_data', '$total_price', 'pending', '$link', '$quantity', '$api_order_id')");

echo json_encode([
    'status' => 'success', 
    'message' => 'Đã tạo đơn hàng thành công!',
    'remaining_balance' => $user['balance'] - $total_price
]);
?>
