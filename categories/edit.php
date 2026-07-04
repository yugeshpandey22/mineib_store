<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Edit Category';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$error = '';

// Fetch existing category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Category name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
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
    <h2>Edit Category</h2>
    <a href="index.php" style="color: #8f9bba; text-decoration: none;"><i class="fas fa-arrow-left"></i> Back to Categories</a>
</div>

<div class="card" style="display: block; max-width: 600px;">
    <?php if ($error): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form action="edit.php?id=<?php echo $id; ?>" method="POST">
        <div style="margin-bottom: 15px;">
            <label for="name" style="display: block; font-weight: 600; margin-bottom: 5px;">Category Name *</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($category['name']); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="description" style="display: block; font-weight: 600; margin-bottom: 5px;">Description</label>
            <textarea name="description" id="description" rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($category['description']); ?></textarea>
        </div>

        <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #0072ff; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">Update Category</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
