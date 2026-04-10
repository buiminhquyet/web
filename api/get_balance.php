<?php
/**
 * Fast Balance Check API
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user = $db->fetch("SELECT balance FROM users WHERE id = ?", [$user_id]);

if ($user) {
    // Sync session balance if different
    $_SESSION['balance'] = $user['balance'];
    
    echo json_encode([
        'status' => 'success',
        'balance' => floatval($user['balance']),
        'formatted_balance' => number_format($user['balance']) . 'đ'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}
?>
