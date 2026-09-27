<?php
// index.php - Way2Green: Smart Sustainable & Accessible Hospitality Platform
require_once 'db.php';
require_once 'user_auth.php';

$user = get_logged_in_user();

// Fetch curated destinations from database
$featuredDests = [];
try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT * FROM destinations ORDER BY id ASC");
        $featuredDests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $featuredDests = [];
}

// Fetch verified eco-hotels with destination info from database
$featuredHotels = [];
try {
    if (isset($pdo)) {
        $stmtHotels = $pdo->query("
            SELECT h.*, d.name AS destination_name 
            FROM hotels h 
            JOIN destinations d ON h.destination_id = d.id 
            ORDER BY h.eco_rating DESC, h.id ASC 
            LIMIT 6
        ");
        $featuredHotels = $stmtHotels->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $featuredHotels = [];
}

// Curated high-res scenic photos for fallback & destination headers
$destImages = [
    'Agra' => 'https://images.unsplash.com/photo-1564507592333-c60657eea523?auto=format&fit=crop&w=1920&q=80',
    'Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1920&q=80',
    'Mumbai' => 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=1920&q=80',
    'Singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1920&q=80',
    'Munnar' => 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1920&q=80',
    'Manali' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1920&q=80',
    'South Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1920&q=80',
    'Rishikesh' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1920&q=80',
    'Ooty' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1920&q=80'
];

// Rich fallback hotels in case the database is not yet seeded
$fallbackHotels = [
    [
        'id' => 1,
        'name' => 'The Solar Lotus Sanctuary',
        'destination_name' => 'Agra, Uttar Pradesh',
        'eco_rating' => 4.9,
        'price_per_night' => 4200,
        'water_saved_liters' => 145000,
        'power_saved_kwh' => 28000,
        'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'description' => '100% solar microgrid, electric monument shuttles, zero single-use plastics, and step-free wheelchair gardens.',
        'eco_badges' => '100% Solar Powered, Zero Plastic, Greywater Recycling',
        'accessibility_tags' => 'Wheelchair Ramps, Roll-in Showers, Braille Markers'
    ],
    [
        'id' => 2,
        'name' => 'Palolem Dune Bio-Cabins',
        'destination_name' => 'South Goa (Eco-Coast)',
        'eco_rating' => 4.88,
        'price_per_night' => 3800,
        'water_saved_liters' => 110000,
        'power_saved_kwh' => 22000,
        'image_url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',
        'description' => 'Reclaimed bamboo cottages supporting Olive Ridley turtle conservation, with beach wheelchair mats and solar showers.',
        'eco_badges' => 'Solar Powered, Marine Reserve Partner, Zero Waste',
        'accessibility_tags' => 'Beach Wheelchair, Step-free Boardwalk, Sensory Friendly'
    ],
    [
        'id' => 3,
        'name' => 'Misty Mountain Eco-Resort',
        'destination_name' => 'Munnar, Kerala',
        'eco_rating' => 4.95,
        'price_per_night' => 4500,
        'water_saved_liters' => 165000,
        'power_saved_kwh' => 32000,
        'image_url' => 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80',
        'description' => 'Organic tea plantation retreat running on micro-hydro & solar energy. Features wheelchair accessible forest skywalk.',
        'eco_badges' => '100% Renewable, Rainwater Catchment, Organic Farm',
        'accessibility_tags' => 'Skywalk Ramps, Visual Alerts, Wide Doorways'
    ],
    [
        'id' => 4,
        'name' => 'The Urban Mangrove Retreat',
        'destination_name' => 'Mumbai, Maharashtra',
        'eco_rating' => 4.82,
        'price_per_night' => 5200,
        'water_saved_liters' => 125000,
        'power_saved_kwh' => 26000,
        'image_url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
        'description' => 'LEED Platinum certified property with vertical botanical gardens, universal accessibility elevators, and metro connections.',
        'eco_badges' => 'LEED Platinum, 100% Greywater, EV Charging Hub',
        'accessibility_tags' => 'Braille Signs, Auditory Cues, Level Access'
    ],
    [
        'id' => 5,
        'name' => 'Solang Himalayan Bio-Lodge',
        'destination_name' => 'Manali, Himachal Pradesh',
        'eco_rating' => 4.86,
        'price_per_night' => 4100,
        'water_saved_liters' => 98000,
        'power_saved_kwh' => 21000,
        'image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'description' => 'Passive solar-heated alpine timber lodge with triple-glazed windows and zero-food-waste community composting.',
        'eco_badges' => 'Passive Solar, Composting Hub, Native Timber',
        'accessibility_tags' => 'Wheelchair Ramps, Ground Floor Suites, Grab Bars'
    ],
    [
        'id' => 6,
        'name' => 'Marina Biophilic Sanctuary',
        'destination_name' => 'Singapore Garden City',
        'eco_rating' => 4.98,
        'price_per_night' => 12500,
        'water_saved_liters' => 280000,
        'power_saved_kwh' => 74000,
        'image_url' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=800&q=80',
        'description' => 'World-class biophilic towers with universal access, smart rainwater cooling, zero plastics, and verified net-zero operations.',
        'eco_badges' => 'Net-Zero Carbon, Rainwater Cooling, Zero Plastic',
        'accessibility_tags' => 'Full Universal Access, Smart Elevators, Tactile Guides'
    ]
];

$displayHotels = !empty($featuredHotels) ? $featuredHotels : $fallbackHotels;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Way2Green — Travel Light. Leave Only Footprints.</title>
    <meta name="description" content="A conscious travel platform combining low-carbon transit planning, verified barrier-free eco-lodges, dynamic vertical boarding passes, and official green passports.">
    
    <!-- Google Fonts: Modern Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           COLOR PALETTE & DESIGN TOKENS
           Strict Color Palette: Seaweed Green & Alice Blue Dominance
           ========================================================================== */
        :root {
            /* Seaweed Green Family */
            --seaweed-deepest: #04160d;
            --seaweed-dark: #072416;
            --seaweed-primary: #0b3b24;
            --seaweed-mid: #134e35;
            --seaweed-light: #1b633d;
            --seaweed-accent: #22c55e;
            --seaweed-mint: #34d399;
            --seaweed-glow: rgba(34, 197, 94, 0.4);

            /* Alice Blue Family */
            --alice-blue: #F0F8FF;
            --alice-blue-light: #f7fbff;
            --alice-blue-dark: #e1effc;
            --alice-blue-deep: #cae3fb;
            --alice-blue-card: rgba(240, 248, 255, 0.94);
            --alice-blue-glass: rgba(240, 248, 255, 0.88);
            --alice-blue-border: rgba(190, 220, 248, 0.65);

            /* Text & UI Semantics */
            --text-on-seaweed: #F0F8FF;
            --text-on-alice: #072416;
            --text-muted: #4d6d5d;
            --text-light-subtle: rgba(240, 248, 255, 0.82);

            /* Elevation & Shadows */
            --shadow-sm: 0 4px 14px rgba(7, 36, 22, 0.08);
            --shadow-md: 0 12px 32px rgba(7, 36, 22, 0.14);
            --shadow-lg: 0 24px 50px rgba(7, 36, 22, 0.22);
            --shadow-boarding-pass: 0 30px 60px -12px rgba(4, 22, 13, 0.45), 0 0 40px rgba(34, 197, 94, 0.15);
            --shadow-search-btn: 0 14px 28px rgba(7, 36, 22, 0.35), 0 0 25px rgba(34, 211, 153, 0.3);

            /* Layout */
            --radius-sm: 10px;
            --radius-md: 18px;
            --radius-lg: 26px;
            --radius-full: 9999px;
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Global Reset */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--alice-blue);
            color: var(--text-on-alice);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
            padding-bottom: 74px; /* Mobile bottom dock clearance */
        }

        @media (min-width: 900px) {
            body {
                padding-bottom: 0;
            }
        }

        a {
            color: var(--seaweed-primary);
            text-decoration: none;
            transition: var(--transition);
        }

        button, input, select {
            font-family: inherit;
        }

        /* Ambient Glowing Background Elements */
        .ambient-glow-1 {
            position: fixed;
            top: -120px;
            right: -100px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(34, 197, 94, 0.12) 0%, rgba(240, 248, 255, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: fixed;
            bottom: -150px;
            left: -100px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(11, 59, 36, 0.1) 0%, rgba(240, 248, 255, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ==========================================================================
           TOP NAVIGATION BAR (CONNECTED ACROSS WEBSITE)
           ========================================================================== */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(240, 248, 255, 0.90);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--alice-blue-border);
            padding: 0.85rem 2rem;
            transition: var(--transition);
        }

        .nav-inner {
            max-width: 1320px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.45rem;
            font-weight: 800;
            color: var(--seaweed-primary);
            letter-spacing: -0.02em;
        }

        .brand-icon-box {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--seaweed-primary), var(--seaweed-light));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--alice-blue);
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(11, 59, 36, 0.25);
        }

        .desktop-nav {
            display: flex;
            align-items: center;
            gap: 1.6rem;
            list-style: none;
        }

        .nav-link {
            font-size: 0.94rem;
            font-weight: 700;
            color: var(--seaweed-mid);
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        .nav-link:hover, .nav-link.active {
            color: var(--seaweed-primary);
            background: var(--alice-blue-dark);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .eco-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(11, 59, 36, 0.08);
            border: 1px solid rgba(11, 59, 36, 0.18);
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--seaweed-primary);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: var(--seaweed-accent);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 10px var(--seaweed-accent);
            animation: pulse-dot 2s infinite ease-in-out;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.45; transform: scale(0.85); }
        }

        .btn-nav-primary {
            background: linear-gradient(135deg, var(--seaweed-primary), var(--seaweed-light));
            color: var(--alice-blue);
            padding: 8px 18px;
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(11, 59, 36, 0.25);
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-nav-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(11, 59, 36, 0.35);
            color: #ffffff;
        }

        .btn-hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }

        .btn-hamburger span {
            display: block;
            width: 24px;
            height: 2.5px;
            background-color: var(--seaweed-primary);
            border-radius: 2px;
            transition: var(--transition);
        }

        /* Mobile Off-Canvas Drawer */
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(4, 22, 13, 0.6);
            backdrop-filter: blur(4px);
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .drawer-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 300px;
            max-width: 85%;
            background: var(--alice-blue);
            border-left: 2px solid var(--alice-blue-deep);
            padding: 2rem 1.5rem;
            z-index: 201;
            transform: translateX(100%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: -10px 0 30px rgba(0,0,0,0.15);
        }

        .mobile-drawer.open {
            transform: translateX(0);
        }

        .drawer-close {
            align-self: flex-end;
            background: none;
            border: none;
            font-size: 1.4rem;
            color: var(--seaweed-primary);
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .drawer-link {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--seaweed-mid);
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .drawer-link:hover, .drawer-link.active {
            color: var(--seaweed-primary);
            background: var(--alice-blue-dark);
        }

        /* ==========================================================================
           MAIN HERO SECTION WITH DYNAMIC LOCATION BACKGROUND
           Strict UI Constraint: Semi-transparent Seaweed Green / Alice Blue Gradient Overlay
           ========================================================================== */
        .hero-section {
            position: relative;
            min-height: 94vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 1.5rem 6.5rem;
            overflow: hidden;
            background-color: var(--seaweed-dark);
            transition: background 0.6s ease-in-out;
        }

        /* Dynamic Background Layer with Smooth Image Crossfading */
        .hero-bg-layer {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            opacity: 1;
            transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
            transform: scale(1.02);
            z-index: 1;
        }

        /* Gradient Overlay: Semi-transparent Seaweed Green + Alice Blue */
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(7, 36, 22, 0.92) 0%,
                rgba(11, 59, 36, 0.85) 45%,
                rgba(19, 78, 53, 0.76) 75%,
                rgba(240, 248, 255, 0.32) 100%
            );
            backdrop-filter: blur(1.5px);
            -webkit-backdrop-filter: blur(1.5px);
            z-index: 2;
        }

        .hero-ambient-mesh {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 80% 25%, rgba(52, 211, 153, 0.18) 0%, transparent 50%),
                        radial-gradient(circle at 20% 85%, rgba(240, 248, 255, 0.15) 0%, transparent 45%);
            mix-blend-mode: overlay;
            z-index: 3;
            pointer-events: none;
        }

        .hero-container {
            position: relative;
            z-index: 10;
            max-width: 1320px;
            width: 100%;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 3.5rem;
            align-items: center;
        }

        /* Hero Left Content */
        .hero-left {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .hero-badge-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .badge-eco-pill {
            background: rgba(240, 248, 255, 0.15);
            border: 1px solid rgba(240, 248, 255, 0.3);
            color: var(--alice-blue);
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            backdrop-filter: blur(8px);
        }

        .badge-destination-indicator {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid var(--seaweed-accent);
            color: #d1fae5;
            padding: 6px 14px;
            border-radius: var(--radius-full);
            font-size: 0.84rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition);
        }

        .hero-title {
            font-size: clamp(2.3rem, 4.2vw, 3.5rem);
            font-weight: 800;
            line-height: 1.12;
            color: var(--alice-blue);
            letter-spacing: -0.03em;
            text-shadow: 0 3px 18px rgba(4, 22, 13, 0.4);
        }

        .hero-title .highlight {
            background: linear-gradient(120deg, #6ee7b7, #a7f3d0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .hero-subtitle {
            font-size: 1.08rem;
            color: var(--text-light-subtle);
            line-height: 1.65;
            max-width: 580px;
        }

        /* ==========================================================================
           THE COMPLEX SEARCH WIDGET (SUBMITS TO hotels.php)
           ========================================================================== */
        .search-widget-wrapper {
            position: relative;
            margin-top: 1rem;
            padding-bottom: 0;
        }

        .search-widget-card {
            background: var(--alice-blue-card);
            border: 2px solid rgba(240, 248, 255, 0.9);
            border-radius: var(--radius-lg);
            padding: 1.8rem;
            box-shadow: 0 20px 45px -8px rgba(4, 22, 13, 0.35), 0 0 20px rgba(34, 197, 94, 0.12);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            position: relative;
            z-index: 5;
        }

        .search-fields-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr 1.2fr;
            gap: 1rem;
            align-items: start;
        }

        .search-field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
        }

        .field-label {
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--seaweed-primary);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .field-input-box {
            position: relative;
            background: #ffffff;
            border: 1.5px solid var(--alice-blue-deep);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            padding: 10px 12px;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 2px 6px rgba(7, 36, 22, 0.04);
        }

        .field-input-box:focus-within {
            border-color: var(--seaweed-light);
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
            background: #ffffff;
        }

        .field-icon {
            font-size: 1.15rem;
            color: var(--seaweed-mid);
            flex-shrink: 0;
        }

        .field-input-box input {
            width: 100%;
            border: none;
            background: transparent;
            outline: none;
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--seaweed-dark);
        }

        .field-input-box input::placeholder {
            color: #8fa79b;
            font-weight: 500;
        }

        /* Quick Destination Chips */
        .quick-destinations-row {
            margin-top: 0.9rem;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .quick-dest-label {
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--seaweed-mid);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-right: 4px;
        }

        .dest-chip {
            background: #ffffff;
            border: 1px solid var(--alice-blue-deep);
            color: var(--seaweed-primary);
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .dest-chip:hover, .dest-chip.active {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
            border-color: var(--seaweed-primary);
            transform: translateY(-1px);
        }

        /* Guest Selector Dropdown */
        .guest-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1.5px solid var(--alice-blue-deep);
            border-radius: var(--radius-md);
            padding: 1rem;
            box-shadow: var(--shadow-md);
            z-index: 50;
            display: none;
            flex-direction: column;
            gap: 12px;
        }

        .guest-dropdown-menu.show {
            display: flex;
            animation: fadeInMenu 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeInMenu {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .guest-counter-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .counter-info h4 {
            font-size: 0.88rem;
            color: var(--seaweed-dark);
            font-weight: 700;
        }

        .counter-info p {
            font-size: 0.74rem;
            color: var(--text-muted);
        }

        .counter-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-counter {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 1px solid var(--alice-blue-deep);
            background: var(--alice-blue);
            color: var(--seaweed-primary);
            font-size: 1rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn-counter:hover:not(:disabled) {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
        }

        .btn-counter:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .counter-val {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--seaweed-dark);
            min-width: 18px;
            text-align: center;
        }

        /* Amenities & Accessibility Toggles */
        .search-amenities-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 1.1rem;
            padding-top: 0.9rem;
            border-top: 1px dashed rgba(11, 59, 36, 0.16);
            flex-wrap: wrap;
            gap: 12px;
        }

        .eco-filters-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .checkbox-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--seaweed-mid);
            user-select: none;
        }

        .checkbox-pill input[type="checkbox"] {
            accent-color: var(--seaweed-primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        /* ==========================================================================
           BUTTON AREA: VERTICAL STACK (NO OVERLAP, SPACIOUS BREATHING ROOM)
           ========================================================================== */
        .search-actions-group {
            display: flex;
            flex-direction: column;
            gap: 20px;
            align-items: center;
            justify-content: center;
            margin-top: 1.6rem;
            width: 100%;
            position: relative;
            z-index: 15;
        }

        .search-secondary-action {
            display: flex;
            justify-content: center;
            width: 100%;
            margin: 0;
            text-align: center;
        }

        .link-transit-calc {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--alice-blue);
            font-size: 0.92rem;
            font-weight: 700;
            background: rgba(11, 59, 36, 0.55);
            border: 1.5px solid rgba(240, 248, 255, 0.35);
            padding: 9px 22px;
            border-radius: var(--radius-full);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: var(--transition);
            text-align: center;
            max-width: 100%;
            box-shadow: 0 4px 14px rgba(7, 36, 22, 0.2);
        }

        .link-transit-calc:hover {
            background: var(--seaweed-primary);
            border-color: var(--seaweed-accent);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(11, 59, 36, 0.35);
        }

        .search-btn-container {
            position: static;
            transform: none;
            width: 100%;
            display: flex;
            justify-content: center;
            margin: 0;
        }

        .btn-search-large {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: linear-gradient(135deg, var(--seaweed-primary) 0%, var(--seaweed-dark) 100%);
            color: var(--alice-blue);
            font-size: 1.12rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 1.1rem 3.4rem;
            border-radius: var(--radius-full);
            border: 3px solid var(--alice-blue);
            box-shadow: var(--shadow-search-btn);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .btn-search-large:hover {
            transform: scale(1.03) translateY(-2px);
            background: linear-gradient(135deg, var(--seaweed-light) 0%, var(--seaweed-primary) 100%);
            box-shadow: 0 20px 40px rgba(7, 36, 22, 0.45), 0 0 30px rgba(34, 197, 94, 0.5);
            color: #ffffff;
        }

        .btn-search-large:active {
            transform: scale(0.98) translateY(0);
        }

        .search-btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(240, 248, 255, 0.18);
            color: var(--alice-blue);
            transition: var(--transition);
        }

        .btn-search-large:hover .search-btn-icon {
            transform: translateX(4px);
            background: var(--seaweed-mint);
            color: var(--seaweed-dark);
        }

        /* ==========================================================================
           ECO-FRIENDLY VERTICAL BOARDING PASS DESIGN
           ========================================================================== */
        .hero-right {
            display: flex;
            justify-content: center;
            align-items: center;
            perspective: 1000px;
        }

        .boarding-pass-card {
            width: 100%;
            max-width: 410px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: var(--shadow-boarding-pass);
            position: relative;
            overflow: visible;
            border: 2px solid rgba(240, 248, 255, 0.85);
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        .boarding-pass-card:hover {
            transform: translateY(-6px) rotate(0.5deg);
        }

        .pass-header {
            background: linear-gradient(135deg, var(--seaweed-dark) 0%, var(--seaweed-primary) 100%);
            color: var(--alice-blue);
            padding: 1.4rem 1.6rem 1.2rem;
            border-radius: 22px 22px 0 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .pass-airline-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--alice-blue);
        }

        .pass-type-badge {
            background: rgba(34, 197, 94, 0.22);
            border: 1px solid var(--seaweed-accent);
            color: #bbf7d0;
            font-size: 0.72rem;
            font-weight: 800;
            padding: 3px 9px;
            border-radius: var(--radius-full);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pass-main-body {
            padding: 1.5rem 1.8rem;
            background: #ffffff;
        }

        .pass-route-block {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 1.2rem;
            border-bottom: 1px solid rgba(11, 59, 36, 0.1);
        }

        .route-stop {
            display: flex;
            flex-direction: column;
        }

        .stop-code {
            font-family: var(--font-mono);
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--seaweed-primary);
            line-height: 1;
        }

        .stop-city {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .route-connector {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex-grow: 1;
            padding: 0 1rem;
        }

        .route-transport-mode {
            font-size: 0.72rem;
            font-weight: 800;
            color: var(--seaweed-mid);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .connector-line {
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--seaweed-accent), var(--seaweed-primary));
            position: relative;
        }

        .connector-line::after {
            content: '🚆';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 0.95rem;
            background: #ffffff;
            padding: 0 4px;
        }

        .pass-details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem 1.2rem;
            margin-top: 1.2rem;
        }

        .pass-meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            color: #7b998a;
            letter-spacing: 0.05em;
        }

        .meta-value {
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--seaweed-dark);
            margin-top: 2px;
        }

        .meta-value.mono {
            font-family: var(--font-mono);
            letter-spacing: -0.02em;
        }

        .pass-carbon-strip {
            margin-top: 1.3rem;
            background: rgba(240, 248, 255, 0.85);
            border: 1px solid var(--alice-blue-deep);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .carbon-strip-title {
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--seaweed-primary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .carbon-savings-badge {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
            font-size: 0.74rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            letter-spacing: 0.02em;
        }

        /* Perforated Notches & Tear Line */
        .pass-perforation-divider {
            position: relative;
            height: 28px;
            background: #ffffff;
            display: flex;
            align-items: center;
        }

        .tear-dashed-line {
            width: 100%;
            border-top: 2px dashed rgba(11, 59, 36, 0.24);
        }

        .notch-left, .notch-right {
            position: absolute;
            top: 50%;
            width: 26px;
            height: 26px;
            background: var(--seaweed-dark);
            border-radius: 50%;
            transform: translateY(-50%);
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .notch-left {
            left: -13px;
        }

        .notch-right {
            right: -13px;
        }

        /* Pass Stub (Bottom Segment) */
        .pass-stub {
            background: var(--alice-blue);
            padding: 1.4rem 1.8rem 1.6rem;
            border-radius: 0 0 22px 22px;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
        }

        .stub-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stub-seal-badge {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .seal-stamp {
            width: 36px;
            height: 36px;
            border: 2px dashed var(--seaweed-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            color: var(--seaweed-primary);
        }

        .seal-text {
            display: flex;
            flex-direction: column;
        }

        .seal-title {
            font-size: 0.74rem;
            font-weight: 800;
            color: var(--seaweed-primary);
            text-transform: uppercase;
        }

        .seal-subtitle {
            font-size: 0.68rem;
            color: var(--text-muted);
            font-family: var(--font-mono);
        }

        .pass-barcode-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--alice-blue-deep);
        }

        .barcode-strip {
            display: flex;
            align-items: center;
            gap: 3px;
            height: 38px;
            flex-grow: 1;
        }

        .barcode-line {
            background-color: var(--seaweed-dark);
            height: 100%;
            border-radius: 1px;
        }

        .qr-placeholder {
            width: 44px;
            height: 44px;
            background: var(--alice-blue-dark);
            border: 1.5px solid var(--seaweed-primary);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.4rem;
        }

        .pass-serial {
            font-family: var(--font-mono);
            font-size: 0.68rem;
            color: var(--text-muted);
            text-align: center;
            letter-spacing: 0.1em;
        }

        .btn-stub-passport {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
            text-align: center;
            font-size: 0.82rem;
            font-weight: 700;
            padding: 8px 14px;
            border-radius: var(--radius-full);
            display: block;
            transition: var(--transition);
        }

        .btn-stub-passport:hover {
            background: var(--seaweed-light);
            color: #ffffff;
        }

        /* ==========================================================================
           SECTION: 3-STEP JOURNEY ROADMAP (CONNECTED FLOW)
           ========================================================================== */
        .roadmap-section {
            max-width: 1320px;
            margin: -2.5rem auto 3rem;
            padding: 0 1.5rem;
            position: relative;
            z-index: 20;
        }

        .roadmap-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 1.6rem;
        }

        .roadmap-card {
            background: #ffffff;
            border: 1.5px solid var(--alice-blue-deep);
            border-radius: var(--radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
        }

        .roadmap-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-md);
            border-color: var(--seaweed-accent);
        }

        .roadmap-badge {
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--seaweed-light);
            background: var(--alice-blue);
            padding: 4px 10px;
            border-radius: var(--radius-full);
            display: inline-block;
            margin-bottom: 12px;
        }

        .roadmap-icon {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .roadmap-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--seaweed-dark);
            margin-bottom: 8px;
        }

        .roadmap-desc {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 1.4rem;
        }

        .roadmap-btn {
            background: var(--alice-blue);
            color: var(--seaweed-primary);
            border: 1px solid var(--alice-blue-deep);
            padding: 10px 16px;
            border-radius: var(--radius-full);
            font-size: 0.88rem;
            font-weight: 700;
            text-align: center;
            display: inline-block;
            transition: var(--transition);
        }

        .roadmap-btn:hover {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
            border-color: var(--seaweed-primary);
        }

        /* ==========================================================================
           SECTION 2: POPULAR GREEN CORRIDORS & FEATURED ECO-STAYS
           ========================================================================== */
        .section-container {
            max-width: 1320px;
            margin: 0 auto;
            padding: 3.5rem 1.5rem 5rem;
        }

        .section-header-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 2.8rem;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .section-tag {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--seaweed-primary);
            background: var(--alice-blue-dark);
            padding: 4px 12px;
            border-radius: var(--radius-full);
            margin-bottom: 8px;
        }

        .section-heading {
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 800;
            color: var(--seaweed-dark);
            letter-spacing: -0.02em;
        }

        .section-subtext {
            color: var(--text-muted);
            font-size: 1.02rem;
            max-width: 620px;
            margin-top: 6px;
        }

        .stays-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }

        .stay-card {
            background: #ffffff;
            border: 1.5px solid var(--alice-blue-deep);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .stay-card:hover {
            transform: translateY(-7px);
            box-shadow: var(--shadow-md);
            border-color: var(--seaweed-accent);
        }

        .stay-thumb-box {
            position: relative;
            height: 220px;
            overflow: hidden;
        }

        .stay-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .stay-card:hover .stay-thumb-box img {
            transform: scale(1.06);
        }

        .stay-rating-tag {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(7, 36, 22, 0.88);
            color: var(--alice-blue);
            backdrop-filter: blur(8px);
            padding: 4px 10px;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stay-eco-badge {
            position: absolute;
            bottom: 14px;
            left: 14px;
            background: rgba(240, 248, 255, 0.95);
            color: var(--seaweed-primary);
            padding: 4px 12px;
            border-radius: var(--radius-full);
            font-size: 0.76rem;
            font-weight: 800;
        }

        .stay-card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .stay-loc {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--seaweed-light);
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 6px;
        }

        .stay-title {
            font-size: 1.22rem;
            font-weight: 800;
            color: var(--seaweed-dark);
            margin-bottom: 8px;
        }

        .stay-description {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 1.2rem;
            flex-grow: 1;
        }

        .stay-metrics-row {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--seaweed-mid);
            background: var(--alice-blue);
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
        }

        .stay-tags-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 1.3rem;
        }

        .eco-tag {
            font-size: 0.74rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: var(--radius-full);
            background: var(--alice-blue);
            color: var(--seaweed-primary);
            border: 1px solid var(--alice-blue-deep);
        }

        .stay-card-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.1rem;
            border-top: 1px solid var(--alice-blue-dark);
            gap: 8px;
        }

        .stay-price-box {
            display: flex;
            flex-direction: column;
        }

        .price-label {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
        }

        .price-val {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--seaweed-primary);
        }

        .stay-action-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-view-stay {
            background: var(--seaweed-primary);
            color: var(--alice-blue);
            padding: 8px 16px;
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-block;
        }

        .btn-view-stay:hover {
            background: var(--seaweed-light);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .btn-plan-transit {
            background: var(--alice-blue);
            color: var(--seaweed-primary);
            border: 1px solid var(--alice-blue-deep);
            padding: 7px 12px;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-block;
            transition: var(--transition);
        }

        .btn-plan-transit:hover {
            background: var(--alice-blue-dark);
            border-color: var(--seaweed-primary);
        }

        /* ==========================================================================
           SECTION 3: SUSTAINABILITY & ACCESSIBILITY IMPACT METRICS
           ========================================================================== */
        .metrics-banner {
            background: linear-gradient(135deg, var(--seaweed-primary) 0%, var(--seaweed-dark) 100%);
            color: var(--alice-blue);
            border-radius: var(--radius-lg);
            padding: 3.5rem 2.5rem;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .metrics-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 15% 30%, rgba(34, 197, 94, 0.22) 0%, transparent 60%);
            pointer-events: none;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 2.5rem;
            position: relative;
            z-index: 2;
        }

        .metric-col {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .metric-number {
            font-family: var(--font-mono);
            font-size: clamp(2.2rem, 3.5vw, 3rem);
            font-weight: 800;
            color: #6ee7b7;
            line-height: 1;
        }

        .metric-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--alice-blue);
        }

        .metric-desc {
            font-size: 0.88rem;
            color: var(--text-light-subtle);
            line-height: 1.5;
        }

        /* CTA Section */
        .cta-box {
            background: linear-gradient(135deg, var(--seaweed-mid) 0%, var(--seaweed-primary) 100%);
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 3.5rem 2rem;
            text-align: center;
            box-shadow: var(--shadow-md);
            margin-top: 3.5rem;
        }

        .cta-title {
            font-size: clamp(1.8rem, 3.5vw, 2.4rem);
            font-weight: 800;
            margin-bottom: 1rem;
            color: #ffffff;
        }

        .cta-desc {
            max-width: 600px;
            margin: 0 auto 2.2rem;
            font-size: 1.05rem;
            color: rgba(240, 248, 255, 0.9);
            line-height: 1.6;
        }

        .cta-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-cta-white {
            background: #ffffff;
            color: var(--seaweed-dark);
            font-weight: 800;
            padding: 12px 28px;
            border-radius: var(--radius-full);
            transition: var(--transition);
        }

        .btn-cta-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.25);
            background: var(--alice-blue);
        }

        .btn-cta-outline {
            border: 2px solid rgba(240, 248, 255, 0.8);
            color: #ffffff;
            font-weight: 700;
            padding: 10px 24px;
            border-radius: var(--radius-full);
            transition: var(--transition);
        }

        .btn-cta-outline:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffffff;
        }

        /* Toast Notifications */
        .toast-notification {
            position: fixed;
            bottom: 84px;
            right: 24px;
            background: var(--seaweed-dark);
            color: var(--alice-blue);
            padding: 14px 22px;
            border-radius: var(--radius-md);
            border: 1px solid var(--seaweed-accent);
            box-shadow: 0 16px 36px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 0.94rem;
            z-index: 1000;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: none;
        }

        .toast-notification.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        /* Site Footer */
        .site-footer {
            background: var(--seaweed-deepest);
            color: var(--alice-blue);
            padding: 4.5rem 1.5rem 2.5rem;
            border-top: 1px solid rgba(240, 248, 255, 0.1);
        }

        .footer-inner {
            max-width: 1320px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        .footer-brand h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--alice-blue);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
        }

        .footer-brand p {
            color: var(--text-light-subtle);
            font-size: 0.92rem;
            line-height: 1.6;
            max-width: 380px;
        }

        .footer-col h4 {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--alice-blue);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-links a {
            color: var(--text-light-subtle);
            font-size: 0.88rem;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--seaweed-mint);
            transform: translateX(3px);
        }

        .footer-bottom {
            max-width: 1320px;
            margin: 0 auto;
            padding-top: 2rem;
            border-top: 1px solid rgba(240, 248, 255, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.84rem;
            color: rgba(240, 248, 255, 0.55);
            flex-wrap: wrap;
            gap: 1rem;
        }

        /* Mobile Bottom Fixed Dock */
        .mobile-bottom-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: rgba(240, 248, 255, 0.95);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid var(--alice-blue-deep);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 90;
            box-shadow: 0 -4px 16px rgba(4, 22, 13, 0.08);
        }

        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            color: var(--seaweed-mid);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .mobile-nav-item .icon {
            font-size: 1.25rem;
        }

        .mobile-nav-item.active, .mobile-nav-item:hover {
            color: var(--seaweed-primary);
        }

        @media (min-width: 900px) {
            .mobile-bottom-bar {
                display: none;
            }
            .toast-notification {
                bottom: 28px;
            }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1100px) {
            .hero-container {
                grid-template-columns: 1fr;
                gap: 4rem;
            }

            .hero-right {
                order: 2;
            }

            .boarding-pass-card {
                max-width: 460px;
            }

            .search-fields-grid {
                grid-template-columns: 1fr 1fr;
            }

            .footer-inner {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 899px) {
            .desktop-nav {
                display: none;
            }
            .btn-hamburger {
                display: flex;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 3rem 1rem 5.5rem;
            }

            .search-fields-grid {
                grid-template-columns: 1fr;
            }

            .search-actions-group {
                gap: 16px;
                margin-top: 1.3rem;
            }

            .link-transit-calc {
                font-size: 0.82rem;
                padding: 8px 14px;
                width: 100%;
            }

            .btn-search-large {
                width: 100%;
                padding: 1rem 1.4rem;
                font-size: 0.98rem;
                white-space: normal;
            }

            .footer-inner {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing Backdrop -->
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <!-- Mobile Drawer Overlay -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>

    <!-- Off-Canvas Mobile Drawer -->
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--seaweed-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link active">🏡 Home</a>
        <a href="travel.php" class="drawer-link">🚆 Plan Transit</a>
        <a href="hotels.php" class="drawer-link">🏨 Eco-Stays</a>
        <a href="about.php" class="drawer-link">🌿 Our Mission</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">📜 My Passports</a>
            <a href="logout.php" class="drawer-link" style="color: #dc2626;">🚪 Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">👤 Traveler Sign In</a>
            <a href="register.php" class="drawer-link" style="color: var(--seaweed-light);">✨ Create Account</a>
        <?php endif; ?>
        <hr style="border: none; border-top: 1px solid var(--alice-blue-deep); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">🔒 Admin Portal</a>
    </div>

    <!-- Navigation Header -->
    <header class="site-header">
        <div class="nav-inner">
            <a href="index.php" class="brand-logo">
                <div class="brand-icon-box">🌱</div>
                <span>Way_2_Green</span>
            </a>

            <!-- Desktop Nav Menu Connecting Whole Website -->
            <nav class="desktop-nav">
                <a href="index.php" class="nav-link active">Home</a>
                <a href="travel.php" class="nav-link">Plan Transit</a>
                <a href="hotels.php" class="nav-link">Eco-Stays</a>
                <a href="about.php" class="nav-link">Our Mission</a>
                <?php if ($user): ?>
                    <a href="my-trips.php" class="nav-link">My Passports</a>
                    <a href="logout.php" class="nav-link" style="color: #dc2626;">Sign Out</a>
                <?php else: ?>
                    <a href="login.php" class="nav-link">Sign In</a>
                    <a href="register.php" class="btn-nav-primary">Get Started ➔</a>
                <?php endif; ?>
            </nav>

            <div class="header-actions">
                <div class="eco-status-pill">
                    <span class="status-dot"></span>
                    <span><?= $user ? 'Logged in: ' . htmlspecialchars($user['name']) : '100% Carbon Verified' ?></span>
                </div>
                <!-- Mobile Hamburger Button -->
                <button class="btn-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Hero Section with Dynamic Location Backgrounds -->
    <section class="hero-section" id="heroSection">
        <!-- Dynamic Background Layer (Manipulated by Inline JS) -->
        <div class="hero-bg-layer" id="heroBgLayer"></div>

        <!-- Strict UI Constraint: Semi-transparent Seaweed Green / Alice Blue Gradient Overlay -->
        <div class="hero-overlay"></div>
        <div class="hero-ambient-mesh"></div>

        <div class="hero-container">
            <!-- Left Hero Content & The Complex Search Widget -->
            <div class="hero-left">
                <div class="hero-badge-row">
                    <span class="badge-eco-pill">🌍 Sustainable & Barrier-Free Hospitality</span>
                    <span class="badge-destination-indicator" id="heroDestIndicator">
                        📍 Viewing: <strong id="heroDestIndicatorName" style="margin-left: 4px;">Agra • Taj Mahal Corridor</strong>
                    </span>
                </div>

                <h1 class="hero-title">
                    Travel Light. <br>
                    <span class="highlight">Your Ticket</span> to a Greener Planet.
                </h1>

                <p class="hero-subtitle">
                    Select your low-emission corridor, pick verified wheelchair-accessible solar sanctuaries, and generate your instant verified eco-boarding pass.
                </p>

                <!-- THE COMPLEX SEARCH WIDGET (Direct Form Submission to hotels.php) -->
                <div class="search-widget-wrapper">
                    <form method="GET" action="hotels.php" id="heroSearchForm">
                        <div class="search-widget-card">
                            <div class="search-fields-grid">
                                
                                <!-- Field 1: Destination / Location (Monitored by JS) -->
                                <div class="search-field-group">
                                    <label class="field-label" for="locationInput">
                                        <span>📍</span> Destination
                                    </label>
                                    <div class="field-input-box">
                                        <span class="field-icon">🗺️</span>
                                        <input 
                                            type="text" 
                                            name="dest" 
                                            id="locationInput" 
                                            placeholder="Agra, Goa, Mumbai, Singapore, Munnar..." 
                                            value="Agra"
                                            autocomplete="off"
                                            required
                                        >
                                    </div>
                                </div>

                                <!-- Field 2: Check-in Date -->
                                <div class="search-field-group">
                                    <label class="field-label" for="checkInInput">
                                        <span>📅</span> Check-In
                                    </label>
                                    <div class="field-input-box">
                                        <input type="date" name="check_in" id="checkInInput">
                                    </div>
                                </div>

                                <!-- Field 3: Check-out Date -->
                                <div class="search-field-group">
                                    <label class="field-label" for="checkOutInput">
                                        <span>📅</span> Check-Out
                                    </label>
                                    <div class="field-input-box">
                                        <input type="date" name="check_out" id="checkOutInput">
                                    </div>
                                </div>

                                <!-- Field 4: Guests & Rooms Selector -->
                                <div class="search-field-group">
                                    <label class="field-label" for="guestTriggerBtn">
                                        <span>👥</span> Guests & Rooms
                                    </label>
                                    <div class="field-input-box" id="guestTriggerBtn" style="cursor: pointer;">
                                        <span class="field-icon">🧳</span>
                                        <input 
                                            type="text" 
                                            id="guestSummaryInput" 
                                            value="2 Adults, 1 Room" 
                                            readonly 
                                            style="cursor: pointer;"
                                        >
                                        <input type="hidden" name="guests" id="guestsHiddenInput" value="2">
                                    </div>

                                    <!-- Interactive Guest Counter Popover -->
                                    <div class="guest-dropdown-menu" id="guestDropdownMenu">
                                        <div class="guest-counter-row">
                                            <div class="counter-info">
                                                <h4>Adults</h4>
                                                <p>Ages 13 and above</p>
                                            </div>
                                            <div class="counter-controls">
                                                <button class="btn-counter" type="button" onclick="adjustGuest('adults', -1)">−</button>
                                                <span class="counter-val" id="valAdults">2</span>
                                                <button class="btn-counter" type="button" onclick="adjustGuest('adults', 1)">+</button>
                                            </div>
                                        </div>
                                        <div class="guest-counter-row">
                                            <div class="counter-info">
                                                <h4>Children</h4>
                                                <p>Ages 0 to 12</p>
                                            </div>
                                            <div class="counter-controls">
                                                <button class="btn-counter" type="button" onclick="adjustGuest('children', -1)" id="btnMinusChildren" disabled>−</button>
                                                <span class="counter-val" id="valChildren">0</span>
                                                <button class="btn-counter" type="button" onclick="adjustGuest('children', 1)">+</button>
                                            </div>
                                        </div>
                                        <div class="guest-counter-row">
                                            <div class="counter-info">
                                                <h4>Rooms</h4>
                                                <p>Eco-certified units</p>
                                            </div>
                                            <div class="counter-controls">
                                                <button class="btn-counter" type="button" onclick="adjustGuest('rooms', -1)" id="btnMinusRooms" disabled>−</button>
                                                <span class="counter-val" id="valRooms">1</span>
                                                <button class="btn-counter" type="button" onclick="adjustGuest('rooms', 1)">+</button>
                                            </div>
                                        </div>
                                        <button class="btn-nav-primary" type="button" style="width: 100%; margin-top: 6px; justify-content: center;" onclick="closeGuestDropdown()">
                                            Done
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Popular Destination Filter Chips -->
                            <div class="quick-destinations-row">
                                <span class="quick-dest-label">Popular Corridors:</span>
                                <button class="dest-chip active" type="button" onclick="selectDestination('Agra')">🕌 Agra</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Goa')">🏖️ Goa</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Mumbai')">🏛️ Mumbai</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Singapore')">🏙️ Singapore</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Munnar')">🌿 Munnar</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Manali')">🏔️ Manali</button>
                                <button class="dest-chip" type="button" onclick="selectDestination('Kyoto')">🎋 Kyoto</button>
                            </div>

                            <!-- Amenities & Accessibility Toggles -->
                            <div class="search-amenities-row">
                                <div class="eco-filters-group">
                                    <label class="checkbox-pill">
                                        <input type="checkbox" name="accessible" value="1" id="checkWheelchair" checked>
                                        <span>♿ Wheelchair Accessible</span>
                                    </label>
                                    <label class="checkbox-pill">
                                        <input type="checkbox" name="solar" value="1" id="checkSolar" checked>
                                        <span>☀️ 100% Solar Powered</span>
                                    </label>
                                    <label class="checkbox-pill">
                                        <input type="checkbox" name="zero_plastic" value="1" id="checkPlasticFree" checked>
                                        <span>🌱 Zero Single-Use Plastics</span>
                                    </label>
                                </div>
                                <span style="font-size: 0.78rem; font-weight: 700; color: var(--seaweed-mid);">
                                    ⚡ Connected to Official Transit & Stays Engine
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons Group: Vertically Stacked with Breathing Room & No Overlap -->
                        <div class="search-actions-group">
                            <!-- Top Transit Calculator Pill -->
                            <div class="search-secondary-action">
                                <a href="travel.php?dest=Agra" id="transitPlannerHeroLink" class="link-transit-calc">
                                    <span>🚆 Want route emissions first? Open Phase 1 Transit Calculator ➔</span>
                                </a>
                            </div>

                            <!-- Main Search Button Submitting to hotels.php -->
                            <div class="search-btn-container">
                                <button type="submit" class="btn-search-large" id="mainSearchBtn">
                                    <span>Search Eco-Stays</span>
                                    <span class="search-btn-icon">➔</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Hero Content: ECO-FRIENDLY VERTICAL BOARDING PASS -->
            <div class="hero-right">
                <div class="boarding-pass-card" id="boardingPassCard">
                    <!-- Boarding Pass Header -->
                    <div class="pass-header">
                        <div class="pass-airline-brand">
                            <span style="font-size: 1.3rem;">🌱</span>
                            <div>
                                <div style="line-height: 1.1;">WAY2GREEN</div>
                                <span style="font-size: 0.65rem; opacity: 0.85; font-weight: 600; text-transform: uppercase;">Bio-Transit Network</span>
                            </div>
                        </div>
                        <span class="pass-type-badge">First Class Eco</span>
                    </div>

                    <!-- Boarding Pass Main Body -->
                    <div class="pass-main-body">
                        <!-- Origin ➔ Destination Route Block (Dynamically Synchronized) -->
                        <div class="pass-route-block">
                            <div class="route-stop">
                                <span class="stop-code">DEL</span>
                                <span class="stop-city">New Delhi</span>
                            </div>

                            <div class="route-connector">
                                <span class="route-transport-mode" id="passTransitMode">⚡ Solar High-Speed</span>
                                <div class="connector-line"></div>
                                <span style="font-size: 0.7rem; color: var(--seaweed-mid); font-family: var(--font-mono);" id="passDistance">195 km • 0kg CO₂</span>
                            </div>

                            <div class="route-stop" style="text-align: right;">
                                <span class="stop-code" id="passDestCode">AGR</span>
                                <span class="stop-city" id="passDestCity">Agra</span>
                            </div>
                        </div>

                        <!-- Passenger & Transit Metadata Grid -->
                        <div class="pass-details-grid">
                            <div class="pass-meta-item">
                                <span class="meta-label">Passenger Name</span>
                                <span class="meta-value"><?= $user ? htmlspecialchars($user['name']) : 'A. Vance / Voyager' ?></span>
                            </div>
                            <div class="pass-meta-item">
                                <span class="meta-label">Seat / Unit</span>
                                <span class="meta-value mono">04A (Quiet Zone)</span>
                            </div>
                            <div class="pass-meta-item">
                                <span class="meta-label">Departure Date</span>
                                <span class="meta-value mono" id="passDepartDate"><?= date('d M Y') ?></span>
                            </div>
                            <div class="pass-meta-item">
                                <span class="meta-label">Boarding Gate</span>
                                <span class="meta-value mono">G-12 (Solar Wing)</span>
                            </div>
                        </div>

                        <!-- Carbon Savings Live Strip -->
                        <div class="pass-carbon-strip">
                            <div class="carbon-strip-title">
                                <span>🌿 Avoided Footprint:</span>
                            </div>
                            <span class="carbon-savings-badge" id="passSavingsBadge">-84% CO₂ Emissions</span>
                        </div>
                    </div>

                    <!-- Perforation Notches & Tear Dashed Line -->
                    <div class="pass-perforation-divider">
                        <div class="notch-left"></div>
                        <div class="tear-dashed-line"></div>
                        <div class="notch-right"></div>
                    </div>

                    <!-- Boarding Pass Stub (Bottom Segment) -->
                    <div class="pass-stub">
                        <div class="stub-row">
                            <div class="stub-seal-badge">
                                <div class="seal-stamp">✓</div>
                                <div class="seal-text">
                                    <span class="seal-title">Verified Green Passport</span>
                                    <span class="seal-subtitle" id="passLandmarkText">Taj Mahal Sanctuary</span>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 800; color: var(--seaweed-primary);">
                                ACCESSIBLE ♿
                            </span>
                        </div>

                        <!-- Barcode & QR Simulation -->
                        <div class="pass-barcode-container">
                            <div class="barcode-strip" id="barcodeStrip">
                                <div class="barcode-line" style="width: 3px;"></div>
                                <div class="barcode-line" style="width: 1px;"></div>
                                <div class="barcode-line" style="width: 4px;"></div>
                                <div class="barcode-line" style="width: 2px;"></div>
                                <div class="barcode-line" style="width: 1px;"></div>
                                <div class="barcode-line" style="width: 3px;"></div>
                                <div class="barcode-line" style="width: 5px;"></div>
                                <div class="barcode-line" style="width: 2px;"></div>
                                <div class="barcode-line" style="width: 1px;"></div>
                                <div class="barcode-line" style="width: 3px;"></div>
                                <div class="barcode-line" style="width: 2px;"></div>
                                <div class="barcode-line" style="width: 4px;"></div>
                                <div class="barcode-line" style="width: 1px;"></div>
                                <div class="barcode-line" style="width: 3px;"></div>
                                <div class="barcode-line" style="width: 2px;"></div>
                                <div class="barcode-line" style="width: 5px;"></div>
                                <div class="barcode-line" style="width: 1px;"></div>
                                <div class="barcode-line" style="width: 2px;"></div>
                            </div>
                            <div class="qr-placeholder" title="Digital Passport Token">
                                📱
                            </div>
                        </div>

                        <div class="pass-serial" id="passSerialCode">
                            PASS #W2G-9842-AGR-2026 • CARBON NEUTRAL TICKET
                        </div>

                        <!-- Action Button on Pass Stub -->
                        <a href="<?= $user ? 'my-trips.php' : 'travel.php?dest=Agra' ?>" id="stubPassActionBtn" class="btn-stub-passport">
                            <?= $user ? 'View My Saved Passports ➔' : 'Plan Low-Carbon Transit ➔' ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: THE 3-STEP JOURNEY ROADMAP (MULTI-PAGE CONNECTED FLOW) -->
    <section class="roadmap-section">
        <div class="roadmap-grid">
            <!-- Step 1 -->
            <div class="roadmap-card">
                <div>
                    <span class="roadmap-badge">Phase 1 • Low Carbon Transit</span>
                    <div class="roadmap-icon">🚆</div>
                    <h3 class="roadmap-title">Calculate & Cut Emissions</h3>
                    <p class="roadmap-desc">
                        Compare trains, coaches, electric vehicles, and driving. See exact carbon footprints in kilograms and get AI recommendations.
                    </p>
                </div>
                <a href="travel.php" class="roadmap-btn">Open Transit Calculator ➔</a>
            </div>

            <!-- Step 2 -->
            <div class="roadmap-card">
                <div>
                    <span class="roadmap-badge">Phase 2 • Inclusive Hospitality</span>
                    <div class="roadmap-icon">🏨</div>
                    <h3 class="roadmap-title">Stay at Verified Eco-Resorts</h3>
                    <p class="roadmap-desc">
                        Handpicked solar-powered properties with greywater recycling, zero single-use plastics, and physical step-free wheelchair access.
                    </p>
                </div>
                <a href="hotels.php" class="roadmap-btn">Browse Eco-Stays Catalog ➔</a>
            </div>

            <!-- Step 3 -->
            <div class="roadmap-card">
                <div>
                    <span class="roadmap-badge">Phase 3 • Digital Green Passport</span>
                    <div class="roadmap-icon">📜</div>
                    <h3 class="roadmap-title">Earn Your Eco-Passport</h3>
                    <p class="roadmap-desc">
                        Lock in your reservation to receive a verified, downloadable Carbon-Offset Passport showing liters of water saved and clean energy used.
                    </p>
                </div>
                <a href="<?= $user ? 'my-trips.php' : 'login.php' ?>" class="roadmap-btn">
                    <?= $user ? 'View My Trips & Passports ➔' : 'Sign In to View Passports ➔' ?>
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION 2: POPULAR GREEN CORRIDORS & FEATURED ECO-STAYS -->
    <section class="section-container" id="staysSection">
        <div class="section-header-row">
            <div>
                <span class="section-tag">Featured Stays</span>
                <h2 class="section-heading" id="staysHeading">Verified Inclusive Eco-Resorts</h2>
                <p class="section-subtext" id="staysSubtext">
                    Solar-powered, barrier-free certified accommodations across India with real-time transit & reservation links.
                </p>
            </div>
            <div>
                <a href="hotels.php" class="btn-nav-primary">
                    View All 26 Stays in Catalog ➔
                </a>
            </div>
        </div>

        <div class="stays-grid" id="staysGrid">
            <?php foreach ($displayHotels as $h): 
                $badges = array_filter(array_map('trim', explode(',', $h['eco_badges'] ?? 'Solar Powered, Zero Plastic')));
                $access = array_filter(array_map('trim', explode(',', $h['accessibility_tags'] ?? 'Wheelchair Friendly')));
            ?>
            <div class="stay-card">
                <div class="stay-thumb-box">
                    <img src="<?= htmlspecialchars($h['image_url']) ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                    <div class="stay-rating-tag">★ <?= htmlspecialchars($h['eco_rating']) ?></div>
                    <div class="stay-eco-badge">🌱 <?= htmlspecialchars($badges[0] ?? 'Eco Certified') ?></div>
                </div>
                <div class="stay-card-body">
                    <div class="stay-loc">📍 <?= htmlspecialchars($h['destination_name']) ?></div>
                    <h3 class="stay-title"><?= htmlspecialchars($h['name']) ?></h3>
                    <p class="stay-description"><?= htmlspecialchars($h['description']) ?></p>

                    <div class="stay-metrics-row">
                        <span>💧 <?= number_format($h['water_saved_liters'] ?? 120000) ?>L Saved</span>
                        <span>⚡ <?= number_format($h['power_saved_kwh'] ?? 25000) ?> kWh Solar</span>
                    </div>

                    <div class="stay-tags-row">
                        <?php foreach (array_slice($badges, 0, 2) as $b): ?>
                            <span class="eco-tag">🌱 <?= htmlspecialchars($b) ?></span>
                        <?php endforeach; ?>
                        <?php foreach (array_slice($access, 0, 2) as $a): ?>
                            <span class="eco-tag" style="background:#ecfdf5; border-color:#a7f3d0;">♿ <?= htmlspecialchars($a) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="stay-card-bottom">
                        <div class="stay-price-box">
                            <span class="price-label">Eco Rate</span>
                            <span class="price-val">₹<?= number_format($h['price_per_night'] ?? 3500) ?> <span style="font-size:0.8rem; font-weight:500;">/ night</span></span>
                        </div>
                        <div class="stay-action-group">
                            <a href="travel.php?dest=<?= urlencode($h['destination_name']) ?>" class="btn-plan-transit" title="Calculate Route Transit">
                                🚆 Transit
                            </a>
                            <a href="checkout.php?hotel_id=<?= $h['id'] ?>&hotel_name=<?= urlencode($h['name']) ?>&dest_name=<?= urlencode($h['destination_name']) ?>&price=<?= $h['price_per_night'] ?>" class="btn-view-stay">
                                Book Stay ➔
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- SECTION 3: SUSTAINABILITY & ACCESSIBILITY IMPACT METRICS -->
    <section class="section-container" id="impactSection" style="padding-top: 1rem;">
        <div class="metrics-banner">
            <div class="metrics-grid">
                <div class="metric-col">
                    <span class="metric-number">1,420,000L</span>
                    <h3 class="metric-title">Clean Water Conserved</h3>
                    <p class="metric-desc">Rainwater harvesting and wetland greywater recycling at certified partner resorts.</p>
                </div>
                <div class="metric-col">
                    <span class="metric-number">94,500 kWh</span>
                    <h3 class="metric-title">Solar Energy Generated</h3>
                    <p class="metric-desc">Decarbonized stays operating on on-site solar, micro-hydro, and clean battery grids.</p>
                </div>
                <div class="metric-col">
                    <span class="metric-number">100%</span>
                    <h3 class="metric-title">Barrier-Free Standards</h3>
                    <p class="metric-desc">Physical step-free verification, tactile paths, quiet zones, and accessible showers.</p>
                </div>
                <div class="metric-col">
                    <span class="metric-number">0 kg</span>
                    <h3 class="metric-title">Single-Use Plastics</h3>
                    <p class="metric-desc">Glass water refill stations, organic amenities, and zero-waste food composting.</p>
                </div>
            </div>
        </div>

        <!-- Call to Action Banner -->
        <div class="cta-box">
            <h2 class="cta-title">Ready for a Journey That Gives Back?</h2>
            <p class="cta-desc">
                Calculate your transit carbon savings, select a verified barrier-free sanctuary, and receive your certified digital green passport.
            </p>
            <div class="cta-actions">
                <a href="travel.php" class="btn-cta-white">
                    Start Transit Calculator ➔
                </a>
                <a href="hotels.php" class="btn-cta-outline">
                    Explore All Eco-Stays
                </a>
                <a href="about.php" class="btn-cta-outline">
                    Our Mission & Impact
                </a>
            </div>
        </div>
    </section>

    <!-- Site Footer with Full Cross-Page Connectivity -->
    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <h3>🌱 Way_2_Green</h3>
                <p>
                    A conscious travel ecosystem designed to eliminate environmental degradation and create barrier-free travel for everyone.
                </p>
                <div style="margin-top: 14px; font-size: 0.82rem; color: rgba(240, 248, 255, 0.65);">
                    Palette: Strict Seaweed Green (<code>#0b3b24</code>) & Alice Blue (<code>#F0F8FF</code>).
                </div>
            </div>
            <div class="footer-col">
                <h4>Pages & Tools</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home Overview</a></li>
                    <li><a href="travel.php">Phase 1: Transit Emissions Calculator</a></li>
                    <li><a href="hotels.php">Phase 2: Eco-Hotels Catalog</a></li>
                    <li><a href="about.php">About Our Mission & Inclusivity</a></li>
                    <li><a href="my-trips.php">My Eco-Passports & Trips</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Popular Corridors</h4>
                <ul class="footer-links">
                    <li><a href="hotels.php?dest=Agra">Agra Heritage Corridor</a></li>
                    <li><a href="hotels.php?dest=Goa">South Goa Bio-Reserve</a></li>
                    <li><a href="hotels.php?dest=Munnar">Munnar Tea Hills</a></li>
                    <li><a href="hotels.php?dest=Manali">Solang Alpine Sanctuary</a></li>
                    <li><a href="hotels.php?dest=Mumbai">Mumbai Coastal Skyline</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Account & Portal</h4>
                <ul class="footer-links">
                    <?php if ($user): ?>
                        <li><a href="my-trips.php">Profile (<?= htmlspecialchars($user['name']) ?>)</a></li>
                        <li><a href="logout.php">Sign Out</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Traveler Sign In</a></li>
                        <li><a href="register.php">Create Account</a></li>
                    <?php endif; ?>
                    <li><a href="admin/login.php" style="color: var(--seaweed-mint);">Admin Control Panel 🔒</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>© 2026 Way_2_Green Platform • Green & Inclusive Travel Hackathon Challenge.</span>
            <span>Connected Multi-Page Architecture • PHP / MySQL / Vanilla JS</span>
        </div>
    </footer>

    <!-- Mobile Bottom Navigation Dock -->
    <nav class="mobile-bottom-bar">
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
            <span class="label">Mission</span>
        </a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="mobile-nav-item">
                <span class="icon">📜</span>
                <span class="label">Passports</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="mobile-nav-item">
                <span class="icon">👤</span>
                <span class="label">Sign In</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Interactive Toast Notification -->
    <div class="toast-notification" id="toastNotification">
        <span style="font-size: 1.3rem;">🌱</span>
        <span id="toastMsg">Ready to travel light...</span>
    </div>

    <!-- ==========================================================================
       INLINE JAVASCRIPT: Dynamic Location Backgrounds & Synchronized Boarding Pass
       ========================================================================== -->
    <script>
        // 1. JS Dictionary/Mapping of popular destinations to high-quality placeholder image URLs
        const destinationsCatalog = {
            "agra": {
                name: "Agra",
                fullName: "Agra, Uttar Pradesh",
                code: "AGR",
                landmark: "Taj Mahal Eco Sanctuary",
                imageUrl: "https://images.unsplash.com/photo-1564507592333-c60657eea523?auto=format&fit=crop&w=1920&q=80",
                savings: "-84% CO₂ Emissions",
                distance: "195 km • Solar Express"
            },
            "goa": {
                name: "Goa",
                fullName: "South & North Goa",
                code: "GOI",
                landmark: "Palolem & Mandrem Eco-Beaches",
                imageUrl: "https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=1920&q=80",
                savings: "-88% CO₂ Emissions",
                distance: "580 km • Coastal Electric"
            },
            "mumbai": {
                name: "Mumbai",
                fullName: "Mumbai, Maharashtra",
                code: "BOM",
                landmark: "Gateway of India & Marine Drive",
                imageUrl: "https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=1920&q=80",
                savings: "-76% CO₂ Emissions",
                distance: "320 km • High-Speed Electric"
            },
            "singapore": {
                name: "Singapore",
                fullName: "Singapore Garden City",
                code: "SIN",
                landmark: "Marina Bay & Supertrees",
                imageUrl: "https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1920&q=80",
                savings: "-92% CO₂ Emissions",
                distance: "Global Corridor • Carbon Offset"
            },
            "munnar": {
                name: "Munnar",
                fullName: "Munnar, Kerala",
                code: "KER",
                landmark: "Western Ghats Tea Sanctuary",
                imageUrl: "https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1920&q=80",
                savings: "-89% CO₂ Emissions",
                distance: "450 km • Electric Transit"
            },
            "kerala": {
                name: "Kerala",
                fullName: "Kerala Backwaters & Munnar",
                code: "KER",
                landmark: "Western Ghats Tea Sanctuary",
                imageUrl: "https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1920&q=80",
                savings: "-89% CO₂ Emissions",
                distance: "450 km • Electric Transit"
            },
            "manali": {
                name: "Manali",
                fullName: "Manali, Himachal Pradesh",
                code: "KUU",
                landmark: "Solang Alpine Valley",
                imageUrl: "https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=1920&q=80",
                savings: "-81% CO₂ Emissions",
                distance: "540 km • Himalayan Eco-Coach"
            },
            "kyoto": {
                name: "Kyoto",
                fullName: "Kyoto Heritage Sanctuary",
                code: "UKY",
                landmark: "Arashiyama Bamboo Grove",
                imageUrl: "https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?auto=format&fit=crop&w=1920&q=80",
                savings: "-90% CO₂ Emissions",
                distance: "Bullet Train Shinkansen Eco-Link"
            },
            "bali": {
                name: "Bali",
                fullName: "Ubud & Canggu Eco Sanctuary",
                code: "DPS",
                landmark: "Ubud Rice Terraces",
                imageUrl: "https://images.unsplash.com/photo-1537996194471-e657df975ab4?auto=format&fit=crop&w=1920&q=80",
                savings: "-83% CO₂ Emissions",
                distance: "Island Biosphere Reserve"
            }
        };

        // DOM Element Cache
        const heroBgLayer = document.getElementById('heroBgLayer');
        const locationInput = document.getElementById('locationInput');
        const heroDestIndicatorName = document.getElementById('heroDestIndicatorName');
        const passDestCode = document.getElementById('passDestCode');
        const passDestCity = document.getElementById('passDestCity');
        const passSavingsBadge = document.getElementById('passSavingsBadge');
        const passDistance = document.getElementById('passDistance');
        const passLandmarkText = document.getElementById('passLandmarkText');
        const passSerialCode = document.getElementById('passSerialCode');
        const checkInInput = document.getElementById('checkInInput');
        const checkOutInput = document.getElementById('checkOutInput');
        const guestTriggerBtn = document.getElementById('guestTriggerBtn');
        const guestDropdownMenu = document.getElementById('guestDropdownMenu');
        const guestSummaryInput = document.getElementById('guestSummaryInput');
        const guestsHiddenInput = document.getElementById('guestsHiddenInput');
        const transitPlannerHeroLink = document.getElementById('transitPlannerHeroLink');
        const stubPassActionBtn = document.getElementById('stubPassActionBtn');
        const toastNotification = document.getElementById('toastNotification');
        const toastMsg = document.getElementById('toastMsg');

        // State variables
        let currentDestKey = 'agra';
        let guests = { adults: 2, children: 0, rooms: 1 };

        // 2. Function to change background image with Seaweed Green / Alice Blue Gradient Overlay & sync Boarding Pass
        function updateLocationTheme(key) {
            const dest = destinationsCatalog[key];
            if (!dest) return;

            currentDestKey = key;

            // Smooth crossfade dynamic background image
            heroBgLayer.style.opacity = '0';
            setTimeout(() => {
                heroBgLayer.style.backgroundImage = `url('${dest.imageUrl}')`;
                heroBgLayer.style.opacity = '1';
            }, 180);

            // Update Hero Destination Indicator
            if (heroDestIndicatorName) {
                heroDestIndicatorName.textContent = `${dest.name} • ${dest.landmark}`;
            }

            // Sync Vertical Boarding Pass Fields
            if (passDestCode) passDestCode.textContent = dest.code;
            if (passDestCity) passDestCity.textContent = dest.name;
            if (passSavingsBadge) passSavingsBadge.textContent = dest.savings;
            if (passDistance) passDistance.textContent = dest.distance;
            if (passLandmarkText) passLandmarkText.textContent = dest.landmark;
            if (passSerialCode) passSerialCode.textContent = `PASS #W2G-${Math.floor(1000 + Math.random() * 9000)}-${dest.code}-2026 • CARBON NEUTRAL TICKET`;

            // Sync links connecting to travel.php
            if (transitPlannerHeroLink) {
                transitPlannerHeroLink.href = `travel.php?dest=${encodeURIComponent(dest.name)}`;
            }
            if (stubPassActionBtn && !stubPassActionBtn.href.includes('my-trips.php')) {
                stubPassActionBtn.href = `travel.php?dest=${encodeURIComponent(dest.name)}`;
            }

            // Update Active Chip
            document.querySelectorAll('.dest-chip').forEach(chip => {
                if (chip.textContent.toLowerCase().includes(key)) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            });
        }

        // 3. Listen for input changes in the "Location" field of the search widget
        function findMatchingDestination(query) {
            const cleaned = query.trim().toLowerCase();
            if (!cleaned) return null;

            for (const key in destinationsCatalog) {
                if (key.includes(cleaned) || cleaned.includes(key) || destinationsCatalog[key].name.toLowerCase().includes(cleaned)) {
                    return key;
                }
            }
            return null;
        }

        locationInput.addEventListener('input', function (e) {
            const match = findMatchingDestination(e.target.value);
            if (match && match !== currentDestKey) {
                updateLocationTheme(match);
            }
        });

        locationInput.addEventListener('change', function (e) {
            const match = findMatchingDestination(e.target.value);
            if (match) {
                updateLocationTheme(match);
            }
        });

        // Quick chip click handler
        function selectDestination(name) {
            const key = name.toLowerCase();
            locationInput.value = name;
            updateLocationTheme(key);
            showToast(`Selected destination: ${name}`);
        }

        // 4. Guest Stepper Logic
        function toggleGuestDropdown(e) {
            if (e) e.stopPropagation();
            guestDropdownMenu.classList.toggle('show');
        }

        function closeGuestDropdown() {
            guestDropdownMenu.classList.remove('show');
        }

        guestTriggerBtn.addEventListener('click', toggleGuestDropdown);
        document.addEventListener('click', function (e) {
            if (!guestTriggerBtn.contains(e.target) && !guestDropdownMenu.contains(e.target)) {
                closeGuestDropdown();
            }
        });

        function adjustGuest(type, delta) {
            if (type === 'adults') {
                guests.adults = Math.max(1, Math.min(10, guests.adults + delta));
                document.getElementById('valAdults').textContent = guests.adults;
            } else if (type === 'children') {
                guests.children = Math.max(0, Math.min(8, guests.children + delta));
                document.getElementById('valChildren').textContent = guests.children;
                document.getElementById('btnMinusChildren').disabled = guests.children === 0;
            } else if (type === 'rooms') {
                guests.rooms = Math.max(1, Math.min(6, guests.rooms + delta));
                document.getElementById('valRooms').textContent = guests.rooms;
                document.getElementById('btnMinusRooms').disabled = guests.rooms === 1;
            }

            let text = `${guests.adults} Adult${guests.adults > 1 ? 's' : ''}`;
            if (guests.children > 0) {
                text += `, ${guests.children} Child${guests.children > 1 ? 'ren' : ''}`;
            }
            text += `, ${guests.rooms} Room${guests.rooms > 1 ? 's' : ''}`;
            guestSummaryInput.value = text;
            guestsHiddenInput.value = guests.adults + guests.children;
        }

        // Mobile drawer toggle
        function toggleDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            const overlay = document.getElementById('drawerOverlay');
            drawer.classList.toggle('open');
            overlay.classList.toggle('active');
        }

        // Toast Helper
        let toastTimeout = null;
        function showToast(msg) {
            toastMsg.textContent = msg;
            toastNotification.classList.add('active');
            if (toastTimeout) clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                toastNotification.classList.remove('active');
            }, 3200);
        }

        // 5. Initialize on page load
        window.addEventListener('DOMContentLoaded', () => {
            const today = new Date();
            const afterTomorrow = new Date();
            afterTomorrow.setDate(today.getDate() + 2);

            const formatYMD = d => d.toISOString().split('T')[0];
            checkInInput.min = formatYMD(today);
            checkInInput.value = formatYMD(today);

            checkOutInput.min = formatYMD(today);
            checkOutInput.value = formatYMD(afterTomorrow);

            // Set initial Agra theme
            updateLocationTheme('agra');
        });
    </script>
</body>
</html>