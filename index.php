<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Open Sans', sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #111;
        }

        /* Navbar */
        .navbar {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 20px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 100;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: 800;
            color: #222;
            text-decoration: none;
            letter-spacing: 0.5px;
        }

        .navbar-brand span {
            color: #007bff;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .navbar-menu a {
            text-decoration: none;
            color: #444;
            font-size: 16px;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .navbar-menu a:hover {
            color: #007bff;
        }

        .btn-login-outline {
            border: 2px solid #007bff;
            padding: 10px 24px;
            border-radius: 6px;
            color: #007bff !important;
            font-weight: 700;
        }

        .btn-login-outline:hover {
            background-color: #007bff;
            color: #fff !important;
        }

        /* Hero Section */
        .hero-section {
            flex: 1;
            position: relative;
            background-image: url('hero_bg.png'); /* The user's requested background */
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-top: 70px; /* Space for fixed navbar */
        }

        /* Dark overlay to make the image visible but text readable */
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
            padding: 0 20px;
        }

        .hero-content h1 {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 40px;
            color: #eee;
            text-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        .btn-login-primary {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 6px 15px rgba(0, 123, 255, 0.4);
            border: 2px solid #007bff;
        }

        .btn-login-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.5);
        }

        /* Hover Footer Watermark */
        .hover-footer {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20vw;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.08);
            font-family: 'Open Sans', sans-serif;
            letter-spacing: -4px;
            transition: all 0.5s ease-in-out;
            z-index: 1;
            white-space: nowrap;
            user-select: none;
            cursor: default;
            line-height: 1;
            pointer-events: none;
        }

        .hero-section:hover .hover-footer {
            color: rgba(255, 255, 255, 0.15);
            bottom: -10px;
        }

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            color: #bbb;
            text-align: center;
            padding: 20px 0;
            font-size: 14px;
            z-index: 10;
            position: relative;
        }
        
        .footer span {
            color: #007bff;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .hero-content h1 { font-size: 36px; }
            .hero-content p { font-size: 16px; }
            .navbar { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="index.php" class="navbar-brand">Store <span>Management</span></a>
        <div class="navbar-menu">
            <a href="index.php">Home</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="admin/dashboard.php" class="btn-login-outline">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn-login-outline">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        
        <div class="hero-content">
            <h1>Store Management System</h1>
            <p>Smart, simple, and powerful POS & Inventory management</p>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="admin/dashboard.php" class="btn-login-primary">Go to Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn-login-primary">Login Now</a>
            <?php endif; ?>
        </div>
        
        <!-- Hover Footer Watermark -->
        <div class="hover-footer">
            amadika
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        © <?php echo date('Y'); ?> Copyright <span>mineib</span> | All rights reserved.
    </footer>

</body>
</html>
