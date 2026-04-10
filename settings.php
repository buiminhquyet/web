<?php 
require_once 'includes/config.php'; 

if (!isLoggedIn()) {
    redirect('login.php');
}

$user = get_user($_SESSION['user_id']);
$msg = '';

if (isset($_POST['update_profile'])) {
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $new_password = $_POST['new_password'];
    
    // Simple validation
    if (empty($email)) {
        $msg = '<div class="alert alert-error">Email không được để trống.</div>';
    } else {
        $db->query("UPDATE users SET email = '$email' WHERE id = '{$user['id']}'");
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $db->query("UPDATE users SET password = '$hashed' WHERE id = '{$user['id']}'");
        }
        $msg = '<div class="alert alert-success">Cập nhật thông tin thành công!</div>';
        $user = get_user($user['id']); // Refresh data
    }
}

include 'header.php'; 
?>

<main class="container section-padding">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <h1 class="text-gradient" style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px;">Cài Đặt Tài Khoản</h1>
            <p style="opacity: 0.6; font-size: 1rem; max-width: 450px; margin: 0 auto; line-height: 1.7;">
                Cập nhật thông tin cá nhân và quản lý bảo mật tài khoản của bạn.
            </p>
        </div>

        <?php echo $msg; ?>

        <div class="glass-card spotlight" style="padding: 40px; border-radius: 30px;">
            <form method="POST" action="">
                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Tên đăng nhập</label>
                    <input type="text" value="<?php echo $user['username']; ?>" disabled style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(0,0,0,0.05); color: var(--text-dim); cursor: not-allowed;">
                    <small style="display: block; margin-top: 6px; opacity: 0.5;">Tên đăng nhập không thể thay đổi.</small>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Địa chỉ Email</label>
                    <input type="email" name="email" value="<?php echo $user['email']; ?>" style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: white; outline: none; transition: 0.3s;" onfocus="this.style.border='1px solid var(--primary)'" onblur="this.style.border='1px solid var(--glass-border)'">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; opacity: 0.7;">Mật khẩu mới (Để trống nếu không đổi)</label>
                    <input type="password" name="new_password" placeholder="Nhập mật khẩu mới" style="width: 100%; padding: 15px; border-radius: 12px; border: 1px solid var(--glass-border); background: white; outline: none; transition: 0.3s;" onfocus="this.style.border='1px solid var(--primary)'" onblur="this.style.border='1px solid var(--glass-border)'">
                </div>

                <div style="margin-top: 40px;">
                    <button type="submit" name="update_profile" class="btn-premium" style="width: 100%; padding: 15px; border-radius: 14px; font-weight: 800;">
                        Lưu Thay Đổi
                    </button>
                </div>
            </form>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="profile.php" class="btn-ghost"><i class="fas fa-chevron-left"></i> Quay lại trang cá nhân</a>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
