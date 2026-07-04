<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Manage Products';

// Fetch products with their category names
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.name ASC
");
$products = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Products</h2>
    <div>
        <a href="import.php" class="btn" style="background: #111c43; color: white; margin-right: 10px;"><i class="fas fa-file-csv"></i> Import CSV</a>
        <a href="add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
    </div>
</div>

<div class="card" style="display: block;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="badge badge-success" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">Product saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">Product deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'imported' && isset($_GET['count'])): ?>
        <div class="badge badge-primary" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">Successfully imported <?php echo (int)$_GET['count']; ?> products!</div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price (₹)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $prod): ?>
                        <tr>
                            <td>#<?php echo $prod['id']; ?></td>
                            <td style="font-weight: 600;">
                                <?php if($prod['image_url']): ?>
                                    <img src="../uploads/products/<?php echo htmlspecialchars($prod['image_url']); ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; vertical-align: middle; margin-right: 10px;">
                                <?php endif; ?>
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </td>
                            <td><span class="badge badge-primary"><?php echo htmlspecialchars($prod['category_name'] ?: 'None'); ?></span></td>
                            <td style="font-weight: 600;">₹<?php echo number_format($prod['selling_price'], 2); ?></td>
                            <td>
                                <?php if($prod['stock_quantity'] <= 5): ?>
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $prod['stock_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-success"><?php echo $prod['stock_quantity']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="view.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm" style="background: #e0e5f2; color: #2b3674; margin-right: 5px;"><i class="fas fa-eye"></i> View</a>
                                <a href="edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-primary" style="margin-right: 5px;"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 30px; text-align: center; color: #888;">No products found. Click 'Add Product' or 'Import CSV' to create one.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
