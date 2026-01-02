<?php
session_start();
require_once '../../config.php';
require_once '../../db_engine.php';

$user_id = $_SESSION['user_id'];
$user = DBEngine::readJSON("user_data/$user_id.json");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Account Settings | Epistora</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 50px;">
        <div class="post-card">
            <h2>Account Settings</h2>
            <form action="update.php" method="POST">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label>Bio (Short Description)</label>
                    <textarea name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 2rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <label>New Password (Leave blank to keep current)</label>
                    <input type="password" name="new_password" placeholder="Min 8 characters">
                </div>
                <button type="submit" class="btn-apply" style="width: 100%;">Update My Profile</button>
            </form>
        </div>
    </div>
</body>
</html>