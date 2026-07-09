<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

// Fetch all products that have a barcode
$stmt = $pdo->query("SELECT * FROM products WHERE barcode IS NOT NULL AND barcode != '' ORDER BY name ASC");
$products = $stmt->fetchAll();

if (count($products) == 0) {
    $error = "No products found with barcodes. Please add barcodes to your products first.";
}

$qty_per_product = isset($_POST['qty_per_product']) ? (int)$_POST['qty_per_product'] : 1; // Default 1 sticker per product
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print All Barcodes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .no-print {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            max-width: 600px;
            margin: 0 auto 20px auto;
            text-align: center;
        }
        .print-area {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            max-width: 210mm; /* A4 Width roughly */
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .sticker {
            width: 140px;
            height: 80px;
            border: 1px dashed #ccc;
            padding: 10px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
        }
        .sticker-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sticker-price {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }
        .sticker svg {
            max-width: 100%;
            height: 40px;
        }
        
        button {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover { background: #0b5ed7; }
        
        .btn-secondary { background: #6c757d; text-decoration: none; display: inline-block; padding: 10px 20px; border-radius: 5px; color: white; font-size: 16px;}
        
        @media print {
            @page { margin: 0; size: auto; } /* This hides the browser URL and page numbers */
            body { background: white; padding: 0; margin: 1cm; }
            .no-print { display: none !important; }
            .print-area { box-shadow: none; padding: 0; margin: 0; max-width: none; }
            .sticker { border: 1px solid #000; break-inside: avoid; }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <div class="no-print">
        <h2 style="margin-top: 0;">Print All Barcodes</h2>
        
        <?php if(isset($error)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ffeeba;">
                <?php echo $error; ?>
            </div>
            <a href="index.php" class="btn-secondary" style="background: #6c757d;">Go Back</a>
        <?php else: ?>
            <p>Total Products with Barcodes: <strong><?php echo count($products); ?></strong></p>
            <form method="POST" style="margin-bottom: 20px;">
                <label>Stickers PER Product:</label>
                <input type="number" name="qty_per_product" value="<?php echo $qty_per_product; ?>" min="1" max="100" style="padding: 8px; width: 60px; margin: 0 10px;">
                <button type="submit" style="background: #28a745;">Generate View</button>
            </form>
            
            <button onclick="window.print()">🖨️ Print / Download PDF</button>
            <a href="index.php" class="btn-secondary" style="margin-left: 10px;">Back</a>
        <?php endif; ?>
    </div>

    <?php if(!isset($error)): ?>
        <div class="print-area">
            <?php foreach($products as $prod): ?>
                <?php for($i = 0; $i < $qty_per_product; $i++): ?>
                    <div class="sticker">
                        <div class="sticker-title"><?php echo htmlspecialchars($prod['name']); ?></div>
                        <!-- Use a unique ID for each SVG so JsBarcode can find them, but class is better for bulk -->
                        <svg class="barcode" data-barcode="<?php echo htmlspecialchars($prod['barcode']); ?>"></svg>
                        <div class="sticker-price">Price: ₹<?php echo number_format($prod['selling_price'], 2); ?></div>
                    </div>
                <?php endfor; ?>
            <?php endforeach; ?>
        </div>
        
        <script>
            // Initialize JsBarcode on all svg elements with class 'barcode'
            document.querySelectorAll('.barcode').forEach(function(svg) {
                JsBarcode(svg, svg.getAttribute('data-barcode'), {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 1.5,
                    height: 30,
                    displayValue: true,
                    fontSize: 12,
                    margin: 0
                });
            });
        </script>
    <?php endif; ?>

</body>
</html>
