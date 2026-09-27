<?php
// seed_more_hotels.php - Add 3-4 more verified eco-hotels for every destination
require_once 'db.php';

$destinations = [
    1 => 'Munnar, Kerala',
    2 => 'Manali, Himachal Pradesh',
    3 => 'Wayanad, Kerala',
    4 => 'South Goa (Eco-Coast)',
    5 => 'Rishikesh, Uttarakhand',
    6 => 'Ooty, Tamil Nadu'
];

$moreHotels = [
    // Destination 1: Munnar (Add 3 more)
    [
        'destination_id' => 1,
        'name' => 'Tea Valley Bio-Sanctuary',
        'description' => 'Overlooking rolling tea plantations with 100% solar micro-grid, gravity-fed spring water, and tactile nature boardwalks for visual accessibility.',
        'image_url' => 'https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 135000,
        'power_saved_kwh' => 24000,
        'accessibility_tags' => 'Wheelchair Accessible, Step-free Entry, Tactile Paths',
        'eco_rating' => 4.8,
        'price_per_night' => 3400,
        'eco_badges' => '100% Solar Powered, Rainwater Harvesting, Organic Tea Dining'
    ],
    [
        'destination_id' => 1,
        'name' => 'Highland Whisper Eco-Cottages',
        'description' => 'Stone and clay cottages with passive natural cooling, zero single-use plastics, and quiet low-stimulation forest suites for neurodivergent travelers.',
        'image_url' => 'https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 110000,
        'power_saved_kwh' => 19500,
        'accessibility_tags' => 'Sensory Quiet Rooms, Step-free Dining, Service Dog Friendly',
        'eco_rating' => 4.7,
        'price_per_night' => 3100,
        'eco_badges' => 'Zero Single-Use Plastics, Greywater Wetland Filter, Passive Cooling'
    ],
    [
        'destination_id' => 1,
        'name' => 'Anamudi Peak Cloud Chalet',
        'description' => 'High-altitude certified carbon-neutral stay utilizing wind and micro-hydro power with wide doorways and accessible roll-in shower suites.',
        'image_url' => 'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 165000,
        'power_saved_kwh' => 31000,
        'accessibility_tags' => 'Wheelchair Accessible, Roll-in Showers, Braille Signage',
        'eco_rating' => 4.9,
        'price_per_night' => 4200,
        'eco_badges' => 'Carbon Neutral Certified, Micro-Hydro Turbines, Local Sourcing'
    ],

    // Destination 2: Manali (Add 3 more)
    [
        'destination_id' => 2,
        'name' => 'Cedar Ridge Alpine Sanctuary',
        'description' => 'Built with salvaged cedar and thermal rock insulation. Geothermal heat pumps, solar-heated showers, and step-free stone dining verandas.',
        'image_url' => 'https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 140000,
        'power_saved_kwh' => 29000,
        'accessibility_tags' => 'Wheelchair Accessible, Ground Floor Suites, Grab Bars',
        'eco_rating' => 4.8,
        'price_per_night' => 3900,
        'eco_badges' => 'Geothermal Heating, Salvaged Timber, Electric Vehicle Charger'
    ],
    [
        'destination_id' => 2,
        'name' => 'Beas River Whisper Bio-Huts',
        'description' => 'Riverside passive-solar wooden chalets with dedicated acoustic quiet zones, low electromagnetic radiation rooms, and service animal exercise yards.',
        'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 95000,
        'power_saved_kwh' => 17000,
        'accessibility_tags' => 'Sensory Quiet Rooms, Service Dog Friendly, Audio Tour Maps',
        'eco_rating' => 4.7,
        'price_per_night' => 3300,
        'eco_badges' => 'River Ecosystem Protection, Zero Plastic, On-site Solar Array'
    ],
    [
        'destination_id' => 2,
        'name' => 'Old Manali Apple Blossom Retreat',
        'description' => 'Heritage mud-plastered timber architecture in a certified organic orchard. Features wheelchair ramps to all common areas and auditory fire safety cues.',
        'image_url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 125000,
        'power_saved_kwh' => 22000,
        'accessibility_tags' => 'Wheelchair Accessible, Step-free Entry, Auditory Alarms',
        'eco_rating' => 4.9,
        'price_per_night' => 3600,
        'eco_badges' => '100% Organic Orchard Dining, Earth Plaster, Zero Waste'
    ],

    // Destination 3: Wayanad (Add 3 more)
    [
        'destination_id' => 3,
        'name' => 'Rainforest Canopy Eco-Treehouse',
        'description' => 'Elevated low-impact tree-dwellings with wheelchair elevator platform, solar tree power, and rainwater gravity showers.',
        'image_url' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 180000,
        'power_saved_kwh' => 33000,
        'accessibility_tags' => 'Wheelchair Accessible, Platform Elevator, Braille Guides',
        'eco_rating' => 4.9,
        'price_per_night' => 4600,
        'eco_badges' => 'Canopy Conservation, Solar Microgrid, Indigenous Reforestation'
    ],
    [
        'destination_id' => 3,
        'name' => 'Kabini Water-Meadow Haven',
        'description' => 'Wetland preservation retreat powered by floating solar panels. Offers quiet sensory recovery garden and certified guide dog hospitality.',
        'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 230000,
        'power_saved_kwh' => 45000,
        'accessibility_tags' => 'Service Dog Friendly, Sensory Quiet Rooms, Wide Hallways',
        'eco_rating' => 4.8,
        'price_per_night' => 4100,
        'eco_badges' => 'Floating Solar, Wetland Bio-filter, Zero Runoff'
    ],
    [
        'destination_id' => 3,
        'name' => 'Wayanad Spice Valley Clay Manor',
        'description' => 'Unbaked earth brick villas featuring zero air-conditioner bioclimatic design and complete roll-in wheelchair access throughout.',
        'image_url' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 150000,
        'power_saved_kwh' => 26000,
        'accessibility_tags' => 'Wheelchair Accessible, Step-free Entry, Roll-in Showers',
        'eco_rating' => 4.7,
        'price_per_night' => 3500,
        'eco_badges' => 'Bioclimatic Earth Walls, Spice Farm Permaculture, Solar Water'
    ],

    // Destination 4: South Goa (Add 3 more)
    [
        'destination_id' => 4,
        'name' => 'Agonda Dune Eco-Sanctuary',
        'description' => 'Coastal eco-lodge committed to coastal sand dune preservation. Upcycled drift timber rooms, beach mobi-mats for wheelchairs, and sensory quiet sunset pavilions.',
        'image_url' => 'https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 175000,
        'power_saved_kwh' => 32000,
        'accessibility_tags' => 'Beach Wheelchair Available, Step-free Boardwalk, Sensory Friendly',
        'eco_rating' => 4.9,
        'price_per_night' => 4200,
        'eco_badges' => 'Dune Ecosystem Guard, 100% Solar, Compost Sanitation'
    ],
    [
        'destination_id' => 4,
        'name' => 'Cola Lagoon Palm Haven',
        'description' => 'Freshwater lagoon retreat with electric boat transfers, zero single-use plastics, and accessible ramped palm chalets with guide dog welcoming policy.',
        'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 130000,
        'power_saved_kwh' => 21000,
        'accessibility_tags' => 'Wheelchair Accessible, Service Dog Friendly, Ground Floor',
        'eco_rating' => 4.7,
        'price_per_night' => 3600,
        'eco_badges' => 'Electric Boat Transit, Solar Water Heating, Zero Plastic'
    ],
    [
        'destination_id' => 4,
        'name' => 'Cabo de Rama Bio-Cliffs',
        'description' => 'Oceanfront clifftop chalets powered by tidal wind and rooftop solar. Tactile pathway markers and audio beach guidance for visual accessibility.',
        'image_url' => 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 145000,
        'power_saved_kwh' => 27000,
        'accessibility_tags' => 'Braille Signage, Audio Guides, Step-free Dining Deck',
        'eco_rating' => 4.8,
        'price_per_night' => 3800,
        'eco_badges' => 'Hybrid Wind & Solar, Upcycled Bamboo, Organic Coastal Farm'
    ],

    // Destination 5: Rishikesh (Add 3 more)
    [
        'destination_id' => 5,
        'name' => 'Himalayan Foothills Solar Ashram',
        'description' => 'Pure vegetarian zero-waste sanctuary on the banks of holy rivers. 100% solar steam cooking, auditory bell cues, and wheelchair accessible meditation halls.',
        'image_url' => 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 205000,
        'power_saved_kwh' => 38000,
        'accessibility_tags' => 'Wheelchair Accessible, Step-free Meditation Halls, Auditory Bell Cues',
        'eco_rating' => 4.9,
        'price_per_night' => 3100,
        'eco_badges' => 'Solar Steam Kitchen, Ayurvedic Herb Garden, Zero River Waste'
    ],
    [
        'destination_id' => 5,
        'name' => 'Shivpuri Pine & River Bio-Lodge',
        'description' => 'Low-impact stone retreat offering quiet stimulation recovery suites, step-free river boardwalks, and Braille river trail maps.',
        'image_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 120000,
        'power_saved_kwh' => 23000,
        'accessibility_tags' => 'Sensory Quiet Rooms, Braille Trail Maps, Service Dog Friendly',
        'eco_rating' => 4.7,
        'price_per_night' => 2800,
        'eco_badges' => 'Local River Stone, Gravity Spring Catchment, EV Charge Point'
    ],
    [
        'destination_id' => 5,
        'name' => 'Tapovan Green Heights Sanctuary',
        'description' => 'Modern earth-friendly boutique stay with LED smart sensors, organic terrace permaculture, and certified universal wheelchair elevator access.',
        'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 140000,
        'power_saved_kwh' => 25500,
        'accessibility_tags' => 'Wheelchair Accessible, Elevator Access, Roll-in Showers',
        'eco_rating' => 4.8,
        'price_per_night' => 3500,
        'eco_badges' => 'Terrace Permaculture, Smart Energy Management, Rainwater System'
    ],

    // Destination 6: Ooty (Add 3 more)
    [
        'destination_id' => 6,
        'name' => 'Doddabetta Pine Mist Eco-Lodge',
        'description' => 'Nestled beneath pine groves with thermal clay heaters, zero single-use plastic policy, step-free mountain view deck, and sensory calm rooms.',
        'image_url' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 155000,
        'power_saved_kwh' => 29000,
        'accessibility_tags' => 'Sensory Quiet Rooms, Step-free Deck, Service Dog Friendly',
        'eco_rating' => 4.8,
        'price_per_night' => 3700,
        'eco_badges' => 'Thermal Clay Heaters, Rainwater Harvesting, Reforestation Partner'
    ],
    [
        'destination_id' => 6,
        'name' => 'Emerald Lake Bio-Sanctuary',
        'description' => 'Certified Organic tea estate resort powered by lake hydro-turbines with tactile stone gardens and wheelchair roll-in accessibility chalets.',
        'image_url' => 'https://images.unsplash.com/photo-1426604966848-d7adac402bff?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 190000,
        'power_saved_kwh' => 37000,
        'accessibility_tags' => 'Wheelchair Accessible, Tactile Gardens, Braille Signage',
        'eco_rating' => 4.9,
        'price_per_night' => 4400,
        'eco_badges' => 'Hydro Turbine Power, 100% Organic Farm, Wetland Filter'
    ],
    [
        'destination_id' => 6,
        'name' => 'Kotagiri Valley Tea Eco-Retreat',
        'description' => 'Colonial-style stone eco-retreat with electric vehicle chargers, LED circadian lighting, and universal ramped access to all garden pavilions.',
        'image_url' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
        'water_saved_liters' => 125000,
        'power_saved_kwh' => 24000,
        'accessibility_tags' => 'Wheelchair Accessible, Step-free Boardwalk, Auditory Cues',
        'eco_rating' => 4.7,
        'price_per_night' => 3300,
        'eco_badges' => 'EV Fast Chargers, Circadian Lighting, Zero Chemical Fertilizers'
    ]
];

$stmt = $pdo->prepare("INSERT INTO hotels 
    (destination_id, name, description, image_url, water_saved_liters, power_saved_kwh, accessibility_tags, eco_rating, price_per_night, eco_badges) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$added = 0;
foreach ($moreHotels as $h) {
    // Check if hotel name already exists
    $check = $pdo->prepare("SELECT id FROM hotels WHERE name = ?");
    $check->execute([$h['name']]);
    if (!$check->fetch()) {
        $stmt->execute([
            $h['destination_id'],
            $h['name'],
            $h['description'],
            $h['image_url'],
            $h['water_saved_liters'],
            $h['power_saved_kwh'],
            $h['accessibility_tags'],
            $h['eco_rating'],
            $h['price_per_night'],
            $h['eco_badges']
        ]);
        $added++;
    }
}

$totalHotels = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
echo "Successfully added $added new eco-hotels! Total in database: $totalHotels\n";

