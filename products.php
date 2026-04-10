<?php include 'header.php'; ?>

<style>
.products-hero {
    background: var(--bg-card);
    border-bottom: 1px solid var(--border);
    padding: 40px 0 30px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.products-hero::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(124,58,237,0.08), transparent 70%);
    pointer-events: none;
}

.filter-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding: 4px 0 8px;
    scrollbar-width: none;
    flex-wrap: nowrap;
}

.filter-scroll::-webkit-scrollbar { display: none; }

.filter-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 18px;
    border-radius: 100px; border: 1px solid var(--border);
    background: var(--bg-card); color: var(--text-muted);
    font-size: 0.8rem; font-weight: 700; cursor: pointer;
    white-space: nowrap; transition: var(--transition);
    font-family: inherit;
}

.filter-btn:hover { border-color: var(--primary); color: var(--primary-2); background: rgba(124,58,237,0.06); }
.filter-btn.active { background: var(--primary-gradient); color: white; border-color: transparent; }
.filter-btn i { font-size: 0.75rem; }

.p-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden; cursor: pointer;
    transition: var(--transition);
    display: flex; flex-direction: column;
    position: relative;
}

.p-card:hover {
    border-color: rgba(124,58,237,0.4);
    transform: translateY(-6px);
    box-shadow: 0 20px 50px rgba(124,58,237,0.15);
}

.p-card-img {
    width: 100%; aspect-ratio: 16/9;
    background: rgba(255,255,255,0.04);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; position: relative;
}

.p-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.4s ease;
}

.p-card:hover .p-card-img img { transform: scale(1.07); }

.p-card-body { padding: 16px; flex: 1; display: flex; flex-direction: column; }

.p-cat-label {
    font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--primary-2);
    margin-bottom: 6px;
    display: flex; align-items: center; gap: 5px;
}

.p-name {
    font-size: 0.92rem; font-weight: 800; line-height: 1.4;
    color: var(--text); margin-bottom: 8px;
}

.p-stars {
    display: flex; align-items: center; gap: 2px;
    margin-bottom: 8px; font-size: 0.68rem; color: #f59e0b;
}

.p-stars .count { color: var(--text-dim); margin-left: 5px; }

.p-sold { font-size: 0.68rem; color: var(--text-dim); font-weight: 600; margin-bottom: 10px; }

.p-price-row { display: flex; align-items: baseline; gap: 8px; margin-bottom: 14px; margin-top: auto; }
.p-price { font-size: 1.05rem; font-weight: 900; color: #f43f5e; }
.p-price-orig { font-size: 0.75rem; color: var(--text-dim); text-decoration: line-through; }

.disc-badge {
    position: absolute; top: 10px; left: 10px;
    background: linear-gradient(135deg, #ff4e50, #f9d423);
    color: white; font-size: 0.62rem; font-weight: 900;
    padding: 3px 8px; border-radius: 6px; z-index: 2;
}

.wish-btn {
    position: absolute; top: 8px; right: 8px; z-index: 2;
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(13,15,24,0.7); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--text-dim); font-size: 0.8rem; cursor: pointer;
    transition: var(--transition); backdrop-filter: blur(8px);
}

.wish-btn:hover { color: #ef4444; border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.1); }
.wish-btn.wished { color: #ef4444; }

.hot-label {
    position: absolute; top: 10px; right: 10px;
    background: linear-gradient(135deg, #ff4e50, #f9d423);
    color: white; font-size: 0.55rem; font-weight: 900;
    padding: 2px 7px; border-radius: 4px; z-index: 2;
    letter-spacing: 0.5px;
}

/* Placeholder card while filtering */
.no-results {
    grid-column: 1 / -1;
    text-align: center; padding: 80px 20px;
    color: var(--text-dim);
}
.no-results i { font-size: 2.5rem; display: block; margin-bottom: 15px; opacity: 0.4; }
.no-results p { font-weight: 600; font-size: 0.88rem; }
</style>

<!-- Hero -->
<div class="products-hero">
    <div class="container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:20px; margin-bottom:24px;">
            <div>
                <div style="font-size:0.7rem;font-weight:900;text-transform:uppercase;letter-spacing:1.2px;color:var(--primary-2);margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                    <span style="width:20px;height:2px;background:var(--primary-gradient);border-radius:2px;display:inline-block;"></span>
                    Cửa Hàng
                </div>
                <h1 style="font-size:1.8rem;font-weight:900;letter-spacing:-0.5px;">
                    Tất Cả <span class="text-gradient">Sản Phẩm</span>
                </h1>
                <p style="color:var(--text-muted);font-size:0.88rem;margin-top:6px;">
                    Tài khoản premium bản quyền, giá tốt nhất Việt Nam
                </p>
            </div>
            <!-- Search -->
            <div style="position:relative; flex:1; max-width:400px;">
                <div style="display:flex; align-items:center; background:var(--bg-2); border:1px solid var(--border); border-radius:20px; padding:6px; transition:var(--transition); position:relative;" id="searchBox">
                    <i class="fas fa-search" style="position:absolute; left:18px; color:var(--text-dim); font-size:0.85rem;"></i>
                    <input type="text" id="productSearch" placeholder="Tìm kiếm sản phẩm..."
                        style="width:100%; padding:10px 15px 10px 45px; background:transparent; border:none; color:var(--text); outline:none; font-size:0.88rem; font-weight:600;">
                    <button class="btn-premium" style="padding:8px 20px; border-radius:15px; font-size:0.75rem; font-weight:800; white-space:nowrap; border:none;">Tìm Kiếm</button>
                </div>
            </div>

            <script>
                const sb = document.getElementById('searchBox');
                const pInp = document.getElementById('productSearch');
                pInp.onfocus = () => {
                    sb.style.borderColor = 'var(--primary)';
                    sb.style.boxShadow = '0 0 0 4px rgba(124,58,237,0.1)';
                };
                pInp.onblur = () => {
                    sb.style.borderColor = 'var(--border)';
                    sb.style.boxShadow = 'none';
                };
            </script>
        </div>

        <!-- Category Filters -->
        <div class="filter-scroll">
            <button class="filter-btn active" data-cat="all" onclick="filterCat(this)">
                <i class="fas fa-layer-group"></i> Tất Cả
            </button>
            <?php
            $filterCats = $db->query("
                SELECT c.*, COUNT(p.id) as pcount 
                FROM categories c 
                JOIN products p ON p.category_id = c.id AND p.status = 'active'
                WHERE c.category_type = 'product'
                GROUP BY c.id 
                HAVING pcount > 0 
                ORDER BY c.display_order ASC, c.id ASC
            ");
            $catIconMap = [
                'Giải Trí'      => 'fas fa-play',
                'Làm Việc'      => 'fas fa-briefcase',
                'Học Tập'       => 'fas fa-graduation-cap',
                'Công Cụ AI'    => 'fas fa-robot',
                'Dịch vụ MXH'   => 'fab fa-facebook',
                'Dịch vụ TikTok'=> 'fab fa-tiktok',
                'Entertainment' => 'fas fa-play',
                'Working'       => 'fas fa-briefcase',
                'Learning'      => 'fas fa-graduation-cap',
            ];
            while ($fc = $filterCats->fetch_assoc()):
                $icon = $catIconMap[$fc['name']] ?? ($fc['icon'] ?? 'fas fa-cube');
            ?>
            <button class="filter-btn" data-cat="cat<?php echo $fc['id']; ?>" onclick="filterCat(this)">
                <i class="<?php echo $icon; ?>"></i> <?php echo htmlspecialchars($fc['name']); ?>
                <span style="font-size:0.6rem;opacity:0.6;">(<?php echo $fc['pcount']; ?>)</span>
            </button>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<!-- Product Grid -->
<main class="container" style="padding-bottom:80px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:10px;">
        <p style="font-size:0.82rem;color:var(--text-muted);font-weight:600;" id="productCount"></p>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:0.78rem;color:var(--text-dim);font-weight:600;">Sắp xếp:</span>
            <select id="sortSelect" onchange="sortProducts()" style="padding:8px 14px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:0.78rem;font-weight:700;outline:none;cursor:pointer;font-family:inherit;">
                <option value="default">Mặc định</option>
                <option value="price-asc">Giá tăng dần</option>
                <option value="price-desc">Giá giảm dần</option>
                <option value="featured">Nổi bật trước</option>
            </select>
        </div>
    </div>

    <div class="product-grid" id="productGrid">
        <?php
        $products = $db->query("
            SELECT p.*, MIN(pkg.price) as min_price, MAX(pkg.price) as max_price,
                   c.name as cat_name, c.id as cat_id
            FROM products p 
            LEFT JOIN packages pkg ON p.id = pkg.product_id 
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'active' AND c.category_type = 'product'
            GROUP BY p.id
            ORDER BY p.is_featured DESC, p.id DESC
        ");

        $discounts = [85, 62, 78, 90, 55, 45, 82, 70, 40, 65, 88, 73];
        $di = 0;
        $total_products = 0;

        if ($products && $products->num_rows > 0):
            while ($item = $products->fetch_assoc()):
                $total_products++;
                $disc = $discounts[$di % count($discounts)];
                $di++;
                $min_p = $item['min_price'] ?? 0;
                $original = $min_p > 0 ? $min_p * (100 / (100 - $disc)) : 0;
        ?>
        <div class="p-card product-item cat<?php echo $item['cat_id']; ?>"
             data-name="<?php echo strtolower(htmlspecialchars($item['name'])); ?>"
             data-price="<?php echo $item['min_price']; ?>"
             data-featured="<?php echo $item['is_featured']; ?>"
             onclick="window.location.href='product_detail.php?id=<?php echo $item['id']; ?>'">

            <!-- Discount badge -->
            <div class="disc-badge">-<?php echo $disc; ?>%</div>

            <?php if ($item['is_featured']): ?>
            <div class="hot-label">HOT</div>
            <?php endif; ?>

            <!-- Wishlist -->
            <button class="wish-btn" onclick="event.stopPropagation();this.classList.toggle('wished');this.querySelector('i').className=this.classList.contains('wished')?'fas fa-heart':'far fa-heart';" aria-label="Yêu thích">
                <i class="far fa-heart"></i>
            </button>

            <!-- Image -->
            <div class="p-card-img">
                <img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>"
                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                     loading="lazy"
                     onerror="this.parentElement.innerHTML='<div style=\'color:var(--primary-2);font-size:2.5rem;\'><i class=\'fas fa-cube\'></i></div>'">
            </div>

            <!-- Body -->
            <div class="p-card-body">
                <div class="p-cat-label">
                    <i class="fas fa-tag" style="font-size:0.6rem;"></i>
                    <?php echo htmlspecialchars($item['cat_name'] ?? 'Sản Phẩm'); ?>
                </div>
                <div class="p-name"><?php echo htmlspecialchars($item['name']); ?></div>
                <div class="p-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <i class="fas fa-star"></i><i class="fas fa-star"></i>
                    <span class="count">5.0</span>
                </div>
                <div class="p-sold">
                    <i class="fas fa-shopping-cart" style="font-size:0.6rem;margin-right:3px;"></i>
                    Đã bán <?php echo rand(8, 500); ?>
                </div>
                <div class="p-price-row">
                    <?php if ($min_p > 0): ?>
                        <span class="p-price"><?php echo format_currency($min_p); ?></span>
                        <span class="p-price-orig"><?php echo format_currency($original); ?></span>
                    <?php else: ?>
                        <span class="p-price" style="font-size: 0.85rem; color: var(--primary-2);">Liên hệ</span>
                    <?php endif; ?>
                </div>
                <a href="product_detail.php?id=<?php echo $item['id']; ?>" 
                   class="btn-premium" style="width:100%;border-radius:12px;padding:11px;font-size:0.82rem;" 
                   onclick="event.stopPropagation();">
                    Xem Chi Tiết
                </a>
            </div>
        </div>
        <?php
            endwhile;
        else:
        ?>
        <div class="no-results">
            <i class="fas fa-box-open"></i>
            <p>Chưa có sản phẩm. <a href="setup.php" style="color:var(--primary-2);">Chạy setup</a></p>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
const allCards = document.querySelectorAll('.product-item');
const countEl  = document.getElementById('productCount');

function updateCount() {
    const vis = [...allCards].filter(c => c.style.display !== 'none').length;
    countEl.textContent = `Hiển thị ${vis} sản phẩm`;
}

updateCount();

// Filter by category
function filterCat(btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cat = btn.dataset.cat;
    allCards.forEach(c => {
        const show = cat === 'all' || c.classList.contains(cat);
        c.style.display = show ? 'flex' : 'none';
    });
    updateCount();
}

// Search
document.getElementById('productSearch').addEventListener('input', function() {
    const term = this.value.toLowerCase().trim();
    const activeCat = document.querySelector('.filter-btn.active')?.dataset.cat || 'all';
    allCards.forEach(c => {
        const nameMatch = c.dataset.name.includes(term);
        const catMatch  = activeCat === 'all' || c.classList.contains(activeCat);
        c.style.display = (nameMatch && catMatch) ? 'flex' : 'none';
    });
    updateCount();
});

// Sort
function sortProducts() {
    const val = document.getElementById('sortSelect').value;
    const grid = document.getElementById('productGrid');
    const cards = [...allCards];
    
    if (val === 'price-asc')  cards.sort((a,b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
    if (val === 'price-desc') cards.sort((a,b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
    if (val === 'featured')   cards.sort((a,b) => parseInt(b.dataset.featured) - parseInt(a.dataset.featured));
    
    cards.forEach(c => grid.appendChild(c));
}

// Scroll reveal
const revealObs = new IntersectionObserver(entries => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            setTimeout(() => {
                e.target.style.opacity = '1';
                e.target.style.transform = 'translateY(0)';
            }, i * 60);
            revealObs.unobserve(e.target);
        }
    });
}, { threshold: 0.05 });

allCards.forEach(c => {
    c.style.opacity = '0';
    c.style.transform = 'translateY(20px)';
    c.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
    revealObs.observe(c);
});
</script>

<?php include 'footer.php'; ?>
