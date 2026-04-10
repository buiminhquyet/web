<?php 
require_once 'includes/config.php'; 

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);

    // Check if email exists
    $user = $db->fetch("SELECT * FROM users WHERE email = '$email'");
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update user with token
        $db->query("UPDATE users SET reset_token = '$token', reset_expiry = '$expiry' WHERE id = " . $user['id']);
        
        // Send Email
        $reset_link = SITE_URL . "/reset_password.php?token=" . $token;
        $subject = "Khôi phục mật khẩu - " . (get_setting('site_name') ?? 'QUYETDEV Shop');
        
        $email_body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;'>
            <div style='background: linear-gradient(135deg, #6366f1, #a855f7); padding: 30px; text-align: center; color: white;'>
                <h2 style='margin: 0;'>KHÔI PHỤC MẬT KHẨU</h2>
                <p style='margin: 10px 0 0; opacity: 0.9;'>Đừng lo, chúng tôi ở đây để giúp bạn!</p>
            </div>
            <div style='padding: 30px; line-height: 1.6; color: #334155; text-align: center;'>
                <p>Chào <strong>{$user['username']}</strong>,</p>
                <p>Bạn nhận được email này vì chúng tôi nhận được yêu cầu khôi phục mật khẩu cho tài khoản của bạn.</p>
                <div style='margin: 30px 0;'>
                    <a href='{$reset_link}' style='background: #6366f1; color: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 800; display: inline-block; box-shadow: 0 10px 15px rgba(99, 102, 241, 0.3);'>ĐẶT LẠI MẬT KHẨU</a>
                </div>
                <p style='font-size: 0.85rem; color: #64748b;'>Đường dẫn này sẽ hết hạn trong vòng <strong>10 phút</strong>. Nếu bạn không yêu cầu điều này, vui lòng bỏ qua email này.</p>
                <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;'>
                <p style='font-size: 0.8rem; color: #94a3b8;'>Trân trọng,<br>Hệ thống " . (get_setting('site_name') ?? 'QUYETDEV') . "</p>
            </div>
        </div>";
        
        if (sendEmail($email, $subject, $email_body)) {
            $success = "Liên kết khôi phục đã được gửi tới email của bạn. Vui lòng kiểm tra hộp thư đến.";
        } else {
            $error = "Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.";
        }
    } else {
        // We show the same message for security reasons to prevent email discovery
        $success = "Nếu email tồn tại trong hệ thống, liên kết khôi phục sẽ được gửi tới hòm thư của bạn.";
    }
}

include 'header.php'; 
?>

<main class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px;">
    <div class="glass-card spotlight" style="width: 100%; max-width: 480px; padding: 50px 40px; border-radius: 30px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 60px; height: 60px; background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.5rem; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);">
                <i class="fas fa-key"></i>
            </div>
            <h2 class="text-gradient" style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px;">Quên Mật Khẩu?</h2>
            <p style="font-size: 0.95rem; opacity: 0.6;">Nhập email để nhận hướng dẫn khôi phục</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center;">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="forgot_password.php" method="POST">
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Email Tài Khoản</label>
                <div style="position: relative;">
                    <i class="far fa-envelope" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                    <input type="email" name="email" required placeholder="Nhập địa chỉ email của bạn" 
                        style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                        onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                </div>
            </div>

            <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 16px; margin-bottom: 25px;">
                <i class="fas fa-paper-plane"></i> Gửi Yêu Cầu
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 0.95rem; opacity: 0.7;">
            Nhớ lại mật khẩu? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">Đăng nhập</a>
        </p>
    </div>
</main>

<?php include 'footer.php'; ?>
