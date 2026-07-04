<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        echo "Error deleting supplier: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
