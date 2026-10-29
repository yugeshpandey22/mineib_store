<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    $reason = $_POST['reason'];
    $deduct_stock = isset($_POST['deduct_stock']) ? 1 : 0;

    if ($product_id && $quantity > 0) {
        try {
            $pdo->beginTransaction();

            // Insert into damaged products
            $stmt = $pdo->prepare("INSERT INTO damaged_products (product_id, quantity, reason) VALUES (?, ?, ?)");
            $stmt->execute([$product_id, $quantity, $reason]);

            // Deduct from stock if requested
            if ($deduct_stock) {
                $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
                $stmt->execute([$quantity, $product_id]);
            }

            $pdo->commit();
            header("Location: damaged.php?msg=success");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = 'Error adding entry: ' . $e->getMessage();
        }
    } else {
        $msg = "Please select a product and enter a valid quantity.";
    }
}

// Fetch all products for dropdown
$stmt = $pdo->query("SELECT id, name FROM products ORDER BY name ASC");
$products = $stmt->fetchAll();

// Fetch damaged products history
$stmt = $pdo->query("
    SELECT d.*, p.name as product_name 
    FROM damaged_products d 
    JOIN products p ON d.product_id = p.id 
    ORDER BY d.created_at DESC
");
$damaged_items = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2>Damaged / Returned Products</h2>
</div>

<?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
    <div class="badge badge-success" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;">Entry added successfully!</div>
<?php endif; ?>
<?php if ($msg): ?>
    <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    
    <!-- Add Form -->
    <div class="card" style="align-self: start;">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px;">Log Damaged/Returned Item</h3>
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Product</label>
                <select name="product_id" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
                    <option value="">Select a Product...</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Quantity</label>
                <input type="number" name="quantity" min="1" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Reason</label>
                <input type="text" name="reason" placeholder="e.g. Customer returned, Broken seal, Expired" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; cursor: pointer; font-size: 14px; color: #555;">
                    <input type="checkbox" name="deduct_stock" value="1" checked style="margin-right: 10px;"> 
                    Deduct this quantity from main stock
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-save"></i> Log Entry</button>
        </form>
    </div>

    <!-- History List -->
    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 18px;">History</h3>
        <div style="overflow-x: auto;">
            <table class="custom-table" style="width: 100%; text-align: left; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Date</th>
                        <th style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Product</th>
                        <th style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Qty</th>
                        <th style="border-bottom: 2px solid #eee; padding-bottom: 10px;">Reason</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($damaged_items) > 0): ?>
                        <?php foreach ($damaged_items as $item): ?>
                            <tr>
                                <td style="padding: 12px 0; border-bottom: 1px solid #f4f6f9; color: #8f9bba;">
                                    <?php echo date('d M Y, H:i', strtotime($item['created_at'])); ?>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #f4f6f9; font-weight: 600;">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #f4f6f9;">
                                    <span class="badge badge-danger"><?php echo $item['quantity']; ?></span>
                                </td>
                                <td style="padding: 12px 0; border-bottom: 1px solid #f4f6f9; color: #555;">
                                    <?php echo htmlspecialchars($item['reason']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: #8f9bba;">No damaged or returned items logged yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
