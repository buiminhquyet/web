<?php 
require_once '../includes/config.php'; 

if (!isAdmin()) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUYETDEV Admin - Control Center</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --nav-height: 85px;
        }
        body { font-family: 'Outfit', sans-serif; padding-top: var(--nav-height); }
        
        /* New Horizontal Navbar */
        .admin-navbar {
            height: var(--nav-height);
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            border-bottom: 1px solid var(--glass-border);
            z-index: 2000;
            background: rgba(13, 15, 24, 0.85); /* Slightly darker for clarity */
            backdrop-filter: blur(15px);
        }

        .admin-nav-container {
            display: flex;
            align-items: center;
            gap: 30px;
            height: 100%;
        }

        .admin-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            height: 100%;
        }

        .admin-main {
            padding: 40px;
            min-height: calc(100vh - var(--nav-height));
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            border-radius: 12px;
            color: var(--text-light);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0.65;
            white-space: nowrap;
        }

        .admin-nav-link:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-1px);
        }

        .admin-nav-link.active {
            background: var(--primary-gradient);
            color: white !important;
            opacity: 1;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
        }

        .admin-nav-link i { font-size: 1rem; }

        /* Responsive Grid Utilities */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .user-status-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        @media (max-width: 992px) {
            .admin-navbar { padding: 0 20px !important; }
            .admin-nav-container { gap: 15px !important; }
            .admin-menu { overflow-x: auto !important; -webkit-overflow-scrolling: touch; padding: 0 10px !important; }
            .admin-menu::-webkit-scrollbar { display: none !important; }
            .admin-nav-link span { display: none !important; }
            .admin-nav-link { padding: 12px !important; }
            .admin-nav-link i { font-size: 1.2rem !important; }
            
            .stat-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 15px !important; }
            .main-grid { grid-template-columns: 1fr !important; gap: 20px !important; }
        }

        @media (max-width: 768px) {
            :root { --nav-height: 70px !important; }
            .admin-navbar { padding: 0 15px !important; }
            .logo-text { display: none !important; }
            .admin-main { padding: 25px 15px !important; }
            .admin-main header { margin-top: 20px !important; }
        }

        /* Force True Dark for Admin */
        [data-theme="dark"] body { background: #0d0f18; }
        .glass-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); }
        .btn-premium { background: var(--primary-gradient); border: none; font-weight: 700; color: white; cursor: pointer; transition: 0.3s; }
        input, select, textarea { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            color: #333;
            border-radius: 12px !important;
        }

        select option {
            background: #fff !important;
            color: #333 !important;
        }

        [data-theme="dark"] input, [data-theme="dark"] select, [data-theme="dark"] textarea {
            color: white !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }
        
        [data-theme="dark"] select option {
            background: #0d0f18 !important;
            color: white !important;
        }
        input:focus, textarea:focus { border-color: var(--primary) !important; box-shadow: 0 0 10px rgba(124, 58, 237, 0.2) !important; }
        
        .theme-toggle-btn {
            width: 40px; height: 40px; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            background: rgba(255, 255, 255, 0.05); border: 1px solid var(--glass-border);
            color: var(--text-light); cursor: pointer; transition: 0.3s;
        }
        .theme-toggle-btn:hover { background: var(--primary-gradient); color: white; transform: rotate(15deg); }

        @media (max-width: 576px) {
            .stat-grid { grid-template-columns: 1fr !important; }
        }

        /* Sticky Action Bar & Quick Action Styles */
        .sticky-action-bar {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 800px;
            background: rgba(13, 15, 24, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            border-radius: 20px;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 3000;
            animation: slideUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border-top: 1px solid rgba(124, 58, 237, 0.3);
        }

        @keyframes slideUp {
            from { transform: translate(-50%, 100px); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }

        .quick-save-btn {
            padding: 8px 15px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
            font-size: 0.75rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .quick-save-btn:hover {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }

        .section-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Responsive Utilities */
        .admin-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .admin-grid {
                grid-template-columns: 1fr !important;
                gap: 20px !important;
            }
            .section-header-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .section-header-flex .quick-save-btn {
                width: 100%;
                justify-content: center;
            }
            .glass-card {
                padding: 20px !important;
            }
            .sticky-action-bar {
                width: 95% !important;
                padding: 12px 20px !important;
                bottom: 10px !important;
            }
            .sticky-action-bar .btn-premium {
                padding: 10px 20px !important;
                font-size: 0.8rem !important;
            }
        }
    </style>
</head>
<body class="admin-body">

    <nav class="admin-navbar">
        <div class="admin-nav-container">
            <!-- Logo Section -->
            <a href="index.php" style="display: flex; align-items: center; gap: 15px; text-decoration: none;">
                <div class="logo-box" style="width: 40px; height: 40px; background: var(--primary-gradient); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.2rem;">Q</div>
                <div class="logo-text" style="line-height: 1;">
                    <h4 class="text-gradient" style="font-weight: 800; font-size: 0.85rem; letter-spacing: 1px; margin: 0;">CONTROL CENTER</h4>
                    <p style="font-size: 0.65rem; opacity: 0.5; margin: 0; font-weight: 600;">ADMIN PANEL V2</p>
                </div>
            </a>

            <!-- Navigation Menu -->
            <div class="admin-menu">
                <a href="index.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> <span>Dashboard</span>
                </a>
                <a href="categories.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-layer-group"></i> <span>Chuyên mục</span>
                </a>
                <a href="products.php" class="admin-nav-link <?php echo strpos(basename($_SERVER['PHP_SELF']), 'product') !== false ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> <span>Sản phẩm</span>
                </a>
                <a href="orders.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-bag"></i> <span>Đơn hàng</span>
                </a>
                <a href="users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> <span>Users</span>
                </a>
                <a href="settings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-sliders-h"></i> <span>Cài đặt</span>
                </a>
            </div>
        </div>

        <!-- User Actions -->
        <div class="user-status-box">
            <button id="theme-toggle" class="theme-toggle-btn">
                <i class="fas fa-moon"></i>
            </button>

            <div style="width: 1px; height: 25px; background: var(--glass-border);"></div>
            
            <div class="glass" style="padding: 8px 15px; border-radius: 50px; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981;"></div>
                <span style="font-size: 0.75rem; font-weight: 600; color: #10b981;">Online</span>
            </div>
            
            <div style="width: 1px; height: 25px; background: var(--glass-border);"></div>
            
            <a href="../logout.php" class="admin-nav-link" style="color: #ef4444; opacity: 1; padding: 0;">
                <i class="fas fa-power-off"></i>
            </a>
        </div>
    </nav>

    <main class="admin-main">
        <header style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
            <div>
                <p style="opacity: 0.5; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
                    <?php 
                    $page = basename($_SERVER['PHP_SELF']);
                    if($page == 'index.php') echo 'Overview Analytics';
                    elseif($page == 'products.php' || $page == 'product_add.php' || $page == 'packages.php') echo 'Inventory Management';
                    elseif($page == 'users.php') echo 'Member Management';
                    elseif($page == 'settings.php') echo 'System Configuration';
                    else echo 'Admin Control';
                    ?>
                </p>
                <h1 style="font-size: 2.2rem; font-weight: 900; letter-spacing: -1.2px; line-height: 1;">
                    <?php 
                    if($page == 'index.php') echo 'Bảng Điều Khiển';
                    elseif($page == 'products.php' || $page == 'product_add.php' || $page == 'packages.php') echo 'Quản Lý Sản Phẩm';
                    elseif($page == 'users.php') echo 'Quản Lý Thành Viên';
                    elseif($page == 'settings.php') echo 'Cài Đặt Hệ Thống';
                    else echo 'Quản Trị';
                    ?>
                </h1>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-light);">Chào, <?php echo $_SESSION['username']; ?> 👋</div>
                <div style="font-size: 0.75rem; opacity: 0.5;">Quản trị viên hệ thống</div>
            </div>
        </header>
