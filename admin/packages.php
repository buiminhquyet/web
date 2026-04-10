<?php 
include 'header.php'; 
require_once '../includes/lib/smm_api.php';

$product_id = isset($_GET['product_id']) ? clean($_GET['product_id']) : null;

if (!$product_id) {
    redirect('admin/products.php');
}

$product = $db->fetch("SELECT * FROM products WHERE id = '$product_id'");

// Handle Sync Prices via API
if (isset($_GET['sync_prices'])) {
    $api_key = get_setting('smm_api_key');
    $api_url = get_setting('smm_api_url') ?: 'https://autosub.vn/api/v2';
    
    if ($api_key && $api_url) {
        $api = new SMMApi($api_key, $api_url);
        $services = $api->getServices();
        
        if (!isset($services['error']) && is_array($services)) {
            $updated = 0;
            $packages = $db->query("SELECT * FROM packages WHERE product_id = '$product_id' AND api_service_id IS NOT NULL");
            
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
            alert('success', "Đã đồng bộ giá gốc thành công ($updated gói thay đổi).");
        } else {
            alert('error', 'Lỗi kết nối API: ' . json_encode($services));
        }
    } else {
        alert('error', 'Chưa cấu hình API Key trong cài đặt.');
    }
    redirect('admin/packages.php?product_id=' . $product_id);
}

// Handle Add/Edit Package
if (isset($_POST['save_package'])) {
    $pkg_id = isset($_POST['pkg_id']) ? clean($_POST['pkg_id']) : null;
    $name = clean($_POST['name']);
    $price = clean($_POST['price']);
    $stock_content = clean($_POST['stock_content']);
    $api_service_id = clean($_POST['api_service_id']);
    $original_price = clean($_POST['original_price']);

    // Auto-calculate stock if content is provided
    if (!empty(trim($stock_content))) {
        $lines = explode("\n", str_replace("\r", "", trim($stock_content)));
        $stock = count($lines);
    } else {
        $stock = clean($_POST['stock']);
    }

    if ($pkg_id) {
        $db->query("UPDATE packages SET name = '$name', price = '$price', stock = '$stock', stock_content = '$stock_content', api_service_id = '$api_service_id', original_price = '$original_price' WHERE id = '$pkg_id'");
        alert('success', 'Đã cập nhật gói thành công.');
    } else {
        $db->query("INSERT INTO packages (product_id, name, price, stock, stock_content, api_service_id, original_price) VALUES ('$product_id', '$name', '$price', '$stock', '$stock_content', '$api_service_id', '$original_price')");
        alert('success', 'Đã thêm gói mới thành công.');
    }
    redirect('admin/packages.php?product_id=' . $product_id);
}

// Handle Delete Package
if (isset($_GET['delete'])) {
    $del_id = clean($_GET['delete']);
    $db->query("DELETE FROM packages WHERE id = '$del_id'");
    alert('success', 'Đã xóa gói thành công.');
    redirect('admin/packages.php?product_id=' . $product_id);
}

// Fetch packages
$packages = $db->query("SELECT * FROM packages WHERE product_id = '$product_id'");
?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
    <div>
        <h3 style="font-weight: 800; font-size: 1.5rem; margin-bottom: 5px;">Quản Lý Gói Dịch Vụ</h3>
        <p style="opacity: 0.6; font-size: 0.9rem;">Sản phẩm: <span style="font-weight: 800; color: var(--primary);"><?php echo $product['name']; ?></span></p>
    </div>
    <div style="display: flex; gap: 15px;">
        <a href="products.php" class="btn-secondary" style="padding: 12px 20px; border-radius: 12px;"><i class="fas fa-arrow-left"></i> Quay lại</a>
        <a href="?product_id=<?php echo $product_id; ?>&sync_prices=1" class="btn-secondary" style="padding: 12px 20px; border-radius: 12px; background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2);">
            <i class="fas fa-sync-alt"></i> Đồng Bộ Giá API
        </a>
        <button onclick="openModal()" class="btn-premium" style="padding: 12px 25px; border-radius: 12px;">
            <i class="fas fa-plus"></i> Thêm gói mới
        </button>
    </div>
</div>

<div class="glass-card" style="padding: 35px; border-radius: 24px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 15px 10px;">ID</th>
                    <th style="padding: 15px 10px;">Tên gói / ID API</th>
                    <th style="padding: 15px 10px;">Giá bán / Giá gốc</th>
                    <th style="padding: 15px 10px;">Lợi nhuận</th>
                    <th style="padding: 15px 10px; text-align: right;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($packages->num_rows > 0): while($pkg = $packages->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                    <td style="padding: 20px 10px; opacity: 0.5; font-weight: 800;">#<?php echo $pkg['id']; ?></td>
                    <td style="padding: 20px 10px;">
                        <div style="font-weight: 700; color: var(--text-light);"><?php echo $pkg['name']; ?></div>
                        <div style="font-size: 0.75rem; opacity: 0.6;">API ID: <?php echo $pkg['api_service_id'] ?: 'N/A'; ?></div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div>
                                <span style="font-size: 0.7rem; opacity: 0.5; display: block; margin-bottom: 2px;">Bán ra</span>
                                <span style="font-weight: 800; color: #ff0169;"><?php echo format_currency($pkg['price']); ?></span>
                            </div>
                            <div>
                                <span style="font-size: 0.7rem; opacity: 0.5; display: block; margin-bottom: 2px;">Giá gốc (API)</span>
                                <span style="font-weight: 800; color: #38bdf8;"><?php echo format_currency($pkg['original_price']); ?></span>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php 
                        $profit = $pkg['price'] - $pkg['original_price'];
                        $is_loss = $profit < 0;
                        $color = $is_loss ? '#ef4444' : '#10b981';
                        $bg = $is_loss ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)';
                        // Calculate percentage
                        $margin_pct = $pkg['original_price'] > 0 ? round(($profit / $pkg['original_price']) * 100) : 100;
                        ?>
                        <span class="badge" style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>; font-weight: 800; padding: 6px 12px; font-size: 0.8rem;">
                            <?php echo format_currency($profit); ?> (<?php echo $is_loss ? '' : '+'; ?><?php echo $margin_pct; ?>%)
                        </span>
                        <?php if($is_loss): ?>
                        <div style="font-size:0.7rem; color:#ef4444; margin-top:5px; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Đang bán lỗ!</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 20px 10px; text-align: right;">
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick='editPackage(<?php echo json_encode($pkg); ?>)' class="action-btn" style="color: var(--primary); background: rgba(92, 84, 229, 0.05); border:none;"><i class="fas fa-pen"></i></button>
                            <a href="?product_id=<?php echo $product_id; ?>&delete=<?php echo $pkg['id']; ?>" onclick="return confirm('Xóa gói này?')" class="action-btn" style="color: #ef4444; background: rgba(239, 68, 68, 0.05);"><i class="fas fa-trash-alt"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr>
                    <td colspan="5" style="padding: 50px; text-align: center; opacity: 0.5;">
                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px;"></i>
                        <p>Chưa có gói dịch vụ nào cho sản phẩm này.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="pkgModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); backdrop-filter: blur(5px); z-index: 2000; align-items: center; justify-content: center;">
    <div class="glass-card" style="width: 100%; max-width: 600px; padding: 40px; border-radius: 30px; max-height: 90vh; overflow-y: auto;">
        <h3 id="modalTitle" style="font-weight: 800; margin-bottom: 30px;">Thêm Gói Mới</h3>
        <form action="" method="POST">
            <input type="hidden" name="pkg_id" id="pkg_id">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Tên gói (Ví dụ: Sv1 Follow Bảo Hành)</label>
                <input type="text" name="name" id="pkg_name" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Giá bán (VNĐ)</label>
                <input type="number" step="0.01" name="price" id="pkg_price" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">SMM Service ID (API)</label>
                    <input type="number" name="api_service_id" id="pkg_api_id" placeholder="VD: 21" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Giá gốc (API)</label>
                    <input type="number" step="0.01" name="original_price" id="pkg_original_price" placeholder="0" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Số lượng tồn kho</label>
                    <input type="number" name="stock" id="pkg_stock" value="100" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none;">
                </div>
                <div style="display: flex; align-items: flex-end; padding-bottom: 5px; font-size: 0.75rem; opacity: 0.5;">
                    * Tự động tính nếu nhập kho bên dưới
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Danh sách tài khoản (Mỗi dòng 1 tài khoản)</label>
                <textarea name="stock_content" id="pkg_stock_content" rows="6" placeholder="Nếu bán acc, hãy nhập vào đây. Hệ thống sẽ tự động trừ đi mỗi khi có khách mua." style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-family: monospace; font-size: 0.85rem;"></textarea>
            </div>
            <div style="display: flex; gap: 15px;">
                <button type="button" onclick="closeModal()" class="btn-secondary" style="flex: 1; padding: 12px;">Hủy</button>
                <button type="submit" name="save_package" class="btn-premium" style="flex: 1; padding: 12px;">Lưu lại</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('pkgModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Thêm Gói Mới';
    document.getElementById('pkg_id').value = '';
    document.getElementById('pkg_name').value = '';
    document.getElementById('pkg_price').value = '';
    document.getElementById('pkg_api_id').value = '';
    document.getElementById('pkg_original_price').value = '';
    document.getElementById('pkg_stock').value = '100';
    document.getElementById('pkg_stock_content').value = '';
}
function closeModal() {
    document.getElementById('pkgModal').style.display = 'none';
}
function editPackage(pkg) {
    document.getElementById('pkgModal').style.display = 'flex';
    document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Gói';
    document.getElementById('pkg_id').value = pkg.id;
    document.getElementById('pkg_name').value = pkg.name;
    document.getElementById('pkg_price').value = pkg.price;
    document.getElementById('pkg_api_id').value = pkg.api_service_id;
    document.getElementById('pkg_original_price').value = pkg.original_price;
    document.getElementById('pkg_stock').value = pkg.stock;
    document.getElementById('pkg_stock_content').value = pkg.stock_content || '';
}
</script>

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
    cursor: pointer;
}
.action-btn:hover { transform: translateY(-2px); }
</style>

</main>
</body>
</html>
