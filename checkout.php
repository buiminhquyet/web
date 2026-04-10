<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    alert('error', 'Vui lòng đăng nhập để thanh toán.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $package_id = clean($_POST['package_id']);
    $service_data = clean($_POST['service_data']);

    // Fetch package details
    $package = $db->fetch("SELECT * FROM packages WHERE id = '$package_id'");
    if (!$package) {
        alert('error', 'Gói dịch vụ không hợp lệ.');
        redirect('products.php');
    }

    if ($package['stock'] <= 0) {
        alert('error', 'Sản phẩm này hiện đang hết hàng.');
        redirect('product_detail.php?id=' . $package['product_id']);
    }

    // Fetch user balance
    $user = get_user($user_id);
    $total_price = $package['price'];

    if ($user['balance'] < $total_price) {
        alert('error', 'Số dư tài khoản không đủ. Vui lòng nạp thêm tiền.');
        redirect('deposit.php');
    }

    // Processing Purchase
    $db->query("START TRANSACTION");

    // 1. Deduct balance (Atomic SQL to prevent overspending)
    $db->query("UPDATE users SET balance = balance - $total_price WHERE id = '$user_id' AND balance >= $total_price");
    
    if ($db->affected_rows() === 0) {
        $db->query("ROLLBACK");
        alert('error', 'Số dư tài khoản không đủ hoặc xảy ra lỗi giao dịch.');
        redirect('deposit.php');
    }
    
    // Refresh the user data for notification and session
    $user = get_user($user_id);
    $new_balance = $user['balance'];

    // 2. Handle Stock and Account Delivery
    $recipient_email = $service_data; // Form entered email
    $delivered_account = "Chờ bàn giao (Vui lòng liên hệ Admin)"; 
    
    if (!empty(trim($package['stock_content']))) {
        $lines = explode("\n", str_replace("\r", "", trim($package['stock_content'])));
        if (count($lines) > 0) {
            $delivered_account = trim(array_shift($lines)); // Pop first line
            $new_stock_content = implode("\n", $lines);
            $new_count = count($lines);
            $db->query("UPDATE packages SET stock = '$new_count', stock_content = '" . $db->escape($new_stock_content) . "' WHERE id = '$package_id'");
        }
    } else {
        $db->query("UPDATE packages SET stock = stock - 1 WHERE id = '$package_id'");
    }

    // 3. Create order (Store both recipient and info in service_data)
    $log_data = "Gmail: {$recipient_email} | Info: {$delivered_account}";
    $db->query("INSERT INTO orders (user_id, package_id, service_data, total_price, status) 
                VALUES ('$user_id', '$package_id', '" . $db->escape($log_data) . "', '$total_price', 'completed')");

    $db->query("COMMIT");

    // Send Telegram Notification
    $order_id = $db->query("SELECT LAST_INSERT_ID() as last_id")->fetch_assoc()['last_id'];
    $tg_msg = "<b>🔔 ĐƠN HÀNG MỚI #{$order_id}</b>\n";
    $tg_msg .= "━━━━━━━━━━━━━━━━━━\n";
    $tg_msg .= "👤 Khách hàng: <b>{$user['username']}</b>\n";
    $tg_msg .= "📦 Sản phẩm: <b>{$package['name']}</b>\n";
    $tg_msg .= "💰 Tổng tiền: <b>" . format_currency($total_price) . "</b>\n";
    $tg_msg .= "🎯 Nhận tại: <code>{$recipient_email}</code>\n";
    $tg_msg .= "📧 Dữ liệu: <code>{$delivered_account}</code>\n";
    $tg_msg .= "━━━━━━━━━━━━━━━━━━\n";
    $tg_msg .= "⏰ Thời gian: " . date('d/m/Y H:i:s');
    
    sendTelegramNotification($tg_msg);
    
    // SEND EMAIL NOTIFICATION TO TARGET GMAIL
    if (!empty($recipient_email)) {
        $subject = "Thông tin tài khoản đơn hàng #{$order_id} - " . (get_setting('site_name') ?? 'QUYETDEV Shop');
        
        $email_body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #6366f1, #a855f7); padding: 25px; text-align: center; color: white;'>
                <h2 style='margin: 0;'>BÀN GIAO TÀI KHOẢN THÀNH CÔNG!</h2>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đây là thông tin đơn hàng #{$order_id}</p>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #334155;'>
                <p>Chào bạn,</p>
                <p>Cảm ơn bạn đã lựa chọn dịch vụ của chúng tôi. Thông tin tài khoản của bạn đã sẵn sàng:</p>
                <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px dashed #cbd5e1;'>
                    <p style='margin: 0 0 10px;'>📦 Sản phẩm: <strong>{$package['name']}</strong></p>
                    <p style='margin: 0 0 10px;'>💰 Tổng tiền: <strong style='color: #ef4444;'>" . format_currency($total_price) . "</strong></p>
                    <p style='margin: 0;'>🛡️ <strong>THÔNG TIN TÀI KHOẢN:</strong></p>
                    <div style='background: #fff; border: 1px solid #e2e8f0; padding: 15px; margin-top: 10px; border-radius: 6px; font-family: monospace; font-size: 1.2rem; color: #6366f1; text-align: center; font-weight: bold;'>
                        " . nl2br(htmlspecialchars($delivered_account)) . "
                    </div>
                </div>
                <p style='font-size: 0.85rem; color: #64748b;'><strong>Lưu ý:</strong> Vui lòng đổi mật khẩu (nếu gói cho phép) để bảo mật tài khoản tốt nhất.</p>
                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                <p style='text-align: center; font-size: 0.8rem; color: #94a3b8;'>Hệ thống " . (get_setting('site_name') ?? 'QUYETDEV') . " trân trọng cảm ơn!</p>
            </div>
        </div>";
        
        sendEmail($recipient_email, $subject, $email_body);
    }

    // Update session balance
    $_SESSION['balance'] = $new_balance;

    alert('success', 'Thanh toán thành công! Đơn hàng đang được xử lý.');
    redirect('profile.php');
} else {
    redirect('index.php');
}
?>
