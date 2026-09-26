<?php
// index.php — Way2Green Homepage: Phase 1 — Travel Planning Platform
require_once 'db.php';
require_once 'user_auth.php';

$user = get_logged_in_user();

// Fetch curated destinations for the Eco-Stays preview
$featuredDests = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY id ASC LIMIT 6");
    $featuredDests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredDests = [];
}

// Fetch top eco-hotels for the preview section
$featuredHotels = [];
try {
    $stmtHotels = $pdo->query("
        SELECT h.*, d.name AS destination_name
        FROM hotels h
        JOIN destinations d ON h.destination_id = d.id
        ORDER BY h.eco_rating DESC, h.id ASC
        LIMIT 3
    ");
    $featuredHotels = $stmtHotels->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $featuredHotels = [];
}

// Destination imagery
$destImages = [
    'Munnar'    => 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80',
    'Manali'    => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=800&q=80',
    'Wayanad'   => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80',
    'South Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
    'Rishikesh' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
    'Ooty'      => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Way2Green — Plan Your Journey. Travel Consciously.</title>
    <meta name="description" content="Way2Green is a sustainable travel platform. Plan your complete journey, compare eco-friendly travel options, find verified green stays and earn your Eco Passport.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Ambient background — subtle, toned down from previous version -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <!-- Mobile Drawer Overlay -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>

    <!-- Off-Canvas Mobile Drawer -->
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
            <img src="uploads/w2g-logo.png" alt="Way2Green" style="height: 30px; width: auto;">
        </div>
        <a href="index.php" class="drawer-link" style="color: var(--primary);">Home</a>
        <a href="travel.php" class="drawer-link">Plan a Trip</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">My Trips</a>
            <a href="passport.php" class="drawer-link">Eco Passport</a>
            <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">Sign In</a>
            <a href="register.php" class="drawer-link" style="color: var(--primary-light);">Create Account</a>
        <?php endif; ?>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- ====================================================================
         HEADER / NAVIGATION
         Updated nav links: Home, Plan a Trip, Eco-Stays, My Trips, Eco Passport
         Auth preserved: login, register, logout
         ==================================================================== -->
    <header class="header-top">
        <a href="index.php" class="brand" aria-label="Way2Green Home">
            <img src="uploads/w2g-logo.png" alt="Way2Green" class="brand-logo-img">
        </a>

        <!-- Desktop Navigation -->
        <nav class="desktop-nav" aria-label="Main navigation">
            <a href="index.php" class="active">Home</a>
            <a href="travel.php">Plan a Trip</a>
            <a href="hotels.php">Eco-Stays</a>
            <?php if ($user): ?>
                <a href="my-trips.php">My Trips</a>
                <a href="passport.php">Eco Passport</a>
                <a href="logout.php" style="color: #dc2626;">Sign Out</a>
            <?php else: ?>
                <a href="login.php">Sign In</a>
                <a href="register.php" class="nav-cta">Get Started</a>
            <?php endif; ?>
        </nav>

        <!-- Mobile Hamburger -->
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu" id="hamburger-btn">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <!-- ====================================================================
         HERO — Phase 1
         Primary purpose: Way2Green is a complete travel planning platform.
         CTA: Find My Journey (trip search).
         Removed: "Gemini AI Travel Insights" element.
         ==================================================================== -->
    <section class="hero-phase1" aria-labelledby="hero-headline">

        <div class="hero-eyebrow">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2z"/><path d="M12 6v6l4 2"/></svg>
            Sustainable Travel Planning
        </div>

        <h1 class="hero-headline" id="hero-headline">
            Plan Your Journey.<br><em>Travel Consciously.</em>
        </h1>

        <p class="hero-sub">
            Way2Green helps you plan your complete trip — compare travel options by cost, time and carbon impact, find verified eco-stays, and earn your digital Eco Passport.
        </p>
    </section>

    <!-- ====================================================================
         TRIP SEARCH — "Plan Your Trip"
         Inputs: From, To, Departure, Return (optional), Travellers, Preference
         Preferences: Way2Green Pick | Lowest Price | Fastest | Lower Impact
         Supports One Way / Round Trip. Client-side validation.
         FUTURE API INTEGRATION — PHASE 7
         ==================================================================== -->
    <div class="trip-search-wrap" role="search" aria-label="Plan your trip">
        <div class="trip-search-card">

            <!-- Trip type toggle -->
            <div class="trip-type-row" role="group" aria-label="Trip type">
                <button class="trip-type-btn" data-type="round" id="btn-round-trip">Round Trip</button>
                <button class="trip-type-btn active" data-type="one-way" id="btn-one-way">One Way</button>
            </div>

            <form id="trip-search-form" novalidate>

                <!-- Search fields grid: From | To | Departure | Return | Travellers -->
                <div class="search-fields-grid" id="search-fields-grid">

                    <!-- From -->
                    <div class="sf-field" id="sf-from">
                        <label class="sf-label" for="trip-from">From</label>
                        <input
                            type="text"
                            id="trip-from"
                            name="from"
                            class="sf-input"
                            placeholder="Departure city"
                            autocomplete="off"
                            list="loc-datalist-from"
                            aria-describedby="from-err"
                            aria-required="true"
                        >
                        <datalist id="loc-datalist-from" class="location-datalist"></datalist>
                        <span class="sf-error" id="from-err" role="alert" hidden></span>
                    </div>

                    <!-- To -->
                    <div class="sf-field" id="sf-to">
                        <label class="sf-label" for="trip-to">To</label>
                        <input
                            type="text"
                            id="trip-to"
                            name="to"
                            class="sf-input"
                            placeholder="Destination"
                            autocomplete="off"
                            list="loc-datalist-to"
                            aria-describedby="to-err"
                            aria-required="true"
                        >
                        <datalist id="loc-datalist-to" class="location-datalist"></datalist>
                        <span class="sf-error" id="to-err" role="alert" hidden></span>
                    </div>

                    <!-- Departure date -->
                    <div class="sf-field" id="sf-departure">
                        <label class="sf-label" for="trip-departure">Departure</label>
                        <input
                            type="date"
                            id="trip-departure"
                            name="departure"
                            class="sf-input"
                            aria-describedby="departure-err"
                            aria-required="true"
                        >
                        <span class="sf-error" id="departure-err" role="alert" hidden></span>
                    </div>

                    <!-- Return date (hidden for one-way) -->
                    <div class="sf-field" id="return-field-wrap" style="display:none;">
                        <label class="sf-label" for="trip-return">Return</label>
                        <input
                            type="date"
                            id="trip-return"
                            name="return"
                            class="sf-input"
                            aria-describedby="return-err"
                        >
                        <span class="sf-error" id="return-err" role="alert" hidden></span>
                    </div>

                    <!-- Travellers -->
                    <div class="sf-field" id="sf-travellers">
                        <label class="sf-label" for="trip-travellers">Travellers</label>
                        <select id="trip-travellers" name="travellers" class="sf-select sf-input" aria-label="Number of travellers">
                            <option value="1">1 Adult</option>
                            <option value="2">2 Adults</option>
                            <option value="3">3 Adults</option>
                            <option value="4">4 Adults</option>
                            <option value="5">5+ Adults</option>
                        </select>
                    </div>

                </div><!-- /.search-fields-grid -->

                <!-- Preference pills -->
                <div class="pref-row" role="group" aria-label="Journey preference">
                    <span class="pref-label">Preference:</span>
                    <button type="button" class="pref-pill active" data-pref="w2g" id="pref-w2g">🌿 Way2Green Pick</button>
                    <button type="button" class="pref-pill" data-pref="price" id="pref-price">💰 Lowest Price</button>
                    <button type="button" class="pref-pill" data-pref="fast" id="pref-fast">⚡ Fastest</button>
                    <button type="button" class="pref-pill" data-pref="impact" id="pref-impact">🌍 Lower Impact</button>
                </div>

                <!-- Submit -->
                <div class="search-action-row">
                    <button type="submit" class="btn-find-journey" id="btn-find-journey">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Find My Journey
                    </button>
                </div>

                <!-- FUTURE API INTEGRATION — PHASE 7: Journey results rendered here -->
                <div id="journey-results-wrap" hidden>
                    <p class="journey-results-heading">Journey Options</p>
                    <div id="journey-results-list" role="list" aria-label="Journey results"></div>
                    <p style="font-size:0.76rem; color: var(--text-muted); margin-top: 0.8rem; font-style: italic;">
                        Showing preview results. Live pricing &amp; real-time availability coming in Phase 7.
                    </p>
                </div>

            </form>
        </div>
    </div><!-- /.trip-search-wrap -->

    <div class="hero-to-search-spacer"></div>

    <!-- ====================================================================
         HOW IT WORKS — Plan → Compare → Book → Travel
         ==================================================================== -->
    <section class="how-it-works" aria-labelledby="hiw-heading">
        <div class="section-head">
            <span class="section-tag">How It Works</span>
            <h2 class="section-title" id="hiw-heading">From idea to journey in four steps</h2>
            <p class="section-desc">A complete platform built around every stage of your sustainable trip.</p>
        </div>

        <div class="hiw-steps" role="list">
            <div class="hiw-step" role="listitem">
                <div class="hiw-num" aria-hidden="true">1</div>
                <span class="hiw-icon-wrap" aria-hidden="true">🗺️</span>
                <div class="hiw-label">Plan</div>
                <p class="hiw-desc">Enter your route, travel dates and how many are travelling.</p>
            </div>
            <div class="hiw-step" role="listitem">
                <div class="hiw-num" aria-hidden="true">2</div>
                <span class="hiw-icon-wrap" aria-hidden="true">⚖️</span>
                <div class="hiw-label">Compare</div>
                <p class="hiw-desc">See train, flight and bus options side by side — price, duration and carbon.</p>
            </div>
            <div class="hiw-step" role="listitem">
                <div class="hiw-num" aria-hidden="true">3</div>
                <span class="hiw-icon-wrap" aria-hidden="true">🛎️</span>
                <div class="hiw-label">Book</div>
                <p class="hiw-desc">Select a verified eco-stay that matches your values and budget.</p>
            </div>
            <div class="hiw-step" role="listitem">
                <div class="hiw-num" aria-hidden="true">4</div>
                <span class="hiw-icon-wrap" aria-hidden="true">🌿</span>
                <div class="hiw-label">Travel</div>
                <p class="hiw-desc">Earn your Eco Passport — a verified record of your sustainable journey.</p>
            </div>
        </div>
    </section>

    <hr class="section-divider" style="margin-top: 4rem;">

    <!-- ====================================================================
         JOURNEY COMPARISON PREVIEW
         Static preview — FUTURE API INTEGRATION — PHASE 7
         ==================================================================== -->
    <section class="comparison-section" aria-labelledby="comp-heading">
        <div class="section-head">
            <span class="section-tag">Journey Comparison</span>
            <h2 class="section-title" id="comp-heading">See what matters before you choose</h2>
            <p class="section-desc">Compare every route by price, travel time, and carbon footprint in one clear view.</p>
        </div>

        <!-- FUTURE API INTEGRATION — PHASE 7: Replace static rows with live journey data -->
        <div class="comparison-table-wrap" role="table" aria-label="Example journey comparison: New Delhi to Munnar">
            <!-- Header -->
            <div class="comp-row comp-header" role="row">
                <div class="comp-cell" role="columnheader">Travel Mode</div>
                <div class="comp-cell" role="columnheader">Duration</div>
                <div class="comp-cell" role="columnheader">Price (est.)</div>
                <div class="comp-cell" role="columnheader">CO₂ (kg)</div>
                <div class="comp-cell" role="columnheader">Recommended</div>
            </div>
            <!-- Row: Train -->
            <div class="comp-row comp-recommended" role="row">
                <div class="comp-cell mode-cell" role="cell">🚆 Train</div>
                <div class="comp-cell" role="cell">36h 00m</div>
                <div class="comp-cell" role="cell">₹1,850</div>
                <div class="comp-cell co2-cell good" role="cell">14 kg</div>
                <div class="comp-cell" role="cell"><span class="comp-rec-badge">🌿 Way2Green Pick</span></div>
            </div>
            <!-- Row: Bus -->
            <div class="comp-row" role="row">
                <div class="comp-cell mode-cell" role="cell">🚌 Bus</div>
                <div class="comp-cell" role="cell">14h 00m</div>
                <div class="comp-cell" role="cell">₹950</div>
                <div class="comp-cell co2-cell good" role="cell">22 kg</div>
                <div class="comp-cell" role="cell"><span class="comp-rec-badge" style="background: rgba(245,158,11,0.12); color: #92400e;">💰 Lowest Price</span></div>
            </div>
            <!-- Row: Flight -->
            <div class="comp-row" role="row">
                <div class="comp-cell mode-cell" role="cell">✈️ Flight</div>
                <div class="comp-cell" role="cell">2h 20m</div>
                <div class="comp-cell" role="cell">₹4,200</div>
                <div class="comp-cell co2-cell bad" role="cell">185 kg</div>
                <div class="comp-cell" role="cell"><span class="comp-rec-badge" style="background: rgba(59,130,246,0.12); color: #1d4ed8;">⚡ Fastest</span></div>
            </div>
        </div>
        <p style="font-size:0.78rem; color: var(--text-muted); margin-top: 0.8rem; font-style: italic; text-align: center;">
            Example: New Delhi → Munnar. Live pricing coming in Phase 7.
        </p>
    </section>

    <hr class="section-divider" style="margin-top: 4rem;">

    <!-- ====================================================================
         CONCEPTS — Verified Eco-Stays | Accessible Travel | Eco Passport
         These sections introduce the platform features. No booking logic yet.
         ==================================================================== -->
    <section class="concept-section" aria-labelledby="concepts-heading">
        <div class="section-head">
            <span class="section-tag">Platform Features</span>
            <h2 class="section-title" id="concepts-heading">Everything you need for responsible travel</h2>
            <p class="section-desc">Three pillars that make Way2Green different from any other travel platform.</p>
        </div>

        <div class="concept-grid">

            <!-- Verified Eco-Stays -->
            <div class="concept-card" id="concept-eco-stays">
                <div class="concept-icon-wrap" aria-hidden="true">🏡</div>
                <span class="concept-tag">Eco-Stays</span>
                <h3 class="concept-title">Verified Sustainable Accommodation</h3>
                <p class="concept-desc">
                    Every property on Way2Green is individually reviewed. We look for solar power, rainwater harvesting, zero single-use plastics, and organic local dining — so you never have to guess if a hotel is really green.
                </p>
                <a href="hotels.php" class="concept-link">
                    Browse Eco-Stays
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Accessible Travel -->
            <div class="concept-card" id="concept-accessible">
                <div class="concept-icon-wrap" aria-hidden="true">♿</div>
                <span class="concept-tag">Accessible Travel</span>
                <h3 class="concept-title">Built for Every Traveller</h3>
                <p class="concept-desc">
                    We physically verify step-free entrances, tactile signage, roll-in showers, and sensory-quiet zones. Sustainability must include everyone — 15% of the world lives with a disability, and nature should be welcoming for all.
                </p>
                <a href="hotels.php?filter=accessible" class="concept-link">
                    Find Accessible Stays
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Eco Passport -->
            <div class="concept-card" id="concept-passport">
                <div class="concept-icon-wrap" aria-hidden="true">📗</div>
                <span class="concept-tag">Eco Passport</span>
                <h3 class="concept-title">Your Verified Green Travel Record</h3>
                <p class="concept-desc">
                    Complete a trip to receive your digital Eco Passport — a verified, downloadable certificate showing your carbon savings, water conserved, and renewable energy used. A record you can share and be proud of.
                </p>
                <a href="<?= $user ? 'passport.php' : 'login.php' ?>" class="concept-link">
                    <?= $user ? 'View My Eco Passport' : 'Sign In to Get Started' ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>

        </div>
    </section>

    <hr class="section-divider" style="margin-top: 4rem;">

    <!-- ====================================================================
         ECO-STAYS PREVIEW — 3 featured stays from the database
         ==================================================================== -->
    <?php if (!empty($featuredHotels)): ?>
    <section class="page-container" aria-labelledby="stays-preview-heading" style="padding-top: 2rem;">
        <div class="section-head reveal-on-scroll">
            <span class="section-tag">Eco-Stays</span>
            <h2 class="section-title" id="stays-preview-heading">Featured Verified Stays</h2>
            <p class="section-desc">Handpicked properties running on renewable energy with barrier-free access.</p>
        </div>

        <div class="stays-grid">
            <?php foreach ($featuredHotels as $h):
                $badges = array_filter(array_map('trim', explode(',', $h['eco_badges'] ?? 'Solar Powered')));
                $access = array_filter(array_map('trim', explode(',', $h['accessibility_tags'] ?? 'Wheelchair Friendly')));
            ?>
            <div class="stay-card card-3d reveal-on-scroll">
                <div class="stay-img-box">
                    <img src="<?= htmlspecialchars($h['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80') ?>"
                         alt="<?= htmlspecialchars($h['name']) ?>"
                         loading="lazy"
                         onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
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
                        <?php foreach (array_slice($access, 0, 1) as $a): ?>
                            <span class="tag-badge tag-access">♿ <?= htmlspecialchars($a) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="stay-card-footer">
                        <div class="price-text">
                            <span class="amount">₹<?= number_format($h['price_per_night'] ?? 3500) ?></span>
                            <span>/ night</span>
                        </div>
                        <a href="hotels.php?dest=<?= urlencode($h['destination_name']) ?>" class="btn-select-stay">
                            View Stay ➔
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;" class="reveal-on-scroll">
            <a href="hotels.php" class="btn-nature-outline">
                Explore All Eco-Stays ➔
            </a>
        </div>
    </section>
    <?php endif; ?>

    <!-- ====================================================================
         FINAL CTA — Plan Your Trip
         ==================================================================== -->
    <section class="final-cta-section" aria-labelledby="final-cta-heading">
        <div class="final-cta-inner">
            <span class="section-tag">Ready to Travel?</span>
            <h2 class="final-cta-headline" id="final-cta-heading">
                Your sustainable journey starts here.
            </h2>
            <p class="final-cta-sub">
                Plan your route, choose an eco-friendly stay, and earn a verified record of your positive impact.
            </p>
            <a href="travel.php" class="btn-nature-white" id="final-cta-btn">
                Plan a Trip ➔
            </a>
        </div>
    </section>

    <!-- ====================================================================
         FOOTER — preserved structure, updated links
         ==================================================================== -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">
                    A conscious travel platform helping you plan greener journeys, find verified eco-stays, and earn your digital Eco Passport.
                </p>
            </div>

            <div>
                <h4 class="footer-heading">Navigate</h4>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="travel.php">Plan a Trip</a>
                    <a href="hotels.php">Eco-Stays</a>
                    <a href="my-trips.php">My Trips</a>
                    <a href="passport.php">Eco Passport</a>
                </div>
            </div>

            <div>
                <h4 class="footer-heading">Account</h4>
                <div class="footer-links">
                    <?php if ($user): ?>
                        <a href="my-trips.php">Profile (<?= htmlspecialchars($user['name']) ?>)</a>
                        <a href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a href="login.php">Sign In</a>
                        <a href="register.php">Create Account</a>
                    <?php endif; ?>
                    <a href="admin/login.php" style="color: var(--leaf);">Admin Portal</a>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            © 2026 Way2Green · Sustainable Travel for Everyone.
        </div>
    </footer>

    <!-- ====================================================================
         MOBILE BOTTOM NAVIGATION — updated labels
         ==================================================================== -->
    <div class="mobile-bottom-bar">
        <a href="index.php" class="mobile-nav-item active" aria-label="Home">
            <span class="icon">🏡</span>
            <span class="label">Home</span>
        </a>
        <a href="travel.php" class="mobile-nav-item" aria-label="Plan a Trip">
            <span class="icon">🗺️</span>
            <span class="label">Plan</span>
        </a>
        <a href="hotels.php" class="mobile-nav-item" aria-label="Eco-Stays">
            <span class="icon">🏡</span>
            <span class="label">Stays</span>
        </a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="mobile-nav-item" aria-label="My Trips">
                <span class="icon">🧳</span>
                <span class="label">My Trips</span>
            </a>
            <a href="passport.php" class="mobile-nav-item" aria-label="Eco Passport">
                <span class="icon">📗</span>
                <span class="label">Passport</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="mobile-nav-item" aria-label="Sign In">
                <span class="icon">👤</span>
                <span class="label">Sign In</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Effects (counter animations, particles) — existing, preserved -->
    <script src="js/effects.js"></script>

    <!-- Trip search — mock data and form logic (FUTURE API INTEGRATION — PHASE 7) -->
    <script src="js/trip-search.js"></script>

    <script>
        function toggleDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            const overlay = document.getElementById('drawerOverlay');
            drawer.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // Set min date on departure input to today
        (function() {
            var dep = document.getElementById('trip-departure');
            if (dep) {
                var today = new Date().toISOString().split('T')[0];
                dep.setAttribute('min', today);
            }
        })();
    </script>
</body>
</html>
