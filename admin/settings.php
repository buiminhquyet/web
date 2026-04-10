<?php 
include 'header.php'; 

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Regular settings update
    foreach ($_POST as $key => $value) {
        if ($key == 'save_settings' || !is_string($value)) continue;
        
        $key = clean($key);
        $value = trim($value);

        // Skip updating sensitive fields if they contain masking placeholders
        if (strpos($value, '********') !== false && 
            (strpos($key, 'key') !== false || strpos($key, 'token') !== false || strpos($key, 'pass') !== false)) {
            continue; 
        }

        // Special cleaning for URLs
        $final_value = (strpos($key, 'link') !== false || strpos($key, 'url') !== false) ? $value : clean($value);
        
        $db->query("INSERT INTO settings (s_key, s_value) VALUES (?, ?) 
                    ON DUPLICATE KEY UPDATE s_value = ?", [$key, $final_value, $final_value]);
    }

    // Handle File Uploads for QR Codes
    $upload_dir = '../assets/images/qr/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['bank_qr']) && $_FILES['bank_qr']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['bank_qr']['name'], PATHINFO_EXTENSION);
        $filename = 'bank_qr_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['bank_qr']['tmp_name'], $upload_dir . $filename)) {
            $path = 'assets/images/qr/' . $filename;
            $db->query("UPDATE settings SET `s_value` = '$path' WHERE `s_key` = 'bank_qr'");
        }
    }

    if (isset($_FILES['zalopay_qr']) && $_FILES['zalopay_qr']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['zalopay_qr']['name'], PATHINFO_EXTENSION);
        $filename = 'zalopay_qr_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['zalopay_qr']['tmp_name'], $upload_dir . $filename)) {
            $path = 'assets/images/qr/' . $filename;
            $db->query("UPDATE settings SET `s_value` = '$path' WHERE `s_key` = 'zalopay_qr'");
        }
    }

    alert('success', 'Đã cập nhật cài đặt hệ thống thành công!');
    redirect('admin/settings.php');
}

// Fetch all settings
$res = $db->query("SELECT * FROM settings");
$current_settings = [];
while($row = $res->fetch_assoc()) {
    $current_settings[$row['s_key']] = $row['s_value'];
}

// Function to mask sensitive strings for display
function mask_sensitive($str) {
    if (empty($str)) return '';
    if (strlen($str) <= 8) return '********';
    return substr($str, 0, 4) . '********' . substr($str, -4);
}
?>

<div class="glass-card" style="padding: 40px; max-width: 800px; margin: 0 auto; border-radius: 24px;">
    <h3 style="margin-bottom: 35px; font-weight: 800; letter-spacing: -1px;"><i class="fas fa-tools text-gradient" style="margin-right: 10px;"></i> Cài Đặt Hệ Thống</h3>

    <form action="settings.php" method="POST" enctype="multipart/form-data">
        <div class="admin-grid">
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Tên Thương Hiệu</label>
                <input type="text" name="site_name" value="<?php echo $current_settings['site_name']; ?>" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Tên Hiển Thị Logo</label>
                <input type="text" name="site_logo" value="<?php echo $current_settings['site_logo']; ?>" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
            </div>

            <div style="margin-bottom: 25px; grid-column: span 2;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 1; color: #3b82f6;">
                    <i class="fas fa-globe"></i> Tên Miền Hệ Thống (URL)
                </label>
                <input type="text" name="site_url" value="<?php echo $current_settings['site_url'] ?? ''; ?>" placeholder="Ví dụ: https://quyetdev.shop" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid #3b82f6; background: rgba(59, 130, 246, 0.05); color: #333; outline: none;">
                <p style="font-size: 0.75rem; opacity: 0.6; margin-top: 5px;"><i class="fas fa-info-circle"></i> Để trống nếu muốn hệ thống tự động nhận diện theo tên miền hiện tại.</p>
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Thông Báo Chạy (Topbar)</label>
            <textarea name="announcement" rows="3" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none; transition: var(--transition);"><?php echo $current_settings['announcement']; ?></textarea>
        </div>


        <div style="padding: 20px; border-radius: 20px; background: rgba(59, 130, 246, 0.05); margin-bottom: 35px; border: 1px dashed #3b82f6;">
            <div class="section-header-flex">
                <h4 style="color: #3b82f6; margin: 0;"><i class="fas fa-network-wired"></i> Tích Hợp SMM API</h4>
                <button type="submit" class="quick-save-btn" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">API Domain (URL)</label>
                <input type="text" name="smm_api_url" value="<?php echo $current_settings['smm_api_url']; ?>" placeholder="https://autosub.vn/api/v2" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">API Key</label>
                <div style="position: relative;">
                    <input type="password" name="smm_api_key" id="smm_api_key" value="<?php echo mask_sensitive($current_settings['smm_api_key']); ?>" placeholder="Nhập khóa API mới để thay đổi" style="width: 100%; padding: 12px 45px 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
                    <button type="button" onclick="togglePass('smm_api_key')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                        <i class="fas fa-eye-slash" id="smm_api_key_icon"></i>
                    </button>
                </div>
            </div>
            <?php 
                require_once '../includes/lib/smm_api.php';
                $api = new SMMApi($current_settings['smm_api_key'], $current_settings['smm_api_url']);
                $balance = $api->getBalance();
                $cur = isset($balance['currency']) ? $balance['currency'] : 'VNĐ';
            ?>
            <div style="margin-top: 15px; font-size: 0.85rem; font-weight: 700;">
                Tình trạng kết nối: 
                <span style="color: <?php echo isset($balance['balance']) ? '#10b981' : '#ef4444'; ?>">
                    <?php echo isset($balance['balance']) ? 'Kết nối thành công (Số dư: ' . number_format($balance['balance']) . ' ' . $cur . ')' : 'Chưa kết nối / Lỗi xác thực'; ?>
                </span>
            </div>
        </div>

        <div style="padding: 20px; border-radius: 20px; background: rgba(16, 185, 129, 0.05); margin-bottom: 35px; border: 1px dashed #10b981;">
            <div class="section-header-flex">
                <h4 style="color: #10b981; margin: 0;"><i class="fas fa-share-alt"></i> Hệ Thống Cộng Tác Viên (Affiliate)</h4>
                <button type="submit" class="quick-save-btn" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2);"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Tỷ lệ Hoa hồng nạp tiền (%)</label>
                <input type="number" name="referral_commission_percent" value="<?php echo $current_settings['referral_commission_percent'] ?? '5'; ?>" step="0.1" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
                <p style="font-size: 0.75rem; opacity: 0.5; margin-top: 8px;"><i class="fas fa-info-circle"></i> Người giới thiệu sẽ nhận được X% giá trị mỗi khi cấp dưới nạp thêm tiền vào tài khoản.</p>
            </div>
        </div>

        <div style="padding: 20px; border-radius: 20px; background: rgba(138, 43, 226, 0.05); margin-bottom: 35px; border: 1px dashed rgba(138, 43, 226, 0.2);">
            <div class="section-header-flex">
                <h4 style="color: #8A2BE2; margin: 0;"><i class="fab fa-telegram"></i> Thông Báo Telegram</h4>
                <button type="submit" class="quick-save-btn" style="background: rgba(138, 43, 226, 0.1); color: #8A2BE2; border-color: rgba(138, 43, 226, 0.2);"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>
            <div class="admin-grid" style="margin-bottom: 15px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Telegram Bot Token</label>
                    <div style="position: relative;">
                        <input type="password" name="telegram_bot_token" id="telegram_bot_token" value="<?php echo mask_sensitive($current_settings['telegram_bot_token'] ?? ''); ?>" placeholder="Nhập Token mới để thay đổi" style="width: 100%; padding: 12px 45px 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
                        <button type="button" onclick="togglePass('telegram_bot_token')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                            <i class="fas fa-eye-slash" id="telegram_bot_token_icon"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8;">Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id" value="<?php echo $current_settings['telegram_chat_id']; ?>" placeholder="ID từ @userinfobot" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
                </div>
            </div>
            <p style="font-size: 0.75rem; opacity: 0.5; margin: 0;"><i class="fas fa-info-circle"></i> Hệ thống sẽ tự động gửi thông báo đơn hàng mới về Chat ID này.</p>
        </div>

        <div style="padding: 25px; border-radius: 20px; background: rgba(239, 68, 68, 0.03); margin-bottom: 35px; border: 1px solid rgba(239, 68, 68, 0.1);">
            <div class="section-header-flex" style="margin-bottom: 25px;">
                <h4 style="color: #ef4444; margin: 0; display: flex; align-items: center; gap: 10px; text-transform: uppercase; font-size: 0.9rem; font-weight: 800;">
                    <i class="fas fa-envelope"></i> Cấu Hình <span style="background:#ef4444; color:#fff; padding:2px 6px; border-radius:4px;">SMTP EMAIL</span>
                </h4>
                <button type="submit" class="quick-save-btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>

            <div style="border-left: 4px solid #ef4444; padding: 15px 20px; background: rgba(255,255,255,0.02); border-radius: 0 12px 12px 0; margin-bottom: 25px;">
                <p style="font-size: 0.85rem; line-height: 1.6; margin: 0;">
                    <i class="fas fa-info-circle" style="color:#ef4444; margin-right:5px;"></i> 
                    <strong style="color:var(--text-light);">Lưu ý Gmail:</strong> Sử dụng Mật khẩu ứng dụng (App Password) 16 ký tự thay vì mật khẩu Gmail thông thường. Bạn có thể tạo App Password trong Cài đặt tài khoản Google &rarr; Bảo mật &rarr; Mật khẩu ứng dụng.
                </p>
            </div>

            <div class="admin-grid" style="margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">SMTP Host</label>
                    <input type="text" name="smtp_host" value="<?php echo $current_settings['smtp_host'] ?? 'smtp.gmail.com'; ?>" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">SMTP Port</label>
                    <input type="text" name="smtp_port" value="<?php echo $current_settings['smtp_port'] ?? '587'; ?>" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">SMTP Username (Email)</label>
                <input type="email" name="smtp_user" value="<?php echo $current_settings['smtp_user'] ?? ''; ?>" placeholder="name@gmail.com" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">SMTP Password</label>
                <div style="position: relative;">
                    <input type="password" name="smtp_pass" id="smtp_pass" value="<?php echo mask_sensitive($current_settings['smtp_pass'] ?? ''); ?>" style="width: 100%; padding: 12px 45px 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    <button type="button" onclick="togglePass('smtp_pass')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                        <i class="fas fa-eye-slash" id="smtp_pass_icon"></i>
                    </button>
                </div>
            </div>

            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Bảo mật (Secure)</label>
                <select name="smtp_secure" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    <option value="tls" <?php echo ($current_settings['smtp_secure'] ?? '') == 'tls' ? 'selected' : ''; ?>>TLS (Khuyên dùng cho Port 587)</option>
                    <option value="ssl" <?php echo ($current_settings['smtp_secure'] ?? '') == 'ssl' ? 'selected' : ''; ?>>SSL (Khuyên dùng cho Port 465)</option>
                    <option value="none" <?php echo ($current_settings['smtp_secure'] ?? '') == 'none' ? 'selected' : ''; ?>>None</option>
                </select>
            </div>
        </div>

        <div style="padding: 25px; border-radius: 20px; background: rgba(16, 185, 129, 0.03); margin-bottom: 35px; border: 1px solid rgba(16, 185, 129, 0.1);">
            <div class="section-header-flex">
                <h4 style="color: #10b981; margin: 0;"><i class="fas fa-university"></i> Cài Đặt Nạp Tiền (Bank & ZaloPay)</h4>
                <button type="submit" class="quick-save-btn"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>
            
            <div class="admin-grid" style="margin-bottom: 30px;">
                <!-- Bank Settings -->
                <div style="padding: 15px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid var(--glass-border);">
                    <h5 style="margin-bottom: 15px; color: #3b82f6;">Ngân hàng MB Bank</h5>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">Chủ tài khoản</label>
                        <input type="text" name="bank_owner" value="<?php echo $current_settings['bank_owner'] ?? ''; ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">Số tài khoản</label>
                        <input type="text" name="bank_number" value="<?php echo $current_settings['bank_number'] ?? ''; ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">Mã QR (Upload)</label>
                        <?php if(!empty($current_settings['bank_qr'])): ?>
                            <img src="../<?php echo $current_settings['bank_qr']; ?>" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; border-radius: 8px; display: block; background: #fff;">
                        <?php endif; ?>
                        <input type="file" name="bank_qr" style="font-size: 0.8rem; color: var(--text-dim);">
                    </div>
                </div>

                <!-- ZaloPay Settings -->
                <div style="padding: 15px; background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid var(--glass-border);">
                    <h5 style="margin-bottom: 15px; color: #0085FF;">ZaloPay</h5>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">Tên chủ ZaloPay</label>
                        <input type="text" name="zalopay_owner" value="<?php echo $current_settings['zalopay_owner'] ?? ''; ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">Số điện thoại / STK</label>
                        <input type="text" name="zalopay_number" value="<?php echo $current_settings['zalopay_number'] ?? ''; ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 5px; opacity: 0.7;">QR ZaloPay (Upload)</label>
                        <?php if(!empty($current_settings['zalopay_qr'])): ?>
                            <img src="../<?php echo $current_settings['zalopay_qr']; ?>" style="width: 80px; height: 80px; object-fit: contain; margin-bottom: 10px; border-radius: 8px; display: block; background: #fff;">
                        <?php endif; ?>
                        <input type="file" name="zalopay_qr" style="font-size: 0.8rem; color: var(--text-dim);">
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--glass-border);">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: #10b981;">
                    <i class="fas fa-key"></i> WEBHOOK TOKEN (Mã bảo mật nạp tiền)
                </label>
                <div style="position: relative;">
                    <input type="password" name="bank_webhook_token" id="webhook_token" value="<?php echo mask_sensitive($current_settings['bank_webhook_token'] ?? ''); ?>" style="width: 100%; padding: 12px 45px 12px 15px; border-radius: 12px; border: 1px solid #10b981; background: rgba(16, 185, 129, 0.05); color: #333; outline: none; font-family: monospace;">
                    <button type="button" onclick="togglePass('webhook_token')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-dim); cursor: pointer;">
                        <i class="fas fa-eye-slash" id="webhook_token_icon"></i>
                    </button>
                </div>
                <p style="font-size: 0.75rem; opacity: 0.6; margin-top: 8px;"><i class="fas fa-info-circle"></i> Token này dùng để xác thực các yêu cầu từ SePay/Casso. Vui lòng giữ kín.</p>
            </div>
            <p style="font-size: 0.75rem; opacity: 0.5; margin: 0;"><i class="fas fa-info-circle"></i> Bạn có thể tải lên ảnh chụp màn hình mã QR của mình. Hệ thống sẽ tự động cập nhật lên trang nạp tiền.</p>
        </div>

        <div style="padding: 25px; border-radius: 20px; background: rgba(59, 130, 246, 0.03); margin-bottom: 35px; border: 1px solid rgba(59, 130, 246, 0.1);">
            <div class="section-header-flex" style="margin-bottom: 25px;">
                <h4 style="color: #3b82f6; margin: 0; display: flex; align-items: center; gap: 10px; text-transform: uppercase; font-size: 0.9rem; font-weight: 800;">
                    <i class="fas fa-headset"></i> Thông Tin <span style="background:#3b82f6; color:#fff; padding:2px 6px; border-radius:4px;">LIÊN HỆ & NÚT NỔI</span>
                </h4>
                <button type="submit" class="quick-save-btn" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);"><i class="fas fa-check"></i> Lưu mục này</button>
            </div>

            <div class="admin-grid" style="margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Hotline / Zalo Số</label>
                    <input type="text" name="contact_phone" value="<?php echo $current_settings['contact_phone']; ?>" placeholder="0123.456.789" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Email Hỗ Trợ</label>
                    <input type="email" name="contact_email" value="<?php echo $current_settings['contact_email']; ?>" placeholder="support@domain.com" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                </div>
            </div>

            <div style="display: grid; gap: 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #1877F2; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fab fa-facebook-f"></i>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; opacity: 0.5; margin-bottom: 3px;">Link Facebook Cá Nhân / Fanpage</label>
                        <input type="text" name="facebook_link" value="<?php echo $current_settings['facebook_link']; ?>" placeholder="https://facebook.com/your-profile" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #0068FF; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fas fa-comment"></i>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; opacity: 0.5; margin-bottom: 3px;">Link Chat Zalo (Ví dụ: https://zalo.me/0123456789)</label>
                        <input type="text" name="zalo_link" value="<?php echo $current_settings['zalo_link']; ?>" placeholder="https://zalo.me/your-number" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #26A5E4; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="fab fa-telegram-plane"></i>
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.7rem; font-weight: 700; opacity: 0.5; margin-bottom: 3px;">Link Chat Telegram (Không bắt buộc)</label>
                        <input type="text" name="telegram_link" value="<?php echo $current_settings['telegram_link']; ?>" placeholder="https://t.me/your-username" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 15px; border-top: 1px dashed var(--glass-border);">
                    <div style="display: flex; align-items: center; gap: 12px; flex: 1;">
                        <div style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <div style="flex: 1;">
                            <label style="display: block; font-size: 0.75rem; font-weight: 800; color: var(--primary-2); margin-bottom: 3px;">LINK WEBSITE CHUYỂN HƯỚNG (Nút Website trên Menu)</label>
                            <input type="text" name="website_redirect_url" value="<?php echo $current_settings['website_redirect_url'] ?? ''; ?>" placeholder="Link trang web bạn muốn khách hàng chuyển sang" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--primary); background: rgba(124, 58, 237, 0.05); color: #333; outline: none; font-weight: 600;">
                        </div>
                    </div>
                    <button type="submit" class="quick-save-btn" style="margin-left: 15px; background: var(--primary-gradient); color: white; border: none;"><i class="fas fa-save"></i> LƯU LINK</button>
                </div>
            </div>
            <p style="font-size: 0.75rem; opacity: 0.5; margin-top: 15px; text-align: center;"><i class="fas fa-info-circle"></i> Các thông tin này sẽ được cập nhật lên nút bấm nổi (Floating Buttons) và chân trang (Footer).</p>
        </div>

        <div class="sticky-action-bar">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-info-circle" style="color: var(--primary); font-size: 1.2rem;"></i>
                <div style="line-height: 1.2;">
                    <div style="font-weight: 800; font-size: 0.85rem; color: white;">TRÌNH QUẢN LÝ CẤU HÌNH</div>
                    <div style="font-size: 0.7rem; opacity: 0.5;">Nhấn lưu để áp dụng thay đổi ngay</div>
                </div>
            </div>
            <button type="submit" class="btn-premium" style="padding: 12px 35px; border-radius: 12px; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 5px 15px rgba(124, 58, 237, 0.3);">
                <i class="fas fa-save"></i> LƯU CẤU HÌNH
            </button>
        </div>
    </form>
</div>

<script>
function togglePass(id) {
    const input = document.getElementById(id);
    const icon = document.getElementById(id + '_icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>
