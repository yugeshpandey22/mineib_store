<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'View Supplier';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch supplier
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header("Location: index.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Supplier Details</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Suppliers</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <div style="margin-bottom: 20px;">
        <h3 style="color: #2b3674; margin-bottom: 10px;"><?php echo htmlspecialchars($supplier['company_name']); ?></h3>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Contact Person:</strong> <?php echo htmlspecialchars($supplier['name']); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Phone:</strong> <?php echo htmlspecialchars($supplier['phone'] ?: 'N/A'); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Email:</strong> <?php echo htmlspecialchars($supplier['email'] ?: 'N/A'); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($supplier['address'] ?: 'N/A')); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Added On:</strong> <?php echo date('d M Y, h:i A', strtotime($supplier['created_at'])); ?></p>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <a href="edit.php?id=<?php echo $supplier['id']; ?>" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; margin-right: 10px;"><i class="fas fa-edit"></i> Edit Supplier</a>
        <a href="delete.php?id=<?php echo $supplier['id']; ?>" style="color: #ff4b4b; text-decoration: none; padding: 10px 20px; border: 1px solid #ff4b4b; border-radius: 5px; display: inline-block;" onclick="return confirm('Are you sure you want to delete this supplier?');"><i class="fas fa-trash"></i> Delete</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
