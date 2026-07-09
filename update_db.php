<?php
require 'c:/xampp/htdocs/storemanagement/includes/db.php';

try {
    $pdo->exec("ALTER TABLE products ADD COLUMN barcode VARCHAR(255) NULL UNIQUE AFTER category_id");
    echo "Successfully added barcode column.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column barcode already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
