<?php
// hotels.php - Phase 2: Eco-Hotels Catalog & Accessible Hospitality
require_once 'db.php';
require_once 'user_auth.php';

// Must be logged in
require_user_login('hotels.php');
$user = get_logged_in_user();

$destId = isset($_GET['dest_id']) ? intval($_GET['dest_id']) : 0;
$destName = trim($_GET['dest_name'] ?? $_GET['dest'] ?? '');
$origin = trim($_GET['origin'] ?? 'Mumbai');
$mode = trim($_GET['mode'] ?? 'train');
$distance = floatval($_GET['distance'] ?? 450);
$co2Saved = floatval($_GET['co2_saved'] ?? 50);

// Fetch all destinations for switcher
$allDests = [];
try {
    $stmt = $pdo->query("SELECT * FROM destinations ORDER BY name ASC");
    $allDests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $allDests = [];
}

// If destId is 0 but destName was provided, match it against all destinations
if ($destId === 0 && !empty($destName)) {
    foreach ($allDests as $d) {
        if (stripos($d['name'], $destName) !== false || stripos($destName, $d['name']) !== false) {
            $destId = $d['id'];
            $destName = $d['name'];
            break;
        }
    }
}

// If still 0, default to first destination
if ($destId === 0 && !empty($allDests)) {
    $destId = $allDests[0]['id'];
    $destName = $allDests[0]['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 2: Choose Your Eco-Stay — Way2Green</title>
    <meta name="description" content="Discover verified inclusive eco-lodges with water conservation and accessibility features.">
    
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
        <a href="travel.php" class="drawer-link">Plan Transit</a>
        <a href="hotels.php" class="drawer-link" style="color: var(--primary);">Eco-Stays</a>
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
            <a href="travel.php">Plan Transit</a>
            <a href="hotels.php" class="active">Eco-Stays</a>
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

    <main class="page-container" style="max-width: 1140px;">
        <!-- Visual Multi-Step Tracker -->
        <div class="step-progress-bar">
            <div class="step-bubble">
                <span class="step-num">1</span>
                <span>Green Transit</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble active">
                <span class="step-num">2</span>
                <span>Select Eco-Stay</span>
            </div>
            <span style="color: var(--text-muted);">➔</span>
            <div class="step-bubble">
                <span class="step-num">3</span>
                <span>Eco-Passport</span>
            </div>
        </div>

        <!-- Selected Route Glassmorphic Context Bar (No Jitter, Spacious) -->
        <div class="trip-context-bar">
            <div class="trip-route-info">
                <span class="route-badge">
                    Chosen Route
                </span>
                <span class="route-cities">
                    <span id="route-origin-text"><?= htmlspecialchars($origin) ?></span>
                    <span style="margin: 0 6px; opacity: 0.7;">➔</span>
                    <span id="currentDestLabel"><?= htmlspecialchars($destName) ?></span>
                </span>
                <span class="route-meta" id="route-meta-text">
                    • <?= strtoupper($mode) ?> (<?= $distance ?> km) • <strong><?= $co2Saved ?> kg CO₂ Avoided</strong>
                </span>
            </div>

            <!-- Switch Destination Dropdown with Clean Styling -->
            <div class="trip-dest-picker">
                <label for="destinationFilterSelect" style="font-size: 0.88rem; font-weight: 700; color: var(--primary);">Change Region:</label>
                <select id="destinationFilterSelect" class="dest-select-input" onchange="changeDestination(this.value)">
                    <?php foreach ($allDests as $d): ?>
                        <option value="<?= $d['id'] ?>" data-name="<?= htmlspecialchars($d['name']) ?>" <?= $d['id'] == $destId ? 'selected' : '' ?>>
                            📍 <?= htmlspecialchars($d['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Inclusivity & Accessibility Filters -->
        <div class="section-head reveal-on-scroll" style="margin-bottom: 1.5rem;">
            <span class="section-tag">Step 2 of 3</span>
            <h1 class="section-title">Verified Sustainable & Accessible Stays</h1>
            <p class="section-desc">Every retreat is evaluated for low energy consumption, water conservation, and certified barrier-free accessibility.</p>
        </div>

        <div class="filter-pills-row">
            <button type="button" class="pill-filter active" onclick="applyFilter('all', this)">
                All Eco-Stays
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('wheelchair', this)">
                Wheelchair & Step-Free Access
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('sensory', this)">
                Sensory Quiet & Low Stimulation
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('braille', this)">
                Braille & Audio Assisted
            </button>
            <button type="button" class="pill-filter" onclick="applyFilter('dog', this)">
                Service Dog Friendly
            </button>
        </div>

        <!-- Stays Grid -->
        <div class="stays-grid" id="staysContainer">
            <div style="text-align: center; grid-column: 1 / -1; padding: 2rem; color: var(--text-muted);">
                Finding verified stays...
            </div>
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
                <h4 class="footer-heading">Pages</h4>
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
                    <a href="my-trips.php">My Saved Passports</a>
                    <a href="logout.php">Sign Out</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Green & Inclusive Travel Challenge.
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
        <a href="hotels.php" class="mobile-nav-item active">
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
    <script>
        // ── PHP-injected server-side values ──────────────────────────────
        let currentDestId   = <?= $destId ?>;
        let currentFilter   = 'all';
        let originCity      = "<?= htmlspecialchars($origin) ?>";  // overridden by localStorage
        const travelMode    = "<?= htmlspecialchars($mode) ?>";
        const travelDistance = <?= $distance ?>;
        const carbonAvoided  = <?= $co2Saved ?>;

        let currentDestName = '';  // updated from localStorage & dropdown

        // ── Mobile Drawer ─────────────────────────────────────────────────
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        // ================================================================
        //  MOCK ECO-STAYS DATABASE  (8 destinations × 4 properties each)
        //  Acts as guaranteed fallback when real API has no data.
        // ================================================================
        const MOCK_DB = {
            agra: [
                { id:'m-agra-1', name:'Taj Eco Sanctuary', destination_name:'Agra',
                  description:'Steps from the Taj Mahal, this certified zero-waste heritage retreat runs entirely on rooftop solar and harvested rainwater. Braille menus, wide-aisle corridors, and tactile path guides throughout.',
                  image_url:'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:5800, water_saved_liters:180000, power_saved_kwh:42000,
                  eco_badges:'Solar Powered, Zero Waste, Rainwater Harvesting',
                  accessibility_tags:'Wheelchair Friendly, Braille Menus, Audio Guides' },
                { id:'m-agra-2', name:'Yamuna Green Lodge', destination_name:'Agra',
                  description:'A serene riverside eco-lodge with organic gardens and composting toilets. Roll-in showers, pool lift, and sensory-calm zones for guests with cognitive needs.',
                  image_url:'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:3900, water_saved_liters:120000, power_saved_kwh:28000,
                  eco_badges:'Organic Garden, Composting, Low Carbon',
                  accessibility_tags:'Roll-in Showers, Pool Lift, Sensory Calm Zones' },
                { id:'m-agra-3', name:'Heritage Biome Retreat', destination_name:'Agra',
                  description:'Built from reclaimed Mughal-era stone using biogas from kitchen waste and a rooftop herb garden. Service dog facilities and trained accessibility staff on-site.',
                  image_url:'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:4500, water_saved_liters:95000, power_saved_kwh:19000,
                  eco_badges:'Biogas Kitchen, Reclaimed Materials, Rooftop Garden',
                  accessibility_tags:'Service Dog Friendly, Accessible Staff, Step-Free' },
                { id:'m-agra-4', name:'Lotus Pond Eco Haveli', destination_name:'Agra',
                  description:'A 200-year-old haveli restored with traditional lime plaster and passive solar design. Silent EV rickshaw transfers and sign-language-trained concierge.',
                  image_url:'https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:3200, water_saved_liters:75000, power_saved_kwh:15000,
                  eco_badges:'Passive Solar, EV Transfers, Zero Plastic',
                  accessibility_tags:'Sign Language Staff, Step-Free Access, Audio Maps' }
            ],
            goa: [
                { id:'m-goa-1', name:'Coral Coast Eco Resort', destination_name:'Goa',
                  description:'Beachfront property powered by wind turbines and solar panels. Runs an active coral reef restoration programme. Floating pontoon with wheelchair ramp and pool hoist.',
                  image_url:'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:6200, water_saved_liters:210000, power_saved_kwh:55000,
                  eco_badges:'Wind & Solar, Coral Restoration, Zero Plastic Beach',
                  accessibility_tags:'Wheelchair Ramp, Pool Hoist, Tactile Beach Path' },
                { id:'m-goa-2', name:'Spice Grove Retreat', destination_name:'Goa',
                  description:'Nestled in a working spice plantation with clay-walled cottages, greywater recycling, and open-air yoga platforms. Hearing loop systems in all meeting spaces.',
                  image_url:'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:4100, water_saved_liters:145000, power_saved_kwh:31000,
                  eco_badges:'Greywater Recycling, Natural Build, Organic Spice Farm',
                  accessibility_tags:'Hearing Loop, Sensory Garden, Wide Pathways' },
                { id:'m-goa-3', name:'Mangrove Haven Bungalows', destination_name:'Goa',
                  description:'Elevated bungalows on stilts within a protected mangrove forest. Paddle-powered kayak canals. Braille nature trail maps and audio guide devices for every guest.',
                  image_url:'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:5100, water_saved_liters:88000, power_saved_kwh:22000,
                  eco_badges:'Mangrove Protected, Low Impact, Nature Immersive',
                  accessibility_tags:'Braille Nature Trail, Audio Devices, Service Dog Friendly' },
                { id:'m-goa-4', name:'Salcette Solar Villas', destination_name:'Goa',
                  description:'Portuguese-colonial architecture with 100% solar microgrids and on-site water filtration. Accessible pool, roll-in shower, and EV charging for guest vehicles.',
                  image_url:'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:4800, water_saved_liters:130000, power_saved_kwh:48000,
                  eco_badges:'Solar Microgrid, Water Filtration, EV Charging',
                  accessibility_tags:'Roll-in Shower, Accessible Pool, Step-Free Villas' }
            ],
            manali: [
                { id:'m-manali-1', name:'Alpine Solar Lodge', destination_name:'Manali',
                  description:'At 2,050m altitude, this off-grid lodge uses roof-integrated solar panels and a biomass boiler fuelled by pine waste. Heated accessible rooms and adjustable-height beds.',
                  image_url:'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:5200, water_saved_liters:95000, power_saved_kwh:38000,
                  eco_badges:'Off-Grid Solar, Biomass Boiler, Zero Emissions',
                  accessibility_tags:'Heated Accessible Rooms, Adjustable Beds, Step-Free Entry' },
                { id:'m-manali-2', name:'Beas River Eco Camp', destination_name:'Manali',
                  description:'Glamping tents with solar fairy lights and composting dry toilets beside the Beas river. Quiet meditation dome and service animals always welcome.',
                  image_url:'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:2800, water_saved_liters:42000, power_saved_kwh:11000,
                  eco_badges:'Glamping, Composting Toilets, Solar Lights',
                  accessibility_tags:'Service Animal Welcome, Sensory Quiet Dome, Level Access' },
                { id:'m-manali-3', name:'Himalayan Deodar Retreat', destination_name:'Manali',
                  description:'Sustainably-harvested deodar cedar with passive solar heating. Traditional Kullu architecture meets modern accessibility — Braille floor numbers and wide doorways throughout.',
                  image_url:'https://images.unsplash.com/photo-1519659528534-7fd733a832a0?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:4400, water_saved_liters:78000, power_saved_kwh:26000,
                  eco_badges:'Sustainable Timber, Passive Solar, Low Waste',
                  accessibility_tags:'Braille Floor Numbers, Wide Doorways, Accessible Bathroom' },
                { id:'m-manali-4', name:'Solang Valley Green House', destination_name:'Manali',
                  description:'Glass-wall greenhouse rooms with integrated herb gardens and mountain panoramas. Greywater recycling and EV shuttle from Manali town. Hearing loops and tactile maps.',
                  image_url:'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:3600, water_saved_liters:62000, power_saved_kwh:18000,
                  eco_badges:'Greenhouse Design, Herb Garden, EV Shuttle',
                  accessibility_tags:'Hearing Loop, Tactile Maps, Step-Free Paths' }
            ],
            mumbai: [
                { id:'m-mumbai-1', name:'Marine Drive EcoSuites', destination_name:'Mumbai',
                  description:'LEED Platinum seafront suites with tidal energy supplement and rooftop solar. Universally designed rooms with adjustable counters, Braille lifts, and visual fire alarms.',
                  image_url:'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:7800, water_saved_liters:250000, power_saved_kwh:72000,
                  eco_badges:'LEED Platinum, Tidal Energy, Rooftop Solar',
                  accessibility_tags:'Universal Design, Braille Lifts, Visual Fire Alarms' },
                { id:'m-mumbai-2', name:'Dharavi Upcycle Hostel', destination_name:'Mumbai',
                  description:'Community-owned hostel using upcycled materials from Dharavi\'s creative industries. 100% renewable energy and on-site composting. Wheelchair access throughout.',
                  image_url:'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:1800, water_saved_liters:85000, power_saved_kwh:21000,
                  eco_badges:'Upcycled Build, 100% Renewable, Community Owned',
                  accessibility_tags:'Wheelchair Throughout, Audio Navigation, Service Dogs' },
                { id:'m-mumbai-3', name:'Bandra Green Loft', destination_name:'Mumbai',
                  description:'Stylish vertical garden loft with rain-harvesting, greywater recycling, and a rooftop food forest. Sensory-friendly design with dimmable lighting and noise-dampening walls.',
                  image_url:'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:5500, water_saved_liters:110000, power_saved_kwh:33000,
                  eco_badges:'Vertical Garden, Rain Harvesting, Food Forest',
                  accessibility_tags:'Sensory Friendly, Dimmable Lighting, Sound Dampening' },
                { id:'m-mumbai-4', name:'Gateway Solar Inn', destination_name:'Mumbai',
                  description:'Heritage colonial building retrofitted with floating solar panels on the adjacent dock. Certified barrier-free with pool lift, sign-language concierge, and Braille room cards.',
                  image_url:'https://images.unsplash.com/photo-1551918120-9739cb430c6d?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:6100, water_saved_liters:190000, power_saved_kwh:58000,
                  eco_badges:'Floating Solar, Heritage Retrofit, Zero Plastic',
                  accessibility_tags:'Barrier-Free Certified, Pool Lift, Sign Language Staff' }
            ],
            munnar: [
                { id:'m-munnar-1', name:'Tea Hills Eco Bungalow', destination_name:'Munnar',
                  description:'Perched at 1,800m in a working tea estate with micro-hydro power from the estate stream. Wake to mist-covered valleys. Accessible tea-tasting rooms with audio descriptions.',
                  image_url:'https://images.unsplash.com/photo-1600298881974-6be191ceeda1?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:4700, water_saved_liters:140000, power_saved_kwh:35000,
                  eco_badges:'Micro-Hydro Power, Tea Estate, Zero Plastic',
                  accessibility_tags:'Accessible Tea Rooms, Audio Descriptions, Step-Free' },
                { id:'m-munnar-2', name:'Cardamom Forest Lodge', destination_name:'Munnar',
                  description:'Treehouses built on living platforms in a cardamom forest. Solar-powered with composting waste system and on-site permaculture garden. Wide decks with ramp access.',
                  image_url:'https://images.unsplash.com/photo-1552733407-5d5c46c3bb3b?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:3800, water_saved_liters:65000, power_saved_kwh:17000,
                  eco_badges:'Solar Treehouse, Permaculture, Composting',
                  accessibility_tags:'Ramp Access Decks, Wide Pathways, Service Dogs Welcome' },
                { id:'m-munnar-3', name:'Eravikulam Green Camp', destination_name:'Munnar',
                  description:'Eco-camp adjacent to Eravikulam National Park with canvas lodges and solar showers. All-ability trail with tactile maps and audio commentary devices for every guest.',
                  image_url:'https://images.unsplash.com/photo-1533240332313-0db49b459ad6?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:2900, water_saved_liters:48000, power_saved_kwh:12000,
                  eco_badges:'National Park Adjacent, Solar Showers, Low Impact',
                  accessibility_tags:'All-Ability Trail, Tactile Maps, Audio Devices' },
                { id:'m-munnar-4', name:'Mattupetty Lake Retreat', destination_name:'Munnar',
                  description:'Lakeside cottages with floating solar panels and bio-sand water filtration. Heritage electric catamaran tours. Wheelchair-friendly jetty and accessible boat ramps.',
                  image_url:'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:3400, water_saved_liters:92000, power_saved_kwh:24000,
                  eco_badges:'Floating Solar, Bio-Sand Filter, Electric Boats',
                  accessibility_tags:'Wheelchair Jetty, Boat Ramps, Hearing Loop' }
            ],
            jaipur: [
                { id:'m-jaipur-1', name:'Pink City Solar Haveli', destination_name:'Jaipur',
                  description:'A restored 18th-century haveli in the walled city, fully solar-powered with traditional lime-plaster cooling. Tactile courtyard maps and Braille menus in the rooftop restaurant.',
                  image_url:'https://images.unsplash.com/photo-1603262110263-fb0112e7cc33?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:5400, water_saved_liters:165000, power_saved_kwh:44000,
                  eco_badges:'Solar Powered, Heritage Lime Plaster, Zero Plastic',
                  accessibility_tags:'Tactile Courtyard Maps, Braille Menus, Lift Access' },
                { id:'m-jaipur-2', name:'Aravalli Forest Camp', destination_name:'Jaipur',
                  description:'Luxury glamping at the foot of the Aravalli hills with biodegradable camp furniture and solar lighting. Inclusive astronomy nights with audio star-gazing descriptions.',
                  image_url:'https://images.unsplash.com/photo-1537225228614-56cc3556d7ed?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:3100, water_saved_liters:52000, power_saved_kwh:14000,
                  eco_badges:'Biodegradable Furniture, Solar Glamping, Low Impact',
                  accessibility_tags:'Audio Star-Gazing, Level Access Tents, Service Dogs' },
                { id:'m-jaipur-3', name:'Jal Mahal Eco Inn', destination_name:'Jaipur',
                  description:'Boutique inn with lake views of Jal Mahal, greywater recycling, and a rainwater garden courtyard. Sensory room for guests with autism and hearing loop throughout.',
                  image_url:'https://images.unsplash.com/photo-1477587458883-47145ed31955?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:4200, water_saved_liters:118000, power_saved_kwh:29000,
                  eco_badges:'Greywater Recycled, Rainwater Garden, Solar AC',
                  accessibility_tags:'Sensory Room, Hearing Loop, Wide Corridors' },
                { id:'m-jaipur-4', name:'Amber Fort Green Villas', destination_name:'Jaipur',
                  description:'Private villas overlooking Amber Fort with rooftop solar and organic vegetable gardens. EV jeep safaris available. Sign-language interpreters for fort tours on request.',
                  image_url:'https://images.unsplash.com/photo-1598091383021-15ddea10925d?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:6300, water_saved_liters:175000, power_saved_kwh:51000,
                  eco_badges:'Rooftop Solar, Organic Gardens, EV Safaris',
                  accessibility_tags:'Sign Language Tours, Step-Free Villas, Pool Lift' }
            ],
            rishikesh: [
                { id:'m-rishi-1', name:'Ganga Green Ashram Stay', destination_name:'Rishikesh',
                  description:'Riverside ashram-inspired eco-lodge on micro-hydro and solar, with daily yoga sessions. All-ability yoga classes with mat guides designed for visually impaired guests.',
                  image_url:'https://images.unsplash.com/photo-1621996659490-3275b4d0d951?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.9', price_per_night:3300, water_saved_liters:88000, power_saved_kwh:26000,
                  eco_badges:'Micro-Hydro, Solar, Zero Meat Menu',
                  accessibility_tags:'All-Ability Yoga, Mat Guides, Step-Free Ghat Access' },
                { id:'m-rishi-2', name:'Laxman Jhula Bamboo Resort', destination_name:'Rishikesh',
                  description:'Built entirely from locally-sourced bamboo and natural fibre, with solar water heating and biogas cooking. Inclusive white-water rafting with adaptive equipment.',
                  image_url:'https://images.unsplash.com/photo-1535913989690-f90e1c2d4cfa?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:2700, water_saved_liters:56000, power_saved_kwh:16000,
                  eco_badges:'Bamboo Build, Biogas Cooking, Solar Water',
                  accessibility_tags:'Adaptive Rafting, Service Dogs, Wide Pathways' },
                { id:'m-rishi-3', name:'Forest Canopy Tree Rooms', destination_name:'Rishikesh',
                  description:'Canopy-level rooms in the Rajaji buffer zone with assisted zip-line and rope bridge access. Night safaris with audio nature commentary for visually impaired guests.',
                  image_url:'https://images.unsplash.com/photo-1528183429752-a97d0bf99b5a?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:4600, water_saved_liters:71000, power_saved_kwh:20000,
                  eco_badges:'Buffer Zone Adjacent, Low Footprint, Night Safaris',
                  accessibility_tags:'Assisted Zip-line, Audio Commentary, Sensory Calm Areas' },
                { id:'m-rishi-4', name:'Neelkanth Organic Cottages', destination_name:'Rishikesh',
                  description:'Organic farm cottages with permaculture design, biogas from cattle waste, and spring-fed water. Ayurvedic wellness retreat with accessible treatment rooms.',
                  image_url:'https://images.unsplash.com/photo-1601918774516-9ed9e6deba4e?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:2400, water_saved_liters:45000, power_saved_kwh:12000,
                  eco_badges:'Permaculture Farm, Biogas, Spring Water',
                  accessibility_tags:'Accessible Treatment Rooms, Sign Language Therapists' }
            ],
            varanasi: [
                { id:'m-vns-1', name:'Ghat View Solar Guesthouse', destination_name:'Varanasi',
                  description:'Traditional haveli on the Assi Ghat with 100% rooftop solar and rainwater collection. Morning aarti views from the terrace. Audio description devices for the ghat experience.',
                  image_url:'https://images.unsplash.com/photo-1561361058-c24e1f74b7b3?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.8', price_per_night:3700, water_saved_liters:98000, power_saved_kwh:30000,
                  eco_badges:'Solar Haveli, Rainwater Collection, Heritage Build',
                  accessibility_tags:'Audio Ghat Experience, Braille Room Numbers, Step-Free' },
                { id:'m-vns-2', name:'Kashi Bamboo Boutique', destination_name:'Varanasi',
                  description:'Boutique property built with Kashi brick and bamboo panels. Plant-based cuisine, composting kitchen waste, and solar courtyard lighting. Hearing loop in the music hall.',
                  image_url:'https://images.unsplash.com/photo-1604608672516-f1b9b9e82e3d?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:2800, water_saved_liters:67000, power_saved_kwh:19000,
                  eco_badges:'Bamboo Build, Plant-Based Menu, Solar Courtyard',
                  accessibility_tags:'Hearing Loop, Wide Corridors, Service Dog Friendly' },
                { id:'m-vns-3', name:'Banaras Silk Farm Stay', destination_name:'Varanasi',
                  description:'Silk weaving village homestay on solar power with natural dye gardens and handloom workshops. Tactile silk-weaving experience designed for visually impaired guests.',
                  image_url:'https://images.unsplash.com/photo-1570135460919-9abb51b1bd23?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.6', price_per_night:1900, water_saved_liters:38000, power_saved_kwh:10000,
                  eco_badges:'Solar Powered, Natural Dyes, Handloom Village',
                  accessibility_tags:'Tactile Weaving, Audio Workshop Tour, Level Access' },
                { id:'m-vns-4', name:'Sarnath Lotus Eco Inn', destination_name:'Varanasi',
                  description:'Near Sarnath deer park with passive cooling, a lotus pond for greywater filtration, and organic kitchen produce. Sensory-friendly Buddhist meditation sessions.',
                  image_url:'https://images.unsplash.com/photo-1604608672516-f1b9b9e82e3d?auto=format&fit=crop&w=800&q=80',
                  eco_rating:'4.7', price_per_night:2500, water_saved_liters:55000, power_saved_kwh:14000,
                  eco_badges:'Passive Cooling, Lotus Pond Filter, Organic Kitchen',
                  accessibility_tags:'Sensory Meditation, Accessible Garden, Step-Free' }
            ]
        };

        // ── Accessibility filter keyword map ──────────────────────────────
        const FILTER_MAP = {
            wheelchair: ['wheelchair','step-free','ramp','pool lift','roll-in','barrier-free','universal design','accessible'],
            sensory:    ['sensory','quiet','low stimulation','dimmable','sound damp','calm','autism'],
            braille:    ['braille','audio','tactile','visual','hearing loop','sign language'],
            dog:        ['service dog','service animal','dog friendly']
        };

        function hotelMatchesFilter(hotel, filter) {
            if (filter === 'all') return true;
            const keywords = FILTER_MAP[filter] || [];
            const haystack = ((hotel.accessibility_tags || '') + ' ' + (hotel.eco_badges || '')).toLowerCase();
            return keywords.some(kw => haystack.includes(kw));
        }

        // ── Single card HTML builder (structure identical to API renderer) ─
        function buildCardHTML(h) {
            const badges = (h.eco_badges || 'Solar Powered, Zero Plastic')
                .split(',').map(b => `<span class="tag-badge">🌱 ${b.trim()}</span>`).join('');
            const access = (h.accessibility_tags || 'Wheelchair Friendly')
                .split(',').map(a => `<span class="tag-badge tag-access">♿ ${a.trim()}</span>`).join('');
            const checkoutParams = new URLSearchParams({
                hotel_id: h.id, hotel_name: h.name, dest_name: h.destination_name,
                origin: originCity, mode: travelMode, distance: travelDistance,
                co2_saved: carbonAvoided, price: h.price_per_night || 3200,
                water_saved: h.water_saved_liters || 100000, power_saved: h.power_saved_kwh || 25000
            });
            return `
                <div class="stay-card">
                    <div class="stay-img-box">
                        <img src="${h.image_url || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'}"
                             alt="${h.name}" loading="lazy"
                             onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                        <div class="stay-rating">★ ${h.eco_rating || '4.8'}</div>
                    </div>
                    <div class="stay-body">
                        <div class="stay-location">📍 ${h.destination_name}</div>
                        <h3 class="stay-title">${h.name}</h3>
                        <p class="stay-desc">${h.description}</p>
                        <div class="stay-metrics-banner">
                            <span>💧 ${(h.water_saved_liters || 0).toLocaleString()}L Water Saved/yr</span>
                            <span>⚡ ${(h.power_saved_kwh || 0).toLocaleString()} kWh Solar</span>
                        </div>
                        <div class="stay-badges-row">${badges}${access}</div>
                        <div class="stay-card-footer">
                            <div class="price-text">
                                <span class="amount">₹${(h.price_per_night || 3200).toLocaleString()}</span>
                                <span>/ night</span>
                            </div>
                            <a href="checkout.php?${checkoutParams.toString()}" class="btn-select-stay">
                                View &amp; Book Stay ➔
                            </a>
                        </div>
                    </div>
                </div>`;
        }

        // ── Mock renderer — checks strictly lowercase keys in MOCK_DB ────
        function renderMockCards(destName, filter) {
            const container = document.getElementById('staysContainer');
            if (!container) return;

            const destKey = (destName || '').toLowerCase().trim();

            // Check if destination key exists in database
            if (MOCK_DB[destKey]) {
                const hotels  = MOCK_DB[destKey];
                const filtered = hotels.filter(h => hotelMatchesFilter(h, filter || 'all'));

                if (filtered.length === 0) {
                    container.innerHTML = `
                        <div style="text-align:center;grid-column:1/-1;padding:3.5rem 2rem;background:rgba(255,255,255,0.85);border-radius:24px;border:1.5px dashed rgba(34,197,94,0.3);">
                            <div style="font-size:2.5rem;margin-bottom:8px;">🌿</div>
                            <h3 style="color:var(--primary);font-size:1.3rem;">No certified properties found for this filter</h3>
                            <p style="color:var(--text-muted);font-size:0.92rem;margin-top:6px;">Try switching to <strong>"All Eco-Stays"</strong> or select another destination above.</p>
                        </div>`;
                    return;
                }
                container.innerHTML = filtered.map(buildCardHTML).join('');
                if (typeof init3DTilt === 'function') init3DTilt();
                if (typeof initScrollReveal === 'function') initScrollReveal();
            } else {
                // Exact fallback message required when destination is not found in database
                container.innerHTML = `
                    <div style="text-align:center;grid-column:1/-1;padding:3.5rem 2rem;background:rgba(255,255,255,0.85);border-radius:24px;border:1.5px dashed rgba(34,197,94,0.3);">
                        <div style="font-size:2.5rem;margin-bottom:8px;">🌿</div>
                        <h3 style="color:var(--primary);font-size:1.3rem;">No verified eco-stays found for this location yet.</h3>
                        <p style="color:var(--text-muted);font-size:0.92rem;margin-top:6px;">We are actively auditing and certifying sustainable sanctuaries in this region.</p>
                    </div>`;
            }
        }

        // ── Main loader: real API → mock fallback ─────────────────────────
        async function loadStays() {
            const container = document.getElementById('staysContainer');
            if (!container) return;
            container.innerHTML = '<div style="text-align:center;grid-column:1/-1;padding:3rem;color:var(--text-muted);font-size:1.05rem;">🌱 Loading verified eco-stays for this sanctuary...</div>';
            const destForMock = (currentDestName || 'manali').toLowerCase().trim();

            try {
                const res  = await fetch(`api/get_hotels.php?destination_id=${currentDestId}&filter=${encodeURIComponent(currentFilter)}`);
                const data = await res.json();

                if (data.status === 'success' && data.hotels && data.hotels.length > 0) {
                    // Real DB data available — render it
                    container.innerHTML = data.hotels.map(h => {
                        const badges = (h.eco_badges || 'Solar Powered, Zero Plastic').split(',').map(b => `<span class="tag-badge">🌱 ${b.trim()}</span>`).join('');
                        const access = (h.accessibility_tags || 'Wheelchair Friendly').split(',').map(a => `<span class="tag-badge tag-access">♿ ${a.trim()}</span>`).join('');
                        const params = new URLSearchParams({
                            hotel_id: h.id, hotel_name: h.name, dest_name: h.destination_name,
                            origin: originCity, mode: travelMode, distance: travelDistance,
                            co2_saved: carbonAvoided, price: h.price_per_night || 3200,
                            water_saved: h.water_saved_liters || 100000, power_saved: h.power_saved_kwh || 25000
                        });
                        return `
                            <div class="stay-card">
                                <div class="stay-img-box">
                                    <img src="${h.image_url || 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'}" alt="${h.name}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                                    <div class="stay-rating">★ ${h.eco_rating || '4.8'}</div>
                                </div>
                                <div class="stay-body">
                                    <div class="stay-location">📍 ${h.destination_name}</div>
                                    <h3 class="stay-title">${h.name}</h3>
                                    <p class="stay-desc">${h.description}</p>
                                    <div class="stay-metrics-banner">
                                        <span>💧 ${(h.water_saved_liters||0).toLocaleString()}L Water Saved/yr</span>
                                        <span>⚡ ${(h.power_saved_kwh||0).toLocaleString()} kWh Solar</span>
                                    </div>
                                    <div class="stay-badges-row">${badges}${access}</div>
                                    <div class="stay-card-footer">
                                        <div class="price-text">
                                            <span class="amount">₹${(h.price_per_night||3200).toLocaleString()}</span>
                                            <span>/ night</span>
                                        </div>
                                        <a href="checkout.php?${params.toString()}" class="btn-select-stay">View &amp; Book Stay ➔</a>
                                    </div>
                                </div>
                            </div>`;
                    }).join('');
                    if (typeof init3DTilt === 'function') init3DTilt();
                    if (typeof initScrollReveal === 'function') initScrollReveal();
                } else {
                    renderMockCards(destForMock, currentFilter);
                }
            } catch (_err) {
                // Network / API unavailable — fallback to mock cards
                renderMockCards(destForMock, currentFilter);
            }
        }

        // ── Dropdown handler ──────────────────────────────────────────────
        function changeDestination(newDestId) {
            currentDestId = parseInt(newDestId);
            const select   = document.getElementById('destinationFilterSelect');
            const destName = select.selectedOptions[0].getAttribute('data-name') || select.selectedOptions[0].text.replace(/^📍\s*/, '').trim();
            currentDestName = destName.toLowerCase().trim();

            const label = document.getElementById('currentDestLabel');
            if (label) label.textContent = destName;

            const url = new URL(window.location);
            url.searchParams.set('dest_id',   currentDestId);
            url.searchParams.set('dest_name', destName);
            window.history.replaceState({}, '', url);

            loadStays();
        }

        function applyFilter(filter, btn) {
            currentFilter = filter;
            document.querySelectorAll('.pill-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            loadStays();
        }

        // ── DOMContentLoaded: Data Receiver from homepage ────────────────
        window.addEventListener('DOMContentLoaded', () => {
            // Retrieve destination from localStorage using key 'way2green_dest'
            const dest = localStorage.getItem('way2green_dest') || localStorage.getItem('w2g_dest') || 'manali';
            const origin = localStorage.getItem('way2green_origin') || localStorage.getItem('w2g_origin') || 'New Delhi';

            // Console log for developer verification
            console.log("Received Destination:", dest);

            originCity      = origin;
            currentDestName = dest;

            // Update route-bar origin & destination labels
            const routeOriginEl = document.getElementById('route-origin-text');
            if (routeOriginEl) routeOriginEl.textContent = origin.charAt(0).toUpperCase() + origin.slice(1);

            const destLabel = document.getElementById('currentDestLabel');
            if (destLabel) destLabel.textContent = dest.charAt(0).toUpperCase() + dest.slice(1);

            // Sync dropdown if option matches
            const destSelect = document.getElementById('destinationFilterSelect');
            if (destSelect) {
                const searchLower = dest.toLowerCase().trim();
                Array.from(destSelect.options).forEach(opt => {
                    const optName = (opt.getAttribute('data-name') || opt.text).replace(/^📍\s*/, '').toLowerCase().trim();
                    if (optName.includes(searchLower) || searchLower.includes(optName)) {
                        currentDestId = parseInt(opt.value);
                        opt.selected = true;
                        if (destLabel) destLabel.textContent = opt.getAttribute('data-name') || opt.text.replace(/^📍\s*/, '').trim();
                    }
                });
            }

            // Load verified eco-stays (checks MOCK_DB lowercase keys)
            loadStays();
        });
    </script>
    <script src="js/effects.js"></script>
</body>
</html>
