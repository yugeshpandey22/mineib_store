<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Manage Suppliers';

// Fetch suppliers
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name ASC");
$suppliers = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Suppliers</h2>
    <a href="add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none;"><i class="fas fa-plus"></i> Add Supplier</a>
</div>

<div class="card" style="display: block;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div style="background: #05cd99; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">Supplier saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #ff4b4b; color: white; padding: 10px; border-radius: 5px; margin-bottom: 15px;">Supplier deleted successfully!</div>
    <?php endif; ?>

    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1;">ID</th>
                <th style="padding: 12px; color: #a3aed1;">Name</th>
                <th style="padding: 12px; color: #a3aed1;">Company</th>
                <th style="padding: 12px; color: #a3aed1;">Phone</th>
                <th style="padding: 12px; color: #a3aed1;">Email</th>
                <th style="padding: 12px; color: #a3aed1;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($suppliers) > 0): ?>
                <?php foreach ($suppliers as $sup): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">#<?php echo $sup['id']; ?></td>
                        <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($sup['name']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($sup['company_name']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($sup['phone']); ?></td>
                        <td style="padding: 12px; color: #666;"><?php echo htmlspecialchars($sup['email']); ?></td>
                        <td style="padding: 12px;">
                            <a href="view.php?id=<?php echo $sup['id']; ?>" style="color: #05cd99; margin-right: 10px; text-decoration: none;"><i class="fas fa-eye"></i> View</a>
                            <a href="edit.php?id=<?php echo $sup['id']; ?>" style="color: #0072ff; margin-right: 10px; text-decoration: none;"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete.php?id=<?php echo $sup['id']; ?>" style="color: #ff4b4b; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this supplier?');"><i class="fas fa-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #888;">No suppliers found. Click 'Add Supplier' to create one.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>
