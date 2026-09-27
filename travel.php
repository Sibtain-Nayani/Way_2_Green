<?php
// travel.php - Phase 2: Navigation & Transit Redesign
require_once 'db.php';
require_once 'user_auth.php';

// Must be logged in to access the booking flow
require_user_login('travel.php');
$user = get_logged_in_user();

// Handle form submission
$errors = [];
$searchPerformed = false;
$tripData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $departure_date = trim($_POST['departure_date'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $trip_type = trim($_POST['trip_type'] ?? 'one-way');
    $travellers = (int)($_POST['travellers'] ?? 1);

    $today = date('Y-m-d');

    if (empty($origin)) {
        $errors[] = "Origin is required.";
    }
    if (empty($destination)) {
        $errors[] = "Destination is required.";
    }
    if (strtolower($origin) === strtolower($destination) && !empty($origin)) {
        $errors[] = "Origin and destination cannot be the same.";
    }
    if (empty($departure_date)) {
        $errors[] = "Departure date is required.";
    } elseif ($departure_date < $today) {
        $errors[] = "Departure date cannot be in the past.";
    }
    
    if ($trip_type === 'round-trip') {
        if (empty($return_date)) {
            $errors[] = "Return date is required for round trips.";
        } elseif ($return_date < $departure_date) {
            $errors[] = "Return date must be on or after the departure date.";
        }
    }

    if ($travellers < 1) {
        $errors[] = "At least 1 traveller is required.";
    }

    if (empty($errors)) {
        $searchPerformed = true;
        $tripData = [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'trip_type' => $trip_type,
            'travellers' => $travellers
        ];
        $_SESSION['trip_search'] = $tripData;
        
        // FUTURE API INTEGRATION — PHASE 7
        // Route generation logic would fetch real APIs here
    }
}

// Fetch destinations
$destinations = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $destinations = [];
}

$preselectedDest = $_GET['dest'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Transit — Way2Green</title>
    <meta name="description" content="Discover premium, eco-friendly transit options.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Phase 2: Premium Transit Redesign Specific Styles */
        .transit-hero {
            position: relative;
            width: 100%;
            height: 75vh;
            min-height: 600px;
            background: url('assets/img/hero-bg.jpg') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        
        .transit-hero::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.6) 100%);
            z-index: 1;
        }
        
        .transit-hero-content {
            position: relative;
            z-index: 2;
            padding: 0 20px;
        }
        
        .transit-title {
            font-size: 5rem;
            font-weight: 800;
            letter-spacing: -2px;
            margin: 0 0 10px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .transit-subtitle {
            font-size: 1.2rem;
            font-weight: 500;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.9;
        }
        
        .search-widget {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius-xl);
            padding: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            z-index: 3;
        }
        
        .search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)) auto;
            gap: 16px;
            align-items: end;
            color: var(--text-color);
        }
        
        .search-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            text-align: left;
        }
        
        .search-field label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        
        .search-field input, .search-field select {
            padding: 12px 16px;
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            font-family: inherit;
            font-size: 0.95rem;
            background: #fff;
            color: var(--text-color);
            transition: border-color 0.2s;
        }
        
        .search-field input:focus, .search-field select:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-md);
            padding: 14px 32px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            height: 48px;
            transition: transform 0.2s, background 0.2s;
        }
        
        .btn-search:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .results-section {
            padding: 120px 20px 80px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }
        
        .section-header h2 {
            font-size: 3rem;
            color: var(--text-color);
            margin: 0 0 10px;
            letter-spacing: -1px;
        }
        
        .route-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
        }
        
        .route-card {
            background: #fff;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            border: 1px solid var(--border-subtle);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }
        
        .route-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-card);
        }
        
        .route-badge {
            position: absolute;
            top: 16px;
            right: 16px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            background: #f0fdf4;
            color: #166534;
        }
        
        .route-card.recommended {
            border: 2px solid var(--primary);
        }
        
        .route-card.recommended .route-badge {
            background: var(--primary);
            color: #fff;
        }
        
        .route-image {
            height: 160px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
        }
        
        .route-content {
            padding: 24px;
        }
        
        .route-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 8px;
            color: var(--text-color);
        }
        
        .route-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
        }
        
        .route-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-color);
        }
        
        .btn-select {
            display: block;
            width: 100%;
            text-align: center;
            padding: 12px;
            background: #f8fcf8;
            color: var(--primary);
            border: 1px solid var(--primary);
            border-radius: var(--radius-md);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-select:hover {
            background: var(--primary);
            color: #fff;
        }
        
        /* Transparent nav overrides for hero */
        .header-top.transparent-header {
            position: absolute;
            top: 0; left: 0; right: 0;
            background: transparent;
            border-bottom: none;
            z-index: 10;
        }
        .header-top.transparent-header .desktop-nav a {
            color: rgba(255,255,255,0.9);
        }
        .header-top.transparent-header .desktop-nav a.active {
            color: white;
            border-bottom-color: white;
        }
        .header-top.transparent-header .brand img {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<body>
    
    <!-- Drawer Overlay & Mobile Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"> Way2Green
        </div>
        <a href="index.php" class="drawer-link">Home</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="travel.php" class="drawer-link" style="color: var(--primary);">Plan Transit</a>
        <a href="about.php" class="drawer-link">About Us</a>
        <a href="my-trips.php" class="drawer-link">My Passports</a>
        <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
    </div>

    <div class="transit-hero">
        <!-- Clean Header (Overlay on Hero) -->
        <header class="header-top transparent-header">
            <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>
            <nav class="desktop-nav">
<<<<<<< HEAD
            <a href="index.php">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php" class="active">Plan Transit</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a></nav>
=======
                <a href="index.php">Home</a>
                <a href="hotels.php">Eco-Stays</a>
                <a href="travel.php" class="active">Plan Transit</a>
                <a href="about.php">About Us</a>
                <a href="my-trips.php">My Passports</a>
                <a href="logout.php" style="color: #fca5a5;">Sign Out</a>
            </nav>
>>>>>>> 95927ad17535a90f2869adb9fd4075fb46196d00
            <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu" style="filter: brightness(0) invert(1);">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </header>

        <div class="transit-hero-content">
            <h1 class="transit-title">Travel</h1>
            <p class="transit-subtitle">Travel with intention. Discover active adventures and green transit options all in one place.</p>
        </div>

        <div class="search-widget">
            <?php if (!empty($errors)): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 10px; border-radius: var(--radius-md); margin-bottom: 1rem; border: 1px solid #f87171; text-align: left;">
                    <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.9rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="travel.php" onsubmit="return validateForm()">
                <div class="search-grid">
                    <div class="search-field">
                        <label>Trip Type</label>
                        <select name="trip_type" onchange="toggleReturnDate(this.value)">
                            <option value="one-way" <?= (empty($_POST['trip_type']) || $_POST['trip_type'] === 'one-way') ? 'selected' : '' ?>>One-way</option>
                            <option value="round-trip" <?= (isset($_POST['trip_type']) && $_POST['trip_type'] === 'round-trip') ? 'selected' : '' ?>>Round-trip</option>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Origin</label>
                        <input type="text" id="origin" name="origin" placeholder="Where from?" value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>" required>
                    </div>
                    <div class="search-field">
                        <label>Destination</label>
                        <select id="destination" name="destination" required>
                            <option value="">Where to?</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?= htmlspecialchars($d['name']) ?>" <?= ((isset($_POST['destination']) && $_POST['destination'] === $d['name']) || (!empty($preselectedDest) && stripos($d['name'], $preselectedDest) !== false)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="search-field">
                        <label>Departure</label>
                        <input type="date" id="departure_date" name="departure_date" value="<?= htmlspecialchars($_POST['departure_date'] ?? date('Y-m-d')) ?>" required>
                    </div>
                    <div class="search-field">
                        <label>Return</label>
                        <input type="date" id="return_date" name="return_date" value="<?= htmlspecialchars($_POST['return_date'] ?? '') ?>" <?= (empty($_POST['trip_type']) || $_POST['trip_type'] === 'one-way') ? 'disabled' : 'required' ?>>
                    </div>
                    <div class="search-field">
                        <label>Travellers</label>
                        <input type="number" id="travellers" name="travellers" min="1" value="<?= htmlspecialchars($_POST['travellers'] ?? 1) ?>" required style="width: 80px;">
                    </div>
                    <div>
                        <button type="submit" class="btn-search">Explore</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($searchPerformed && $tripData): ?>
    <main class="results-section" id="results">
        <div class="section-header">
            <h2 style="font-size: 2.2rem; font-weight: 500;">Way2Green means <br><span style="font-weight: 800;">Going Places</span></h2>
            <p style="color: var(--text-muted);">Here are the best options for your trip to <?= htmlspecialchars($tripData['destination']) ?>.</p>
        </div>

        <div class="route-options">
            <!-- Cheapest -->
            <div class="route-card">
                <div class="route-badge" style="background: #f1f5f9; color: #475569;">Cheapest</div>
                <div class="route-image">🚌</div>
                <div class="route-content">
                    <h3 class="route-title">Shared Coach</h3>
                    <div class="route-details">
                        <span>14h 30m</span>
                        <span>Direct</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span class="route-price">$24</span>
                        <span style="font-size: 0.8rem; color: var(--leaf);">Moderate CO₂</span>
                    </div>
                    <a href="journey.php" class="btn-select">Select</a>
                </div>
            </div>

            <!-- Fastest -->
            <div class="route-card">
                <div class="route-badge" style="background: #fef2f2; color: #b91c1c;">Fastest</div>
                <div class="route-image">✈️</div>
                <div class="route-content">
                    <h3 class="route-title">Direct Flight</h3>
                    <div class="route-details">
                        <span>2h 15m</span>
                        <span>Direct</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span class="route-price">$145</span>
                        <span style="font-size: 0.8rem; color: #ef4444;">High CO₂</span>
                    </div>
                    <a href="journey.php" class="btn-select">Select</a>
                </div>
            </div>

            <!-- Most Eco-Friendly -->
            <div class="route-card">
                <div class="route-badge">Most Eco-Friendly</div>
                <div class="route-image">🚆</div>
                <div class="route-content">
                    <h3 class="route-title">Electric Rail</h3>
                    <div class="route-details">
                        <span>18h 45m</span>
                        <span>1 Transfer</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span class="route-price">$42</span>
                        <span style="font-size: 0.8rem; color: var(--leaf); font-weight: 700;">-85% CO₂</span>
                    </div>
                    <a href="journey.php" class="btn-select">Select</a>
                </div>
            </div>

            <!-- Way2Green Recommended -->
            <div class="route-card recommended">
                <div class="route-badge">Way2Green Pick</div>
                <div class="route-image">🚄</div>
                <div class="route-content">
                    <h3 class="route-title">High-Speed Rail</h3>
                    <div class="route-details">
                        <span>6h 20m</span>
                        <span>Direct</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <span class="route-price">$85</span>
                        <span style="font-size: 0.8rem; color: var(--leaf); font-weight: 700;">-70% CO₂</span>
                    </div>
                    <a href="journey.php" class="btn-select" style="background: var(--primary); color: white;">Select Recommended</a>
                </div>
            </div>
            
            <!-- Custom Constraints -->
            <div class="route-card" style="border: 1px dashed var(--border-subtle); display: flex; flex-direction: column; align-items: center; justify-content: center; background: #fafafa; padding: 30px; text-align: center;">
                <div style="font-size: 2rem; margin-bottom: 10px;">⚙️</div>
                <h3 class="route-title">Custom Priorities</h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px;">Set specific limits for budget, time, and emissions.</p>
                <a href="#" class="btn-select" style="background: transparent;">Configure</a>
            </div>
        </div>
        
        <!-- FUTURE API INTEGRATION — PHASE 7 -->
        <div style="text-align: center; margin-top: 40px; color: var(--text-muted); font-size: 0.85rem;">
            // FUTURE API INTEGRATION — PHASE 7 <br> Route options will be populated dynamically via transit provider APIs.
        </div>
    </main>
    
    <script>
        // Scroll to results if search performed
        window.addEventListener('DOMContentLoaded', () => {
            const results = document.getElementById('results');
            if (results) {
                results.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>
    <?php else: ?>
    <!-- Empty state before search -->
    <main class="results-section">
        <div class="section-header">
            <h2 style="font-size: 2.2rem; font-weight: 500;">Way2Green means <br><span style="font-weight: 800;">Going Places</span></h2>
            <p style="color: var(--text-muted);">Enter a destination above to find sustainable travel options.</p>
        </div>
    </main>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 24px; width: auto;"></div>
                <p class="footer-text">Empowering travelers with honest carbon transparency and barrier-free stays.</p>
            </div>
            <div>
                <h4 class="footer-heading">Navigation</h4>
                <div class="footer-links">
                    <a href="index.php">Home</a>
                    <a href="hotels.php">Eco-Stays</a>
                    <a href="travel.php">Plan Transit</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Account</h4>
                <div class="footer-links">
                    <a href="my-trips.php">Signed in as <?= htmlspecialchars($user['name']) ?></a>
                    <a href="logout.php">Sign Out</a>
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
        <a href="hotels.php" class="mobile-nav-item">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="travel.php" class="mobile-nav-item active">
            <span class="icon">🚆</span>
            <span class="label">Transit</span>
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

    <script src="js/effects.js"></script>
    <script>
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        function toggleReturnDate(val) {
            const isRoundTrip = (val === 'round-trip');
            const returnDateInput = document.getElementById('return_date');
            returnDateInput.disabled = !isRoundTrip;
            if (isRoundTrip) {
                returnDateInput.required = true;
            } else {
                returnDateInput.required = false;
                returnDateInput.value = '';
            }
        }

        function validateForm() {
            const origin = document.getElementById('origin').value.trim();
            const destination = document.getElementById('destination').value.trim();
            const departureDate = document.getElementById('departure_date').value;
            const returnDate = document.getElementById('return_date').value;
            const isRoundTrip = document.querySelector('select[name="trip_type"]').value === 'round-trip';
            const today = new Date().toISOString().split('T')[0];

            if (origin.toLowerCase() === destination.toLowerCase() && origin !== '') {
                alert("Origin and destination cannot be the same.");
                return false;
            }
            if (departureDate < today) {
                alert("Departure date cannot be in the past.");
                return false;
            }
            if (isRoundTrip) {
                if (!returnDate) {
                    alert("Return date is required for round trips.");
                    return false;
                }
                if (returnDate < departureDate) {
                    alert("Return date must be on or after the departure date.");
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>

