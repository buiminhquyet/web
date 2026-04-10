<!-- ===================== FLOATING ACTIONS ===================== -->
<?php 
    $zalo_url = $settings['zalo_link'] ?? '';
    if (!empty($zalo_url) && !filter_var($zalo_url, FILTER_VALIDATE_URL)) {
        $zalo_url = 'https://zalo.me/' . preg_replace('/[^0-9]/', '', $zalo_url);
    }
    
    $fb_url = $settings['facebook_link'] ?? '';
    if (!empty($fb_url) && !filter_var($fb_url, FILTER_VALIDATE_URL)) {
        if (strpos($fb_url, 'facebook.com') === false) {
            $fb_url = 'https://facebook.com/' . $fb_url;
        }
    }
?>
<div class="floating-actions">
    <a href="<?php echo secure_redirect('zalo'); ?>" class="float-btn zalo" target="_blank" rel="noopener noreferrer" title="Chat Zalo" aria-label="Zalo">
        <img src="assets/images/zalo_icon_circle.png?v=1.3" alt="Zalo" style="width: 100%; height: 100%;">
    </a>
    <a href="<?php echo secure_redirect('facebook'); ?>" class="float-btn facebook" target="_blank" rel="noopener noreferrer" title="Chat Facebook" aria-label="Facebook">
        <img src="assets/images/facebook_chat_icon_circle.png?v=1.3" alt="Facebook Botchat" style="width: 100%; height: 100%;">
    </a>
    <button class="float-btn" onclick="window.scrollTo({top:0,behavior:'smooth'})" 
            style="background: var(--bg-card); color: var(--text-muted);" 
            title="Lên đầu trang" aria-label="Scroll to top" id="scrollTopBtn">
        <i class="fas fa-chevron-up" style="font-size: 0.85rem;"></i>
    </button>
</div>

<!-- ===================== MOBILE TAB BAR ===================== -->
<nav class="mobile-tab-bar" aria-label="Mobile Navigation">
    <a href="index.php"    class="tab-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <i class="fas fa-home"></i>
        <span>Trang Chủ</span>
    </a>
    <a href="products.php" class="tab-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
        <i class="fas fa-th-large"></i>
        <span>Sản Phẩm</span>
    </a>
    <a href="social.php"   class="tab-item <?php echo basename($_SERVER['PHP_SELF']) == 'social.php' ? 'active' : ''; ?>">
        <i class="fas fa-fire"></i>
        <span>MXH</span>
    </a>
    <a href="topup.php"    class="tab-item <?php echo basename($_SERVER['PHP_SELF']) == 'topup.php' ? 'active' : ''; ?>">
        <i class="fas fa-wallet"></i>
        <span>Nạp Tiền</span>
    </a>
    <a href="<?php echo secure_redirect('website'); ?>" target="_blank" class="tab-item">
        <i class="fas fa-globe"></i>
        <span>Website</span>
    </a>
    <a href="profile.php"  class="tab-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
        <i class="fas fa-user-circle"></i>
        <span>Tài Khoản</span>
    </a>
</nav>

<!-- ===================== FOOTER ===================== -->
<footer style="margin-top: 40px; border-top: 1px solid var(--border); padding: 40px 0 110px; position: relative;">
    <!-- Top gradient border -->
    <div style="position: absolute; top: 0; left: 0; right: 0; height: 1px; background: var(--rainbow-gradient); opacity: 0.3;"></div>
    
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1.2fr; gap: 50px; margin-bottom: 60px;">
            
            <!-- Brand Column -->
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <div style="width: 36px; height: 36px; background: var(--primary-gradient); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1rem; box-shadow: 0 4px 15px rgba(124,58,237,0.4);">
                        <?php echo strtoupper(substr($settings['site_logo'] ?? 'Q', 0, 1)); ?>
                    </div>
                    <span class="logo-text" style="font-size: 1.2rem; font-weight: 900;"><?php echo $settings['site_logo'] ?? 'QUYETDEV'; ?></span>
                </div>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; line-height: 1.8; max-width: 280px;">
                    Nền tảng cung cấp tài khoản số cao cấp, uy tín hàng đầu với chính sách bảo hành rõ ràng, xử lý tự động hoá 100%.
                </p>
                <div style="display: flex; gap: 10px;">
                    <a href="<?php echo $settings['facebook_link'] ?? '#'; ?>" target="_blank" 
                       style="width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: #1877F2; transition: var(--transition); text-decoration: none; font-size: 1.1rem;"
                       onmouseover="this.style.background='rgba(24,119,242,0.15)';this.style.borderColor='rgba(24,119,242,0.3)'"
                       onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border)'">
                        <i class="fab fa-facebook"></i>
                    </a>
                    <a href="<?php echo $settings['telegram_link'] ?? '#'; ?>" target="_blank"
                       style="width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: #26A5E4; transition: var(--transition); text-decoration: none; font-size: 1.1rem;"
                       onmouseover="this.style.background='rgba(38,165,228,0.15)';this.style.borderColor='rgba(38,165,228,0.3)'"
                       onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border)'">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <a href="<?php echo $settings['zalo_link'] ?? '#'; ?>" target="_blank"
                       style="width: 40px; height: 40px; border-radius: 10px; background: var(--bg-card); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: #0068FF; transition: var(--transition); text-decoration: none;"
                       onmouseover="this.style.background='rgba(0,104,255,0.15)';this.style.borderColor='rgba(0,104,255,0.3)'"
                       onmouseout="this.style.background='var(--bg-card)';this.style.borderColor='var(--border)'">
                        <svg style="width: 22px; height: 22px;" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M11.99 2C6.47 2 2 5.94 2 10.8c0 2.76 1.43 5.22 3.66 6.84l-.56 2.51c-.12.54.43.99.9.72l2.91-1.63c1.01.24 2.06.37 3.08.37 5.52 0 10-3.94 10-8.8S17.51 2 11.99 2z" fill="#0068FF"/>
                            <path d="M15.48 12.35l-1.12-1.12c-.15-.15-.39-.15-.54 0l-.31.31-.77-.77.31-.31c.15-.15.15-.39 0-.54l-1.12-1.12c-.15-.15-.39-.15-.54 0l-1.2 1.2c-.39.39-.39 1.02 0 1.41l2.5 2.5a.996.996 0 001.41 0l1.2-1.2c.15-.15.15-.39 0-.54z" fill="white"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Services Column -->
            <div>
                <h4 style="font-weight: 800; font-size: 0.9rem; margin-bottom: 20px; color: var(--text); text-transform: uppercase; letter-spacing: 0.5px;">Dịch Vụ</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
                    <?php 
                    $footerLinks = [
                        ['Trang Chủ', 'index.php'],
                        ['Sản Phẩm', 'products.php'],
                        ['Dịch Vụ MXH', 'social.php'],
                        ['Nạp Tiền', 'topup.php'],
                    ];
                    foreach ($footerLinks as $link):
                    ?>
                    <li>
                        <a href="<?php echo $link[1]; ?>" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; transition: var(--transition);"
                           onmouseover="this.style.color='var(--primary-2)';this.style.paddingLeft='5px'" 
                           onmouseout="this.style.color='var(--text-muted)';this.style.paddingLeft='0'">
                            <i class="fas fa-chevron-right" style="font-size: 0.6rem; margin-right: 6px; opacity: 0.4;"></i>
                            <?php echo $link[0]; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Policy Column -->
            <div>
                <h4 style="font-weight: 800; font-size: 0.9rem; margin-bottom: 20px; color: var(--text); text-transform: uppercase; letter-spacing: 0.5px;">Chính Sách</h4>
                <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px;">
                    <?php 
                    $policyLinks = [
                        ['Điều Khoản Dịch Vụ', '#'],
                        ['Bảo Mật Thông Tin', '#'],
                        ['Chế Độ Bảo Hành', '#'],
                        ['Hướng Dẫn Nạp Tiền', 'topup.php'],
                    ];
                    foreach ($policyLinks as $link):
                    ?>
                    <li>
                        <a href="<?php echo $link[1]; ?>" style="text-decoration: none; color: var(--text-muted); font-size: 0.85rem; font-weight: 600; transition: var(--transition);"
                           onmouseover="this.style.color='var(--primary-2)';this.style.paddingLeft='5px'" 
                           onmouseout="this.style.color='var(--text-muted)';this.style.paddingLeft='0'">
                            <i class="fas fa-chevron-right" style="font-size: 0.6rem; margin-right: 6px; opacity: 0.4;"></i>
                            <?php echo $link[0]; ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact Column -->
            <div>
                <h4 style="font-weight: 800; font-size: 0.9rem; margin-bottom: 20px; color: var(--text); text-transform: uppercase; letter-spacing: 0.5px;">Liên Hệ</h4>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: rgba(124,58,237,0.12); color: var(--primary-2); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; margin-top: 2px;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div>
                            <p style="font-size: 0.72rem; color: var(--text-dim); margin-bottom: 2px;">Người sáng lập</p>
                            <p style="font-weight: 700; font-size: 0.85rem;"><?php echo $settings['site_name'] ?? 'QUYETDEV'; ?></p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: rgba(16,185,129,0.12); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; margin-top: 2px;">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <p style="font-size: 0.72rem; color: var(--text-dim); margin-bottom: 2px;">Hotline / Zalo (24/7)</p>
                            <a href="tel:<?php echo $settings['contact_phone'] ?? ''; ?>" style="font-weight: 700; font-size: 0.85rem; text-decoration: none; color: var(--text);">
                                <?php echo $settings['contact_phone'] ?? '0123456789'; ?>
                            </a>
                        </div>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 9px; background: rgba(244,63,94,0.12); color: #f43f5e; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0; margin-top: 2px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <p style="font-size: 0.72rem; color: var(--text-dim); margin-bottom: 2px;">Email Hỗ Trợ</p>
                            <a href="mailto:<?php echo $settings['contact_email'] ?? ''; ?>" style="font-weight: 700; font-size: 0.82rem; text-decoration: none; color: var(--text);">
                                <?php echo $settings['contact_email'] ?? 'support@quyetdev.com'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div style="padding-top: 30px; border-top: 1px solid var(--border); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
            <p style="font-size: 0.8rem; color: var(--text-dim);">
                © 2026 <strong style="color: var(--text-muted);"><?php echo $settings['site_name'] ?? 'QUYETDEV'; ?></strong>. Bản quyền thuộc về <?php echo $settings['site_name'] ?? 'QUYETDEV'; ?>.
            </p>
            <div style="display: flex; align-items: center; gap: 20px;">
                <span style="font-size: 0.75rem; color: var(--text-dim); font-weight: 600;">
                    <i class="fas fa-shield-alt" style="color: #10b981; margin-right: 5px;"></i>
                    Thanh Toán Bảo Mật
                </span>
                <div style="display: flex; gap: 8px; align-items: center; opacity: 0.5;">
                    <i class="fab fa-cc-visa" style="font-size: 1.4rem;"></i>
                    <i class="fab fa-cc-mastercard" style="font-size: 1.4rem;"></i>
                    <i class="fas fa-mobile-alt" style="font-size: 1.1rem;"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ===================== TOAST ===================== -->
<?php if (isset($_SESSION['alert'])): ?>
    <div id="toast" class="toast" style="position: fixed; top: 90px; right: 20px; padding: 14px 22px; border-radius: var(--radius); z-index: 9999; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-lg); background: var(--bg-card); border: 1px solid var(--border); backdrop-filter: blur(16px); animation: slideIn 0.4s cubic-bezier(0.68,-0.55,0.265,1.55);">
        <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: <?php echo $_SESSION['alert']['type'] == 'success' ? 'rgba(16,185,129,0.15)' : 'rgba(244,63,94,0.15)'; ?>; color: <?php echo $_SESSION['alert']['type'] == 'success' ? '#10b981' : '#f43f5e'; ?>; flex-shrink: 0;">
            <i class="fas <?php echo $_SESSION['alert']['type'] == 'success' ? 'fa-check' : 'fa-times'; ?>"></i>
        </div>
        <div style="font-size: 0.88rem; font-weight: 600;"><?php echo $_SESSION['alert']['message']; ?></div>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; color: var(--text-dim); cursor: pointer; margin-left: 8px; font-size: 0.8rem;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <script>
        setTimeout(() => {
            const t = document.getElementById('toast');
            if (t) { t.style.opacity='0'; t.style.transform='translateX(120%)'; setTimeout(()=>t.remove(),400); }
        }, 4500);
    </script>
    <?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<style>
@keyframes slideIn {
    from { transform: translateX(120%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Scroll to top button visibility */
#scrollTopBtn { opacity: 0; transition: opacity 0.3s; }

/* Footer responsive */
@media (max-width: 992px) {
    footer > .container > div:first-child {
        grid-template-columns: 1fr 1fr !important;
        gap: 30px !important;
    }
}

@media (max-width: 576px) {
    footer > .container > div:first-child {
        grid-template-columns: 1fr !important;
        gap: 25px !important;
    }
}
</style>

<script>
// Scroll to top button
const qdevScrollBtn = document.getElementById('scrollTopBtn');
window.addEventListener('scroll', () => {
    if (qdevScrollBtn) {
        qdevScrollBtn.style.opacity = window.scrollY > 300 ? '1' : '0';
    }
});

// Main JS
if (typeof window.mainJsLoaded === 'undefined') {
    window.mainJsLoaded = true;

    // Number counter animation
    function animateCounter(el, target, suffix = '') {
        let current = 0;
        const step = Math.ceil(target / 60);
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('vi-VN') + suffix;
            if (current >= target) clearInterval(timer);
        }, 20);
    }

    // Intersection Observer for counters
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    const el = e.target;
                    animateCounter(el, parseInt(el.dataset.counter), el.dataset.suffix || '');
                    obs.unobserve(el);
                }
            });
        }, { threshold: 0.3 });
        counters.forEach(c => obs.observe(c));
    }
}
</script>

<!-- SweetAlert2 Premium Styling -->
<style>
    .qdev-swal-glass {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px) !important;
        -webkit-backdrop-filter: blur(20px) !important;
        border: 1px solid rgba(124, 58, 237, 0.2) !important;
        border-radius: 28px !important;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
    }
    .qdev-swal-title { color: #1e293b !important; font-weight: 900 !important; font-size: 1.5rem !important; }
    .qdev-swal-txt { color: #475569 !important; font-weight: 600 !important; line-height: 1.6 !important; }
    .qdev-swal-btn { 
        background: var(--primary-gradient) !important; 
        border-radius: 12px !important; 
        padding: 12px 30px !important; 
        font-weight: 800 !important; 
        box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3) !important;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
// Real-time Deposit Check
function qdevCheckDeposit() {
    <?php if (isLoggedIn()): ?>
    fetch('api/check_deposit.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Success Modal - Premium Design
                const amountFormatted = new Intl.NumberFormat('vi-VN').format(data.amount);
                
                // Fire Confetti!
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.6 },
                    colors: ['#7c3aed', '#10b981', '#f59e0b', '#3b82f6']
                });

                Swal.fire({
                    title: '🌟 NẠP TIỀN THÀNH CÔNG!',
                    html: `
                        <div style="margin: 20px 0;">
                            <p class="qdev-swal-txt">Tài khoản của bạn đã được cộng thêm</p>
                            <h2 style="color: #10b981; font-weight: 900; font-size: 2.2rem; margin: 10px 0;">+ ${amountFormatted} VNĐ</h2>
                            <p class="qdev-swal-txt">Cảm ơn bạn đã tin tưởng dịch vụ của chúng tôi!</p>
                        </div>
                    `,
                    iconHtml: '<img src="https://cdn-icons-png.flaticon.com/512/5290/5290058.png" style="width: 80px; height: 80px;">',
                    customClass: {
                        popup: 'qdev-swal-glass',
                        title: 'qdev-swal-title',
                        htmlContainer: 'qdev-swal-txt',
                        confirmButton: 'qdev-swal-btn'
                    },
                    confirmButtonText: 'NHẬN TIỀN NGAY 🚀',
                    buttonsStyling: false,
                    showClass: {
                        popup: 'animate__animated animate__zoomIn'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__zoomOut'
                    }
                }).then(() => {
                    // Refresh balance in header if it exists
                    const balanceEl = document.querySelector('.user-balance');
                    if (balanceEl) {
                        location.reload(); 
                    }
                });
            }
        })
        .catch(error => console.error('Deposit Check Status:', error));
    <?php endif; ?>
}

// Poll every 8 seconds
if (typeof window.depositInterval === 'undefined') {
    window.depositInterval = setInterval(qdevCheckDeposit, 8000);
}
</script>

<script>
// Website Redirect Logic - Security Enhanced
function qdevRedirectWebsite() {
    window.open('<?php echo secure_redirect('website'); ?>', '_blank');
}

// ===================== ANTI-DEVTOOLS & SECURITY (QUYETDEV Defense) =====================
(function() {
    // Disable Right Click
    document.addEventListener('contextmenu', e => e.preventDefault());

    // Disable Keyboard Shortcuts
    document.onkeydown = function(e) {
        // F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
        if (e.keyCode == 123 || 
            (e.ctrlKey && e.shiftKey && (e.keyCode == 73 || e.keyCode == 74)) || 
            (e.ctrlKey && e.keyCode == 85)) {
            
            Swal.fire({
                title: '🛡️ CẢNH BÁO BẢO MẬT',
                text: 'Hành động này đã bị vô hiệu hóa để bảo vệ nội dung website.',
                icon: 'warning',
                confirmButtonText: 'TÔI ĐÃ HIỂU',
                customClass: { popup: 'qdev-swal-glass' }
            });
            return false;
        }
    };

    // Detect DevTools console opening
    let devtools = function() {};
    devtools.toString = function() {
        Swal.fire({
            title: '🚫 PHÁT HIỆN TRUY CẬP TRÁI PHÉP',
            text: 'Vui lòng không can thiệp vào mã nguồn website!',
            icon: 'error',
            confirmButtonText: 'QUAY LẠI',
            customClass: { popup: 'qdev-swal-glass' }
        });
        return '';
    }
    // console.log('%c', devtools); // Potential infinite trigger, enable with caution
})();
</script>

<style>
    .swal-actions-gap { gap: 15px !important; margin-top: 10px !important; }
    /* Prevent text selection to protect content */
    body { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }
    /* Allow selection in inputs */
    input, textarea { -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
</style>

<script src="assets/js/main.js"></script>

</body>
</html>
