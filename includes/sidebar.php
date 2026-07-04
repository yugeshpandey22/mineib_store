<?php
// includes/sidebar.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/storemanagement');
}
?>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-store"></i> mineib
        </div>
        <ul class="sidebar-menu">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            $pages = [
                'dashboard.php' => ['icon' => 'fa-home', 'title' => 'Dashboard'],
                'billing.php' => ['icon' => 'fa-desktop', 'title' => 'POS Billing'],
                'index.php' => ['icon' => 'fa-box', 'title' => 'Products'], // Needs refinement if multiple index.php exist, but simple mapping for now
                'categories/index.php' => ['icon' => 'fa-tags', 'title' => 'Categories'],
                'customers/index.php' => ['icon' => 'fa-users', 'title' => 'Customers'],
                'suppliers/index.php' => ['icon' => 'fa-truck', 'title' => 'Suppliers'],
                'purchases/index.php' => ['icon' => 'fa-download', 'title' => 'Purchases'],
                'stock.php' => ['icon' => 'fa-warehouse', 'title' => 'Inventory'],
                'orders/index.php' => ['icon' => 'fa-shopping-cart', 'title' => 'Orders'],
                'invoice.php' => ['icon' => 'fa-file-invoice', 'title' => 'Invoice'],
                'sales.php' => ['icon' => 'fa-chart-line', 'title' => 'Reports'],
                'users/index.php' => ['icon' => 'fa-user-tie', 'title' => 'Users'],
                'settings.php' => ['icon' => 'fa-cog', 'title' => 'Settings']
            ];
            ?>
            <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>/pos/billing.php" class="<?php echo $current_page == 'billing.php' ? 'active' : ''; ?>"><i class="fas fa-desktop"></i> POS Billing</a></li>
            <li><a href="<?php echo BASE_URL; ?>/products/index.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="<?php echo BASE_URL; ?>/categories/index.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="<?php echo BASE_URL; ?>/customers/index.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="<?php echo BASE_URL; ?>/suppliers/index.php"><i class="fas fa-truck"></i> Suppliers</a></li>
            <li><a href="<?php echo BASE_URL; ?>/purchases/index.php"><i class="fas fa-download"></i> Purchases</a></li>
            <li><a href="<?php echo BASE_URL; ?>/inventory/stock.php" class="<?php echo $current_page == 'stock.php' ? 'active' : ''; ?>"><i class="fas fa-warehouse"></i> Inventory</a></li>
            <li><a href="<?php echo BASE_URL; ?>/orders/index.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="<?php echo BASE_URL; ?>/reports/sales.php" class="<?php echo $current_page == 'sales.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Reports</a></li>
            <li><a href="<?php echo BASE_URL; ?>/users/index.php"><i class="fas fa-user-tie"></i> Users</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
        <div class="sidebar-user">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'Admin'); ?>&background=0d6efd&color=fff" alt="User">
            <div class="sidebar-user-info">
                <h4><?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Super Admin')); ?></h4>
                <p>Online</p>
            </div>
        </div>
    </aside>
