<?php
// /epistora/user/register/index.php
// User registration form and backend handler

declare(strict_types=1);

require_once '../../bootstrap.php';

if (!defined('BASE_PATH')) exit;

// Handle POST registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Rate limit registration attempts
    if (rate_limit('register')) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many attempts. Try again later.']);
        exit;
    }

    // CSRF validation
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token.']);
        exit;
    }

    // Sanitize inputs
    $username = sanitize($_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || $password !== $confirm_password) {
        echo json_encode(['error' => 'Invalid input or passwords do not match.']);
        exit;
    }

    if (strlen($password) < 8) {
        echo json_encode(['error' => 'Password must be at least 8 characters.']);
        exit;
    }

    // Check for existing user
    $users = json_read(USERS_INDEX, true);
    foreach ($users as $user) {
        if ($user['username'] === $username || $user['email'] === $email) {
            echo json_encode(['error' => 'Username or email already exists.']);
            exit;
        }
    }

    // Get next user ID
    $user_id = get_next_id(USERS_INDEX);

    // Hash password
    $hash = password_hash($password, PASSWORD_ALGO);

    // Create user entry
    $users[$user_id] = [
        'username' => $username,
        'email' => $email,
        'password_hash' => $hash,
        'role' => ROLES['user'], // Default role
        'created_at' => time(),
        'verified' => false // For future verification
    ];

    // Atomic write to users.json
    if (!json_write(USERS_INDEX, $users)) {
        echo json_encode(['error' => 'Failed to register user.']);
        exit;
    }

    // Initialize private user vault
    $vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
    $vault_data = [
        'preferences' => [
            'theme' => 'default', // CSS variables will be set here
            'privacy' => 'public'
        ],
        'notifications' => [], // Array for user notifications
        'follows' => [], // Users this user follows
        'bookmarks' => [] // Bookmarked posts
    ];

    if (!json_write($vault_file, $vault_data)) {
        // Rollback if vault fails (simple delete for now)
        unset($users[$user_id]);
        json_write(USERS_INDEX, $users);
        echo json_encode(['error' => 'Failed to initialize user data.']);
        exit;
    }

    // Log event
    security_log('user_registered', ['user_id' => $user_id, 'username' => $username]);

    // Redirect or success (AJAX-friendly)
    echo json_encode(['success' => true, 'message' => 'Registration successful. Please log in.']);
    exit;
}

// Display registration form (GET)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf" content="<?= csrf_token() ?>">
    <title>Register - <?= SITE_NAME ?></title>
    <!-- Add CSS links here -->
</head>
<body>
    <h1>Register</h1>
    <form id="register-form" method="POST" action="">
        <?= csrf_field() ?>
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Email: <input type="email" name="email" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>Confirm Password: <input type="password" name="confirm_password" required></label><br>
        <button type="submit">Register</button>
    </form>

    <script>
        // AJAX submission (optional for better UX)
        document.getElementById('register-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) {
                alert(data.message);
                window.location.href = '/user/login/';
            } else {
                alert(data.error);
            }
        });
    </script>
</body>
</html>