<?php
// travel.php - Phase 1: Transit & Carbon Footprint Calculation
require_once 'db.php';
require_once 'user_auth.php';

// Must be logged in to access the booking flow
require_user_login('travel.php');
$user = get_logged_in_user();

// Fetch destinations
$destinations = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $destinations = [];
}

$preselectedDest = $_GET['dest'] ?? 'Destination';
$origin = $_GET['origin'] ?? 'Origin';
$stops = $_GET['stops'] ?? [];

// Create a unified route array for display
$fullRoute = [$origin];
foreach ($stops as $stop) {
    if (!empty(trim($stop))) $fullRoute[] = trim($stop);
}
$fullRoute[] = $preselectedDest;

$hotel_id = $_GET['hotel_id'] ?? '';
$hotel_name = $_GET['hotel_name'] ?? '';
$hotel_price = $_GET['hotel_price'] ?? 0;
$hotel_water = $_GET['hotel_water'] ?? 0;
$hotel_power = $_GET['hotel_power'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 1: Low-Carbon Transit Planner — Way2Green</title>
    <meta name="description"
        content="Calculate your travel carbon footprint and discover cleaner routes with Gemini AI.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
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
        <div
            style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link">Home</a>
        <a href="travel.php" class="drawer-link" style="color: var(--primary);">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="about.php" class="drawer-link">Our Mission</a>
        <a href="my-trips.php" class="drawer-link">My Passports</a>
        <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out
            (<?= htmlspecialchars($user['name']) ?>)</a>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin
            Portal</a>
    </div>

    <!-- Clean Header -->
    <?php include 'components/navbar.php'; ?>

    <main class="page-container" style="max-width: 980px;">
        <!-- Visual Multi-Step Tracker -->
        

        <div class="card-box card-3d reveal-on-scroll">
            <span class="section-tag">Step 1 of 3</span>
            <h1 style="color: var(--primary); font-size: 1.8rem; margin: 4px 0 6px;">Plan Your Low-Carbon Transit</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.8rem;">
                Hi <strong><?= htmlspecialchars($user['name']) ?></strong>! Enter your travel route to calculate carbon
                emissions and receive smart travel tips.
            </p>

            <form id="transitForm" onsubmit="event.preventDefault(); computeImpact();">
                <div style="margin-bottom: 2rem;">
                    <!-- Unified Route Itinerary Display -->
                    <div style="width: 100%;">
                        <label class="field-label">Your Sustainable Roadmap:</label>
                        <div style="background: rgba(255,255,255,0.8); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 15px;">
                            <?php foreach ($fullRoute as $index => $location): ?>
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: <?= ($index === count($fullRoute)-1) ? '0' : '10px' ?>;">
                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: <?= ($index === 0) ? '#073B2A' : (($index === count($fullRoute)-1) ? '#29AB87' : '#9ca3af') ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div style="font-weight: 700; color: var(--text-dark); font-size: 1.1rem; flex: 1;">
                                        <?= htmlspecialchars($location) ?>
                                    </div>
                                </div>
                                <?php if ($index < count($fullRoute)-1): ?>
                                    <div style="margin-left: 11px; padding: 4px 0; border-left: 2px dashed #cbd5e1; height: 20px;"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Mode Cards -->
                <div style="margin-bottom: 1.8rem;">
                    <label class="field-label">Select How You Will Travel:</label>
                    <div class="modes-row">
                        <div class="mode-pill selected" data-mode="train" onclick="selectMode('train')">
                            <div class="m-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="4" y="3" width="16" height="16" rx="2" />
                                    <path d="M4 11h16" />
                                    <path d="M12 3v8" />
                                    <path d="m8 19-2 3" />
                                    <path d="m18 22-2-3" />
                                    <circle cx="8" cy="15" r="1" />
                                    <circle cx="16" cy="15" r="1" />
                                </svg>
                            </div>
                            <div class="m-title">Electric Rail</div>
                            <div class="m-sub">82% Cleaner</div>
                        </div>
                        <div class="mode-pill" data-mode="ev" onclick="selectMode('ev')">
                            <div class="m-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2" />
                                </svg>
                            </div>
                            <div class="m-title">Electric Car</div>
                            <div class="m-sub">Zero Tailpipe</div>
                        </div>
                        <div class="mode-pill" data-mode="bus" onclick="selectMode('bus')">
                            <div class="m-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M8 6v6" />
                                    <path d="M15 6v6" />
                                    <path d="M2 12h19.6" />
                                    <path
                                        d="M18 18h3s.5-1.7.8-2.8c.1-.4.2-.8.2-1.2 0-.4-.1-.8-.2-1.2l-1.4-5C20.1 6.8 19.1 6 18 6H4a2 2 0 0 0-2 2v10h3" />
                                    <circle cx="7" cy="18" r="2" />
                                    <path d="M9 18h5" />
                                    <circle cx="16" cy="18" r="2" />
                                </svg>
                            </div>
                            <div class="m-title">Shared Coach</div>
                            <div class="m-sub">High Efficiency</div>
                        </div>
                        <div class="mode-pill" data-mode="car" onclick="selectMode('car')">
                            <div class="m-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 11 2 11.4V16c0 .6.4 1 1 1h2" />
                                    <circle cx="7" cy="17" r="2" />
                                    <path d="M9 17h6" />
                                    <circle cx="17" cy="17" r="2" />
                                </svg>
                            </div>
                            <div class="m-title">Petrol Car</div>
                            <div class="m-sub">Baseline Driving</div>
                        </div>
                        <div class="mode-pill" data-mode="flight" onclick="selectMode('flight')">
                            <div class="m-icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z" />
                                </svg>
                            </div>
                            <div class="m-title">Short Flight</div>
                            <div class="m-sub">Max Footprint</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-nature-primary"
                    style="width: 100%; justify-content: center; padding: 15px;">
                    Calculate Carbon Impact ➔
                </button>
            </form>

            <!-- Results Card -->
            <div id="resultsCard"
                style="display: none; margin-top: 2rem; border-top: 1px dashed var(--border-subtle); padding-top: 2rem;">
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">

                    <!-- Carbon Score Box -->
                    <div
                        style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #ffffff; border-radius: var(--radius-lg); padding: 1.8rem;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span
                                style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Trip
                                Footprint</span>
                            <span
                                style="background: rgba(82, 183, 136, 0.3); color: var(--leaf); padding: 3px 8px; border-radius: 99px; font-size: 0.75rem; font-weight: 800;"
                                id="reductionBadge">82% LESS CO₂</span>
                        </div>
                        <div style="font-size: 2.5rem; font-weight: 800; line-height: 1;" id="co2Val">
                            18.9 <span style="font-size: 1rem; font-weight: 500;">kg CO₂</span>
                        </div>
                        <p style="font-size: 0.82rem; color: rgba(255, 255, 255, 0.8); margin-top: 6px;"
                            id="tripDetailText">
                            Distance: ~540 km via Electric Train
                        </p>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 1.5rem;">
                            <div style="background: rgba(255,255,255,0.12); padding: 10px; border-radius: 8px;">
                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--leaf);" id="co2SavedVal">
                                    84.8 kg</div>
                                <div style="font-size: 0.72rem; color: rgba(255,255,255,0.85);">CO₂ Avoided</div>
                            </div>
                            <div style="background: rgba(255,255,255,0.12); padding: 10px; border-radius: 8px;">
                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--leaf);" id="treesVal">3.9
                                    Trees</div>
                                <div style="font-size: 0.72rem; color: rgba(255,255,255,0.85);">Equiv. Absorbed</div>
                            </div>
                        </div>
                    </div>

                    <!-- Gemini AI Advisor Box -->
                    <div
                        style="background: #ffffff; border: 1.5px solid #d1e7dd; border-radius: var(--radius-lg); padding: 1.8rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <span style="font-weight: 800; color: var(--primary); font-size: 1rem;">✨ Gemini AI
                                    Travel Insights</span>
                                <span
                                    style="background: #e0f2fe; color: #0369a1; font-size: 0.72rem; font-weight: 700; padding: 3px 8px; border-radius: 99px;">Smart
                                    Advice</span>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary);">🚆 Route Tip:
                                </div>
                                <p id="aiTransit"
                                    style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.45;">Loading
                                    transit suggestions...</p>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary);">🌿 Destination
                                    Stewardship:</div>
                                <p id="aiHotel"
                                    style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.45;">Loading
                                    local guidelines...</p>
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary);">♿ Accessible
                                    Travel:</div>
                                <p id="aiAccess"
                                    style="font-size: 0.88rem; color: var(--text-muted); line-height: 1.45;">Loading
                                    accessibility tips...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connect Phase 2 to Phase 3 -->
                <div style="text-align: center;">
                    <a href="#" id="continueToHotelsBtn" class="btn-nature-primary"
                        style="padding: 16px 36px; font-size: 1.05rem;">
                        Proceed to Checkout ➔
                    </a>
                </div>
            </div>
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
        let currentMode = 'train';
        let lastCalculation = null;

        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        function setOrigin(city) {
            document.getElementById('sourceInput').value = city;
            computeImpact();
        }

        function selectMode(mode) {
            currentMode = mode;
            document.querySelectorAll('.mode-pill').forEach(p => {
                p.classList.toggle('selected', p.getAttribute('data-mode') === mode);
            });
            computeImpact();
        }

        function useGeolocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(() => {
                    document.getElementById('sourceInput').value = "My Current GPS Location";
                    computeImpact();
                }, () => alert("Location access was denied."));
            }
        }

        const fullRoute = <?= json_encode($fullRoute) ?>;

        function getApproxDistance(source, destination) {
            const map = {
                'mumbai_munnar': 1380,
                'bengaluru_munnar': 475,
                'delhi_manali': 535,
                'mumbai_goa': 580,
                'bengaluru_wayanad': 280,
                'delhi_rishikesh': 240,
                'chennai_ooty': 540,
                'delhi_agra': 233,
                'agra_jaipur': 240,
                'jaipur_delhi': 280
            };
            const key = (source.toLowerCase().split(/[\s,]+/)[0] + '_' + destination.toLowerCase().split(/[\s,]+/)[0]);
            return map[key] || 350; // Fallback distance for unknown legs
        }

        async function computeImpact() {
            let totalDistance = 0;
            for (let i = 0; i < fullRoute.length - 1; i++) {
                totalDistance += getApproxDistance(fullRoute[i], fullRoute[i+1]);
            }
            
            const source = fullRoute[0];
            const destination = fullRoute[fullRoute.length - 1];
            const distance = totalDistance;

            document.getElementById('resultsCard').style.display = 'block';
            document.getElementById('tripDetailText').innerText = `Total Distance: ~${distance} km via ${currentMode.toUpperCase()}`;

            try {
                const res = await fetch('api/get_suggestions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        source: source,
                        destination: destination,
                        mode: currentMode,
                        distance_km: distance
                    })
                });
                const data = await res.json();
                if (data.status === 'success') {
                    lastCalculation = data;
                    document.getElementById('co2Val').innerHTML = `${data.emissions.chosen_co2_kg} <span style="font-size: 1rem; font-weight: 500;">kg CO₂</span>`;
                    document.getElementById('co2SavedVal').innerText = `${data.emissions.co2_saved_kg} kg`;
                    document.getElementById('treesVal').innerText = `${data.emissions.trees_equivalent} Trees`;
                    document.getElementById('reductionBadge').innerText = `${data.emissions.percent_reduction}% LESS CO₂`;

                    document.getElementById('aiTransit').innerHTML = data.ai_insights.transit_advice;
                    document.getElementById('aiHotel').innerHTML = data.ai_insights.hotel_advice;
                    document.getElementById('aiAccess').innerHTML = data.ai_insights.accessibility_advice;

                    // Build link to Checkout
                    const params = new URLSearchParams({
                        hotel_id: "<?= htmlspecialchars($hotel_id) ?>",
                        hotel_name: "<?= htmlspecialchars($hotel_name) ?>",
                        price: "<?= htmlspecialchars($hotel_price) ?>",
                        water_saved: "<?= htmlspecialchars($hotel_water) ?>",
                        power_saved: "<?= htmlspecialchars($hotel_power) ?>",
                        dest_name: destination,
                        origin: source,
                        mode: currentMode,
                        distance: distance,
                        co2_saved: data.emissions.co2_saved_kg
                    });
                    document.getElementById('continueToHotelsBtn').href = `checkout.php?${params.toString()}`;
                }
            } catch (err) {
                console.error(err);
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            computeImpact();
        });
    </script>
</body>

</html>


