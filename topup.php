<?php 
require_once 'includes/config.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

include 'header.php'; 

$user_id = $_SESSION['user_id'];
$user = $db->fetch("SELECT * FROM users WHERE id = '$user_id'");

// Fetch dynamic settings for topup
$res = $db->query("SELECT * FROM settings");
$settings = [];
while($row = $res->fetch_assoc()) {
    $settings[$row['s_key']] = $row['s_value'];
}
?>

<style>
    .topup-tabs {
        display: flex;
        background: var(--glass-bg);
        padding: 4px;
        border-radius: 16px;
        margin-bottom: 25px;
        border: 1px solid var(--glass-border);
    }

    .topup-tab {
        flex: 1;
        padding: 10px;
        text-align: center;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.85rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        opacity: 0.6;
    }

    .topup-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        opacity: 1;
    }

    .qr-container {
        text-align: center;
        padding: 20px 10px;
    }

    .qr-box {
        background: var(--bg-card);
        padding: 20px;
        border-radius: 24px;
        display: inline-block;
        border: 1px solid var(--glass-border);
        box-shadow: var(--shadow);
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        background: var(--bg-card);
        border-radius: 16px;
        border: 1px solid var(--border);
        margin-bottom: 12px;
        transition: var(--transition);
    }
    .info-row:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--primary);
    }

    .info-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .info-value {
        font-weight: 800;
        font-size: 1rem;
        color: var(--text);
    }

    .copy-btn {
        width: 34px;
        height: 34px;
        background: #f1f5f9;
        border: none;
        border-radius: 8px;
        color: var(--primary);
        cursor: pointer;
        transition: var(--transition);
        font-size: 0.85rem;
    }

    .copy-btn:hover {
        background: var(--primary);
        color: white;
    }

    .warning-box {
        background: #111827;
        color: #f8fafc;
        padding: 20px;
        border-radius: 20px;
        margin-top: 30px;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .warning-item {
        margin-bottom: 10px;
        display: flex;
        gap: 10px;
        font-size: 0.85rem;
        line-height: 1.4;
    }
</style>

<main class="container section-padding" style="padding-top: 40px;">
    <div style="max-width: 720px; margin: 0 auto;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h1 class="text-gradient" style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px;">Nạp Số Dư</h1>
            <p style="opacity: 0.6; font-size: 0.9rem; max-width: 500px; margin: 0 auto; line-height: 1.6;">
                Nạp tiền tự động qua QR Code để nhận ngay số dư trong 30 giây.
            </p>
        </div>

        <div class="topup-tabs">
            <div class="topup-tab active" data-tab="qr">
                <i class="fas fa-qrcode"></i> Quét mã QR
            </div>
            <div class="topup-tab" data-tab="zalopay">
                <i class="fab fa-vimeo-v"></i> ZaloPay Auto
            </div>
            <div class="topup-tab" data-tab="manual">
                <i class="fas fa-university"></i> Chuyển khoản
            </div>
        </div>

        <div class="glass-card spotlight" style="padding: 25px; border-radius: 28px;">
            
            <div id="tab-qr" class="tab-content">
                <div class="qr-container">
                    <h4 style="font-weight: 800; margin-bottom: 20px;">Quét Mã QR Chuyển Khoản Tự Động</h4>
                    <div class="qr-box">
                        <?php if(!empty($settings['bank_qr'])): ?>
                            <img src="<?php echo $settings['bank_qr']; ?>" alt="Bank QR" style="width: 100%; max-width: 240px;">
                        <?php else: ?>
                            <img src="https://img.vietqr.io/image/MB-<?php echo $settings['bank_number'] ?? '0335021206'; ?>-compact2.jpg?amount=0&addInfo=QUYETDEV%20<?php echo $user_id; ?>&accountName=<?php echo urlencode($settings['bank_owner'] ?? 'QUYET BUI'); ?>" 
                                 alt="VietQR" style="width: 100%; max-width: 240px;">
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 0.85rem; opacity: 0.7; max-width: 400px; margin: 0 auto;">
                        Sử dụng App Ngân Hàng để quét mã. Số tiền sẽ được cộng tự động sau 1 phút.
                    </p>
                </div>
            </div>

            <div id="tab-zalopay" class="tab-content" style="display: none;">
                <div class="qr-container">
                    <h4 style="font-weight: 800; margin-bottom: 20px; color: #0085FF;">Nạp Tiền Qua ZaloPay Tự Động</h4>
                    <div class="qr-box" style="border-color: #0085FF;">
                        <?php if(!empty($settings['zalopay_qr'])): ?>
                            <img src="<?php echo $settings['zalopay_qr']; ?>" alt="ZaloPay QR" style="width: 100%; max-width: 240px;">
                        <?php else: ?>
                            <div style="width: 200px; height: 200px; background: #f0f9ff; display: flex; align-items: center; justify-content: center; border-radius: 15px; margin: 0 auto;">
                               <i class="fab fa-vimeo-v" style="font-size: 4rem; color: #0085FF;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-row" style="background: rgba(0, 133, 255, 0.03); border-color: rgba(0, 133, 255, 0.2); margin-top: 20px;">
                        <div>
                            <div class="info-label" style="color: #0085FF;">Số điện thoại ZaloPay</div>
                            <div class="info-value" style="color: #0085FF;"><?php echo $settings['zalopay_number'] ?? '0335021206'; ?></div>
                        </div>
                        <button class="copy-btn" style="background: #0085FF; color: white;"><i class="far fa-copy"></i></button>
                    </div>

                    <div class="info-row" style="background: rgba(0, 133, 255, 0.03); border-color: rgba(0, 133, 255, 0.2);">
                        <div>
                            <div class="info-label" style="color: #0085FF;">Nội dung chuyển khoản</div>
                            <div class="info-value" style="color: #0085FF;">QUYETDEV <?php echo $user_id; ?></div>
                        </div>
                        <button class="copy-btn" style="background: #0085FF; color: white;"><i class="far fa-copy"></i></button>
                    </div>
                </div>
            </div>

            <div id="tab-manual" class="tab-content" style="display: none;">
                <h4 style="font-weight: 800; margin-bottom: 20px; text-align: center;">Thông Tin Chuyển Khoản</h4>
                
                <div class="info-row">
                    <div>
                        <div class="info-label">Ngân hàng</div>
                        <div class="info-value"><?php echo $settings['bank_name'] ?? 'MB BANK (Quân Đội)'; ?></div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="background: #004a91; color: white; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 900; display: flex; align-items: center; gap: 5px;">
                            <i class="fas fa-university"></i> MB BANK
                        </div>
                    </div>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">Số tài khoản</div>
                        <div class="info-value"><?php echo $settings['bank_number'] ?? '0335021206'; ?></div>
                    </div>
                    <button class="copy-btn"><i class="far fa-copy"></i></button>
                </div>

                <div class="info-row">
                    <div>
                        <div class="info-label">Chủ tài khoản</div>
                        <div class="info-value uppercase"><?php echo $settings['bank_owner'] ?? 'BUI MINH QUYET'; ?></div>
                    </div>
                </div>

                <div class="info-row" style="background: rgba(255, 1, 105, 0.03); border-color: rgba(255, 1, 105, 0.2);">
                    <div>
                        <div class="info-label" style="color: #ff0169; opacity: 0.8;">Nội dung chuyển khoản</div>
                        <div class="info-value" style="color: #ff0169; font-size: 1.1rem;">QUYETDEV <?php echo $user_id; ?></div>
                    </div>
                    <button class="copy-btn" style="background: #ff0169; color: white;"><i class="far fa-copy"></i></button>
                </div>
            </div>

            <div class="warning-box">
                <h4 style="color: #ef4444; font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i> THÔNG TIN CẦN LƯU Ý
                </h4>
                <div class="warning-item">
                    <span style="color: #f59e0b; font-weight: 800;">01.</span>
                    <p>Hệ thống tự động cộng tiền sau 1-5 phút khi nhận được tiền.</p>
                </div>
                <div class="warning-item">
                    <span style="color: #f59e0b; font-weight: 800;">02.</span>
                    <p>Nạp tối thiểu <span style="color: #34e89e; font-weight: 700;">10,000 VNĐ</span>. Nếu nạp dưới mức này tiền sẽ không được cộng.</p>
                </div>
                <div class="warning-item">
                    <span style="color: #f59e0b; font-weight: 800;">03.</span>
                    <p>Vui lòng ghi đúng <span style="color: #f59e0b; font-weight: 800;">NỘI DUNG CHUYỂN KHOẢN</span> phía trên để được cộng tiền tự động.</p>
                </div>
            </div>

            <!-- Deposit History -->
            <div style="margin-top: 40px;">
                <h4 style="font-weight: 800; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-history" style="color: var(--primary);"></i> Lịch Sử Nạp Tiền
                </h4>
                <div class="glass-card" style="padding: 0; overflow: hidden; border-radius: 20px;">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                            <thead>
                                <tr style="background: rgba(255,255,255,0.03); border-bottom: 1px solid var(--border);">
                                    <th style="padding: 15px 20px; text-align: left; opacity: 0.6;">Mã GD</th>
                                    <th style="padding: 15px 20px; text-align: left; opacity: 0.6;">Số tiền</th>
                                    <th style="padding: 15px 20px; text-align: left; opacity: 0.6;">Thời gian</th>
                                    <th style="padding: 15px 20px; text-align: right; opacity: 0.6;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $deposits = $db->fetchAll("SELECT * FROM bank_transactions WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 10");
                                if ($deposits):
                                    foreach ($deposits as $deposit):
                                ?>
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 15px 20px; font-weight: 700; color: var(--primary);"><?php echo $deposit['transaction_id']; ?></td>
                                    <td style="padding: 15px 20px; font-weight: 800; color: #10b981;">+<?php echo number_format($deposit['amount']); ?>đ</td>
                                    <td style="padding: 15px 20px; opacity: 0.7;"><?php echo date('H:i d/m', strtotime($deposit['created_at'])); ?></td>
                                    <td style="padding: 15px 20px; text-align: right;">
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Thành công</span>
                                    </td>
                                </tr>
                                <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="4" style="padding: 40px; text-align: center; opacity: 0.5;">
                                        <i class="fas fa-info-circle"></i> Chưa có giao dịch nào được thực hiện.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    const tabs = document.querySelectorAll('.topup-tab');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            contents.forEach(c => {
                c.style.display = c.id === 'tab-' + tabName ? 'block' : 'none';
            });
        });
    });

    document.querySelectorAll('.copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const value = btn.parentElement.querySelector('.info-value').innerText;
            navigator.clipboard.writeText(value);
            
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.style.background = '#10b981';
            btn.style.color = 'white';
            
            setTimeout(() => {
                btn.innerHTML = originalIcon;
                btn.style.background = '';
                btn.style.color = '';
            }, 2000);
        });
    });
</script>

<?php include 'footer.php'; ?>
