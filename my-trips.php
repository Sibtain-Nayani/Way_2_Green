<?php
// my-trips.php - Traveler's Saved Eco-Passports
require_once 'db.php';
require_once 'user_auth.php';

require_user_login('my-trips.php');
$user = get_logged_in_user();

$bookings = [];
try {
    $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name, h.image_url, h.water_saved_liters 
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC");
    $stmt->execute([$user['id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Eco-Passports — Way2Green</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Ambient Glowing 3D Moving Scene Layer -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Drawer Overlay & Mobile Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link">Home</a>
        <a href="travel.php" class="drawer-link">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="about.php" class="drawer-link">Our Mission</a>
        <a href="my-trips.php" class="drawer-link" style="color: var(--primary);">My Passports</a>
        <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- Clean Header -->
    <header class="header-top">
        <a href="index.php" class="brand">
            <span class="brand-leaf">🌱</span>
            <span>Way2Green</span>
        </a>
        <nav class="desktop-nav">
            <a href="index.php">Home</a>
            <a href="travel.php">Plan Transit</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php" class="active">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a>
        </nav>
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <main class="page-container" style="max-width: 960px;">
        <div class="section-head reveal-on-scroll" style="text-align: left; margin-bottom: 2rem;">
            <span class="section-tag">Traveler Passport Vault</span>
            <h1 class="section-title">My Verified Eco-Passports</h1>
            <p class="section-desc" style="margin: 0;">Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong>! Here is your verified history of low-carbon journeys and sustainable stays.</p>
        </div>

        <?php if (!empty($bookings)): ?>
            <div style="display: grid; gap: 1.5rem;">
                <?php foreach ($bookings as $b): ?>
                    <div class="card-3d reveal-on-scroll" style="background: #ffffff; border-radius: var(--radius-lg); padding: 1.6rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-card); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px;">
                        <div style="display: flex; gap: 16px; align-items: center;">
                            <img src="<?= htmlspecialchars($b['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=400&q=80') ?>" alt="Hotel" style="width: 80px; height: 80px; border-radius: 12px; object-fit: cover;">
                            <div>
                                <span style="font-family: monospace; font-size: 0.8rem; font-weight: 800; color: var(--primary-accent);"><?= htmlspecialchars($b['booking_code']) ?></span>
                                <h3 style="color: var(--primary); font-size: 1.25rem; margin: 2px 0;"><?= htmlspecialchars($b['hotel_name']) ?></h3>
                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                    📍 <?= htmlspecialchars($b['origin']) ?> ➔ <?= htmlspecialchars($b['destination']) ?> • Via <?= strtoupper($b['travel_mode']) ?>
                                </div>
                                <div style="font-size: 0.82rem; color: var(--text-muted); margin-top: 4px;">
                                    📅 <?= date('M d, Y', strtotime($b['check_in'])) ?> – <?= date('M d, Y', strtotime($b['check_out'])) ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 6px 12px; display: inline-block;">
                                <span style="font-weight: 800; color: var(--primary); font-size: 0.88rem;">🌱 <?= floatval($b['co2_saved_kg']) ?> kg CO₂ Avoided</span>
                            </div>
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                                <a href="passport.php?code=<?= urlencode($b['booking_code']) ?>" class="btn-nature-primary" style="padding: 8px 16px; font-size: 0.85rem;">
                                    📜 View Eco-Passport ➔
                                </a>
                                <a href="passport.php?code=<?= urlencode($b['booking_code']) ?>&print=1" class="btn-nature-outline" style="padding: 8px 14px; font-size: 0.85rem;">
                                    🖨️ Print / PDF
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: #ffffff; border-radius: var(--radius-lg); padding: 3rem 1.5rem; text-align: center; border: 1px dashed var(--border-subtle);">
                <div style="font-size: 3rem; margin-bottom: 10px;">📜</div>
                <h3 style="color: var(--primary); font-size: 1.4rem; margin-bottom: 6px;">No Eco-Passports Yet</h3>
                <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 1.5rem; font-size: 0.95rem;">
                    Plan your first low-carbon trip, reserve an inclusive bio-resort, and receive your official net-zero certificate.
                </p>
                <a href="travel.php" class="btn-nature-primary">
                    Plan Your First Eco-Trip ➔
                </a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">Clean transit and barrier-free eco-stays for conscious travelers.</p>
            </div>
            <div>
                <h4 class="footer-heading">Navigation</h4>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="travel.php">Phase 1: Transit</a>
                    <a href="hotels.php">Phase 2: Eco-Hotels</a>
                    <a href="about.php">About Mission</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Account</h4>
                <div class="footer-links">
                    <a href="logout.php">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
                    <a href="admin/login.php">Admin Login</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Hackathon 2026.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-bar">
        <a href="index.php" class="mobile-nav-item">
            <span class="icon">🏡</span>
            <span class="label">Home</span>
        </a>
        <a href="travel.php" class="mobile-nav-item">
            <span class="icon">🚆</span>
            <span class="label">Transit</span>
        </a>
        <a href="hotels.php" class="mobile-nav-item">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="about.php" class="mobile-nav-item">
            <span class="icon">🌿</span>
            <span class="label">About</span>
        </a>
        <a href="my-trips.php" class="mobile-nav-item active">
            <span class="icon">📜</span>
            <span class="label">Passport</span>
        </a>
    </div>

    <script src="js/effects.js"></script>
    <script>
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }
    </script>
</body>
</html>
