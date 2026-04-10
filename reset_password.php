<?php 
require_once 'includes/config.php'; 

if (isLoggedIn()) {
    redirect('index.php');
}

$token = isset($_GET['token']) ? clean($_GET['token']) : null;
$error = null;
$success = null;
$user = null;

if (!$token) {
    redirect('login.php');
}

// Check if token is valid and not expired
$now = date('Y-m-d H:i:s');
$user = $db->fetch("SELECT * FROM users WHERE reset_token = '$token' AND reset_expiry > '$now'");

if (!$user) {
    $error = "Liên kết khôi phục đã hết hạn hoặc không hợp lệ. Vui lòng yêu cầu lại.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không trùng khớp.";
    } else {
        // Update password and clear token
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $db->query("UPDATE users SET password = '$hashed_password', reset_token = NULL, reset_expiry = NULL WHERE id = " . $user['id']);
        
        $success = "Mật khẩu của bạn đã được cập nhật thành công. Đang chuyển hướng đến trang đăng nhập...";
        echo "<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>";
    }
}

include 'header.php'; 
?>

<main class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px;">
    <div class="glass-card spotlight" style="width: 100%; max-width: 480px; padding: 50px 40px; border-radius: 30px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin: 0 auto 20px;">
                <i class="fas fa-lock-open"></i>
            </div>
            <h2 class="text-gradient" style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px;">Đặt Lại Mật Khẩu</h2>
            <p style="font-size: 0.95rem; opacity: 0.6;">Thiết lập mật khẩu mới cho tài khoản của bạn</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <?php if (strpos($error, 'hết hạn') !== false): ?>
                    <br><a href="forgot_password.php" style="color: var(--primary); font-weight: 700; text-decoration: none; margin-top: 10px; display: inline-block;">Gửi lại yêu cầu</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($user && !$success): ?>
            <form action="" method="POST">
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Mật Khẩu Mới</label>
                    <div style="position: relative;">
                        <i class="far fa-key" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="password" name="password" required placeholder="Ít nhất 6 ký tự" 
                            style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Xác Nhận Mật Khẩu</label>
                    <div style="position: relative;">
                        <i class="far fa-check-double" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu mới" 
                            style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>

                <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 16px;">
                    <i class="fas fa-save"></i> Cập Nhật Mật Khẩu
                </button>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?>
