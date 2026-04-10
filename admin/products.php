<?php 
include 'header.php'; 

// Handle Product Deletion
if (isset($_GET['delete'])) {
    $del_id = clean($_GET['delete']);
    $db->query("DELETE FROM products WHERE id = '$del_id'");
    alert('success', 'Đã xóa sản phẩm thành công.');
    redirect('admin/products.php');
}

// Handle Global API Sync
if (isset($_GET['sync_all'])) {
    require_once '../includes/lib/smm_api.php';
    $api_key = get_setting('smm_api_key');
    $api_url = get_setting('smm_api_url') ?: 'https://autosub.vn/api/v2';
    
    if ($api_key && $api_url) {
        $api = new SMMApi($api_key, $api_url);
        $services = $api->getServices();
        
        if (!isset($services['error']) && is_array($services)) {
            $updated = 0;
            $packages = $db->query("SELECT * FROM packages WHERE api_service_id IS NOT NULL");
            
            while ($pkg = $packages->fetch_assoc()) {
                foreach ($services as $srv) {
                    if ($srv['service'] == $pkg['api_service_id']) {
                        $rate = floatval($srv['rate']);
                        if ($rate != $pkg['original_price']) {
                            $db->query("UPDATE packages SET original_price = $rate WHERE id = " . $pkg['id']);
                            $updated++;
                        }
                        break;
                    }
                }
            }
            alert('success', "Đã đồng bộ giá gốc toàn hệ thống thành công ($updated gói thay đổi).");
        } else {
            alert('error', 'Lỗi kết nối API: ' . json_encode($services));
        }
    } else {
        alert('error', 'Chưa cấu hình API Key trong cài đặt.');
    }
    redirect('admin/products.php');
}

// Fetch all products
$products = $db->query("SELECT p.*, c.name as cat_name, (SELECT COUNT(*) FROM packages WHERE product_id = p.id) as pkg_count 
                       FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.created_at DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
    <div>
        <h3 style="font-weight: 800; font-size: 1.5rem; margin-bottom: 5px;">Danh Sách Sản Phẩm</h3>
        <p style="opacity: 0.6; font-size: 0.9rem;">Quản lý kho tài khoản và các gói dịch vụ</p>
    </div>
    <div style="display: flex; gap: 15px;">
        <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
            <input type="text" placeholder="Tìm sản phẩm..." style="padding: 12px 15px 12px 40px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-size: 0.9rem; width: 250px;">
        </div>
        <a href="?sync_all=1" onclick="return confirm('Tiến hành đồng bộ giá gốc của toàn bộ Server từ API?')" class="btn-secondary" style="padding: 12px 20px; border-radius: 12px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">
            <i class="fas fa-sync-alt"></i> Đồng bộ hệ thống
        </a>
        <a href="product_add.php" class="btn-premium" style="padding: 12px 25px; border-radius: 12px;">
            <i class="fas fa-plus"></i> <span style="margin-left: 5px;">Thêm mới</span>
        </a>
    </div>
</div>

<div class="glass-card" style="padding: 35px; border-radius: 24px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 15px 10px;">ID</th>
                    <th style="padding: 15px 10px;">Sản phẩm</th>
                    <th style="padding: 15px 10px;">Chuyên mục</th>
                    <th style="padding: 15px 10px;">Gói dịch vụ</th>
                    <th style="padding: 15px 10px;">Trạng thái</th>
                    <th style="padding: 15px 10px;">Nổi bật</th>
                    <th style="padding: 15px 10px; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $products->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                    <td style="padding: 20px 10px; font-weight: 800; opacity: 0.5;">#<?php echo $p['id']; ?></td>
                    <td style="padding: 20px 10px;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(255,255,255,0.05); padding: 5px; border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; overflow: hidden;">
                                <?php echo render_product_image($p['image'] ?? '', $p['name'], '40px', '1.1rem'); ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-light);"><?php echo $p['name']; ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.5;">ID: <?php echo $p['id']; ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <span class="badge" style="background: rgba(92, 84, 229, 0.05); color: var(--primary); font-size: 0.8rem;"><?php echo $p['cat_name']; ?></span>
                    </td>
                    <td style="padding: 20px 10px;">
                        <div style="font-weight: 700; opacity: 0.8;"><?php echo $p['pkg_count']; ?> gói</div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php if($p['status'] == 'active'): ?>
                            <div style="display: flex; align-items: center; gap: 8px; color: #10b981; font-weight: 700; font-size: 0.85rem;">
                                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div> Hiện
                            </div>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; gap: 8px; color: #ef4444; font-weight: 700; font-size: 0.85rem;">
                                <div style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></div> Ẩn
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php if($p['is_featured']): ?>
                            <i class="fas fa-star" style="color: #f59e0b;" title="Nổi bật"></i>
                        <?php else: ?>
                            <i class="far fa-star" style="opacity: 0.2;"></i>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 20px 10px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <a href="product_edit.php?id=<?php echo $p['id']; ?>" class="action-btn" style="color: var(--primary); background: rgba(92, 84, 229, 0.05);" title="Chỉnh sửa"><i class="fas fa-pen"></i></a>
                            <a href="packages.php?product_id=<?php echo $p['id']; ?>" class="action-btn" style="color: #10b981; background: rgba(16, 185, 129, 0.05);" title="Quản lý gói"><i class="fas fa-cubes"></i></a>
                            <a href="?delete=<?php echo $p['id']; ?>" onclick="return confirm('Xác nhận xóa sản phẩm này?')" class="action-btn" style="color: #ef4444; background: rgba(239, 68, 68, 0.05);" title="Xóa"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-row:hover { background: rgba(92, 84, 229, 0.02); }
.action-btn {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: 0.3s;
    font-size: 0.9rem;
}
.action-btn:hover { transform: translateY(-2px); }
</style>

</main>
</body>
</html>
