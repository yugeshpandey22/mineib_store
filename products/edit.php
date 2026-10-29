<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Edit Product';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch categories for dropdown
$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $purchase_price = $_POST['purchase_price'] ?? 0;
    $selling_price = $_POST['selling_price'] ?? 0;
    $stock_quantity = $_POST['stock_quantity'] ?? 0;
    $return_policy = trim($_POST['return_policy'] ?? '');
    $barcode = trim($_POST['barcode'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $size = trim($_POST['size'] ?? '');
    
    $image_url = $product['image_url'];

    if (empty($name) || empty($category_id) || empty($selling_price)) {
        $error = 'Name, Category, and Selling Price are required.';
    } else {
        // Handle Image Upload if a new one is selected
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/products/' . $new_filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if it exists
                    if ($image_url && file_exists('../uploads/products/' . $image_url)) {
                        unlink('../uploads/products/' . $image_url);
                    }
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
                $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, color = ?, size = ?, barcode = ?, description = ?, return_policy = ?, purchase_price = ?, selling_price = ?, stock_quantity = ?, image_url = ? WHERE id = ?");
                $stmt->execute([$category_id, $name, $color, $size, $barcode, $description, $return_policy, $purchase_price, $selling_price, $stock_quantity, $image_url, $id]);
                header("Location: index.php?msg=updated");
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
    <h2>Edit Product</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Products</a>
</div>

<div class="card" style="display: block; max-width: 800px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Product Name *</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="barcode" style="display: block; font-weight: 600; margin-bottom: 5px;">Barcode</label>
                <input type="text" name="barcode" id="barcode" value="<?php echo htmlspecialchars($product['barcode'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="category_id" style="display: block; font-weight: 600; margin-bottom: 5px;">Category *</label>
                <select name="category_id" id="category_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label for="purchase_price" style="display: block; font-weight: 600; margin-bottom: 5px;">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" id="purchase_price" value="<?php echo htmlspecialchars($product['purchase_price']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="selling_price" style="display: block; font-weight: 600; margin-bottom: 5px;">Selling Price *</label>
                <input type="number" step="0.01" name="selling_price" id="selling_price" value="<?php echo htmlspecialchars($product['selling_price']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="stock_quantity" style="display: block; font-weight: 600; margin-bottom: 5px;">Stock</label>
                <input type="number" name="stock_quantity" id="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
            <div>
                <label for="color" style="display: block; font-weight: 600; margin-bottom: 5px;">Color (Optional)</label>
                <input type="text" name="color" id="color" value="<?php echo htmlspecialchars($product['color'] ?? ''); ?>" placeholder="e.g. Red, Black, Blue" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div>
                <label for="size" style="display: block; font-weight: 600; margin-bottom: 5px;">Size (Optional)</label>
                <input type="text" name="size" id="size" value="<?php echo htmlspecialchars($product['size'] ?? ''); ?>" placeholder="e.g. S, M, L, XL, 42" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="image" style="display: block; font-weight: 600; margin-bottom: 5px;">Update Product Image (Leave empty to keep current)</label>
            <?php if($product['image_url']): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../uploads/products/<?php echo htmlspecialchars($product['image_url']); ?>" alt="" style="width: 80px; border-radius: 5px; border: 1px solid #ddd;">
                </div>
            <?php endif; ?>
            <input type="file" name="image" id="image" accept="image/*" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px;">Product Description</label>
            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="return_policy" style="display: block; font-weight: 600; margin-bottom: 5px;">Return & Refund Policy</label>
            <input type="text" name="return_policy" id="return_policy" value="<?php echo htmlspecialchars($product['return_policy'] ?? ''); ?>" placeholder="e.g. 7 Days Replacement, No Return" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Update Product</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
