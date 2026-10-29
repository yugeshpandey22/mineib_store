<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin(); // Protect this page

$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Today's Stats & Trends
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$today_sales = (float)($pdo->query("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn() ?: 0);
$yesterday_sales = (float)($pdo->query("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = '$yesterday'")->fetchColumn() ?: 0);
$sales_trend = $yesterday_sales > 0 ? (($today_sales - $yesterday_sales) / $yesterday_sales) * 100 : ($today_sales > 0 ? 100 : 0);

$today_orders = (int)($pdo->query("SELECT COUNT(id) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn() ?: 0);
$yesterday_orders = (int)($pdo->query("SELECT COUNT(id) FROM orders WHERE DATE(created_at) = '$yesterday'")->fetchColumn() ?: 0);
$orders_trend = $yesterday_orders > 0 ? (($today_orders - $yesterday_orders) / $yesterday_orders) * 100 : ($today_orders > 0 ? 100 : 0);

$total_products = (int)($pdo->query("SELECT COUNT(id) FROM products")->fetchColumn() ?: 0);
$total_stock = (int)($pdo->query("SELECT SUM(stock_quantity) FROM products")->fetchColumn() ?: 0);
$stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5");
$low_stock_count = $stmt->fetchColumn();

// Fetch total damaged products
$stmt = $pdo->query("SELECT COALESCE(SUM(quantity), 0) FROM damaged_products");
$damaged_count = $stmt->fetchColumn();

// Monthly Stats & Trends
$month = date('m');
$year = date('Y');
$last_month = date('m', strtotime('-1 month'));
$last_month_year = date('Y', strtotime('-1 month'));

$monthly_sales = (float)($pdo->query("SELECT SUM(grand_total) FROM orders WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'")->fetchColumn() ?: 0);
$last_monthly_sales = (float)($pdo->query("SELECT SUM(grand_total) FROM orders WHERE MONTH(created_at) = '$last_month' AND YEAR(created_at) = '$last_month_year'")->fetchColumn() ?: 0);
$monthly_sales_trend = $last_monthly_sales > 0 ? (($monthly_sales - $last_monthly_sales) / $last_monthly_sales) * 100 : ($monthly_sales > 0 ? 100 : 0);

$monthly_purchases = (float)($pdo->query("SELECT SUM(total_amount) FROM purchases WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'")->fetchColumn() ?: 0);
$profit = $monthly_sales - $monthly_purchases;

// Sales Target (Power BI Style Goal)
$monthly_target = 500000; // 5 Lakh Target
$target_percent = min(($monthly_sales / $monthly_target) * 100, 100);

// Chart Data (Past 7 Days Sales)
$sales_labels = [];
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $sales_labels[] = date('d M', strtotime($date));
    $day_sales = (float)($pdo->query("SELECT SUM(grand_total) FROM orders WHERE DATE(created_at) = '$date'")->fetchColumn() ?: 0);
    $sales_data[] = $day_sales;
}

// Chart Data (Top Categories)
$cat_stmt = $pdo->query("SELECT c.name, SUM(oi.subtotal) as total FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN categories c ON p.category_id = c.id GROUP BY c.id ORDER BY total DESC LIMIT 4");
$cat_sales = $cat_stmt->fetchAll();
$cat_labels = [];
$cat_data = [];
$cat_colors = ['#0d6efd', '#05cd99', '#ff9920', '#8b5cf6'];
foreach($cat_sales as $c) {
    $cat_labels[] = $c['name'];
    $cat_data[] = (float)$c['total'];
}
if(empty($cat_labels)) {
    $cat_labels = ['No Data'];
    $cat_data = [1];
    $cat_colors = ['#e0e5f2'];
}

// Bottom Grid Data
$recent_orders = $pdo->query("SELECT o.id, o.grand_total, o.created_at, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id ORDER BY o.id DESC LIMIT 4")->fetchAll();
$low_stock = $pdo->query("SELECT name, stock_quantity FROM products WHERE stock_quantity <= 5 ORDER BY stock_quantity ASC LIMIT 4")->fetchAll();
$top_products = $pdo->query("SELECT p.name, SUM(oi.quantity) as qty_sold FROM order_items oi JOIN products p ON oi.product_id = p.id JOIN orders o ON oi.order_id = o.id WHERE MONTH(o.created_at) = MONTH(CURRENT_DATE()) AND YEAR(o.created_at) = YEAR(CURRENT_DATE()) GROUP BY p.id ORDER BY qty_sold DESC LIMIT 4")->fetchAll();
$max_qty = !empty($top_products) ? max(array_column($top_products, 'qty_sold')) : 1;

define('BASE_URL', '/storemanagement');
$page_title = 'Dashboard';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

        <div class="dashboard-header">
            <div>
                <h2>Dashboard</h2>
                <p>Welcome back, <strong><?php echo htmlspecialchars(ucfirst($username)); ?>!</strong> Here's what's happening with your store today.</p>
            </div>
            <div class="date-picker-btn">
                <i class="far fa-calendar-alt"></i> <?php echo date('d M Y'); ?> <i class="fas fa-chevron-down"></i>
            </div>
        </div>

        <div class="amadika-cards">
            <!-- Row 1 -->
            <div class="amadika-card">
                <div class="ac-icon bg-c-blue"><i class="fas fa-wallet"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Today's Sales</div>
                    <div class="ac-value">₹ <?php echo number_format($today_sales, 2); ?></div>
                    <div class="ac-change">
                        <span class="<?php echo $sales_trend >= 0 ? 'text-up' : 'text-down'; ?>">
                            <?php echo $sales_trend >= 0 ? '↑' : '↓'; ?> <?php echo number_format(abs($sales_trend), 1); ?>%
                        </span> vs yesterday
                    </div>
                </div>
            </div>
            <div class="amadika-card">
                <div class="ac-icon bg-c-green"><i class="fas fa-shopping-bag"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Today's Orders</div>
                    <div class="ac-value"><?php echo $today_orders; ?></div>
                    <div class="ac-change">
                        <span class="<?php echo $orders_trend >= 0 ? 'text-up' : 'text-down'; ?>">
                            <?php echo $orders_trend >= 0 ? '↑' : '↓'; ?> <?php echo number_format(abs($orders_trend), 1); ?>%
                        </span> vs yesterday
                    </div>
                </div>
            </div>
            <div class="amadika-card">
                <div class="ac-icon bg-c-purple"><i class="fas fa-box"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Total Products</div>
                    <div class="ac-value" id="live_total_products"><?php echo $total_products; ?></div>
                    <div class="ac-change"><span style="color: #8f9bba;">Active in inventory</span></div>
                </div>
            </div>
            <div class="amadika-card">
                <div class="ac-icon bg-c-red"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Low Stock Items</div>
                    <div class="ac-value" id="live_low_stock"><?php echo $low_stock_count; ?></div>
                    <div class="ac-change"><span class="text-down">Requires Attention</span></div>
                </div>
            </div>
            
            <div class="amadika-card">
                <div class="ac-icon bg-c-purple" style="background: #e3e1fc; color: #6f42c1;"><i class="fas fa-layer-group"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Total Stock</div>
                    <div class="ac-value" id="live_total_stock"><?php echo $total_stock; ?></div>
                    <div class="ac-change"><span style="color: #8f9bba;">Total Qty Available</span></div>
                </div>
            </div>
            
            <!-- Row 2 (Dynamic Monthly Data) -->
            <div class="amadika-card">
                <div class="ac-icon bg-c-orange"><i class="fas fa-chart-bar"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title" style="display: flex; justify-content: space-between;">
                        <span>Monthly Sales</span>
                        <span class="<?php echo $monthly_sales_trend >= 0 ? 'text-up' : 'text-down'; ?>" style="font-size: 11px;">
                            <?php echo $monthly_sales_trend >= 0 ? '↑' : '↓'; ?> <?php echo number_format(abs($monthly_sales_trend), 1); ?>% vs last month
                        </span>
                    </div>
                    <div class="ac-value">₹ <?php echo number_format($monthly_sales, 2); ?></div>
                    <div style="margin-top: 10px;">
                        <div style="display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; color: #8f9bba; font-weight: 600;">
                            <span>Target: ₹<?php echo number_format($monthly_target); ?></span>
                            <span><?php echo number_format($target_percent, 1); ?>%</span>
                        </div>
                        <div style="width: 100%; background: #e0e5f2; height: 6px; border-radius: 3px; overflow: hidden;">
                            <div style="width: <?php echo $target_percent; ?>%; background: <?php echo $target_percent >= 100 ? '#05cd99' : '#0d6efd'; ?>; height: 100%; border-radius: 3px;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="amadika-card">
                <div class="ac-icon bg-c-red"><i class="fas fa-undo-alt"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Damaged Products</div>
                    <div class="ac-value"><?php echo $damaged_count; ?></div>
                    <div class="ac-change"><span class="text-down">Returned Items</span></div>
                </div>
            </div>
            <div class="amadika-card">
                <div class="ac-icon bg-c-yellow"><i class="fas fa-shopping-cart"></i></div>
                <div class="ac-details" style="width: 100%;">
                    <div class="ac-title">Purchase Amount</div>
                    <div class="ac-value">₹ <?php echo number_format($monthly_purchases, 2); ?></div>
                    <div class="ac-change"><span class="text-up">Total Cost</span></div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="dashboard-charts">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Sales Overview</h3>
                    <div class="chart-filter">This Month <i class="fas fa-chevron-down"></i></div>
                </div>
                <div style="position: relative; height: 280px; width: 100%;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Top Category Sales</h3>
                    <div class="chart-filter">This Month <i class="fas fa-chevron-down"></i></div>
                </div>
                <div style="position: relative; height: 280px; width: 100%; display: flex; justify-content: center; align-items: center;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Grid Section -->
        <div class="dashboard-bottom">
            <div class="panel-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="font-size: 16px; color: #111c43;">Recent Orders</h3>
                    <div style="font-size: 12px; color: #0d6efd; border: 1px solid #0d6efd; padding: 3px 10px; border-radius: 4px; cursor: pointer;">View All</div>
                </div>
                <table class="custom-table" style="box-shadow: none;">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_orders) > 0): ?>
                            <?php foreach($recent_orders as $o): ?>
                            <tr>
                                <td>#ORD-<?php echo str_pad($o['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($o['customer_name'] ?: 'Walk-in'); ?></td>
                                <td>₹ <?php echo number_format($o['grand_total'], 2); ?></td>
                                <td><span class="badge badge-success">Completed</span></td>
                                <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                                <td><a href="<?php echo BASE_URL; ?>/pos/receipt.php?id=<?php echo $o['id']; ?>" class="btn-icon" target="_blank"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="text-align: center;">No orders yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="font-size: 16px; color: #111c43;">Low Stock Products</h3>
                    <div style="font-size: 12px; color: #0d6efd; border: 1px solid #0d6efd; padding: 3px 10px; border-radius: 4px; cursor: pointer;">View All</div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php if (count($low_stock) > 0): ?>
                        <?php foreach($low_stock as $ls): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f0f2f8; padding-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; background: #e0e5f2; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #8f9bba;"><i class="fas fa-box"></i></div>
                                <span style="font-size: 13px; font-weight: 600; color: #2b3674;"><?php echo htmlspecialchars($ls['name']); ?></span>
                            </div>
                            <span style="font-size: 13px; font-weight: 600;"><?php echo $ls['stock_quantity']; ?></span>
                            <span class="badge badge-danger">Low</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #888; padding: 10px;">No low stock products.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="font-size: 16px; color: #111c43;">Top Selling Products</h3>
                    <div style="font-size: 12px; color: #8f9bba;">This Month</div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php if(count($top_products) > 0): ?>
                        <?php foreach($top_products as $tp): 
                            $percent = ($tp['qty_sold'] / $max_qty) * 100;
                        ?>
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #2b3674; margin-bottom: 6px;">
                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 120px;" title="<?php echo htmlspecialchars($tp['name']); ?>"><?php echo htmlspecialchars($tp['name']); ?></span>
                                <span style="color: #0d6efd;"><?php echo $tp['qty_sold']; ?> sold</span>
                            </div>
                            <div style="width: 100%; background: #e0e5f2; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="width: <?php echo $percent; ?>%; background: linear-gradient(90deg, #0d6efd, #00d2ff); height: 100%; border-radius: 4px;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: #888; padding: 10px;">No sales data yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

<!-- Chart.js for graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Line Chart
    const ctxSales = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctxSales, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($sales_labels); ?>,
            datasets: [{
                label: 'Sales (₹)',
                data: <?php echo json_encode($sales_data); ?>,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#0d6efd',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f0f2f8' } },
                x: { grid: { display: false } }
            }
        }
    });

    // Category Donut Chart
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($cat_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($cat_data); ?>,
                backgroundColor: <?php echo json_encode($cat_colors); ?>,
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, usePointStyle: true, font: { size: 12 } }
                }
            }
        }
    });

    // Auto update live stats for Dashboard
    function updateLiveStats() {
        fetch('<?php echo BASE_URL; ?>/ajax/get_live_stats.php')
            .then(res => res.json())
            .then(data => {
                if(data.dashboard) {
                    const prodEl = document.getElementById('live_total_products');
                    if(prodEl) prodEl.innerText = data.dashboard.total_products;
                    
                    const lowEl = document.getElementById('live_low_stock');
                    if(lowEl) lowEl.innerText = data.dashboard.low_stock_count;
                    
                    const stockEl = document.getElementById('live_total_stock');
                    if(stockEl) stockEl.innerText = data.dashboard.total_stock;
                }
            })
            .catch(err => console.error('Error fetching live stats:', err));
    }
    // Poll every 5 seconds
    setInterval(updateLiveStats, 5000);

</script>

<?php require_once '../includes/footer.php'; ?>
