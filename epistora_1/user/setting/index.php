<?php
// /epistora/user/setting/index.php
// Personalization: theme colors (CSS variables), privacy

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

if (!is_logged_in()) {
    header('Location: /user/login/');
    exit;
}

$user_id = $_SESSION['user_id'];
$vault_file = USER_DATA_DIR . '/' . $user_id . '.json';
$vault = $_SESSION['vault'] ?? json_read($vault_file, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token';
    } else {
        $theme_data = [
            '--bg'         => sanitize($_POST['bg'] ?? '#ffffff'),
            '--text'       => sanitize($_POST['text'] ?? '#000000'),
            '--accent'     => sanitize($_POST['accent'] ?? '#0066cc'),
            '--card-bg'    => sanitize($_POST['card_bg'] ?? '#f9f9f9'),
            '--border'     => sanitize($_POST['border'] ?? '#dddddd'),
        ];

        $privacy = in_array($_POST['privacy'] ?? '', ['public', 'followers', 'private'])
            ? $_POST['privacy']
            : 'public';

        $new_vault = $vault;
        $new_vault['preferences']['theme_data'] = $theme_data;
        $new_vault['preferences']['privacy'] = $privacy;

        if (json_write($vault_file, $new_vault)) {
            $_SESSION['vault'] = $new_vault; // Update session
            $success = 'Settings saved!';
        } else {
            $error = 'Failed to save settings';
        }
    }
}

// Load current values
$theme = $vault['preferences']['theme_data'] ?? [
    '--bg'      => '#ffffff',
    '--text'    => '#000000',
    '--accent'  => '#0066cc',
    '--card-bg' => '#f9f9f9',
    '--border'  => '#dddddd'
];
$privacy = $vault['preferences']['privacy'] ?? 'public';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Settings - <?= SITE_NAME ?></title>
    <style>
        :root {
            <?php foreach ($theme as $var => $val): ?>
            <?= $var ?>: <?= htmlspecialchars($val) ?>;
            <?php endforeach; ?>
        }
        body { font-family: sans-serif; padding: 20px; }
        input[type="color"] { width: 60px; height: 40px; }
        label { display: block; margin: 15px 0 5px; }
    </style>
</head>
<body>
    <h1>Personalization Settings</h1>
    <a href="/user/profile/?u=<?= urlencode($_SESSION['username']) ?>">← Back to Profile</a>

    <?php if (isset($success)): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>
    <?php if (isset($error)): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>

    <form method="POST">
        <?= csrf_field() ?>

        <h2>Theme Colors</h2>
        <label>Background <input type="color" name="bg" value="<?= htmlspecialchars($theme['--bg']) ?>"></label>
        <label>Text Color <input type="color" name="text" value="<?= htmlspecialchars($theme['--text']) ?>"></label>
        <label>Accent / Links <input type="color" name="accent" value="<?= htmlspecialchars($theme['--accent']) ?>"></label>
        <label>Card Background <input type="color" name="card_bg" value="<?= htmlspecialchars($theme['--card-bg']) ?>"></label>
        <label>Border Color <input type="color" name="border" value="<?= htmlspecialchars($theme['--border']) ?>"></label>

        <h2>Privacy</h2>
        <label>
            <select name="privacy">
                <option value="public" <?= $privacy === 'public' ? 'selected' : '' ?>>Public (anyone can see profile)</option>
                <option value="followers" <?= $privacy === 'followers' ? 'selected' : '' ?>>Followers only</option>
                <option value="private" <?= $privacy === 'private' ? 'selected' : '' ?>>Private (only you)</option>
            </select>
        </label>

        <br><br>
        <button type="submit">Save Settings</button>
    </form>

    <h3>Live Preview</h3>
    <div style="background:var(--card-bg); border:1px solid var(--border); padding:15px; border-radius:8px;">
        <p style="color:var(--text);">This is how your site will look.</p>
        <a href="#" style="color:var(--accent);">Sample link →</a>
    </div>
</body>
</html>