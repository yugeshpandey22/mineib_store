<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch purchase
$stmt = $pdo->prepare("
    SELECT p.*, s.company_name, s.name as contact_person, s.phone as supplier_phone, u.username as admin_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN users u ON p.user_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$purchase = $stmt->fetch();

if (!$purchase) {
    header("Location: index.php");
    exit;
}

// Fetch items
$stmt = $pdo->prepare("
    SELECT pi.*, pr.name as product_name
    FROM purchase_items pi
    JOIN products pr ON pi.product_id = pr.id
    WHERE pi.purchase_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

define('BASE_URL', '/storemanagement');
$page_title = 'View Purchase';
require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Purchase Details #<?php echo $purchase['id']; ?></h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Purchases</a>
</div>

<div class="card" style="display: block; max-width: 800px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
        <div>
            <h3 style="color: #2b3674; margin-bottom: 10px;">Supplier Info</h3>
            <p><strong>Company:</strong> <?php echo htmlspecialchars($purchase['company_name']); ?></p>
            <p><strong>Contact:</strong> <?php echo htmlspecialchars($purchase['contact_person']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($purchase['supplier_phone']); ?></p>
        </div>
        <div style="text-align: right;">
            <h3 style="color: #2b3674; margin-bottom: 10px;">Purchase Info</h3>
            <p><strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($purchase['created_at'])); ?></p>
            <p><strong>Added By:</strong> <?php echo htmlspecialchars($purchase['admin_name']); ?></p>
            <p><strong>Status:</strong> <span style="background: #e6fcf5; color: #05cd99; padding: 2px 8px; border-radius: 4px;"><?php echo htmlspecialchars($purchase['payment_status']); ?></span></p>
        </div>
    </div>

    <h3 style="color: #2b3674; margin-bottom: 15px;">Items Received</h3>
    <table style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 20px;">
        <thead>
            <tr style="background: #f4f7fe;">
                <th style="padding: 10px; border: 1px solid #eee;">Product</th>
                <th style="padding: 10px; border: 1px solid #eee; text-align: center;">Qty</th>
                <th style="padding: 10px; border: 1px solid #eee; text-align: right;">Unit Cost</th>
                <th style="padding: 10px; border: 1px solid #eee; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td style="padding: 10px; border: 1px solid #eee;"><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td style="padding: 10px; border: 1px solid #eee; text-align: center;"><?php echo $item['quantity']; ?></td>
                <td style="padding: 10px; border: 1px solid #eee; text-align: right;">₹<?php echo number_format($item['unit_cost'], 2); ?></td>
                <td style="padding: 10px; border: 1px solid #eee; text-align: right;">₹<?php echo number_format($item['subtotal'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" style="padding: 10px; border: 1px solid #eee; text-align: right;">Grand Total:</th>
                <th style="padding: 10px; border: 1px solid #eee; text-align: right; color: #0072ff; font-size: 18px;">₹<?php echo number_format($purchase['total_amount'], 2); ?></th>
            </tr>
        </tfoot>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
