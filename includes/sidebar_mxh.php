<?php
// Global Platform Data for Sidebar
$all_prods_sb = $db->query("
    SELECT p.id, p.name, c.name as cat_name
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.category_id ASC, p.id ASC
");

$platforms = [];
$seen = [];
while ($p = $all_prods_sb->fetch_assoc()) {
    $plt = detectPlatform($p['name'], $p['cat_name']);
    if (!isset($platforms[$plt])) {
        $platforms[$plt] = [];
        $seen[$plt] = [];
    }
    
    // Only add if name is not already present in this platform
    if (!in_array($p['name'], $seen[$plt])) {
        $platforms[$plt][] = $p;
        $seen[$plt][] = $p['name'];
    }
}

$order_sb = ['Facebook', 'Facebook VIP', 'TikTok', 'Instagram', 'Youtube', 'Shopee', 'Telegram', 'Twitter', 'Thread', 'Bigo Live', 'Google'];
$sorted_platforms = [];
foreach ($order_sb as $plt) {
    if (isset($platforms[$plt])) $sorted_platforms[$plt] = $platforms[$plt];
}
foreach ($platforms as $k => $v) {
    if (!isset($sorted_platforms[$k])) $sorted_platforms[$k] = $v;
}

$plt_cfg_sb = [
    'Facebook'     => ['fab fa-facebook',       '#1877f2', 'rgba(24,119,242,0.12)',  'rgba(24,119,242,0.25)',  'HOT'],
    'Facebook VIP' => ['fab fa-facebook',        '#e8a800', 'rgba(232,168,0,0.12)',   'rgba(232,168,0,0.25)',   'VIP'],
    'TikTok'       => ['fab fa-tiktok',          '#fe2c55', 'rgba(254,44,85,0.08)',  'rgba(254,44,85,0.2)',   'HOT'],
    'Instagram'    => ['fab fa-instagram',       '#e4405f', 'rgba(228,64,95,0.12)',   'rgba(228,64,95,0.25)',   'HOT'],
    'Youtube'      => ['fab fa-youtube',         '#ff0000', 'rgba(255,0,0,0.1)',      'rgba(255,0,0,0.25)',     ''],
    'Shopee'       => ['fas fa-shopping-bag',    '#f57224', 'rgba(245,114,36,0.12)',  'rgba(245,114,36,0.25)',  ''],
    'Telegram'     => ['fab fa-telegram',        '#26a5e4', 'rgba(38,165,228,0.12)', 'rgba(38,165,228,0.25)',  ''],
    'Twitter'      => ['fab fa-twitter',         '#1da1f2', 'rgba(29,161,242,0.12)', 'rgba(29,161,242,0.25)',  ''],
    'Thread'       => ['fas fa-at',              '#000000', 'rgba(255,255,255,0.08)', 'rgba(255,255,255,0.15)', ''],
    'Bigo Live'    => ['fas fa-video',           '#ff6900', 'rgba(255,105,0,0.12)',  'rgba(255,105,0,0.25)',   ''],
    'Google'       => ['fab fa-google',          '#4285F4', 'rgba(66,133,244,0.12)', 'rgba(66,133,244,0.25)',  ''],
    '_default'     => ['fas fa-share-alt',       '#a855f7', 'rgba(168,85,247,0.12)', 'rgba(168,85,247,0.25)',  ''],
];
?>
<style>
/* Drawer for Mobile */
.mxh-sidebar { 
    position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; 
    z-index: 99999 !important; 
    background: rgba(0,0,0,0.7) !important;
    -webkit-backdrop-filter: blur(10px);
    backdrop-filter: blur(10px);
    display: none; align-items: flex-start; justify-content: flex-start;
    opacity: 0; transition: all 0.3s ease;
    visibility: hidden;
    pointer-events: none;
}
.mxh-sidebar.active { display: flex !important; opacity: 1 !important; visibility: visible !important; pointer-events: auto !important; }

.sidebar-inner {
    width: 65%; max-width: 320px; height: 100vh; overflow-y: auto;
    background: var(--bg-card); border-radius: 0 30px 30px 0;
    padding: 30px 16px;
    transform: translateX(-100%); transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 15px 0 35px rgba(0,0,0,0.3);
    border-right: 1px solid var(--border);
}
.mxh-sidebar.active .sidebar-inner { transform: translateX(0); }

/* Common Sidebar Styles */
.s-card-header {
    justify-content: space-between; background: transparent; border: none; 
    color: var(--text); letter-spacing: 1px; font-size: 0.85rem; 
    font-weight: 800; padding: 0 0 20px 0; display: flex; align-items: center;
}

.plt-item { margin-bottom: 5px; }
.plt-row {
    width: 100%; display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; border: none; background: none;
    border-radius: 12px; transition: 0.2s; cursor: pointer;
    color: var(--text); font-weight: 700;
}
.plt-row:hover { background: rgba(124,58,237,0.08); }

.plt-icon {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
}
.plt-name { font-size: 0.88rem; font-weight: 700; color: var(--text); }
.plt-chevron { margin-left: auto; font-size: 0.7rem; transition: 0.3s; color: var(--text-dim); }
.plt-item.open .plt-chevron { transform: rotate(90deg); }

.plt-body { overflow: hidden; max-height: 0; transition: max-height 0.35s ease-out; }
.plt-item.open .plt-body { max-height: 1000px; }
.plt-body-inner { border-left: 2px solid var(--border-strong); margin-left: 31px; padding: 5px 0 10px 10px; }

.srv-item {
    padding: 8px 10px; font-size: 0.8rem; font-weight: 700; color: var(--text-muted);
    cursor: pointer; border-radius: 8px; transition: 0.2s; text-transform: uppercase;
}
.srv-item:hover, .srv-item.active { color: var(--primary-2); background: rgba(124,58,237,0.06); }

/* Badges */
.plt-badge-hot { background: #fe2c55; color: white; font-size: 0.55rem; padding: 2px 6px; border-radius: 4px; font-weight: 900; margin-left: 6px; }
.plt-badge-vip { background: #f59e0b; color: white; font-size: 0.55rem; padding: 2px 6px; border-radius: 4px; font-weight: 900; margin-left: 6px; }
</style>
<?php ?>

<?php 
function renderMXHSidebar($sorted_platforms, $plt_cfg_sb, $isDesktop = false) {
?>
    <div class="<?php echo $isDesktop ? 'desktop-sidebar' : 's-card'; ?>" style="<?php echo $isDesktop ? '' : 'border:none;background:transparent;'; ?>">
        <div class="s-card-header" style="<?php echo $isDesktop ? 'padding: 0 0 20px 0;' : ''; ?>">
            <span><i class="fas fa-layer-group" style="color:var(--primary-2);"></i> DANH MỤC DỊCH VỤ</span>
            <?php if (!$isDesktop): ?>
            <button class="btn-icon" style="font-size:1.4rem;color:var(--text);" onclick="document.getElementById('mxhSidebar').classList.remove('active')">
                <i class="fas fa-times"></i>
            </button>
            <?php endif; ?>
        </div>

        <?php foreach ($sorted_platforms as $plt_name => $prods): 
            $cfg = $plt_cfg_sb[$plt_name] ?? $plt_cfg_sb['_default'];
            [$plt_icon, $plt_color, $plt_bg, $plt_border, $plt_badge] = $cfg;
        ?>
        <div class="plt-item">
            <button class="plt-row" onclick="togglePlt(this)">
                <div class="plt-icon" style="background:<?php echo $plt_bg; ?>; border: 1px solid <?php echo $plt_border; ?>;">
                    <i class="<?php echo $plt_icon; ?>" style="color:<?php echo $plt_color; ?>;"></i>
                </div>
                <span class="plt-name"><?php echo htmlspecialchars($plt_name); ?></span>
                <?php if ($plt_badge): ?>
                    <span class="plt-badge-<?php echo strtolower($plt_badge); ?>"><?php echo $plt_badge; ?></span>
                <?php endif; ?>
                <i class="fas fa-chevron-right plt-chevron"></i>
            </button>
            <div class="plt-body">
                <div class="plt-body-inner">
                    <?php foreach ($prods as $prod): ?>
                    <div class="srv-item" onclick="handleSrvClick(<?php echo $prod['id']; ?>, '<?php echo addslashes($prod['name']); ?>', this)">
                        <?php echo htmlspecialchars($prod['name']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php
}
?>

<!-- Sidebar HTML removed and moved to social.php -->

<script>
function openSidebar() {
    const sb = document.getElementById('mxhSidebar');
    sb.classList.add('active');
    const inner = sb.querySelector('.sidebar-inner');
    if(inner) inner.scrollTop = 0;
}

function handleSrvClick(id, name, el) {
    if (window.location.pathname.includes('social.php')) {
        loadPkgs(id, name, el);
    } else {
        window.location.href = 'social.php?id=' + id;
    }
    // Auto-close sidebar on mobile
    if (window.innerWidth <= 992) {
        const sb = document.getElementById('mxhSidebar');
        if (sb) sb.classList.remove('active');
    }
}
</script>
