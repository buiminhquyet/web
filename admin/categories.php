<?php 
include 'header.php'; 

// Handle Addition
if (isset($_POST['add_category'])) {
    $name = clean($_POST['name']);
    $icon = clean($_POST['icon']);
    $type = clean($_POST['category_type']);
    $db->query("INSERT INTO categories (name, icon, category_type) VALUES ('$name', '$icon', '$type')");
    alert('success', 'Đã thêm chuyên mục mới!');
    redirect('admin/categories.php');
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = clean($_GET['delete']);
    $db->query("DELETE FROM categories WHERE id = '$id'");
    alert('success', 'Đã xóa chuyên mục.');
    redirect('admin/categories.php');
}

$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC");
?>

<div class="main-grid">
    <!-- Category List -->
    <div class="glass-card" style="padding: 30px;">
        <h3 style="margin-bottom: 25px;">Danh Sách Chuyên Mục</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="text-align: left; border-bottom: 1px solid var(--glass-border); opacity: 0.6;">
                    <th style="padding: 15px 10px;">Icon</th>
                    <th style="padding: 15px 10px;">Tên Chuyên Mục</th>
                    <th style="padding: 15px 10px;">Loại</th>
                    <th style="padding: 15px 10px;">Sắp Xếp</th>
                    <th style="padding: 15px 10px;">Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = $categories->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 15px 10px; font-size: 1.2rem; color: #8A2BE2;"><i class="fas <?php echo $c['icon']; ?>"></i></td>
                    <td style="padding: 15px 10px; font-weight: 600;"><?php echo $c['name']; ?></td>
                    <td style="padding: 15px 10px;">
                        <span style="background: <?php echo $c['category_type'] == 'mxh' ? 'rgba(168,85,247,0.1)' : 'rgba(16,185,129,0.1)'; ?>; 
                                     color: <?php echo $c['category_type'] == 'mxh' ? '#a855f7' : '#10b981'; ?>; 
                                     padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                            <?php echo $c['category_type'] == 'mxh' ? 'MXH' : 'Sản Phẩm'; ?>
                        </span>
                    </td>
                    <td style="padding: 15px 10px;"><?php echo $c['display_order']; ?></td>
                    <td style="padding: 15px 10px;">
                        <a href="?delete=<?php echo $c['id']; ?>" onclick="return confirm('Xóa chuyên mục này?')" style="color: #ef4444; text-decoration: none;"><i class="fas fa-trash"></i> Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Category Form -->
    <div class="glass-card spotlight" style="padding: 30px; height: fit-content;">
        <h3 style="margin-bottom: 25px;">Thêm Chuyên Mục</h3>
        <form action="categories.php" method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Tên Chuyên Mục</label>
                <input type="text" name="name" required placeholder="Ví dụ: Tài Khoản AI" style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none;">
            </div>
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">FontAwesome Icon (vd: fa-robot)</label>
                <input type="text" name="icon" value="fa-cube" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none; color: var(--text);">
            </div>
            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px;">Loại Hiển Thị (Trực thuộc trang)</label>
                <select name="category_type" required style="width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); outline: none; color: var(--text); cursor: pointer;">
                    <option value="product">Trang SẢN PHẨM (Tài khoản, v.v)</option>
                    <option value="mxh">Trang MXH (Facebook, TikTok, v.v)</option>
                </select>
            </div>
            <button type="submit" name="add_category" class="btn-premium" style="width: 100%; padding: 15px;">
                <i class="fas fa-save"></i> Lưu Chuyên Mục
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
