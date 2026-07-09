<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'New Purchase';

// Fetch suppliers and products
$suppliers = $pdo->query("SELECT id, company_name FROM suppliers ORDER BY company_name ASC")->fetchAll();
$products = $pdo->query("SELECT id, name, purchase_price FROM products ORDER BY name ASC")->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplier_id = $_POST['supplier_id'] ?? null;
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $unit_cost = $_POST['unit_cost'] ?? 0;
    $user_id = $_SESSION['user_id'];

    if (empty($supplier_id) || empty($product_id) || $quantity <= 0 || $unit_cost < 0) {
        $error = 'Please fill all required fields correctly.';
    } else {
        try {
            $pdo->beginTransaction();
            
            $total_amount = $quantity * $unit_cost;

            // Create Purchase
            $stmt = $pdo->prepare("INSERT INTO purchases (supplier_id, user_id, total_amount) VALUES (?, ?, ?)");
            $stmt->execute([$supplier_id, $user_id, $total_amount]);
            $purchase_id = $pdo->lastInsertId();
            // Insert Purchase Item
            $stmt = $pdo->prepare("INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$purchase_id, $product_id, $quantity, $unit_cost, $total_amount]);
            // UPDATE INVENTORY
            $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ?, purchase_price = ? WHERE id = ?");
            $stmt->execute([$quantity, $unit_cost, $product_id]);

            $pdo->commit();
            header("Location: index.php?msg=success");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Error processing purchase: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Add New Purchase (Restock)</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Purchases</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="supplier_id" style="display: block; font-weight: 600; margin-bottom: 5px;">Supplier *</label>
            <select name="supplier_id" id="supplier_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?php echo $sup['id']; ?>"><?php echo htmlspecialchars($sup['company_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="product_id" style="display: block; font-weight: 600; margin-bottom: 5px;">Product to Restock *</label>
            <select name="product_id" id="product_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="">Select Product</option>
                <?php foreach ($products as $prod): ?>
                    <option value="<?php echo $prod['id']; ?>"><?php echo htmlspecialchars($prod['name']); ?> (Current Cost: ₹<?php echo $prod['purchase_price']; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label for="quantity" style="display: block; font-weight: 600; margin-bottom: 5px;">Quantity Received *</label>
                <input type="number" name="quantity" id="quantity" min="1" value="1" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="unit_cost" style="display: block; font-weight: 600; margin-bottom: 5px;">Unit Cost (₹) *</label>
                <input type="number" step="0.01" name="unit_cost" id="unit_cost" min="0" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; width: 100%;">Record Purchase & Update Stock</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
