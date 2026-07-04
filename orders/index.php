<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Sales Orders';

// Fetch orders with customer and user info
$stmt = $pdo->query("
    SELECT o.*, c.name as customer_name, u.username as cashier_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Sales History (Orders)</h2>
    <a href="../pos/billing.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none;"><i class="fas fa-plus"></i> New Order (POS)</a>
</div>

<div class="card" style="display: block;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1;">Order ID</th>
                <th style="padding: 12px; color: #a3aed1;">Date & Time</th>
                <th style="padding: 12px; color: #a3aed1;">Customer</th>
                <th style="padding: 12px; color: #a3aed1;">Cashier</th>
                <th style="padding: 12px; color: #a3aed1;">Total Amount</th>
                <th style="padding: 12px; color: #a3aed1;">Payment</th>
                <th style="padding: 12px; color: #a3aed1;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($orders) > 0): ?>
                <?php foreach ($orders as $order): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px; font-weight: 600;">#<?php echo $order['id']; ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($order['customer_name'] ?: 'Walk-in'); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($order['cashier_name']); ?></td>
                        <td style="padding: 12px; font-weight: 700; color: #05cd99;">₹<?php echo number_format($order['grand_total'], 2); ?></td>
                        <td style="padding: 12px;"><span style="background: #f4f7fe; color: #0072ff; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;"><?php echo htmlspecialchars($order['payment_method']); ?></span></td>
                        <td style="padding: 12px;">
                            <a href="../pos/receipt.php?id=<?php echo $order['id']; ?>" target="_blank" style="color: #0072ff; text-decoration: none;"><i class="fas fa-file-invoice"></i> View Receipt</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="padding: 20px; text-align: center; color: #888;">No orders found yet.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
