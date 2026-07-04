<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'View Customer';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch customer
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: index.php");
    exit;
}

// Fetch customer's billing history (orders)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC");
$stmt->execute([$id]);
$orders = $stmt->fetchAll();

if (!$customer) {
    header("Location: index.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Customer Details</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Customers</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <div style="margin-bottom: 20px;">
        <h3 style="color: #2b3674; margin-bottom: 10px;">ID #<?php echo htmlspecialchars($customer['id']); ?></h3>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Name:</strong> <?php echo htmlspecialchars($customer['name']); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Phone:</strong> <?php echo htmlspecialchars($customer['phone'] ?: 'N/A'); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Email:</strong> <?php echo htmlspecialchars($customer['email'] ?: 'N/A'); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Address:</strong> <?php echo nl2br(htmlspecialchars($customer['address'] ?: 'N/A')); ?></p>
        <p style="font-size: 16px; margin-bottom: 8px;"><strong>Joined On:</strong> <?php echo date('d M Y, h:i A', strtotime($customer['created_at'])); ?></p>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <a href="edit.php?id=<?php echo $customer['id']; ?>" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; margin-right: 10px;"><i class="fas fa-edit"></i> Edit Customer</a>
        <a href="delete.php?id=<?php echo $customer['id']; ?>" style="color: #ff4b4b; text-decoration: none; padding: 10px 20px; border: 1px solid #ff4b4b; border-radius: 5px; display: inline-block;" onclick="return confirm('Are you sure you want to delete this customer?');"><i class="fas fa-trash"></i> Delete</a>
    </div>
</div>

<div class="card" style="display: block; margin-top: 20px;">
    <h3 style="color: #2b3674; margin-bottom: 15px;"><i class="fas fa-file-invoice-dollar"></i> Billing History</h3>
    
    <div style="overflow-x: auto;">
        <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid #ddd; background: #fcfcfc;">
                    <th style="padding: 12px; color: #333; font-weight: 600;">Bill ID</th>
                    <th style="padding: 12px; color: #333; font-weight: 600;">Date</th>
                    <th style="padding: 12px; color: #333; font-weight: 600;">Amount (₹)</th>
                    <th style="padding: 12px; color: #333; font-weight: 600;">Payment Mode</th>
                    <th style="padding: 12px; color: #333; font-weight: 600;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px;">#<?php echo $order['id']; ?></td>
                            <td style="padding: 12px;"><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                            <td style="padding: 12px; font-weight: 600;">₹<?php echo number_format($order['grand_total'], 2); ?></td>
                            <td style="padding: 12px;">
                                <span style="background: <?php echo $order['payment_method'] == 'Online' ? '#e8f5e9' : '#fff3e0'; ?>; color: <?php echo $order['payment_method'] == 'Online' ? '#2e7d32' : '#ef6c00'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                    <?php echo htmlspecialchars($order['payment_method']); ?>
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <a href="../pos/receipt.php?id=<?php echo $order['id']; ?>" class="btn btn-sm" style="background: #e0e5f2; color: #2b3674; text-decoration: none; padding: 5px 10px; border-radius: 4px;" target="_blank"><i class="fas fa-eye"></i> View Bill</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="padding: 20px; text-align: center; color: #888;">No bills found for this customer.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
