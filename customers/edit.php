<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Edit Customer';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$error = '';

// Fetch existing customer
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Customer name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $email, $address, $id]);
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
    <h2>Edit Customer</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Customers</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Customer Name *</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="phone" style="display: block; font-weight: 600; margin-bottom: 5px;">Phone Number</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; font-weight: 600; margin-bottom: 5px;">Email Address</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="address" style="display: block; font-weight: 600; margin-bottom: 5px;">Address</label>
            <textarea name="address" id="address" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($customer['address']); ?></textarea>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Update Customer</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
