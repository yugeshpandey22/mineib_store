<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Edit Supplier';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$error = '';

// Fetch existing supplier
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $company_name = trim($_POST['company_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Supplier name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE suppliers SET name = ?, company_name = ?, phone = ?, email = ?, address = ? WHERE id = ?");
            $stmt->execute([$name, $company_name, $phone, $email, $address, $id]);
            header("Location: index.php?msg=success");
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving supplier: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Edit Supplier</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Suppliers</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Supplier Name (Contact Person) *</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($supplier['name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="company_name" style="display: block; font-weight: 600; margin-bottom: 5px;">Company Name</label>
            <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($supplier['company_name']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="phone" style="display: block; font-weight: 600; margin-bottom: 5px;">Phone Number</label>
            <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($supplier['phone']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="email" style="display: block; font-weight: 600; margin-bottom: 5px;">Email Address</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($supplier['email']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="address" style="display: block; font-weight: 600; margin-bottom: 5px;">Address</label>
            <textarea name="address" id="address" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($supplier['address']); ?></textarea>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Update Supplier</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
