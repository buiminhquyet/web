<?php include 'header.php'; ?>

<style>
.hero-section {
    position: relative;
    min-height: 90vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    padding: 40px 0;
}

.hero-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: center;
}

@media (max-width: 992px) {
    .hero-section { min-height: auto; padding: 100px 0 60px; }
    .hero-grid { grid-template-columns: 1fr; gap: 40px; text-align: center; }
    .hero-actions { justify-content: center; }
    .hero-desc { margin: 0 auto 30px; }
    .hero-feat { justify-content: center; }
    .hero-badge { margin: 0 auto 24px; }
}

.hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
    overflow: hidden;
}

.hero-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    animation: blobFloat 8s ease-in-out infinite;
}

@keyframes blobFloat {
    0%, 100% { transform: translate(0, 0) scale(1); }
    33% { transform: translate(20px, -20px) scale(1.05); }
    66% { transform: translate(-10px, 15px) scale(0.95); }
}

.hero-content { position: relative; z-index: 2; }

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 0.78rem;
    font-weight: 700;
    margin-bottom: 24px;
    background: rgba(124,58,237,0.15);
    border: 1px solid rgba(124,58,237,0.3);
    color: var(--primary-2);
}

.hero-badge .dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #10b981;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.3); }
}

.hero-title {
    font-size: clamp(2.5rem, 6vw, 4.5rem);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -2px;
    margin-bottom: 24px;
}

.hero-desc {
    font-size: 1.05rem;
    color: var(--text-muted);
    max-width: 540px;
    line-height: 1.8;
    margin-bottom: 40px;
}

.hero-actions { display: flex; gap: 15px; flex-wrap: wrap; margin-bottom: 50px; }

.hero-feat {
    display: flex; align-items: center; gap: 8px;
    font-size: 0.82rem; font-weight: 700; color: var(--text-muted);
}

.hero-feat i { color: #10b981; }

.hero-stats {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 20px; margin-top: 50px;
    padding-top: 40px;
    border-top: 1px solid var(--border);
}

.hero-stat-num {
    font-size: 1.8rem; font-weight: 900;
    background: var(--primary-gradient);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    line-height: 1;
}

.hero-stat-label { font-size: 0.75rem; color: var(--text-dim); font-weight: 600; margin-top: 4px; }

/* ============ SEARCH BAR (REMASTERED) ============ */
.search-bar-wrap {
    position: relative;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 28px;
    padding: 24px 30px;
    box-shadow: var(--shadow-lg);
}

.search-inner-container {
    display: flex;
    align-items: center;
    background: var(--bg-2);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 6px;
    transition: var(--transition);
    position: relative;
}

.search-inner-container:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
}

.search-inner-container i.search-icon {
    position: absolute;
    left: 20px;
    color: var(--text-dim);
    font-size: 0.95rem;
}

#productSearch {
    flex: 1;
    background: transparent;
    border: none !important;
    outline: none !important;
    padding: 12px 15px 12px 48px;
    color: var(--text);
    font-size: 0.95rem;
    font-weight: 500;
    box-shadow: none !important;
}

.search-btn-compact {
    padding: 10px 24px;
    border-radius: 16px;
    font-size: 0.85rem;
    font-weight: 800;
    white-space: nowrap;
    cursor: pointer;
    transition: var(--transition);
    border: none;
}

/* ============ SECTION HEADERS ============ */
.section-header { margin-bottom: 40px; }
.section-label {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 1.5px; color: var(--primary-2);
    margin-bottom: 12px;
}

.section-label::before {
    content: '';
    width: 20px; height: 2px;
    background: var(--primary-gradient);
    border-radius: 2px;
}

.section-title {
    font-size: clamp(1.6rem, 4vw, 2.2rem);
    font-weight: 900;
    letter-spacing: -1px;
    line-height: 1.2;
}

/* ============ WHY CHOOSE US ============ */
.feature-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 32px;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
}

.feature-card:nth-child(1)::before { background: linear-gradient(90deg, #7c3aed, #a855f7); }
.feature-card:nth-child(2)::before { background: linear-gradient(90deg, #ec4899, #f43f5e); }
.feature-card:nth-child(3)::before { background: linear-gradient(90deg, #10b981, #06b6d4); }
.feature-card:nth-child(4)::before { background: linear-gradient(90deg, #f59e0b, #f43f5e); }

.feature-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-6px);
    box-shadow: var(--shadow-lg);
}

.feature-icon {
    width: 56px; height: 56px;
    border-radius: var(--radius);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 20px;
}

/* ============ HOW IT WORKS ============ */
.step-number {
    width: 50px; height: 50px;
    border-radius: 50%;
    background: var(--primary-gradient);
    display: flex; align-items: center; justify-content: center;
    color: white;
    font-weight: 900;
    font-size: 1.1rem;
    margin: 0 auto 20px;
    box-shadow: 0 8px 20px rgba(124,58,237,0.3);
}

/* ============ CTA SECTION ============ */
.cta-section {
    background: var(--primary-gradient);
    border-radius: var(--radius-xl);
    padding: 80px 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

/* ============ PRODUCT CARD ENHANCED ============ */
.sold-tag {
    font-size: 0.68rem;
    color: var(--text-dim);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 12px;
}
</style>

<main>
    <!-- ===================== HERO ===================== -->
    <section class="hero-section">
        <div class="hero-bg">
            <div class="hero-blob" style="width: 600px; height: 600px; top: -200px; right: -100px; background: rgba(124,58,237,0.12);"></div>
            <div class="hero-blob" style="width: 400px; height: 400px; bottom: -150px; left: -100px; background: rgba(236,72,153,0.1); animation-delay: -3s;"></div>
            <div class="hero-blob" style="width: 300px; height: 300px; top: 30%; left: 40%; background: rgba(6,182,212,0.06); animation-delay: -6s;"></div>
            <!-- Grid pattern -->
            <div style="position: absolute; inset: 0; background-image: linear-gradient(var(--border) 1px, transparent 1px), linear-gradient(90deg, var(--border) 1px, transparent 1px); background-size: 60px 60px; opacity: 0.3; pointer-events: none;"></div>
        </div>

        <div class="container" style="width: 100%;">
            <div class="hero-grid">
                <div class="hero-content">
                    <div class="hero-badge">
                        <div class="dot"></div>
                        Hệ Thống Hoạt Động 24/7
                    </div>
                    <h1 class="hero-title">
                        Nền Tảng<br>
                        <span class="text-gradient">Tài Khoản Số</span><br>
                        Hàng Đầu Việt Nam
                    </h1>
                    <p class="hero-desc">
                        Cung cấp tài khoản Premium bản quyền (Netflix, Spotify, ChatGPT Plus), dịch vụ Mạng Xã Hội (SMM) chất lượng cao. Kích hoạt tự động - Bảo hành uy tín.
                    </p>
                    <div class="hero-actions">
                        <a href="products.php" class="btn-premium" style="padding: 15px 36px; font-size: 1rem; border-radius: 14px;">
                            <i class="fas fa-shopping-bag"></i> Mua Ngay
                        </a>
                        <a href="register.php" class="btn-secondary" style="padding: 15px 30px; font-size: 1rem; border-radius: 14px;">
                            <i class="fas fa-user-plus"></i> Đăng Ký
                        </a>
                    </div>
                    <div style="display: flex; gap: 25px; flex-wrap: wrap;">
                        <div class="hero-feat"><i class="fas fa-check-circle"></i> Bảo hành 1:1</div>
                        <div class="hero-feat"><i class="fas fa-bolt" style="color: #f59e0b;"></i> Giao hàng 30 giây</div>
                        <div class="hero-feat"><i class="fas fa-lock" style="color: #60a5fa;"></i> Bảo mật tuyệt đối</div>
                    </div>
                    
                    <!-- Stats -->
                    <div class="hero-stats">
                        <div>
                            <div class="hero-stat-num" data-counter="10000" data-suffix="+">0</div>
                            <div class="hero-stat-label">Khách hàng tin dùng</div>
                        </div>
                        <div>
                            <div class="hero-stat-num" data-counter="50" data-suffix="+">0</div>
                            <div class="hero-stat-label">Sản phẩm đa dạng</div>
                        </div>
                        <div>
                            <div class="hero-stat-num" data-counter="99" data-suffix="%">0</div>
                            <div class="hero-stat-label">Tỷ lệ hài lòng</div>
                        </div>
                    </div>
                </div>

                <!-- Hero visual (right side) -->
                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                    <!-- Floating cards -->
                    <div style="position: relative; width: 100%; max-width: 440px;">
                        <!-- Main card -->
                        <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 24px; padding: 28px; box-shadow: var(--shadow-lg);">
                            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                                <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.3rem; flex-shrink: 0;">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <div style="font-weight: 800; font-size: 0.95rem; margin-bottom: 3px;">Đơn hàng tự động</div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">Xử lý trong 30 giây</div>
                                </div>
                                <div style="margin-left: auto; font-size: 0.75rem; background: rgba(16,185,129,0.12); color: #10b981; padding: 4px 10px; border-radius: 8px; font-weight: 700;">LIVE</div>
                            </div>
                            
                            <?php
                            // Fetch ONLY manual products (Netflix, etc.) which have api_service_id IS NULL
                            $heroProducts = $db->query("
                                SELECT p.name, p.image, MIN(pk.price) as min_price 
                                FROM products p 
                                JOIN packages pk ON p.id = pk.product_id 
                                JOIN categories c ON p.category_id = c.id
                                WHERE p.is_featured = 1 AND c.category_type = 'product' AND p.status = 'active'
                                GROUP BY p.id 
                                LIMIT 3
                            ");
                            while($hp = $heroProducts->fetch_assoc()):
                                $imgPath = 'assets/images/' . $hp['image'];
                                $hasImg = !empty($hp['image']) && file_exists(__DIR__ . '/' . $imgPath);
                            ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 12px; background: var(--bg-2); margin-bottom: 10px; border: 1px solid var(--border);">
                                <div style="width: 36px; height: 36px; border-radius: 9px; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8rem; font-weight: 800; flex-shrink: 0; overflow: hidden;">
                                    <?php if ($hasImg): ?>
                                        <img src="<?php echo $imgPath; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <?php echo strtoupper(substr($hp['name'], 0, 2)); ?>
                                    <?php endif; ?>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 700; font-size: 0.82rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($hp['name']); ?></div>
                                    <div style="font-size: 0.72rem; color: var(--text-dim);">Từ <?php echo format_currency($hp['min_price']); ?></div>
                                </div>
                                <i class="fas fa-chevron-right" style="font-size: 0.65rem; color: var(--text-dim);"></i>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Floating success badge -->
                        <div style="position: absolute; bottom: -20px; right: -20px; background: var(--bg-card); border: 1px solid var(--border); border-radius: 16px; padding: 14px 18px; display: flex; align-items: center; gap: 10px; box-shadow: var(--shadow-lg); z-index: 2;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(16,185,129,0.12); display: flex; align-items: center; justify-content: center; color: #10b981; font-size: 0.9rem;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; font-size: 0.82rem;">Đơn hàng thành công</div>
                                <div style="font-size: 0.7rem; color: var(--text-dim);">Vừa xong • Tự động giao</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===================== SEARCH + FILTER ===================== -->
    <section class="container" style="margin-top: 40px;">
        <div class="search-bar-wrap">
            <div class="search-inner-container">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="productSearch" placeholder="Tìm kiếm sản phẩm... (vd: Netflix, YouTube, ChatGPT)">
                <button class="btn-premium search-btn-compact">
                    <i class="fas fa-search"></i> Tìm Kiếm
                </button>
            </div>
            
            <!-- Category Chips -->
            <div style="display: flex; gap: 10px; margin-top: 20px; overflow-x: auto; padding: 4px 0; scrollbar-width: none; flex-wrap: nowrap;">
                <a href="#" class="chip active" data-id="">
                    <i class="fas fa-layer-group" style="font-size: 0.75rem;"></i> Tất Cả
                </a>
                <?php
                $cats = $db->query("SELECT * FROM categories WHERE category_type = 'product' ORDER BY display_order ASC, id ASC LIMIT 8");
                $catIcons = ['fa-play', 'fa-briefcase', 'fa-graduation-cap', 'fa-users', 'fa-robot', 'fab fa-tiktok', 'fas fa-music', 'fas fa-film'];
                $i = 0;
                while($cat = $cats->fetch_assoc()):
                    $icon = $catIcons[$i] ?? 'fas fa-cube';
                    $i++;
                ?>
                <a href="#" class="chip" data-id="<?php echo $cat['id']; ?>">
                    <i class="fas <?php echo $icon; ?>" style="font-size: 0.72rem;"></i>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- ===================== FEATURED PRODUCTS ===================== -->
    <section class="section-padding">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; flex-wrap: wrap; gap: 15px;">
                <div class="section-header" style="margin-bottom: 0;">
                    <div class="section-label"><i class="fas fa-fire"></i> Nổi Bật</div>
                    <h2 class="section-title">Sản Phẩm <span class="text-gradient">Được Chọn Nhiều</span></h2>
                </div>
                <a href="products.php" class="btn-ghost">
                    Xem tất cả <i class="fas fa-arrow-right" style="font-size: 0.8rem;"></i>
                </a>
            </div>

        <div class="product-grid" id="productGridMain">
            <?php
            $featured = $db->query("
                SELECT p.*, MIN(pk.price) as min_price, MAX(pk.price) as max_price
                FROM products p 
                JOIN packages pk ON p.id = pk.product_id 
                JOIN categories c ON p.category_id = c.id
                WHERE p.is_featured = 1 AND p.status = 'active' AND c.category_type = 'product'
                GROUP BY p.id 
                ORDER BY p.id DESC
                LIMIT 8
            ");
            
            $discounts = [85, 62, 82, 90, 93, 45, 75, 40]; 
            $di = 0;
            while($item = $featured->fetch_assoc()):
                $disc = $discounts[$di++ % count($discounts)];
                $original = $item['min_price'] * (100 / (100 - $disc));
            ?>
            <div class="product-card" data-cat="<?php echo $item['category_id']; ?>" onclick="window.location.href='product_detail.php?id=<?php echo $item['id']; ?>'">
                <!-- Discount Badge -->
                <div class="discount-badge">-<?php echo $disc; ?>%</div>
                
                <!-- Wishlist -->
                <button class="wishlist-btn" onclick="event.stopPropagation(); this.querySelector('i').classList.toggle('fas'); this.querySelector('i').classList.toggle('far');" aria-label="Wishlist">
                    <i class="far fa-heart"></i>
                </button>
                
                <!-- Image -->
                <div class="glass-img-container" style="height: 140px; display: flex; align-items: center; justify-content: center;">
                    <?php echo render_product_image($item['image'], $item['name']); ?>
                </div>

                <!-- Info -->
                <h3 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 6px; line-height: 1.4; color: var(--text);">
                    <?php echo htmlspecialchars($item['name']); ?>
                </h3>
                
                <div class="star-rating">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <span class="count">5.0</span>
                </div>

                <div class="sold-tag">
                    <i class="fas fa-shopping-cart" style="font-size: 0.6rem;"></i> 
                    Đã bán <?php echo rand(0, 100); ?>
                </div>

                <div class="price-wrap" style="margin-top: auto; margin-bottom: 14px;">
                    <span class="price-main"><?php echo format_currency($item['min_price']); ?></span>
                    <span class="price-original"><?php echo format_currency($original); ?></span>
                </div>

                <a href="product_detail.php?id=<?php echo $item['id']; ?>" class="btn-premium" style="width: 100%; border-radius: 12px; padding: 11px; font-size: 0.85rem;" onclick="event.stopPropagation();">
                    Xem Chi Tiết
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

    <!-- ===================== WHY CHOOSE US ===================== -->
    <section class="section-padding">
        <div class="container">
            <div style="text-align: center; margin-bottom: 50px;">
                <div class="section-label" style="justify-content: center;"><i class="fas fa-award"></i> Ưu Điểm</div>
                <h2 class="section-title">Tại Sao Chọn <span class="text-gradient"><?php echo $settings['site_name'] ?? 'QUYETDEV'; ?>?</span></h2>
                <p style="margin-top: 12px; color: var(--text-muted); font-size: 0.95rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                    Chúng tôi cam kết mang đến trải nghiệm tốt nhất với chất lượng dịch vụ hàng đầu
                </p>
            </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            <?php
            $features = [
                ['fa-shield-alt', 'rgba(124,58,237,0.12)', '#a855f7', 'Bảo Hành Uy Tín', 'Cam kết bảo hành 1:1 trong suốt thời gian sử dụng. Hỗ trợ xử lý lỗi nhanh chóng trong 5-15 phút.'],
                ['fa-bolt', 'rgba(244,63,94,0.12)', '#f43f5e', 'Giao Hàng Tự Động', 'Hệ thống thanh toán và giao hàng hoàn toàn tự động 24/7. Nhận tài khoản ngay sau khi thanh toán.'],
                ['fa-headset', 'rgba(16,185,129,0.12)', '#10b981', 'Hỗ Trợ 24/7', 'Đội ngũ kỹ thuật hỗ trợ xuyên suốt 24/7 qua Zalo và Telegram. Sẵn sàng giải đáp mọi thắc mắc.'],
                ['fa-lock', 'rgba(6,182,212,0.12)', '#06b6d4', 'Bảo Mật Tuyệt Đối', 'Hệ thống mã hóa SSL 256-bit bảo vệ mọi giao dịch. Thông tin cá nhân được bảo mật hoàn toàn.'],
            ];
            foreach ($features as $f):
            ?>
            <div class="feature-card spotlight">
                <div class="feature-icon" style="background: <?php echo $f[1]; ?>; color: <?php echo $f[2]; ?>;">
                    <i class="fas <?php echo $f[0]; ?>"></i>
                </div>
                <h4 style="font-size: 1.05rem; font-weight: 800; margin-bottom: 12px;"><?php echo $f[3]; ?></h4>
                <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.7;"><?php echo $f[4]; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

    <!-- ===================== HOW IT WORKS ===================== -->
    <section style="padding: 40px 0; background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
        <div class="container">
            <div style="text-align: center; margin-bottom: 50px;">
                <div class="section-label" style="justify-content: center;"><i class="fas fa-map-signs"></i> Quy Trình</div>
                <h2 class="section-title">Chỉ <span class="text-gradient">3 Bước</span> Đơn Giản</h2>
            </div>
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 30px; position: relative;">
                <!-- Connector line -->
                <div style="position: absolute; top: 25px; left: calc(16.6% + 10px); right: calc(16.6% + 10px); height: 2px; background: var(--primary-gradient); opacity: 0.3; pointer-events: none;"></div>
                
                <?php
                $steps = [
                    ['1', 'fas fa-user-plus', 'Đăng Ký Tài Khoản', 'Tạo tài khoản miễn phí và nạp tiền vào ví để bắt đầu mua sắm.'],
                    ['2', 'fas fa-shopping-cart', 'Chọn & Thanh Toán', 'Duyệt sản phẩm, chọn gói phù hợp và thanh toán qua ví số dư.'],
                    ['3', 'fas fa-check-circle', 'Nhận Hàng Ngay', 'Hệ thống giao tài khoản tự động trong vòng 30 giây sau thanh toán.'],
                ];
                foreach ($steps as $s):
                ?>
                <div style="text-align: center; padding: 10px;">
                    <div class="step-number"><?php echo $s[0]; ?></div>
                    <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(124,58,237,0.1); display: flex; align-items: center; justify-content: center; color: var(--primary-2); font-size: 1.3rem; margin: 0 auto 20px;">
                        <i class="<?php echo $s[1]; ?>"></i>
                    </div>
                    <h4 style="font-size: 1rem; font-weight: 800; margin-bottom: 12px;"><?php echo $s[2]; ?></h4>
                    <p style="color: var(--text-muted); font-size: 0.85rem; line-height: 1.7; max-width: 260px; margin: 0 auto;"><?php echo $s[3]; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ===================== MEGA CTA ===================== -->
    <section class="container section-padding">
        <div class="cta-section spotlight">
            <div style="position: relative; z-index: 1;">
                <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 18px; border-radius: 100px; background: rgba(255,255,255,0.15); color: white; font-size: 0.78rem; font-weight: 700; margin-bottom: 24px;">
                    <i class="fas fa-users"></i> Hơn 10,000+ khách hàng tin dùng
                </div>
                <h2 style="font-size: clamp(1.8rem, 5vw, 3rem); font-weight: 900; color: white; margin-bottom: 16px; letter-spacing: -1px;">
                    Sẵn Sàng Trải Nghiệm Dịch Vụ?
                </h2>
                <p style="color: rgba(255,255,255,0.8); font-size: 1rem; margin-bottom: 40px; max-width: 500px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                    Đăng ký miễn phí và nhận ngay ưu đãi nạp tiền lần đầu. Hàng nghìn sản phẩm premium đang chờ bạn khám phá.
                </p>
                <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                    <a href="register.php" style="padding: 15px 40px; border-radius: 14px; background: white; color: #7c3aed; font-weight: 800; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: var(--transition); font-size: 0.95rem;"
                       onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 15px 35px rgba(0,0,0,0.2)'" 
                       onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                        <i class="fas fa-rocket"></i> Đăng Ký Tài Khoản
                    </a>
                    <a href="<?php echo $settings['telegram_link'] ?? '#'; ?>" target="_blank"
                       style="padding: 15px 35px; border-radius: 14px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); color: white; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.3); transition: var(--transition); font-size: 0.95rem;"
                       onmouseover="this.style.background='rgba(255,255,255,0.25)'" 
                       onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                        <i class="fab fa-telegram-plane"></i> Kênh Telegram
                    </a>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Search
const searchInput = document.getElementById('productSearch');
const productCards = document.querySelectorAll('.product-card[data-cat]');

if (searchInput) {
    searchInput.addEventListener('input', e => {
        const term = e.target.value.toLowerCase().trim();
        productCards.forEach(card => {
            const title = card.querySelector('h3').innerText.toLowerCase();
            card.style.display = (!term || title.includes(term)) ? 'flex' : 'none';
        });
    });
}

// Category filter
document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        const catId = chip.getAttribute('data-id');
        productCards.forEach(card => {
            if (!catId || card.getAttribute('data-cat') == catId) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});

// Animate product cards on scroll
const obs = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
            }, i * 80);
            obs.unobserve(e.target);
        }
    });
}, { threshold: 0.1 });

productCards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
    obs.observe(card);
});
</script>

<?php include 'footer.php'; ?>
