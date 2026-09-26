USE way2green;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    origin VARCHAR(100) NOT NULL,
    destination VARCHAR(100) NOT NULL,
    travel_mode VARCHAR(50) NOT NULL,
    distance_km DECIMAL(8,2) DEFAULT 0,
    co2_saved_kg DECIMAL(8,2) DEFAULT 0,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT DEFAULT 1,
    booking_code VARCHAR(50) NOT NULL UNIQUE,
    total_price INT DEFAULT 0,
    accessibility_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
);

-- Seed demo traveler
INSERT IGNORE INTO users (id, name, email, password_hash) 
VALUES (1, 'Alex Morgan', 'traveler@way2green.com', '$2y$10$fWJ0LhA1Xn19oR3R0l5lCe9lW2VqU18a5X0R27h3.W/qgqO3k8qKy');
