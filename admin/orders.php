<?php 
include 'header.php'; 

// Search & Filter Logic
$where = "WHERE 1=1";
$search = isset($_GET['search']) ? clean($_GET['search']) : '';
$status = isset($_GET['status']) ? clean($_GET['status']) : '';

if (!empty($search)) {
    if (is_numeric($search)) {
        $where .= " AND o.id = '$search'";
    } else {
        $where .= " AND u.username LIKE '%$search%'";
    }
}

if (!empty($status)) {
    $where .= " AND o.status = '$status'";
}

// Fetch orders with filters
$orders = $db->query("SELECT o.*, u.username, p.name as product_name, pkg.name as package_name 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     JOIN packages pkg ON o.package_id = pkg.id 
                     JOIN products p ON pkg.product_id = p.id 
                     $where
                     ORDER BY o.created_at DESC");
?>

<div class="admin-orders-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; gap: 20px; flex-wrap: wrap;">
    <div>
        <h3 style="font-weight: 800; font-size: 1.5rem; margin-bottom: 5px;">Quản Lý Đơn Hàng</h3>
        <p style="opacity: 0.6; font-size: 0.9rem;">Xem và kiểm soát tất cả giao dịch trên hệ thống</p>
    </div>
    <form method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; align-items: center;">
        <select name="status" onchange="this.form.submit()" style="padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-size: 0.9rem;">
            <option value="">Tất cả trạng thái</option>
            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Thành công</option>
            <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
            <option value="failed" <?php echo $status == 'failed' ? 'selected' : ''; ?>>Thất bại</option>
        </select>
        <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
            <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Tìm ID hoặc Username..." style="padding: 12px 15px 12px 40px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-size: 0.9rem; min-width: 250px;">
        </div>
        <button type="submit" class="btn-premium" style="padding: 12px 20px; border-radius: 12px;"><i class="fas fa-filter"></i> Lọc</button>
        <?php if(!empty($search) || !empty($status)): ?>
            <a href="orders.php" class="btn-secondary" style="padding: 12px 20px; border-radius: 12px; text-decoration: none; display: flex; align-items: center; justify-content: center;"><i class="fas fa-redo"></i> Reset</a>
        <?php endif; ?>
    </form>
</div>

<div class="glass-card" style="padding: 35px; border-radius: 24px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1000px;">
            <thead>
                <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 15px 10px;">ID ĐH</th>
                    <th style="padding: 15px 10px;">Khách hàng</th>
                    <th style="padding: 15px 10px;">Sản phẩm / Gói</th>
                    <th style="padding: 15px 10px;">Phân loại</th>
                    <th style="padding: 15px 10px;">Thông tin nhận</th>
                    <th style="padding: 15px 10px;">Thanh toán</th>
                    <th style="padding: 15px 10px;">Thời gian</th>
                    <th style="padding: 15px 10px;">Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows > 0): while($o = $orders->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                    <td style="padding: 20px 10px; font-weight: 800; opacity: 0.5;">#<?php echo $o['id']; ?></td>
                    <td style="padding: 20px 10px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"><i class="fas fa-user"></i></div>
                            <span style="font-weight: 700; opacity: 0.9;"><?php echo htmlspecialchars($o['username']); ?></span>
                        </div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <div style="font-weight: 700; color: var(--text-light);"><?php echo htmlspecialchars($o['product_name']); ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.5;"><?php echo htmlspecialchars($o['package_name']); ?></div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php 
                        // Logic to detect type based on session data or naming convention
                        $is_service = (strpos($o['service_data'], 'link') !== false || strpos($o['service_data'], 'quantity') !== false);
                        if ($is_service): ?>
                            <span class="badge" style="background: rgba(124, 58, 237, 0.1); color: #7c3aed; font-size: 0.7rem; font-weight: 800;">DỊCH VỤ API</span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.7rem; font-weight: 800;">SẢN PHẨM</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 20px 10px;">
                        <div style="padding: 6px 12px; background: rgba(255,255,255,0.05); border-radius: 8px; font-size: 0.85rem; display: inline-block; color: var(--primary-2); line-height: 1.4;">
                            <?php echo format_service_data($o['service_data']); ?>
                        </div>
                    </td>
                    <td style="padding: 20px 10px; font-weight: 800; color: #ff0169;"><?php echo format_currency($o['total_price']); ?></td>
                    <td style="padding: 20px 10px;">
                        <div style="font-size: 0.85rem; opacity: 0.6;"><?php echo date('H:i', strtotime($o['created_at'])); ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.4;"><?php echo date('d/m/Y', strtotime($o['created_at'])); ?></div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php 
                        $st = strtolower($o['status']);
                        if ($st == 'completed' || $st == 'success') : ?>
                            <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 0.75rem; padding: 6px 12px; border-radius: 50px; font-weight: 700;">
                                <i class="fas fa-check-circle" style="margin-right: 5px;"></i> Thành công
                            </span>
                        <?php elseif ($st == 'pending') : ?>
                            <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 0.75rem; padding: 6px 12px; border-radius: 50px; font-weight: 700;">
                                <i class="fas fa-clock" style="margin-right: 5px;"></i> Chờ xử lý
                            </span>
                        <?php elseif ($st == 'processing' || $st == 'in progress') : ?>
                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.75rem; padding: 6px 12px; border-radius: 50px; font-weight: 700;">
                                <i class="fas fa-spinner fa-spin" style="margin-right: 5px;"></i> Đang chạy
                            </span>
                        <?php else : ?>
                            <span class="badge" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e; font-size: 0.75rem; padding: 6px 12px; border-radius: 50px; font-weight: 700;">
                                <i class="fas fa-times-circle" style="margin-right: 5px;"></i> Thất bại
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="7" style="padding: 80px; text-align: center;">
                        <div style="font-size: 4rem; margin-bottom: 20px; opacity: 0.2; color: var(--primary);">
                            <i class="fas fa-shopping-basket"></i>
                        </div>
                        <p style="opacity: 0.4; font-weight: 600;">Hệ thống chưa ghi nhận đơn hàng nào.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-row:hover { background: rgba(255, 255, 255, 0.02); }
@media (max-width: 576px) {
    .admin-orders-header { align-items: flex-start !important; }
}
</style>

</main>
</body>
</html>
