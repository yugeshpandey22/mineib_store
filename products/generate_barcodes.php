<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

// Fetch products without barcode
$stmt = $pdo->query("SELECT id FROM products WHERE barcode IS NULL OR barcode = ''");
$products = $stmt->fetchAll();
$count = 0;

if (count($products) > 0) {
    foreach ($products as $prod) {
        $id = $prod['id'];
        // Generate a barcode: PRD + ID padded to 6 digits, e.g., PRD000042
        // Or just a numeric barcode which JsBarcode can easily render as CODE128
        // Let's use a 8 digit numeric barcode: 100000 + ID
        $barcode = str_pad($id, 8, '0', STR_PAD_LEFT); 
        
        $updateStmt = $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?");
        if ($updateStmt->execute([$barcode, $id])) {
            $count++;
        }
    }
}

header("Location: index.php?msg=generated&count=" . $count);
exit;
