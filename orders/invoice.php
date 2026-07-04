<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Invoices';

// Fetch orders with customer and user info
$stmt = $pdo->query("
    SELECT o.*, c.name as customer_name, c.email as customer_email, u.username as cashier_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$invoices = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Tax Invoices</h2>
</div>

<div class="card" style="display: block;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1;">Invoice No.</th>
                <th style="padding: 12px; color: #a3aed1;">Date</th>
                <th style="padding: 12px; color: #a3aed1;">Customer</th>
                <th style="padding: 12px; color: #a3aed1;">Amount</th>
                <th style="padding: 12px; color: #a3aed1;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($invoices) > 0): ?>
                <?php foreach ($invoices as $inv): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: 600;">INV-<?php echo str_pad($inv['id'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo date('d M Y', strtotime($inv['created_at'])); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($inv['customer_name'] ?: 'Walk-in Customer'); ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #05cd99;">₹<?php echo number_format($inv['grand_total'], 2); ?></td>
                        <td style="padding: 12px;">
                            <a href="invoice_print.php?id=<?php echo $inv['id']; ?>" target="_blank" style="color: #0072ff; text-decoration: none; padding: 5px 10px; border: 1px solid #0072ff; border-radius: 4px; font-size: 13px;"><i class="fas fa-print"></i> Print A4 Invoice</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #888;">No invoices generated yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
