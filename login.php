<?php 
require_once 'includes/config.php'; 

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['otp_code'])) {
        // Handle 2FA Verification
        if (isset($_SESSION['2fa_pending_user'])) {
            $user_id = $_SESSION['2fa_pending_user'];
            $user = get_user($user_id);
            require_once 'includes/lib/TwoFactorAuth.php';
            $tfa = new TwoFactorAuth();
            
            if ($tfa->verifyCode($user['two_fa_secret'], $_POST['otp_code'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['balance'] = $user['balance'];
                unset($_SESSION['2fa_pending_user']);
                
                alert('success', 'Xác minh thành công! Chào mừng trở lại.');
                redirect('index.php');
            } else {
                $error = "Mã bảo mật 2FA không chính xác.";
                $show_2fa = true;
            }
        }
    } else {
        // Handle Regular Login
        $login = clean($_POST['login']);
        $password = $_POST['password'];

        $user = $db->fetch("SELECT * FROM users WHERE username = '$login' OR email = '$login'");
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['two_fa_enabled']) {
                $_SESSION['2fa_pending_user'] = $user['id'];
                $show_2fa = true;
                $msg_2fa = "Tài khoản của bạn đã được bảo vệ. Vui lòng nhập mã 6 số từ ứng dụng Authenticator.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['balance'] = $user['balance'];
                
                alert('success', 'Chào mừng trở lại, ' . $user['username'] . '!');
                redirect('index.php');
            }
        } else {
            $error = "Thông tin đăng nhập không chính xác.";
        }
    }
}

include 'header.php'; 
?>

<main class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px;">
    <div class="glass-card spotlight" style="width: 100%; max-width: 480px; padding: 50px 40px; border-radius: 30px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.5rem; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(92, 84, 229, 0.3);">Q</div>
            <h2 class="text-gradient" style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px;">Chào Mừng Trở Lại</h2>
            <p style="font-size: 0.95rem; opacity: 0.6;">Đăng nhập để trải nghiệm dịch vụ Premium</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center; display: flex; align-items: center; gap: 10px; justify-content: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <?php if (isset($show_2fa) && $show_2fa): ?>
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 15px; border-radius: 14px; margin-bottom: 25px; font-size: 0.85rem; line-height: 1.5;">
                        <i class="fas fa-shield-check"></i> <?php echo $msg_2fa ?? 'Vui lòng nhập mã bảo mật 2 lớp để tiếp tục.'; ?>
                    </div>
                    
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 15px; opacity: 0.7;">Mã xác thực 6 số</label>
                    <input type="text" name="otp_code" autofocus required maxlength="6" autocomplete="off"
                        placeholder="000000"
                        style="width: 100%; padding: 18px; border-radius: 16px; border: 2px solid var(--primary); background: rgba(92, 84, 229, 0.05); color: white; outline: none; text-align: center; font-size: 1.8rem; font-weight: 800; letter-spacing: 12px;">
                </div>

                <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 16px; margin-bottom: 15px;">
                    <i class="fas fa-lock-open"></i> Xác Minh & Đăng Nhập
                </button>
                <div style="text-align: center;">
                    <a href="login.php" style="font-size: 0.85rem; color: var(--text-dim); text-decoration: none;">Quay lại đăng nhập</a>
                </div>

            <?php else: ?>
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Username hoặc Email</label>
                    <div style="position: relative;">
                        <i class="far fa-user" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="text" name="login" required placeholder="Nhập tên đăng nhập hoặc email" 
                            style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Mật Khẩu</label>
                    <div style="position: relative;">
                        <i class="far fa-eye" id="togglePassword" style="position: absolute; right: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4; cursor: pointer; z-index: 10;"></i>
                        <i class="far fa-lock" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="password" name="password" id="password" required placeholder="Nhập mật khẩu của bạn" 
                            style="width: 100%; padding: 15px 50px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; margin-left: 5px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="remember" style="accent-color: var(--primary); cursor: pointer;">
                        <label for="remember" style="font-size: 0.85rem; opacity: 0.7; cursor: pointer;">Ghi nhớ</label>
                    </div>
                    <a href="forgot_password.php" style="font-size: 0.8rem; color: var(--primary); font-weight: 600; text-decoration: none; opacity: 0.8;">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 16px; margin-bottom: 25px;">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập Ngay
                </button>
            <?php endif; ?>
        </form>


        <p style="text-align: center; margin-top: 40px; font-size: 0.95rem; opacity: 0.7;">
            Bạn chưa có tài khoản? <a href="register.php" style="color: var(--primary); font-weight: 700; text-decoration: none; border-bottom: 2px solid rgba(92, 84, 229, 0.2); padding-bottom: 2px;">Tham gia ngay</a>
        </p>
    </div>
</main>

<script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function (e) {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

<?php include 'footer.php'; ?>
