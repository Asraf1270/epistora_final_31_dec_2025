<?php
// /epistora/user/login/index.php
// User login form and backend handler (includes logout handling)

declare(strict_types=1);

require_once '../../bootstrap.php';

if (!defined('BASE_PATH')) exit;

// Handle logout first (via GET ?logout=1)
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: /user/login/');
    exit;
}

// Handle POST login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit login attempts
    if (rate_limit('login', RATE_LIMIT_LOGIN_ATTEMPTS)) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many login attempts. Try again later.']);
        exit;
    }

    // CSRF validation
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token.']);
        exit;
    }

    // Sanitize inputs
    $identifier = sanitize($_POST['identifier'] ?? ''); // Username or email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        echo json_encode(['error' => 'Invalid input.']);
        exit;
    }

    // Load users index
    $users = json_read(USERS_INDEX, true);

    // Find user by username or email
    $user_id = null;
    $user_data = null;
    foreach ($users as $id => $user) {
        if ($user['username'] === $identifier || $user['email'] === $identifier) {
            $user_id = $id;
            $user_data = $user;
            break;
        }
    }

    if (!$user_id || !password_verify($password, $user_data['password_hash'])) {
        security_log('failed_login', ['identifier' => $identifier]);
        echo json_encode(['error' => 'Invalid credentials.']);
        exit;
    }

    // Load private user vault into session
    $vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
    $vault = json_read($vault_file, true);

    if (!$vault) {
        echo json_encode(['error' => 'Failed to load user data.']);
        exit;
    }

    // Set session data
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['role'] = $user_data['role'];
    $_SESSION['vault'] = $vault; // Full vault in session for quick access

    // Regenerate session ID for security
    session_regenerate_id(true);

    // Log event
    security_log('user_login', ['user_id' => $user_id]);

    // Success (redirect to homepage or dashboard)
    echo json_encode(['success' => true, 'redirect' => '/']);
    exit;
}

// Display login form (GET)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - <?= SITE_NAME ?></title>
    <!-- Add CSS links here -->
</head>
<body>
    <h1>Login</h1>
    <form id="login-form" method="POST" action="">
        <?= csrf_field() ?>
        <label>Username or Email: <input type="text" name="identifier" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Login</button>
    </form>
    <a href="?logout=1">Logout</a> <!-- Simple logout link -->

    <script>
        // AJAX submission
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.error);
            }
        });
    </script>
</body>
</html>