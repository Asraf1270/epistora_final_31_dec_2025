<?php
require_once '../../config.php';
require_once '../../db_engine.php';

$message = "";
$message_type = ""; // 'error' or 'success'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = strip_tags($_POST['name']);
    $email    = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $pass     = $_POST['password'];
    $re_pass  = $_POST['re_password'];

    // 1. Validation Logic
    $users = DBEngine::readJSON("users.json") ?? [];

    if ($pass !== $re_pass) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (strlen($pass) < 8) {
        $message = "Password must be at least 8 characters.";
        $message_type = "error";
    } elseif (isset($users[$email])) {
        $message = "This email is already registered.";
        $message_type = "error";
    } else {
        // 2. Generate Unique User ID
        $user_id = "U-" . bin2hex(random_bytes(4)) . "-" . time();

        // 3. Update Global User Index
        $users[$email] = [
            "id"       => $user_id,
            "name"     => $name,
            "password" => password_hash($pass, PASSWORD_DEFAULT),
            "role"     => ROLE_USER 
        ];
        DBEngine::writeJSON("users.json", $users);

        // 4. Initialize Private Vault
        if (DBEngine::initVault($user_id)) {
            $vault = DBEngine::readJSON("user_data/$user_id.json");
            $vault['profile']['name']  = $name;
            $vault['profile']['email'] = $email;
            $vault['role']             = ROLE_USER; 
            
            DBEngine::writeJSON("user_data/$user_id.json", $vault);

            $message = "Account created! <a href='../login/'>Login here</a>";
            $message_type = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Epistora</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg-color: #f8fafc;
            --border: #e2e8f0;
            --radius: 8px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .auth-card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            box-sizing: border-box;
        }

        h2 {
            margin: 0 0 8px 0;
            font-size: 1.5rem;
            text-align: center;
            color: var(--text-main);
        }

        p.subtitle {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 32px;
        }

        .form-group { margin-bottom: 18px; }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 6px;
            color: var(--text-main);
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-sizing: border-box;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }

        .btn-primary:hover { background: var(--primary-hover); }

        /* Status Messages */
        .msg {
            padding: 12px;
            border-radius: var(--radius);
            font-size: 0.9rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .msg-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fee2e2;
        }
        .msg-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #dcfce7;
        }
        .msg-success a {
            color: #15803d;
            font-weight: 600;
            text-decoration: underline;
        }

        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .footer-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="auth-card">
    <h2>Create Account</h2>
    <p class="subtitle">Join the Epistora writing community</p>

    <?php if ($message): ?>
        <div class="msg msg-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter your name" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="re_password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary">Register</button>
    </form>

    <div class="footer-link">
        Already have an account? <a href="../../user/login/">Sign in</a>
    </div>
</div>

</body>
</html>