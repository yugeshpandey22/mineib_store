<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Import Products';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $filename = $_FILES['csv_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if ($ext === 'csv') {
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            $header = fgetcsv($file); // skip header
            
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, purchase_price, selling_price, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
                $count = 0;
                while (($row = fgetcsv($file)) !== false) {
                    // Expected columns: Product Name, Category, Price, Stock
                    $name = isset($row[0]) ? trim($row[0]) : '';
                    $category_name = isset($row[1]) ? trim($row[1]) : '';
                    $selling_price = isset($row[2]) ? (float)$row[2] : 0;
                    $stock_quantity = isset($row[3]) ? (int)$row[3] : 0;
                    
                    if (!empty($name) && $selling_price > 0) {
                        $category_id = null;
                        
                        if (!empty($category_name)) {
                            // check if category exists
                            $cstmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
                            $cstmt->execute([$category_name]);
                            $cat = $cstmt->fetch();
                            
                            if ($cat) {
                                $category_id = $cat['id'];
                            } else {
                                // create category
                                $istmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, '')");
                                $istmt->execute([$category_name]);
                                $category_id = $pdo->lastInsertId();
                            }
                        }

                        $stmt->execute([$category_id, $name, '', 0, $selling_price, $stock_quantity]);
                        $count++;
                    }
                }
                $pdo->commit();
                fclose($file);
                header("Location: index.php?msg=imported&count=" . $count);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Error importing data: " . $e->getMessage();
            }
        } else {
            $error = 'Please upload a valid CSV file.';
        }
    } else {
        $error = 'Please select a file to upload.';
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <h2>Import Products via CSV</h2>
    <a href="index.php" class="btn" style="background: #f4f7fe; color: #8f9bba;"><i class="fas fa-arrow-left"></i> Back to Products</a>
</div>

<div class="card" style="display: block; max-width: 600px; margin: 0 auto;">
    <?php if ($error): ?>
        <div class="badge badge-danger" style="display: block; font-size: 14px; padding: 12px; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
        <h4 style="margin-bottom: 10px; color: #333;">CSV Format Guide</h4>
        <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Please ensure your CSV file has the following columns in exact order (with header row):</p>
        <code style="display: block; background: #fff; padding: 10px; border-radius: 5px; font-size: 12px; color: #d63384; font-weight: bold;">Product Name, Category, Price, Stock</code>
        <div style="margin-top: 15px; text-align: center;">
            <a href="sample_import.csv" download class="btn btn-sm" style="background: #e0e5f2; color: #2b3674;"><i class="fas fa-download"></i> Download Sample CSV</a>
        </div>
    </div>

    <form action="import.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="csv_file" class="form-label">Upload CSV File *</label>
            <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required style="padding: 10px; background: #f8f9fa; border: 2px dashed #0072ff; text-align: center; cursor: pointer;">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;"><i class="fas fa-file-import"></i> Upload and Import Products</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
