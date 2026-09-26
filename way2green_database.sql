-- Way2Green Complete Production Database Dump
-- Compatible with MySQL / MariaDB / phpMyAdmin / InfinityFree

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for table `admin_users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$H9Vg12Uq1fNcWUeiaMPbFuIrkvcrmWqiXmv/C2z/2oW4NQloDOXv6','2026-09-07 13:26:07');

-- --------------------------------------------------------
-- Table structure for table `destinations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `destinations`;
CREATE TABLE `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `destinations` VALUES 
(1,'Munnar, Kerala',10.08893300,77.05952500,'2026-09-08 08:41:48'),
(2,'Manali, Himachal Pradesh',32.23963200,77.18871300,'2026-09-08 08:41:48'),
(3,'Wayanad, Kerala',11.68535800,76.13203400,'2026-09-08 08:41:48'),
(4,'South Goa (Eco-Coast)',15.15000000,73.95000000,'2026-09-08 08:41:48'),
(5,'Rishikesh, Uttarakhand',30.08692700,78.26761200,'2026-09-08 08:41:48'),
(6,'Ooty, Tamil Nadu',11.41020400,76.69503800,'2026-09-08 08:41:48');

-- --------------------------------------------------------
-- Table structure for table `hotels`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `hotels`;
CREATE TABLE `hotels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `destination_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `water_saved_liters` int(11) DEFAULT 0,
  `power_saved_kwh` int(11) DEFAULT 0,
  `accessibility_tags` varchar(255) DEFAULT NULL,
  `eco_rating` decimal(3,1) DEFAULT 0.0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price_per_night` int(11) DEFAULT 2500,
  `eco_badges` varchar(255) DEFAULT 'Solar Powered, Zero Plastic',
  PRIMARY KEY (`id`),
  KEY `destination_id` (`destination_id`),
  CONSTRAINT `hotels_ibfk_1` FOREIGN KEY (`destination_id`) REFERENCES `destinations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hotels` VALUES 
(1,1,'Misty Mountain Eco-Resort','Nestled among the organic tea plantations of Munnar, this retreat runs on 100% micro-hydro and solar power. Features greywater recycling, organic farm harvests, and certified quiet wellness zones.','https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',145000,28000,'Wheelchair Accessible, Step-free Entry, Sensory Quiet Rooms',4.9,'2026-09-08 08:41:48',3800,'100% Solar & Hydro, Zero Single-Use Plastic, Organic Farm'),
(2,1,'Cardamom Grove Bamboo Stay','Built entirely from sustainably harvested bamboo and local laterite stone. Features rainwater gravity filtration and universal ramps to all cottages.','https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',98000,18500,'Wheelchair Accessible, Visual Strobe Alarms, Braille Signage',4.7,'2026-09-08 08:41:48',2900,'Rainwater Harvesting, Bamboo Architecture, Local Artisan Employment'),
(3,2,'Solang Himalayan Bio-Lodge','Passive solar-heated alpine cottages with triple-glazed recycled timber windows. Organic apple orchard on-site with zero food waste composting system.','https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80',120000,34000,'Wheelchair Accessible, Elevator, Accessible Bathroom with Grab Bars',4.8,'2026-09-08 08:41:48',4200,'Passive Solar Heating, Zero Waste Composting, Electric Vehicle Charging'),
(4,2,'Pine Forest Sanctuary & Chalets','A low-impact wilderness stay offering low-sensory environment rooms for neurodivergent travelers and tactile path navigation.','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',85000,19200,'Sensory Quiet Rooms, Tactile Floor Guides, Step-free Dining',4.6,'2026-09-08 08:41:48',3200,'Geothermal Ground Heat, Wildlife Corridor Protection'),
(5,3,'Banasura Earth Sanctuary','Asia\'s largest earth-architecture eco-lodge crafted from mud, clay, and terracotta. Boasts natural bioclimatic cooling and dedicated accessibility suites with wide doors.','https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',210000,41000,'Wheelchair Accessible, Wide Doorways, Guide Dog Friendly, Braille Menus',4.9,'2026-09-08 08:41:48',4500,'Mud Architecture, 100% Greywater Wetland Treatment, Biodiversity Buffer'),
(6,4,'Palolem Turtle Bay Eco-Huts','Solar-powered beach cottages supporting Olive Ridley turtle conservation. Strictly zero plastic, solar water heating, and beach wheelchair accessibility mats.','https://images.unsplash.com/photo-1499793983690-e29da59ef1c2?auto=format&fit=crop&w=800&q=80',110000,22000,'Beach Wheelchair Available, Step-free Boardwalk, Sensory Friendly',4.8,'2026-09-08 08:41:48',3400,'Marine Habitat Protection, Solar Powered, Upcycled Coastal Materials'),
(7,5,'Ganges Serenity Ashram & Eco-Retreat','Situated along sacred waterways with natural spring water catchment, vegan organic kitchen, and tactile meditation gardens for visual-impaired travelers.','https://images.unsplash.com/photo-1507652313519-d4e9174996dd?auto=format&fit=crop&w=800&q=80',160000,27000,'Wheelchair Accessible, Braille Signage, Audio Guided Tours',4.7,'2026-09-08 08:41:48',2600,'Zero River Runoff, Organic Ayurvedic Farming, Solar Cookers'),
(8,6,'Nilgiri Heritage Green Manor','Restored heritage property surrounded by organic tea gardens. Features electrical vehicle fast-chargers, LED-only adaptive lighting, and auditory elevator cues.','https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=800&q=80',135000,26500,'Wheelchair Accessible, Auditory Elevator Cues, Accessible Roll-in Showers',4.8,'2026-09-08 08:41:48',3900,'EV Fast Charging, Certified Rainforest Alliance Partner'),
(9,1,'Tea Valley Bio-Sanctuary','Overlooking rolling tea plantations with 100% solar micro-grid, gravity-fed spring water, and tactile nature boardwalks for visual accessibility.','https://images.unsplash.com/photo-1544644181-1484b3fdfc62?auto=format&fit=crop&w=800&q=80',135000,24000,'Wheelchair Accessible, Step-free Entry, Tactile Paths',4.8,'2026-09-08 10:34:04',3400,'100% Solar Powered, Rainwater Harvesting, Organic Tea Dining'),
(10,1,'Highland Whisper Eco-Cottages','Stone and clay cottages with passive natural cooling, zero single-use plastics, and quiet low-stimulation forest suites for neurodivergent travelers.','https://images.unsplash.com/photo-1587061949409-02df41d5e562?auto=format&fit=crop&w=800&q=80',110000,19500,'Sensory Quiet Rooms, Step-free Dining, Service Dog Friendly',4.7,'2026-09-08 10:34:04',3100,'Zero Single-Use Plastics, Greywater Wetland Filter, Passive Cooling'),
(11,1,'Anamudi Peak Cloud Chalet','High-altitude certified carbon-neutral stay utilizing wind and micro-hydro power with wide doorways and accessible roll-in shower suites.','https://images.unsplash.com/photo-1470770841072-f978cf4d019e?auto=format&fit=crop&w=800&q=80',165000,31000,'Wheelchair Accessible, Roll-in Showers, Braille Signage',4.9,'2026-09-08 10:34:04',4200,'Carbon Neutral Certified, Micro-Hydro Turbines, Local Sourcing'),
(12,2,'Cedar Ridge Alpine Sanctuary','Built with salvaged cedar and thermal rock insulation. Geothermal heat pumps, solar-heated showers, and step-free stone dining verandas.','https://images.unsplash.com/photo-1510798831971-661eb04b3739?auto=format&fit=crop&w=800&q=80',140000,29000,'Wheelchair Accessible, Ground Floor Suites, Grab Bars',4.8,'2026-09-08 10:34:04',3900,'Geothermal Heating, Salvaged Timber, Electric Vehicle Charger'),
(13,2,'Beas River Whisper Bio-Huts','Riverside passive-solar wooden chalets with dedicated acoustic quiet zones, low electromagnetic radiation rooms, and service animal exercise yards.','https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80',95000,17000,'Sensory Quiet Rooms, Service Dog Friendly, Audio Tour Maps',4.7,'2026-09-08 10:34:04',3300,'River Ecosystem Protection, Zero Plastic, On-site Solar Array'),
(14,2,'Old Manali Apple Blossom Retreat','Heritage mud-plastered timber architecture in a certified organic orchard. Features wheelchair ramps to all common areas and auditory fire safety cues.','https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80',125000,22000,'Wheelchair Accessible, Step-free Entry, Auditory Alarms',4.9,'2026-09-08 10:34:04',3600,'100% Organic Orchard Dining, Earth Plaster, Zero Waste'),
(15,3,'Rainforest Canopy Eco-Treehouse','Elevated low-impact tree-dwellings with wheelchair elevator platform, solar tree power, and rainwater gravity showers.','https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=800&q=80',180000,33000,'Wheelchair Accessible, Platform Elevator, Braille Guides',4.9,'2026-09-08 10:34:04',4600,'Canopy Conservation, Solar Microgrid, Indigenous Reforestation'),
(16,3,'Kabini Water-Meadow Haven','Wetland preservation retreat powered by floating solar panels. Offers quiet sensory recovery garden and certified guide dog hospitality.','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',230000,45000,'Service Dog Friendly, Sensory Quiet Rooms, Wide Hallways',4.8,'2026-09-08 10:34:04',4100,'Floating Solar, Wetland Bio-filter, Zero Runoff'),
(17,3,'Wayanad Spice Valley Clay Manor','Unbaked earth brick villas featuring zero air-conditioner bioclimatic design and complete roll-in wheelchair access throughout.','https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80',150000,26000,'Wheelchair Accessible, Step-free Entry, Roll-in Showers',4.7,'2026-09-08 10:34:04',3500,'Bioclimatic Earth Walls, Spice Farm Permaculture, Solar Water'),
(18,4,'Agonda Dune Eco-Sanctuary','Coastal eco-lodge committed to coastal sand dune preservation. Upcycled drift timber rooms, beach mobi-mats for wheelchairs, and sensory quiet sunset pavilions.','https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80',175000,32000,'Beach Wheelchair Available, Step-free Boardwalk, Sensory Friendly',4.9,'2026-09-08 10:34:04',4200,'Dune Ecosystem Guard, 100% Solar, Compost Sanitation'),
(19,4,'Cola Lagoon Palm Haven','Freshwater lagoon retreat with electric boat transfers, zero single-use plastics, and accessible ramped palm chalets with guide dog welcoming policy.','https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80',130000,21000,'Wheelchair Accessible, Service Dog Friendly, Ground Floor',4.7,'2026-09-08 10:34:04',3600,'Electric Boat Transit, Solar Water Heating, Zero Plastic'),
(20,4,'Cabo de Rama Bio-Cliffs','Oceanfront clifftop chalets powered by tidal wind and rooftop solar. Tactile pathway markers and audio beach guidance for visual accessibility.','https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80',145000,27000,'Braille Signage, Audio Guides, Step-free Dining Deck',4.8,'2026-09-08 10:34:04',3800,'Hybrid Wind & Solar, Upcycled Bamboo, Organic Coastal Farm'),
(21,5,'Himalayan Foothills Solar Ashram','Pure vegetarian zero-waste sanctuary on the banks of holy rivers. 100% solar steam cooking, auditory bell cues, and wheelchair accessible meditation halls.','https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&w=800&q=80',205000,38000,'Wheelchair Accessible, Step-free Meditation Halls, Auditory Bell Cues',4.9,'2026-09-08 10:34:04',3100,'Solar Steam Kitchen, Ayurvedic Herb Garden, Zero River Waste'),
(22,5,'Shivpuri Pine & River Bio-Lodge','Low-impact stone retreat offering quiet stimulation recovery suites, step-free river boardwalks, and Braille river trail maps.','https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=800&q=80',120000,23000,'Sensory Quiet Rooms, Braille Trail Maps, Service Dog Friendly',4.7,'2026-09-08 10:34:04',2800,'Local River Stone, Gravity Spring Catchment, EV Charge Point'),
(23,5,'Tapovan Green Heights Sanctuary','Modern earth-friendly boutique stay with LED smart sensors, organic terrace permaculture, and certified universal wheelchair elevator access.','https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=800&q=80',140000,25500,'Wheelchair Accessible, Elevator Access, Roll-in Showers',4.8,'2026-09-08 10:34:04',3500,'Terrace Permaculture, Smart Energy Management, Rainwater System'),
(24,6,'Doddabetta Pine Mist Eco-Lodge','Nestled beneath pine groves with thermal clay heaters, zero single-use plastic policy, step-free mountain view deck, and sensory calm rooms.','https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=800&q=80',155000,29000,'Sensory Quiet Rooms, Step-free Deck, Service Dog Friendly',4.8,'2026-09-08 10:34:04',3700,'Thermal Clay Heaters, Rainwater Harvesting, Reforestation Partner'),
(25,6,'Emerald Lake Bio-Sanctuary','Certified Organic tea estate resort powered by lake hydro-turbines with tactile stone gardens and wheelchair roll-in accessibility chalets.','https://images.unsplash.com/photo-1426604966848-d7adac402bff?auto=format&fit=crop&w=800&q=80',190000,37000,'Wheelchair Accessible, Tactile Gardens, Braille Signage',4.9,'2026-09-08 10:34:04',4400,'Hydro Turbine Power, 100% Organic Farm, Wetland Filter'),
(26,6,'Kotagiri Valley Tea Eco-Retreat','Colonial-style stone eco-retreat with electric vehicle chargers, LED circadian lighting, and universal ramped access to all garden pavilions.','https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',125000,24000,'Wheelchair Accessible, Step-free Boardwalk, Auditory Cues',4.7,'2026-09-08 10:34:04',3300,'EV Fast Chargers, Circadian Lighting, Zero Chemical Fertilizers');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` VALUES 
(1,'Alex Morgan','traveler@way2green.com','$2y$10$fWJ0LhA1Xn19oR3R0l5lCe9lW2VqU18a5X0R27h3.W/qgqO3k8qKy','2026-09-08 09:42:31'),
(2,'Jack','jack@j.com','$2y$10$8rAFOwFRc7f.B7Fp9fg8we/hUWzfUXUYCP6YFROGxu9TYpbEXMHBq','2026-09-08 09:57:36');

-- --------------------------------------------------------
-- Table structure for table `bookings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `bookings`;
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `travel_mode` varchar(50) NOT NULL,
  `distance_km` decimal(8,2) DEFAULT 0.00,
  `co2_saved_kg` decimal(8,2) DEFAULT 0.00,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `guests` int(11) DEFAULT 1,
  `booking_code` varchar(50) NOT NULL,
  `total_price` int(11) DEFAULT 0,
  `accessibility_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_code` (`booking_code`),
  KEY `user_id` (`user_id`),
  KEY `hotel_id` (`hotel_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `hotels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bookings` VALUES 
(1,2,4,'Mumbai','Manali, Himachal Pradesh','train',480.00,75.40,'2026-09-08','2026-09-10',3,'W2G-F234A6-ECO',6400,'Sensory Quiet Room | Ground Floor Preferred','2026-09-08 09:58:31'),
(2,2,8,'Bengaluru','Ooty, Tamil Nadu','train',480.00,75.40,'2026-09-08','2026-09-10',2,'W2G-41A8CA-ECO',7800,'Ground Floor Preferred','2026-09-08 09:59:32'),
(3,2,4,'Mumbai','Manali, Himachal Pradesh','train',480.00,75.40,'2026-09-08','2026-09-10',2,'W2G-E8704A-ECO',6400,'','2026-09-08 10:24:13'),
(4,2,19,'Mumbai','South Goa (Eco-Coast)','flight',480.00,0.00,'2026-09-08','2026-09-10',2,'W2G-A6F5E5-ECO',7200,'','2026-09-08 11:01:10');

SET FOREIGN_KEY_CHECKS = 1;
