<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Inventory / Stock';

// Build the query with filters
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

// Fetch products
$query = "
    SELECT p.id, p.name, p.stock_quantity, p.selling_price, p.purchase_price, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    LEFT JOIN order_items oi ON p.id = oi.product_id
    $where_sql
    GROUP BY p.id
    ORDER BY p.stock_quantity ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$inventory = $stmt->fetchAll();

// Fetch categories for filter dropdown
$cat_stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

// Calculate Totals for all items
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

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2>Inventory Status</h2>
    <div>
        <a href="export_stock.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; background: #05cd99; margin-right: 10px;"><i class="fas fa-file-excel"></i> Export CSV</a>
        <a href="../purchases/add.php" class="btn-submit" style="display: inline-block; width: auto; padding: 10px 20px; text-decoration: none; background: #0072ff;"><i class="fas fa-truck"></i> Restock via Purchase</a>
    </div>
</div>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 20px;">
    <div class="card" style="padding: 20px; display: flex; flex-direction: column;">
        <span style="color: #a3aed1; font-size: 14px; font-weight: 600; text-transform: uppercase;">Total Items in Stock</span>
        <span style="color: #2b3674; font-size: 24px; font-weight: 700; margin-top: 5px;" id="live_inv_total_items"><?php echo number_format($total_items); ?></span>
    </div>
    <div class="card" style="padding: 20px; display: flex; flex-direction: column;">
        <span style="color: #a3aed1; font-size: 14px; font-weight: 600; text-transform: uppercase;">Total Stock Value</span>
        <span style="color: #2b3674; font-size: 24px; font-weight: 700; margin-top: 5px;" id="live_inv_stock_value">₹<?php echo number_format($total_stock_value, 2); ?></span>
    </div>
    <div class="card" style="padding: 20px; display: flex; flex-direction: column;">
        <span style="color: #a3aed1; font-size: 14px; font-weight: 600; text-transform: uppercase;">Expected Revenue</span>
        <span style="color: #2b3674; font-size: 24px; font-weight: 700; margin-top: 5px;" id="live_inv_expected_revenue">₹<?php echo number_format($total_expected_revenue, 2); ?></span>
    </div>
</div>

<div class="card" style="display: block;">
    
    <!-- Filters -->
    <div style="margin-bottom: 20px; background: #f8f9fc; padding: 15px; border-radius: 8px;">
        <form method="GET" action="" style="display: flex; gap: 15px; align-items: flex-end;">
            <div style="flex: 1;">
                <label style="display: block; font-size: 12px; color: #a3aed1; margin-bottom: 5px;">Search Product</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Product name..." style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 12px; color: #a3aed1; margin-bottom: 5px;">Category</label>
                <select name="category" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1;">
                <label style="display: block; font-size: 12px; color: #a3aed1; margin-bottom: 5px;">Stock Status</label>
                <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">All Statuses</option>
                    <option value="in" <?php echo $status_filter === 'in' ? 'selected' : ''; ?>>In Stock</option>
                    <option value="low" <?php echo $status_filter === 'low' ? 'selected' : ''; ?>>Low Stock (<= 5)</option>
                    <option value="out" <?php echo $status_filter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                </select>
            </div>
            <div>
                <button type="submit" style="padding: 9px 20px; background: #4318FF; color: white; border: none; border-radius: 5px; cursor: pointer;">Filter</button>
                <a href="stock.php" style="padding: 9px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 5px; display: inline-block; margin-left: 5px;">Clear</a>
            </div>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left;" id="stockTable">
        <thead>
            <tr style="border-bottom: 2px solid #eee;">
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(0)">S.No. ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(1)">Product Name ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(2)">Category ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(3)">Purchase Cost ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(4)">Selling Price ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(5)">Current Stock ↕</th>
                <th style="padding: 12px; color: #a3aed1; cursor: pointer; white-space: nowrap;" onclick="sortTable(6)">Total Sold ↕</th>
                <th style="padding: 12px; color: #a3aed1; white-space: nowrap;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($inventory) > 0): ?>
                <?php $sn = 1; foreach ($inventory as $item): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">#<?php echo $sn++; ?></td>
                        <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($item['category_name'] ?: 'N/A'); ?></td>
                        <td style="padding: 12px;">₹<?php echo number_format($item['purchase_price'], 2); ?></td>
                        <td style="padding: 12px;">₹<?php echo number_format($item['selling_price'], 2); ?></td>
                        <td style="padding: 12px; font-size: 16px; font-weight: 700; color: <?php echo ($item['stock_quantity'] <= 5) ? '#ff4b4b' : '#05cd99'; ?>;">
                            <?php echo $item['stock_quantity']; ?>
                        </td>
                        <td style="padding: 12px; font-size: 15px; font-weight: 600; color: #0d6efd;">
                            <?php echo $item['total_sold']; ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php if($item['stock_quantity'] <= 0): ?>
                                <span style="background: #ffe5e5; color: #ff4b4b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Out of Stock</span>
                            <?php elseif($item['stock_quantity'] <= 5): ?>
                                <span style="background: #fff4e5; color: #ff9920; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">Low Stock</span>
                            <?php else: ?>
                                <span style="background: #e6fcf5; color: #05cd99; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">In Stock</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #888;">No products found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function sortTable(n) {
  var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  table = document.getElementById("stockTable");
  switching = true;
  dir = "asc"; 
  while (switching) {
    switching = false;
    rows = table.rows;
    for (i = 1; i < (rows.length - 1); i++) {
      shouldSwitch = false;
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      
      let xContent = x.innerHTML.toLowerCase().replace(/[^0-9.-]+/g,"");
      let yContent = y.innerHTML.toLowerCase().replace(/[^0-9.-]+/g,"");
      
      let isNum = !isNaN(parseFloat(xContent)) && isFinite(xContent);
      
      if (dir == "asc") {
        if (isNum) {
            if (parseFloat(xContent) > parseFloat(yContent)) {
              shouldSwitch = true;
              break;
            }
        } else {
            if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
              shouldSwitch = true;
              break;
            }
        }
      } else if (dir == "desc") {
        if (isNum) {
            if (parseFloat(xContent) < parseFloat(yContent)) {
              shouldSwitch = true;
              break;
            }
        } else {
            if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
              shouldSwitch = true;
              break;
            }
        }
      }
    }
    if (shouldSwitch) {
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      switchcount ++;      
    } else {
      if (switchcount == 0 && dir == "asc") {
        dir = "desc";
        switching = true;
      }
    }
  }
}

function updateLiveInventoryStats() {
    const queryParams = window.location.search;
    fetch('<?php echo BASE_URL; ?>/ajax/get_live_stats.php' + queryParams)
        .then(res => res.json())
        .then(data => {
            if(data.inventory) {
                const itemsEl = document.getElementById('live_inv_total_items');
                if(itemsEl) itemsEl.innerText = data.inventory.total_items;
                
                const valueEl = document.getElementById('live_inv_stock_value');
                if(valueEl) valueEl.innerText = '₹' + data.inventory.total_stock_value;
                
                const revEl = document.getElementById('live_inv_expected_revenue');
                if(revEl) revEl.innerText = '₹' + data.inventory.total_expected_revenue;
            }
        })
        .catch(err => console.error('Error fetching inventory stats:', err));
}
setInterval(updateLiveInventoryStats, 5000);
</script>

<?php require_once '../includes/footer.php'; ?>
