<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header("Location: admin/dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } elseif (loginUser($pdo, $username, $password)) {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Store Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            background: #fff;
        }

        /* Left side - Branding/Image */
        .login-left {
            flex: 1;
            background: url('login_bg.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .login-left h1 {
            font-size: 38px;
            font-weight: 700;
            margin-bottom: 24px;
            z-index: 2;
            text-align: center;
            line-height: 1.2;
            text-shadow: 0 2px 10px rgba(0,0,0,0.6);
            max-width: 500px;
        }

        .login-left p {
            font-size: 17px;
            opacity: 1;
            text-align: center;
            max-width: 480px;
            z-index: 2;
            line-height: 1.6;
            letter-spacing: 0.2px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.6);
        }

        /* Abstract shapes for left side */
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        .shape-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
        .shape-2 { width: 500px; height: 500px; bottom: -150px; right: -100px; }

        /* Right side - Form */
        .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            background-color: #ffffff;
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
        }

        .login-form-container h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1f36;
            margin-bottom: 10px;
        }

        .login-form-container p {
            color: #697386;
            font-size: 15px;
            margin-bottom: 35px;
        }

        .error-message {
            background-color: #fee2e2;
            border: 1px solid #ef4444;
            color: #b91c1c;
            padding: 12px 15px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #3c4257;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 15px;
            border: 1px solid #d9e2ec;
            border-radius: 8px;
            color: #1a1f36;
            background-color: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input:focus {
            border-color: #0052D4;
            box-shadow: 0 0 0 3px rgba(0, 82, 212, 0.1);
        }

        /* Fix for Webkit autofill making background white/yellowish */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus, 
        input:-webkit-autofill:active{
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }

        .btn-submit {
            width: 100%;
            background-color: #0052D4;
            color: white;
            border: none;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
            box-shadow: 0 4px 6px rgba(0, 82, 212, 0.2);
        }

        .btn-submit:hover {
            background-color: #0043ae;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-wrapper { flex-direction: column; }
            .login-left { padding: 60px 20px; }
            .login-right { padding: 40px 20px; }
        }
    </style>
</head>
<body>
 
    <div class="login-wrapper">
        <div class="login-left">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <h1>Amadika Store Management System</h1>
            <p>Manage products, inventory, billing, orders, customers, and daily business operations efficiently with the Amadika Store Management System.</p>
        </div>
        
        <div class="login-right">
            <div class="login-form-container">
                <h2>Welcome Back</h2>
                <p>Please enter your details to sign in.</p>

                <?php if($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-submit">Sign In</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
