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

$fallbackHotels = [
    ['id'=>1,'name'=>'Rainforest Canopy Retreat','destination_name'=>'Wayanad, Kerala','eco_rating'=>4.9,'price_per_night'=>4200,'water_saved_liters'=>145000,'power_saved_kwh'=>28000,'image_url'=>'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80','description'=>'100% solar microgrid with zero single-use plastics.','eco_badges'=>'100% Solar, Zero Plastic','accessibility_tags'=>'Wheelchair Ramps'],
    ['id'=>2,'name'=>'Mountain Village Homestay','destination_name'=>'Shimla, Himachal Pradesh','eco_rating'=>4.88,'price_per_night'=>3800,'water_saved_liters'=>110000,'power_saved_kwh'=>22000,'image_url'=>'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80','description'=>'Handcrafted stone lodge powered by micro-hydro.','eco_badges'=>'Micro-Hydro, Community Farm','accessibility_tags'=>'Ground Floor'],
    ['id'=>3,'name'=>'Coastal Dune Sanctuary','destination_name'=>'Alleppey, Kerala','eco_rating'=>4.95,'price_per_night'=>4500,'water_saved_liters'=>165000,'power_saved_kwh'=>32000,'image_url'=>'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80','description'=>'Sustainable dune retreat with organic farm-to-table.','eco_badges'=>'100% Renewable, Organic Farm','accessibility_tags'=>'Beach Access'],
    ['id'=>4,'name'=>'Urban Mangrove Retreat','destination_name'=>'Mumbai, Maharashtra','eco_rating'=>4.82,'price_per_night'=>5200,'water_saved_liters'=>125000,'power_saved_kwh'=>26000,'image_url'=>'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80','description'=>'LEED Platinum with vertical gardens and metro links.','eco_badges'=>'LEED Platinum, EV Charging','accessibility_tags'=>'Level Access']
];

$displayHotels = !empty($featuredHotels) ? $featuredHotels : $fallbackHotels;

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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --forest: #0f2b1c;
            --forest-800: #163d28;
            --forest-700: #1a5035;
            --green: #29AB87;
            --green-light: #3ec99b;
            --green-pale: #e8f7f1;
            --green-50: #f0faf6;
            --alice: #F0F8FF;
            --cream: #fafaf7;
            --white: #ffffff;
            --text: #1a2e23;
            --text-secondary: #4a6355;
            --text-muted: #7d9488;
            --border: #e5ebe7;
            --border-light: #f0f3f1;
            --font-display: 'DM Serif Display', Georgia, serif;
            --font-body: 'Inter', -apple-system, sans-serif;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-body);
            background: var(--white);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; display: block; }
        .container { max-width: 1240px; margin: 0 auto; padding: 0 32px; }

        /* ── NAVBAR ── */
        .navbar {
            position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
            width: calc(100% - 40px); max-width: 1260px;
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(0,0,0,0.04);
            border-radius: 60px;
            padding: 0 8px 0 24px;
            display: flex; align-items: center; justify-content: space-between;
            z-index: 1000;
            height: 56px;
        }
        .nav-brand img { height: 28px; }
        .nav-brand span { font-weight: 800; font-size: 1.2rem; color: var(--forest); }
        .nav-menu { display: flex; align-items: center; gap: 0; }
        .nav-menu a {
            font-size: 0.85rem; font-weight: 500; color: var(--text-secondary);
            padding: 8px 16px; border-radius: 40px; transition: all 0.2s;
        }
        .nav-menu a:hover { color: var(--forest); background: var(--green-50); }
        .nav-menu a.active { color: var(--white); background: var(--forest); }
        .nav-right { display: flex; align-items: center; gap: 6px; }
        .nav-btn {
            font-size: 0.84rem; font-weight: 600; padding: 8px 20px;
            border-radius: 40px; border: none; cursor: pointer; transition: all 0.2s;
        }
        .nav-btn-ghost { background: transparent; color: var(--text-secondary); }
        .nav-btn-ghost:hover { color: var(--forest); }
        .nav-btn-fill { background: var(--green); color: var(--white); }
        .nav-btn-fill:hover { background: var(--green-light); }
        .nav-hamburger {
            display: none; background: none; border: none; cursor: pointer; padding: 8px;
            flex-direction: column; gap: 4px;
        }
        .nav-hamburger span {
            display: block; width: 20px; height: 2px; background: var(--forest); border-radius: 1px;
        }

        /* ── HERO ── */
        .hero {
            position: relative; width: 100%; height: 100vh; min-height: 700px;
            overflow: hidden; display: flex; align-items: flex-end;
        }
        .hero-img {
            position: absolute; inset: 0;
            background: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=2400&q=85') center/cover no-repeat;
        }
        .hero-img::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(
                180deg,
                rgba(15,43,28,0.15) 0%,
                rgba(15,43,28,0.25) 40%,
                rgba(15,43,28,0.65) 80%,
                rgba(15,43,28,0.85) 100%
            );
        }
        .hero-inner {
            position: relative; z-index: 2;
            width: 100%; max-width: 1240px; margin: 0 auto;
            padding: 0 32px 140px;
        }
        .hero-label {
            font-size: 0.72rem; font-weight: 600; letter-spacing: 3px;
            text-transform: uppercase; color: rgba(255,255,255,0.5);
            margin-bottom: 20px;
        }
        .hero-title {
            font-family: var(--font-display); font-size: clamp(3.2rem, 7vw, 5.5rem);
            font-weight: 400; color: var(--white); line-height: 1.05;
            margin-bottom: 20px; max-width: 700px;
        }
        .hero-desc {
            font-size: 1rem; color: rgba(255,255,255,0.7); max-width: 480px;
            line-height: 1.7; margin-bottom: 28px; font-weight: 400;
        }
        .hero-cta {
            display: inline-flex; align-items: center; gap: 10px;
            background: var(--green); color: var(--white);
            padding: 14px 28px; border-radius: 8px;
            font-weight: 600; font-size: 0.92rem; transition: all 0.25s;
        }
        .hero-cta:hover { background: var(--green-light); }
        .hero-cta svg { width: 16px; height: 16px; }

        /* ── PLANNER ── */
        .planner-wrap {
            position: relative; z-index: 10;
            max-width: 1180px; margin: -72px auto 0; padding: 0 24px;
        }
        .planner {
            background: var(--white); border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .planner-tabs {
            display: flex; border-bottom: 1px solid var(--border-light);
        }
        .planner-tab {
            padding: 16px 24px; font-size: 0.84rem; font-weight: 600;
            color: var(--text-muted); border: none; background: none;
            cursor: pointer; border-bottom: 2px solid transparent; transition: all 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .planner-tab svg { width: 16px; height: 16px; stroke: currentColor; }
        .planner-tab.active { color: var(--forest); border-bottom-color: var(--green); }
        .planner-tab:hover { color: var(--forest); }
        .planner-body { padding: 28px 32px 24px; }
        .planner-row {
            display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 16px; align-items: end; margin-bottom: 20px;
        }
        .p-field label {
            display: block; font-size: 0.7rem; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;
        }
        .p-field input, .p-field select {
            width: 100%; padding: 12px 14px;
            border: 1px solid var(--border); border-radius: 10px;
            font-family: var(--font-body); font-size: 0.9rem; color: var(--text);
            background: var(--white); transition: border-color 0.2s; appearance: none;
            -webkit-appearance: none;
        }
        .p-field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237d9488' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 14px center;
            padding-right: 36px;
        }
        .p-field input:focus, .p-field select:focus {
            outline: none; border-color: var(--green);
        }
        .btn-search {
            padding: 12px 28px; background: var(--forest); color: var(--white);
            border: none; border-radius: 10px; font-weight: 600; font-size: 0.88rem;
            cursor: pointer; white-space: nowrap; display: flex; align-items: center; gap: 8px;
            transition: all 0.2s; height: 46px;
        }
        .btn-search:hover { background: var(--forest-800); }
        .btn-search svg { width: 16px; height: 16px; stroke: currentColor; }
        .planner-prefs {
            display: flex; align-items: center; justify-content: space-between;
        }
        .pref-chips { display: flex; gap: 8px; }
        .pref-chip {
            padding: 8px 18px; border-radius: 40px; font-size: 0.82rem; font-weight: 500;
            border: 1px solid var(--border); background: var(--white); color: var(--text-secondary);
            cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
        }
        .pref-chip svg { width: 14px; height: 14px; stroke: currentColor; }
        .pref-chip:hover { border-color: var(--green); color: var(--green); }
        .pref-chip.active { background: var(--green); color: var(--white); border-color: var(--green); }
        .pref-chip.active svg { stroke: var(--white); }
        .planner-meta {
            font-size: 0.76rem; color: var(--text-muted); font-weight: 500;
        }

        /* ── VALUE STRIP ── */
        .values {
            max-width: 1180px; margin: 56px auto; padding: 0 24px;
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 40px;
            border-top: 1px solid var(--border-light); padding-top: 56px;
        }
        .val-item { display: flex; align-items: flex-start; gap: 16px; }
        .val-icon {
            width: 40px; height: 40px; border-radius: 10px; background: var(--green-50);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .val-icon svg { width: 20px; height: 20px; stroke: var(--green); stroke-width: 1.5; fill: none; }
        .val-item h4 { font-size: 0.88rem; font-weight: 600; color: var(--text); margin-bottom: 2px; }
        .val-item p { font-size: 0.78rem; color: var(--text-muted); line-height: 1.45; }

        /* ── SECTION PATTERNS ── */
        .sec { padding: 100px 0; }
        .sec-label {
            font-size: 0.68rem; font-weight: 600; letter-spacing: 2.5px;
            text-transform: uppercase; color: var(--text-muted); margin-bottom: 12px;
        }
        .sec-title {
            font-family: var(--font-display); font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 400; color: var(--text); line-height: 1.12;
        }
        .sec-head {
            display: flex; align-items: flex-end; justify-content: space-between;
            margin-bottom: 48px;
        }
        .btn-text {
            font-size: 0.84rem; font-weight: 600; color: var(--text-secondary);
            display: flex; align-items: center; gap: 6px; transition: all 0.2s;
        }
        .btn-text svg { width: 16px; height: 16px; stroke: currentColor; }
        .btn-text:hover { color: var(--green); }

        /* ── DESTINATIONS ── */
        .dest-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .dest-card { cursor: pointer; transition: transform 0.3s; }
        .dest-card:hover { transform: translateY(-4px); }
        .dest-img {
            position: relative; border-radius: 14px; overflow: hidden;
            aspect-ratio: 3/4; margin-bottom: 14px;
        }
        .dest-img img { width: 100%; height: 100%; object-fit: cover; }
        .dest-label {
            position: absolute; top: 14px; left: 14px;
            padding: 5px 12px; border-radius: 6px;
            font-size: 0.7rem; font-weight: 600;
            background: var(--green); color: var(--white);
        }
        .dest-card h3 {
            font-size: 1rem; font-weight: 600; color: var(--text); margin-bottom: 4px;
        }
        .dest-card .loc {
            font-size: 0.8rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;
        }
        .dest-card .loc svg { width: 13px; height: 13px; stroke: var(--text-muted); }
        .dest-card .meta {
            display: flex; gap: 12px; margin-top: 8px; font-size: 0.76rem; color: var(--text-muted);
        }

        /* ── TRANSIT ── */
        .transit-sec { background: var(--cream); }
        .transit-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: center; }
        .transit-text h2 {
            font-family: var(--font-display); font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 400; color: var(--text); line-height: 1.12; margin-bottom: 16px;
        }
        .transit-text p {
            font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7; margin-bottom: 28px;
        }
        .transit-btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--green); color: var(--white);
            padding: 12px 24px; border-radius: 8px;
            font-weight: 600; font-size: 0.88rem; transition: all 0.2s;
        }
        .transit-btn svg { width: 16px; height: 16px; stroke: currentColor; }
        .transit-btn:hover { background: var(--green-light); }
        .transit-photos { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .transit-photo {
            position: relative; border-radius: 12px; overflow: hidden;
            aspect-ratio: 4/3;
        }
        .transit-photo img { width: 100%; height: 100%; object-fit: cover; }
        .transit-photo-label {
            position: absolute; bottom: 0; left: 0; right: 0; padding: 28px 14px 12px;
            background: linear-gradient(transparent, rgba(0,0,0,0.65));
        }
        .transit-photo-label h4 { font-size: 0.88rem; font-weight: 600; color: var(--white); }
        .transit-photo-label span { font-size: 0.72rem; color: rgba(255,255,255,0.65); }

        /* ── IMPACT ── */
        .impact { overflow: hidden; }
        .impact-grid { display: grid; grid-template-columns: 1fr 1fr; }
        .impact-img { position: relative; min-height: 520px; }
        .impact-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .impact-data {
            background: var(--forest); color: var(--white); padding: 72px 64px;
            display: flex; flex-direction: column; justify-content: center;
        }
        .impact-data .sec-label { color: var(--green-light); }
        .impact-data h2 {
            font-family: var(--font-display); font-size: clamp(1.8rem, 3vw, 2.4rem);
            font-weight: 400; color: var(--white); line-height: 1.15; margin-bottom: 48px;
        }
        .impact-stat { margin-bottom: 28px; }
        .impact-stat:last-of-type { margin-bottom: 32px; }
        .impact-num {
            font-size: 1.8rem; font-weight: 700; color: var(--green-light);
            letter-spacing: -0.5px; line-height: 1.2;
        }
        .impact-desc { font-size: 0.84rem; color: rgba(255,255,255,0.55); margin-top: 2px; }
        .impact-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 6px;
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);
            font-size: 0.76rem; font-weight: 600; color: var(--green-light);
        }

        /* ── HOW IT WORKS ── */
        .steps { background: var(--white); }
        .steps-row {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 0;
            position: relative;
        }
        .steps-row::before {
            content: ''; position: absolute; top: 24px; left: 12.5%; right: 12.5%;
            height: 1px; background: var(--border);
        }
        .step { text-align: center; padding: 0 20px; position: relative; }
        .step-circle {
            width: 48px; height: 48px; border-radius: 50%;
            border: 1.5px solid var(--border); background: var(--white);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 18px; font-size: 0.92rem; font-weight: 600;
            color: var(--text); position: relative; z-index: 2;
        }
        .step h3 { font-size: 0.95rem; font-weight: 600; color: var(--text); margin-bottom: 6px; }
        .step p { font-size: 0.82rem; color: var(--text-muted); line-height: 1.5; }

        /* ── MISSION ── */
        .mission-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center;
        }
        .mission-img { border-radius: 16px; overflow: hidden; aspect-ratio: 4/3; }
        .mission-img img { width: 100%; height: 100%; object-fit: cover; }
        .mission-text h2 {
            font-family: var(--font-display); font-size: clamp(1.8rem, 3vw, 2.5rem);
            font-weight: 400; color: var(--text); line-height: 1.12; margin-bottom: 20px;
        }
        .mission-text p {
            font-size: 0.95rem; color: var(--text-secondary); line-height: 1.8; margin-bottom: 20px;
        }
        .mission-btn {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 0.88rem; font-weight: 600; color: var(--green);
            border-bottom: 1px solid var(--green); padding-bottom: 2px; transition: all 0.2s;
        }
        .mission-btn svg { width: 16px; height: 16px; stroke: currentColor; }
        .mission-btn:hover { color: var(--green-light); border-color: var(--green-light); }

        /* ── CTA ── */
        .cta {
            background: var(--forest); padding: 64px 0;
        }
        .cta-inner {
            max-width: 1240px; margin: 0 auto; padding: 0 32px;
        }
        .cta-label { font-size: 0.68rem; font-weight: 600; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255,255,255,0.35); margin-bottom: 20px; }
        .cta-row { display: flex; align-items: center; justify-content: space-between; gap: 32px; }
        .cta h2 {
            font-family: var(--font-display); font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 400; color: var(--white); line-height: 1.2;
        }
        .cta h2 em { font-style: normal; color: var(--green-light); }
        .cta-form { display: flex; gap: 8px; }
        .cta-form input {
            padding: 12px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05); color: var(--white);
            font-family: var(--font-body); font-size: 0.88rem; min-width: 260px;
        }
        .cta-form input::placeholder { color: rgba(255,255,255,0.3); }
        .cta-form button {
            padding: 12px 24px; border-radius: 8px; background: var(--green);
            color: var(--white); border: none; font-weight: 600; font-size: 0.88rem;
            cursor: pointer; white-space: nowrap; transition: all 0.2s;
        }
        .cta-form button:hover { background: var(--green-light); }

        /* ── FOOTER ── */
        .footer {
            background: var(--forest-800); color: rgba(255,255,255,0.55);
            padding: 64px 0 32px;
        }
        .footer-grid {
            display: grid; grid-template-columns: 1.6fr 1fr 1fr 1fr;
            gap: 48px; margin-bottom: 48px;
        }
        .footer-brand img { height: 24px; margin-bottom: 16px; filter: brightness(0) invert(1); }
        .footer-brand span { font-weight: 800; font-size: 1.1rem; color: var(--white); display: block; margin-bottom: 16px; }
        .footer-brand p { font-size: 0.84rem; line-height: 1.6; margin-bottom: 20px; }
        .footer-social { display: flex; gap: 8px; }
        .footer-social a {
            width: 32px; height: 32px; border-radius: 6px;
            background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
        }
        .footer-social a svg { width: 14px; height: 14px; stroke: rgba(255,255,255,0.5); }
        .footer-social a:hover { background: rgba(255,255,255,0.12); }
        .footer-social a:hover svg { stroke: var(--white); }
        .footer-col h4 {
            font-size: 0.72rem; font-weight: 600; color: rgba(255,255,255,0.35);
            text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 16px;
        }
        .footer-col a { display: block; font-size: 0.84rem; padding: 4px 0; transition: color 0.2s; }
        .footer-col a:hover { color: var(--white); }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.06); padding-top: 24px;
            display: flex; justify-content: space-between; font-size: 0.76rem;
            color: rgba(255,255,255,0.3);
        }
        .footer-bottom-right { display: flex; gap: 20px; }

        /* ── MOBILE ── */
        .drawer-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.4);
            z-index: 1100; opacity: 0; pointer-events: none; transition: opacity 0.3s;
        }
        .drawer-overlay.active { opacity: 1; pointer-events: auto; }
        .mobile-drawer {
            position: fixed; top: 0; right: 0; bottom: 0; width: 280px;
            background: var(--white); z-index: 1200; transform: translateX(100%);
            transition: transform 0.3s; padding: 24px; overflow-y: auto;
        }
        .mobile-drawer.open { transform: translateX(0); }
        .drawer-close {
            position: absolute; top: 16px; right: 16px; background: none; border: none;
            font-size: 1.2rem; cursor: pointer; color: var(--text);
        }
        .drawer-link {
            display: block; padding: 14px 0; font-size: 0.95rem; font-weight: 500;
            color: var(--text-secondary); border-bottom: 1px solid var(--border-light);
        }
        .drawer-link:hover { color: var(--green); }
        .mobile-bottom-bar {
            display: none; position: fixed; bottom: 0; left: 0; right: 0;
            background: var(--white); border-top: 1px solid var(--border);
            z-index: 900; grid-template-columns: repeat(5, 1fr);
            padding: 6px 0 calc(6px + env(safe-area-inset-bottom));
        }
        .mob-nav {
            display: flex; flex-direction: column; align-items: center; gap: 2px;
            padding: 4px; font-size: 0.65rem; color: var(--text-muted); text-decoration: none;
        }
        .mob-nav svg { width: 20px; height: 20px; stroke: currentColor; }
        .mob-nav.active { color: var(--green); }

        /* ── RESPONSIVE ── */
        @media (max-width: 1024px) {
            .planner-row { grid-template-columns: 1fr 1fr; }
            .dest-grid { grid-template-columns: 1fr 1fr; }
            .steps-row { grid-template-columns: 1fr 1fr; gap: 32px; }
            .steps-row::before { display: none; }
            .impact-grid { grid-template-columns: 1fr; }
            .impact-img { min-height: 320px; }
            .mission-grid { grid-template-columns: 1fr; gap: 32px; }
            .transit-grid { grid-template-columns: 1fr; gap: 32px; }
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .cta-row { flex-direction: column; text-align: center; }
        }
        @media (max-width: 768px) {
            .navbar { padding: 0 16px; top: 8px; width: calc(100% - 16px); height: 48px; }
            .nav-menu { display: none; }
            .nav-btn-ghost, .nav-btn-fill { display: none; }
            .nav-hamburger { display: flex; }
            .hero { height: 85vh; min-height: 560px; }
            .hero-inner { padding: 0 20px 120px; }
            .hero-title { font-size: 2.6rem; }
            .planner-wrap { margin-top: -60px; padding: 0 12px; }
            .planner-body { padding: 20px 16px; }
            .planner-row { grid-template-columns: 1fr; }
            .pref-chips { flex-wrap: wrap; }
            .values { grid-template-columns: 1fr 1fr; gap: 24px; padding-top: 40px; }
            .dest-grid { grid-template-columns: 1fr; }
            .transit-photos { grid-template-columns: 1fr; }
            .steps-row { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .footer-bottom { flex-direction: column; gap: 8px; text-align: center; }
            .cta-form { flex-direction: column; }
            .cta-form input { min-width: auto; }
            .mobile-bottom-bar { display: grid; }
            body { padding-bottom: 64px; }
            .sec { padding: 60px 0; }
            .container { padding: 0 16px; }
        }

        /* Fade-in on scroll */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.7s, transform 0.7s; }
        .reveal.visible { opacity: 1; transform: none; }
    </style>
</head>
<body>

    <!-- Mobile drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">&#x2715;</button>
        <div style="font-weight:700;font-size:1.1rem;color:var(--forest);margin:8px 0 24px;">W2G</div>
        <a href="index.php" class="drawer-link" style="color:var(--green)">Home</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="travel.php" class="drawer-link">Transit</a>
        <a href="about.php" class="drawer-link">About Us</a>
        <?php if ($user): ?>
            <a href="my-trips.php" class="drawer-link">My Passport</a>
            <a href="logout.php" class="drawer-link" style="color:#b91c1c">Sign Out</a>
        <?php else: ?>
            <a href="login.php" class="drawer-link">Sign In</a>
            <a href="register.php" class="drawer-link" style="color:var(--green)">Get Started</a>
        <?php endif; ?>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">
            <img src="assets/img/logo.png" alt="W2G" onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">
            <span style="display:none">W2G</span>
        </a>
        <div class="nav-menu">
            <a href="index.php" class="active">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Transit</a>
            <a href="about.php">About Us</a>
            <?php if ($user): ?>
                <a href="my-trips.php">My Passport</a>
            <?php endif; ?>
        </div>
        <div class="nav-right">
            <?php if ($user): ?>
                <a href="my-trips.php" class="nav-btn nav-btn-ghost"><?= htmlspecialchars($user['name']) ?></a>
                <a href="logout.php" class="nav-btn nav-btn-fill" style="background:#b91c1c">Sign Out</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn nav-btn-ghost">Sign In</a>
                <a href="register.php" class="nav-btn nav-btn-fill">Sign Up</a>
            <?php endif; ?>
            <button class="nav-hamburger" onclick="toggleDrawer()" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-img"></div>
        <div class="hero-inner">
            <p class="hero-label">Travel &middot; Explore &middot; Preserve</p>
            <h1 class="hero-title">Travel Further.<br>Tread Lighter.</h1>
            <p class="hero-desc">Plan your journeys with lower emissions, verified eco-stays, and meaningful experiences for a greener planet.</p>
            <a href="travel.php" class="hero-cta">
                Start Planning
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
        </div>

        <h1 class="hero-headline" id="hero-headline">
            Plan Your Journey.<br><em>Travel Consciously.</em>
        </h1>

        <p class="hero-sub">
            Way2Green helps you plan your complete trip — compare travel options by cost, time and carbon impact, find verified eco-stays, and earn your digital Eco Passport.
        </p>
    </section>

    <!-- TRIP PLANNER -->
    <div class="planner-wrap reveal">
        <div class="planner">
            <div class="planner-tabs">
                <button class="planner-tab active" onclick="setTab(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/></svg>
                    Plan a Trip
                </button>
                <button class="planner-tab" onclick="window.location='hotels.php'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/><path d="m9 16 .348-.24c1.465-1.013 3.84-1.013 5.304 0L15 16"/><path d="M8 7h.01"/><path d="M16 7h.01"/></svg>
                    Eco-Stays
                </button>
                <button class="planner-tab" onclick="window.location='travel.php'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/></svg>
                    Transit
                </button>
            </div>
            <div class="planner-body">
                <form method="GET" action="travel.php">
                    <div class="planner-row">
                        <div class="p-field">
                            <label>From</label>
                            <input type="text" name="origin" placeholder="Leaving from?">
                        </div>
                        <div class="p-field">
                            <label>To</label>
                            <select name="dest">
                                <option value="">Where to?</option>
                                <?php foreach ($destinations as $d): ?>
                                    <option value="<?= htmlspecialchars($d['name']) ?>"><?= htmlspecialchars($d['name']) ?></option>
                                <?php endforeach; ?>
                                <?php if (empty($destinations)): ?>
                                    <option value="Agra">Agra</option><option value="Goa">Goa</option>
                                    <option value="Munnar">Munnar</option><option value="Manali">Manali</option>
                                    <option value="Mumbai">Mumbai</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="p-field">
                            <label>Date</label>
                            <input type="date" id="planDate" name="departure_date">
                        </div>
                        <div class="p-field">
                            <label>Travellers</label>
                            <select name="travellers">
                                <option>1 Traveller</option><option>2 Travellers</option>
                                <option>3 Travellers</option><option>4+</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn-search">
                                Search Routes
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="planner-prefs">
                        <div class="pref-chips">
                            <span class="pref-chip" onclick="pickPref(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                Cheapest
                            </span>
                            <span class="pref-chip" onclick="pickPref(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                Fastest
                            </span>
                            <span class="pref-chip active" onclick="pickPref(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m7.5 12 3 3 6-6"/></svg>
                                Most Eco-Friendly
                            </span>
                            <span class="pref-chip" onclick="pickPref(this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                                Custom
                            </span>
                        </div>
                        <span class="planner-meta">1 Route &middot; 4 Greener Options</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- VALUE STRIP -->
    <div class="values reveal">
        <div class="val-item">
            <div class="val-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M2 22c1.25-.987 2.27-1.975 3.9-2.2 1.9-.2 2.1 1 4 .8 1.9-.2 1.5-2 3.5-2 2.3 0 2.1 2 4 2 1.5 0 2.7-.8 3.6-1.6"/><path d="M2 16c1.25-.987 2.27-1.975 3.9-2.2 1.9-.2 2.1 1 4 .8 1.9-.2 1.5-2 3.5-2 2.3 0 2.1 2 4 2 1.5 0 2.7-.8 3.6-1.6"/><path d="M12 2v6"/><path d="m4.93 10.93 1.41 1.41"/><path d="M2 18h2"/><path d="M20 18h2"/><path d="m19.07 10.93-1.41 1.41"/><path d="M22 22H2"/></svg>
            </div>
            <div>
                <h4>Lower Carbon Footprint</h4>
                <p>Every route optimized for minimal emissions</p>
            </div>
        </div>
        <div class="val-item">
            <div class="val-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div>
                <h4>Verified &amp; Safe Eco-Stays</h4>
                <p>Certified sustainable accommodations</p>
            </div>
        </div>
        <div class="val-item">
            <div class="val-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h4>Support Local Communities</h4>
                <p>Travel that empowers destinations</p>
            </div>
        </div>
        <div class="val-item">
            <div class="val-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
            </div>
            <div>
                <h4>Sustainable Travel Resources</h4>
                <p>Data-driven insights for greener trips</p>
            </div>
        </div>
    </div>

    <!-- DESTINATIONS -->
    <section class="sec">
        <div class="container">
            <div class="sec-head reveal">
                <div>
                    <p class="sec-label">Featured Destinations</p>
                    <h2 class="sec-title">Journeys That<br>Give Back</h2>
                    <p style="color:var(--text-muted);margin-top:12px;max-width:420px;font-size:0.92rem;line-height:1.6">Explore handpicked stays and experiences that protect nature and empower local communities.</p>
                </div>
                <a href="hotels.php" class="btn-text">
                    View All Destinations
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="dest-grid reveal">
                <?php foreach (array_slice($displayHotels, 0, 4) as $i => $h):
                    $badges = array_filter(array_map('trim', explode(',', $h['eco_badges'] ?? 'Eco Certified')));
                    $labels = ['Eco Stay', 'Community Stay', 'Eco Stay', 'Eco Stay'];
                ?>
                <a href="checkout.php?hotel_id=<?= $h['id'] ?>&hotel_name=<?= urlencode($h['name']) ?>&dest_name=<?= urlencode($h['destination_name']) ?>&price=<?= $h['price_per_night'] ?>" class="dest-card">
                    <div class="dest-img">
                        <img src="<?= htmlspecialchars($h['image_url']) ?>" alt="<?= htmlspecialchars($h['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                        <span class="dest-label"><?= $labels[$i] ?? 'Eco Stay' ?></span>
                    </div>
                    <h3><?= htmlspecialchars($h['name']) ?></h3>
                    <p class="loc">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($h['destination_name']) ?>
                    </p>
                    <div class="meta">
                        <span><?= number_format($h['water_saved_liters'] ?? 100000) ?>L saved</span>
                        <span><?= htmlspecialchars($badges[0] ?? 'Eco Certified') ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- GREENER ROUTE / TRANSIT -->
    <section class="sec transit-sec">
        <div class="container">
            <p class="sec-label">Greener Transit</p>
            <div class="transit-grid reveal">
                <div class="transit-text">
                    <h2>Choose a<br>Greener Route</h2>
                    <p>Compare travel options and pick the best route based on cost, time, or environmental impact.</p>
                    <a href="travel.php" class="transit-btn">
                        Explore Transit
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="transit-photos">
                    <div class="transit-photo">
                        <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?auto=format&fit=crop&w=600&q=80" alt="Train" loading="lazy">
                        <div class="transit-photo-label"><h4>Trains</h4><span>Lower Emissions</span></div>
                    </div>
                    <div class="transit-photo">
                        <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?auto=format&fit=crop&w=600&q=80" alt="Bus" loading="lazy">
                        <div class="transit-photo-label"><h4>Buses</h4><span>Budget Friendly</span></div>
                    </div>
                    <div class="transit-photo">
                        <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?auto=format&fit=crop&w=600&q=80" alt="Carpool" loading="lazy">
                        <div class="transit-photo-label"><h4>Carpool</h4><span>Share the journey</span></div>
                    </div>
                    <div class="transit-photo">
                        <img src="https://images.unsplash.com/photo-1558618666-fcd25c85f82e?auto=format&fit=crop&w=600&q=80" alt="Electric" loading="lazy">
                        <div class="transit-photo-label"><h4>Electric</h4><span>Zero emissions</span></div>
                    </div>
                </div>
            </div>
        </div>
        <p style="font-size:0.78rem; color: var(--text-muted); margin-top: 0.8rem; font-style: italic; text-align: center;">
            Example: New Delhi → Munnar. Live pricing coming in Phase 7.
        </p>
    </section>

    <!-- IMPACT -->
    <section class="impact reveal">
        <div class="impact-grid">
            <div class="impact-img">
                <img src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80" alt="Landscape" loading="lazy">
            </div>
            <div class="impact-data">
                <p class="sec-label">Real Impact</p>
                <h2>Travel Today.<br>A Healthier Tomorrow.</h2>
                <p style="font-size:0.9rem;color:rgba(255,255,255,0.55);margin-bottom:40px;line-height:1.7">Every journey on W2G helps reduce emissions, support communities, and protect natural habitats.</p>
                <div class="impact-stat">
                    <div class="impact-num">1,420,000L</div>
                    <div class="impact-desc">Clean Water Conserved</div>
                </div>
                <div class="impact-stat">
                    <div class="impact-num">94,500 kWh</div>
                    <div class="impact-desc">Green Energy Supported</div>
                </div>
                <div class="impact-stat">
                    <div class="impact-num">12,800 kg</div>
                    <div class="impact-desc">CO&#8322; Emissions Avoided</div>
                </div>
                <div class="impact-stat">
                    <div class="impact-num">100%</div>
                    <div class="impact-desc">Single-Use Plastic Free Match</div>
                </div>
                <div class="impact-badge">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Verified Impact Data
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="sec steps">
        <div class="container">
            <div class="sec-head reveal">
                <div>
                    <p class="sec-label">How It Works</p>
                    <h2 class="sec-title">Simple Steps.<br>Greater Impact.</h2>
                </div>
                <a href="about.php" class="btn-text">
                    Our Mission
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
            <div class="steps-row reveal">
                <div class="step">
                    <div class="step-circle">1</div>
                    <h3>Plan</h3>
                    <p>Choose your destination and travel preferences.</p>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <h3>Compare</h3>
                    <p>Find eco-friendly options for transit and stays.</p>
                </div>
                <div class="step">
                    <div class="step-circle">3</div>
                    <h3>Book</h3>
                    <p>Secure your trip with verified partners.</p>
                </div>
                <div class="step">
                    <div class="step-circle">4</div>
                    <h3>Travel &amp; Contribute</h3>
                    <p>Explore responsibly and make a real impact.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- MISSION -->
    <section class="sec" style="background:var(--cream)">
        <div class="container">
            <p class="sec-label">Our Mission</p>
            <div class="mission-grid reveal">
                <div class="mission-img">
                    <img src="https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80" alt="Nature" loading="lazy">
                </div>
                <div class="mission-text">
                    <h2>A World of<br>Greener Journeys</h2>
                    <p>Every journey on W2G connects travelers with experiences that protect the environment, uplift rural communities, and create a sustainable future.</p>
                    <p>We believe travel should leave places better than we found them. That's why we partner with locally-owned eco-stays and carbon-verified transit providers.</p>
                    <a href="about.php" class="mission-btn">
                        Learn More
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <div class="cta-inner">
            <p class="cta-label">Join Our Journey</p>
            <div class="cta-row">
                <h2>Be Part of a Cleaner,<br><em>Greener Planet.</em></h2>
                <div class="cta-form">
                    <input type="email" placeholder="Enter your email">
                    <button type="button">Join Now</button>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="assets/img/logo.png" alt="W2G" onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                    <span style="display:none">W2G</span>
                    <p>Way2Green connects conscious travelers with eco-verified routes, sustainable stays, and experiences that give back to the planet.</p>
                    <div class="footer-social">
                        <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg></a>
                        <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
                        <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg></a>
                        <a href="#"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg></a>
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
                <span>&copy; 2026 W2G. All rights reserved.</span>
                <div class="footer-bottom-right">
                    <span>Travel Lighter.</span>
                    <span>Leave it Brighter.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Bar -->
    <nav class="mobile-bottom-bar">
        <a href="index.php" class="mob-nav active">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Home</span>
        </a>
        <a href="hotels.php" class="mob-nav">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/><path d="m9 16 .348-.24c1.465-1.013 3.84-1.013 5.304 0L15 16"/></svg>
            <span>Stays</span>
        </a>
        <a href="travel.php" class="mob-nav">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" x2="4" y1="22" y2="15"/></svg>
            <span>Transit</span>
        </a>
        <a href="about.php" class="mob-nav">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>About</span>
        </a>
        <?php if ($user): ?>
        <a href="my-trips.php" class="mob-nav">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/></svg>
            <span>Passport</span>
        </a>
        <?php else: ?>
        <a href="login.php" class="mob-nav">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Sign In</span>
        </a>
        <?php endif; ?>
    </nav>

    <script>
        function toggleDrawer(){document.getElementById('mobileDrawer').classList.toggle('open');document.getElementById('drawerOverlay').classList.toggle('active')}
        function setTab(el){document.querySelectorAll('.planner-tab').forEach(t=>t.classList.remove('active'));el.classList.add('active')}
        function pickPref(el){document.querySelectorAll('.pref-chip').forEach(c=>c.classList.remove('active'));el.classList.add('active')}
        window.addEventListener('DOMContentLoaded',()=>{const d=document.getElementById('planDate');if(d){const t=new Date().toISOString().split('T')[0];d.min=t;d.value=t}});
        const obs=new IntersectionObserver(e=>{e.forEach(en=>{if(en.isIntersecting)en.target.classList.add('visible')})},{threshold:0.08,rootMargin:'0px 0px -40px 0px'});
        document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
    </script>
</body>
</html>
