<?php
// /epistora/user/register/index.php
// Registration page with CSRF protection REMOVED (as requested)

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

// Set JSON header for AJAX responses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
}

// ==================== HANDLE REGISTRATION ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Optional rate limiting (keep if you have it)
    if (function_exists('rate_limit_register') && rate_limit_register()) {
        http_response_code(429);
        exit(json_encode(['error' => 'Too many attempts. Please wait.']));
    }

    $username = trim($_POST['username'] ?? '');
    $email    = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation
    $errors = [];
    if (strlen($username) < 3 || strlen($username) > 30 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username: 3–30 characters, letters, numbers, underscore only.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        exit(json_encode(['error' => implode('<br>', $errors)]));
    }

    // Check for existing user
    $users = json_read(USERS_INDEX, true);
    foreach ($users as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            $errors[] = 'Username already taken.';
        }
        if (strtolower($user['email']) === $email) {
            $errors[] = 'Email already registered.';
        }
    }

    if (!empty($errors)) {
        exit(json_encode(['error' => implode('<br>', $errors)]));
    }

    // Create user
    $user_id = get_next_id(USERS_INDEX);

    $users[$user_id] = [
        'username'      => $username,
        'email'         => $email,
        'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
        'role'          => ROLES['user'],
        'created_at'    => time(),
        'verified'      => false
    ];

    if (!json_write(USERS_INDEX, $users)) {
        exit(json_encode(['error' => 'Failed to save account. Please try again.']));
    }

    // Create private vault
    $vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
    $vault = [
        'preferences' => [
            'theme_data' => [
                '--bg'      => '#ffffff',
                '--text'    => '#000000',
                '--accent'  => '#5d8ffc',
                '--card-bg' => '#f8f9fc',
                '--border'  => '#e0e6ed'
            ],
            'privacy' => 'public'
        ],
        'notifications' => [],
        'follows'       => [],
        'bookmarks'     => []
    ];

    if (!json_write($vault_file, $vault)) {
        unset($users[$user_id]);
        json_write(USERS_INDEX, $users);
        exit(json_encode(['error' => 'Failed to create profile.']));
    }

    security_log('user_registered', ['user_id' => $user_id, 'username' => $username]);

    exit(json_encode([
        'success' => true,
        'message' => 'Account created! Redirecting to login...'
    ]));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register • <?= SITE_NAME ?></title>
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
    </style>
</head>
<body>
    <div class="auth-box">
        <div class="logo"><?= SITE_NAME ?></div>
        <div class="tagline">Join the community — create your account</div>

        <div id="message"></div>

        <form id="registerForm" method="POST">
            <!-- NO CSRF FIELD ANYMORE -->

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required minlength="3" maxlength="30" autocomplete="username">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn">Create Account</button>
        </form>

        <div class="footer-link">
            Already have an account? <a href="/epistora/user/login/index.php">Log in</a>
        </div>
    </div>

    <script>
        const messageBox = document.getElementById('message');
        const form = document.getElementById('registerForm');
        const submitBtn = form.querySelector('button');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            messageBox.innerHTML = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';

            const formData = new FormData(form);

            try {
                const response = await fetch('/epistora/user/register/index.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageBox.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    setTimeout(() => {
                        window.location.href = '/epistora/user/login/index.php';
                    }, 1500);
                } else {
                    messageBox.innerHTML = `<div class="alert alert-error">${result.error}</div>`;
                }
            } catch (err) {
                messageBox.innerHTML = `<div class="alert alert-error">Connection error. Try again.</div>`;
            }

            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
        });
    </script>
</body>
</html>