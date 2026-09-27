<?php
// my-trips.php - Traveler's Saved Eco-Passports & Live Boarding Pass
require_once 'db.php';
require_once 'user_auth.php';

require_user_login('my-trips.php');
$user = get_logged_in_user();

$bookings = [];
try {
    $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name, h.image_url, h.water_saved_liters, h.power_saved_kwh, h.eco_rating 
        FROM bookings b 
        JOIN hotels h ON b.hotel_id = h.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC");
    $stmt->execute([$user['id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $bookings = [];
}

// User Eco Resurrection & Contribution Aggregation
$totalTrips = count($bookings);
$totalCo2Saved = 0;
$totalWaterSaved = 0;
$totalPowerSaved = 0;
$latestBooking = !empty($bookings) ? $bookings[0] : null;

foreach ($bookings as $b) {
    $totalCo2Saved += floatval($b['co2_saved_kg'] ?? 0);
    $totalWaterSaved += floatval($b['water_saved_liters'] ?? 0);
    $totalPowerSaved += floatval($b['power_saved_kwh'] ?? 0);
}

// Eco resurrection benchmark metrics if hotel records are newly established
if ($totalTrips > 0) {
    if ($totalWaterSaved == 0) {
        $totalWaterSaved = $totalTrips * 12500;
    }
    if ($totalPowerSaved == 0) {
        $totalPowerSaved = $totalTrips * 340;
    }
}

// Latest Trip Details for Reference Pass Display
$latestOrigin = $latestBooking['origin'] ?? 'BOM';
$latestDest = $latestBooking['destination'] ?? 'GOA';
$latestMode = strtoupper($latestBooking['travel_mode'] ?? 'TRANSIT');
$latestDateStr = $latestBooking ? date('d M', strtotime($latestBooking['check_in'])) : date('d M');
$latestCode = $latestBooking ? $latestBooking['booking_code'] : 'W2G-102';

$originCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $latestOrigin) ?: 'DEP', 0, 3));
$destCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $latestDest) ?: 'ARR', 0, 3));

$flightNumber = 'W2G-' . (preg_replace('/[^0-9]/', '', $latestCode) ?: '102');
if (strlen($flightNumber) > 8) {
    $flightNumber = substr($flightNumber, 0, 7);
}

$transitPrefix = 'FLIGHT';
if (stripos($latestMode, 'RAIL') !== false || stripos($latestMode, 'TRAIN') !== false) {
    $transitPrefix = 'RAIL';
} elseif (stripos($latestMode, 'BUS') !== false) {
    $transitPrefix = 'COACH';
} elseif (stripos($latestMode, 'EV') !== false || stripos($latestMode, 'CAR') !== false) {
    $transitPrefix = 'EV-CORRIDOR';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Eco-Passports & Boarding Pass — Way2Green</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Split Layout 25% - 75% */
        .trips-page-main {
            max-width: 1360px !important;
            margin: 0 auto 3rem !important;
            padding: 1.5rem 1.5rem 3rem !important;
        }

        .trips-split-layout {
            display: grid;
            grid-template-columns: 310px 1fr;
            gap: 2.2rem;
            align-items: start;
            width: 100%;
        }

        @media (min-width: 1280px) {
            .trips-split-layout {
                grid-template-columns: 27% 73%;
            }
        }

        @media (max-width: 1024px) {
            .trips-split-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        .trips-card-col {
            position: sticky;
            top: 1.5rem;
            z-index: 10;
        }

        @media (max-width: 1024px) {
            .trips-card-col {
                position: static;
            }
        }

        .trips-content-col {
            min-width: 0;
            width: 100%;
        }

        /* ==========================================================================
           REFERENCE-MATCHED ECO BOARDING PASS TICKET CARD (25% COLUMN)
           ========================================================================== */
        .ref-boarding-pass {
            width: 100%;
            max-width: 330px;
            margin: 0 auto;
            background-color: #eee7dc;
            background-image: 
                radial-gradient(#d3cbba 0.75px, transparent 0.75px),
                radial-gradient(#d3cbba 0.75px, #eee7dc 0.75px);
            background-size: 16px 16px;
            background-position: 0 0, 8px 8px;
            border-radius: 20px;
            border: 1px solid #d4ccbd;
            box-shadow: 0 16px 36px rgba(26, 46, 32, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            transition: var(--transition);
        }

        .ref-boarding-pass:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 46px rgba(26, 46, 32, 0.18), 0 4px 12px rgba(0, 0, 0, 0.06);
        }

        /* Solid Olive/Sage Green Vertical Stripe on Left Edge */
        .pass-left-stripe {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 25px;
            background-color: #4b8259;
            z-index: 1;
            border-top-left-radius: 19px;
            border-bottom-left-radius: 19px;
        }

        /* Perforation Tear Notch Rows (Upper & Lower) */
        .pass-perforation {
            position: relative;
            height: 24px;
            margin: 2px 0;
            display: flex;
            align-items: center;
            z-index: 5;
        }

        .pass-notch-left,
        .pass-notch-right {
            position: absolute;
            width: 24px;
            height: 24px;
            background-color: var(--bg-main, #f3f8f5);
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 6;
            box-shadow: inset 0 0 0 1px #d4ccbd;
        }

        .pass-notch-left {
            left: -12px;
        }

        .pass-notch-right {
            right: -12px;
        }

        .pass-dashed-line {
            width: calc(100% - 24px);
            margin: 0 auto;
            border-top: 1.5px dashed #a8a090;
            position: relative;
            z-index: 4;
        }

        /* Top Header Section */
        .pass-top-header {
            position: relative;
            z-index: 2;
            padding: 1.3rem 1.2rem 0.5rem 2.2rem; /* Left padding clears stripe */
            text-align: center;
        }

        .pass-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 800;
            font-size: 1.15rem;
            color: #14532d;
            letter-spacing: -0.3px;
        }

        .pass-subhead {
            font-size: 0.68rem;
            font-weight: 800;
            color: #27372c;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        /* Middle Main Body Section */
        .pass-main-body {
            position: relative;
            z-index: 2;
            padding: 0.6rem 1.4rem 0.6rem 2.4rem; /* Left padding clears stripe */
        }

        .pass-traveler-name {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .pass-flight-code {
            font-size: 0.72rem;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .pass-route-date {
            font-size: 1.05rem;
            font-weight: 800;
            color: #043d22;
            margin-bottom: 0.85rem;
            letter-spacing: -0.2px;
        }

        /* Barcode Area */
        .pass-barcode-wrap {
            background: rgba(255, 255, 255, 0.45);
            border: 1px solid #dfd8cb;
            border-radius: 8px;
            padding: 8px 6px 4px;
            text-align: center;
            margin: 0.6rem 0;
        }

        .pass-barcode-bars {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 2.2px;
            height: 48px;
            margin-bottom: 4px;
            overflow: hidden;
        }

        .p-bar {
            background: #111827;
            height: 100%;
            border-radius: 1px;
            display: inline-block;
        }
        .p-w1 { width: 1.5px; }
        .p-w2 { width: 3px; }
        .p-w3 { width: 4.5px; }
        .p-w4 { width: 6px; }

        .pass-seat-group {
            text-align: center;
            margin-top: 0.4rem;
        }

        .pass-seat-lbl {
            font-size: 0.65rem;
            font-weight: 800;
            color: #4b5563;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .pass-seat-val {
            font-size: 1.45rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
        }

        /* Bottom Stub Section (Eco Resurrection & Stats) */
        .pass-bottom-stub {
            position: relative;
            z-index: 2;
            padding: 0.6rem 1.4rem 1.4rem 2.4rem; /* Left padding clears stripe */
        }

        .pass-stub-lbl {
            font-size: 0.65rem;
            font-weight: 800;
            color: #4b5563;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .pass-stub-name {
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .pass-resurrection-title {
            font-size: 0.65rem;
            font-weight: 800;
            color: #065f46;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .pass-stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            margin-bottom: 12px;
        }

        .pass-stat-pill {
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid #dcd4c5;
            border-radius: 6px;
            padding: 5px 8px;
        }

        .pass-stat-pill-label {
            font-size: 0.58rem;
            font-weight: 700;
            color: #526056;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .pass-stat-pill-val {
            font-size: 0.82rem;
            font-weight: 800;
            color: #064e3b;
        }

        .btn-latest-booking {
            display: block;
            width: 100%;
            background: #0f3d24;
            color: #ffffff !important;
            text-align: center;
            padding: 9px 12px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 0.82rem;
            box-shadow: 0 4px 12px rgba(15, 61, 36, 0.22);
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-latest-booking:hover {
            background: #1b633d;
            transform: translateY(-2px);
            color: #ffffff !important;
            box-shadow: 0 6px 16px rgba(15, 61, 36, 0.32);
        }

        /* Summary header pill in 75% column */
        .vault-summary-strip {
            background: #ffffff;
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-md);
            padding: 1rem 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }
    </style>
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
        <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>
        <nav class="desktop-nav">
            <a href="index.php">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Plan Transit</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php" class="active">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a></nav>
        <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </header>

    <main class="page-container trips-page-main">
        
        <!-- Header Banner -->
        <div class="section-head reveal-on-scroll" style="text-align: left; margin-bottom: 2rem;">
            <span class="section-tag">Traveler Passport Vault</span>
            <h1 class="section-title">My Verified Eco-Passports & Stays</h1>
            <p class="section-desc" style="margin: 0;">Welcome, <strong><?= htmlspecialchars($user['name']) ?></strong>! Track your net-zero journeys and verified eco-resurrection impact.</p>
        </div>

        <!-- 25% - 75% Split Layout -->
        <div class="trips-split-layout">

            <!-- 25% Left Column: Reference-Matched Eco Boarding Pass Ticket Card -->
            <aside class="trips-card-col">
                <div class="ref-boarding-pass">
                    <!-- Solid Green Stripe along Left Edge -->
                    <div class="pass-left-stripe"></div>

                    <!-- Top Header Section -->
                    <div class="pass-top-header">
                        <div class="pass-logo">
                            <span>🌱</span>
                            <span>Way2Green</span>
                        </div>
                        <div class="pass-subhead">
                            BOARDING PASS
                        </div>
                    </div>

                    <!-- Upper Perforation Notch Row -->
                    <div class="pass-perforation">
                        <div class="pass-notch-left"></div>
                        <div class="pass-dashed-line"></div>
                        <div class="pass-notch-right"></div>
                    </div>

                    <!-- Middle Main Section -->
                    <div class="pass-main-body">
                        <div class="pass-traveler-name"><?= htmlspecialchars($user['name']) ?></div>
                        <div class="pass-flight-code"><?= htmlspecialchars($transitPrefix) ?> <?= htmlspecialchars($flightNumber) ?></div>
                        <div class="pass-route-date"><?= htmlspecialchars($originCode) ?> ➔ <?= htmlspecialchars($destCode) ?> | <?= strtoupper($latestDateStr) ?></div>

                        <!-- Barcode Graphic -->
                        <div class="pass-barcode-wrap">
                            <div class="pass-barcode-bars" aria-hidden="true">
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w3"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w4"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w3"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w3"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w4"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w3"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w4"></span>
                                <span class="p-bar p-w1"></span>
                                <span class="p-bar p-w2"></span>
                                <span class="p-bar p-w3"></span>
                            </div>
                            <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.72rem; color: #4b5563; font-weight: 700; letter-spacing: 1.5px;">
                                <?= htmlspecialchars($latestCode) ?>
                            </div>
                        </div>

                        <div class="pass-seat-group">
                            <div class="pass-seat-lbl">SEAT / CLASS</div>
                            <div class="pass-seat-val"><?= $latestBooking ? '12A' : 'ECO-PASS' ?></div>
                        </div>
                    </div>

                    <!-- Lower Perforation Notch Row -->
                    <div class="pass-perforation">
                        <div class="pass-notch-left"></div>
                        <div class="pass-dashed-line"></div>
                        <div class="pass-notch-right"></div>
                    </div>

                    <!-- Bottom Stub Section (Eco Resurrection Contributions) -->
                    <div class="pass-bottom-stub">
                        <div class="pass-stub-lbl">TRAVELER INFO</div>
                        <div class="pass-stub-name"><?= htmlspecialchars($user['name']) ?></div>

                        <div class="pass-resurrection-title">
                            <span>🌿</span> Eco Resurrection Stats
                        </div>

                        <div class="pass-stats-grid">
                            <div class="pass-stat-pill">
                                <div class="pass-stat-pill-label">CO₂ AVOIDED</div>
                                <div class="pass-stat-pill-val">🌱 <?= number_format($totalCo2Saved, 1) ?> kg</div>
                            </div>
                            <div class="pass-stat-pill">
                                <div class="pass-stat-pill-label">WATER SAVED</div>
                                <div class="pass-stat-pill-val">💧 <?= number_format($totalWaterSaved) ?> L</div>
                            </div>
                            <div class="pass-stat-pill">
                                <div class="pass-stat-pill-label">CLEAN SOLAR</div>
                                <div class="pass-stat-pill-val">⚡ <?= number_format($totalPowerSaved) ?> kWh</div>
                            </div>
                            <div class="pass-stat-pill">
                                <div class="pass-stat-pill-label">ECO-TRIPS</div>
                                <div class="pass-stat-pill-val">🌍 <?= $totalTrips ?> Trips</div>
                            </div>
                        </div>

                        <!-- Action Button to Show Latest Booking -->
                        <?php if ($latestBooking): ?>
                            <a href="passport.php?code=<?= urlencode($latestBooking['booking_code']) ?>" class="btn-latest-booking">
                                🎫 Show Latest Booking ➔
                            </a>
                        <?php else: ?>
                            <a href="travel.php" class="btn-latest-booking">
                                🌱 Plan Your First Trip ➔
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </aside>

            <!-- 75% Right Column: Traveler Passport Vault & Trips List -->
            <section class="trips-content-col">
                
                <div class="vault-summary-strip">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.4rem;">📜</span>
                        <div>
                            <strong style="color: var(--primary); font-size: 0.98rem;">Verified Net-Zero Travel Ledger</strong>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= $totalTrips ?> Low-Carbon Trip(s) recorded in your passport</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="travel.php" class="btn-nature-primary" style="padding: 7px 14px; font-size: 0.85rem;">
                            + New Low-Carbon Trip
                        </a>
                    </div>
                </div>

                <?php if (!empty($bookings)): ?>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach ($bookings as $idx => $b): ?>
                            <div id="<?= $idx === 0 ? 'latest-booking' : 'booking-' . $b['id'] ?>" class="card-3d reveal-on-scroll" style="background: #ffffff; border-radius: var(--radius-lg); padding: 1.6rem; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-card); display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 15px; position: relative;">
                                <?php if ($idx === 0): ?>
                                    <div style="position: absolute; top: -10px; right: 20px; background: #064e3b; color: #ecfdf5; font-size: 0.68rem; font-weight: 800; padding: 3px 10px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.8px;">
                                        ★ Latest Booking
                                    </div>
                                <?php endif; ?>
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
            </section>

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

