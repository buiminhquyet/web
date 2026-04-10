<?php 
include 'header.php'; 

// =====================================================
// Build the sidebar platform structure
// =====================================================
include 'includes/sidebar_mxh.php'; 

// Get all products that belong to "social" categories
$all_products = $db->query("
    SELECT p.*, c.name as cat_name, c.icon as cat_icon
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
      AND c.category_type = 'mxh'
    ORDER BY p.category_id ASC, p.id ASC
");

// Grouping logic now handled globally in includes/sidebar_mxh.php
// Loading just for current page state if needed
$first_product = null;
if (!empty($sorted_platforms)) {
    $first_platform = array_key_first($sorted_platforms);
    $first_product = !empty($sorted_platforms[$first_platform]) ? $sorted_platforms[$first_platform][0] : null;
}

// Fetch user order history
$user_orders = [];
$current_uid = $_SESSION['user_id'] ?? null;

if ($current_uid) {
    // SYNC STATUS WITH PROVIDER API
    $active_orders = $db->fetchAll("SELECT id, api_order_id, status FROM orders WHERE user_id = '$current_uid' AND status IN ('pending', 'processing') AND api_order_id IS NOT NULL AND api_order_id != '' LIMIT 10");
    if (!empty($active_orders)) {
        require_once 'includes/lib/smm_api.php';
        $api_key = get_setting('smm_api_key');
        $api_url = get_setting('smm_api_url');
        if ($api_key && $api_url) {
            $api = new SMMApi($api_key, $api_url);
            foreach ($active_orders as $o) {
                $st = $api->getStatus($o['api_order_id']);
                if (isset($st['status'])) {
                    $new_status = strtolower($o['status']);
                    $raw_st = strtolower($st['status']);
                    if ($raw_st == 'completed' || $raw_st == 'success' || $raw_st == 'done') $new_status = 'completed';
                    elseif (in_array($raw_st, ['canceled', 'cancelled', 'partial', 'error', 'failed'])) $new_status = 'failed';
                    elseif (in_array($raw_st, ['in progress', 'processing', 'running'])) $new_status = 'processing';
                    elseif ($raw_st == 'pending' || $raw_st == 'waiting') $new_status = 'pending';
                    
                    if ($new_status != strtolower($o['status'])) {
                        $db->query("UPDATE orders SET status = '$new_status' WHERE id = " . $o['id']);
                    }
                }
            }
        }
    }

    $user_orders = $db->fetchAll("
        SELECT o.*, p.name as pkg_name, pr.name as prod_name, pr.image as prod_image
        FROM orders o
        JOIN packages p ON o.package_id = p.id
        JOIN products pr ON p.product_id = pr.id
        JOIN categories c ON pr.category_id = c.id
        WHERE o.user_id = '$current_uid' AND c.category_type = 'mxh'
        ORDER BY o.id DESC
        LIMIT 50
    ");
}
?>
<style>
/* ==============================
   MXH PAGE
============================== */

/* Layout */
.mxh-wrap { padding: 15px 0 30px; }

@media (max-width: 992px) {
    .mxh-wrap { padding: 5px 0 20px; }
}

.mxh-layout {
    display: flex;
    gap: 30px;
    align-items: start;
    margin-top: 10px;
}

.desktop-sidebar {
    width: 300px;
    flex-shrink: 0;
    position: sticky;
    top: 90px;
    display: block;
}

@media (max-width: 992px) {
    .mxh-layout { display: block !important; }
    .desktop-sidebar { display: none !important; }
}

/* History Table Styles */
.history-table-container { overflow-x: auto; margin-top: 10px; }
.history-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
.history-table th { 
    padding: 12px 15px; text-align: left; font-size: 0.72rem; 
    text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);
}
.history-row { background: rgba(255,255,255,0.03); border-radius: 12px; transition: all 0.3s; }
.history-row:hover { background: rgba(255,255,255,0.06); transform: translateY(-2px); }
.history-row td { padding: 15px; font-size: 0.82rem; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
.history-row td:first-child { border-left: 1px solid var(--border); border-radius: 12px 0 0 12px; }
.history-row td:last-child { border-right: 1px solid var(--border); border-radius: 0 12px 12px 0; }

.status-badge {
    padding: 5px 12px; border-radius: 8px; font-size: 0.7rem; font-weight: 800;
    text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px;
}
.status-pending { background: rgba(245,158,11,0.15); color: #f59e0b; }
.status-processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
.status-completed { background: rgba(16,185,129,0.15); color: #10b981; }
.status-failed { background: rgba(244,63,94,0.15); color: #f43f5e; }

/* Mobile History Cards */
.history-card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: 18px; padding: 16px; margin-bottom: 15px; display: none;
}
@media (max-width: 768px) {
    .history-table-container { display: none; }
    .history-card { display: block; }
}

.h-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.h-card-label { font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; margin-bottom: 2px; }
.h-card-val { font-size: 0.85rem; font-weight: 700; color: var(--text); }
.h-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px; border-top: 1px dashed var(--border); padding-top: 12px; }

.copy-badge { cursor: pointer; background: rgba(124,58,237,0.1); color: var(--primary-2); padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; margin-left: 5px; }

/* Sidebar card inside desktop */
.desktop-sidebar .s-card-header {
    padding: 0 0 15px 0;
    border-bottom: 1px solid var(--border);
    margin-bottom: 15px;
}
.s-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.s-card-header {
    padding: 13px 16px;
    font-size: 0.68rem;
    font-weight: 900;
    color: var(--text-dim);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(0,0,0,0.1);
}

/* Platform row (top-level accordion) */
.plt-row {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px 16px;
    cursor: pointer;
    transition: var(--transition);
    border-bottom: 1px solid var(--border);
    border-left: 3px solid transparent;
    background: none;
    border-right: none;
    border-top: none;
    width: 100%;
    text-align: left;
    color: var(--text-muted);
}

.plt-row:hover {
    background: var(--bg-card-hover);
    color: var(--text);
    border-left-color: rgba(168,85,247,0.4);
}

.plt-row.active {
    background: rgba(124,58,237,0.07);
    color: var(--text);
    border-left-color: var(--primary-2);
}

.plt-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.plt-name {
    flex: 1;
    font-weight: 700;
    font-size: 0.82rem;
}

.plt-badge-hot {
    font-size: 0.55rem;
    font-weight: 900;
    padding: 2px 6px;
    border-radius: 4px;
    background: linear-gradient(135deg, #ff6b35, #f7c331);
    color: white;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.plt-badge-vip {
    font-size: 0.55rem;
    font-weight: 900;
    padding: 2px 6px;
    border-radius: 4px;
    background: linear-gradient(135deg, #f59e0b, #f43f5e);
    color: white;
    letter-spacing: 0.5px;
    flex-shrink: 0;
}

.plt-chevron {
    font-size: 0.6rem;
    color: var(--text-dim);
    transition: transform 0.3s;
    flex-shrink: 0;
}

/* Accordion content */
.plt-body {
    overflow: hidden;
    max-height: 0;
    transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.plt-item.open .plt-body { max-height: 1500px; }
.plt-item.open .plt-chevron { transform: rotate(90deg); }
.plt-item.open .plt-row {
    background: transparent;
    color: var(--primary-2);
}

/* Sub items */
.plt-body-inner {
    border-left: 2px solid #3b82f6; /* Blue line style */
    margin-left: 30px; /* Align under the icon */
    padding-left: 5px;
    margin-top: 5px;
    margin-bottom: 10px;
}

.srv-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 10px;
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    border-radius: var(--radius-sm);
    text-transform: uppercase; /* Match the screenshot (THEO DÕI NHANH) */
}

.srv-item:hover {
    color: var(--primary-2);
    background: rgba(124,58,237,0.06);
}

.srv-item.active {
    color: var(--primary-2);
    font-weight: 800;
}

/* Rules card */
.rules-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    margin-top: 12px;
    overflow: hidden;
}

.rule-item {
    display: flex; align-items: flex-start; gap: 9px;
    padding: 9px 14px;
    font-size: 0.74rem; font-weight: 600;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
}
.rule-item:last-child { border-bottom: none; }
.rule-item i { color: #f59e0b; font-size: 0.65rem; margin-top: 3px; flex-shrink: 0; }

/* ===== MAIN PANEL ===== */
.main-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
}

/* Tabs */
.mxh-tabs {
    display: flex; gap: 6px;
    background: var(--bg-2);
    padding: 5px; margin-bottom: 22px;
    border-radius: var(--radius); border: 1px solid var(--border);
}

.mxh-tab {
    flex: 1; padding: 10px;
    border-radius: calc(var(--radius) - 2px);
    cursor: pointer; font-weight: 700;
    font-size: 0.75rem; text-transform: uppercase;
    letter-spacing: 0.3px;
    color: var(--text-muted);
    transition: var(--transition);
    text-align: center;
    display: flex; align-items: center; justify-content: center; gap: 7px;
    border: none; background: none;
}

.mxh-tab:hover { color: var(--text); background: var(--bg-card); }
.mxh-tab.active { background: var(--primary-gradient); color: white; box-shadow: 0 4px 15px rgba(124,58,237,0.35); }

/* Service header */
.svc-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 18px; flex-wrap: wrap; gap: 8px;
}

.svc-live-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #10b981;
    animation: blink 1.8s ease-in-out infinite; flex-shrink: 0;
}

@keyframes blink { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(1.4)} }

/* Package cards */
.pkg-card {
    background: var(--bg-2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 13px 16px;
    cursor: pointer;
    transition: var(--transition);
    display: flex; align-items: center; gap: 13px;
    margin-bottom: 9px; position: relative; overflow: hidden;
}

.pkg-card::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
    background: var(--primary-gradient); opacity: 0; transition: opacity 0.2s;
}

.pkg-card:hover { border-color: rgba(168,85,247,0.35); transform: translateX(2px); }
.pkg-card:hover::before { opacity: 1; }

.pkg-card.selected {
    border-color: var(--primary-2);
    background: rgba(124,58,237,0.07);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
}
.pkg-card.selected::before { opacity: 1; }

.radio-dot {
    width: 18px; height: 18px; border: 2px solid var(--border-strong);
    border-radius: 50%; flex-shrink: 0; position: relative; transition: var(--transition);
}

.pkg-card.selected .radio-dot { border-color: var(--primary-2); }
.pkg-card.selected .radio-dot::after {
    content: ''; position: absolute; inset: 3px;
    background: var(--primary-gradient); border-radius: 50%;
}

.pkg-api-badge {
    display: inline-flex;
    background: rgba(124,58,237,0.15); color: var(--primary-2);
    padding: 1px 7px; border-radius: 5px; font-size: 0.62rem; font-weight: 800;
    border: 1px solid rgba(124,58,237,0.2); flex-shrink: 0;
}

/* Form */
.order-form-label {
    display: block; font-weight: 800; font-size: 0.68rem;
    margin-bottom: 7px; text-transform: uppercase; letter-spacing: 0.5px;
    color: var(--text-muted);
}

.order-input {
    width: 100%; padding: 12px 16px;
    border-radius: var(--radius); border: 1px solid var(--border);
    background: var(--bg-2); color: var(--text);
    outline: none; font-size: 0.88rem; font-weight: 600;
    font-family: inherit; transition: var(--transition);
}

.order-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
}

.order-input::placeholder { color: var(--text-dim); font-weight: 500; }

/* Summary box */
.sum-box {
    background: rgba(124,58,237,0.06);
    border: 1px solid rgba(124,58,237,0.15);
    border-radius: var(--radius); padding: 16px 18px; margin: 18px 0;
}

.sum-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: 0.82rem; font-weight: 700; color: var(--text-muted); margin-bottom: 10px;
}

.sum-total {
    display: flex; justify-content: space-between; align-items: center;
    border-top: 1px dashed var(--border-strong); padding-top: 12px;
}

/* Skeleton */
.pkg-skeleton {
    height: 64px; border-radius: var(--radius); margin-bottom: 9px;
    background: linear-gradient(90deg, var(--bg-card) 25%, var(--bg-card-hover) 50%, var(--bg-card) 75%);
    background-size: 200% 100%; animation: shimmer 1.4s infinite;
}

@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

.empty-state { text-align: center; padding: 50px 20px; color: var(--text-dim); }
.empty-state i { font-size: 2.2rem; margin-bottom: 12px; display: block; opacity: 0.5; }
.empty-state p { font-size: 0.82rem; font-weight: 600; }

    /* Reaction Picker - Premium Style */
    .reaction-picker {
        display: none;
        margin-bottom: 24px;
        background: #f8fafc;
        padding: 20px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
    }
    .reaction-label {
        font-size: 0.9rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 15px;
        display: block;
    }
    .reaction-list {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    .reaction-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 12px 10px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        background: #fff;
        width: calc(25% - 8px);
        min-width: 65px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .reaction-item:hover { border-color: var(--primary-2); transform: translateY(-2px); }
    .reaction-item.active {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59,130,246,0.15);
    }
    .reaction-item img, .reaction-item .emoji { font-size: 22px; line-height: 1; }
    .reaction-item span { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-top: 4px; }
    .reaction-item.active {
        background: rgba(59, 130, 246, 0.1);
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59,130,246,0.15);
    }
    .reaction-item.active span { color: #3b82f6; }

    /* Comment Box Style */
    .order-form-label { font-size: 0.88rem; font-weight: 800; color: #1e293b; margin-bottom: 10px; display: block; }
    .order-input {
        width: 100%; padding: 14px 18px; border-radius: 14px;
        border: 1px solid #e2e8f0; background: #fff;
        font-size: 0.92rem; font-weight: 600; color: #334155;
        transition: 0.2s;
    }
    .order-input:focus { border-color: var(--primary-2); outline: none; box-shadow: 0 0 0 4px rgba(124,58,237,0.1); }
</style>

<div class="mxh-wrap">
<div class="container">
<div class="mxh-layout">

    <!-- ============ SIDEBAR PC ============ -->
    <div class="desktop-sidebar">
        <?php renderMXHSidebar($sorted_platforms, $plt_cfg_sb, true); ?>
    </div>

    <!-- ============ MAIN ============ -->
    <div style="flex: 1; min-width: 0;">
        
        <!-- Tabs -->
        <div class="mxh-tabs">
            <button class="mxh-tab active" onclick="switchTab('order',this)">
                <i class="fas fa-shopping-cart"></i> Tạo Đơn Hàng
            </button>
            <button class="mxh-tab" onclick="switchTab('history',this)">
                <i class="fas fa-history"></i> Lịch Sử Đơn
            </button>
        </div>

        <!-- ORDER TAB -->
        <div id="tab-order">
            <div class="main-panel">
                <div class="svc-header">
                    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                        <button class="nav-hamburger" onclick="openSidebar()">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div class="svc-live-dot"></div>
                        <span style="font-size:0.65rem;font-weight:900;text-transform:uppercase;letter-spacing:.5px;color:var(--text-dim);">ĐANG CHỌN</span>
                        <span id="curSvcName" style="font-weight:800;font-size:0.88rem;color:var(--primary-2);">
                            <?php echo $first_product ? htmlspecialchars($first_product['name']) : 'Chọn dịch vụ bên trái'; ?>
                        </span>
                    </div>
                    <span id="pkgCountBadge" style="font-size:0.7rem;background:var(--bg-2);border:1px solid var(--border);padding:3px 12px;border-radius:100px;font-weight:700;color:var(--text-muted);display:none;"></span>
                </div>

                <!-- Packages -->
                <div id="pkgList">
                    <div class="pkg-skeleton"></div>
                    <div class="pkg-skeleton" style="opacity:.6;"></div>
                    <div class="pkg-skeleton" style="opacity:.3;"></div>
                </div>

                <!-- Divider -->
                <div style="height:1px;background:var(--border);margin:20px 0;"></div>

                <!-- link input -->
                <div style="margin-bottom:14px;">
                    <label class="order-form-label">
                        <i class="fas fa-link" style="color:var(--primary-2);margin-right:4px;"></i>
                        Nhập Link / ID cần chạy
                    </label>
                    <input type="text" id="orderLink" class="order-input" placeholder="VD: https://www.facebook.com/profile.php?id=...">
                    <p style="font-size:0.68rem;color:#f59e0b;margin-top:6px;font-weight:700;display:flex;align-items:center;gap:5px;">
                        <i class="fas fa-exclamation-triangle"></i> Vui lòng nhập đúng định dạng link của dịch vụ
                    </p>
                </div>

                <!-- Comment Box -->
                <div id="commentBox" style="display:none;margin-bottom:20px;">
                    <label class="order-form-label">
                        <i class="fas fa-comments" style="color:var(--primary-2);margin-right:4px;"></i>
                        Nội dung bình luận
                    </label>
                    <textarea id="orderComment" class="order-input" rows="4" placeholder="Nhập mỗi nội dung 1 dòng..." style="resize:none;"></textarea>
                    <p style="font-size:0.65rem;color:var(--text-dim);margin-top:6px;font-weight:600;">Hệ thống sẽ tự động đếm số dòng để tính số lượng.</p>
                </div>

                <!-- Reaction Picker -->
                <div id="reactionPicker" class="reaction-picker">
                    <label class="reaction-label">Loại cảm xúc:</label>
                    <div class="reaction-list">
                        <div class="reaction-item active" onclick="setReaction('like', this)">
                            <div class="emoji">👍</div>
                            <span>Like</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('care', this)">
                            <div class="emoji">🥰</div>
                            <span>Care</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('love', this)">
                            <div class="emoji">❤️</div>
                            <span>Love</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('haha', this)">
                            <div class="emoji">😂</div>
                            <span>Haha</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('wow', this)">
                            <div class="emoji">😮</div>
                            <span>Wow</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('sad', this)">
                            <div class="emoji">😢</div>
                            <span>Sad</span>
                        </div>
                        <div class="reaction-item" onclick="setReaction('angry', this)">
                            <div class="emoji">😡</div>
                            <span>Angry</span>
                        </div>
                    </div>

                    <input type="hidden" id="reactionType" value="like">
                </div>


                <!-- quantity input -->
                <div style="margin-bottom:14px;">
                    <label class="order-form-label" id="qtyLabelContainer">
                        <i class="fas fa-sort-numeric-up" style="color:var(--primary-2);margin-right:4px;"></i>
                        Số lượng: <span id="qtyRange" style="color:#64748b;font-weight:600;font-size:0.8rem;">(Tối thiểu 10)</span>
                    </label>
                    <input type="number" id="orderQty" class="order-input" value="1000" oninput="calcTotal()">
                </div>


                <!-- Summary -->
                <div class="sum-box">
                    <div class="sum-row">
                        <span>Dịch vụ:</span>
                        <span id="sumName" style="color:var(--text);font-size:0.78rem;max-width:55%;text-align:right;">--</span>
                    </div>
                    <div class="sum-row">
                        <span>Đơn giá:</span>
                        <span id="sumPrice" style="color:var(--primary-2);">0đ / 1K</span>
                    </div>
                    <div class="sum-total">
                        <span style="font-weight:800;font-size:0.9rem;">TỔNG THANH TOÁN</span>
                        <span id="sumTotal" style="font-weight:900;font-size:1.4rem;color:#f43f5e;">0đ</span>
                    </div>
                </div>

                <?php if (!isLoggedIn()): ?>
                <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:var(--radius);padding:12px 16px;margin-bottom:12px;font-size:0.8rem;color:#f59e0b;font-weight:600;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-lock"></i>
                    Vui lòng <a href="login.php" style="color:var(--primary-2);font-weight:800;">đăng nhập</a> để đặt hàng!
                </div>
                <?php endif; ?>

                <button id="buyBtn" class="btn-premium" style="width:100%;border-radius:var(--radius);padding:14px;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;" onclick="placeOrder()">
                    <i class="fas fa-bolt"></i> THANH TOÁN NGAY
                </button>

                <!-- Warning Note -->
                <div class="alert-note" style="margin-top: 20px; background: rgba(245,158,11,0.06); border: 1px solid rgba(245,158,11,0.25); border-radius: 16px; padding: 18px;">
                    <h6 style="color: #d97706; font-weight: 800; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-circle"></i> LƯU Ý NÊN ĐỌC TRÁNH MẤT TIỀN
                    </h6>
                    <div style="font-size: 0.78rem; color: #92400e; font-weight: 600; line-height: 1.6;">
                        <p style="margin-bottom: 8px;">- <b>Nghiêm cấm</b> buff các đơn có nội dung vi phạm pháp luật, chính trị, đồi trụy... Nếu cố tình buff bạn sẽ bị <b>trừ hết tiền và ban khỏi hệ thống vĩnh viễn</b>, và phải chịu hoàn toàn trách nhiệm trước pháp luật.</p>
                        <p style="margin-bottom: 8px;">- Nếu đơn đang chạy trên hệ thống mà bạn vẫn mua ở các hệ thống bên khác hoặc đè nhiều đơn, nếu có tình trạng hụt, thiếu số lượng giữa 2 bên thì sẽ <b>không được xử lí</b>.</p>
                        <p style="margin-bottom: 8px;">- Đơn cài sai thông tin hoặc lỗi trong quá trình tăng hệ thống sẽ không hoàn lại tiền.</p>
                        <p>- Nếu gặp lỗi hãy nhắn tin hỗ trợ phía bên phải góc màn hình hoặc vào mục liên hệ hỗ trợ để được hỗ trợ tốt nhất.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORY TAB -->
        <div id="tab-history" style="display:none;">
            <div class="main-panel">
                <h5 style="font-weight:800;font-size:0.88rem;margin-bottom:20px;display:flex;align-items:center;gap:8px;">
                    <i class="fas fa-history" style="color:var(--primary-2);"></i> LỊCH SỬ ĐƠN HÀNG
                </h5>
                <?php if (!isLoggedIn()): ?>
                <div class="empty-state">
                    <i class="fas fa-lock"></i>
                    <p>Vui lòng <a href="login.php" style="color:var(--primary-2);font-weight:700;">đăng nhập</a> để xem</p>
                </div>
                <?php elseif (empty($user_orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>Bạn chưa có đơn hàng nào. Hãy trải nghiệm dịch vụ ngay!</p>
                </div>
                <?php else: ?>
                <!-- Desktop Table -->
                <div class="history-table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Dịch vụ / Server</th>
                                <th>Thông tin đơn</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($user_orders as $ord): 
                                $status_class = 'status-' . $ord['status'];
                                $status_text = strtoupper($ord['status']);
                                if ($ord['status'] == 'pending') $status_text = 'ĐANG CHỜ XỬ LÝ';
                                if ($ord['status'] == 'processing') $status_text = 'ĐANG CHẠY';
                                if ($ord['status'] == 'completed') $status_text = 'HOÀN THÀNH';
                                if ($ord['status'] == 'failed') $status_text = 'LỖI / THẤT BẠI';
                            ?>
                            <tr class="history-row">
                                <td>
                                    <span style="font-weight:800;color:var(--primary-2);">#<?php echo $ord['id']; ?></span>
                                    <span class="copy-badge" onclick="copyText('<?php echo $ord['id']; ?>')"><i class="far fa-copy"></i></span>
                                </td>
                                <td>
                                    <div style="font-weight:700;font-size:0.85rem;margin-bottom:3px;"><?php echo $ord['prod_name']; ?></div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);max-width:250px;"><?php echo $ord['pkg_name']; ?></div>
                                </td>
                                <td>
                                    <div style="margin-bottom:4px;"><i class="fas fa-link" style="width:14px;opacity:0.6;"></i> <a href="<?php echo htmlspecialchars($ord['link']); ?>" target="_blank" style="color:var(--primary-w);text-decoration:underline;">Xem link</a></div>
                                    <div style="font-size:0.75rem;"><i class="fas fa-shopping-cart" style="width:14px;opacity:0.6;"></i> Số lượng: <b><?php echo number_format($ord['quantity']); ?></b></div>
                                    <div style="font-size:0.75rem;"><i class="fas fa-coins" style="width:14px;opacity:0.6;"></i> Tổng: <b style="color:var(--primary-2);"><?php echo number_format($ord['total_price']); ?>đ</b></div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <i class="fas <?php echo ($ord['status']=='completed'?'fa-check-circle':($ord['status']=='failed'?'fa-times-circle':'fa-clock')); ?>"></i>
                                        <?php echo $status_text; ?>
                                    </span>
                                </td>
                                <td style="font-size:0.75rem;color:var(--text-muted);">
                                    <?php echo date('H:i:s', strtotime($ord['created_at'])); ?><br>
                                    <?php echo date('d/m/Y', strtotime($ord['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <?php foreach ($user_orders as $ord): 
                    $status_class = 'status-' . $ord['status'];
                    $status_text = strtoupper($ord['status']);
                    if ($ord['status'] == 'pending') $status_text = 'ĐANG CHỜ XỬ LÝ';
                    if ($ord['status'] == 'processing') $status_text = 'ĐANG CHẠY';
                    if ($ord['status'] == 'completed') $status_text = 'HOÀN THÀNH';
                    if ($ord['status'] == 'failed') $status_text = 'LỖI / THẤT BẠI';
                ?>
                <div class="history-card">
                    <div class="h-card-header">
                        <div>
                            <div class="h-card-label">Mã đơn hàng</div>
                            <div class="h-card-val" style="color:var(--primary-2);">#<?php echo $ord['id']; ?> <i class="far fa-copy" onclick="copyText('<?php echo $ord['id']; ?>')" style="margin-left:5px;cursor:pointer;"></i></div>
                        </div>
                        <span class="status-badge <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </div>
                    
                    <div style="margin-bottom:12px;">
                        <div class="h-card-label">Dịch vụ / Server</div>
                        <div class="h-card-val" style="font-size:0.8rem;"><?php echo $ord['prod_name']; ?></div>
                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:2px;"><?php echo $ord['pkg_name']; ?></div>
                    </div>

                    <div style="margin-bottom:12px;">
                        <div class="h-card-label">Liên kết</div>
                        <div style="font-size:0.75rem;word-break:break-all;"><a href="<?php echo htmlspecialchars($ord['link']); ?>" target="_blank" style="color:var(--primary-w);"><?php echo $ord['link']; ?></a></div>
                    </div>

                    <div class="h-card-grid">
                        <div>
                            <div class="h-card-label">Số lượng / Giá</div>
                            <div class="h-card-val"><?php echo number_format($ord['quantity']); ?> / <span style="color:var(--primary-2);"><?php echo number_format($ord['total_price']); ?>đ</span></div>
                        </div>
                        <div>
                            <div class="h-card-label">Thời gian</div>
                            <div class="h-card-val" style="font-size:0.75rem;"><?php echo date('H:i d/m/Y', strtotime($ord['created_at'])); ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- end main -->
</div><!-- end layout -->
</div><!-- end container -->
</div><!-- end mxh-wrap -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
'use strict';
let currentPkg = null;

// ===== PLATFORM ACCORDION =====
function togglePlt(btn) {
    const item = btn.closest('.plt-item');
    const wasOpen = item.classList.contains('open');

    document.querySelectorAll('.plt-item.open').forEach(p => {
        p.classList.remove('open');
        p.querySelector('.plt-row').classList.remove('active');
    });

    if (!wasOpen) {
        item.classList.add('open');
        btn.classList.add('active');
    }
}

// ===== LOAD PACKAGES =====
async function loadPkgs(productId, productName, el) {
    document.querySelectorAll('.srv-item').forEach(i => i.classList.remove('active'));
    if (el) el.classList.add('active');

    document.getElementById('curSvcName').textContent = productName;
    document.getElementById('pkgCountBadge').style.display = 'none';
    currentPkg = null;
    resetSum();

    const list = document.getElementById('pkgList');
    list.innerHTML = `
        <div class="pkg-skeleton"></div>
        <div class="pkg-skeleton" style="opacity:.6;"></div>
        <div class="pkg-skeleton" style="opacity:.3;"></div>
    `;

    try {
        const res = await fetch(`includes/ajax/get_packages.php?product_id=${productId}`);
        const pkgs = await res.json();

        if (!pkgs.length) {
            list.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>Chưa có gói dịch vụ nào.</p></div>';
            return;
        }

        const badge = document.getElementById('pkgCountBadge');
        badge.textContent = pkgs.length + ' gói';
        badge.style.display = 'inline-block';

        list.innerHTML = '';
        pkgs.forEach((pkg, idx) => {
            const card = document.createElement('div');
            card.className = `pkg-card ${idx === 0 ? 'selected' : ''}`;
            if (idx === 0) { currentPkg = pkg; }
            
            // Format name and extract badges
            let rawName = pkg.name;
            let badgesHTML = '';
            
            // Extract typical Tags
            const tagMatch = [
                { regex: /Có hủy hoãn/i, type: 'warning' },
                { regex: /ĐỘC QUYỀN/i, type: 'danger-outline' },
                { regex: /Rẻ Nhanh/i, type: 'success-outline' },
                { regex: /DONE NHANH/i, type: 'success-outline' },
                { regex: /Bảo trì/i, type: 'danger' },
                { regex: /Chạy Sau Vài Phút/i, type: 'danger-outline' },
                { regex: /Chạy Sau 10 Phút/i, type: 'success-outline' },
                { regex: /Bảo Hành/i, type: 'info' },
                { regex: /Không Bảo Hành/i, type: 'danger' },
            ];
            
            tagMatch.forEach(t => {
                if (t.regex.test(rawName)) {
                    const matchedStr = rawName.match(t.regex)[0];
                    rawName = rawName.replace(t.regex, '').trim();
                    if(t.type === 'warning') badgesHTML += `<span style="background:#f59e0b;color:#fff;padding:1px 5px;border-radius:4px;font-size:0.58rem;font-weight:800;margin-right:4px;">${matchedStr}</span>`;
                    else if(t.type === 'danger') badgesHTML += `<span style="background:#ef4444;color:#fff;padding:1px 5px;border-radius:4px;font-size:0.58rem;font-weight:800;margin-right:4px;">${matchedStr}</span>`;
                    else if(t.type === 'danger-outline') badgesHTML += `<span style="border:1px solid #ef4444;color:#ef4444;padding:0px 4px;border-radius:4px;font-size:0.58rem;font-weight:800;margin-right:4px;">${matchedStr}</span>`;
                    else if(t.type === 'success-outline') badgesHTML += `<span style="border:1px solid #10b981;color:#10b981;padding:0px 4px;border-radius:4px;font-size:0.58rem;font-weight:800;margin-right:4px;">${matchedStr}</span>`;
                    else if(t.type === 'info') badgesHTML += `<span style="background:#3b82f6;color:#fff;padding:1px 5px;border-radius:4px;font-size:0.58rem;font-weight:800;margin-right:4px;">${matchedStr}</span>`;
                }
            });
            
            // Extract Server Prefix like ✨ OK1✨ or Sv1 or VIP6
            let serverName = '';
            const svMatch = rawName.match(/^(✨[^✨]+✨|Sv\d+|VIP\d+|✨\s?[A-Za-z0-9]+\s?✨)\s*-?\s*/i);
            if(svMatch) {
                serverName = svMatch[1].trim();
                rawName = rawName.replace(svMatch[0], '').trim();
            } else {
                // fallback to first word if it looks like a short server tag
                const words = rawName.split(' ');
                if(words[0].length <= 8 && !words[0].toLowerCase().includes('facebook') && !words[0].toLowerCase().includes('tăng')) {
                    serverName = words[0];
                    rawName = rawName.replace(words[0], '').trim();
                }
            }

            const apiLabel = pkg.api_service_id ? `<span style="background:#38bdf8;color:#fff;padding:1px 4px;border-radius:4px;font-size:0.62rem;font-weight:900;">[${pkg.api_service_id}]</span>` : '';
            const svLabel = serverName ? `<span style="background:#10b981;color:#fff;padding:1px 5px;border-radius:4px;font-size:0.65rem;font-weight:800;box-shadow:0 2px 5px rgba(16,185,129,0.3);">- ${serverName}</span>` : '';
            
            // Format Price Badge
            const priceVal = parseFloat(pkg.price);
            const formattedVal = (priceVal < 10) ? priceVal.toFixed(4).replace(/\.?0+$/, '') : priceVal.toLocaleString('vi-VN');
            const priceBadge = `<span style="background:var(--primary-2);color:#fff;padding:1px 5px;border-radius:4px;font-size:0.62rem;font-weight:900;margin-left:4px;">${formattedVal}đ</span>`;

            card.innerHTML = `
                <div class="radio-dot" style="margin-top:4px;"></div>
                <div style="flex:1;min-width:0;line-height:1.4;">
                    <div style="font-size:0.78rem;font-weight:700;color:var(--text-light);word-wrap:break-word;">
                        <span style="display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;margin-bottom:2px;vertical-align:middle;">
                            ${apiLabel} ${svLabel}
                        </span>
                        <span style="vertical-align:middle;color:var(--text);">${rawName}</span> 
                        ${priceBadge}
                    </div>
                    ${badgesHTML ? `<div style="margin-top:4px;display:flex;flex-wrap:wrap;gap:4px;">${badgesHTML}</div>` : ''}
                </div>
                <i class="fas fa-check-circle" style="color:var(--primary-2);font-size:1.1rem;opacity:${idx===0?1:0};transition:.2s;flex-shrink:0;margin-left:10px;"></i>
            `;

            card.addEventListener('click', () => {
                document.querySelectorAll('.pkg-card').forEach(c => {
                    c.classList.remove('selected');
                    const checkIcon = c.querySelector('.fa-check-circle');
                    if(checkIcon) checkIcon.style.opacity = '0';
                });
                card.classList.add('selected');
                const myCheck = card.querySelector('.fa-check-circle');
                if(myCheck) myCheck.style.opacity = '1';
                currentPkg = pkg;
                calcTotal();
                
                const nameLC = pkg.name.toLowerCase();
                const typeLC = (pkg.service_type || '').toLowerCase();
                
                // Hide ALL by default
                const reactEl = document.getElementById('reactionPicker');
                const commentEl = document.getElementById('commentBox');
                reactEl.style.display = 'none';
                commentEl.style.display = 'none';

                // 1. Detection: COMMENT BOX (Highest Priority)
                const commentTerms = ['bình luận', 'comment', 'cmt', 'nội dung', 'review', 'đánh giá'];
                const isCommentSvc = typeLC.includes('comment') || commentTerms.some(t => nameLC.includes(t));
                
                // 2. Detection: REACTION PICKER
                const reactionTerms = ['cảm xúc', 'reaction', 'emoji', 'emotions', 'thả icon', 'biểu tượng', 'chọn icon'];
                const isReactionSvc = reactionTerms.some(t => nameLC.includes(t));
                
                // 3. Final Logic: Comment and Follow MUST NEVER show icons
                if (isCommentSvc) {
                    commentEl.style.display = 'block';
                    reactEl.style.display = 'none'; // Force hide icons for cmt
                } else if (isReactionSvc) {
                    // List of terms that signify it's NOT a reaction service
                    const blockIcons = ['view', 'mắt', 'follow', 'sub', 'theo dõi', 'member', 'thành viên', 'vào nhóm', 'share', 'chia sẻ', 'fl', 'group'];
                    const isBlocked = blockIcons.some(t => nameLC.includes(t));
                    
                    if (!isBlocked) {
                        reactEl.style.display = 'block';
                    }
                }

                // Display quantity range if available
                if (pkg.stock) {
                    document.getElementById('qtyRange').textContent = `(Tối thiểu 10 ~ ${pkg.stock.toLocaleString()})`;
                }
                
                // Auto-set emoji if named
                if (isReactionSvc) {
                    const found = ['like', 'care', 'love', 'haha', 'wow', 'sad', 'angry'].find(r => nameLC.includes(r));
                    if (found) {
                        const targetItem = document.querySelector(`.reaction-item img[alt="${found}"]`)?.parentElement;
                        if (targetItem) setReaction(found, targetItem);
                    } else {
                        // Default to like for generic reaction services
                        const defaultItem = document.querySelector(`.reaction-item img[alt="like"]`)?.parentElement;
                        if (defaultItem) setReaction('like', defaultItem);
                    }
                }
                
                if (isCommentSvc) {
                    document.getElementById('orderQty').readOnly = true;
                    document.getElementById('orderQty').style.opacity = '0.6';
                } else {
                    document.getElementById('orderQty').readOnly = false;
                    document.getElementById('orderQty').style.opacity = '1';
                }
            });

            list.appendChild(card);
        });

        calcTotal();
    } catch (e) {
        list.innerHTML = '<div style="color:#f43f5e;padding:20px;font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Lỗi kết nối máy chủ.</div>';
    }
}

// ===== CALCULATIONS =====
function calcTotal() {
    if (!currentPkg) return;
    
    // Update quantity if it's a comment box
    const commentBox = document.getElementById('commentBox');
    if (commentBox && commentBox.style.display !== 'none') {
        const lines = document.getElementById('orderComment').value.split('\n').filter(l => l.trim() !== '');
        document.getElementById('orderQty').value = lines.length;
    }

    const qty = parseInt(document.getElementById('orderQty').value) || 0;
    const price = parseFloat(currentPkg.price);
    const total = (qty / 1000) * price;

    document.getElementById('sumName').textContent = currentPkg.name;
    document.getElementById('sumPrice').textContent = price.toLocaleString('vi-VN') + 'đ / 1K';
    document.getElementById('sumTotal').textContent = Math.round(total).toLocaleString('vi-VN') + 'đ';
}

// Auto-calc comments
document.getElementById('orderComment').addEventListener('input', calcTotal);

function resetSum() {
    document.getElementById('sumName').textContent = '--';
    document.getElementById('sumPrice').textContent = '0đ / 1K';
    document.getElementById('sumTotal').textContent = '0đ';
}

// ===== ORDER =====
async function placeOrder() {
    <?php if (!isLoggedIn()): ?>
    window.location.href = 'login.php'; return;
    <?php endif; ?>

    const link = document.getElementById('orderLink').value.trim();
    const qty  = parseInt(document.getElementById('orderQty').value);

    const cfg = { confirmButtonColor:'#7c3aed', background:'var(--bg-card)', color:'var(--text)' };
    if (!currentPkg) return Swal.fire({...cfg, icon:'warning', title:'Chưa chọn gói', text:'Vui lòng chọn một gói dịch vụ.'});
    if (!link)       return Swal.fire({...cfg, icon:'warning', title:'Thiếu link', text:'Vui lòng nhập link hoặc ID.'});
    if (qty <= 0)    return Swal.fire({...cfg, icon:'warning', title:'Số lượng không hợp lệ'});

    const btn = document.getElementById('buyBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

    try {
        const qty = document.getElementById('orderQty').value;
        const link = document.getElementById('orderLink').value;
        const comment = document.getElementById('orderComment').value;
        
        const fd = new FormData();
        fd.append('package_id', currentPkg.id);
        fd.append('link', link);
        fd.append('quantity', qty);
        
        const reactionBox = document.getElementById('reactionPicker');
        if (reactionBox.style.display !== 'none') {
            fd.append('reaction', document.getElementById('reactionType').value);
        }
        
        const commentBox = document.getElementById('commentBox');
        if (commentBox.style.display !== 'none') {
            fd.append('comment', comment);
        }


        const res  = await fetch('includes/ajax/order_process.php?v=' + Date.now(), {method:'POST', body:fd});
        const data = await res.json();

        if (data.status === 'success') {
            Swal.fire({
                ...cfg, icon:'success',
                title: '✅ Đặt Hàng Thành Công!',
                html: `${data.message}<br><br><small style="opacity:.7">Số dư còn lại: <b>${Math.round(data.remaining_balance).toLocaleString('vi-VN')}đ</b></small>`,
                confirmButtonText: 'Tuyệt vời!',
            }).then(() => location.reload());
        } else {
            Swal.fire({...cfg, icon:'error', title:'Thất Bại', text:data.message, confirmButtonColor:'#f43f5e'});
        }
    } catch(e) {
        console.error('Order Error:', e);
        Swal.fire({
            ...cfg, icon:'error', title:'Lỗi Hệ Thống', 
            text:'Có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại hoặc liên hệ hỗ trợ.', 
            confirmButtonColor:'#f43f5e'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-bolt"></i> THANH TOÁN NGAY';
    }
}

// ===== TABS =====
function switchTab(tab, el) {
    document.querySelectorAll('.mxh-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tab-order').style.display   = tab === 'order'   ? 'block' : 'none';
    document.getElementById('tab-history').style.display = tab === 'history' ? 'block' : 'none';
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded', () => {
    <?php if ($first_product): ?>
    loadPkgs(<?php echo (int)$first_product['id']; ?>, '<?php echo addslashes(htmlspecialchars($first_product['name'])); ?>');
    <?php else: ?>
    document.getElementById('pkgList').innerHTML = '<div class="empty-state"><i class="fas fa-database"></i><p>Chưa có dữ liệu. <a href="migrate_autosub.php" style="color:var(--primary-2)">Chạy migration</a></p></div>';
    <?php endif; ?>
});
// ===== REACTIONS =====
function setReaction(type, el) {
    document.getElementById('reactionType').value = type;
    document.querySelectorAll('.reaction-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
}

// ===== UTILS =====
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            icon: 'success',
            title: 'Đã sao chép mã đơn: ' + text,
            background: 'var(--bg-card)',
            color: 'var(--text)'
        });
    });
}
</script>

<!-- Sidebar HTML will be rendered in social.php or footer -->

<aside class="mxh-sidebar" id="mxhSidebar" onclick="if(event.target===this) this.classList.remove('active')">
    <div class="sidebar-inner">
        <?php renderMXHSidebar($sorted_platforms, $plt_cfg_sb, false); ?>
    </div>
</aside>

<?php include 'footer.php'; ?>
