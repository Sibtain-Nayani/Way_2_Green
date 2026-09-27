<?php
// index.php - Way2Green: Sustainable Travel Platform — Homepage
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
            LIMIT 4
        ");
        $featuredHotels = $stmtHotels->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $featuredHotels = [];
}

// Curated high-res scenic photos
$destImages = [
    'Agra' => 'https://images.unsplash.com/photo-1564507592333-c60657eea523?auto=format&fit=crop&w=800&q=80',
    'Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
    'Mumbai' => 'https://images.unsplash.com/photo-1570168007204-dfb528c6958f?auto=format&fit=crop&w=800&q=80',
    'Singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=800&q=80',
    'Munnar' => 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=800&q=80',
    'Manali' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?auto=format&fit=crop&w=800&q=80',
    'South Goa' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
    'Rishikesh' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
    'Ooty' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=800&q=80'
];

// Rich fallback hotels
$fallbackHotels = [
    [
        'id' => 1,
        'name' => 'Rainforest Canopy Retreat',
        'destination_name' => 'Wayanad, Kerala',
        'eco_rating' => 4.9,
        'price_per_night' => 4200,
        'water_saved_liters' => 145000,
        'power_saved_kwh' => 28000,
        'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'description' => '100% solar microgrid, electric shuttles, zero single-use plastics, and step-free wheelchair gardens.',
        'eco_badges' => '100% Solar Powered, Zero Plastic, Greywater Recycling',
        'accessibility_tags' => 'Wheelchair Ramps, Roll-in Showers, Braille Markers'
    ],
    [
        'id' => 2,
        'name' => 'Mountain Village Homestay',
        'destination_name' => 'Shimla, Himachal Pradesh',
        'eco_rating' => 4.88,
        'price_per_night' => 3800,
        'water_saved_liters' => 110000,
        'power_saved_kwh' => 22000,
        'image_url' => 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
        'description' => 'Handcrafted local stone construction powered by micro-hydro with community-sourced sustainable living.',
        'eco_badges' => 'Micro-Hydro, Community Farm, Zero Waste',
        'accessibility_tags' => 'Ground Floor, Wide Doorways, Sensory Friendly'
    ],
    [
        'id' => 3,
        'name' => 'Coastal Dune Sanctuary',
        'destination_name' => 'Alleppey, Kerala',
        'eco_rating' => 4.95,
        'price_per_night' => 4500,
        'water_saved_liters' => 165000,
        'power_saved_kwh' => 32000,
        'image_url' => 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',
        'description' => 'Sustainable dune retreat with solar panels, organic farm-to-table dining and zero-plastic policy.',
        'eco_badges' => '100% Renewable, Rainwater Catchment, Organic Farm',
        'accessibility_tags' => 'Beach Access, Visual Alerts, Wide Doorways'
    ],
    [
        'id' => 4,
        'name' => 'Urban Mangrove Retreat',
        'destination_name' => 'Mumbai, Maharashtra',
        'eco_rating' => 4.82,
        'price_per_night' => 5200,
        'water_saved_liters' => 125000,
        'power_saved_kwh' => 26000,
        'image_url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
        'description' => 'LEED Platinum certified with vertical gardens, universal accessibility, and metro connections.',
        'eco_badges' => 'LEED Platinum, 100% Greywater, EV Charging Hub',
        'accessibility_tags' => 'Braille Signs, Auditory Cues, Level Access'
    ]
];

$displayHotels = !empty($featuredHotels) ? $featuredHotels : $fallbackHotels;

// Fetch destinations for trip planner dropdown
$destinations = [];
try {
    if (isset($pdo)) {
        $stmt2 = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
        $destinations = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    $destinations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>W2G — Travel Further. Tread Lighter.</title>
    <meta name="description" content="Way2Green connects conscious travelers with lower-emission routes, verified eco-stays, and meaningful experiences for a greener planet.">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* =============================================
           W2G DESIGN SYSTEM
           Seaweed Green #29AB87 + Alice Blue #F0F8FF
           ============================================= */
        :root {
            --green-900: #0a2e1c;
            --green-800: #0f3d26;
            --green-700: #155c3a;
            --green-600: #1a7a4e;
            --green-500: #29AB87;
            --green-400: #3ec99b;
            --green-300: #6edbb5;
            --green-200: #a8ecd4;
            --green-100: #d4f5e9;
            --green-50: #eefbf5;

            --alice: #F0F8FF;
            --alice-warm: #fafcf9;
            --off-white: #f5f7f4;
            --white: #ffffff;
            --cream: #f8f6f1;

            --text-dark: #0f1f17;
            --text-body: #2d3d33;
            --text-muted: #6b7c72;
            --text-light: #94a39a;

            --border: #e2e8e4;
            --border-light: #eef2ef;

            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 24px;
            --radius-full: 9999px;

            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.1);
            --shadow-xl: 0 16px 48px rgba(0,0,0,0.12);

            --font-sans: 'Plus Jakarta Sans', -apple-system, sans-serif;
            --font-serif: 'Playfair Display', Georgia, serif;
            --transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-sans);
            background: var(--white);
            color: var(--text-body);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; transition: var(--transition); }
        img { max-width: 100%; display: block; }

        /* =============================================
           1. FLOATING NAVBAR
           ============================================= */
        .w2g-nav {
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 32px);
            max-width: 1280px;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: var(--radius-full);
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 1000;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .nav-logo img { height: 32px; width: auto; }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .nav-links a {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-body);
            padding: 8px 16px;
            border-radius: var(--radius-full);
            transition: var(--transition);
        }
        .nav-links a:hover, .nav-links a.active {
            background: var(--green-50);
            color: var(--green-700);
        }
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-signin {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-body);
            padding: 8px 20px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border);
            background: transparent;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-signin:hover { border-color: var(--green-500); color: var(--green-600); }
        .btn-signup {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--white);
            padding: 8px 20px;
            border-radius: var(--radius-full);
            background: var(--green-500);
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-signup:hover { background: var(--green-600); transform: translateY(-1px); }
        .nav-hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }
        .nav-hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: var(--text-dark);
            border-radius: 2px;
            transition: var(--transition);
        }

        /* =============================================
           2. HERO SECTION
           ============================================= */
        .hero {
            position: relative;
            width: 100%;
            min-height: 92vh;
            overflow: hidden;
            display: flex;
            align-items: flex-end;
            padding-bottom: 100px;
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=1920&q=80') center/cover no-repeat;
            z-index: 0;
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(10,46,28,0.3) 0%, rgba(10,46,28,0.55) 70%, rgba(10,46,28,0.8) 100%);
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 40px;
            width: 100%;
        }
        .hero-breadcrumb {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            font-size: 0.82rem;
            font-weight: 600;
            color: rgba(255,255,255,0.65);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .hero-breadcrumb span { opacity: 0.5; }
        .hero-title {
            font-family: var(--font-serif);
            font-size: clamp(3rem, 6vw, 5rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.08;
            margin-bottom: 20px;
            max-width: 680px;
            letter-spacing: -1px;
        }
        .hero-desc {
            font-size: 1.05rem;
            color: rgba(255,255,255,0.8);
            max-width: 540px;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .btn-hero {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green-500);
            color: var(--white);
            padding: 14px 28px;
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        .btn-hero:hover { background: var(--green-400); transform: translateY(-2px); }

        /* Hero side info cards */
        .hero-side-cards {
            position: absolute;
            right: 40px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 2;
        }
        .hero-side-card {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            min-width: 160px;
        }
        .hero-side-card .label {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .hero-side-card .value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
        }

        /* =============================================
           3. TRIP PLANNER (overlapping hero)
           ============================================= */
        .planner-wrapper {
            position: relative;
            z-index: 5;
            max-width: 1200px;
            margin: -60px auto 0;
            padding: 0 24px;
        }
        .planner-card {
            background: var(--white);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            padding: 0;
            overflow: hidden;
        }
        .planner-tabs {
            display: flex;
            border-bottom: 1px solid var(--border-light);
        }
        .planner-tab {
            flex: 1;
            padding: 16px 20px;
            text-align: center;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: var(--transition);
            background: none;
            border-top: none;
            border-left: none;
            border-right: none;
        }
        .planner-tab.active {
            color: var(--green-600);
            border-bottom-color: var(--green-500);
        }
        .planner-tab:hover { color: var(--green-500); }
        .planner-body { padding: 28px 32px; }
        .planner-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 16px;
            align-items: end;
            margin-bottom: 20px;
        }
        .planner-field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .planner-field input,
        .planner-field select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-family: var(--font-sans);
            font-size: 0.92rem;
            color: var(--text-dark);
            background: var(--white);
            transition: var(--transition);
        }
        .planner-field input:focus,
        .planner-field select:focus {
            outline: none;
            border-color: var(--green-500);
            box-shadow: 0 0 0 3px rgba(41,171,135,0.1);
        }
        .planner-preferences {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pref-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-body);
            cursor: pointer;
            transition: var(--transition);
        }
        .pref-chip:hover { border-color: var(--green-400); color: var(--green-600); }
        .pref-chip.active {
            background: var(--green-500);
            color: var(--white);
            border-color: var(--green-500);
        }
        .btn-search-routes {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green-900);
            color: var(--white);
            padding: 12px 28px;
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: 0.9rem;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            transition: var(--transition);
        }
        .btn-search-routes:hover { background: var(--green-800); transform: translateY(-1px); }

        .planner-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 4px;
        }

        /* =============================================
           4. VALUE STRIP
           ============================================= */
        .value-strip {
            max-width: 1200px;
            margin: 48px auto;
            padding: 0 24px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }
        .value-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .value-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            background: var(--green-50);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        .value-text h4 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 2px;
        }
        .value-text p {
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        /* =============================================
           Section shared patterns
           ============================================= */
        .section-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 80px 24px;
        }
        .section-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        .section-title {
            font-family: var(--font-serif);
            font-size: clamp(2rem, 3.5vw, 3rem);
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.15;
            letter-spacing: -0.5px;
        }
        .section-header-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 48px;
        }
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 22px;
            border: 1px solid var(--border);
            border-radius: var(--radius-full);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-body);
            transition: var(--transition);
        }
        .btn-outline:hover { border-color: var(--green-500); color: var(--green-600); }

        /* =============================================
           5. DESTINATIONS SECTION
           ============================================= */
        .dest-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }
        .dest-card {
            border-radius: var(--radius-lg);
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            cursor: pointer;
        }
        .dest-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }
        .dest-card-img {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        .dest-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .dest-card:hover .dest-card-img img { transform: scale(1.05); }
        .dest-tag {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 5px 12px;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            background: var(--green-500);
            color: var(--white);
        }
        .dest-eco-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            padding: 5px 12px;
            border-radius: var(--radius-full);
            font-size: 0.72rem;
            font-weight: 700;
            background: rgba(255,255,255,0.9);
            color: var(--green-700);
        }
        .dest-card-body { padding: 16px 18px; }
        .dest-loc {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .dest-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        .dest-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .dest-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* =============================================
           6. GREENER ROUTE SECTION
           ============================================= */
        .route-section {
            background: var(--off-white);
        }
        .route-content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }
        .route-left h2 {
            font-family: var(--font-serif);
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .route-left p {
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.7;
        }
        .btn-explore {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: var(--green-500);
            color: var(--white);
            border-radius: var(--radius-full);
            font-weight: 700;
            font-size: 0.9rem;
            transition: var(--transition);
        }
        .btn-explore:hover { background: var(--green-600); }
        .route-gallery {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .route-gallery-item {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: 180px;
        }
        .route-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .route-gallery-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 24px 14px 12px;
        }
        .route-gallery-label h4 {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--white);
        }
        .route-gallery-label p {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.7);
        }

        /* =============================================
           7. IMPACT SECTION (full-width)
           ============================================= */
        .impact-section {
            position: relative;
            overflow: hidden;
        }
        .impact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 480px;
        }
        .impact-image {
            position: relative;
        }
        .impact-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .impact-data {
            background: var(--green-900);
            color: var(--white);
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .impact-stat {
            margin-bottom: 32px;
        }
        .impact-stat .number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--green-300);
            letter-spacing: -1px;
        }
        .impact-stat .desc {
            font-size: 0.88rem;
            color: rgba(255,255,255,0.7);
            margin-top: 2px;
        }
        .impact-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-full);
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--green-300);
            margin-top: 8px;
        }

        /* =============================================
           8. STEPS SECTION
           ============================================= */
        .steps-section {
            background: var(--white);
        }
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }
        .step-card {
            text-align: center;
            padding: 32px 20px;
        }
        .step-num {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--text-dark);
        }
        .step-card h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        .step-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* =============================================
           9. MISSION SECTION
           ============================================= */
        .mission-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }
        .mission-image {
            border-radius: var(--radius-xl);
            overflow: hidden;
            height: 400px;
        }
        .mission-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .mission-content h2 {
            font-family: var(--font-serif);
            font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1.15;
            margin-bottom: 16px;
        }
        .mission-content p {
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 24px;
        }

        /* =============================================
           10. CTA SECTION
           ============================================= */
        .cta-section {
            background: var(--green-900);
            padding: 80px 24px;
        }
        .cta-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
        }
        .cta-section h2 {
            font-family: var(--font-serif);
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
        }
        .cta-section h2 span { color: var(--green-300); }
        .cta-email-form {
            display: flex;
            gap: 8px;
        }
        .cta-email-form input {
            padding: 14px 20px;
            border-radius: var(--radius-full);
            border: 1px solid rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
            color: var(--white);
            font-family: var(--font-sans);
            font-size: 0.9rem;
            min-width: 280px;
        }
        .cta-email-form input::placeholder { color: rgba(255,255,255,0.4); }
        .cta-email-form button {
            padding: 14px 28px;
            border-radius: var(--radius-full);
            background: var(--green-500);
            color: var(--white);
            border: none;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            white-space: nowrap;
            transition: var(--transition);
        }
        .cta-email-form button:hover { background: var(--green-400); }

        /* =============================================
           11. FOOTER
           ============================================= */
        .w2g-footer {
            background: var(--green-800);
            color: rgba(255,255,255,0.7);
            padding: 64px 24px 32px;
        }
        .footer-inner {
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px;
        }
        .footer-brand img { height: 28px; margin-bottom: 16px; filter: brightness(0) invert(1); }
        .footer-brand p {
            font-size: 0.85rem;
            line-height: 1.6;
            color: rgba(255,255,255,0.55);
            margin-bottom: 16px;
        }
        .footer-social {
            display: flex;
            gap: 12px;
        }
        .footer-social a {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            transition: var(--transition);
        }
        .footer-social a:hover { background: rgba(255,255,255,0.15); color: var(--white); }
        .footer-col h4 {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--white);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }
        .footer-col a {
            display: block;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.55);
            padding: 4px 0;
            transition: var(--transition);
        }
        .footer-col a:hover { color: var(--white); }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            color: rgba(255,255,255,0.4);
        }
        .footer-bottom-links {
            display: flex;
            gap: 20px;
        }
        .footer-bottom-links a {
            color: rgba(255,255,255,0.4);
            transition: var(--transition);
        }
        .footer-bottom-links a:hover { color: var(--white); }

        /* =============================================
           MOBILE DRAWER
           ============================================= */
        .drawer-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }
        .drawer-overlay.active { opacity: 1; pointer-events: auto; }
        .mobile-drawer {
            position: fixed;
            top: 0; right: 0; bottom: 0;
            width: 300px;
            background: var(--white);
            z-index: 1200;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            padding: 24px;
            overflow-y: auto;
        }
        .mobile-drawer.open { transform: translateX(0); }
        .drawer-close {
            position: absolute;
            top: 16px; right: 16px;
            background: none; border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: var(--text-dark);
        }
        .drawer-link {
            display: block;
            padding: 12px 0;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-body);
            border-bottom: 1px solid var(--border-light);
        }
        .drawer-link:hover { color: var(--green-600); }

        /* =============================================
           MOBILE BOTTOM BAR
           ============================================= */
        .mobile-bottom-bar {
            display: none;
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--white);
            border-top: 1px solid var(--border);
            z-index: 900;
            padding: 8px 0 calc(8px + env(safe-area-inset-bottom));
        }
        .mobile-bottom-bar {
            grid-template-columns: repeat(5, 1fr);
        }
        .mobile-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 4px;
            font-size: 0.68rem;
            color: var(--text-muted);
            text-decoration: none;
        }
        .mobile-nav-item .icon { font-size: 1.2rem; }
        .mobile-nav-item.active { color: var(--green-600); }

        /* =============================================
           RESPONSIVE
           ============================================= */
        @media (max-width: 1024px) {
            .hero-side-cards { display: none; }
            .planner-grid { grid-template-columns: 1fr 1fr; }
            .dest-grid { grid-template-columns: 1fr 1fr; }
            .steps-grid { grid-template-columns: 1fr 1fr; }
            .impact-grid { grid-template-columns: 1fr; }
            .impact-image { height: 300px; }
            .mission-grid { grid-template-columns: 1fr; }
            .mission-image { height: 300px; }
            .route-content-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 32px; }
            .cta-inner { flex-direction: column; text-align: center; }
        }

        @media (max-width: 768px) {
            .w2g-nav { padding: 8px 16px; top: 8px; width: calc(100% - 16px); }
            .nav-links { display: none; }
            .nav-actions .btn-signin,
            .nav-actions .btn-signup { display: none; }
            .nav-hamburger { display: flex; }
            .hero { min-height: 80vh; padding-bottom: 80px; }
            .hero-title { font-size: 2.4rem; }
            .hero-content { padding: 0 20px; }
            .planner-wrapper { padding: 0 12px; }
            .planner-grid { grid-template-columns: 1fr; }
            .planner-body { padding: 20px 16px; }
            .value-strip { grid-template-columns: 1fr 1fr; gap: 20px; }
            .dest-grid { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr; }
            .route-gallery { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; gap: 32px; }
            .footer-bottom { flex-direction: column; gap: 12px; text-align: center; }
            .cta-email-form { flex-direction: column; }
            .cta-email-form input { min-width: auto; width: 100%; }
            .mobile-bottom-bar { display: grid; }
            body { padding-bottom: 72px; }
            .section-wrapper { padding: 48px 16px; }
        }

        /* Subtle entrance animations */
        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

    <!-- Mobile Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.2rem; color: var(--green-700); margin-bottom: 20px;">W2G</div>
        <a href="index.php" class="drawer-link" style="color: var(--green-600);">Home</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="travel.php" class="drawer-link">Transit</a>
        <a href="about.php" class="drawer-link">About Us</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">My Passport</a>
            <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">Sign In</a>
            <a href="register.php" class="drawer-link">Get Started</a>
        <?php endif; ?>
    </div>

    <!-- 1. FLOATING NAVBAR -->
    <nav class="w2g-nav">
        <a href="index.php" class="nav-logo">
            <img src="assets/img/logo.png" alt="W2G" onerror="this.outerHTML='<span style=\'font-weight:800;font-size:1.3rem;color:#155c3a\'>W2G</span>'">
        </a>
        <div class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Transit</a>
            <a href="about.php">About Us</a>
            <?php if ($user): ?>
                <a href="my-trips.php">My Passport</a>
            <?php endif; ?>
        </div>
        <div class="nav-actions">
            <?php if ($user): ?>
                <a href="my-trips.php" class="btn-signin"><?= htmlspecialchars($user['name']) ?></a>
                <a href="logout.php" class="btn-signup" style="background: #dc2626;">Sign Out</a>
            <?php else: ?>
                <a href="login.php" class="btn-signin">Sign In</a>
                <a href="register.php" class="btn-signup">Sign Up</a>
            <?php endif; ?>
            <button class="nav-hamburger" onclick="toggleDrawer()" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- 2. HERO SECTION -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-content">
            <div class="hero-breadcrumb">
                <span>Travel</span> <span>·</span> <span>Explore</span> <span>·</span> <span>Preserve</span>
            </div>
            <h1 class="hero-title">Travel Further.<br>Tread Lighter.</h1>
            <p class="hero-desc">Plan your journeys with lower emissions, verified eco-stays, and meaningful experiences for a greener planet.</p>
            <a href="travel.php" class="btn-hero">Start Planning →</a>
        </div>
        <div class="hero-side-cards">
            <div class="hero-side-card">
                <div class="label">Safety</div>
                <div class="value">Verified<br>Checked</div>
            </div>
            <div class="hero-side-card">
                <div class="label">Journey</div>
                <div class="value">Carbon<br>Neutral</div>
            </div>
            <div class="hero-side-card">
                <div class="label">Rating</div>
                <div class="value">4.9 ★</div>
            </div>
        </div>
    </section>

    <!-- 3. TRIP PLANNER -->
    <div class="planner-wrapper fade-up">
        <div class="planner-card">
            <div class="planner-tabs">
                <button class="planner-tab active" onclick="setTab(this)">✈ Plan a Trip</button>
                <button class="planner-tab" onclick="setTab(this)">🏨 Eco-Stays</button>
                <button class="planner-tab" onclick="setTab(this)">🚆 Transit</button>
            </div>
            <div class="planner-body">
                <form method="GET" action="travel.php">
                    <div class="planner-grid">
                        <div class="planner-field">
                            <label for="plannerFrom">From</label>
                            <input type="text" id="plannerFrom" name="origin" placeholder="Leaving from?" required>
                        </div>
                        <div class="planner-field">
                            <label for="plannerTo">To</label>
                            <select id="plannerTo" name="dest" required>
                                <option value="">Where to?</option>
                                <?php foreach ($destinations as $d): ?>
                                    <option value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($destinations)): ?>
                                    <option value="Agra">Agra</option>
                                    <option value="Goa">Goa</option>
                                    <option value="Munnar">Munnar</option>
                                    <option value="Manali">Manali</option>
                                    <option value="Mumbai">Mumbai</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="planner-field">
                            <label for="plannerDate">Date</label>
                            <input type="date" id="plannerDate" name="departure_date">
                        </div>
                        <div class="planner-field">
                            <label for="plannerTravellers">Travellers</label>
                            <select id="plannerTravellers" name="travellers">
                                <option value="1">1 Traveller</option>
                                <option value="2">2 Travellers</option>
                                <option value="3">3 Travellers</option>
                                <option value="4">4 Travellers</option>
                                <option value="5">5+</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn-search-routes">Search Routes →</button>
                        </div>
                    </div>
                    <div class="planner-footer">
                        <div class="planner-preferences">
                            <span class="pref-chip" onclick="togglePref(this)">💰 Cheapest</span>
                            <span class="pref-chip" onclick="togglePref(this)">⚡ Fastest</span>
                            <span class="pref-chip active" onclick="togglePref(this)">🌿 Most Eco-Friendly</span>
                            <span class="pref-chip" onclick="togglePref(this)">⚙️ Custom</span>
                        </div>
                        <span style="font-size: 0.78rem; color: var(--text-light);">1 Route · 4 Greener Options</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. VALUE STRIP -->
    <div class="value-strip fade-up">
        <div class="value-item">
            <div class="value-icon">🍃</div>
            <div class="value-text">
                <h4>Lower Carbon Footprint</h4>
                <p>Every route optimized for minimal emissions</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-icon">✅</div>
            <div class="value-text">
                <h4>Verified & Safe Eco-Stays</h4>
                <p>Certified sustainable accommodations</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-icon">🤝</div>
            <div class="value-text">
                <h4>Support Local Communities</h4>
                <p>Travel that empowers destinations</p>
            </div>
        </div>
        <div class="value-item">
            <div class="value-icon">📖</div>
            <div class="value-text">
                <h4>Sustainable Travel Resources</h4>
                <p>Data-driven insights for greener trips</p>
            </div>
        </div>
    </div>

    <!-- 5. DESTINATIONS SECTION -->
    <section class="section-wrapper fade-up">
        <div class="section-header-row">
            <div>
                <p class="section-label">Featured Destinations</p>
                <h2 class="section-title">Journeys That<br>Give Back</h2>
                <p style="color: var(--text-muted); margin-top: 12px; max-width: 480px;">Explore handpicked stays and experiences that protect nature and empower local communities.</p>
            </div>
            <a href="hotels.php" class="btn-outline">View All Destinations →</a>
        </div>
        <div class="dest-grid">
            <?php foreach (array_slice($displayHotels, 0, 4) as $i => $h):
                $badges = array_filter(array_map('trim', explode(',', $h['eco_badges'] ?? 'Eco Certified')));
                $tags = ['Eco Stay', 'Community Stay', 'Eco Stay', 'Eco Stay'];
                $ecoTags = ['Plant-Free', 'Eco-Certified', 'Carbon-Neutral', 'LEED Certified'];
            ?>
            <div class="dest-card">
                <div class="dest-card-img">
                    <img src="<?= htmlspecialchars($h['image_url']) ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                    <span class="dest-tag"><?= $tags[$i] ?? 'Eco Stay' ?></span>
                    <span class="dest-eco-tag"><?= $ecoTags[$i] ?? 'Eco' ?></span>
                </div>
                <div class="dest-card-body">
                    <p class="dest-loc">📍 <?= htmlspecialchars($h['destination_name']) ?></p>
                    <h3 class="dest-name"><?= htmlspecialchars($h['name']) ?></h3>
                    <div class="dest-meta">
                        <span>🌱 <?= number_format($h['water_saved_liters'] ?? 100000) ?>L saved</span>
                        <span>⚡ <?= htmlspecialchars($badges[0] ?? 'Eco Certified') ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 6. GREENER ROUTE SECTION -->
    <section class="route-section">
        <div class="section-wrapper">
            <p class="section-label">Greener Transit</p>
            <div class="route-content-grid fade-up">
                <div class="route-left">
                    <h2>Choose a<br>Greener Route</h2>
                    <p>Compare travel options and pick the best route based on cost, time, or environmental impact. Every journey on W2G helps reduce emissions.</p>
                    <a href="travel.php" class="btn-explore">Explore Transit →</a>
                </div>
                <div class="route-gallery">
                    <div class="route-gallery-item">
                        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=600&q=80" alt="Train" loading="lazy">
                        <div class="route-gallery-label">
                            <h4>Trains</h4>
                            <p>Lower Emissions</p>
                        </div>
                    </div>
                    <div class="route-gallery-item">
                        <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=600&q=80" alt="Buses" loading="lazy">
                        <div class="route-gallery-label">
                            <h4>Buses</h4>
                            <p>Budget Friendly</p>
                        </div>
                    </div>
                    <div class="route-gallery-item">
                        <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=600&q=80" alt="Carpool" loading="lazy">
                        <div class="route-gallery-label">
                            <h4>Carpool</h4>
                            <p>Share the journey</p>
                        </div>
                    </div>
                    <div class="route-gallery-item">
                        <img src="https://images.unsplash.com/photo-1436491865332-7a61a109db05?auto=format&fit=crop&w=600&q=80" alt="Cycling" loading="lazy">
                        <div class="route-gallery-label">
                            <h4>Cycling</h4>
                            <p>Zero emissions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. IMPACT SECTION -->
    <section class="impact-section fade-up">
        <div class="impact-grid">
            <div class="impact-image">
                <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80" alt="Green landscape" loading="lazy">
            </div>
            <div class="impact-data">
                <p class="section-label" style="color: var(--green-300);">Real Impact</p>
                <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--white); margin-bottom: 40px; line-height: 1.2;">Travel Today.<br>A Healthier Tomorrow.</h2>
                <div class="impact-stat">
                    <div class="number">1,420,000L</div>
                    <div class="desc">Clean Water Conserved</div>
                </div>
                <div class="impact-stat">
                    <div class="number">94,500 kWh</div>
                    <div class="desc">Green Energy Supported</div>
                </div>
                <div class="impact-stat">
                    <div class="number">12,800 kg</div>
                    <div class="desc">CO₂ Emissions Avoided</div>
                </div>
                <div class="impact-stat" style="margin-bottom: 0;">
                    <div class="number">100%</div>
                    <div class="desc">Single-Use Plastic Free Match</div>
                </div>
                <div class="impact-badge">🌿 Verified Impact Data</div>
            </div>
        </div>
    </section>

    <!-- 8. STEPS SECTION -->
    <section class="steps-section">
        <div class="section-wrapper">
            <div class="section-header-row fade-up">
                <div>
                    <p class="section-label">How It Works</p>
                    <h2 class="section-title">Simple Steps.<br>Greater Impact.</h2>
                </div>
                <a href="about.php" class="btn-outline">Our Mission →</a>
            </div>
            <div class="steps-grid fade-up">
                <div class="step-card">
                    <div class="step-num">1</div>
                    <h3>Plan</h3>
                    <p>Choose your destination and travel preferences for eco-friendliness.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">2</div>
                    <h3>Compare</h3>
                    <p>Find eco-friendly options for transit and stays at verified partners.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">3</div>
                    <h3>Book</h3>
                    <p>Secure your trip with verified partners and transparent pricing.</p>
                </div>
                <div class="step-card">
                    <div class="step-num">4</div>
                    <h3>Travel & Contribute</h3>
                    <p>Explore responsibly and make a real impact on local communities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 9. MISSION SECTION -->
    <section class="section-wrapper fade-up">
        <p class="section-label">Our Mission</p>
        <div class="mission-grid">
            <div class="mission-image">
                <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80" alt="Nature" loading="lazy">
            </div>
            <div class="mission-content">
                <h2>A World of<br>Greener Journeys</h2>
                <p>Every journey on W2G connects travelers with experiences that protect the environment, uplift rural communities, and create a sustainable future.</p>
                <p>We believe travel should leave places better than we found them. That's why we partner with locally-owned eco-stays and carbon-verified transit providers.</p>
                <a href="about.php" class="btn-explore">Learn More →</a>
            </div>
        </div>
    </section>

    <!-- 10. CTA SECTION -->
    <section class="cta-section">
        <div class="section-label" style="color: var(--green-300); text-align: center;">Join Our Journey</div>
        <div class="cta-inner">
            <h2>Be Part of a Cleaner,<br><span>Greener Planet.</span></h2>
            <div class="cta-email-form">
                <input type="email" placeholder="Enter your email">
                <button type="button">Join Now</button>
            </div>
        </div>
    </section>

    <!-- 11. FOOTER -->
    <footer class="w2g-footer">
        <div class="footer-inner">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="assets/img/logo.png" alt="W2G Logo" onerror="this.outerHTML='<span style=\'font-weight:800;font-size:1.3rem;color:white\'>W2G</span>'">
                    <p>Way2Green connects conscious travelers with eco-verified routes, verified sustainable stays, and experiences that give back to the planet.</p>
                    <div class="footer-social">
                        <a href="#">𝕏</a>
                        <a href="#">f</a>
                        <a href="#">in</a>
                        <a href="#">▶</a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Pages</h4>
                    <a href="index.php">Home</a>
                    <a href="hotels.php">Eco-Stays</a>
                    <a href="travel.php">Transit</a>
                    <a href="about.php">About Us</a>
                </div>
                <div class="footer-col">
                    <h4>Resources</h4>
                    <a href="hotels.php">Sustainable Guide</a>
                    <a href="about.php">Our Mission</a>
                    <a href="travel.php">Partner with Us</a>
                    <a href="about.php">FAQ</a>
                </div>
                <div class="footer-col">
                    <h4>Company</h4>
                    <?php if ($user): ?>
                        <a href="my-trips.php">My Passport</a>
                        <a href="logout.php">Sign Out</a>
                    <?php else: ?>
                        <a href="login.php">Sign In</a>
                        <a href="register.php">Create Account</a>
                    <?php endif; ?>
                    <a href="about.php">Privacy Policy</a>
                    <a href="about.php">Terms of Service</a>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© 2026 W2G. All rights reserved.</span>
                <div class="footer-bottom-links">
                    <span>Travel Lighter.</span>
                    <span>Leave it Brighter.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Bar -->
    <nav class="mobile-bottom-bar">
        <a href="index.php" class="mobile-nav-item active">
            <span class="icon">🏡</span>
            <span class="label">Home</span>
        </a>
        <a href="hotels.php" class="mobile-nav-item">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="travel.php" class="mobile-nav-item">
            <span class="icon">🚆</span>
            <span class="label">Transit</span>
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
    </nav>

    <script>
        // Drawer toggle
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        // Planner tabs
        function setTab(el) {
            document.querySelectorAll('.planner-tab').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        // Preference chips
        function togglePref(el) {
            document.querySelectorAll('.pref-chip').forEach(c => c.classList.remove('active'));
            el.classList.add('active');
        }

        // Set min date
        window.addEventListener('DOMContentLoaded', () => {
            const dateInput = document.getElementById('plannerDate');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
                dateInput.value = today;
            }
        });

        // Intersection Observer for fade-up animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    </script>
</body>
</html>
