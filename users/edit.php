<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();



define('BASE_URL', '/storemanagement');
$page_title = 'Edit User';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$error = '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'cashier';

    if (empty($username)) {
        $error = 'Username is required.';
    } else {
        // Check if username exists for another user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()) {
            $error = 'Username already taken by another account.';
        } else {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $hashed, $role, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $role, $id]);
            }
            header("Location: index.php?msg=success");
            exit;
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2>Edit User</h2>
    <a href="index.php" class="btn" style="background: #f4f7fe; color: #8f9bba;"><i class="fas fa-arrow-left"></i> Back to Users</a>
</div>

<div class="card" style="display: block; max-width: 500px; margin: 0 auto;">
    <?php if ($error): ?>
        <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <div class="form-group">
            <label for="username" class="form-label">Username *</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="role" class="form-label">Role</label>
            <select name="role" id="role" class="form-control" <?php if($id == $_SESSION['user_id']) echo 'disabled title="You cannot change your own role"'; ?>>
                <option value="cashier" <?php echo ($user['role'] == 'cashier') ? 'selected' : ''; ?>>Cashier</option>
                <option value="admin" <?php echo ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
            <?php if($id == $_SESSION['user_id']): ?>
                <!-- Hidden input so the role is still submitted since disabled select doesn't send data -->
                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Update User</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
