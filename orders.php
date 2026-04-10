<?php 
require_once 'includes/config.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = get_user($_SESSION['user_id']);
$orders = $db->query("SELECT o.*, p.name as product_name, pkg.name as package_name 
                     FROM orders o 
                     JOIN packages pkg ON o.package_id = pkg.id 
                     JOIN products p ON pkg.product_id = p.id 
                     WHERE o.user_id = '{$user['id']}' 
                     ORDER BY o.created_at DESC");

include 'header.php'; 
?>

<main class="container section-padding">
    <div style="max-width: 1000px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 class="text-gradient" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px;">Lịch Sử Đơn Hàng</h1>
            <p style="opacity: 0.6; font-size: 1rem; max-width: 600px; margin: 0 auto; line-height: 1.7;">
                Xem lại toàn bộ các dịch vụ và tài khoản bạn đã mua trên hệ thống.
            </p>
        </div>

        <div class="glass-card Spotlight" style="padding: 40px; border-radius: 30px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                    <thead>
                        <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                            <th style="padding: 15px 10px;">Mã Đơn</th>
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
                            <td style="padding: 20px 10px; font-weight: 700; font-family: monospace;">#<?php echo $o['id']; ?></td>
                            <td style="padding: 20px 10px;">
                                <div style="font-weight: 700; color: var(--text-light);"><?php echo htmlspecialchars($o['product_name']); ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.5;"><?php echo htmlspecialchars($o['package_name']); ?></div>
                            </td>
                            <td style="padding: 20px 10px; font-size: 0.85rem; opacity: 0.9;">
                                <?php echo format_service_data($o['service_data']); ?>
                            </td>
                            <td style="padding: 20px 10px; font-weight: 800; color: #ff0169;"><?php echo format_currency($o['total_price']); ?></td>
                             <td style="padding: 20px 10px;">
                                <?php 
                                $st = strtolower($o['status']);
                                if ($st == 'completed' || $st == 'success') : ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800;">
                                        THÀNH CÔNG
                                    </span>
                                <?php elseif ($st == 'pending') : ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.7rem; font-weight: 800;">
                                        ĐANG CHỜ XỬ LÝ
                                    </span>
                                <?php elseif ($st == 'processing' || $st == 'in progress') : ?>
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.7rem; font-weight: 800;">
                                        ĐANG CHẠY
                                    </span>
                                <?php else : ?>
                                    <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e; font-size: 0.7rem; font-weight: 800;">
                                        THẤT BẠI
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 20px 10px; opacity: 0.5; font-size: 0.85rem;"><?php echo date('d/m/Y H:i', strtotime($o['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" style="padding: 80px; text-align: center; opacity: 0.4;">
                                <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                Bạn chưa có đơn hàng nào.
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
