<?php
// eco-stays.php - Way2Green: Phase 2 Verified Eco-Stays & Inclusive Sanctuaries
if (file_exists('db.php')) {
    @include_once 'db.php';
}
if (file_exists('user_auth.php')) {
    @include_once 'user_auth.php';
}
$user = function_exists('get_logged_in_user') ? get_logged_in_user() : null;

// ================================================================
// PHP Initialization: Safely capture GET parameters
// ================================================================
$origin = htmlspecialchars($_GET['origin'] ?? 'New Delhi');
$dest   = strtolower(htmlspecialchars($_GET['dest'] ?? 'manali'));

// Capitalized display versions
$originDisplay = ucwords(strtolower(trim($origin)));
$destDisplay   = ucwords(strtolower(trim($dest)));

// ================================================================
// PHP Mock Database: Verified Eco-Hotels mapped to lowercase keys
// ================================================================
$hotelDatabase = [
    'agra' => [
        [
            'id' => 'agra-1',
            'name' => 'Taj Eco Sanctuary',
            'destination_name' => 'Agra, Uttar Pradesh',
            'description' => 'Steps from the Taj Mahal, this certified zero-waste heritage retreat runs entirely on rooftop solar and harvested rainwater. Tactile path guides and Braille menus throughout.',
            'image_url' => 'https://images.unsplash.com/photo-1548013146-72479768bada?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 5800,
            'water_saved' => 180000,
            'power_saved' => 42000,
            'eco_badges' => ['Solar Powered', 'Zero Waste', 'Rainwater Harvesting'],
            'accessibility_tags' => ['Wheelchair Friendly', 'Braille Menus', 'Audio Guides']
        ],
        [
            'id' => 'agra-2',
            'name' => 'Yamuna Green Lodge',
            'destination_name' => 'Agra, Uttar Pradesh',
            'description' => 'A serene riverside eco-lodge with organic gardens and composting toilets. Roll-in showers, pool lift, and sensory-calm zones for travelers with cognitive sensitivities.',
            'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.7',
            'price' => 3900,
            'water_saved' => 120000,
            'power_saved' => 28000,
            'eco_badges' => ['Organic Garden', 'Composting', 'Low Carbon'],
            'accessibility_tags' => ['Roll-in Showers', 'Pool Lift', 'Sensory Calm Zones']
        ],
        [
            'id' => 'agra-3',
            'name' => 'Heritage Biome Retreat',
            'destination_name' => 'Agra, Uttar Pradesh',
            'description' => 'Constructed from reclaimed Mughal stone, this boutique property uses biogas kitchen energy, provides service dog facilities, and maintains barrier-free step-less corridors.',
            'image_url' => 'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.8',
            'price' => 4500,
            'water_saved' => 95000,
            'power_saved' => 19000,
            'eco_badges' => ['Biogas Kitchen', 'Reclaimed Stone', 'Rooftop Herbs'],
            'accessibility_tags' => ['Service Dog Friendly', 'Accessible Staff', 'Step-Free']
        ],
        [
            'id' => 'agra-4',
            'name' => 'Lotus Pond Eco Haveli',
            'destination_name' => 'Agra, Uttar Pradesh',
            'description' => 'A 200-year-old haveli restored with natural lime plaster and passive solar thermal airflow. Zero single-use plastic, silent EV transfers, and sign-language concierge.',
            'image_url' => 'https://images.unsplash.com/photo-1529290130-4ca3753253ae?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.6',
            'price' => 3200,
            'water_saved' => 75000,
            'power_saved' => 15000,
            'eco_badges' => ['Passive Solar', 'EV Transfers', 'Zero Plastic'],
            'accessibility_tags' => ['Sign Language Staff', 'Step-Free Access', 'Audio Maps']
        ]
    ],
    'goa' => [
        [
            'id' => 'goa-1',
            'name' => 'Coral Coast Eco Resort',
            'destination_name' => 'Goa (Eco-Coast)',
            'description' => 'Beachfront sanctuary powered by micro-wind turbines and solar panels. Runs coral restoration tours. Features a floating pontoon with wheelchair ramp and pool hoist.',
            'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 6200,
            'water_saved' => 210000,
            'power_saved' => 55000,
            'eco_badges' => ['Wind & Solar', 'Coral Restoration', 'Zero Plastic Beach'],
            'accessibility_tags' => ['Wheelchair Ramp', 'Pool Hoist', 'Tactile Beach Path']
        ],
        [
            'id' => 'goa-2',
            'name' => 'Spice Grove Retreat',
            'destination_name' => 'Goa (Ponda Hinterlands)',
            'description' => 'Nestled in a working spice plantation with clay-walled cottages, greywater recycling, and open-air yoga decks. Hearing loop systems in all guest pavilions.',
            'image_url' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.7',
            'price' => 4100,
            'water_saved' => 145000,
            'power_saved' => 31000,
            'eco_badges' => ['Greywater Recycling', 'Natural Earth Build', 'Organic Farm'],
            'accessibility_tags' => ['Hearing Loop', 'Sensory Garden', 'Wide Pathways']
        ],
        [
            'id' => 'goa-3',
            'name' => 'Mangrove Haven Bungalows',
            'destination_name' => 'Goa (Mandovi Estuary)',
            'description' => 'Elevated bungalows on stilts within a protected mangrove estuary. Solar water heaters, Braille nature trail guides, and certified service animal amenities.',
            'image_url' => 'https://images.unsplash.com/photo-1571003123894-1f0594d2b5d9?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.8',
            'price' => 5100,
            'water_saved' => 88000,
            'power_saved' => 22000,
            'eco_badges' => ['Mangrove Protected', 'Low Footprint', 'Silent Kayaking'],
            'accessibility_tags' => ['Braille Nature Trail', 'Audio Devices', 'Service Dogs Welcome']
        ],
        [
            'id' => 'goa-4',
            'name' => 'Salcette Solar Villas',
            'destination_name' => 'Goa (South Goa)',
            'description' => 'Portuguese-colonial architecture paired with 100% solar microgrids and on-site UV water filtration. Accessible pool, roll-in shower, and EV fast charging.',
            'image_url' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.6',
            'price' => 4800,
            'water_saved' => 130000,
            'power_saved' => 48000,
            'eco_badges' => ['Solar Microgrid', 'Water Filtration', 'EV Charging'],
            'accessibility_tags' => ['Roll-in Shower', 'Accessible Pool', 'Step-Free Villas']
        ]
    ],
    'mumbai' => [
        [
            'id' => 'mumbai-1',
            'name' => 'Marine Drive EcoSuites',
            'destination_name' => 'Mumbai, Maharashtra',
            'description' => 'LEED Platinum seafront suites with rooftop solar and tidal energy supplement. Universal design rooms with Braille lifts, tactile maps, and visual fire alarms.',
            'image_url' => 'https://images.unsplash.com/photo-1595658658481-d53d3f999875?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 7800,
            'water_saved' => 250000,
            'power_saved' => 72000,
            'eco_badges' => ['LEED Platinum', 'Tidal Energy', 'Rooftop Solar'],
            'accessibility_tags' => ['Universal Design', 'Braille Lifts', 'Visual Fire Alarms']
        ],
        [
            'id' => 'mumbai-2',
            'name' => 'Dharavi Upcycle Hostel',
            'destination_name' => 'Mumbai, Maharashtra',
            'description' => 'Community-owned eco-stay crafted from upcycled industrial materials. 100% renewable energy, on-site composters, and complete wheelchair accessibility throughout.',
            'image_url' => 'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.7',
            'price' => 1800,
            'water_saved' => 85000,
            'power_saved' => 21000,
            'eco_badges' => ['Upcycled Build', '100% Renewable', 'Community Owned'],
            'accessibility_tags' => ['Wheelchair Throughout', 'Audio Navigation', 'Service Dogs']
        ],
        [
            'id' => 'mumbai-3',
            'name' => 'Bandra Green Loft',
            'destination_name' => 'Mumbai, Maharashtra',
            'description' => 'Stylish vertical garden loft in Bandra with rainwater harvesting, greywater recycling, and sensory-friendly acoustic insulation for quiet relaxation.',
            'image_url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.8',
            'price' => 5500,
            'water_saved' => 110000,
            'power_saved' => 33000,
            'eco_badges' => ['Vertical Garden', 'Rain Harvesting', 'Food Forest'],
            'accessibility_tags' => ['Sensory Friendly', 'Dimmable Lighting', 'Sound Dampening']
        ],
        [
            'id' => 'mumbai-4',
            'name' => 'Gateway Solar Inn',
            'destination_name' => 'Mumbai, Maharashtra',
            'description' => 'Heritage colonial structure retrofitted with floating solar panels. Certified barrier-free with pool lift, sign-language concierge, and Braille room cards.',
            'image_url' => 'https://images.unsplash.com/photo-1551918120-9739cb430c6d?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.7',
            'price' => 6100,
            'water_saved' => 190000,
            'power_saved' => 58000,
            'eco_badges' => ['Floating Solar', 'Heritage Retrofit', 'Zero Plastic'],
            'accessibility_tags' => ['Barrier-Free Certified', 'Pool Lift', 'Sign Language Staff']
        ]
    ],
    'manali' => [
        [
            'id' => 'manali-1',
            'name' => 'Alpine Solar Lodge',
            'destination_name' => 'Manali, Himachal Pradesh',
            'description' => 'At 2,050m altitude, this off-grid mountain lodge runs on roof-integrated solar panels and a biomass boiler fuelled by pine needles. Heated step-free accessible rooms.',
            'image_url' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 5200,
            'water_saved' => 95000,
            'power_saved' => 38000,
            'eco_badges' => ['Off-Grid Solar', 'Biomass Boiler', 'Zero Emissions'],
            'accessibility_tags' => ['Heated Accessible Rooms', 'Adjustable Beds', 'Step-Free Entry']
        ],
        [
            'id' => 'manali-2',
            'name' => 'Beas River Eco Camp',
            'destination_name' => 'Manali, Himachal Pradesh',
            'description' => 'Riverside glamping with solar fairy lights, dry composting toilets, and organic community meals beside the Beas river. Quiet sensory meditation dome on-site.',
            'image_url' => 'https://images.unsplash.com/photo-1504280390367-361c6d9f38f4?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.7',
            'price' => 2800,
            'water_saved' => 42000,
            'power_saved' => 11000,
            'eco_badges' => ['Glamping', 'Composting Toilets', 'Solar Lights'],
            'accessibility_tags' => ['Service Animal Welcome', 'Sensory Quiet Dome', 'Level Access']
        ],
        [
            'id' => 'manali-3',
            'name' => 'Himalayan Deodar Retreat',
            'destination_name' => 'Manali, Himachal Pradesh',
            'description' => 'Crafted from sustainable deodar cedar with passive solar heat walls. Traditional Kullu craft meets modern barrier-free design with wide doorways and Braille floor tags.',
            'image_url' => 'https://images.unsplash.com/photo-1519659528534-7fd733a832a0?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.8',
            'price' => 4400,
            'water_saved' => 78000,
            'power_saved' => 26000,
            'eco_badges' => ['Sustainable Timber', 'Passive Solar', 'Low Waste'],
            'accessibility_tags' => ['Braille Floor Numbers', 'Wide Doorways', 'Accessible Bathroom']
        ],
        [
            'id' => 'manali-4',
            'name' => 'Solang Valley Green House',
            'destination_name' => 'Manali, Himachal Pradesh',
            'description' => 'Glasshouse chalets with indoor herbal heat-exchangers, panoramic glacier vistas, greywater recycling, and wheelchair-accessible mountain garden ramps.',
            'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.6',
            'price' => 3600,
            'water_saved' => 62000,
            'power_saved' => 18000,
            'eco_badges' => ['Greenhouse Design', 'Herb Garden', 'EV Shuttle'],
            'accessibility_tags' => ['Hearing Loop', 'Tactile Maps', 'Step-Free Paths']
        ]
    ],
    'munnar' => [
        [
            'id' => 'munnar-1',
            'name' => 'Tea Hills Eco Bungalow',
            'destination_name' => 'Munnar, Kerala',
            'description' => 'Perched at 1,800m in an organic tea estate powered by stream micro-hydro. Wake to mist-covered valleys. Accessible tea-tasting pavilion with audio commentary.',
            'image_url' => 'https://images.unsplash.com/photo-1600298881974-6be191ceeda1?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 4700,
            'water_saved' => 140000,
            'power_saved' => 35000,
            'eco_badges' => ['Micro-Hydro Power', 'Tea Estate', 'Zero Plastic'],
            'accessibility_tags' => ['Accessible Tea Rooms', 'Audio Descriptions', 'Step-Free']
        ],
        [
            'id' => 'munnar-2',
            'name' => 'Cardamom Forest Lodge',
            'destination_name' => 'Munnar, Kerala',
            'description' => 'Treehouses built on living platforms in a cardamom forest. Solar electricity, composting waste management, and ramp-accessible forest boardwalks.',
            'image_url' => 'https://images.unsplash.com/photo-1552733407-5d5c46c3bb3b?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.8',
            'price' => 3800,
            'water_saved' => 65000,
            'power_saved' => 17000,
            'eco_badges' => ['Solar Treehouse', 'Permaculture', 'Composting'],
            'accessibility_tags' => ['Ramp Access Decks', 'Wide Pathways', 'Service Dogs Welcome']
        ]
    ],
    'jaipur' => [
        [
            'id' => 'jaipur-1',
            'name' => 'Pink City Solar Haveli',
            'destination_name' => 'Jaipur, Rajasthan',
            'description' => 'Restored 18th-century haveli in the old walled city, fully solar-powered with traditional lime-plaster cooling. Tactile courtyard maps and Braille restaurant menus.',
            'image_url' => 'https://images.unsplash.com/photo-1603262110263-fb0112e7cc33?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 5400,
            'water_saved' => 165000,
            'power_saved' => 44000,
            'eco_badges' => ['Solar Powered', 'Heritage Lime Plaster', 'Zero Plastic'],
            'accessibility_tags' => ['Tactile Courtyard Maps', 'Braille Menus', 'Lift Access']
        ]
    ],
    'rishikesh' => [
        [
            'id' => 'rishikesh-1',
            'name' => 'Ganga Green Ashram Stay',
            'destination_name' => 'Rishikesh, Uttarakhand',
            'description' => 'Riverside eco-retreat running on micro-hydro and solar power. Features all-ability yoga classes with mat guides designed for visually impaired travelers.',
            'image_url' => 'https://images.unsplash.com/photo-1621996659490-3275b4d0d951?auto=format&fit=crop&w=800&q=80',
            'eco_rating' => '4.9',
            'price' => 3300,
            'water_saved' => 88000,
            'power_saved' => 26000,
            'eco_badges' => ['Micro-Hydro', 'Solar Powered', 'Zero Meat Menu'],
            'accessibility_tags' => ['All-Ability Yoga', 'Mat Guides', 'Step-Free Ghat Access']
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verified Eco-Stays for <?= htmlspecialchars($destDisplay) ?> — Way2Green</title>
    <meta name="description" content="Discover verified inclusive eco-lodges with water conservation and accessibility features in <?= htmlspecialchars($destDisplay) ?>.">
    
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

    <?php include 'components/navbar.php'; ?>

    <main class="page-container" style="max-width: 1140px;">
        <!-- Visual Multi-Step Tracker -->
        

        <!-- Roadmap Builder Component -->
        <div class="trip-context-bar" style="flex-direction: column; align-items: stretch; gap: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.5rem;">
                <h2 style="font-size: 1.2rem; color: var(--primary); margin: 0;">🗺️ Your Green Roadmap</h2>
            </div>
            
            <form action="travel.php" method="GET" id="roadmapForm" style="display: flex; flex-direction: column; gap: 10px;">
                <!-- Source -->
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="font-weight: bold; width: 60px; color: var(--text-muted);">Source</div>
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="origin" id="rmOrigin" value="<?= htmlspecialchars($originDisplay) ?>" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                        <button type="button" onclick="openMapPicker('rmOrigin')" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--primary); color: #fff; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Map</button>
                    </div>
                </div>

                <!-- Stops Container -->
                <div id="stopsContainer" style="display: flex; flex-direction: column; gap: 10px;"></div>

                <div style="display: flex; margin-left: 70px;">
                    <button type="button" onclick="addStop()" style="background: transparent; color: var(--primary); border: 1px dashed var(--primary); padding: 6px 12px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: bold;">+ Add Stop</button>
                </div>

                <!-- Destination -->
                <div style="display: flex; gap: 10px; align-items: center;">
                    <div style="font-weight: bold; width: 60px; color: var(--text-muted);">Dest</div>
                    <div style="position: relative; flex: 1;">
                        <input type="text" name="dest" id="rmDest" value="<?= htmlspecialchars($destDisplay) ?>" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                        <button type="button" onclick="openMapPicker('rmDest')" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--primary); color: #fff; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Map</button>
                    </div>
                </div>

                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" style="background: var(--primary); color: #fff; border: none; padding: 10px 20px; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer;">Calculate Transit Route ➔</button>
                </div>
            </form>
        </div>

        <!-- Inclusivity & Accessibility Filters -->
        <div class="section-head reveal-on-scroll" style="margin-bottom: 1.5rem;">
            <h1 class="section-title">Verified Sustainable &amp; Accessible Stays</h1>
            <p class="section-desc">Showing verified sanctuaries for <strong><?= htmlspecialchars($destDisplay) ?></strong> evaluated for zero waste, water conservation, and certified barrier-free accessibility.</p>
        </div>

        <div class="filter-pills-row">
            <button type="button" class="pill-filter active" onclick="filterCards('all', this)">
                All Eco-Stays
            </button>
            <button type="button" class="pill-filter" onclick="filterCards('wheelchair', this)">
                Wheelchair &amp; Step-Free Access
            </button>
            <button type="button" class="pill-filter" onclick="filterCards('sensory', this)">
                Sensory Quiet &amp; Low Stimulation
            </button>
            <button type="button" class="pill-filter" onclick="filterCards('braille', this)">
                Braille &amp; Audio Assisted
            </button>
            <button type="button" class="pill-filter" onclick="filterCards('dog', this)">
                Service Dog Friendly
            </button>
        </div>

        <!-- Dynamic Stays Grid (Rendered via PHP Backend) -->
        <div class="stays-grid" id="staysContainer">
            <?php if (isset($hotelDatabase[$dest]) && !empty($hotelDatabase[$dest])): ?>
                <?php foreach ($hotelDatabase[$dest] as $hotel): ?>
                    <div class="stay-card" data-tags="<?= htmlspecialchars(strtolower(implode(' ', array_merge($hotel['eco_badges'] ?? [], $hotel['accessibility_tags'] ?? [])))) ?>">
                        <div class="stay-img-box">
                            <img src="<?= htmlspecialchars($hotel['image_url']) ?>" alt="<?= htmlspecialchars($hotel['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80'">
                            <div class="stay-rating">★ <?= htmlspecialchars($hotel['eco_rating']) ?></div>
                        </div>
                        <div class="stay-body">
                            <div class="stay-location">📍 <?= htmlspecialchars($hotel['destination_name']) ?></div>
                            <h3 class="stay-title"><?= htmlspecialchars($hotel['name']) ?></h3>
                            <p class="stay-desc"><?= htmlspecialchars($hotel['description']) ?></p>

                            <div class="stay-metrics-banner">
                                <span>💧 <?= number_format($hotel['water_saved']) ?>L Water Saved/yr</span>
                                <span>⚡ <?= number_format($hotel['power_saved']) ?> kWh Solar</span>
                            </div>

                            <div class="stay-badges-row">
                                <?php foreach ($hotel['eco_badges'] as $b): ?>
                                    <span class="tag-badge">🌱 <?= htmlspecialchars($b) ?></span>
                                <?php endforeach; ?>
                                <?php foreach ($hotel['accessibility_tags'] as $a): ?>
                                    <span class="tag-badge tag-access">♿ <?= htmlspecialchars($a) ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div class="stay-card-footer">
                                <div class="price-text">
                                    <span class="amount">₹<?= number_format($hotel['price']) ?></span>
                                    <span>/ night</span>
                                </div>
                                <a href="checkout.php?hotel_id=<?= urlencode($hotel['id']) ?>&hotel_name=<?= urlencode($hotel['name']) ?>&dest_name=<?= urlencode($destDisplay) ?>&origin=<?= urlencode($originDisplay) ?>&price=<?= urlencode($hotel['price']) ?>&water_saved=<?= urlencode($hotel['water_saved']) ?>&power_saved=<?= urlencode($hotel['power_saved']) ?>" class="btn-select-stay">
                                    View &amp; Book Stay ➔
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback Message When Key Doesn't Exist in Database -->
                <div class="no-stays-fallback" style="text-align: center; grid-column: 1 / -1; padding: 4rem 2rem; background: rgba(255, 255, 255, 0.9); border-radius: 24px; border: 2px dashed rgba(34, 197, 94, 0.35); box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
                    <div style="font-size: 3rem; margin-bottom: 12px;">🌿</div>
                    <h3 style="color: var(--primary, #0f5132); font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">No eco-stays verified for this region yet.</h3>
                    <p style="color: var(--text-muted, #6c757d); font-size: 0.96rem; max-width: 500px; margin: 0 auto 1.5rem auto;">
                        We currently certify sustainable, barrier-free properties in <strong>Agra, Goa, Mumbai, and Manali</strong>. Select one from the dropdown above to view verified sanctuaries.
                    </p>
                    <a href="eco-stays.php?origin=<?= urlencode($origin) ?>&dest=manali" class="btn-select-stay" style="display: inline-block; padding: 0.75rem 1.6rem; text-decoration: none;">
                        View Manali Eco-Stays ➔
                    </a>
                </div>
            <?php endif; ?>
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
                    <a href="eco-stays.php">Phase 2: Eco-Hotels</a>
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
            © 2026 Way2Green • Built for Green &amp; Inclusive Travel Challenge.
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
        <a href="eco-stays.php" class="mobile-nav-item active">
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

    <!-- Client-side filter pills script & drawer toggle -->
    <script>
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }

        const FILTER_KEYWORDS = {
            wheelchair: ['wheelchair', 'step-free', 'ramp', 'pool lift', 'roll-in', 'barrier-free', 'universal design', 'accessible'],
            sensory:    ['sensory', 'quiet', 'low stimulation', 'dimmable', 'sound damp', 'calm'],
            braille:    ['braille', 'audio', 'tactile', 'hearing loop', 'sign language'],
            dog:        ['service dog', 'service animal', 'dog']
        };

        function filterCards(filter, btn) {
            document.querySelectorAll('.pill-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const cards = document.querySelectorAll('.stays-grid .stay-card');
            cards.forEach(card => {
                if (filter === 'all') {
                    card.style.display = '';
                    return;
                }
                const tags = card.getAttribute('data-tags') || '';
                const keywords = FILTER_KEYWORDS[filter] || [];
                const match = keywords.some(kw => tags.includes(kw));
                card.style.display = match ? '' : 'none';
            });
        }
    </script>
    <?php include 'components/map_picker.php'; ?>
    <script>
        let stopCount = 0;
        function addStop() {
            stopCount++;
            const container = document.getElementById('stopsContainer');
            const stopId = 'stop_' + stopCount;
            const div = document.createElement('div');
            div.style.display = 'flex';
            div.style.gap = '10px';
            div.style.alignItems = 'center';
            div.id = 'stop_row_' + stopCount;
            div.innerHTML = `
                <div style="font-weight: bold; width: 60px; color: var(--text-muted); font-size: 0.9rem;">Stop</div>
                <div style="position: relative; flex: 1;">
                    <input type="text" name="stops[]" id="${stopId}" placeholder="Add a city or tourist spot" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <button type="button" onclick="openMapPicker('${stopId}')" style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); background: var(--primary); color: #fff; border: none; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">Map</button>
                </div>
                <button type="button" onclick="document.getElementById('stop_row_${stopCount}').remove()" style="background: transparent; border: none; color: #dc2626; font-size: 1.2rem; cursor: pointer; padding: 0 5px;">&times;</button>
            `;
            container.appendChild(div);
        }
    </script>
    <script src="js/effects.js"></script>
</body>
</html>


