<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        // If there are products linked, it might fail depending on foreign key constraints
        echo "Error deleting category: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
