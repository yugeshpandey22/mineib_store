<?php
// Disable display of raw PHP errors so we only output valid JSON
ini_set('display_errors', 0);
error_reporting(0);

require_once '../includes/db.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

// Check if user is logged in (auth.php might redirect, so we do manual check here for API)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please login again.']);
    exit;
}

// Get JSON POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

if (!$data || empty($data['items'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty or invalid data format']);
    exit;
}

$customer_phone = isset($data['customer_phone']) ? trim($data['customer_phone']) : '';
$customer_name = isset($data['customer_name']) ? trim($data['customer_name']) : '';
$customer_email = isset($data['customer_email']) ? trim($data['customer_email']) : '';

try {
    $pdo->beginTransaction();
    
    $customer_id = null;
    if (!empty($customer_phone)) {
        // Check if customer exists by phone
        $cstmt = $pdo->prepare("SELECT id, name, email FROM customers WHERE phone = ?");
        $cstmt->execute([$customer_phone]);
        $c_row = $cstmt->fetch();
        
        if ($c_row) {
            $customer_id = $c_row['id'];
            
            $updates = [];
            $params = [];
            
            if (!empty($customer_name) && $c_row['name'] !== $customer_name && ($c_row['name'] === 'Walk-in Customer' || strpos($c_row['name'], 'Walk-in ') === 0)) {
                $updates[] = "name = ?";
                $params[] = $customer_name;
            }
            if (!empty($customer_email) && empty($c_row['email'])) {
                $updates[] = "email = ?";
                $params[] = $customer_email;
            }
            
            if (count($updates) > 0) {
                $params[] = $customer_id;
                $ustmt = $pdo->prepare("UPDATE customers SET " . implode(', ', $updates) . " WHERE id = ?");
                $ustmt->execute($params);
            }
            
        } else {
            // Create new customer with provided name, or fallback to Walk-in
            $insert_name = !empty($customer_name) ? $customer_name : 'Walk-in Customer';
            $nstmt = $pdo->prepare("INSERT INTO customers (name, phone, email) VALUES (?, ?, ?)");
            $nstmt->execute([$insert_name, $customer_phone, $customer_email]);
            $customer_id = $pdo->lastInsertId();
        }
    } else if (!empty($customer_name)) {
        // If phone is empty but name is provided
        $nstmt = $pdo->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        $nstmt->execute([$customer_name, $customer_email]);
        $customer_id = $pdo->lastInsertId();
    }

$discount = isset($data['discount']) ? (float)$data['discount'] : 0;
$payment_method = $data['payment_method'] ?? 'Cash';
$user_id = $_SESSION['user_id'];

    $total_amount = 0;
    $total_tax = 0;
    
    // First loop: calculate totals
    foreach ($data['items'] as $item) {
        $stmt = $pdo->prepare("SELECT stock_quantity, selling_price FROM products WHERE id = ?");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new Exception("Product not found: " . $item['name']);
        }

        $item_subtotal = $item['price'] * $item['qty'];
        $item_gst_rate = isset($item['gst_rate']) ? (float)$item['gst_rate'] : 0;
        $item_gst_amount = ($item_subtotal * $item_gst_rate) / 100;
        
        $total_amount += $item_subtotal;
        $total_tax += $item_gst_amount;
    }
    
    $grand_total = ($total_amount - $discount) + $total_tax;
    if ($grand_total < 0) $grand_total = 0;

    // Create Order
    // Note: tax_rate on orders is now redundant but we can set it to 0 or average. Let's just set to 0.
    $stmt = $pdo->prepare("INSERT INTO orders (customer_id, user_id, total_amount, discount, tax_rate, tax_amount, grand_total, payment_method) VALUES (?, ?, ?, ?, 0, ?, ?, ?)");
    $stmt->execute([$customer_id, $user_id, $total_amount, $discount, $total_tax, $grand_total, $payment_method]);
    $order_id = $pdo->lastInsertId();

    // Insert Order Items and Update Stock
    foreach ($data['items'] as $item) {
        $subtotal = $item['price'] * $item['qty'];
        $item_gst_rate = isset($item['gst_rate']) ? (float)$item['gst_rate'] : 0;
        $item_gst_amount = ($subtotal * $item_gst_rate) / 100;
        
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, tax_rate, tax_amount, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['id'], $item['qty'], $item['price'], $item_gst_rate, $item_gst_amount, $subtotal]);

        // Reduce stock
        $stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $stmt->execute([$item['qty'], $item['id']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
