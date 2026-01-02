<?php require 'header.php'; ?>
<h2>Security Logs</h2>
<pre style="background:#fff;padding:15px;overflow:auto;max-height:600px;">
<?= htmlspecialchars(file_get_contents(LOGS_PATH . '/security.log') ?: 'No logs yet.') ?>
</pre>
</body></html>