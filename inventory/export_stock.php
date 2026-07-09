<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

// Fetch products
$query = "
    SELECT p.id, p.name, p.stock_quantity, p.selling_price, p.purchase_price, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id
    ORDER BY p.stock_quantity ASC
";
$stmt = $pdo->query($query);
$inventory = $stmt->fetchAll();

// Set headers to force download as CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_stock_' . date('Y-m-d') . '.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Product ID', 'Product Name', 'Category', 'Purchase Cost', 'Selling Price', 'Current Stock', 'Total Sold', 'Status', 'Total Value'));

// Fetch the data and output it row by row
foreach ($inventory as $item) {
    $status = 'In Stock';
    if ($item['stock_quantity'] <= 0) {
        $status = 'Out of Stock';
    } elseif ($item['stock_quantity'] <= 5) {
        $status = 'Low Stock';
    }
    
    $total_value = $item['purchase_price'] * $item['stock_quantity'];
    
    fputcsv($output, array(
        $item['id'],
        $item['name'],
        $item['category_name'] ?: 'N/A',
        $item['purchase_price'],
        $item['selling_price'],
        $item['stock_quantity'],
        $item['total_sold'],
        $status,
        $total_value
    ));
}
fclose($output);
exit;
