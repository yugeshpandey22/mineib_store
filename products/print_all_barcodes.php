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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            max-width: 210mm;
            /* A4 Width roughly */
            margin: auto;
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .sticker {
            width: 150px;
            height: 90px;
            border: 1px dashed #ccc;
            padding: 8px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: white;
            box-sizing: border-box;
        }

        .sticker-brand {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000;
        }

        .sticker-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 2px;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sticker-price {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }

        .sticker svg {
            max-width: 100%;
            height: 35px;
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

        button:hover {
            background: #0b5ed7;
        }

        .btn-secondary {
            background: #6c757d;
            text-decoration: none;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-size: 16px;
        }

        @media print {
            @page {
                margin: 0;
                size: auto;
            }

            /* This hides the browser URL and page numbers */
            body {
                background: white;
                padding: 0;
                margin: 1cm;
            }

            .no-print {
                display: none !important;
            }

            .print-area {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: none;
            }

            .sticker {
                border: 1px solid #000;
                break-inside: avoid;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
</head>

<body>

    <div class="no-print">
        <h2 style="margin-top: 0;">Print All Barcodes</h2>

        <?php if (isset($error)): ?>
            <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #ffeeba;">
                <?php echo $error; ?>
            </div>
            <a href="index.php" class="btn-secondary" style="background: #6c757d;">Go Back</a>
        <?php else: ?>
            <p>Total Products with Barcodes: <strong><?php echo count($products); ?></strong></p>


            <button onclick="window.print()">🖨️ Print / Download PDF</button>
            <a href="index.php" class="btn-secondary" style="margin-left: 10px;">Back</a>
        <?php endif; ?>
    </div>

    <?php if (!isset($error)): ?>
        <div class="print-area">
            <?php foreach ($products as $prod): ?>
                <?php
                // Determine how many stickers to print for this product based on stock quantity
                $stickers_to_print = (int)$prod['stock_quantity'];

                // If stock is 0 we skip printing for this product
                if ($stickers_to_print <= 0) {
                    continue;
                }
                ?>
                <?php for ($i = 0; $i < $stickers_to_print; $i++): ?>
                    <div class="sticker">
                        <div class="sticker-brand">Amadika</div>
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
                    height: 25,
                    displayValue: true,
                    fontSize: 10,
                    margin: 0
                });
            });
        </script>
    <?php endif; ?>

</body>

</html>