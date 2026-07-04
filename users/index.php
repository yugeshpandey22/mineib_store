<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

// Only admin should manage users ideally, but we'll assume current user has access

define('BASE_URL', '/storemanagement');
$page_title = 'Manage Users';

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Staff / Users</h2>
    <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</a>
</div>

<div class="card" style="display: block;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="badge badge-success" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">User saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">User deleted successfully!</div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td>#<?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td>
                            <span class="badge <?php echo $u['role'] === 'admin' ? 'badge-primary' : 'badge-success'; ?>" style="text-transform: capitalize;">
                                <?php echo htmlspecialchars($u['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        <td>
                            <a href="edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-primary" style="margin-right: 5px;"><i class="fas fa-edit"></i> Edit</a>
                            <?php if ($u['id'] != $_SESSION['user_id']): // Can't delete self ?>
                                <a href="delete.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash"></i> Delete</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
