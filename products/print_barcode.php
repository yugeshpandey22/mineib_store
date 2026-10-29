<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: index.php");
    exit;
}

if (empty($product['barcode'])) {
    // If no barcode, redirect back with error or just show error
    $error = "This product does not have a barcode. Please edit the product and add a barcode first.";
}

$qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 24; // Default 24 stickers
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Barcode - <?php echo htmlspecialchars($product['name']); ?></title>
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
        <h2 style="margin-top: 0;">Print Barcode: <?php echo htmlspecialchars($product['name']); ?></h2>
        
        <?php if(isset($error)): ?>
            <div style="color: red; margin-bottom: 15px; font-weight: bold;"><?php echo $error; ?></div>
            <a href="edit.php?id=<?php echo $id; ?>" class="btn-secondary">Edit Product</a>
            <a href="index.php" class="btn-secondary" style="background: #dc3545;">Back to List</a>
        <?php else: ?>
            <form method="POST" style="margin-bottom: 20px;">
                <label>Number of Stickers:</label>
                <input type="number" name="qty" value="<?php echo $qty; ?>" min="1" max="100" style="padding: 8px; width: 60px; margin: 0 10px;">
                <button type="submit" style="background: #28a745;">Generate</button>
            </form>
            
            <button onclick="window.print()">🖨️ Print Now</button>
            <a href="index.php" class="btn-secondary" style="margin-left: 10px;">Back</a>
        <?php endif; ?>
    </div>

    <?php if(!isset($error)): ?>
        <div class="print-area">
            <?php for($i = 0; $i < $qty; $i++): ?>
                <div class="sticker">
                    <div class="sticker-brand">Amadika</div>
                    <div class="sticker-title"><?php echo htmlspecialchars($product['name']); ?></div>
                    <svg class="barcode"></svg>
                    <div class="sticker-price">Price: ₹<?php echo number_format($product['selling_price'], 2); ?></div>
                </div>
            <?php endfor; ?>
        </div>
        
        <script>
            // Initialize JsBarcode on all svg elements with class 'barcode'
            JsBarcode(".barcode", "<?php echo htmlspecialchars($product['barcode']); ?>", {
                format: "CODE128",
                lineColor: "#000",
                width: 1.5,
                height: 25,
                displayValue: true,
                fontSize: 10,
                margin: 0
            });
        </script>
    <?php endif; ?>

</body>
</html>
