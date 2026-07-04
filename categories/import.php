<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Import Categories';

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
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $count = 0;
                while (($row = fgetcsv($file)) !== false) {
                    $name = isset($row[0]) ? trim($row[0]) : '';
                    $description = isset($row[1]) ? trim($row[1]) : '';
                    
                    if (!empty($name)) {
                        $stmt->execute([$name, $description]);
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
    <h2>Import Categories via CSV</h2>
    <a href="index.php" class="btn" style="background: #f4f7fe; color: #8f9bba; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: 600;"><i class="fas fa-arrow-left"></i> Back to Categories</a>
</div>

<div class="card" style="display: block; max-width: 600px; margin: 0 auto; padding: 20px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 12px; border-radius: 5px; margin-bottom: 20px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc;">
        <h4 style="margin-bottom: 10px; color: #333;">CSV Format Guide</h4>
        <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Please ensure your CSV file has the following columns in exact order (with header row):</p>
        <code style="display: block; background: #fff; padding: 10px; border-radius: 5px; font-size: 12px; color: #d63384; font-weight: bold;">Category Name, Description</code>
        <div style="margin-top: 15px; text-align: center;">
            <a href="sample_categories.csv" download class="btn btn-sm" style="background: #e0e5f2; color: #2b3674; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: 600;"><i class="fas fa-download"></i> Download Sample CSV</a>
        </div>
    </div>

    <form action="import.php" method="POST" enctype="multipart/form-data">
        <div style="margin-bottom: 15px;">
            <label for="csv_file" style="display: block; font-weight: 600; margin-bottom: 5px;">Upload CSV File *</label>
            <input type="file" name="csv_file" id="csv_file" accept=".csv" required style="width: 100%; padding: 10px; background: #f8f9fa; border: 2px dashed #0072ff; text-align: center; cursor: pointer; border-radius: 5px;">
        </div>

        <button type="submit" class="btn-submit" style="width: 100%; padding: 12px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;"><i class="fas fa-file-import"></i> Upload and Import Categories</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
