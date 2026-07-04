<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // First get the image url to delete the physical file
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        
        if ($product && $product['image_url']) {
            $image_path = '../uploads/products/' . $product['image_url'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: index.php?msg=deleted");
    } catch (PDOException $e) {
        echo "Error deleting product: " . $e->getMessage();
    }
} else {
    header("Location: index.php");
}
?>
