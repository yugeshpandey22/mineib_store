<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Inventory / Stock';

// Fetch products with their category names and stock
$stmt = $pdo->query("
    SELECT p.id, p.name, p.stock_quantity, p.selling_price, p.purchase_price, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY p.stock_quantity ASC
");
$inventory = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Inventory Status</h2>
    <a href="../purchases/add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; background: #0072ff;"><i class="fas fa-truck"></i> Restock via Purchase</a>
</div>

<div class="card" style="display: block;">
    <div style="margin-bottom: 20px;">
        <h3 style="color: #2b3674; margin-bottom: 5px;">Stock Alerts</h3>
        <p style="color: #666; font-size: 14px;">Products with 5 or fewer items are highlighted in red.</p>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1;">Product ID</th>
                <th style="padding: 12px; color: #a3aed1;">Product Name</th>
                <th style="padding: 12px; color: #a3aed1;">Category</th>
                <th style="padding: 12px; color: #a3aed1;">Purchase Cost</th>
                <th style="padding: 12px; color: #a3aed1;">Selling Price</th>
                <th style="padding: 12px; color: #a3aed1;">Current Stock</th>
                <th style="padding: 12px; color: #a3aed1;">Total Sold</th>
                <th style="padding: 12px; color: #a3aed1;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($inventory) > 0): ?>
                <?php foreach ($inventory as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">#<?php echo $item['id']; ?></td>
                        <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($item['category_name'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px;">₹<?php echo number_format($item['purchase_price'], 2); ?></td>
                        <td style="padding: 12px;">₹<?php echo number_format($item['selling_price'], 2); ?></td>
                        <td style="padding: 12px; font-size: 16px; font-weight: 700; color: <?php echo ($item['stock_quantity'] <= 5) ? '#ff4b4b' : '#05cd99'; ?>;">
                            <?php echo $item['stock_quantity']; ?>
                        </td>
                        <td style="padding: 12px; font-size: 15px; font-weight: 600; color: #0d6efd;">
                            <?php echo $item['total_sold']; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if($item['stock_quantity'] <= 0): ?>
                                <span style="background: #ffe5e5; color: #ff4b4b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Out of Stock</span>
                            <?php elseif($item['stock_quantity'] <= 5): ?>
                                <span style="background: #fff4e5; color: #ff9920; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Low Stock</span>
                            <?php else: ?>
                                <span style="background: #e6fcf5; color: #05cd99; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #888;">No products found in inventory.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
