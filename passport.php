<?php
// passport.php - Dedicated Official Eco-Passport & Verified Booking Receipt
require_once 'db.php';
require_once 'user_auth.php';

require_user_login('my-trips.php');
$currentUser = get_logged_in_user();

$code = trim($_GET['code'] ?? '');
$bookingId = intval($_GET['id'] ?? 0);
$autoPrint = isset($_GET['print']);
$isNew = isset($_GET['new']) || isset($_GET['success']);

$booking = null;
try {
    if (!empty($code)) {
        $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name, h.image_url, h.water_saved_liters, h.power_saved_kwh, h.eco_rating, h.accessibility_tags, u.name as traveler_name, u.email as traveler_email 
            FROM bookings b 
            JOIN hotels h ON b.hotel_id = h.id 
            JOIN users u ON b.user_id = u.id 
            WHERE b.booking_code = ?");
        $stmt->execute([$code]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif ($bookingId > 0) {
        $stmt = $pdo->prepare("SELECT b.*, h.name as hotel_name, h.image_url, h.water_saved_liters, h.power_saved_kwh, h.eco_rating, h.accessibility_tags, u.name as traveler_name, u.email as traveler_email 
            FROM bookings b 
            JOIN hotels h ON b.hotel_id = h.id 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $booking = null;
}

// Ensure traveler has access (owns booking, possesses unique booking code voucher, or is admin)
if ($booking && $booking['user_id'] != $currentUser['id'] && empty($code) && empty($_SESSION['admin_logged_in'])) {
    $booking = null;
}

// Calculate night duration
$nights = 1;
if ($booking && !empty($booking['check_in']) && !empty($booking['check_out'])) {
    $d1 = new DateTime($booking['check_in']);
    $d2 = new DateTime($booking['check_out']);
    $nights = max(1, $d1->diff($d2)->days);
}

// Transit mode helpers & corridor codes for Eco-Boarding Pass Card
$modeIcon = '🚆';
$modeLabel = 'Clean Rail';
$originCode = 'DEP';
$destCode = 'ARR';

if ($booking) {
    $tMode = strtolower($booking['travel_mode'] ?? '');
    if (strpos($tMode, 'flight') !== false || strpos($tMode, 'air') !== false) {
        $modeIcon = '✈️';
        $modeLabel = 'Clean Aviation';
    } elseif (strpos($tMode, 'bus') !== false) {
        $modeIcon = '🚌';
        $modeLabel = 'Electric Coach';
    } elseif (strpos($tMode, 'car') !== false || strpos($tMode, 'ev') !== false) {
        $modeIcon = '⚡';
        $modeLabel = 'EV Corridor';
    }

    $originClean = preg_replace('/[^a-zA-Z]/', '', $booking['origin'] ?? 'DEP');
    $originCode = strtoupper(substr($originClean ?: 'DEP', 0, 3));
    $destClean = preg_replace('/[^a-zA-Z]/', '', $booking['destination'] ?? 'ARR');
    $destCode = strtoupper(substr($destClean ?: 'ARR', 0, 3));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $booking ? 'Eco-Passport #' . htmlspecialchars($booking['booking_code']) : 'Eco-Passport' ?> — Way2Green</title>
    <meta name="description" content="Official Verified Eco-Passport and Sustainable Hospitality Receipt issued by Way2Green.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        /* Screen UI Elements */
        .passport-page-main {
            max-width: 1360px !important;
            margin: 0 auto 3rem !important;
            padding: 1.5rem 1.5rem 3rem !important;
        }

        .receipt-action-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: var(--radius-md);
            padding: 1rem 1.4rem;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            box-shadow: var(--shadow-sm);
        }

        /* 25% - 75% Master Split Layout */
        .passport-split-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 2.2rem;
            align-items: start;
            width: 100%;
        }

        @media (min-width: 1280px) {
            .passport-split-layout {
                grid-template-columns: 26% 74%;
            }
        }

        @media (max-width: 1024px) {
            .passport-split-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        .passport-card-col {
            position: sticky;
            top: 1.5rem;
            z-index: 10;
        }

        @media (max-width: 1024px) {
            .passport-card-col {
                position: static;
            }
        }

        .passport-cert-col {
            min-width: 0;
            width: 100%;
        }

        /* ==========================================================================
           DESIGN 2: ECO BOARDING PASS TICKET CARD (25% COLUMN)
           ========================================================================== */
        .eco-boarding-ticket {
            background: #fdfbf7;
            background-image: radial-gradient(#e5dfd3 0.75px, transparent 0.75px);
            background-size: 14px 14px;
            border: 1.8px solid #dcd4c3;
            border-radius: 20px;
            box-shadow: 0 16px 36px rgba(15, 61, 36, 0.12), 0 2px 8px rgba(0,0,0,0.04);
            position: relative;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e293b;
            transition: var(--transition);
        }

        .eco-boarding-ticket:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 48px rgba(15, 61, 36, 0.18), 0 4px 12px rgba(0,0,0,0.06);
        }

        /* Header of Boarding Pass */
        .ticket-header {
            background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
            color: #ffffff;
            padding: 1.25rem 1.4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        .ticket-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.3px;
        }

        .ticket-badge-pill {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #ecfdf5;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        /* Body Section */
        .ticket-body {
            padding: 1.3rem 1.4rem 0.5rem;
        }

        /* Route Corridor Banner */
        .ticket-route {
            background: #ffffff;
            border: 1px solid #e2dcd2;
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        }

        .route-point {
            text-align: left;
        }

        .route-point.dest {
            text-align: right;
        }

        .route-code {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 1.35rem;
            color: #064e3b;
            line-height: 1;
        }

        .route-city {
            font-size: 0.72rem;
            font-weight: 600;
            color: #64748b;
            margin-top: 3px;
            max-width: 90px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .route-plane {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: #059669;
            font-size: 1.1rem;
            padding: 0 6px;
        }

        .route-plane-line {
            width: 44px;
            height: 2px;
            background: #a7f3d0;
            position: relative;
        }

        .route-plane-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: -3px;
            width: 7px;
            height: 7px;
            border-top: 2px solid #059669;
            border-right: 2px solid #059669;
            transform: rotate(45deg);
        }

        /* Passenger Details Fields */
        .ticket-passenger-group {
            margin-bottom: 1rem;
        }

        .ticket-label {
            font-size: 0.65rem;
            color: #78716c;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .ticket-value-main {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
        }

        .ticket-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 0.9rem;
        }

        .ticket-info-tile {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid #e5e0d4;
            border-radius: 8px;
            padding: 7px 9px;
        }

        .ticket-value-sub {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1e293b;
        }

        .ticket-value-mono {
            font-family: 'JetBrains Mono', monospace;
            color: #047857;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* Perforation Tear Notch Row */
        .ticket-perforation-wrap {
            position: relative;
            margin: 0.7rem 0;
            padding: 0;
            display: flex;
            align-items: center;
        }

        .ticket-perforation-wrap::before,
        .ticket-perforation-wrap::after {
            content: '';
            position: absolute;
            width: 24px;
            height: 24px;
            background-color: var(--bg-main, #f3f8f5);
            border: 1.8px solid #dcd4c3;
            border-radius: 50%;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
        }

        .ticket-perforation-wrap::before {
            left: -13px;
        }

        .ticket-perforation-wrap::after {
            right: -13px;
        }

        .ticket-dashed-tear {
            width: 100%;
            border-top: 2px dashed #cbd5e1;
            position: relative;
            z-index: 1;
        }

        /* Lower Ticket Stub */
        .ticket-stub {
            padding: 0.5rem 1.4rem 1.4rem;
            background: rgba(255, 255, 255, 0.45);
        }

        .ticket-eco-stamp-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 0.9rem;
        }

        .ticket-eco-stamp {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #ecfdf5;
            border: 1.5px solid #059669;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transform: rotate(-1deg);
        }

        .ticket-carbon-tag {
            background: #dcfce7;
            color: #15803d;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        /* Authentic CSS Barcode */
        .ticket-barcode-box {
            background: #ffffff;
            border: 1px solid #e2dcd2;
            border-radius: 10px;
            padding: 10px 8px 6px;
            text-align: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }

        .barcode-graphic {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 2px;
            height: 44px;
            margin: 0 auto 5px;
            padding: 0 6px;
            overflow: hidden;
        }

        .b-bar {
            background-color: #0f172a;
            height: 100%;
            border-radius: 1px;
            display: inline-block;
        }
        .b-w1 { width: 1.5px; }
        .b-w2 { width: 3px; }
        .b-w3 { width: 4.5px; }
        .b-w4 { width: 6px; }

        .barcode-code-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.76rem;
            font-weight: 700;
            color: #334155;
            letter-spacing: 2px;
        }

        /* Action Buttons within Card */
        .ticket-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-ticket-action {
            flex: 1;
            background: #ffffff;
            border: 1.5px solid #059669;
            color: #065f46;
            padding: 7px 8px;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            transition: var(--transition);
        }

        .btn-ticket-action:hover {
            background: #059669;
            color: #ffffff;
        }

        .btn-ticket-action.primary {
            background: #065f46;
            color: #ffffff;
            border-color: #064e3b;
        }

        .btn-ticket-action.primary:hover {
            background: #047857;
        }

        /* Certificate Master Layout */
        .certificate-container {
            max-width: 100%;
            margin: 0 auto 3rem;
        }

        .eco-passport-cert {
            background: #ffffff;
            border: 2.5px solid #0f5132;
            border-radius: 20px;
            padding: 2.5rem 2.8rem;
            box-shadow: 0 20px 50px rgba(15, 61, 36, 0.12);
            position: relative;
            overflow: hidden;
        }

        /* Security Decorative Corner Guilloche Border */
        .eco-passport-cert::before {
            content: '';
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: 6px;
            border: 1px dashed rgba(15, 81, 50, 0.35);
            border-radius: 14px;
            pointer-events: none;
        }

        .cert-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 1.4rem;
            margin-bottom: 1.6rem;
            gap: 16px;
        }

        .cert-logo-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .cert-logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #10b981 0%, #064e3b 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .cert-title-text {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 1.35rem;
            color: #064e3b;
            letter-spacing: -0.5px;
            line-height: 1.15;
        }

        .cert-subtitle {
            font-size: 0.78rem;
            color: #4b6354;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            margin-top: 3px;
        }

        .cert-seal-badge {
            border: 2px solid #059669;
            background: #ecfdf5;
            color: #065f46;
            padding: 8px 14px;
            border-radius: 10px;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.15);
            flex-shrink: 0;
        }

        /* 2-Column Clean Grid */
        .cert-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 600px) {
            .cert-grid {
                grid-template-columns: 1fr;
            }
            .eco-passport-cert {
                padding: 1.8rem 1.4rem;
            }
        }

        .cert-tile {
            background: #f8faf9;
            border: 1px solid #d1e7dd;
            border-radius: 10px;
            padding: 12px 16px;
        }

        .cert-tile-label {
            font-size: 0.72rem;
            color: #4b6354;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 700;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .cert-tile-value {
            font-size: 1.05rem;
            font-weight: 800;
            color: #0b2e1b;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .cert-tile-value.mono {
            font-family: 'JetBrains Mono', monospace;
            color: #047857;
            font-size: 1rem;
        }

        /* Impact Green Highlight Strip */
        .cert-impact-strip {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1.5px solid #a7f3d0;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 1.4rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 14px;
            text-align: center;
        }

        .cert-impact-item {
            flex: 1;
            min-width: 140px;
        }

        .cert-impact-num {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            color: #065f46;
        }

        .cert-impact-lbl {
            font-size: 0.74rem;
            color: #047857;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 2px;
        }

        /* Accessibility Box */
        .cert-acc-box {
            background: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 0.82rem;
            color: #854d0e;
            margin-bottom: 1.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Receipt Financial Table */
        .cert-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.4rem;
            font-size: 0.88rem;
        }

        .cert-table th {
            text-align: left;
            padding: 8px 10px;
            background: #f1f5f9;
            color: #334155;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            border-radius: 6px 6px 0 0;
        }

        .cert-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }

        .cert-table .total-row td {
            font-weight: 800;
            font-size: 1.05rem;
            color: #064e3b;
            border-bottom: 2px solid #064e3b;
            background: #f0fdf4;
        }

        /* Official Footer Stamp */
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 1rem;
            border-top: 1px dashed #cbd5e1;
            font-size: 0.75rem;
            color: #64748b;
        }

        .cert-signature {
            text-align: right;
        }

        .cert-signature-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            color: #064e3b;
            font-size: 0.85rem;
        }

        /* ==========================================================================
           PRINT STYLES — STRICT 1-PAGE A4 PORTRAIT FORMAT
           ========================================================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }

            html, body {
                background: #ffffff !important;
                color: #0b2e1b !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 9.5pt !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* Completely hide all screen navigation, ambient orbs, and screen buttons */
            header, footer, nav, .header-top, .site-footer, .mobile-bottom-bar,
            .mobile-drawer, .drawer-overlay, .ambient-scene, .receipt-action-bar,
            .no-print, button, .section-head, .btn-hamburger, hr {
                display: none !important;
            }

            .page-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .passport-split-layout {
                display: block !important;
            }

            .passport-card-col {
                display: none !important;
            }

            .passport-cert-col {
                width: 100% !important;
            }

            .certificate-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .eco-passport-cert {
                border: 2px solid #064e3b !important;
                border-radius: 12px !important;
                box-shadow: none !important;
                padding: 16px 20px !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            .cert-header {
                padding-bottom: 10px !important;
                margin-bottom: 12px !important;
            }

            .cert-logo-icon {
                width: 36px !important;
                height: 36px !important;
                font-size: 1.2rem !important;
            }

            .cert-title-text {
                font-size: 1.15rem !important;
                color: #064e3b !important;
            }

            .cert-subtitle {
                font-size: 7pt !important;
                color: #2e4436 !important;
            }

            .cert-seal-badge {
                padding: 5px 10px !important;
                font-size: 7pt !important;
                border: 1.5px solid #059669 !important;
                background: #ecfdf5 !important;
                color: #065f46 !important;
            }

            .cert-grid {
                gap: 8px !important;
                margin-bottom: 12px !important;
            }

            .cert-tile {
                padding: 8px 12px !important;
                background: #f8faf9 !important;
                border: 1px solid #c8e6c9 !important;
            }

            .cert-tile-label {
                font-size: 6.5pt !important;
                color: #2e4436 !important;
            }

            .cert-tile-value {
                font-size: 9.5pt !important;
                color: #064e3b !important;
            }

            .cert-tile-value.mono {
                font-size: 9pt !important;
                color: #047857 !important;
            }

            .cert-impact-strip {
                padding: 8px 12px !important;
                margin-bottom: 10px !important;
                border: 1px solid #a7f3d0 !important;
                background: #f0fdf4 !important;
            }

            .cert-impact-num {
                font-size: 10pt !important;
                color: #065f46 !important;
            }

            .cert-impact-lbl {
                font-size: 6.5pt !important;
                color: #047857 !important;
            }

            .cert-acc-box {
                padding: 6px 12px !important;
                font-size: 7.5pt !important;
                margin-bottom: 10px !important;
                background: #fefce8 !important;
            }

            .cert-table {
                margin-bottom: 10px !important;
                font-size: 8pt !important;
            }

            .cert-table th, .cert-table td {
                padding: 6px 8px !important;
            }

            .cert-footer {
                padding-top: 8px !important;
                font-size: 7pt !important;
                color: #475569 !important;
            }

            /* Prevent any accidental page break */
            * {
                page-break-after: avoid !important;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Background Scene -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Mobile Drawer Overlay & Menu -->
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
        <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($currentUser['name']) ?>)</a>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- Clean Header -->
    <?php include 'components/navbar.php'; ?>

    <main class="page-container passport-page-main">

        <?php if ($booking): ?>

            <!-- Success Alert Banner for Just-Booked Trips -->
            <?php if ($isNew): ?>
                <div class="no-print" style="background: #ecfdf5; border: 1.5px solid #10b981; border-radius: 12px; padding: 14px 20px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.6rem;">🎉</span>
                        <div>
                            <strong style="color: #065f46; font-size: 1.05rem;">Reservation Confirmed & Passport Issued!</strong>
                            <div style="color: #047857; font-size: 0.85rem;">Your stay has been officially recorded in the verified low-carbon ledger.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-nature-primary" style="padding: 8px 16px; font-size: 0.88rem;" onclick="window.print()">
                        🖨️ Print / Save PDF
                    </button>
                </div>
            <?php endif; ?>

            <!-- Screen Action Bar -->
            <div class="receipt-action-bar no-print">
                <a href="my-trips.php" class="btn-nature-outline" style="padding: 8px 16px; font-size: 0.88rem;">
                    ← Back to My Trips
                </a>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <button type="button" class="btn-nature-outline" style="padding: 8px 14px; font-size: 0.88rem;" onclick="copyBookingCode('<?= htmlspecialchars($booking['booking_code']) ?>')">
                        📋 Copy Code (<span style="font-family: monospace; font-weight: 700;"><?= htmlspecialchars($booking['booking_code']) ?></span>)
                    </button>
                    <button type="button" class="btn-nature-primary" style="padding: 9px 20px; font-size: 0.92rem;" onclick="window.print()">
                        🖨️ Print / Save Official Certificate
                    </button>
                </div>
            </div>

            <!-- 25% - 75% Split Layout Container -->
            <div class="passport-split-layout">

                <!-- 25% Left Column: Eco Boarding Pass Ticket Card -->
                <aside class="passport-card-col no-print">
                    <div class="eco-boarding-ticket">
                        <!-- Top Header -->
                        <div class="ticket-header">
                            <div class="ticket-brand">
                                <span>🌱</span>
                                <span>Way2Green</span>
                            </div>
                            <div class="ticket-badge-pill">
                                Boarding Pass
                            </div>
                        </div>

                        <!-- Ticket Main Body -->
                        <div class="ticket-body">
                            <!-- Route Corridor -->
                            <div class="ticket-route">
                                <div class="route-point">
                                    <div class="route-code"><?= $originCode ?></div>
                                    <div class="route-city" title="<?= htmlspecialchars($booking['origin']) ?>"><?= htmlspecialchars($booking['origin']) ?></div>
                                </div>
                                <div class="route-plane">
                                    <span><?= $modeIcon ?></span>
                                    <div class="route-plane-line"></div>
                                </div>
                                <div class="route-point dest">
                                    <div class="route-code"><?= $destCode ?></div>
                                    <div class="route-city" title="<?= htmlspecialchars($booking['destination']) ?>"><?= htmlspecialchars($booking['destination']) ?></div>
                                </div>
                            </div>

                            <!-- Passenger Details -->
                            <div class="ticket-passenger-group">
                                <div class="ticket-label">Passenger / Eco Traveler</div>
                                <div class="ticket-value-main"><?= htmlspecialchars($booking['traveler_name'] ?? $currentUser['name']) ?></div>
                            </div>

                            <div class="ticket-grid-2">
                                <div class="ticket-info-tile">
                                    <div class="ticket-label">Corridor Pass</div>
                                    <div class="ticket-value-mono"><?= htmlspecialchars(substr($booking['booking_code'], 0, 8)) ?></div>
                                </div>
                                <div class="ticket-info-tile">
                                    <div class="ticket-label">Transit Mode</div>
                                    <div class="ticket-value-sub" style="color: #047857;"><?= htmlspecialchars($modeLabel) ?></div>
                                </div>
                                <div class="ticket-info-tile">
                                    <div class="ticket-label">Check-In Date</div>
                                    <div class="ticket-value-sub"><?= date('d M Y', strtotime($booking['check_in'])) ?></div>
                                </div>
                                <div class="ticket-info-tile">
                                    <div class="ticket-label">Stay Duration</div>
                                    <div class="ticket-value-sub"><?= $nights ?> Night(s)</div>
                                </div>
                            </div>

                            <div class="ticket-info-tile" style="margin-bottom: 0.5rem;">
                                <div class="ticket-label">Eco-Sanctuary Hotel</div>
                                <div class="ticket-value-sub" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($booking['hotel_name']) ?>">
                                    <?= htmlspecialchars($booking['hotel_name']) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Perforation Line with Realistic Semicircle Edge Notches -->
                        <div class="ticket-perforation-wrap">
                            <div class="ticket-dashed-tear"></div>
                        </div>

                        <!-- Lower Ticket Stub with Barcode & Verification -->
                        <div class="ticket-stub">
                            <div class="ticket-eco-stamp-row">
                                <div class="ticket-eco-stamp">
                                    <span>🌿</span> Verified Net-Zero
                                </div>
                                <div class="ticket-carbon-tag">
                                    -<?= floatval($booking['co2_saved_kg']) ?> kg CO₂
                                </div>
                            </div>

                            <div class="ticket-barcode-box">
                                <div class="barcode-graphic" aria-hidden="true">
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w3"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w4"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w3"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w3"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w4"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w3"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w4"></span>
                                    <span class="b-bar b-w1"></span>
                                    <span class="b-bar b-w2"></span>
                                    <span class="b-bar b-w3"></span>
                                </div>
                                <div class="barcode-code-text"><?= htmlspecialchars($booking['booking_code']) ?></div>
                            </div>

                            <div class="ticket-actions">
                                <button type="button" class="btn-ticket-action" onclick="copyBookingCode('<?= htmlspecialchars($booking['booking_code']) ?>')">
                                    📋 Copy Pass
                                </button>
                                <button type="button" class="btn-ticket-action primary" onclick="window.print()">
                                    🖨️ Print Certificate
                                </button>
                            </div>
                        </div>

                    </div>
                </aside>

                <!-- 75% Right Column: The Official Verified Certificate & Receipt -->
                <section class="passport-cert-col">
                    <div class="certificate-container">
                        <div class="eco-passport-cert">
                            
                            <!-- Header Section -->
                            <div class="cert-header">
                                <div class="cert-logo-group">
                                    <div class="cert-logo-icon">🌱</div>
                                    <div>
                                        <div class="cert-title-text">Official Way2Green Eco-Passport</div>
                                        <div class="cert-subtitle">Verified Sustainable Hospitality Record • Global Tourism Code #W2G-<?= date('Y') ?></div>
                                    </div>
                                </div>
                                <div class="cert-seal-badge">
                                    ✓ Verified<br>Net-Zero
                                </div>
                            </div>

                            <!-- 2-Column Passport Details Grid -->
                            <div class="cert-grid">
                                <div class="cert-tile">
                                    <div class="cert-tile-label">Eco Traveler</div>
                                    <div class="cert-tile-value"><?= htmlspecialchars($booking['traveler_name'] ?? $currentUser['name']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;"><?= htmlspecialchars($booking['traveler_email'] ?? $currentUser['email']) ?></div>
                                </div>

                                <div class="cert-tile">
                                    <div class="cert-tile-label">Booking Reference</div>
                                    <div class="cert-tile-value mono"><?= htmlspecialchars($booking['booking_code']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Issued: <?= date('M d, Y • H:i', strtotime($booking['created_at'])) ?> UTC</div>
                                </div>

                                <div class="cert-tile">
                                    <div class="cert-tile-label">Certified Eco-Sanctuary</div>
                                    <div class="cert-tile-value"><?= htmlspecialchars($booking['hotel_name']) ?></div>
                                    <div style="font-size: 0.75rem; color: #047857; margin-top: 2px;">★ <?= number_format($booking['eco_rating'], 1) ?> Eco-Accredited</div>
                                </div>

                                <div class="cert-tile">
                                    <div class="cert-tile-label">Destination Corridor</div>
                                    <div class="cert-tile-value"><?= htmlspecialchars($booking['destination']) ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Origin: <?= htmlspecialchars($booking['origin']) ?></div>
                                </div>

                                <div class="cert-tile">
                                    <div class="cert-tile-label">Stay Duration & Dates</div>
                                    <div class="cert-tile-value">
                                        <?= date('M d, Y', strtotime($booking['check_in'])) ?> – <?= date('M d, Y', strtotime($booking['check_out'])) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;"><?= $nights ?> Night(s) • <?= intval($booking['guests']) ?> Guest(s)</div>
                                </div>

                                <div class="cert-tile">
                                    <div class="cert-tile-label">Low-Carbon Transit Mode</div>
                                    <div class="cert-tile-value" style="text-transform: capitalize;">
                                        <?= strtoupper($booking['travel_mode']) ?> (<?= floatval($booking['distance_km']) ?> km)
                                    </div>
                                    <div style="font-size: 0.75rem; color: #047857; margin-top: 2px;">Clean Journey Corridor</div>
                                </div>
                            </div>

                            <!-- Environmental Impact Metric Strip -->
                            <div class="cert-impact-strip">
                                <div class="cert-impact-item">
                                    <div class="cert-impact-num">🌱 <?= floatval($booking['co2_saved_kg']) ?> kg</div>
                                    <div class="cert-impact-lbl">Carbon Avoided</div>
                                </div>
                                <div class="cert-impact-item">
                                    <div class="cert-impact-num">💧 <?= number_format($booking['water_saved_liters'] ?: 120000) ?>L</div>
                                    <div class="cert-impact-lbl">Water Preserved</div>
                                </div>
                                <div class="cert-impact-item">
                                    <div class="cert-impact-num">⚡ <?= number_format($booking['power_saved_kwh'] ?: 28000) ?> kWh</div>
                                    <div class="cert-impact-lbl">Clean Solar Energy</div>
                                </div>
                            </div>

                            <!-- Accessibility Accommodations (Zero Extra Charge) -->
                            <?php if (!empty($booking['accessibility_notes'])): ?>
                                <div class="cert-acc-box">
                                    <span style="font-size: 1.1rem;">♿</span>
                                    <div>
                                        <strong>Universal Inclusivity Guaranteed:</strong> <?= htmlspecialchars($booking['accessibility_notes']) ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Itemized Financial Receipt -->
                            <table class="cert-table">
                                <thead>
                                    <tr>
                                        <th>Hospitality & Transit Description</th>
                                        <th style="text-align: center;">Units</th>
                                        <th style="text-align: right;">Amount (INR)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['hotel_name']) ?></strong><br>
                                            <span style="font-size: 0.78rem; color: #64748b;">Eco-Cottage / Suite reservation at verified destination</span>
                                        </td>
                                        <td style="text-align: center;"><?= $nights ?> Night(s)</td>
                                        <td style="text-align: right; font-weight: 600;">₹<?= number_format($booking['total_price']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <strong>Sustainable Travel Infrastructure Fee</strong><br>
                                            <span style="font-size: 0.78rem; color: #059669;">100% Waived by Way2Green Low-Carbon Initiative</span>
                                        </td>
                                        <td style="text-align: center;">1</td>
                                        <td style="text-align: right; color: #059669; font-weight: 700;">₹0 (Free)</td>
                                    </tr>
                                    <tr class="total-row">
                                        <td colspan="2">
                                            Total Paid (Inclusive of All Sustainable Taxes)
                                        </td>
                                        <td style="text-align: right;">₹<?= number_format($booking['total_price']) ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Digital Verification & Signature Footer -->
                            <div class="cert-footer">
                                <div>
                                    <div><strong>Way2Green Certified Digital Ledger</strong></div>
                                    <div>Hash: <span style="font-family: monospace;"><?= substr(hash('sha256', $booking['booking_code'] . $booking['created_at']), 0, 24) ?>...</span></div>
                                    <div>Registry: <em>way2green.synergize.co/verify</em></div>
                                </div>
                                <div class="cert-signature">
                                    <div style="font-size: 1.4rem; color: #059669; margin-bottom: -4px;">🌿</div>
                                    <div class="cert-signature-name">Way2Green Climate Board</div>
                                    <div style="font-size: 0.7rem; color: #64748b;">Certified Global Eco-Hospitality</div>
                                </div>
                            </div>

                        </div>
                    </div>
                </section>

            </div>

        <?php else: ?>

            <div style="background: #ffffff; border-radius: var(--radius-lg); padding: 3rem 1.5rem; text-align: center; border: 1px dashed var(--border-subtle); margin-top: 2rem;">
                <div style="font-size: 3rem; margin-bottom: 10px;">🔍</div>
                <h2 style="color: var(--primary); font-size: 1.5rem; margin-bottom: 8px;">Passport Not Found</h2>
                <p style="color: var(--text-muted); max-width: 480px; margin: 0 auto 1.5rem; font-size: 0.95rem;">
                    We could not locate this eco-passport record. It may belong to another user account or the reference code is invalid.
                </p>
                <a href="my-trips.php" class="btn-nature-primary">
                    View My Saved Passports ➔
                </a>
            </div>

        <?php endif; ?>

    </main>

    <!-- Footer -->
    <footer class="site-footer no-print">
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
                    <a href="my-trips.php">My Passports</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Account</h4>
                <div class="footer-links">
                    <a href="my-trips.php">Signed in as <?= htmlspecialchars($currentUser['name']) ?></a>
                    <a href="logout.php">Sign Out</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Hackathon 2026.
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Bar -->
    <div class="mobile-bottom-bar no-print">
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

        function copyBookingCode(code) {
            navigator.clipboard.writeText(code).then(function() {
                alert('Booking reference ' + code + ' copied to clipboard!');
            }).catch(function() {
                prompt('Booking reference:', code);
            });
        }

        <?php if ($autoPrint): ?>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
        <?php endif; ?>
    </script>
</body>
</html>


