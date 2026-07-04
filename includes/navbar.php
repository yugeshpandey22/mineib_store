<?php
// includes/navbar.php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/storemanagement');
}
?>
    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="page-title">
                <i class="fas fa-bars" style="color: #8f9bba; margin-right: 15px; cursor: pointer; display: none;"></i> <!-- Mobile menu icon placeholder -->
                <h2><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
            </div>
            
            <div class="nav-search">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search here...">
            </div>
            
            <div class="nav-actions">
                <a href="<?php echo BASE_URL; ?>/index.php" title="Go to Website Home" style="color: #8f9bba; text-decoration: none; font-size: 14px; font-weight: 600; margin-right: 20px; display: flex; align-items: center; gap: 8px; border: 1px solid #e0e5f2; padding: 6px 12px; border-radius: 6px; background: #fff; transition: all 0.3s ease;"><i class="fas fa-home" style="font-size: 16px;"></i> View Home Page</a>
                <i class="fas fa-bell"></i>
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars(ucfirst($_SESSION['username'] ?? 'User')); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'admin')); ?></div>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/logout.php" title="Logout" style="color: #ff4b4b; text-decoration: none; font-size: 15px; font-weight: 600; margin-left: 15px; display: flex; align-items: center; gap: 8px;"><i class="fas fa-sign-out-alt" style="font-size: 18px;"></i> Logout</a>
            </div>
        </nav>
