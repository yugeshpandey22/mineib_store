<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
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
        }

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 10;
        }

        .navbar-brand {
            font-size: 20px;
            color: #333;
            text-decoration: none;
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .navbar-menu a {
            text-decoration: none;
            color: #666;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .navbar-menu a:hover {
            color: #007bff;
        }

        .btn-login-outline {
            border: 1px solid #ddd;
            padding: 8px 20px;
            border-radius: 4px;
            color: #333 !important;
        }

        .btn-login-outline:hover {
            background-color: #f8f9fa;
            border-color: #ccc;
        }

        /* Hero Section */
        .hero-section {
            flex: 1;
            position: relative;
            background-image: url('hero_bg.png'); /* Replace with the actual image */
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }



        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #222;
        }

        .hero-content h1 {
            font-size: 42px;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.3);
        }

        .btn-login-primary {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 123, 255, 0.3);
        }

        .btn-login-primary:hover {
            background-color: #0056b3;
        }

        /* Footer */
        .footer {
            background-color: #212529;
            color: #fff;
            text-align: center;
            padding: 15px 0;
            font-size: 14px;
        }

        /* Hover Footer Watermark */
        .hover-footer {
            position: absolute;
            bottom: -30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 20vw;
            font-weight: 900;
            color: rgba(255, 255, 255, 0.6); /* Clean, visible white */
            text-shadow: 0px 10px 20px rgba(0, 0, 0, 0.5); /* Strong shadow so it stands out cleanly */
            font-family: 'Open Sans', sans-serif;
            letter-spacing: -4px;
            transition: all 0.5s ease-in-out;
            z-index: 1; /* Less than hero-content so button is clickable */
            white-space: nowrap;
            user-select: none;
            cursor: default;
            line-height: 1;
            pointer-events: none; /* Do not block clicks */
        }

        .hero-section:hover .hover-footer {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0px 15px 30px rgba(0, 0, 0, 0.7);
            bottom: -10px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <header class="navbar">
        <a href="index.php" class="navbar-brand">Store Management System</a>
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
        © Copyright <span style="color: #007bff; font-weight: bold;">mineib</span> | All rights reserved.
    </footer>

</body>
</html>
