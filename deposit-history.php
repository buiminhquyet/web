<?php 
require_once 'includes/config.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = get_user($_SESSION['user_id']);
$deposits = $db->query("SELECT * FROM deposits WHERE user_id = '{$user['id']}' ORDER BY created_at DESC");

include 'header.php'; 
?>

<main class="container section-padding">
    <div style="max-width: 800px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 class="text-gradient" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px;">Lịch Sử Nạp Tiền</h1>
            <p style="opacity: 0.6; font-size: 1rem; max-width: 600px; margin: 0 auto; line-height: 1.7;">
                Theo dõi tất cả các giao dịch nạp tiền vào tài khoản của bạn.
            </p>
        </div>

        <div class="glass-card spotlight" style="padding: 40px; border-radius: 30px;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 15px 10px;">Mã Giao Dịch</th>
                            <th style="padding: 15px 10px;">Số Tiền</th>
                            <th style="padding: 15px 10px;">Trạng thái</th>
                            <th style="padding: 15px 10px;">Ngày nạp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($deposits->num_rows > 0): while($d = $deposits->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                            <td style="padding: 20px 10px; font-weight: 700; font-family: monospace;">DEP_#<?php echo $d['id']; ?></td>
                            <td style="padding: 20px 10px; font-weight: 800; color: #10b981;"><?php echo format_currency($d['amount']); ?></td>
                            <td style="padding: 20px 10px;">
                                <span class="badge" style="background: <?php echo $d['status'] === 'completed' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; color: <?php echo $d['status'] === 'completed' ? '#10b981' : '#f59e0b'; ?>; font-size: 0.7rem;">
                                    <?php echo $d['status'] === 'completed' ? 'Thành công' : 'Đang xử lý'; ?>
                                </span>
                            </td>
                            <td style="padding: 20px 10px; opacity: 0.5; font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($d['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="4" style="padding: 80px; text-align: center; opacity: 0.4;">
                                <i class="fas fa-history" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                Bạn chưa thực hiện nạp tiền bao giờ.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="profile.php" class="btn-ghost"><i class="fas fa-chevron-left"></i> Quay lại trang cá nhân</a>
        </div>
    </div>
</main>

<style>
.table-row:hover { background: rgba(92, 84, 229, 0.02); }
</style>

<?php include 'footer.php'; ?>
