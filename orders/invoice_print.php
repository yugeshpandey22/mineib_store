<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: invoice.php");
    exit;
}

$order_id = $_GET['id'];

// Fetch order
$stmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name, c.phone as customer_phone, c.email as customer_email, c.address as customer_address
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Invoice not found!");
}

// Fetch order items
$stmt = $pdo->prepare("
    SELECT oi.*, p.name as product_name, p.return_policy
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - <?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Include FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f7fe;
            color: #333;
            padding: 40px;
        }
        .invoice-box {
            background: #fff;
            max-width: 800px;
            margin: auto;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border-radius: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #0072ff;
            padding-bottom: 20px;
        }
        .company-details h1 {
            color: #0072ff;
            margin: 0 0 5px;
            font-size: 28px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0 0 10px;
            font-size: 24px;
            color: #2b3674;
            text-transform: uppercase;
        }
        .billing-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        .bill-to, .bill-from {
            width: 45%;
        }
        .bill-to h3, .bill-from h3 {
            color: #8f9bba;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th {
            background: #0072ff;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .totals {
            width: 300px;
            float: right;
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 20px;
        }
        .totals div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .grand-total {
            font-size: 20px;
            font-weight: 700;
            color: #0072ff;
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 10px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .print-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 12px;
            background: #05cd99;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        @media print {
            body { background: white; padding: 0; }
            .invoice-box { box-shadow: none; border: none; padding: 0; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <div class="invoice-box">
        <div class="header">
            <div class="company-details">
                <h1>mineib</h1>
                <p>123 Business Street, Tech City<br>Phone: +91 9876543210<br>Email: contact@pospro.com</p>
            </div>
            <div class="invoice-details">
                <h2>TAX INVOICE</h2>
                <p><strong>Invoice No:</strong> INV-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?><br>
                <strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?></p>
            </div>
        </div>

        <div class="billing-section">
            <div class="bill-to">
                <h3>Bill To:</h3>
                <?php if ($order['customer_name']): ?>
                    <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong><br>
                    <?php if($order['customer_phone']) echo htmlspecialchars($order['customer_phone']) . "<br>"; ?>
                    <?php if($order['customer_email']) echo htmlspecialchars($order['customer_email']) . "<br>"; ?>
                    <?php if($order['customer_address']) echo nl2br(htmlspecialchars($order['customer_address'])); ?>
                <?php else: ?>
                    <strong>Walk-in Customer</strong>
                <?php endif; ?>
            </div>
            <div class="bill-from">
                <h3>Payment Details:</h3>
                <p><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?><br>
                <strong>Status:</strong> Paid</p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Item</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">GST</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($items as $item): 
                    $item_tax = $item['tax_amount'] ?? 0;
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td>
                        <?php echo htmlspecialchars($item['product_name']); ?>
                        <?php if(!empty($item['return_policy'])): ?>
                            <br><small style="color: #666; font-size: 11px;"><i class="fas fa-undo-alt" style="font-size: 10px;"></i> <?php echo htmlspecialchars($item['return_policy']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                    <td style="text-align: right;">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td style="text-align: right;">
                        <?php if ($item_tax > 0): ?>
                            <?php echo $item['tax_rate'] ?? 0; ?>% (₹<?php echo number_format($item_tax, 2); ?>)
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right;">₹<?php echo number_format($item['subtotal'] + $item_tax, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="clearfix">
            <div class="totals">
                <div>
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <?php if ($order['discount'] > 0): ?>
                <div>
                    <span>Discount:</span>
                    <span style="color: #ff4b4b;">- ₹<?php echo number_format($order['discount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                <div>
                    <span>Total GST:</span>
                    <span>+ ₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="grand-total">
                    <span>Grand Total:</span>
                    <span>₹<?php echo number_format($order['grand_total'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature is required.</p>
        </div>
    </div>

    <!-- Include html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <?php
    $store = 'mineib'; // Or use settings if available
    $inv_no = 'INV-' . str_pad($order['id'], 5, '0', STR_PAD_LEFT);
    $total = number_format($order['grand_total'], 2);
    
    // Generate full link to this invoice
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $invoice_link = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $wa_msg = "Hello! Thank you for shopping with *$store*.\n\nYour Invoice *$inv_no* is ready.\nTotal Amount: *₹$total*\n\nClick the link below to view/download your PDF bill:\n$invoice_link\n\nHave a great day!";
    $wa_msg_encoded = urlencode($wa_msg);
    $wa_phone = $order['customer_phone'] ? preg_replace('/[^0-9]/', '', $order['customer_phone']) : '';
    if($wa_phone && strlen($wa_phone) == 10) {
        $wa_phone = '91' . $wa_phone;
    }
    $wa_link = "https://wa.me/{$wa_phone}?text={$wa_msg_encoded}";
    ?>

    <div style="text-align: center; margin: 30px auto; max-width: 800px;" class="actions-container">
        <a href="index.php" class="btn" style="background: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: 600; margin: 0 5px; display: inline-block;">Save</a>
        <button class="btn" style="background: #dc3545; color: white; padding: 12px 25px; border: none; border-radius: 5px; font-weight: 600; margin: 0 5px; cursor: pointer;" onclick="window.print()"><i class="fas fa-file-alt"></i> Print</button>
        <button class="btn" style="background: #ffc107; color: #000; padding: 12px 25px; border: none; border-radius: 5px; font-weight: 600; margin: 0 5px; cursor: pointer;" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> Download PDF</button>
        <a href="<?php echo $wa_link; ?>" target="_blank" class="btn" style="background: #25D366; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: 600; margin: 0 5px; display: inline-block;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
    </div>

    <style>
        @media print {
            .actions-container { display: none !important; }
        }
    </style>

    <script>
        function downloadPDF() {
            const element = document.querySelector('.invoice-box');
            const opt = {
                margin:       10,
                filename:     'Invoice_INV-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>
