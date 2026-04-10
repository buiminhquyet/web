<?php 
require_once 'includes/config.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = get_user($_SESSION['user_id']);
$orders_count = $db->fetch("SELECT COUNT(id) as total FROM orders WHERE user_id = '{$user['id']}'")['total'];
$spent = $db->fetch("SELECT SUM(total_price) as total FROM orders WHERE user_id = '{$user['id']}'")['total'] ?? 0;

// Affiliate Stats
$referral_count = $db->fetch("SELECT COUNT(id) as total FROM users WHERE ref_by = '{$user['id']}'")['total'];
$referral_link = SITE_URL . "/register?ref=" . $user['username'];

$orders = $db->query("SELECT o.*, p.name as product_name, pkg.name as package_name 
                     FROM orders o 
                     JOIN packages pkg ON o.package_id = pkg.id 
                     JOIN products p ON pkg.product_id = p.id 
                     WHERE o.user_id = '{$user['id']}' 
                     ORDER BY o.created_at DESC LIMIT 10");


// 2FA Setup Logic
require_once 'includes/lib/TwoFactorAuth.php';
$tfa = new TwoFactorAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'enable_2fa') {
        $secret = $_POST['secret'];
        $code = $_POST['code'];
        if ($tfa->verifyCode($secret, $code)) {
            $db->query("UPDATE users SET two_fa_secret = '$secret', two_fa_enabled = 1 WHERE id = '{$user['id']}'");
            alert('success', 'Bảo mật 2 lớp đã được kích hoạt thành công!');
            redirect('profile.php');
        } else {
            $error_2fa = "Mã xác nhận 2FA không chính xác.";
        }
    } elseif ($_POST['action'] === 'disable_2fa') {
        $db->query("UPDATE users SET two_fa_enabled = 0 WHERE id = '{$user['id']}'");
        alert('success', 'Đã tắt bảo mật 2 lớp.');
        redirect('profile.php');
    }
}

// Generate new secret if not enabled
$temp_secret = $tfa->createSecret();
$qrCodeUrl = $tfa->getQRCodeUrl($user['username'] . "@QUYETDEV", $temp_secret, "QUYETDEV SHOP");

include 'header.php'; 
?>

<style>
.profile-layout {
    display: grid;
    grid-template-columns: 320px 1fr;
    gap: 40px;
    align-items: start;
}
.profile-layout > div {
    min-width: 0; /* Fix Grid Blowout */
    width: 100%;
}
.profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
.tfa-setup-grid {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 30px;
    align-items: start;
}
.affiliate-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}
@media (max-width: 768px) {
    .profile-layout { grid-template-columns: 1fr !important; }
    .profile-stats-grid { grid-template-columns: 1fr !important; }
    .glass-card { padding: 25px !important; }
    .tfa-setup-grid { 
        grid-template-columns: 1fr !important; 
        text-align: center;
        justify-items: center;
        gap: 20px;
    }
    .affiliate-header {
        flex-direction: column;
        text-align: center;
    }
}
@media (max-width: 992px) {
    .profile-layout { grid-template-columns: 1fr !important; }
    .profile-stats-grid { grid-template-columns: 1fr !important; }
    .glass-card { padding: 20px !important; }
}
</style>

<main class="container" style="padding: 60px 20px;">
    <div class="profile-layout">
        <!-- Profile Sidebar -->
        <div style="display: grid; gap: 30px;">
            <div class="glass-card spotlight" style="padding: 40px 30px; text-align: center; border-radius: 30px;">
                <div style="position: relative; width: 100px; height: 100px; margin: 0 auto 25px;">
                    <div style="width: 100%; height: 100%; background: var(--primary-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: white; box-shadow: 0 10px 25px rgba(92, 84, 229, 0.3);">
                        <i class="fas fa-user"></i>
                    </div>
                    <div style="position: absolute; bottom: 0; right: 0; width: 30px; height: 30px; background: #10b981; border: 4px solid var(--bg-light); border-radius: 50%;"></div>
                </div>
                <h3 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 5px;"><?php echo htmlspecialchars($user['username']); ?></h3>
                <p style="font-size: 0.9rem; opacity: 0.6; margin-bottom: 30px;"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div style="display: grid; gap: 12px;">
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="btn-premium" style="width: 100%; border-radius: 14px; padding: 12px; font-size: 0.9rem; background: #111827; border-color: #374151;">
                        <i class="fas fa-user-shield"></i> Quản trị hệ thống
                    </a>
                    <?php endif; ?>
                    <a href="topup.php" class="btn-premium" style="width: 100%; border-radius: 14px; padding: 12px; font-size: 0.9rem;">
                        <i class="fas fa-wallet"></i> Nạp Tiền
                    </a>
                    <a href="logout.php" class="btn-secondary" style="width: 100%; border-radius: 14px; padding: 12px; font-size: 0.9rem; color: #ef4444; border-color: rgba(239, 68, 68, 0.1);">
                        <i class="fas fa-sign-out-alt"></i> Đăng Xuất
                    </a>
                </div>
            </div>

            <div class="glass-card" style="padding: 30px; border-radius: 24px;">
                <h4 style="font-weight: 800; margin-bottom: 20px;">Menu Cá Nhân</h4>
                <div style="display: grid; gap: 5px;">
                    <a href="profile.php" class="menu-item active"><i class="fas fa-th-large"></i> Tổng quan</a>
                    <a href="orders.php" class="menu-item"><i class="fas fa-shopping-basket"></i> Đơn hàng đã mua</a>
                    <a href="deposit-history.php" class="menu-item"><i class="fas fa-history"></i> Lịch sử nạp tiền</a>
                    <a href="settings.php" class="menu-item"><i class="fas fa-user-cog"></i> Cài đặt tài khoản</a>
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/index.php" class="menu-item" style="color: #f59e0b;"><i class="fas fa-user-shield"></i> Quản trị hệ thống</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div style="display: flex; flex-direction: column; gap: 30px; min-width: 0; width: 100%;">
            <!-- Stats -->
            <div class="profile-stats-grid">
                <div class="glass-card" style="padding: 30px; border-radius: 24px;">
                    <p style="font-size: 0.85rem; opacity: 0.6; margin-bottom: 10px;">Số dư hiện tại</p>
                    <h2 class="text-gradient" style="font-weight: 800;"><?php echo format_currency($user['balance']); ?></h2>
                </div>
                <div class="glass-card" style="padding: 30px; border-radius: 24px;">
                    <p style="font-size: 0.85rem; opacity: 0.6; margin-bottom: 10px;">Tổng đơn hàng</p>
                    <h2 style="font-weight: 800;"><?php echo $orders_count; ?></h2>
                </div>
                <div class="glass-card" style="padding: 30px; border-radius: 24px;">
                    <p style="font-size: 0.85rem; opacity: 0.6; margin-bottom: 10px;">Tổng chi tiêu</p>
                    <h2 style="font-weight: 800; color: #ff0169;"><?php echo format_currency($spent); ?></h2>
                </div>
            </div>

            <!-- 2FA Section -->
            <div class="glass-card" style="padding: 30px 40px; border-radius: 24px; border-left: 5px solid #10b981;">
                <h4 style="font-weight: 800; margin-bottom: 20px; color: #10b981;"><i class="fas fa-shield-alt"></i> Bảo Mật 2 Lớp (2FA)</h4>
                
                <?php if ($user['two_fa_enabled']): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
                        <div>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; margin-bottom: 10px; display: inline-block;">ĐÃ KÍCH HOẠT</span>
                            <p style="font-size: 0.85rem; opacity: 0.7;">Tài khoản của bạn đang được bảo vệ bởi lớp mã OTP 6 chữ số.</p>
                        </div>
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="disable_2fa">
                            <button type="submit" class="btn-secondary" style="border-radius: 12px; color: #ef4444; border-color: rgba(239, 68, 68, 0.1);">
                                <i class="fas fa-power-off"></i> Tắt 2FA
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="tfa-setup-grid">
                        <img src="<?php echo $qrCodeUrl; ?>" style="background: #fff; padding: 10px; border-radius: 15px; width: 150px; height: 150px; box-shadow: 0 10px 20px rgba(0,0,0,0.2);">
                        <div>
                            <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 15px; line-height: 1.6;">
                                1. Dùng ứng dụng <strong>Google Authenticator</strong> hoặc <strong>Authy</strong> quét mã QR bên cạnh.<br>
                                2. Nhập mã 6 số từ ứng dụng để xác nhận kích hoạt.
                            </p>
                            
                            <?php if (isset($error_2fa)): ?>
                                <p style="color: #ef4444; font-size: 0.8rem; margin-bottom: 10px;"><i class="fas fa-exclamation-triangle"></i> <?php echo $error_2fa; ?></p>
                            <?php endif; ?>

                            <form action="profile.php" method="POST" style="display: flex; gap: 10px;">
                                <input type="hidden" name="action" value="enable_2fa">
                                <input type="hidden" name="secret" value="<?php echo $temp_secret; ?>">
                                <input type="text" name="code" placeholder="Nhập mã 6 số" required maxlength="6"
                                    style="padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 12px; color: white; width: 140px; text-align: center; letter-spacing: 5px; font-weight: 800;">
                                <button type="submit" class="btn-premium" style="padding: 12px 20px; border-radius: 12px;">Xác Nhận</button>
                            </form>
                            <p style="font-size: 0.7rem; opacity: 0.4; margin-top: 10px;">Mã bí mật: <code><?php echo $temp_secret; ?></code> (Lưu lại để khôi phục)</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Affiliate Section -->
            <div class="glass-card spotlight" style="padding: 30px 40px; border-radius: 24px; border-left: 5px solid var(--primary);">
                <div class="affiliate-header">
                    <div style="flex: 1; min-width: 250px;">
                        <h4 style="font-weight: 800; margin-bottom: 5px;"><i class="fas fa-hand-holding-usd" style="color: var(--primary); margin-right: 8px;"></i> Kiếm Tiền Cùng QUYETDEV</h4>
                        <p style="font-size: 0.85rem; opacity: 0.7;">Giới thiệu bạn bè và nhận ngay <strong style="color: var(--primary);"><?php echo get_setting('referral_commission_percent') ?: 5; ?>%</strong> hoa hồng tiền nạp.</p>
                    </div>
                    <div style="text-align: inherit;">
                        <span style="font-size: 0.8rem; opacity: 0.5;">Đã giới thiệu:</span>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);"><?php echo $referral_count; ?> người</div>
                    </div>
                </div>
                
                <div style="margin-top: 25px;">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; margin-bottom: 8px; opacity: 0.5; margin-left: 5px;">Link giới thiệu của bạn:</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="ref-link" value="<?php echo $referral_link; ?>" readonly
                            style="flex: 1; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px 15px; border-radius: 12px; color: var(--text-light); font-size: 0.9rem; font-family: monospace;">
                        <button onclick="copyRefLink()" class="btn-premium" style="padding: 12px 25px; border-radius: 12px; font-size: 0.9rem;">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>

            <script>
            function copyRefLink() {
                var copyText = document.getElementById("ref-link");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value).then(() => {
                    alert('Đã sao chép link giới thiệu của bạn!');
                });
            }
            </script>

            <!-- Recent Orders -->
            <div class="glass-card" style="padding: 40px; border-radius: 30px; overflow: hidden; min-width: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                    <h3 style="font-weight: 800; font-size: clamp(1rem, 4vw, 1.3rem);"><i class="fas fa-clock" style="margin-right: 10px; opacity: 0.5;"></i> Đơn hàng gần đây</h3>
                    <a href="orders.php" style="font-size: 0.9rem; color: var(--primary); text-decoration: none; font-weight: 700;">Xem tất cả</a>
                </div>
                
                <div style="overflow-x: auto; width: 100%;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                                <th style="padding: 15px 10px;">Sản phẩm</th>
                                <th style="padding: 15px 10px;">Thông tin nhận</th>
                                <th style="padding: 15px 10px;">Giá</th>
                                <th style="padding: 15px 10px;">Trạng thái</th>
                                <th style="padding: 15px 10px;">Ngày mua</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($orders->num_rows > 0): while($o = $orders->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                                <td style="padding: 20px 10px;">
                                    <div style="font-weight: 700; color: var(--text-light);"><?php echo htmlspecialchars($o['product_name']); ?></div>
                                    <div style="font-size: 0.75rem; opacity: 0.5;"><?php echo htmlspecialchars($o['package_name']); ?></div>
                                </td>
                                <td style="padding: 20px 10px; font-size: 0.85rem; opacity: 0.9;"><?php echo format_service_data($o['service_data']); ?></td>
                                <td style="padding: 20px 10px; font-weight: 800; color: #ff0169;"><?php echo format_currency($o['total_price']); ?></td>
                                <td style="padding: 20px 10px;">
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.75rem;">
                                        Thành công
                                    </span>
                                </td>
                                <td style="padding: 20px 10px; opacity: 0.5; font-size: 0.85rem;"><?php echo date('d/m/Y', strtotime($o['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" style="padding: 60px; text-align: center; opacity: 0.4;">Bạn chưa có đơn hàng nào thực hiện.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.menu-item {
    padding: 12px 20px;
    border-radius: 12px;
    color: var(--text-light);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: 0.3s;
    opacity: 0.7;
}
.menu-item i { width: 20px; text-align: center; }
.menu-item:hover {
    background: rgba(92, 84, 229, 0.05);
    opacity: 1;
    color: var(--primary);
}
.menu-item.active {
    background: var(--primary-gradient);
    color: white;
    opacity: 1;
    box-shadow: 0 5px 15px rgba(92, 84, 229, 0.2);
}
.table-row:hover {
    background: rgba(92, 84, 229, 0.02);
}
</style>

<?php include 'footer.php'; ?>
