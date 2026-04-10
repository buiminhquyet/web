<?php 
require_once 'includes/config.php'; 

if (isLoggedIn()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp.";
    } elseif (strlen($password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } else {
        $check = $db->fetch("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($check) {
            $error = "Tên đăng nhập hoặc Email đã tồn tại.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Affiliate Logic: Check for referrer
            $ref_by = "NULL";
            if (!empty($_COOKIE['ref_user'])) {
                $ref_name = clean($_COOKIE['ref_user']);
                $referrer = $db->fetch("SELECT id FROM users WHERE username = '$ref_name'");
                if ($referrer) {
                    $ref_by = $referrer['id'];
                }
            }

            $db->query("INSERT INTO users (username, email, password, ref_by) VALUES ('$username', '$email', '$hashed_password', $ref_by)");
            alert('success', 'Đăng ký thành công! Vui lòng đăng nhập.');
            redirect('login.php');
        }
    }
}

include 'header.php'; 
?>

<main class="container" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px;">
    <div class="glass-card spotlight" style="width: 100%; max-width: 520px; padding: 50px 40px; border-radius: 30px;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="width: 60px; height: 60px; background: var(--primary-gradient); border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 1.5rem; margin: 0 auto 20px; box-shadow: 0 10px 20px rgba(92, 84, 229, 0.3);">Q</div>
            <h2 class="text-gradient" style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px;">Tham Gia Cùng Chúng Tôi</h2>
            <p style="font-size: 0.95rem; opacity: 0.6;">Tạo tài khoản để sở hữu kho tài khoản Premium</p>
        </div>

        <?php if (isset($error)): ?>
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.1); padding: 15px; border-radius: 14px; margin-bottom: 30px; font-size: 0.9rem; text-align: center; display: flex; align-items: center; gap: 10px; justify-content: center;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Username</label>
                    <div style="position: relative;">
                        <i class="far fa-user" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="text" name="username" required placeholder="Tên đăng nhập" 
                            style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Email</label>
                    <div style="position: relative;">
                        <i class="far fa-envelope" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                        <input type="email" name="email" required placeholder="Địa chỉ email" 
                            style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                            onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                            onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Mật Khẩu</label>
                <div style="position: relative;">
                    <i class="far fa-lock" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                    <input type="password" name="password" required placeholder="Mật khẩu ít nhất 6 ký tự" 
                        style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                        onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 700; margin-bottom: 10px; opacity: 0.7; margin-left: 5px;">Xác Nhận Mật Khẩu</label>
                <div style="position: relative;">
                    <i class="far fa-check-circle" style="position: absolute; left: 18px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
                    <input type="password" name="confirm_password" required placeholder="Nhập lại mật khẩu" 
                        style="width: 100%; padding: 15px 15px 15px 50px; border-radius: 16px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; transition: 0.3s; font-size: 0.95rem;"
                        onfocus="this.style.borderColor='var(--primary)'; this.style.boxShadow='0 0 0 4px rgba(92, 84, 229, 0.1)'"
                        onblur="this.style.borderColor='var(--glass-border)'; this.style.boxShadow='none'">
                </div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 30px; margin-left: 5px;">
                <input type="checkbox" id="terms" required style="accent-color: var(--primary); cursor: pointer; margin-top: 4px;">
                <label for="terms" style="font-size: 0.85rem; opacity: 0.7; cursor: pointer; line-height: 1.4;">
                    Tôi đồng ý với <a href="terms.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Điều khoản dịch vụ</a> và <a href="privacy.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Chính sách bảo mật</a>.
                </label>
            </div>

            <button type="submit" class="btn-premium" style="width: 100%; padding: 16px; font-size: 1rem; border-radius: 16px; margin-bottom: 25px;">
                <i class="fas fa-user-plus"></i> Tạo Tài Khoản Ngay
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 0.95rem; opacity: 0.7;">
            Bạn đã có tài khoản? <a href="login.php" style="color: var(--primary); font-weight: 700; text-decoration: none; border-bottom: 2px solid rgba(92, 84, 229, 0.2); padding-bottom: 2px;">Đăng nhập ngay</a>
        </p>
    </div>
</main>


<?php include 'footer.php'; ?>
