<?php
$activeNav = $activeNav ?? '';
$showProfileLinkInNav = $showProfileLinkInNav ?? false;
$logged_in = $logged_in ?? isset($_SESSION['user_id']);

function nav_is_active(string $item, string $activeNav): string
{
    return $item === $activeNav ? ' class="active"' : '';
}
?>
<nav>
    <a href="index.php" class="logo">Safe<span>Night</span></a>
    <ul class="nav-links">
        <li><a href="index.php"<?= nav_is_active('home', $activeNav); ?>>Home</a></li>
        <li><a href="map.php"<?= nav_is_active('map', $activeNav); ?>>Safe Map</a></li>
        <li><a href="trips.php"<?= nav_is_active('trips', $activeNav); ?>>My Trips</a></li>
        <li><a href="contacts.php"<?= nav_is_active('contacts', $activeNav); ?>>Contacts</a></li>
        <li><a href="sos.php"<?= nav_is_active('sos', $activeNav); ?>>SOS</a></li>
        <li><a href="about.php"<?= nav_is_active('about', $activeNav); ?>>About</a></li>
    </ul>
    <div class="nav-buttons">
        <?php if ($logged_in): ?>
            <?php if ($showProfileLinkInNav && isset($_SESSION['user_name'])): ?>
                <a href="profile.php" class="profile-link">
                    <div class="profile-avatar">
                        <?= htmlspecialchars(strtoupper(substr($_SESSION['user_name'], 0, 1))); ?>
                    </div>
                    <?= htmlspecialchars($_SESSION['user_name']); ?>
                </a>
            <?php endif; ?>
            <a href="logout.php" class="btn-outline">Logout</a>
            <?php if (!$showProfileLinkInNav): ?>
                <a href="dashboard.php" class="btn-solid">Dashboard</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn-outline">Login</a>
            <a href="register.php" class="btn-solid">Register</a>
        <?php endif; ?>
    </div>
</nav>
