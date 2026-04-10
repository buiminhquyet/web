<?php
require_once '../config.php';

header('Content-Type: application/json');

/**
 * API: Get Recent Orders for Real-time Notifications
 */

// If settings has a specific anonymization style, use it (future proof)
function mask_username($name) {
    if (empty($name)) return 'Guest';
    $len = strlen($name);
    if ($len <= 2) {
        return substr($name, 0, 1) . '***';
    }
    return substr($name, 0, 1) . '***' . substr($name, -1);
}

$notifications = [];

// Fetch real data from DB
$query = "SELECT u.username, p.name as package_name, o.created_at 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          JOIN packages p ON o.package_id = p.id 
          WHERE o.status = 'completed' 
          ORDER BY o.created_at DESC 
          LIMIT 10";

$res = $db->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $notifications[] = [
            'user' => mask_username($row['username']),
            'package' => $row['package_name'],
            'time' => $row['created_at'],
            'is_real' => true
        ];
    }
}

// If zero or low real data, add realistic seed data to make the site look active
if (count($notifications) < 5) {
    $seeds = [
        ['user' => 'q***v', 'package' => 'Tăng Follow TikTok (Gói VIP)', 'time' => date('Y-m-d H:i:s', strtotime('-5 minutes')), 'is_real' => false],
        ['user' => 'n***9', 'package' => 'Like Facebook Tốc độ cao', 'time' => date('Y-m-d H:i:s', strtotime('-12 minutes')), 'is_real' => false],
        ['user' => 'm***h', 'package' => 'Mắt Xem Livestream v2', 'time' => date('Y-m-d H:i:s', strtotime('-25 minutes')), 'is_real' => false],
        ['user' => 'v***p', 'package' => 'Sub YouTube chất lượng', 'time' => date('Y-m-d H:i:s', strtotime('-45 minutes')), 'is_real' => false],
        ['user' => 'h***1', 'package' => 'Tài khoản Netflix Premium', 'time' => date('Y-m-d H:i:s', strtotime('-1 hour')), 'is_real' => false],
    ];
    $notifications = array_merge($notifications, $seeds);
}

shuffle($notifications); // Make it more natural

echo json_encode([
    'status' => 'success',
    'data' => $notifications
]);
