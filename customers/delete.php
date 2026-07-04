<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        // If there are orders linked, it might fail depending on foreign key constraints (though we set ON DELETE SET NULL for orders)
        echo "Error deleting customer: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
