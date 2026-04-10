<?php
/**
 * PRIVATE BANK API - MOBILE TO WEB BRIDGE
 * This script receives raw notification text from your Android phone
 * and automatically processes top-ups.
 */

require_once __DIR__ . '/../includes/config.php';

// --- CONFIGURATION ---
// You will set this token in your Mobile Forwarder App as a header or body param
define('PRIVATE_TOKEN', 'QUYET_PRIVATE_API_SECURE_7788');
// --- END CONFIGURATION ---

// 1. Authenticate Request
$headers = getallheaders();
$receivedToken = $headers['X-Private-Token'] ?? $_POST['token'] ?? '';

if ($receivedToken !== PRIVATE_TOKEN) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized access.');
}

// 2. Capture Notification Data
// Mobile apps usually send 'title' and 'content' of the notification
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? $_POST['text'] ?? '';

if (empty($content)) {
    exit('No content to process.');
}

// 3. Logic: Extract Amount and User ID
// Support Bank (MB) and ZaloPay formats
// Bank (MB): "MB: +50,000VND tai MB... ND: QUYETDEV 2"
// ZaloPay: "Bạn vừa nhận được 50.000đ từ... ND: QUYETDEV 2"

// Extract Amount (Flexible regex for +x,xxx or "nhận được x.xxx")
$amount = 0;
// Pattern 1: Look for +10,000 or +10.000 (Common in Bank SMS/Push)
if (preg_match('/[+]([\d,.]+)/', $content, $matches)) {
    $amountStr = str_replace([',', '.'], '', $matches[1]);
    $amount = floatval($amountStr);
} 
// Pattern 2: Look for "nhận được 10.000" or "nhận 10,000" (Common in ZaloPay/Momo)
elseif (preg_match('/(?:nhận|nạp|cộng)\s+(?:được\s+)?([\d,.]+)/ui', $content, $matches)) {
    $amountStr = str_replace([',', '.'], '', $matches[1]);
    $amount = floatval($amountStr);
}

// Extract User ID (Pattern: QUYETDEV [UserID])
$user_id = 0;
if (preg_match('/QUYETDEV\s*(\d+)/i', $content, $matches)) {
    $user_id = intval($matches[1]);
}

if ($amount <= 0 || $user_id <= 0) {
    // Log ignored notification for debugging
    file_put_contents('log_ignored.txt', date('Y-m-d H:i:s') . " | Content: $content\n", FILE_APPEND);
    exit('Amount or User ID not found in content.');
}

// 4. Process Deposit
try {
    // Generate a unique ID for this transaction to avoid duplicates
    // We use a hash of the content and time to prevent same-second double-processing
    $transaction_hash = md5($content);

    // Check if hash already exists in a dedicated log or the transactions table
    $exists = $db->fetch("SELECT id FROM bank_transactions WHERE transaction_id = 'HASH_$transaction_hash'");
    if ($exists) {
        exit('Transaction already processed.');
    }

    // Verify User
    $user = $db->fetch("SELECT id FROM users WHERE id = '$user_id'");
    if (!$user) {
        exit('User ID not found in database.');
    }

    $db->query("START TRANSACTION");

    // Add Balance
    $db->query("UPDATE users SET balance = balance + $amount WHERE id = '$user_id'");

    // Log Transaction (We use the hash as a temporary ID if the bank doesn't provide one in the notification)
    $db->query("INSERT INTO bank_transactions (user_id, amount, transaction_id, content, bank_name) 
                VALUES ('$user_id', '$amount', 'HASH_$transaction_hash', '$content', 'Mobile_Push')");

    $db->query("COMMIT");

    echo "SUCCESS: Added $amount to User #$user_id";

} catch (Exception $e) {
    if (isset($db)) $db->query("ROLLBACK");
    header('HTTP/1.1 500 Internal Server Error');
    exit('Error: ' . $e->getMessage());
}
