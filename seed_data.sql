USE way2green;

-- Ensure table has price_per_night and badge columns if missing
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS price_per_night INT DEFAULT 2500;
ALTER TABLE hotels ADD COLUMN IF NOT EXISTS eco_badges VARCHAR(255) DEFAULT 'Solar Powered, Zero Plastic';

-- Clear existing sample data to prevent duplicate seeds
DELETE FROM hotels;
DELETE FROM destinations;

-- Insert Destinations
INSERT INTO destinations (id, name, latitude, longitude) VALUES
(1, 'Munnar, Kerala', 10.088933, 77.059525),
(2, 'Manali, Himachal Pradesh', 32.239632, 77.188713),
(3, 'Wayanad, Kerala', 11.685358, 76.132034),
(4, 'South Goa (Eco-Coast)', 15.150000, 73.950000),
(5, 'Rishikesh, Uttarakhand', 30.086927, 78.267612),
(6, 'Ooty, Tamil Nadu', 11.410204, 76.695038);

-- Insert Hotels
INSERT INTO hotels (id, destination_id, name, description, image_url, water_saved_liters, power_saved_kwh, accessibility_tags, eco_rating, price_per_night, eco_badges) VALUES
(1, 1, 'Misty Mountain Eco-Resort', 
 'Nestled among the organic tea plantations of Munnar, this retreat runs on 100% micro-hydro and solar power. Features greywater recycling, organic farm harvests, and certified quiet wellness zones.',
 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
 145000, 28000, 'Wheelchair Accessible, Step-free Entry, Sensory Quiet Rooms', 4.9, 3800, '100% Solar & Hydro, Zero Single-Use Plastic, Organic Farm'),

(2, 1, 'Cardamom Grove Bamboo Stay',
 'Built entirely from sustainably harvested bamboo and local laterite stone. Features rainwater gravity filtration and universal ramps to all cottages.',
 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',
 98000, 18500, 'Wheelchair Accessible, Visual Strobe Alarms, Braille Signage', 4.7, 2900, 'Rainwater Harvesting, Bamboo Architecture, Local Artisan Employment'),

(3, 2, 'Solang Himalayan Bio-Lodge',
 'Passive solar-heated alpine cottages with triple-glazed recycled timber windows. Organic apple orchard on-site with zero food waste composting system.',
 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',
 120000, 34000, 'Wheelchair Accessible, Elevator, Accessible Bathroom with Grab Bars', 4.8, 4200, 'Passive Solar Heating, Zero Waste Composting, Electric Vehicle Charging'),

(4, 2, 'Pine Forest Sanctuary & Chalets',
 'A low-impact wilderness stay offering low-sensory environment rooms for neurodivergent travelers and tactile path navigation.',
 'https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',
 85000, 19200, 'Sensory Quiet Rooms, Tactile Floor Guides, Step-free Dining', 4.6, 3200, 'Geothermal Ground Heat, Wildlife Corridor Protection'),

(5, 3, 'Banasura Earth Sanctuary',
 'Asia\'s largest earth-architecture eco-lodge crafted from mud, clay, and terracotta. Boasts natural bioclimatic cooling and dedicated accessibility suites with wide doors.',
 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
 210000, 41000, 'Wheelchair Accessible, Wide Doorways, Guide Dog Friendly, Braille Menus', 4.9, 4500, 'Mud Architecture, 100% Greywater Wetland Treatment, Biodiversity Buffer'),

(6, 4, 'Palolem Turtle Bay Eco-Huts',
 'Solar-powered beach cottages supporting Olive Ridley turtle conservation. Strictly zero plastic, solar water heating, and beach wheelchair accessibility mats.',
 'https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',
 110000, 22000, 'Beach Wheelchair Available, Step-free Boardwalk, Sensory Friendly', 4.8, 3400, 'Marine Habitat Protection, Solar Powered, Upcycled Coastal Materials'),

(7, 5, 'Ganges Serenity Ashram & Eco-Retreat',
 'Situated along sacred waterways with natural spring water catchment, vegan organic kitchen, and tactile meditation gardens for visual-impaired travelers.',
 'https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=800&q=80',
 160000, 27000, 'Wheelchair Accessible, Braille Signage, Audio Guided Tours', 4.7, 2600, 'Zero River Runoff, Organic Ayurvedic Farming, Solar Cookers'),

(8, 6, 'Nilgiri Heritage Green Manor',
 'Restored heritage property surrounded by organic tea gardens. Features electrical vehicle fast-chargers, LED-only adaptive lighting, and auditory elevator cues.',
 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=800&q=80',
 135000, 26500, 'Wheelchair Accessible, Auditory Elevator Cues, Accessible Roll-in Showers', 4.8, 3900, 'EV Fast Charging, Certified Rainforest Alliance Partner');
