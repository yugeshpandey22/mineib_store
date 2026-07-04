<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();



if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Cannot delete self
    if ($id == $_SESSION['user_id']) {
        die("You cannot delete your own account.");
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        echo "Error deleting user: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
