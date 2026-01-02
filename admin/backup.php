<?php
require_once 'auth_check.php';
require_once '../config.php';
require_once '../db_engine.php';

// 1. Extension Guard
$zipEnabled = class_exists('ZipArchive');

if (isset($_POST['create_backup']) && $zipEnabled) {
    $zip = new ZipArchive();
    $backup_name = "epistora_backup_" . date('Y-m-d_H-i-s') . ".zip";
    $backup_path = DATA_PATH . $backup_name;

    if ($zip->open($backup_path, ZipArchive::CREATE) === TRUE) {
        $rootPath = realpath(DATA_PATH);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                if (basename($filePath) == $backup_name) continue;
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        DBEngine::logAction($_SESSION['user_id'], $_SESSION['user_name'], 'SYSTEM_BACKUP', "Downloaded backup: $backup_name");

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $backup_name . '"');
        readfile($backup_path);
        unlink($backup_path); // Cleanup
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Backup | Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-layout" style="display:flex;">
    <?php include 'sidebar.php'; ?>

    <main style="flex:1; padding: 2rem;">
        <h1>Data Backup</h1>

        <?php if (!$zipEnabled): ?>
            <div style="background: #fff5f5; color: #c53030; padding: 1.5rem; border-radius: 8px; border: 1px solid #feb2b2; margin-bottom: 2rem;">
                <strong>⚠️ Server Limitation Detected</strong><br>
                The <code>ZipArchive</code> extension is disabled in your <code>php.ini</code>. 
                Please enable it and restart Apache to use the backup feature.
            </div>
        <?php endif; ?>

        <div class="stat-box" style="max-width: 500px; padding: 2rem; background: white; border-radius: 12px; border: 1px solid var(--border);">
            <h3>Full JSON Export</h3>
            <p>Download a compressed archive of all users, posts, and system logs.</p>
            
            <form method="POST">
                <button type="submit" name="create_backup" 
                        class="btn-apply" 
                        style="width: 100%; padding: 1rem; <?= !$zipEnabled ? 'opacity:0.5; cursor:not-allowed;' : 'cursor:pointer;' ?>"
                        <?= !$zipEnabled ? 'disabled' : '' ?>>
                    <?= $zipEnabled ? 'Download ZIP Backup' : 'Backup Unavailable' ?>
                </button>
            </form>
        </div>
    </main>
</body>
</html>