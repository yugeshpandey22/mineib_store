<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

define('BASE_URL', '/storemanagement');
$page_title = 'Sales Reports';

// Handle filter
$daterange = $_GET['daterange'] ?? '';
if (!empty($daterange)) {
    $dates = explode(' - ', $daterange);
    if (count($dates) == 2) {
        $start_dt = DateTime::createFromFormat('m/d/Y', trim($dates[0]));
        $end_dt = DateTime::createFromFormat('m/d/Y', trim($dates[1]));
        if ($start_dt && $end_dt) {
            $start_date = $start_dt->format('Y-m-d');
            $end_date = $end_dt->format('Y-m-d');
        } else {
            $start_date = date('Y-m-01');
            $end_date = date('Y-m-t');
        }
    } else {
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
    }
} else {
    // Default to current month
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}
// format for the input value
$display_daterange = date('m/d/Y', strtotime($start_date)) . ' - ' . date('m/d/Y', strtotime($end_date));

$where_clause = "DATE(o.created_at) >= :start_date AND DATE(o.created_at) <= :end_date";
$params = ['start_date' => $start_date, 'end_date' => $end_date];

if ($start_date === $end_date) {
    $report_title = "Sales for " . date('d M Y', strtotime($start_date));
} else {
    $report_title = "Sales from " . date('d M Y', strtotime($start_date)) . " to " . date('d M Y', strtotime($end_date));
}

// Fetch stats for the selected filter
$stats_query = $pdo->prepare("
    SELECT 
        COUNT(o.id) as total_orders,
        SUM(o.grand_total) as total_sales,
        SUM((SELECT SUM(quantity) FROM order_items WHERE order_id = o.id)) as total_items
    FROM orders o
    WHERE $where_clause
");
$stats_query->execute($params);
$stats = $stats_query->fetch();

$total_sales = $stats['total_sales'] ?: 0;
$total_orders = $stats['total_orders'] ?: 0;
$total_items = $stats['total_items'] ?: 0;

// Fetch the actual orders for the table
$stmt = $pdo->prepare("
    SELECT o.*, c.name as customer_name 
    FROM orders o 
    LEFT JOIN customers c ON o.customer_id = c.id 
    WHERE $where_clause
    ORDER BY o.created_at DESC
");
$stmt->execute($params);
$sales_records = $stmt->fetchAll();

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<!-- Include Required Prerequisites -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!-- Include Date Range Picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<style>
.filter-form-container {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 8px;
    border: 1px solid #eef2f5;
}
.filter-field {
    flex: 1;
    min-width: 250px;
    max-width: 350px;
}
.filter-label {
    display: block; 
    font-size: 13px; 
    margin-bottom: 6px; 
    color: #495057; 
    font-weight: 600;
}
.filter-btn-container {
    flex: 0 0 auto;
}
</style>

<div class="content-header" style="margin-bottom: 20px;">
    <h2>Sales Reports & Overview</h2>
</div>

<div class="panel-card" style="margin-bottom: 20px;">
    <form method="GET" action="" class="filter-form">
        <div class="filter-form-container">
            
            <div class="filter-field">
                <label class="filter-label"><i class="far fa-calendar-alt"></i> Select Date Range</label>
                <input type="text" name="daterange" id="daterange" class="custom-input" value="<?php echo htmlspecialchars($display_daterange); ?>" style="cursor: pointer; background-color: #fff;" readonly>
            </div>

            <div class="filter-btn-container">
                <button type="submit" class="btn-primary" style="padding: 0 25px; height: 40px; border-radius: 6px;">
                    <i class="fas fa-filter"></i> Apply Filter
                </button>
            </div>
            
        </div>
    </form>
</div>

<div class="amadika-cards">
    <div class="amadika-card">
        <div class="ac-icon bg-c-blue"><i class="fas fa-chart-line"></i></div>
        <div class="ac-details">
            <div class="ac-title">Total Revenue</div>
            <div class="ac-value">₹ <?php echo number_format($total_sales, 2); ?></div>
        </div>
    </div>
    <div class="amadika-card">
        <div class="ac-icon bg-c-orange"><i class="fas fa-shopping-cart"></i></div>
        <div class="ac-details">
            <div class="ac-title">Total Orders</div>
            <div class="ac-value"><?php echo $total_orders; ?></div>
        </div>
    </div>
    <div class="amadika-card">
        <div class="ac-icon bg-c-purple"><i class="fas fa-box-open"></i></div>
        <div class="ac-details">
            <div class="ac-title">Items Sold</div>
            <div class="ac-value"><?php echo $total_items; ?></div>
        </div>
    </div>
</div>

<div class="panel-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="font-size: 16px; color: #111c43;"><?php echo htmlspecialchars($report_title); ?></h3>
    </div>
    <table class="custom-table" style="box-shadow: none;">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Date & Time</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales_records) > 0): ?>
                <?php foreach ($sales_records as $order): ?>
                    <tr>
                        <td style="font-weight: 600;">#ORD-<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 25px; height: 25px; border-radius: 50%; background: #0d6efd; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <?php echo strtoupper(substr($order['customer_name'] ?: 'W', 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($order['customer_name'] ?: 'Walk-in'); ?>
                            </div>
                        </td>
                        <td style="font-weight: 700; color: #05cd99;">₹ <?php echo number_format($order['grand_total'], 2); ?></td>
                        <td>
                            <a href="../pos/receipt.php?id=<?php echo $order['id']; ?>" target="_blank" class="btn-icon" style="text-decoration: none;" title="View Receipt"><i class="fas fa-file-invoice"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #888;">No sales data available for the selected filter.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
$(function() {
    $('#daterange').daterangepicker({
        opens: 'right',
        locale: {
            format: 'MM/DD/YYYY'
        },
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Automatically submit the form when a date is selected and applied
    $('#daterange').on('apply.daterangepicker', function(ev, picker) {
        $(this).closest('form').submit();
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
