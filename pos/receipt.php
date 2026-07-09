<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Removed requireLogin() so customers can view their receipt via WhatsApp link
if (!isset($_GET['id'])) {
    header("Location: billing.php");
    exit;
}

$order_id = $_GET['id'];

// Fetch order
$stmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name, c.phone as customer_phone, u.username as cashier_name
    FROM orders o
    LEFT JOIN customers c ON o.customer_id = c.id
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    die("Order not found!");
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

// Fetch settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings_rows = $stmt->fetchAll();
$settings = [];
foreach($settings_rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$store_name = $settings['store_name'] ?? 'MINEIB';
$address = nl2br(htmlspecialchars($settings['address'] ?? '123 Business Street, Tech City'));
$gstin = $settings['gstin'] ?? '';
$footer_msg = $settings['footer_msg'] ?? 'Thank you for shopping!';
$phone = $settings['phone'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $order['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&family=Libre+Barcode+39&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Space Mono', monospace; /* Thermal printer aesthetic */
            background: #e0e5ec;
            color: #111;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
        }
        .receipt-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
        }
        .receipt {
            background: #fff;
            width: 320px; /* Thermal receipt width */
            padding: 30px 20px;
            border-radius: 4px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
        }
        /* Zigzag bottom effect for thermal receipt */
        .receipt::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 10px;
            background-image: radial-gradient(circle at 10px 0, transparent 11px, #fff 12px);
            background-size: 20px 20px;
            background-position: -10px 0;
        }
        .store-logo {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 5px;
            letter-spacing: -1px;
            text-transform: uppercase;
        }
        .store-details {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .divider {
            border-bottom: 2px dashed #ccc;
            margin: 15px 0;
        }
        .meta-info {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
        }
        .customer-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            font-size: 13px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .item-table th {
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
            text-align: left;
            font-weight: 700;
        }
        .item-table td {
            padding: 12px 0;
            border-bottom: 1px dotted #ccc;
        }
        .item-table tr:last-child td {
            border-bottom: none;
        }
        .totals-section {
            font-size: 14px;
            margin-top: 15px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #444;
        }
        .grand-total {
            font-size: 20px;
            font-weight: 700;
            text-align: center;
            border-top: 2px dashed #333;
            border-bottom: 2px dashed #333;
            padding: 15px 0;
            margin-top: 15px;
        }
        .footer-text {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin-top: 25px;
            line-height: 1.5;
        }
        .barcode-wrap {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px dashed #ccc;
        }
        .barcode-text {
            font-family: 'Libre Barcode 39', cursive;
            font-size: 46px;
            line-height: 1;
            color: #111;
        }
        .barcode-number {
            font-size: 11px;
            letter-spacing: 4px;
            color: #555;
            margin-top: 5px;
        }
        .paid-stamp {
            position: absolute;
            top: 150px;
            right: 20px;
            color: #05cd99;
            border: 3px solid #05cd99;
            border-radius: 8px;
            padding: 5px 15px;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            transform: rotate(-15deg);
            opacity: 0.2;
            pointer-events: none;
            letter-spacing: 2px;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn {
            padding: 12px 20px;
            border-radius: 5px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            color: white;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .btn:hover { transform: translateY(-2px); }
        .btn-save { background: #2b3674; }
        .btn-print { background: #ff4b4b; }
        .btn-pdf { background: #05cd99; }
        
        @media print {
            @page { margin: 0; size: auto; }
            html, body { background: white; padding: 0; margin: 0; height: auto; }
            .receipt-container { gap: 0; margin: 0; }
            .receipt { box-shadow: none; width: 320px; margin: 0 auto; padding: 20px; page-break-after: avoid; page-break-before: avoid; page-break-inside: avoid; }
            .receipt::after { display: none; }
            .actions { display: none; }
        }
    </style>
</head>
<body>

    <?php
    $inv_no = 'ORD-' . str_pad($order['id'], 6, '0', STR_PAD_LEFT);
    $total = number_format($order['grand_total'], 2);
    
    // Generate full link to this receipt
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $receipt_link = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $wa_msg = "Hello! Thank you for shopping with *$store_name*.\n\nYour Receipt *$inv_no* is ready.\nTotal Amount: *₹$total*\n\nClick the link below to view/download your PDF bill:\n$receipt_link\n\nHave a great day!";
    $wa_msg_encoded = urlencode($wa_msg);
    $wa_phone = $order['customer_phone'] ? preg_replace('/[^0-9]/', '', $order['customer_phone']) : '';
    if($wa_phone && strlen($wa_phone) == 10) {
        $wa_phone = '91' . $wa_phone;
    }
    $wa_link = "https://wa.me/{$wa_phone}?text={$wa_msg_encoded}";
    ?>

    <div class="receipt-container">
        <!-- Action Buttons (Hidden in print) -->
        <div class="actions">
            <?php if (isLoggedIn()): ?>
                <a href="../orders/index.php" class="btn btn-save"><i class="fas fa-arrow-left"></i> Back to Orders</a>
            <?php endif; ?>
            
            <button class="btn btn-print" onclick="window.print()"><i class="fas fa-print"></i> Print Bill</button>
            <button class="btn btn-pdf" onclick="downloadPDF()"><i class="fas fa-download"></i> Download PDF</button>
            
            <?php if (isLoggedIn()): ?>
                <a href="<?php echo $wa_link; ?>" target="_blank" class="btn" style="background: #25D366; color: white;"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <?php endif; ?>
        </div>

        <!-- The Receipt -->
        <div class="receipt" id="printable-receipt">
            <?php if ($order['payment_method'] !== 'Credit'): ?>
                <div class="paid-stamp">PAID</div>
            <?php endif; ?>
            
            <?php if(file_exists('../assets/logo.png')): ?>
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="../assets/logo.png" style="max-width: 220px; max-height: 100px; object-fit: contain;" alt="Store Logo">
                </div>
            <?php elseif(file_exists('../assets/logo.jpg')): ?>
                <div style="text-align: center; margin-bottom: 15px;">
                    <img src="../assets/logo.jpg" style="max-width: 220px; max-height: 100px; object-fit: contain;" alt="Store Logo">
                </div>
            <?php endif; ?>
            
            <div class="store-logo">
                <?php if(!file_exists('../assets/logo.png') && !file_exists('../assets/logo.jpg')): ?>
                    <i class="fa-solid fa-store" style="font-size: 22px; color: #555; margin-right: 5px;"></i> 
                <?php endif; ?>
                <?php echo htmlspecialchars($store_name); ?>
            </div>
            <div class="store-details">
                <?php echo $address; ?><br>
                <?php if($phone): ?>Phone: <?php echo htmlspecialchars($phone); ?><br><?php endif; ?>
                <?php if($gstin): ?>GSTIN: <span style="font-weight:bold;"><?php echo htmlspecialchars($gstin); ?></span><?php endif; ?>
            </div>

            <div class="divider"></div>

            <div class="meta-info">
                <div>
                    <strong>Order:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?><br>
                    <strong>Date:</strong> <?php echo date('d-M-Y H:i', strtotime($order['created_at'])); ?>
                </div>
                <div style="text-align: right;">
                    <strong>Cashier:</strong> <?php echo htmlspecialchars($order['cashier_name']); ?><br>
                    <strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                </div>
            </div>

            <?php if ($order['customer_name']): ?>
            <div class="customer-info">
                <div style="font-weight: 700; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 5px; color: #333;"><i class="fa-regular fa-user"></i> Customer Info</div>
                <?php echo htmlspecialchars($order['customer_name']); ?><br>
                <?php if($order['customer_phone']) echo "<i class='fa-solid fa-phone' style='font-size:10px;'></i> " . htmlspecialchars($order['customer_phone']); ?>
            </div>
            <?php endif; ?>

            <table class="item-table">
                <thead>
                    <tr>
                        <th style="width: 40%;">Item</th>
                        <th style="text-align: center; width: 15%;">Qty</th>
                        <th style="text-align: right; width: 15%;">Tax</th>
                        <th style="text-align: right; width: 30%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $item): 
                        $item_tax = $item['tax_amount'] ?? 0;
                        $item_total = $item['subtotal'] + $item_tax;
                    ?>
                    <tr>
                        <td>
                            <?php echo htmlspecialchars($item['product_name']); ?><br>
                            <small style="color: #666;">@ ₹<?php echo number_format($item['unit_price'], 2); ?></small>
                            <?php if(!empty($item['return_policy'])): ?>
                                <br><small style="color: #444; font-size: 10px; font-weight: bold;">(<?php echo htmlspecialchars($item['return_policy']); ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;"><?php echo $item['quantity']; ?></td>
                        <td style="text-align: right; font-size: 11px;">
                            <?php if ($item_tax > 0): ?>
                                <?php echo $item['tax_rate'] ?? 0; ?>%<br>
                                (₹<?php echo number_format($item_tax, 2); ?>)
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">₹<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totals-section">
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <?php if ($order['discount'] > 0): ?>
                <div class="totals-row">
                    <span>Discount:</span>
                    <span>- ₹<?php echo number_format($order['discount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($order['tax_amount']) && $order['tax_amount'] > 0): ?>
                <div class="totals-row">
                    <span>Total GST:</span>
                    <span>+ ₹<?php echo number_format($order['tax_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="grand-total">
                TOTAL: ₹<?php echo number_format($order['grand_total'], 2); ?>
            </div>

            <div class="barcode-wrap">
                <!-- '*' is required for Code39 barcode format to be valid -->
                <div class="barcode-text">*ORD-<?php echo $order['id']; ?>*</div>
                <div class="barcode-number">ORD-<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>

            <div class="footer-text">
                <strong><?php echo htmlspecialchars($footer_msg); ?></strong>
            </div>
        </div>
    </div>

    <!-- Include html2pdf library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        function downloadPDF() {
            const element = document.getElementById('printable-receipt');
            
            // Force layout recalculation just in case
            element.style.width = '320px';
            
            // Wait for any images to load and layout to settle
            setTimeout(() => {
                const pxToMm = 0.264583;
                // Get exact scroll height which represents the true full content height
                const contentHeight = (element.scrollHeight * pxToMm) + 15; 
                
                const opt = {
                    margin:       0,
                    filename:     'Receipt_<?php echo $order['id']; ?>.pdf',
                    image:        { type: 'jpeg', quality: 1 },
                    html2canvas:  { scale: 2, useCORS: true, scrollY: 0, scrollX: 0, backgroundColor: '#ffffff' },
                    // Create a custom page size that perfectly fits the receipt height
                    jsPDF:        { unit: 'mm', format: [85, contentHeight < 100 ? 100 : contentHeight], orientation: 'portrait' }
                };
                
                // Generate PDF without page breaks
                html2pdf().set(opt).from(element).save();
            }, 100);
        }
    </script>
</body>
</html>
