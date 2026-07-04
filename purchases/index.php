<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Purchases History';

// Fetch purchases
$stmt = $pdo->query("
    SELECT p.*, s.company_name as supplier_name, u.username as admin_name
    FROM purchases p
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    LEFT JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
");
$purchases = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Purchases (Restock Inventory)</h2>
    <a href="add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none;"><i class="fas fa-plus"></i> New Purchase</a>
</div>

<div class="card" style="display: block;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div style="background: #05cd99; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">Purchase recorded and inventory updated successfully!</div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1;">Purchase ID</th>
                <th style="padding: 12px; color: #a3aed1;">Date</th>
                <th style="padding: 12px; color: #a3aed1;">Supplier</th>
                <th style="padding: 12px; color: #a3aed1;">Total Amount</th>
                <th style="padding: 12px; color: #a3aed1;">Added By</th>
                <th style="padding: 12px; color: #a3aed1;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($purchases) > 0): ?>
                <?php foreach ($purchases as $pur): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: 600;">#<?php echo $pur['id']; ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo date('d M Y, h:i A', strtotime($pur['created_at'])); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($pur['supplier_name'] ?: 'Unknown'); ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #0072ff;">₹<?php echo number_format($pur['total_amount'], 2); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($pur['admin_name']); ?></td>
                        <td style="padding: 12px;">
                            <a href="view.php?id=<?php echo $pur['id']; ?>" style="color: #05cd99; text-decoration: none; padding: 5px 10px; border: 1px solid #05cd99; border-radius: 4px; font-size: 13px;"><i class="fas fa-eye"></i> View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #888;">No purchases found. Click 'New Purchase' to restock items.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
