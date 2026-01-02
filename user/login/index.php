<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass  = $_POST['password'];

    $users = DBEngine::readJSON("users.json");

    if (isset($users[$email]) && password_verify($pass, $users[$email]['password'])) {
        // Create Session State
        $_SESSION['user_id']   = $users[$email]['id'];
        $_SESSION['user_name'] = $users[$email]['name'];
        $_SESSION['role']      = $users[$email]['role'];

        // Load specific vault settings
        $vault = DBEngine::readJSON("user_data/" . $_SESSION['user_id'] . ".json");
        $_SESSION['user_settings'] = $vault['settings'] ?? null;

        // SYSTEM LOG: Record successful entry
        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'AUTH_LOGIN', "User accessed account from IP: " . $_SERVER['REMOTE_ADDR']);

        header("Location: ../../index.php");
        exit;
    } else {
        $error = "The email or password you entered is incorrect.";
        // SYSTEM LOG: Record failed attempt
        DBEngine::logAction('0', 'Guest', 'AUTH_FAILURE', "Failed login attempt for email: $email");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #f8fafc; /* Lighter background to match dashboard */
            --border: #e2e8f0;
            --radius: 12px;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(79, 70, 229, 0.05) 0px, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            width: 100%;
            max-width: 400px;
        }

        .logo-area {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-area h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .logo-area p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-top: 8px;
        }

        .error-box {
            background: #fef2f2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: var(--radius);
            font-size: 0.875rem;
            margin-bottom: 24px;
            border: 1px solid #fee2e2;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s;
            box-sizing: border-box;
            background: #ffffff;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        button {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }

        button:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        button:active {
            transform: translateY(0);
        }

        .footer-text {
            margin-top: 24px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .footer-text a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-area">
        <h3>Epistora</h3>
        <p>Welcome back! Please enter your details.</p>
    </div>

    <?php if(!empty($error)): ?>
        <div class="error-box">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="name@example.com" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit">Sign In</button>
    </form>

    <div class="footer-text">
        Don't have an account? <a href="../register/">Create one now</a>
    </div>
</div>

</body>
</html>