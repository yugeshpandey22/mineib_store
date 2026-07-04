<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Sales Reports';

// Basic Stats
$today = date('Y-m-d');
$today_sales = $pdo->query("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn() ?: 0;
$total_sales = $pdo->query("SELECT SUM(grand_total) FROM orders")->fetchColumn() ?: 0;
$total_orders = $pdo->query("SELECT COUNT(id) FROM orders")->fetchColumn() ?: 0;
$total_products_sold = $pdo->query("SELECT SUM(quantity) FROM order_items")->fetchColumn() ?: 0;

// Fetch Recent 10 Orders
$stmt = $pdo->query("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    ORDER BY o.created_at DESC LIMIT 10
");
$recent_orders = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Sales Reports & Overview</h2>
</div>

<div class="amadika-cards">
    <div class="amadika-card">
        <div class="ac-icon bg-c-blue"><i class="fas fa-calendar-day"></i></div>
        <div class="ac-details">
            <div class="ac-title">Today's Sales</div>
            <div class="ac-value">₹ <?php echo number_format($today_sales, 2); ?></div>
        </div>
    </div>
    <div class="amadika-card">
        <div class="ac-icon bg-c-green"><i class="fas fa-chart-line"></i></div>
        <div class="ac-details">
            <div class="ac-title">Total Revenue</div>
            <div class="ac-value">₹ <?php echo number_format($total_sales, 2); ?></div>
        </div>
    </div>
    <div class="amadika-card">
        <div class="ac-icon bg-c-orange"><i class="fas fa-shopping-cart"></i></div>
        <div class="ac-details">
            <div class="ac-title">Total Orders</div>
            <div class="ac-value"><?php echo $total_orders; ?></div>
        </div>
    </div>
    <div class="amadika-card">
        <div class="ac-icon bg-c-purple"><i class="fas fa-box-open"></i></div>
        <div class="ac-details">
            <div class="ac-title">Items Sold</div>
            <div class="ac-value"><?php echo $total_products_sold; ?></div>
        </div>
    </div>
</div>

<div class="panel-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="font-size: 16px; color: #111c43;">Recent 10 Sales</h3>
    </div>
    <table class="custom-table" style="box-shadow: none;">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date & Time</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($recent_orders) > 0): ?>
                <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td style="font-weight: 600;">#ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 25px; height: 25px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <?php echo strtoupper(substr($order['customer_name'] ?: 'W', 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($order['customer_name'] ?: 'Walk-in'); ?>
                            </div>
                        </td>
                        <td style="font-weight: 700; color: #05cd99;">₹ <?php echo number_format($order['grand_total'], 2); ?></td>
                        <td>
                            <a href="../pos/receipt.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn-icon" style="text-decoration: none;" title="View Receipt"><i class="fas fa-file-invoice"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #888;">No sales data available.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
