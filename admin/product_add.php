<?php 
include 'header.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $cat_id = clean($_POST['category_id']);
    $desc = clean($_POST['description']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Image Handling
    $image = 'default_product.png';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['product_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_name = 'prod_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $upload_path = '../assets/images/products/' . $new_name;
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image = 'products/' . $new_name;
            }
        }
    }

    $db->query("INSERT INTO products (category_id, name, description, image, is_featured) 
                VALUES ('$cat_id', '$name', '$desc', '$image', '$is_featured')");
    $product_id = $db->insert_id();

    // Add Packages
    if (isset($_POST['package_names'])) {
        foreach ($_POST['package_names'] as $i => $pkg_name) {
            $pkg_name = clean($pkg_name);
            $pkg_price = clean($_POST['package_prices'][$i]);
            $pkg_stock = clean($_POST['package_stocks'][$i]);
            if (!empty($pkg_name)) {
                $db->query("INSERT INTO packages (product_id, name, price, stock) 
                            VALUES ('$product_id', '$pkg_name', '$pkg_price', '$pkg_stock')");
            }
        }
    }

    alert('success', 'Đã thêm sản phẩm thành công!');
    redirect('admin/products.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC");
?>

<style>
    .admin-form-container { max-width: 900px; margin: 0 auto; padding-bottom: 50px; }
    .package-grid { display: grid; grid-template-columns: 1fr 1fr 100px 50px; gap: 10px; margin-bottom: 12px; padding: 12px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border); position: relative; }
    @media (max-width: 768px) {
        .glass-card { padding: 20px !important; }
        .package-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
        .package-grid input[name*="package_names"] { grid-column: span 2; }
        .package-grid button { position: absolute; top: -5px; right: -5px; width: 25px; height: 25px; border-radius: 50%; background: #ef4444 !important; color: white !important; border: none !important; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); }
        .section-header-flex h3 { font-size: 1.1rem !important; }
        .section-header-flex button span { display: none; }
    }
</style>

<div class="admin-form-container">
    <div class="section-header-flex" style="margin-bottom: 25px;">
        <h3 style="margin: 0; font-weight: 800; font-size: 1.4rem;">Thêm Sản Phẩm Mới</h3>
        <button type="submit" form="addProductForm" class="btn-premium" style="padding: 10px 20px; border-radius: 12px; font-size: 0.82rem; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-plus"></i> <span>ĐĂNG SẢN PHẨM</span>
        </button>
    </div>

    <div class="glass-card" style="padding: 30px; border-radius: 20px;">
        <form id="addProductForm" action="product_add.php" method="POST" enctype="multipart/form-data">
        <div class="admin-grid" style="margin-bottom: 20px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Tên Sản Phẩm</label>
                <input type="text" name="name" required placeholder="Ví dụ: Netflix Premium" style="width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Chuyên Mục</label>
                <select name="category_id" style="width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;">
                    <?php while($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Mô Tả Sản Phẩm</label>
            <textarea name="description" rows="3" style="width: 100%; padding: 10px 15px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: #333; outline: none;"></textarea>
        </div>

        <div class="admin-grid" style="margin-bottom: 20px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 8px; opacity: 0.7;">Hình Ảnh Sản Phẩm</label>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <div id="imagePreview" style="width: 50px; height: 50px; border-radius: 10px; border: 2px dashed var(--glass-border); display: flex; align-items: center; justify-content: center; overflow: hidden; background: var(--glass-bg); flex-shrink: 0;">
                        <i class="fas fa-image" style="opacity: 0.3;"></i>
                    </div>
                    <div style="flex: 1;">
                        <input type="file" name="product_image" id="productImageInput" accept="image/*" style="display: none;" onchange="previewImg(this)">
                        <button type="button" onclick="document.getElementById('productImageInput').click()" class="btn-secondary" style="width: 100%; border-radius: 8px; padding: 8px; font-size: 0.75rem;">
                            <i class="fas fa-camera"></i> Chọn Ảnh
                        </button>
                    </div>
                </div>
            </div>
            <div style="display: flex; align-items: center; padding-top: 15px;">
                <label style="font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 8px; color: var(--text-dim);">
                    <input type="checkbox" name="is_featured" style="width: 18px; height: 18px; accent-color: var(--primary);"> Sản Phẩm Nổi Bật
                </label>
            </div>
        </div>

        <!-- Dynamic Packages -->
        <div style="margin-top: 30px; padding: 20px; border-radius: 15px; background: rgba(124, 58, 237, 0.04); border: 1px solid rgba(124, 58, 237, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0; font-size: 0.95rem; font-weight: 800; color: var(--primary-2);">Gói Dịch Vụ & Giá</h4>
                <button type="button" onclick="addPackage()" class="quick-save-btn" style="background: var(--primary-gradient); color: white; border: none; padding: 6px 12px;"><i class="fas fa-plus"></i> Thêm Gói</button>
            </div>

            <div id="package-list">
                <div class="package-grid">
                    <input type="text" name="package_names[]" placeholder="Tên Gói (vd: 1 Tháng)" required style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #fff; color: #333; font-size: 0.85rem;">
                    <input type="number" name="package_prices[]" placeholder="Giá (vd: 65000)" required style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #fff; color: #333; font-size: 0.85rem;">
                    <input type="number" name="package_stocks[]" placeholder="Kho" value="100" style="padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: #fff; color: #333; font-size: 0.85rem;">
                    <button type="button" class="btn-secondary" style="color: #ef4444; border: none; background: none;"><i class="fas fa-times"></i></button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-premium" style="width: 100%; margin-top: 30px; padding: 15px; font-size: 1rem; border-radius: 15px; box-shadow: 0 10px 25px rgba(124, 58, 237, 0.2);">
            <i class="fas fa-cloud-upload-alt"></i> ĐĂNG SẢN PHẨM NGAY
        </button>
    </form>
</div>

<script>
function previewImg(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
            preview.style.borderStyle = 'solid';
            preview.style.borderColor = 'var(--primary)';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function addPackage() {
    const div = document.createElement('div');
    div.className = 'package-grid';
    div.innerHTML = `
        <input type="text" name="package_names[]" placeholder="Tên Gói" required style="padding:10px; border-radius:8px; border:1px solid var(--glass-border); background:#fff; color:#333; font-size:0.85rem;">
        <input type="number" name="package_prices[]" placeholder="Giá" required style="padding:10px; border-radius:8px; border:1px solid var(--glass-border); background:#fff; color:#333; font-size:0.85rem;">
        <input type="number" name="package_stocks[]" placeholder="Kho" value="100" style="padding:10px; border-radius:8px; border:1px solid var(--glass-border); background:#fff; color:#333; font-size:0.85rem;">
        <button type="button" onclick="this.parentElement.remove()" class="btn-secondary" style="color: #ef4444; border: none; background:none;"><i class="fas fa-times"></i></button>
    `;
    document.getElementById('package-list').appendChild(div);
}
</script>

</main>
</body>
</html>
