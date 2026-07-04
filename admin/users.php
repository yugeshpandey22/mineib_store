<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();
if($_SESSION['role'] !== 'admin') {
    die("Access Denied. Only Admins can view this page.");
}

define('BASE_URL', '/storemanagement');
$page_title = 'User Management';

// Fetch all users
$stmt = $pdo->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="panel-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="font-size: 18px; color: #111c43;">System Users</h3>
        <button class="btn btn-primary" onclick="alert('Add User form is not implemented yet in this version.')"><i class="fas fa-plus"></i> Add New User</button>
    </div>
    
    <table class="custom-table" style="box-shadow: none;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td style="font-weight: 600;">#<?php echo $u['id']; ?></td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 30px; height: 30px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                                <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                            </div>
                            <?php echo htmlspecialchars(ucfirst($u['username'])); ?>
                        </div>
                    </td>
                    <td>
                        <?php if($u['role'] === 'admin'): ?>
                            <span class="badge badge-success">Admin</span>
                        <?php else: ?>
                            <span class="badge badge-primary">Staff</span>
                        <?php endif; ?>
                    </td>
                    <td style="color: #666; font-size: 13px;"><?php echo date('d M Y, h:i A', strtotime($u['created_at'])); ?></td>
                    <td>
                        <div style="display: flex; gap: 5px;">
                            <button class="btn-icon" onclick="alert('Edit clicked')" title="Edit User"><i class="fas fa-edit" style="color: #0d6efd;"></i></button>
                            <?php if($u['id'] !== $_SESSION['user_id']): ?>
                                <button class="btn-icon" onclick="alert('Delete clicked')" title="Delete User"><i class="fas fa-trash" style="color: #ff4b4b;"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
