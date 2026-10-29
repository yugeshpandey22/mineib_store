<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if we need inventory stats with filters
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "p.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter !== '') {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter === 'out') {
    $where_clauses[] = "p.stock_quantity <= 0";
} elseif ($status_filter === 'low') {
    $where_clauses[] = "p.stock_quantity > 0 AND p.stock_quantity <= 5";
} elseif ($status_filter === 'in') {
    $where_clauses[] = "p.stock_quantity > 5";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Dashboard Stats
$total_products = (int)($pdo->query("SELECT COUNT(id) FROM products")->fetchColumn() ?: 0);
$total_stock = (int)($pdo->query("SELECT SUM(stock_quantity) FROM products")->fetchColumn() ?: 0);
$low_stock_count = (int)($pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")->fetchColumn() ?: 0);

// Inventory Stats
$inventory_query = "
    SELECT p.stock_quantity, p.purchase_price, p.selling_price 
    FROM products p 
    $where_sql
";
$stmt = $pdo->prepare($inventory_query);
$stmt->execute($params);
$inventory = $stmt->fetchAll();

$total_items = 0;
$total_stock_value = 0;
$total_expected_revenue = 0;

foreach ($inventory as $item) {
    if ($item['stock_quantity'] > 0) {
        $total_items += $item['stock_quantity'];
        $total_stock_value += ($item['purchase_price'] * $item['stock_quantity']);
        $total_expected_revenue += ($item['selling_price'] * $item['stock_quantity']);
    }
}

echo json_encode([
    'dashboard' => [
        'total_products' => $total_products,
        'total_stock' => $total_stock,
        'low_stock_count' => $low_stock_count
    ],
    'inventory' => [
        'total_items' => number_format($total_items),
        'total_stock_value' => number_format($total_stock_value, 2),
        'total_expected_revenue' => number_format($total_expected_revenue, 2)
    ]
]);
