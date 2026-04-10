<?php 
include 'header.php'; 

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$id = clean($_GET['id']);
$product = $db->fetch("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = '$id'");

if (!$product) {
    redirect('products.php');
}

$packages = $db->query("SELECT * FROM packages WHERE product_id = '$id' ORDER BY price ASC");
?>

<main class="container" style="padding: 30px 20px 60px;">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 10px; margin-bottom: 40px; font-size: 0.9rem; opacity: 0.6;">
        <a href="index.php" style="color: var(--text-light); text-decoration: none;">Trang Chủ</a>
        <span>/</span>
        <a href="products.php" style="color: var(--text-light); text-decoration: none;">Sản Phẩm</a>
        <span>/</span>
        <span style="color: var(--primary); font-weight: 700;"><?php echo $product['name']; ?></span>
    </div>

    <div class="main-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 50px; align-items: start;">
        <!-- Left: Image & Info -->
        <div class="glass-card spotlight" style="padding: 30px; border-radius: 24px;">
            <div style="width: 100%; max-width: 400px; margin: 0 auto 30px; background: rgba(255,255,255,0.05); border-radius: 20px; padding: 30px; display: flex; align-items: center; justify-content: center;">
                <?php echo render_product_image($product['image'], $product['name'], '120px', '3rem'); ?>
            </div>
            
            <div class="badge bg-gradient" style="margin-bottom: 10px; color: white;">Best Seller</div>
            <h1 style="font-size: 1.7rem; font-weight: 800; margin-bottom: 15px; letter-spacing: -1px;"><?php echo $product['name']; ?></h1>
            
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; padding-bottom: 25px; border-bottom: 1px solid var(--glass-border);">
                <div style="display: flex; align-items: center; gap: 4px; color: #f59e0b; font-size: 0.8rem;">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <span style="font-size: 0.8rem; opacity: 0.6;">(99+ đánh giá)</span>
            </div>

            <div style="text-align: left; font-size: 1rem; opacity: 0.8; line-height: 1.8;">
                <h4 style="font-weight: 800; margin-bottom: 15px; color: var(--text-light);">Đặc điểm nổi bật:</h4>
                <div style="display: grid; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span>Bảo hành trọn đời dịch vụ</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span>Xử lý tự động nhanh chóng</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981;"></i>
                        <span>Giá ưu đãi nhất thị trường</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Selection & Purchase -->
        <div class="glass-card" style="padding: 30px; border-radius: 24px;">
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 8px;">Cấu hình dịch vụ</h3>
                <p style="font-size: 0.85rem; opacity: 0.6;">Chọn gói phù hợp với nhu cầu của bạn</p>
            </div>
            
            <form action="checkout.php" method="POST" id="purchase-form">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                
                <div style="display: grid; gap: 12px; margin-bottom: 30px;">
                    <?php 
                    $first = true;
                    while($pkg = $packages->fetch_assoc()): 
                    ?>
                    <label class="package-option <?php echo $first ? 'selected' : ''; ?>" style="display: flex; justify-content: space-between; align-items: center; padding: 18px 22px; border-radius: 16px; border: 2px solid var(--glass-border); background: var(--glass-bg); cursor: pointer; transition: 0.3s; position: relative; overflow: hidden;">
                        <input type="radio" name="package_id" value="<?php echo $pkg['id']; ?>" <?php echo $first ? 'checked' : ''; ?> style="display: none;" onchange="updateSelection(this)">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div class="radio-indicator" style="width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--glass-border); display: flex; align-items: center; justify-content: center; transition: 0.3s;">
                                <div style="width: 9px; height: 9px; border-radius: 50%; background: var(--primary); opacity: 0; transition: 0.3s;"></div>
                            </div>
                            <div>
                                <span style="font-weight: 800; display: block; font-size: 0.95rem; margin-bottom: 2px;"><?php echo $pkg['name']; ?></span>
                                <span style="font-size: 0.75rem; opacity: 0.6;">Sẵn có: <span style="color: #10b981; font-weight: 700;">Có sẵn</span></span>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 800; color: var(--primary); font-size: 1.15rem;">
                                <?php echo format_currency($pkg['price']); ?>
                            </div>
                        </div>
                    </label>
                    <?php 
                        $first = false; 
                    endwhile; 
                    ?>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 800; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Gmail Nhận Hàng</label>
                    <div style="position: relative;">
                        <i class="far fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); opacity: 0.4; font-size: 0.9rem;"></i>
                        <input type="email" name="service_data" required placeholder="Nhập Gmail để nhận TK/MK" 
                            style="width: 100%; padding: 14px 18px 14px 48px; border-radius: 14px; border: 1px solid var(--glass-border); background: var(--bg-light); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.92rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                    <button type="submit" class="btn-premium" style="width: 100%; padding: 22px; font-size: 1.1rem; border-radius: 20px; box-shadow: 0 10px 25px rgba(92, 84, 229, 0.4);">
                        <i class="fas fa-shopping-cart" style="margin-right: 10px;"></i> XÁC NHẬN THANH TOÁN
                    </button>
                <?php else: ?>
                    <a href="login.php" class="btn-secondary" style="width: 100%; padding: 20px; text-align: center; display: block; text-decoration: none; border-radius: 20px;">
                        <i class="fas fa-lock" style="margin-right: 10px;"></i> ĐĂNG NHẬP ĐỂ MUA HÀNG
                    </a>
                <?php endif; ?>
            </form>

            <div style="margin-top: 30px; padding: 18px; border-radius: 16px; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1);">
                <div style="display: flex; gap: 12px; font-size: 0.85rem; align-items: flex-start;">
                    <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.8rem;"><i class="fas fa-bolt"></i></div>
                    <p style="opacity: 0.8; line-height: 1.5;"><strong>Gmail:</strong> TK/MK sẽ được gửi ngay về Gmail của bạn sau khi thanh toán.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Sticky Buy Bar (Mobile) -->
<div class="mobile-buy-bar glass">
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; gap: 15px;">
        <div style="flex: 1;">
            <p style="font-size: 0.75rem; opacity: 0.5; margin-bottom: 2px;">Giá ưu đãi</p>
            <p id="stickyPrice" style="font-weight: 800; color: var(--primary); font-size: 1.2rem;"><?php echo format_currency(0); ?></p>
        </div>
        <button onclick="document.getElementById('purchase-form').submit()" class="btn-premium" style="padding: 12px 25px; border-radius: 12px; font-size: 0.95rem;">
            Mua Ngay
        </button>
    </div>
</div>

<script>
function updateSelection(radio) {
    const options = document.querySelectorAll('.package-option');
    options.forEach(o => o.classList.remove('selected'));
    
    const selectedOption = radio.closest('.package-option');
    selectedOption.classList.add('selected');
    
    // Update sticky price
    const priceEl = selectedOption.querySelector('[style*="1.15rem"]');
    if (priceEl) document.getElementById('stickyPrice').innerText = priceEl.innerText;
}

// Initial price update
window.onload = () => {
    const active = document.querySelector('.package-option.selected [style*="1.15rem"]');
    if (active) document.getElementById('stickyPrice').innerText = active.innerText;
}
</script>

<style>
.package-option.selected {
    border-color: var(--primary) !important;
    background: rgba(92, 84, 229, 0.05) !important;
    box-shadow: 0 10px 25px rgba(92, 84, 229, 0.08);
}
.package-option.selected .radio-indicator {
    border-color: var(--primary);
}
.package-option.selected .radio-indicator div {
    opacity: 1;
}
.mobile-buy-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    border-top: 1px solid var(--glass-border);
    display: none;
    animation: slideUp 0.5s ease;
}
@media (max-width: 768px) {
    .package-option {
        padding: 15px 18px !important;
        border-radius: 16px !important;
    }
    .package-option [style*="font-size: 1.1rem"] {
        font-size: 0.95rem !important;
    }
    .package-option [style*="font-size: 1.3rem"] {
        font-size: 1.1rem !important;
    }
    .main-grid {
        grid-template-columns: 1fr !important;
        gap: 30px !important;
    }
}
</style>

<?php include 'footer.php'; ?>
