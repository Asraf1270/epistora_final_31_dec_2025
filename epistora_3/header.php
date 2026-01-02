<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['profile']['role'] ?? 'guest';
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Epistora | Share Your Story</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary-color: #1d9bf0;
    --text-color: #0f1419;
    --text-muted: #536471;
    --bg-light: #f7f9f9;
    --header-bg: rgba(255, 255, 255, 0.95);
    --hover-bg: #f0f4f8;
    --btn-radius: 999px;
    --transition-speed: 0.3s;
}

/* Reset */
* { margin:0; padding:0; box-sizing:border-box; }

body { font-family: 'Inter', sans-serif; background: var(--bg-light); padding-top: 80px; color: var(--text-color); }

.container { max-width:1200px; margin:0 auto; padding:0 20px; }

/* Header */
.main-header {
    position: fixed; top:0; left:0; width:100%; height:80px;
    background: var(--header-bg); backdrop-filter: blur(12px);
    border-bottom:1px solid #e6e8eb; display:flex; align-items:center; justify-content:space-between;
    z-index: 2000; padding: 0 20px;
}

/* Logo */
.logo a { font-size:28px; font-weight:800; color: var(--primary-color); text-decoration:none; transition: transform 0.3s, opacity 0.3s; }
.logo a:hover { transform: scale(1.05); opacity:0.85; }

/* Desktop Nav */
.nav-links { display:flex; gap:28px; align-items:center; }
.nav-links a { font-weight:600; font-size:0.95rem; color: var(--text-color); text-decoration:none; transition: color 0.3s, transform 0.3s; }
.nav-links a:hover { color: var(--primary-color); transform: scale(1.05); }

/* Buttons */
.btn-write, .register-btn, .login-link {
    border-radius: var(--btn-radius); padding:10px 22px; font-weight:600; transition: all 0.3s; text-decoration:none; cursor:pointer;
}
.btn-write { background: var(--text-color); color:#fff; }
.btn-write:hover { transform: scale(1.05); opacity:0.85; }
.register-btn { border:1px solid var(--primary-color); color: var(--primary-color); }
.register-btn:hover { background: var(--primary-color); color:#fff; transform: scale(1.05); }
.login-link { background:#f5f5f5; color: var(--text-color); border:1px solid transparent; font-weight:500; }
.login-link:hover { background: var(--primary-color); color:#fff; border-color: var(--primary-color); transform: scale(1.05); }

/* Profile */
.user-profile-trigger { display:flex; align-items:center; gap:10px; cursor:pointer; padding:6px 12px; border-radius:var(--btn-radius); transition:0.3s; position:relative; }
.user-profile-trigger:hover { background: var(--hover-bg); transform: scale(1.05); }
.avatar-small { width:36px; height:36px; background:#ddd; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:0.85rem; color:var(--text-color); }

/* Profile Dropdown */
.profile-dropdown {
    position:absolute; top:50px; right:0; background:#fff; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,0.15);
    display:none; flex-direction:column; min-width:150px; overflow:hidden; z-index:3000;
    opacity:0; transform: translateY(-10px); transition: all 0.3s ease;
}
.profile-dropdown.show { display:flex; opacity:1; transform: translateY(0); }
.profile-dropdown a { padding:10px 16px; color: var(--text-color); font-size:0.9rem; text-decoration:none; transition: background 0.2s, transform 0.2s; }
.profile-dropdown a:hover { background: var(--hover-bg); transform: scale(1.02); }

/* Mobile toggle */
.mobile-toggle { display:none; font-size:1.8rem; cursor:pointer; transition: transform 0.3s; }
.mobile-toggle:hover { transform: scale(1.2); }

/* Mobile sidebar */
.mobile-sidebar {
    position:fixed; top:0; right:-100%; width:250px; height:100vh; background:#fff;
    box-shadow:-4px 0 20px rgba(0,0,0,0.15); display:flex; flex-direction:column; padding:80px 20px; z-index:2500;
    transition: right 0.4s cubic-bezier(0.77, 0, 0.175, 1);
}
.mobile-sidebar.show { right:0; }
.mobile-sidebar a { margin-bottom:16px; font-weight:600; color: var(--text-color); text-decoration:none; transition: color 0.3s, transform 0.3s; }
.mobile-sidebar a:hover { color: var(--primary-color); transform: scale(1.05); }

/* Overlay */
.sidebar-overlay {
    position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.3); opacity:0; visibility:hidden;
    transition: all 0.4s ease;
    z-index:2400;
}
.sidebar-overlay.show { opacity:1; visibility:visible; }

/* Responsive */
@media (max-width:768px) {
    .nav-links, .auth-actions { display:none; }
    .mobile-toggle { display:block; }
}
</style>
</head>
<body>

<header class="main-header">
    <div class="logo"><a href="/index.php">Epistora</a></div>

    <!-- Desktop Nav -->
    <nav class="nav-links">
        <a href="/index.php">Explore</a>
        <a href="#">Trending</a>
        <?php if($userRole==='admin'): ?>
            <a href="/admin/dashboard.php" style="color:#d32f2f;">Admin Panel</a>
        <?php endif; ?>
    </nav>

    <!-- Desktop Actions -->
    <div class="auth-actions">
        <?php if($isLoggedIn): ?>
            <?php if(in_array($userRole,['writer','v_writer','admin'])): ?>
                <a href="/post/create/index.php" class="btn-write">Write</a>
            <?php endif; ?>
            <div class="user-profile-trigger" onclick="toggleProfileDropdown()">
                <div class="avatar-small"><?php echo strtoupper(substr($username,0,1)); ?></div>
                <span><?php echo htmlspecialchars($username); ?></span>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="/profile/index.php">Profile</a>
                    <a href="/profile/settings.php">Settings</a>
                    <a href="/logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="/login/index.php" class="login-link">Sign In</a>
            <a href="/register/index.php" class="register-btn">Get Started</a>
        <?php endif; ?>
    </div>

    <!-- Mobile toggle -->
    <div class="mobile-toggle" onclick="toggleMobileSidebar()">&#9776;</div>
</header>

<!-- Mobile Sidebar -->
<div class="mobile-sidebar" id="mobileSidebar">
    <a href="/index.php">Explore</a>
    <a href="#">Trending</a>
    <?php if($userRole==='admin'): ?>
        <a href="/admin/dashboard.php" style="color:#d32f2f;">Admin Panel</a>
    <?php endif; ?>
    <hr style="margin:10px 0;">
    <?php if($isLoggedIn): ?>
        <?php if(in_array($userRole,['writer','v_writer','admin'])): ?>
            <a href="/post/create/index.php" class="btn-write">Write</a>
        <?php endif; ?>
        <a href="/profile/index.php">Profile</a>
        <a href="/profile/settings.php">Settings</a>
        <a href="/logout.php" class="login-link">Logout</a>
    <?php else: ?>
        <a href="/login/index.php" class="login-link">Sign In</a>
        <a href="/register/index.php" class="register-btn">Get Started</a>
    <?php endif; ?>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<script>
    // Profile Dropdown toggle
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }
    document.addEventListener('click', function(e){
        const dropdown = document.getElementById('profileDropdown');
        const trigger = document.querySelector('.user-profile-trigger');
        if(!trigger.contains(e.target)) dropdown.classList.remove('show');
    });

    // Mobile Sidebar toggle
    function toggleMobileSidebar() {
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
    function closeSidebar() {
        document.getElementById('mobileSidebar').classList.remove('show');
        document.getElementById('sidebarOverlay').classList.remove('show');
    }
</script>
