<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Manage Customers';

// Fetch customers
$stmt = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Customers</h2>
    <a href="add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none;"><i class="fas fa-plus"></i> Add Customer</a>
</div>

<div class="card" style="display: block; padding: 0;">
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div style="background: #05cd99; color: white; padding: 15px 20px; margin: 20px; border-radius: 5px;">Customer saved successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background: #ff4b4b; color: white; padding: 15px 20px; margin: 20px; border-radius: 5px;">Customer deleted successfully!</div>
    <?php endif; ?>

    <div style="overflow-x: auto; padding: 0 20px 20px;">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $cust): ?>
                        <tr>
                            <td>#<?php echo $cust['id']; ?></td>
                            <td style="font-weight: 600;"><?php echo htmlspecialchars($cust['name']); ?></td>
                            <td><?php echo htmlspecialchars($cust['phone']); ?></td>
                            <td style="color: #666;"><?php echo htmlspecialchars($cust['email']); ?></td>
                            <td>
                                <a href="view.php?id=<?php echo $cust['id']; ?>" style="color: #05cd99; margin-right: 15px; text-decoration: none; font-weight: 600;"><i class="fas fa-eye"></i> View</a>
                                <a href="edit.php?id=<?php echo $cust['id']; ?>" style="color: #0072ff; margin-right: 15px; text-decoration: none; font-weight: 600;"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete.php?id=<?php echo $cust['id']; ?>" style="color: #ff4b4b; text-decoration: none; font-weight: 600;" onclick="return confirm('Are you sure you want to delete this customer?');"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #888;">No customers found. Click 'Add Customer' to create one.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
