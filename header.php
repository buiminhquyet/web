<?php require_once 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="vi" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_name'] ?? 'QUYETDEV'; ?> - Nền Tảng Tài Khoản Số & Dịch Vụ Premium</title>
    <meta name="description" content="<?php echo $settings['site_name'] ?? 'QUYETDEV'; ?> cung cấp tài khoản Premium bản quyền (Netflix, Spotify, ChatGPT Plus), dịch vụ Mạng Xã Hội (SMM) chất lượng cao, giá tốt nhất Việt Nam.">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700;800;900&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 & Animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- ===================== PRELOADER ===================== -->
<div id="preloader">
    <div class="loader-content">
        <div class="loader-circle"></div>
        <h3 class="text-gradient" style="font-weight: 900; font-size: 1.3rem; margin-top: 16px; letter-spacing: -0.5px;"><?php echo $settings['site_logo'] ?? 'QUYETDEV'; ?></h3>
    </div>
</div>

<!-- ===================== ANNOUNCEMENT BAR ===================== -->
<div class="announcement-bar">
    <div class="announcement-track" id="announceTrack">
        <?php 
        $announce = $settings['announcement'] ?? 'Chào mừng đến với QUYETDEV - Hệ thống tài khoản Premium số 1 Việt Nam';
        $items = [
            ['icon' => 'fas fa-bolt', 'text' => 'Giao hàng tự động 24/7'],
            ['icon' => 'fas fa-shield-alt', 'text' => 'Bảo hành 1:1 uy tín'],
            ['icon' => 'fas fa-star', 'text' => htmlspecialchars($announce)],
            ['icon' => 'fas fa-fire', 'text' => 'Ưu đãi giảm đến 90%'],
            ['icon' => 'fab fa-telegram', 'text' => 'Hỗ trợ ngay qua Telegram'],
            ['icon' => 'fas fa-lock', 'text' => 'Thanh toán bảo mật 100%'],
        ];
        // Duplicate for seamless loop
        $allItems = array_merge($items, $items);
        foreach ($allItems as $item):
        ?>
        <div class="announcement-item">
            <i class="<?php echo $item['icon']; ?>"></i>
            <?php echo $item['text']; ?>
            <span class="sep">⬥</span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ===================== GREETING BAR (Desktop only) ===================== -->
<div class="greeting-bar">
    <div class="container" style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div class="user-welcome">
            <i class="fas fa-wifi" style="color: var(--primary-2); margin-right: 6px; font-size: 0.75rem;"></i>
            Bạn đang kết nối đến hệ thống <strong><?php echo $settings['site_name'] ?? 'QUYETDEV'; ?></strong>
        </div>
        <div style="display: flex; gap: 16px; align-items: center;">
            <?php if (isLoggedIn()): ?>
                <div class="balance-pill" onclick="window.location.href='topup.php'">
                    <i class="fas fa-wallet" style="font-size: 0.75rem;"></i>
                    <span><?php echo format_currency($_SESSION['balance'] ?? 0); ?></span>
                </div>
                <span style="color: var(--text-muted); font-size: 0.78rem;">
                    Xin chào, <strong style="color: var(--text);"><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                </span>
            <?php else: ?>
                <a href="login.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.78rem; font-weight: 600; transition: var(--transition);" onmouseover="this.style.color='var(--primary-2)'" onmouseout="this.style.color='var(--text-muted)'">
                    <i class="fas fa-sign-in-alt" style="margin-right: 5px;"></i> Đăng nhập để xem số dư
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===================== HEADER ===================== -->
<header id="mainHeader">
    <div class="container">
        <nav class="navbar" style="padding: 0;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <!-- Logo -->
                <div class="logo">
                    <a href="index.php">
                        <div class="logo-icon"><?php echo strtoupper(substr($settings['site_logo'] ?? 'Q', 0, 1)); ?></div>
                        <span class="logo-text"><?php echo $settings['site_logo'] ?? 'QUYETDEV'; ?></span>
                    </a>
                </div>
            </div>

            <!-- Desktop Nav -->
            <ul class="nav-links" id="desktopNav">
                <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'style="color:var(--text);background:var(--bg-card);"' : ''; ?>>
                    <i class="fas fa-home" style="font-size: 0.8rem;"></i> Trang Chủ
                </a></li>
                <li><a href="products.php" <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'style="color:var(--text);background:var(--bg-card);"' : ''; ?>>
                    <i class="fas fa-th-large" style="font-size: 0.8rem;"></i> Sản Phẩm
                </a></li>
                <li><a href="topup.php" <?php echo basename($_SERVER['PHP_SELF']) == 'topup.php' ? 'style="color:var(--text);background:var(--bg-card);"' : ''; ?>>
                    <i class="fas fa-wallet" style="font-size: 0.8rem;"></i> Nạp Tiền
                </a></li>
                <li><a href="social.php" class="mxh-link" <?php echo basename($_SERVER['PHP_SELF']) == 'social.php' ? 'style="color:var(--text);background:var(--bg-card);"' : ''; ?>>
                    <i class="fas fa-fire" style="font-size: 0.8rem; color: #ec4899;"></i> Dịch Vụ MXH
                </a></li>
                <?php if (isAdmin()): ?>
                <li><a href="admin/index.php" style="color: var(--primary-2); font-weight: 700;">
                    <i class="fas fa-tools" style="font-size: 0.8rem;"></i> Quản Trị
                </a></li>
                <?php endif; ?>
            </ul>

            <!-- Nav Actions -->
            <div class="nav-actions">
                <!-- Theme Toggle -->
                <button id="theme-toggle" class="btn-icon" title="Đổi giao diện" aria-label="Toggle theme" style="position: relative; z-index: 2001;">
                    <i class="fas fa-moon"></i>
                </button>

                <?php if (isLoggedIn()): ?>
                    <!-- Balance (shown on mobile) -->
                    <div class="balance-pill" style="display: none;" id="mobileBalance" onclick="window.location.href='topup.php'">
                        <i class="fas fa-wallet" style="font-size: 0.7rem;"></i>
                        <span style="font-size: 0.75rem;"><?php echo format_currency($_SESSION['balance'] ?? 0); ?></span>
                    </div>
                    <!-- Avatar -->
                    <a href="profile.php" class="user-avatar" title="Tài khoản của tôi">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
                    </a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; font-size: 0.78rem; color: var(--text-muted); font-weight: 600; padding: 8px 12px; border-radius: var(--radius-sm); transition: var(--transition); white-space: nowrap;"
                       onmouseover="this.style.color='var(--text)';this.style.background='var(--bg-card)'" 
                       onmouseout="this.style.color='var(--text-muted)';this.style.background='transparent'">
                        Đăng Nhập
                    </a>
                    <a href="register.php" class="btn-premium" style="padding: 9px 20px; font-size: 0.82rem; border-radius: var(--radius-sm);">
                        Đăng Ký
                    </a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<script>
// ===== PRELOADER =====
window.addEventListener('load', () => {
    const pre = document.getElementById('preloader');
    setTimeout(() => {
        pre.style.opacity = '0';
        setTimeout(() => pre.style.display = 'none', 400);
    }, 800);
});

// ===== HEADER SCROLL =====
window.addEventListener('scroll', () => {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.style.boxShadow = '0 8px 32px rgba(0,0,0,0.3)';
    } else {
        header.style.boxShadow = 'none';
    }
});

// ===== SPOTLIGHT EFFECT =====
document.addEventListener('mousemove', e => {
    document.querySelectorAll('.spotlight').forEach(el => {
        const rect = el.getBoundingClientRect();
        el.style.setProperty('--x', `${e.clientX - rect.left}px`);
        el.style.setProperty('--y', `${e.clientY - rect.top}px`);
    });
});

// Show balance on mobile nav
const mobileBalance = document.getElementById('mobileBalance');
if (mobileBalance && window.innerWidth < 992) {
    mobileBalance.style.display = 'flex';
}
window.addEventListener('resize', () => {
    if (mobileBalance) {
        mobileBalance.style.display = window.innerWidth < 992 ? 'flex' : 'none';
    }
});
// ===== REAL-TIME BALANCE POLLING =====
<?php if(isLoggedIn()): ?>
let currentBalance = <?php echo $_SESSION['balance'] ?? 0; ?>;
function checkBalance() {
    fetch('api/get_balance.php')
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success' && data.balance > currentBalance) {
            // Balance increased!
            const balanceSpans = document.querySelectorAll('.balance-pill span');
            balanceSpans.forEach(span => {
                span.innerText = data.formatted_balance;
                span.classList.add('balance-pulse-active');
                setTimeout(() => span.classList.remove('balance-pulse-active'), 5000);
            });
            currentBalance = data.balance;
        }
    });
}
// Start polling after 5 seconds
setTimeout(() => {
    setInterval(checkBalance, 6000);
}, 5000);
<?php endif; ?>
</script>
