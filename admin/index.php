<?php 
include 'header.php'; 

// Fetch Stats
$total_users = $db->fetch("SELECT count(*) as count FROM users")['count'];
$total_products = $db->fetch("SELECT count(*) as count FROM products")['count'];
$total_orders = $db->fetch("SELECT count(*) as count FROM orders")['count'];
$total_revenue = $db->fetch("SELECT sum(total_price) as sum FROM orders WHERE status = 'completed'")['sum'] ?? 0;

$recent_orders = $db->query("SELECT o.*, u.username, p.name as product_name 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.id 
                            LEFT JOIN packages pkg ON o.package_id = pkg.id 
                            LEFT JOIN products p ON pkg.product_id = p.id 
                            ORDER BY o.created_at DESC LIMIT 8");
?>

<div class="stat-grid">
    <!-- Stat Cards -->
    <div class="glass-card spotlight" style="padding: 25px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div style="width: 45px; height: 45px; background: rgba(92, 84, 229, 0.1); color: var(--primary); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-users"></i>
            </div>
            <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">+12% <i class="fas fa-arrow-up"></i></span>
        </div>
        <p style="font-size: 0.85rem; opacity: 0.6; font-weight: 600; margin-bottom: 5px;">Thành Viên</p>
        <h2 style="font-weight: 800;"><?php echo number_format($total_users); ?></h2>
    </div>

    <div class="glass-card spotlight" style="padding: 25px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div style="width: 45px; height: 45px; background: rgba(255, 1, 105, 0.1); color: #FF0169; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-box"></i>
            </div>
            <span style="font-size: 0.75rem; opacity: 0.5;">Active</span>
        </div>
        <p style="font-size: 0.85rem; opacity: 0.6; font-weight: 600; margin-bottom: 5px;">Sản Phẩm</p>
        <h2 style="font-weight: 800;"><?php echo number_format($total_products); ?></h2>
    </div>

    <div class="glass-card spotlight" style="padding: 25px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div style="width: 45px; height: 45px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">+5% <i class="fas fa-arrow-up"></i></span>
        </div>
        <p style="font-size: 0.85rem; opacity: 0.6; font-weight: 600; margin-bottom: 5px;">Đơn Hàng</p>
        <h2 style="font-weight: 800;"><?php echo number_format($total_orders); ?></h2>
    </div>

    <div class="glass-card spotlight" style="padding: 25px; border-radius: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
            <div style="width: 45px; height: 45px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-wallet"></i>
            </div>
            <span style="font-size: 0.75rem; color: #10b981; font-weight: 700;">+20% <i class="fas fa-arrow-up"></i></span>
        </div>
        <p style="font-size: 0.85rem; opacity: 0.6; font-weight: 600; margin-bottom: 5px;">Doanh Thu</p>
        <h2 class="text-gradient" style="font-weight: 800;"><?php echo format_currency($total_revenue); ?></h2>
    </div>
</div>

<div class="main-grid">
    <!-- Recent Activity -->
    <div class="glass-card" style="padding: 35px; border-radius: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h3 style="font-weight: 800;"><i class="fas fa-history" style="margin-right: 10px; opacity: 0.5;"></i> Đơn hàng gần đây</h3>
            <a href="orders.php" class="btn-secondary" style="padding: 8px 20px; font-size: 0.85rem; border-radius: 10px;">Xem tất cả</a>
        </div>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                        <th style="padding: 15px 10px;">Thành viên</th>
                        <th style="padding: 15px 10px;">Sản phẩm</th>
                        <th style="padding: 15px 10px;">Số tiền</th>
                        <th style="padding: 15px 10px;">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($o = $recent_orders->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid var(--glass-border);">
                        <td style="padding: 18px 10px;">
                            <div style="font-weight: 700; color: var(--text-light);"><?php echo $o['username']; ?></div>
                            <div style="font-size: 0.75rem; opacity: 0.5;"><?php echo date('H:i d/m/Y', strtotime($o['created_at'])); ?></div>
                        </td>
                        <td style="padding: 18px 10px; font-size: 0.9rem; font-weight: 600; opacity: 0.8;"><?php echo $o['product_name'] ?? 'Sản phẩm đã xóa'; ?></td>
                        <td style="padding: 18px 10px; font-weight: 800; color: #ff0169;"><?php echo format_currency($o['total_price']); ?></td>
                        <td style="padding: 18px 10px;">
                            <?php 
                            $st = strtolower($o['status']);
                            if ($st == 'completed' || $st == 'success') : ?>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.75rem; font-weight: 700;">Thành công</span>
                            <?php elseif ($st == 'pending') : ?>
                                <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.75rem; font-weight: 700;">Chờ xử lý</span>
                            <?php elseif ($st == 'processing' || $st == 'in progress') : ?>
                                <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.75rem; font-weight: 700;">Đang chạy</span>
                            <?php else : ?>
                                <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e; font-size: 0.75rem; font-weight: 700;">Thất bại</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div style="display: flex; flex-direction: column; gap: 25px;">
        <div class="glass-card spotlight" style="padding: 25px; border-radius: 20px; background: var(--primary-gradient); position: relative; overflow: hidden;">
            <h4 style="color: white; font-weight: 800; margin-bottom: 15px;">Quick Actions</h4>
            <p style="color: white; opacity: 0.8; font-size: 0.85rem; margin-bottom: 25px;">Tắt/mở nhanh các chức năng hệ thống</p>
            <div style="display: grid; gap: 10px;">
                <a href="product_add.php" class="glass" style="padding: 12px; border-radius: 12px; color: white; text-decoration: none; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">
                    <i class="fas fa-plus"></i> Thêm sản phẩm mới
                </a>
                <a href="settings.php" class="glass" style="padding: 12px; border-radius: 12px; color: white; text-decoration: none; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.2);">
                    <i class="fas fa-sliders-h"></i> Cài đặt hệ thống
                </a>
            </div>
        </div>

        <div class="glass-card" style="padding: 30px; border-radius: 24px;">
            <h4 style="font-weight: 800; margin-bottom: 20px;">System Health</h4>
            <div style="display: grid; gap: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.9rem; opacity: 0.7;">Database Status</span>
                    <span style="color: #10b981; font-weight: 800; font-size: 0.85rem;">STABLE</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.9rem; opacity: 0.7;">PHP Version</span>
                    <span style="font-weight: 800; font-size: 0.85rem; opacity: 0.8;"><?php echo PHP_VERSION; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.9rem; opacity: 0.7;">Disk Usage</span>
                    <span style="font-weight: 800; font-size: 0.85rem; opacity: 0.8;">12% Used</span>
                </div>
            </div>
        </div>
    </div>
</div>

</main>
</body>
</html>
