<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Add Customer';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Customer name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, address) VALUES (:name, :phone, :email, :address)");
            $stmt->execute(['name' => $name, 'phone' => $phone, 'email' => $email, 'address' => $address]);
            header("Location: index.php?msg=success");
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving customer: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Add New Customer</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Customers</a>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto; display: block; padding: 30px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        
        <div class="form-group">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3"></textarea>
        </div>
        
        <div style="margin-top: 30px;">
            <button type="submit" class="btn-submit" style="padding: 12px 25px; font-size: 15px;"><i class="fas fa-save"></i> Save Customer</button>
            <a href="index.php" style="margin-left: 15px; color: #666; text-decoration: none; font-weight: 600;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
