<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();



define('BASE_URL', '/storemanagement');
$page_title = 'Add User';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'cashier';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed, $role])) {
                header("Location: index.php?msg=success");
                exit;
            } else {
                $error = 'Failed to add user.';
            }
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2>Add New User</h2>
    <a href="index.php" class="btn" style="background: #f4f7fe; color: #8f9bba;"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

<div class="card" style="display: block; max-width: 500px; margin: 0 auto;">
    <?php if ($error): ?>
        <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add.php" method="POST">
        <div class="form-group">
            <label for="username" class="form-label">Username *</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password *</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-control">
                <option value="cashier">Cashier</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Create User</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
