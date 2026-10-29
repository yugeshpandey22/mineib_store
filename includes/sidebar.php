<?php
// includes/sidebar.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/storemanagement');
}
?>
<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fas fa-store"></i> mineib
    </div>
    <div class="sidebar-menu-container">
        <button type="button" class="scroll-arrow left-arrow" onclick="document.getElementById('sidebarMenu').scrollLeft -= 200"><i class="fas fa-chevron-left"></i></button>
        <ul class="sidebar-menu" id="sidebarMenu">
            <?php
            $current_uri = $_SERVER['REQUEST_URI'];
            ?>
            <li><a href="<?php echo BASE_URL; ?>/admin/dashboard.php" class="<?php echo strpos($current_uri, '/admin/dashboard.php') !== false ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>/pos/billing.php" class="<?php echo strpos($current_uri, '/pos/') !== false ? 'active' : ''; ?>"><i class="fas fa-desktop"></i> POS Billing</a></li>
            <li><a href="<?php echo BASE_URL; ?>/products/index.php" class="<?php echo strpos($current_uri, '/products/') !== false ? 'active' : ''; ?>"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="<?php echo BASE_URL; ?>/categories/index.php" class="<?php echo strpos($current_uri, '/categories/') !== false ? 'active' : ''; ?>"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="<?php echo BASE_URL; ?>/customers/index.php" class="<?php echo strpos($current_uri, '/customers/') !== false ? 'active' : ''; ?>"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="<?php echo BASE_URL; ?>/suppliers/index.php" class="<?php echo strpos($current_uri, '/suppliers/') !== false ? 'active' : ''; ?>"><i class="fas fa-truck"></i> Suppliers</a></li>
            <li><a href="<?php echo BASE_URL; ?>/purchases/index.php" class="<?php echo strpos($current_uri, '/purchases/') !== false ? 'active' : ''; ?>"><i class="fas fa-download"></i> Purchases</a></li>
            <li class="menu-header" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center; padding-right: 20px;" onclick="toggleInventoryMenu()">
                <span>Inventory</span>
                <i class="fas fa-chevron-down" id="inventory-chevron" style="transition: transform 0.3s; <?php echo (strpos($current_uri, '/inventory/') !== false) ? 'transform: rotate(180deg);' : ''; ?>"></i>
            </li>
            <div id="inventory-submenu" style="display: <?php echo (strpos($current_uri, '/inventory/') !== false) ? 'block' : 'none'; ?>;">
                <li><a href="<?php echo BASE_URL; ?>/inventory/stock.php" class="<?php echo strpos($current_uri, '/inventory/stock.php') !== false ? 'active' : ''; ?>"><i class="fas fa-boxes"></i> Stock</a></li>
                <li><a href="<?php echo BASE_URL; ?>/inventory/damaged.php" class="<?php echo strpos($current_uri, '/inventory/damaged.php') !== false ? 'active' : ''; ?>"><i class="fas fa-undo-alt"></i> Damaged / Returns</a></li>
            </div>
            <li><a href="<?php echo BASE_URL; ?>/orders/index.php" class="<?php echo (strpos($current_uri, '/orders/') !== false && strpos($current_uri, 'sales.php') === false) ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="<?php echo BASE_URL; ?>/reports/sales.php" class="<?php echo strpos($current_uri, '/reports/') !== false ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Reports</a></li>
            <li><a href="<?php echo BASE_URL; ?>/users/index.php" class="<?php echo strpos($current_uri, '/users/') !== false ? 'active' : ''; ?>"><i class="fas fa-user-tie"></i> Users</a></li>
            <li><a href="<?php echo BASE_URL; ?>/admin/settings.php" class="<?php echo strpos($current_uri, '/admin/settings.php') !== false ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
        <button type="button" class="scroll-arrow right-arrow" onclick="document.getElementById('sidebarMenu').scrollLeft += 200"><i class="fas fa-chevron-right"></i></button>
    </div>
    <div class="sidebar-user">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'Admin'); ?>&background=0d6efd&color=fff" alt="User">
        <div class="sidebar-user-info">
            <h4><?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'Super Admin')); ?></h4>
            <p>YUGESH PARI</p>
        </div>
    </div>
</aside>

<script>
function toggleInventoryMenu() {
    var submenu = document.getElementById('inventory-submenu');
    var chevron = document.getElementById('inventory-chevron');
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
    } else {
        submenu.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
    }
}
</script>