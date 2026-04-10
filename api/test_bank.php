<?php
/**
 * TEST SCRIPT FOR PRIVATE BANK API
 * Use this to simulate a bank notification and verify the top-up logic.
 */

$callbackUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/bank_callback_private.php';
$token = 'QUYET_PRIVATE_API_SECURE_7788';

if (isset($_POST['simulate'])) {
    $testContent = $_POST['content'] ?? 'MB: +100,000VND tai MB... ND: QUYETDEV 2';
    
    // Send POST request via cURL
    $ch = curl_init($callbackUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'token' => $token,
        'content' => $testContent
    ]));
    
    $result = curl_exec($ch);
    curl_close($ch);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giả lập Nạp Tiền - QUYETDEV</title>
    <style>
        body { font-family: sans-serif; padding: 50px; background: #f4f7fb; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        h2 { color: #7c3aed; }
        textarea { width: 100%; height: 80px; margin: 15px 0; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
        button { background: #7c3aed; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; }
        .result { margin-top: 20px; padding: 15px; background: #eef2ff; border-radius: 8px; font-family: monospace; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Giả Lập Nạp Tiền Bank</h2>
        <p>Sử dụng công cụ này để kiểm tra xem hệ thống có nhận diện đúng số tiền và ID người dùng không.</p>
        
        <form method="POST">
            <label>Giả lập Thông báo Ngân hàng:</label>
            <textarea name="content">MB: +50,000VND tai MB. ND: QUYETDEV 2</textarea>
            <button type="submit" name="simulate">Test Nạp Bank</button>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <label>Giả lập Thông báo ZaloPay:</label>
            <textarea name="content">Bạn vừa nhận được 100.000đ từ NGUYEN VAN A. ND: QUYETDEV 2</textarea>
            <button type="submit" name="simulate" style="background: #0085FF;">Test Nạp ZaloPay</button>
        </form>

        <?php if (isset($result)): ?>
            <div class="result">
                <strong>Kết quả từ Server:</strong><br>
                <?php echo htmlspecialchars($result); ?>
            </div>
            <p style="font-size: 0.8rem; margin-top: 10px; color: #666;">
                * Nếu kết quả hiện SUCCESS, hãy kiểm tra lại số dư ID 2 trong Database!
            </p>
        <?php endif; ?>
    </div>
</body>
</html>
