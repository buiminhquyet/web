<?php
/**
 * Automated Banking Callback (Webhook)
 * Handles incoming transaction notifications from services like SePay or Casso.
 */

require_once __DIR__ . '/../includes/config.php';

// --- CONFIGURATION ---
// 1. Authenticate Request
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

// Fetch token from database
$webhook_token = get_setting('bank_webhook_token') ?: 'QUYETDEV_SECURE_TOKEN_2026';

// Support both Bearer token and custom header if needed
if (strpos($authHeader, 'Bearer ') === 0) {
    $token = substr($authHeader, 7);
} else {
    $token = $authHeader;
}

if ($token !== $webhook_token) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// 2. Capture and Parse Payload
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

/* 
  Expected Data Format (Generic):
  - amount: The amount received
  - content: The transfer description/content (e.g. "QUYETDEV 5")
  - referenceCode: The unique transaction ID from the bank
  - gateway: Bank name (optional)
*/

$amountData = $data['amount'] ?? 0;
// Remove any non-numeric characters except for decimal point if it exists as a dot
if (!is_numeric($amountData)) {
    $amountData = str_replace([',', '.'], '', $amountData);
}
$amount = floatval($amountData);

$content = $data['content'] ?? $data['description'] ?? '';
$transaction_id = $data['referenceCode'] ?? $data['id'] ?? '';
$bank_name = $data['gateway'] ?? 'Bank';

if ($amount <= 0 || empty($content) || empty($transaction_id)) {
    echo json_encode(['status' => 'ignore', 'message' => 'Missing data or amount is zero']);
    exit;
}

// 3. Extract User ID using Regex
// Pattern: QUYETDEV [UserID]
// Pattern: QUYETDEV [UserID] - Allow zero or more spaces
if (preg_match('/QUYETDEV\s*(\d+)/i', $content, $matches)) {
    $user_id = intval($matches[1]);
} else {
    file_put_contents(__DIR__ . '/bank_log.txt', date('Y-m-d H:i:s') . " | Pattern mismatch | ID: $transaction_id | Content: $content\n", FILE_APPEND);
    echo json_encode(['status' => 'ignore', 'message' => 'Transaction content does not match pattern']);
    exit;
}

// 4. Process Deposit
try {
    // Check if transaction already processed
    $exists = $db->fetch("SELECT id FROM bank_transactions WHERE transaction_id = ?", [$transaction_id]);
    if ($exists) {
        echo json_encode(['status' => 'ignore', 'message' => 'Transaction already processed']);
        exit;
    }

    // Verify User
    $user = $db->fetch("SELECT id, balance FROM users WHERE id = ?", [$user_id]);
    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    // UPDATE BALANCE & LOG TRANSACTION (Atomic Operation)
    $db->query("START TRANSACTION");

    // Add Balance to user
    $db->query("UPDATE users SET balance = balance + ? WHERE id = ?", [$amount, $user_id]);

    // Affiliate Commission Logic
    $commission_percent = floatval(get_setting('referral_commission_percent') ?: 5);
    $check_ref = $db->fetch("SELECT ref_by FROM users WHERE id = ?", [$user_id]);
    if ($check_ref && !empty($check_ref['ref_by'])) {
        $referrer_id = $check_ref['ref_by'];
        $commission_amount = ($amount * $commission_percent) / 100;
        if ($commission_amount > 0) {
            $db->query("UPDATE users SET balance = balance + ? WHERE id = ?", [$commission_amount, $referrer_id]);
        }
    }

    // Log Transaction
    $db->query("INSERT INTO bank_transactions (user_id, amount, transaction_id, content, bank_name) 
                VALUES (?, ?, ?, ?, ?)", [$user_id, $amount, $transaction_id, $content, $bank_name]);

    $db->query("COMMIT");

    echo json_encode([
        'status' => 'success', 
        'message' => 'Balance updated successfully',
        'user_id' => $user_id,
        'amount' => $amount
    ]);

} catch (Exception $e) {
    if (isset($db)) $db->query("ROLLBACK");
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
