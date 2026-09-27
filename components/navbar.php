<?php
$user = get_logged_in_user();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
/* ── SHARED NAVIGATION STYLES ──────────────────────── */
.site-header {
    position: sticky;
    top: 0;
    z-index: 200;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(190, 220, 248, 0.8);
    padding: 16px 32px;
}
.nav-inner {
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    max-width: 1400px;
}
.brand-logo {
    display: flex; align-items: center; gap: 8px;
    font-size: 1.25rem; font-weight: 800;
    color: #073B2A; letter-spacing: -0.02em;
    text-decoration: none;
}
.brand-icon-box {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, #073B2A, #29AB87);
    border-radius: 6px; display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem;
}
.desktop-nav { display: flex; align-items: center; gap: 16px; }
.nav-link {
    font-size: 0.9rem; font-weight: 600;
    color: #47695c; padding: 8px 12px;
    border-radius: 6px; transition: all 0.25s ease;
    text-decoration: none;
}
.nav-link:hover, .nav-link.active {
    color: #073B2A; background: #F0F8FF;
}
.nav-link-twin {
    font-size: 0.9rem; font-weight: 700;
    color: #29AB87; padding: 8px 12px;
    border-radius: 6px;
    background: rgba(41, 171, 135, 0.08);
    transition: all 0.25s ease;
    text-decoration: none;
}
.nav-link-twin:hover, .nav-link-twin.active { background: #29AB87; color: #fff; }

@media(max-width: 900px) {
    .desktop-nav { display: none; }
    .site-header { padding: 12px 16px; }
}

/* Base button for mobile menu if needed */
.btn-hamburger {
    display: none;
    background: transparent;
    border: none;
    cursor: pointer;
    flex-direction: column;
    gap: 5px;
    padding: 4px;
}
@media(max-width: 900px) {
    .btn-hamburger { display: flex; }
}
.btn-hamburger span {
    display: block; width: 24px; height: 2px;
    background-color: #073B2A; border-radius: 2px;
}
</style>

<header class="site-header">
    <div class="nav-inner">
        <a href="index.php" class="brand-logo">
            <div class="brand-icon-box">🌱</div>
            <span>Way2Green</span>
        </a>
        <nav class="desktop-nav">
            <a href="index.php" class="nav-link <?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a>
            <a href="travel.php" class="nav-link <?= $current_page === 'travel.php' ? 'active' : '' ?>">Plan Transit</a>
            <a href="hotels.php" class="nav-link <?= $current_page === 'hotels.php' ? 'active' : '' ?>">Eco-Stays</a>
            <a href="digital_twin.php" class="nav-link-twin <?= $current_page === 'digital_twin.php' ? 'active' : '' ?>">Digital Twin</a>
            <a href="about.php" class="nav-link <?= $current_page === 'about.php' ? 'active' : '' ?>">Our Mission</a>
            <?php if ($user): ?>
            <a href="my-trips.php" class="nav-link <?= $current_page === 'my-trips.php' ? 'active' : '' ?>">My Passports</a>
            <a href="logout.php" class="nav-link" style="color:#dc2626">Sign Out</a>
            <?php else: ?>
            <a href="login.php" class="nav-link <?= $current_page === 'login.php' ? 'active' : '' ?>">Sign In</a>
            <?php endif; ?>
        </nav>
        
        <!-- Minimal mobile menu toggle -->
        <button class="btn-hamburger" onclick="window.location.href='index.php'" aria-label="Home">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</header>
