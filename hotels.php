<?php
// hotels.php - Phase 2: Eco-Hotels Catalog & Accessible Hospitality
require_once 'db.php';
require_once 'user_auth.php';

// Must be logged in
require_user_login('hotels.php');
$user = get_logged_in_user();

$destId = isset($_GET['dest_id']) ? intval($_GET['dest_id']) : 0;
$destName = trim($_GET['dest_name'] ?? $_GET['dest'] ?? '');
$origin = trim($_GET['origin'] ?? 'Mumbai');
$mode = trim($_GET['mode'] ?? 'train');
$distance = floatval($_GET['distance'] ?? 450);
$co2Saved = floatval($_GET['co2_saved'] ?? 50);

// Fetch all destinations for switcher
$allDests = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
    $allDests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allDests = [];
}

// If destId is 0 but destName was provided, match it against all destinations
if ($destId === 0 && !empty($destName)) {
    foreach ($allDests as $d) {
        if (stripos($d['name'], $destName) !== false || stripos($destName, $d['name']) !== false) {
            $destId = $d['id'];
            $destName = $d['name'];
            break;
        }
    }
}

// If still 0, default to first destination
if ($destId === 0 && !empty($allDests)) {
    $destId = $allDests[0]['id'];
    $destName = $allDests[0]['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 2: Choose Your Eco-Stay — Way2Green</title>
    <meta name="description" content="Discover verified inclusive eco-lodges with water conservation and accessibility features.">
    
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
        <a href="hotels.php" class="drawer-link" style="color: var(--primary);">Eco-Stays</a>
        <a href="about.php" class="drawer-link">Our Mission</a>
        <a href="my-trips.php" class="drawer-link">My Passports</a>
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
            <a href="hotels.php" class="active">Eco-Stays</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a>
        </nav>
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <main class="page-container" style="max-width: 1140px;">
        <!-- Visual Multi-Step Tracker -->
        <div class="step-progress-bar">
            <div class="step-bubble">
                <span class="step-num">1</span>
                <span>Green Transit</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble active">
                <span class="step-num">2</span>
                <span>Select Eco-Stay</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble">
                <span class="step-num">3</span>
                <span>Eco-Passport</span>
            </div>
        </div>

        <!-- Selected Route Glassmorphic Context Bar (No Jitter, Spacious) -->
        <div class="trip-context-bar">
            <div class="trip-route-info">
                <span class="route-badge">
                    Chosen Route
                </span>
                <span class="route-cities">
                    <?= htmlspecialchars($origin) ?> ➔ <span id="currentDestLabel"><?= htmlspecialchars($destName) ?></span>
                </span>
                <span class="route-meta">
                    • <?= strtoupper($mode) ?> (<?= $distance ?> km) • <strong><?= $co2Saved ?> kg CO₂ Avoided</strong>
                </span>
            </div>

            <!-- Switch Destination Dropdown with Clean Styling -->
            <div class="trip-dest-picker">
                <label for="destinationFilterSelect" style="font-size: 0.88rem; font-weight: 700; color: var(--primary);">Change Region:</label>
                <select id="destinationFilterSelect" class="dest-select-input" onchange="changeDestination(this.value)">
                    <?php foreach ($allDests as $d): ?>
                        <option value="<?= $d['id'] ?>" data-name="<?= htmlspecialchars($d['name']) ?>" <?= $d['id'] == $destId ? 'selected' : '' ?>>
                            📍 <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Inclusivity & Accessibility Filters -->
        <div class="section-head reveal-on-scroll" style="margin-bottom: 1.5rem;">
            <span class="section-tag">Step 2 of 3</span>
            <h1 class="section-title">Verified Sustainable & Accessible Stays</h1>
            <p class="section-desc">Every retreat is evaluated for low energy consumption, water conservation, and certified barrier-free accessibility.</p>
        </div>

        <div class="filter-pills-row">
            <button type="button" class="pill-filter active" onclick="applyFilter('all', this)">
                All Eco-Stays
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('wheelchair', this)">
                Wheelchair & Step-Free Access
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('sensory', this)">
                Sensory Quiet & Low Stimulation
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('braille', this)">
                Braille & Audio Assisted
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('dog', this)">
                Service Dog Friendly
            </button>
        </div>

        <!-- Stays Grid -->
        <div class="stays-grid" id="staysContainer">
            <div style="text-align: center; grid-column: 1 / -1; padding: 2rem; color: var(--text-muted);">
                Finding verified stays...
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">Clean transit and barrier-free eco-stays for conscious travelers.</p>
            </div>
            <div>
                <h4 class="footer-heading">Pages</h4>
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
                    <a href="my-trips.php">My Saved Passports</a>
                    <a href="logout.php">Sign Out</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Green & Inclusive Travel Challenge.
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
        <a href="hotels.php" class="mobile-nav-item active">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="about.php" class="mobile-nav-item">
            <span class="icon">🌿</span>
            <span class="label">About</span>
        </a>
        <a href="my-trips.php" class="mobile-nav-item">
            <span class="icon">📜</span>
            <span class="label">Passport</span>
        </a>
    </div>

    <script>
        let currentDestId = <?= $destId ?>;
        let currentFilter = 'all';
        const originCity = "<?= htmlspecialchars($origin) ?>";
        const travelMode = "<?= htmlspecialchars($mode) ?>";
        const travelDistance = <?= $distance ?>;
        const carbonAvoided = <?= $co2Saved ?>;

        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        function changeDestination(newDestId) {
            currentDestId = parseInt(newDestId);
            const select = document.getElementById('destinationFilterSelect');
            const destName = select.selectedOptions[0].getAttribute('data-name');
            const label = document.getElementById('currentDestLabel');
            if (label) label.textContent = destName;

            // Sync URL without reloading
            const url = new URL(window.location);
            url.searchParams.set('dest_id', currentDestId);
            url.searchParams.set('dest_name', destName);
            window.history.replaceState({}, '', url);

            loadStays();
        }

        function applyFilter(filter, btn) {
            currentFilter = filter;
            document.querySelectorAll('.pill-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadStays();
        }

        async function loadStays() {
            const container = document.getElementById('staysContainer');
            container.innerHTML = '<div style="text-align:center; grid-column:1/-1; padding:3rem; color:var(--text-muted); font-size: 1.05rem;">🌱 Loading verified eco-stays for this sanctuary...</div>';

            try {
                const res = await fetch(`api/get_hotels.php?destination_id=${currentDestId}&filter=${encodeURIComponent(currentFilter)}`);
                const data = await res.json();

                if (data.status === 'success' && data.hotels.length > 0) {
                    container.innerHTML = data.hotels.map(h => {
                        const badges = (h.eco_badges || 'Solar Powered, Zero Plastic').split(',').map(b => `<span class="tag-badge">🌱 ${b.trim()}</span>`).join('');
                        const access = (h.accessibility_tags || 'Wheelchair Friendly').split(',').map(a => `<span class="tag-badge tag-access">♿ ${a.trim()}</span>`).join('');

                        // Construct checkout URL with all journey data
                        const checkoutParams = new URLSearchParams({
                            hotel_id: h.id,
                            hotel_name: h.name,
                            dest_name: h.destination_name,
                            origin: originCity,
                            mode: travelMode,
                            distance: travelDistance,
                            co2_saved: carbonAvoided,
                            price: h.price_per_night || 3200,
                            water_saved: h.water_saved_liters || 100000,
                            power_saved: h.power_saved_kwh || 25000
                        });

                        return `
                            <div class="stay-card">
                                <div class="stay-img-box">
                                    <img src="${h.image_url || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'}" alt="${h.name}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                                    <div class="stay-rating">★ ${h.eco_rating || '4.8'}</div>
                                </div>
                                <div class="stay-body">
                                    <div class="stay-location">📍 ${h.destination_name}</div>
                                    <h3 class="stay-title">${h.name}</h3>
                                    <p class="stay-desc">${h.description}</p>

                                    <div class="stay-metrics-banner">
                                        <span>💧 ${(h.water_saved_liters || 0).toLocaleString()}L Water Saved/yr</span>
                                        <span>⚡ ${(h.power_saved_kwh || 0).toLocaleString()} kWh Solar</span>
                                    </div>

                                    <div class="stay-badges-row">
                                        ${badges}
                                        ${access}
                                    </div>

                                    <div class="stay-card-footer">
                                        <div class="price-text">
                                            <span class="amount">₹${(h.price_per_night || 3200).toLocaleString()}</span>
                                            <span>/ night</span>
                                        </div>
                                        <a href="checkout.php?${checkoutParams.toString()}" class="btn-select-stay">
                                            View & Book Stay ➔
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');

                    if (typeof init3DTilt === 'function') init3DTilt();
                    if (typeof initScrollReveal === 'function') initScrollReveal();
                } else {
                    container.innerHTML = `
                        <div style="text-align:center; grid-column:1/-1; padding:3.5rem 2rem; background:rgba(255,255,255,0.85); border-radius:24px; border:1.5px dashed rgba(34, 197, 94, 0.3);">
                            <div style="font-size: 2.5rem; margin-bottom: 8px;">🌿</div>
                            <h3 style="color:var(--primary); font-size: 1.3rem;">No certified properties found for this specific filter</h3>
                            <p style="color:var(--text-muted); font-size:0.92rem; margin-top:6px;">Try switching to <strong>"All Eco-Stays"</strong> or select another destination above.</p>
                        </div>
                    `;
                }
            } catch (err) {
                container.innerHTML = '<div style="text-align:center; grid-column:1/-1; color:red; padding: 2rem;">Failed to load stays. Please try again.</div>';
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            loadStays();
        });
    </script>
    <script src="js/effects.js"></script>
</body>
</html>
