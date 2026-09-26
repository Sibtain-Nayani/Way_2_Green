<?php
// index.php - Way2Green Home: Smart Sustainable & Accessible Hospitality
require_once 'db.php';
require_once 'user_auth.php';

$user = get_logged_in_user();

// Fetch all curated destinations
$featuredDests = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY id ASC");
    $featuredDests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredDests = [];
}

// Curated high-res scenic photos for each destination
$destImages = [
    'Munnar' => 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80',
    'Manali' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=800&q=80',
    'Wayanad' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80',
    'South Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
    'Rishikesh' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
    'Ooty' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80'
];

// Fetch 6 diverse featured eco-hotels with verified photography
$featuredHotels = [];
try {
    $stmtHotels = $pdo->query("
        SELECT h.*, d.name AS destination_name 
        FROM hotels h 
        JOIN destinations d ON h.destination_id = d.id 
        ORDER BY h.eco_rating DESC, h.id ASC 
        LIMIT 6
    ");
    $featuredHotels = $stmtHotels->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredHotels = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Way2Green — Travel Light. Leave Only Footprints.</title>
    <meta name="description" content="A conscious travel platform helping travelers calculate route emissions, find eco-friendly barrier-free stays, and earn verified green passports.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Background Ambient Glowing 3D Moving Orbs Layer -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Mobile Drawer Overlay -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>

    <!-- Off-Canvas Mobile Drawer -->
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link" style="color: var(--primary);">Home</a>
        <a href="travel.php" class="drawer-link">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="about.php" class="drawer-link">Our Mission</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">My Passports</a>
            <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">Traveler Sign In</a>
            <a href="register.php" class="drawer-link" style="color: var(--primary-light);">Create Account</a>
        <?php endif; ?>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- Clean Top Header -->
    <header class="header-top">
        <a href="index.php" class="brand">
            <span class="brand-leaf">🌱</span>
            <span>Way2Green</span>
        </a>

        <!-- Desktop Nav Links -->
        <nav class="desktop-nav">
            <a href="index.php" class="active">Home</a>
            <a href="travel.php">Plan Transit</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="about.php">About Us</a>
            <?php if ($user): ?>
                <a href="my-trips.php">My Passports</a>
                <a href="logout.php" style="color: #dc2626;">Sign Out</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="register.php" class="nav-cta">Get Started</a>
            <?php endif; ?>
        </nav>

        <!-- Mobile Hamburger Button -->
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <!-- Human-Feel Hero Section with Natural Visual Depth -->
    <section class="hero-nature">
        <div class="hero-pill">🌱 Smart Sustainable & Accessible Hospitality</div>
        <h1 class="hero-title">Travel Light. Leave Only Footprints.</h1>
        <p class="hero-subtitle">
            Tourism shouldn't cost the Earth or exclude anyone. We help you pick cleaner routes, stay at verified barrier-free eco-lodges, and earn verified carbon-offset passports.
        </p>

        <div class="hero-actions">
            <a href="travel.php" class="btn-nature-primary">
                Plan Your Journey ➔
            </a>
            <a href="about.php" class="btn-nature-outline">
                Our Mission & Impact
            </a>
        </div>
    </section>

    <!-- The 3-Step Journey Roadmap -->
    <section class="journey-roadmap">
        <!-- Step 1 -->
        <div class="roadmap-card card-3d reveal-on-scroll">
            <div>
                <span class="roadmap-step-badge">Step 1 • Low Carbon Transit</span>
                <div class="roadmap-icon">
                    <svg class="eco-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="16" rx="2"/><path d="M4 11h16"/><path d="M12 3v8"/><path d="m8 19-2 3"/><path d="m18 22-2-3"/><circle cx="8" cy="15" r="1"/><circle cx="16" cy="15" r="1"/></svg>
                </div>
                <h3 class="roadmap-title">Calculate & Cut Emissions</h3>
                <p class="roadmap-desc">
                    Compare trains, electric vehicles, and coaches against driving. See your exact carbon footprint in simple numbers and receive smart route tips.
                </p>
            </div>
            <a href="travel.php" class="roadmap-btn">Open Transit Planner ➔</a>
        </div>

        <!-- Step 2 -->
        <div class="roadmap-card card-3d reveal-on-scroll">
            <div>
                <span class="roadmap-step-badge">Step 2 • Inclusive Hospitality</span>
                <div class="roadmap-icon">
                    <svg class="eco-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/><path d="M14 9h1"/><path d="M14 13h1"/><path d="M14 17h1"/></svg>
                </div>
                <h3 class="roadmap-title">Stay at Verified Eco-Resorts</h3>
                <p class="roadmap-desc">
                    Discover handpicked sanctuaries running on 100% solar power, rainwater harvesting, and zero single-use plastics — built with wheelchair ramps and sensory-quiet spaces.
                </p>
            </div>
            <a href="hotels.php" class="roadmap-btn">Browse Eco-Stays ➔</a>
        </div>

        <!-- Step 3 -->
        <div class="roadmap-card card-3d reveal-on-scroll">
            <div>
                <span class="roadmap-step-badge">Step 3 • Digital Green Passport</span>
                <div class="roadmap-icon">
                    <svg class="eco-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h3 class="roadmap-title">Earn Your Eco-Passport</h3>
                <p class="roadmap-desc">
                    Complete your reservation to receive a verified, downloadable Carbon-Offset Passport showing liters of water saved and emissions avoided.
                </p>
            </div>
            <a href="<?= $user ? 'my-trips.php' : 'login.php' ?>" class="roadmap-btn">View My Passports ➔</a>
        </div>
    </section>

    <!-- Why Sustainable & Accessible Travel Matters -->
    <section class="page-container">
        <div class="section-head reveal-on-scroll">
            <span class="section-tag">Measurable Real Impact</span>
            <h2 class="section-title">Verified Green & Inclusive Metrics</h2>
            <p class="section-desc">
                Small adjustments in how we move and where we stay create massive ripples of positive preservation for our planet and communities.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.8rem;">
            <div class="card-3d reveal-on-scroll" style="background:#ffffff; border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-soft);">
                <div style="font-size: 2.2rem; margin-bottom: 10px; color: #0284c7;">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;" data-counter="1250000" data-suffix=" L+">0 L+</div>
                <h3 style="color: var(--primary); font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Water Recycled & Conserved</h3>
                <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6;">
                    Conventional hotels consume up to 1,500 liters of water per guest each night. Our partner eco-lodges use greywater wetland recycling and rainwater systems to save millions of liters annually.
                </p>
            </div>

            <div class="card-3d reveal-on-scroll" style="background:#ffffff; border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-soft);">
                <div style="font-size: 2.2rem; margin-bottom: 10px; color: #f59e0b;">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;" data-counter="48000" data-suffix=" kWh">0 kWh</div>
                <h3 style="color: var(--primary); font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Clean Renewable Energy</h3>
                <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6;">
                    From micro-hydro turbines in hill stations to rooftop solar arrays, certified stays generate clean power on-site, cutting dependency on coal-fired grids.
                </p>
            </div>

            <div class="card-3d reveal-on-scroll" style="background:#ffffff; border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-soft);">
                <div style="font-size: 2.2rem; margin-bottom: 10px; color: var(--primary-glow);">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;" data-counter="100" data-suffix="% Verified">0%</div>
                <h3 style="color: var(--primary); font-size: 1.15rem; font-weight: 800; margin-bottom: 8px;">Universal Barrier-Free Standards</h3>
                <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6;">
                    Over 15% of the world's population lives with disabilities. We physically verify step-free entrances, tactile signage, and sensory-quiet spaces so nature is welcoming for everyone.
                </p>
            </div>
        </div>
    </section>

    <!-- Featured Destinations Preview with Real Curated Photography -->
    <section class="page-container" style="padding-top: 1rem;">
        <div class="section-head reveal-on-scroll">
            <span class="section-tag">Explore Regions</span>
            <h2 class="section-title">Protected Green Corridors</h2>
            <p class="section-desc">Handpicked biodiversity havens across India ready for your next responsible adventure.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.8rem;">
            <?php foreach ($featuredDests as $d): 
                $destNameParts = explode(',', $d['name']);
                $destKey = trim($destNameParts[0]);
                $imgUrl = $destImages[$destKey] ?? 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80';
            ?>
            <div class="corridor-card card-3d reveal-on-scroll">
                <div class="corridor-img-box">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80'">
                    <span class="corridor-badge">Curated Eco-Zone</span>
                </div>
                <div class="corridor-body">
                    <h3 style="color: var(--primary); font-size: 1.25rem; font-weight: 800; margin: 0 0 8px;"><?= htmlspecialchars($d['name']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.88rem; line-height: 1.55; margin-bottom: 1.3rem; flex-grow: 1;">
                        Verified solar resorts, organic local farm dining, and step-free accessible forest boardwalks.
                    </p>
                    <a href="travel.php?dest=<?= urlencode($d['name']) ?>" class="roadmap-btn">
                        Plan Journey to <?= htmlspecialchars($destKey) ?> ➔
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Verified Eco-Resorts & Sanctuaries Showcase -->
    <section class="page-container" style="padding-top: 1rem;">
        <div class="section-head reveal-on-scroll">
            <span class="section-tag">Inclusive Hospitality</span>
            <h2 class="section-title">Featured Eco-Resorts & Sanctuaries</h2>
            <p class="section-desc">Handpicked stays running on 100% renewable energy, greywater recycling, and universal barrier-free access.</p>
        </div>

        <div class="stays-grid">
            <?php foreach ($featuredHotels as $h): 
                $badges = array_filter(array_map('trim', explode(',', $h['eco_badges'] ?? 'Solar Powered, Zero Plastic')));
                $access = array_filter(array_map('trim', explode(',', $h['accessibility_tags'] ?? 'Wheelchair Friendly')));
            ?>
            <div class="stay-card card-3d reveal-on-scroll">
                <div class="stay-img-box">
                    <img src="<?= htmlspecialchars($h['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80') ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                    <div class="stay-rating">★ <?= htmlspecialchars($h['eco_rating'] ?? '4.9') ?></div>
                </div>
                <div class="stay-body">
                    <div class="stay-location">📍 <?= htmlspecialchars($h['destination_name']) ?></div>
                    <h3 class="stay-title"><?= htmlspecialchars($h['name']) ?></h3>
                    <p class="stay-desc"><?= htmlspecialchars($h['description']) ?></p>

                    <div class="stay-metrics-banner">
                        <span>💧 <?= number_format($h['water_saved_liters'] ?? 100000) ?>L Saved/yr</span>
                        <span>⚡ <?= number_format($h['power_saved_kwh'] ?? 25000) ?> kWh Solar</span>
                    </div>

                    <div class="stay-badges-row">
                        <?php foreach (array_slice($badges, 0, 2) as $b): ?>
                            <span class="tag-badge">🌱 <?= htmlspecialchars($b) ?></span>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($access, 0, 2) as $a): ?>
                            <span class="tag-badge tag-access">♿ <?= htmlspecialchars($a) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="stay-card-footer">
                        <div class="price-text">
                            <span class="amount">₹<?= number_format($h['price_per_night'] ?? 3500) ?></span>
                            <span>/ night</span>
                        </div>
                        <a href="hotels.php?dest=<?= urlencode($h['destination_name']) ?>" class="btn-select-stay">
                            View & Book Stay ➔
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;" class="reveal-on-scroll">
            <a href="hotels.php" class="btn-nature-outline" style="font-size: 1.05rem; padding: 14px 32px;">
                Explore All 26 Eco-Stays Across India ➔
            </a>
        </div>
    </section>

    <!-- Call to Action Banner -->
    <section class="page-container" style="padding-top: 1rem;">
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #ffffff; border-radius: var(--radius-lg); padding: 3.5rem 2rem; text-align: center; box-shadow: 0 16px 40px rgba(15, 61, 36, 0.25);">
            <h2 style="font-size: clamp(1.8rem, 3.5vw, 2.4rem); font-weight: 800; margin-bottom: 1rem; color: #ffffff;">Ready for a Trip That Gives Back?</h2>
            <p style="max-width: 580px; margin: 0 auto 2.2rem; font-size: 1.05rem; color: rgba(255, 255, 255, 0.9); line-height: 1.6;">
                Plan your route, pick an inclusive eco-resort, and hold a verified passport of your carbon savings.
            </p>
            <a href="travel.php" class="btn-nature-white">
                Start Traveling Consciously ➔
            </a>
        </div>
    </section>

    <!-- Site Footer with Specific Page Redirection -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">
                    A community-led open platform making sustainable and barrier-free hospitality intuitive, affordable, and practical for every traveler.
                </p>
                <div style="margin-top: 14px; font-size: 0.82rem; color: rgba(255, 255, 255, 0.6);">
                    Host Subdomain: <code>way2green.synergize.co</code>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Pages & Tools</h4>
                <div class="footer-links">
                    <a href="index.php">Home Overview</a>
                    <a href="travel.php">Phase 1: Transit Calculator</a>
                    <a href="hotels.php">Phase 2: Eco-Hotels Catalog</a>
                    <a href="about.php">About Our Mission</a>
                    <a href="my-trips.php">My Eco-Passports</a>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Account & Access</h4>
                <div class="footer-links">
                    <?php if ($user): ?>
                        <a href="my-trips.php">Profile (<?= htmlspecialchars($user['name']) ?>)</a>
                        <a href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a href="login.php">Traveler Sign In</a>
                        <a href="register.php">Create Account</a>
                    <?php endif; ?>
                    <a href="admin/login.php" style="color: var(--leaf);">Admin Control Panel</a>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            © 2026 Way2Green • Built for Green & Inclusive Travel Hackathon Challenge.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-bar">
        <a href="index.php" class="mobile-nav-item active">
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
        <?php if ($user): ?>
            <a href="my-trips.php" class="mobile-nav-item">
                <span class="icon">📜</span>
                <span class="label">Passport</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="mobile-nav-item">
                <span class="icon">👤</span>
                <span class="label">Sign In</span>
            </a>
        <?php endif; ?>
    </div>

    <script src="js/effects.js"></script>
    <script>
        function toggleDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            const overlay = document.getElementById('drawerOverlay');
            drawer.classList.toggle('open');
            overlay.classList.toggle('active');
        }
    </script>
</body>
</html>
