<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();


define('BASE_URL', '/storemanagement');
$page_title = 'System Settings';

$message = "";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $store_name = trim($_POST['store_name'] ?? '');
    $gstin = trim($_POST['gstin'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $footer_msg = trim($_POST['footer_msg'] ?? '');

    $update = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    $update->execute([$store_name, 'store_name']);
    $update->execute([$gstin, 'gstin']);
    $update->execute([$phone, 'phone']);
    $update->execute([$email, 'email']);
    $update->execute([$address, 'address']);
    $update->execute([$footer_msg, 'footer_msg']);

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['logo']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            if (!is_dir('../assets')) {
                mkdir('../assets', 0777, true);
            }
            if (file_exists('../assets/logo.png')) unlink('../assets/logo.png');
            if (file_exists('../assets/logo.jpg')) unlink('../assets/logo.jpg');
            
            $new_name = 'logo.' . ($ext === 'jpg' || $ext === 'jpeg' ? 'jpg' : 'png');
            move_uploaded_file($_FILES['logo']['tmp_name'], '../assets/' . $new_name);
        }
    }

    $message = "Settings updated successfully!";
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings_rows = $stmt->fetchAll();
$settings = [];
foreach($settings_rows as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
require_once '../includes/navbar.php';
?>

<div class="panel-card" style="max-width: 800px; margin: 0 auto;">
    <h3 style="font-size: 18px; color: #111c43; margin-bottom: 20px; border-bottom: 1px solid #f0f2f8; padding-bottom: 15px;">Store Configuration</h3>
    
    <?php if($message): ?>
        <div class="alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Store Name (Franchise)</label>
                <input type="text" name="store_name" class="form-control" value="<?php echo htmlspecialchars($settings['store_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label class="form-label">GSTIN Number</label>
                <input type="text" name="gstin" class="form-control" value="<?php echo htmlspecialchars($settings['gstin'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Contact Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($settings['phone'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">Store Address</label>
            <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($settings['address'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">Receipt Footer Message</label>
            <input type="text" name="footer_msg" class="form-control" value="<?php echo htmlspecialchars($settings['footer_msg'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">Store Logo</label>
            <div style="display: flex; align-items: center; gap: 15px;">
                <?php if(file_exists('../assets/logo.png')): ?>
                    <img src="../assets/logo.png?v=<?php echo time(); ?>" style="height: 50px; border-radius: 4px; border: 1px solid #ddd;">
                <?php elseif(file_exists('../assets/logo.jpg')): ?>
                    <img src="../assets/logo.jpg?v=<?php echo time(); ?>" style="height: 50px; border-radius: 4px; border: 1px solid #ddd;">
                <?php endif; ?>
                <input type="file" name="logo" class="form-control" accept="image/*" style="width: auto; flex: 1;">
            </div>
            <small style="color: #666; display: block; margin-top: 5px;">Upload a logo for your receipts (PNG/JPG). Leave blank to keep current.</small>
        </div>
        
        <div style="margin-top: 30px; text-align: right;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
