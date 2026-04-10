<?php 
include 'header.php'; 

$id = isset($_GET['id']) ? clean($_GET['id']) : null;
if (!$id) redirect('admin/products.php');

$prod = $db->fetch("SELECT * FROM products WHERE id = '$id'");
if (!$prod) redirect('admin/products.php');

if (isset($_POST['save_product'])) {
    $name = clean($_POST['name']);
    $category_id = clean($_POST['category_id']);
    $description = clean($_POST['description']);
    $status = clean($_POST['status']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    $image = $prod['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $upload_path = '../assets/images/products/' . $new_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image = 'products/' . $new_name;
            }
        }
    }

    $db->query("UPDATE products SET 
                name = '$name', 
                category_id = '$category_id', 
                description = '$description', 
                status = '$status', 
                is_featured = '$is_featured', 
                image = '$image' 
                WHERE id = '$id'");
                
    alert('success', 'Đã cập nhật sản phẩm thành công.');
    redirect('admin/products.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC");
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="section-header-flex" style="margin-bottom: 30px;">
        <h3 style="font-weight: 800; font-size: 1.5rem; margin: 0;">Chỉnh Sửa Sản Phẩm</h3>
        <div style="display: flex; gap: 12px; width: 100%; justify-content: flex-end;">
            <button type="submit" form="editProductForm" class="btn-premium" style="padding: 10px 25px; border-radius: 12px; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-save"></i> CẬP NHẬT
            </button>
            <a href="products.php" class="btn-secondary" style="padding: 10px 20px; border-radius: 12px;"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </div>

    <div class="glass-card" style="padding: 40px; border-radius: 24px;">
        <form id="editProductForm" method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Tên sản phẩm</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($prod['name']); ?>" required style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--bg-card); color: var(--text-light); outline: none;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Chuyên mục</label>
                    <select name="category_id" required style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--bg-card); color: var(--text-light); outline: none;">
                        <?php while($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $prod['category_id'] ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Mô tả sản phẩm</label>
                <textarea name="description" rows="4" style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--bg-card); color: var(--text-light); outline: none;"><?php echo htmlspecialchars($prod['description']); ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Ảnh sản phẩm</label>
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <div id="imagePreview" style="width: 50px; height: 50px; border-radius: 12px; border: 1px solid var(--glass-border); display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--glass-bg);">
                            <?php echo render_product_image($prod['image'] ?? '', $prod['name'], '40px', '1rem'); ?>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="image" id="productImageInput" accept="image/*" style="display: none;" onchange="previewImg(this)">
                            <button type="button" onclick="document.getElementById('productImageInput').click()" class="btn-secondary" style="width: 100%; border-radius: 10px; padding: 10px; font-size: 0.8rem;">
                                <i class="fas fa-camera"></i> Thay Đổi Ảnh
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Trạng thái</label>
                    <select name="status" style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--bg-card); color: var(--text-light); outline: none;">
                        <option value="active" <?php echo $prod['status'] == 'active' ? 'selected' : ''; ?>>Đang bán</option>
                        <option value="inactive" <?php echo $prod['status'] == 'inactive' ? 'selected' : ''; ?>>Ngừng bán</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 35px; display: flex; align-items: center; gap: 12px;">
                <input type="checkbox" name="is_featured" id="feat" <?php echo $prod['is_featured'] ? 'checked' : ''; ?> style="width: 18px; height: 18px; accent-color: var(--primary);">
                <label for="feat" style="font-weight: 700; opacity: 0.8; cursor: pointer;">Đánh dấu là sản phẩm nổi bật</label>
            </div>

            <button type="submit" name="save_product" class="btn-premium" style="width: 100%; padding: 18px; border-radius: 14px; font-weight: 800; font-size: 1rem;">
                <i class="fas fa-save" style="margin-right: 10px;"></i> LƯU THAY ĐỔI
            </button>
        </form>
    </div>
</div>

<script>
function previewImg(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            preview.style.borderColor = 'var(--primary)';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

</main>
</body>
</html>
