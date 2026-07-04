<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Manage Categories';

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Categories</h2>
    <div>
        <a href="import.php" class="btn" style="background: #111c43; color: white; margin-right: 10px; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: 600;"><i class="fas fa-file-csv"></i> Import CSV</a>
        <a href="add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none;"><i class="fas fa-plus"></i> Add Category</a>
    </div>
</div>

<div class="card" style="display: block; padding: 0;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div style="background: #05cd99; color: white; padding: 15px 20px; margin: 20px; border-radius: 5px;">Category saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #ff4b4b; color: white; padding: 15px 20px; margin: 20px; border-radius: 5px;">Category deleted successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'imported' && isset($_GET['count'])): ?>
        <div style="background: #0072ff; color: white; padding: 15px 20px; margin: 20px; border-radius: 5px;">Successfully imported <?php echo (int)$_GET['count']; ?> categories!</div>
    <?php endif; ?>

    <div style="overflow-x: auto; padding: 0 20px 20px;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td>#<?php echo $cat['id']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td style="color: #666;"><?php echo htmlspecialchars($cat['description']); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $cat['id']; ?>" style="color: #0072ff; margin-right: 15px; text-decoration: none; font-weight: 600;"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $cat['id']; ?>" style="color: #ff4b4b; text-decoration: none; font-weight: 600;" onclick="return confirm('Are you sure you want to delete this category?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 30px; color: #888;">No categories found. Click 'Add Category' to create one.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
