<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'View Product';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch product with category name
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Product Details</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Products</a>
</div>

<div class="card" style="display: block; max-width: 800px;">
    <div style="display: flex; gap: 30px;">
        <div style="width: 250px;">
            <?php if($product['image_url']): ?>
                <img src="../uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="" style="width: 100%; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <?php else: ?>
                <div style="width: 100%; height: 250px; background: #f4f7fe; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #a3aed1;">
                    <i class="fas fa-image" style="font-size: 50px;"></i>
                </div>
            <?php endif; ?>
        </div>
        <div style="flex: 1;">
            <h3 style="color: #2b3674; margin-bottom: 5px; font-size: 24px;"><?php echo htmlspecialchars($product['name']); ?></h3>
            <span style="background: #f4f7fe; padding: 5px 12px; border-radius: 20px; font-size: 13px; color: #0072ff; display: inline-block; margin-bottom: 20px; font-weight: 600;">
                <?php echo htmlspecialchars($product['category_name'] ?: 'Uncategorized'); ?>
            </span>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div style="background: #fafafa; padding: 15px; border-radius: 8px;">
                    <p style="color: #8f9bba; font-size: 13px; margin-bottom: 5px;">Selling Price</p>
                    <p style="font-size: 20px; font-weight: 700; color: #05cd99;">₹<?php echo number_format($product['selling_price'], 2); ?></p>
                </div>
                <div style="background: #fafafa; padding: 15px; border-radius: 8px;">
                    <p style="color: #8f9bba; font-size: 13px; margin-bottom: 5px;">Current Stock</p>
                    <p style="font-size: 20px; font-weight: 700; color: <?php echo ($product['stock_quantity'] <= 5) ? '#ff4b4b' : '#2b3674'; ?>;">
                        <?php echo htmlspecialchars($product['stock_quantity']); ?> Units
                    </p>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <p style="font-size: 14px; color: #8f9bba; margin-bottom: 5px;">Purchase Price: <strong>₹<?php echo number_format($product['purchase_price'], 2); ?></strong></p>
                <p style="font-size: 14px; color: #8f9bba; margin-bottom: 5px;">Added On: <strong><?php echo date('d M Y', strtotime($product['created_at'])); ?></strong></p>
                <?php if($product['return_policy']): ?>
                <p style="font-size: 14px; color: #8f9bba; margin-bottom: 5px; margin-top: 5px;">Return Policy: <strong style="color: #ff9800;"><i class="fas fa-undo-alt"></i> <?php echo htmlspecialchars($product['return_policy']); ?></strong></p>
                <?php endif; ?>
            </div>
            
            <?php if($product['description']): ?>
            <div style="margin-bottom: 20px;">
                <h4 style="margin-bottom: 10px; color: #2b3674;">Description</h4>
                <p style="color: #666; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: right;">
        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; margin-right: 10px;"><i class="fas fa-edit"></i> Edit Product</a>
        <a href="delete.php?id=<?php echo $product['id']; ?>" style="color: #ff4b4b; text-decoration: none; padding: 10px 20px; border: 1px solid #ff4b4b; border-radius: 5px; display: inline-block;" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash"></i> Delete</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
