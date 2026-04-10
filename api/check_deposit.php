<?php
require_once __DIR__ . '/../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Find the latest transaction that hasn't been notified yet
$transaction = $db->fetch("SELECT id, amount FROM bank_transactions 
                          WHERE user_id = ? AND is_notified = 0 
                          ORDER BY id DESC LIMIT 1", [$user_id]);

if ($transaction) {
    // Mark as notified immediately to prevent duplicate alerts
    $db->query("UPDATE bank_transactions SET is_notified = 1 WHERE id = ?", [$transaction['id']]);
    
    echo json_encode([
        'status' => 'success',
        'amount' => floatval($transaction['amount']),
        'transaction_id' => $transaction['id']
    ]);
} else {
    echo json_encode([
        'status' => 'no_new_deposit'
    ]);
}
?>
