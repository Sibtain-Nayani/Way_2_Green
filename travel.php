<?php
// travel.php - Phase 1: Trip Search Foundation
require_once 'db.php';
require_once 'user_auth.php';

// Must be logged in to access the booking flow
require_user_login('travel.php');
$user = get_logged_in_user();

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $departure_date = trim($_POST['departure_date'] ?? '');
    $return_date = trim($_POST['return_date'] ?? '');
    $trip_type = trim($_POST['trip_type'] ?? 'one-way');
    $travellers = (int)($_POST['travellers'] ?? 1);
    $preference = trim($_POST['preference'] ?? 'Way2Green Pick');

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
        $_SESSION['trip_search'] = [
            'origin' => $origin,
            'destination' => $destination,
            'departure_date' => $departure_date,
            'return_date' => $return_date,
            'trip_type' => $trip_type,
            'travellers' => $travellers,
            'preference' => $preference
        ];

        // FUTURE API INTEGRATION — PHASE 7
        // Save search history and trigger partner API pre-fetch

        header("Location: journey.php");
        exit;
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
    <title>Step 1: Low-Carbon Transit Planner — Way2Green</title>
    <meta name="description" content="Calculate your travel carbon footprint and discover cleaner routes with Gemini AI.">
    
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
        <a href="travel.php" class="drawer-link" style="color: var(--primary);">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
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
            <a href="travel.php" class="active">Plan Transit</a>
            <a href="hotels.php">Eco-Stays</a>
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

    <main class="page-container" style="max-width: 980px;">
        <!-- Visual Multi-Step Tracker -->
        <div class="step-progress-bar">
            <div class="step-bubble active">
                <span class="step-num">1</span>
                <span>Trip Search</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble">
                <span class="step-num">2</span>
                <span>Select Journey</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble">
                <span class="step-num">3</span>
                <span>Eco-Passport</span>
            </div>
        </div>

        <div class="card-box card-3d reveal-on-scroll">
            <span class="section-tag">Step 1 of 3</span>
            <h1 style="color: var(--primary); font-size: 1.8rem; margin: 4px 0 6px;">Plan Your Trip</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.8rem;">
                Hi <strong><?= htmlspecialchars($user['name']) ?></strong>! Enter your travel details to find the best routes.
            </p>

            <?php if (!empty($errors)): ?>
                <div style="background: #fef2f2; color: #991b1b; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; border: 1px solid #f87171;">
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="transitForm" method="POST" action="travel.php" onsubmit="return validateForm()">
                <div class="transit-input-grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    
                    <!-- Trip Type -->
                    <div style="grid-column: span 2;">
                        <label class="field-label">Trip Type</label>
                        <div style="display: flex; gap: 1rem;">
                            <label><input type="radio" name="trip_type" value="one-way" <?= (empty($_POST['trip_type']) || $_POST['trip_type'] === 'one-way') ? 'checked' : '' ?> onchange="toggleReturnDate()"> One-way</label>
                            <label><input type="radio" name="trip_type" value="round-trip" <?= (isset($_POST['trip_type']) && $_POST['trip_type'] === 'round-trip') ? 'checked' : '' ?> onchange="toggleReturnDate()"> Round-trip</label>
                        </div>
                    </div>

                    <!-- Origin -->
                    <div>
                        <label class="field-label" for="origin">Origin</label>
                        <input type="text" id="origin" name="origin" class="field-input" placeholder="e.g. Mumbai" value="<?= htmlspecialchars($_POST['origin'] ?? '') ?>" required>
                    </div>

                    <!-- Destination -->
                    <div>
                        <label class="field-label" for="destination">Destination</label>
                        <select id="destination" name="destination" class="field-select" required>
                            <option value="">Select Destination...</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?= htmlspecialchars($d['name']) ?>" 
                                        <?= ((isset($_POST['destination']) && $_POST['destination'] === $d['name']) || (!empty($preselectedDest) && stripos($d['name'], $preselectedDest) !== false)) ? 'selected' : '' ?>>
                                    📍 <?= htmlspecialchars($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Departure Date -->
                    <div>
                        <label class="field-label" for="departure_date">Departure Date</label>
                        <input type="date" id="departure_date" name="departure_date" class="field-input" value="<?= htmlspecialchars($_POST['departure_date'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <!-- Return Date -->
                    <div>
                        <label class="field-label" for="return_date">Return Date</label>
                        <input type="date" id="return_date" name="return_date" class="field-input" value="<?= htmlspecialchars($_POST['return_date'] ?? '') ?>" <?= (empty($_POST['trip_type']) || $_POST['trip_type'] === 'one-way') ? 'disabled' : 'required' ?>>
                    </div>

                    <!-- Travellers -->
                    <div>
                        <label class="field-label" for="travellers">Travellers</label>
                        <input type="number" id="travellers" name="travellers" class="field-input" min="1" value="<?= htmlspecialchars($_POST['travellers'] ?? 1) ?>" required>
                    </div>

                    <!-- Preference -->
                    <div>
                        <label class="field-label" for="preference">Preference</label>
                        <select id="preference" name="preference" class="field-select">
                            <option value="Way2Green Pick" <?= (isset($_POST['preference']) && $_POST['preference'] === 'Way2Green Pick') ? 'selected' : '' ?>>Way2Green Pick</option>
                            <option value="Lowest Price" <?= (isset($_POST['preference']) && $_POST['preference'] === 'Lowest Price') ? 'selected' : '' ?>>Lowest Price</option>
                            <option value="Fastest" <?= (isset($_POST['preference']) && $_POST['preference'] === 'Fastest') ? 'selected' : '' ?>>Fastest</option>
                            <option value="Lower Impact" <?= (isset($_POST['preference']) && $_POST['preference'] === 'Lower Impact') ? 'selected' : '' ?>>Lower Impact</option>
                        </select>
                    </div>

                </div>

                <button type="submit" class="btn-nature-primary" style="width: 100%; justify-content: center; padding: 15px;">
                    Search Journeys ➔
                </button>
            </form>
        </div>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-grid">
            <div>
                <div class="footer-brand">🌍 Way2Green</div>
                <p class="footer-text">Empowering travelers with honest carbon transparency and barrier-free stays.</p>
            </div>
            <div>
                <h4 class="footer-heading">Steps</h4>
                <div class="footer-links">
                    <a href="travel.php">Step 1: Transit Planner</a>
                    <a href="hotels.php">Step 2: Eco-Hotels</a>
                    <a href="my-trips.php">Step 3: Eco-Passports</a>
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
        <a href="travel.php" class="mobile-nav-item active">
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

        function toggleReturnDate() {
            const isRoundTrip = document.querySelector('input[name="trip_type"][value="round-trip"]').checked;
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
            const isRoundTrip = document.querySelector('input[name="trip_type"][value="round-trip"]').checked;
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
