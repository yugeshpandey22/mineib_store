<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Add Category';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (:name, :description)");
            $stmt->execute(['name' => $name, 'description' => $description]);
            header("Location: index.php?msg=success");
            exit;
        } catch (PDOException $e) {
            $error = 'Error saving category: ' . $e->getMessage();
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Add New Category</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Categories</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="add.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Category Name *</label>
            <input type="text" name="name" id="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px;">Description</label>
            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Save Category</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
