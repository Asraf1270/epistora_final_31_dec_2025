<?php
// /epistora/user/login/index.php
// Full login page with CSRF protection COMPLETELY REMOVED
// Matches the register page style and functionality

declare(strict_types=1);

// === Force writable session directory for shared hosting ===
$sessions_dir = __DIR__ . '/../../cache/sessions';
if (!is_dir($sessions_dir)) {
    @mkdir($sessions_dir, 0777, true);
}
ini_set('session.save_path', $sessions_dir);
ini_set('session.gc_maxlifetime', '604800');
ini_set('session.cookie_lifetime', '604800');

// === Load bootstrap ===
$bootstrap_path = $_SERVER['DOCUMENT_ROOT'] . '/epistora/bootstrap.php';
if (!file_exists($bootstrap_path)) {
    $bootstrap_path = dirname(__DIR__, 3) . '/bootstrap.php';
}
require_once $bootstrap_path;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /epistora/user/login/index.php');
    exit;
}

// Set JSON header for AJAX responses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
}

// ==================== HANDLE LOGIN ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Optional rate limiting
    if (function_exists('rate_limit') && rate_limit('login', RATE_LIMIT_LOGIN_ATTEMPTS, RATE_LIMIT_WINDOW)) {
        http_response_code(429);
        exit(json_encode(['error' => 'Too many login attempts. Please wait 15 minutes.']));
    }

    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        exit(json_encode(['error' => 'Please enter both username/email and password.']));
    }

    // Load users
    $users = json_read(USERS_INDEX, true);

    $user_id = null;
    $user_data = null;

    foreach ($users as $id => $user) {
        if (strtolower($user['username']) === strtolower($identifier) ||
            strtolower($user['email']) === strtolower($identifier)) {
            $user_id = $id;
            $user_data = $user;
            break;
        }
    }

    if (!$user_id || !password_verify($password, $user_data['password_hash'])) {
        security_log('failed_login', ['identifier' => $identifier]);
        exit(json_encode(['error' => 'Invalid username/email or password.']));
    }

    // Load private vault
    $vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
    $vault = json_read($vault_file, true);

    if (!$vault) {
        exit(json_encode(['error' => 'Failed to load user profile.']));
    }

    // Set session
    $_SESSION['user_id']   = $user_id;
    $_SESSION['username']  = $user_data['username'];
    $_SESSION['role']      = $user_data['role'];
    $_SESSION['vault']     = $vault;

    session_regenerate_id(true);

    security_log('user_login', ['user_id' => $user_id]);

    exit(json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting...'
    ]));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login • <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="/epistora/assets/css/style.css">
    <style>
        .auth-box {
            max-width: 440px;
            margin: 4rem auto;
            padding: 2.5rem;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .logo {
            text-align: center;
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .tagline {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
        }
        .form-group {
            margin-bottom: 1.4rem;
        }
        label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 500;
            color: #444;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
        }
        input:focus {
            outline: none;
            border-color: #5d8ffc;
            box-shadow: 0 0 0 4px rgba(93,143,252,0.15);
        }
        .btn {
            width: 100%;
            padding: 1.1rem;
            margin-top: 1rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, #5d8ffc, #3a6ff8);
            color: white;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(93,143,252,0.4);
        }
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            text-align: center;
        }
        .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .footer-link {
            text-align: center;
            margin-top: 2rem;
            color: #666;
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #e74c3c;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php if (is_logged_in()): ?>
        <a href="?logout=1" class="logout-link">Logout</a>
    <?php endif; ?>

    <div class="auth-box">
        <div class="logo"><?= SITE_NAME ?></div>
        <div class="tagline">Welcome back! Log in to your account</div>

        <div id="message"></div>

        <form id="loginForm" method="POST">
            <!-- NO CSRF FIELD -->

            <div class="form-group">
                <label for="identifier">Username or Email</label>
                <input type="text" name="identifier" id="identifier" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn">Log In</button>
        </form>

        <div class="footer-link">
            Don't have an account? <a href="/epistora/user/register/index.php">Register here</a>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('message');
        const form = document.getElementById('loginForm');
        const submitBtn = form.querySelector('button');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            messageBox.innerHTML = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';

            const formData = new FormData(form);

            try {
                const response = await fetch('/epistora/user/login/index.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageBox.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => {
                        window.location.href = '/epistora/';
                    }, 1200);
                } else {
                    messageBox.innerHTML = `<div class="alert alert-error">${result.error}</div>`;
                }
            } catch (err) {
                messageBox.innerHTML = `<div class="alert alert-error">Connection failed. Try again.</div>`;
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Log In';
        });
    </script>
</body>
</html>