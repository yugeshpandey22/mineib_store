<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Add Product';

// Fetch categories for dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $selling_price = $_POST['selling_price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    
    $image_url = null;

    if (empty($name) || empty($category_id) || empty($selling_price)) {
        $error = 'Name, Category, and Selling Price are required.';
    } else {
        // Handle Image Upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/products/' . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_url = $new_filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid image format. Only JPG, PNG, GIF, WEBP allowed.';
            }
        }

        if (!$error) {
            try {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, purchase_price, selling_price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$category_id, $name, $description, $purchase_price, $selling_price, $stock_quantity, $image_url]);
                header("Location: index.php?msg=success");
                exit;
            } catch (PDOException $e) {
                $error = 'Error saving product: ' . $e->getMessage();
            }
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Add New Product</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Products</a>
</div>

<div class="card" style="display: block; max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add.php" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Product Name *</label>
                <input type="text" name="name" id="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="category_id" style="display: block; font-weight: 600; margin-bottom: 5px;">Category *</label>
                <select name="category_id" id="category_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label for="purchase_price" style="display: block; font-weight: 600; margin-bottom: 5px;">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="selling_price" style="display: block; font-weight: 600; margin-bottom: 5px;">Selling Price *</label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="stock_quantity" style="display: block; font-weight: 600; margin-bottom: 5px;">Initial Stock</label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="image" style="display: block; font-weight: 600; margin-bottom: 5px;">Product Image</label>
            <input type="file" name="image" id="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px;">Product Description</label>
            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Save Product</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
