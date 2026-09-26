<?php
// admin/sidebar.php - Reusable Admin Sidebar & Mobile Bar Component
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Top Bar for Admin -->
<div class="admin-mobile-bar">
    <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 1.15rem;">
        <span style="font-size: 1.3rem;">🌍</span>
        <span>Way2Green Admin</span>
    </div>
    <button class="btn-admin-hamburger" onclick="toggleAdminDrawer()" aria-label="Toggle admin menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</div>

<!-- Mobile Drawer Overlay -->
<div class="admin-drawer-overlay" id="adminDrawerOverlay" onclick="toggleAdminDrawer()"></div>

<!-- Admin Sidebar (Fixed Desktop Sidebar / Off-Canvas Mobile Drawer) -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-brand">
        <div class="logo-icon">🌱</div>
        <div class="logo-text">Way2Green</div>
        <span class="badge-tag">ADMIN</span>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-heading">Main Controls</div>
        
        <a href="dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">📊</span>
            <span>Dashboard</span>
        </a>

        <a href="destinations.php" class="sidebar-link <?= $currentPage === 'destinations.php' ? 'active' : '' ?>">
            <span class="icon">📍</span>
            <span>Destinations</span>
        </a>

        <a href="hotels.php" class="sidebar-link <?= $currentPage === 'hotels.php' ? 'active' : '' ?>">
            <span class="icon">🏨</span>
            <span>Eco-Hotels</span>
        </a>

        <a href="bookings.php" class="sidebar-link <?= $currentPage === 'bookings.php' ? 'active' : '' ?>">
            <span class="icon">📜</span>
            <span>User Bookings</span>
        </a>

        <div class="menu-heading" style="margin-top: 1rem;">Navigation</div>

        <a href="../index.php" target="_blank" class="sidebar-link">
            <span class="icon">🌐</span>
            <span>Live Website ➔</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout">
            <span class="icon">🚪</span>
            <span>Sign Out</span>
        </a>
    </div>
</aside>

<script>
    function toggleAdminDrawer() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('adminDrawerOverlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }
</script>
