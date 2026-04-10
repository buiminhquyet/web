<?php 
include 'header.php'; 

// Handle Balance Update
if (isset($_POST['update_balance'])) {
    $uid = clean($_POST['user_id']);
    $amount = clean($_POST['amount']);
    $db->query("UPDATE users SET balance = '$amount' WHERE id = '$uid'");
    alert('success', 'Đã cập nhật số dư thành công!');
    redirect('admin/users.php');
}

// Fetch all users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px;">
    <div>
        <h3 style="font-weight: 800; font-size: 1.5rem; margin-bottom: 5px;">Quản Lý Thành Viên</h3>
        <p style="opacity: 0.6; font-size: 0.9rem;">Kiểm soát số dư và thông tin người dùng</p>
    </div>
    <div style="display: flex; gap: 15px;">
        <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.4;"></i>
            <input type="text" placeholder="Tìm kiếm thành viên..." style="padding: 12px 15px 12px 40px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-size: 0.9rem; width: 280px;">
        </div>
    </div>
</div>

<div class="glass-card" style="padding: 35px; border-radius: 24px;">
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; opacity: 0.5; font-size: 0.85rem; border-bottom: 1px solid var(--glass-border);">
                    <th style="padding: 15px 10px;">ID</th>
                    <th style="padding: 15px 10px;">Người dùng</th>
                    <th style="padding: 15px 10px;">Số dư</th>
                    <th style="padding: 15px 10px;">Vai trò</th>
                    <th style="padding: 15px 10px;">Ngày gia nhập</th>
                    <th style="padding: 15px 10px; text-align: right;">Điều chỉnh số dư</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u = $users->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid var(--glass-border); transition: 0.3s;" class="table-row">
                    <td style="padding: 20px 10px; font-weight: 800; opacity: 0.5;">#<?php echo $u['id']; ?></td>
                    <td style="padding: 20px 10px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; border-radius: 12px; background: var(--primary-gradient); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                            </div>
                            <div>
                                <div style="font-weight: 700; color: var(--text-light);"><?php echo htmlspecialchars($u['username']); ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.5;"><?php echo htmlspecialchars($u['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <div style="font-weight: 800; color: #ff0169; font-size: 1.1rem;"><?php echo format_currency($u['balance']); ?></div>
                    </td>
                    <td style="padding: 20px 10px;">
                        <?php if($u['role'] == 'admin'): ?>
                            <span class="badge" style="background: rgba(92, 84, 229, 0.1); color: var(--primary); font-size: 0.75rem; font-weight: 800;">ADMIN</span>
                        <?php else: ?>
                            <span class="badge" style="background: rgba(100, 116, 139, 0.1); color: #64748b; font-size: 0.75rem;">MEMBER</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 20px 10px; opacity: 0.6; font-size: 0.9rem;">
                        <?php echo date('d/m/Y', strtotime($u['created_at'])); ?>
                    </td>
                    <td style="padding: 20px 10px; text-align: right;">
                        <form action="users.php" method="POST" style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <div style="position: relative;">
                                <input type="number" name="amount" value="<?php echo (int)$u['balance']; ?>" style="width: 140px; padding: 10px 15px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass-bg); color: var(--text-light); outline: none; font-size: 0.9rem; font-weight: 700;">
                            </div>
                            <button type="submit" name="update_balance" class="action-btn" style="color: white; background: var(--primary-gradient); width: auto; padding: 0 15px; font-weight: 700; font-size: 0.8rem; height: 40px;" title="Cập nhật">Lưu</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.table-row:hover { background: rgba(92, 84, 229, 0.02); }
.action-btn {
    border: none;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: 0.3s;
}
.action-btn:hover { transform: scale(1.05); filter: brightness(1.1); }
</style>

</main>
</body>
</html>
