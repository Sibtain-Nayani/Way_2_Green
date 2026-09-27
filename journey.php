<?php
// journey.php - Phase 1: Trip Search Foundation
require_once 'db.php';
require_once 'user_auth.php';

require_user_login('journey.php');
$user = get_logged_in_user();

$trip_data = $_SESSION['trip_search'] ?? null;

// FUTURE API INTEGRATION — PHASE 7
// Here we would fetch actual journeys from APIs based on $trip_data

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journey Results — Way2Green</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header-top">
        <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>
        <nav class="desktop-nav">
            <a href="index.php">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Plan Transit</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a></nav>
    </header>

    <main class="page-container" style="max-width: 800px; padding-top: 4rem;">
        <div class="card-box card-3d">
            <h1 style="color: var(--primary); font-size: 1.8rem; margin-bottom: 1rem;">Journey Found</h1>
            
            <?php if ($trip_data): ?>
                <div style="background: #f8fcf8; padding: 1.5rem; border-radius: var(--radius-md); border: 1px solid var(--border-subtle);">
                    <h3 style="margin-top: 0; color: var(--primary);">Search Parameters Received:</h3>
                    <ul style="list-style: none; padding: 0; margin: 0; line-height: 1.8; color: var(--text-color);">
                        <li><strong>Origin:</strong> <?= htmlspecialchars($trip_data['origin']) ?></li>
                        <li><strong>Destination:</strong> <?= htmlspecialchars($trip_data['destination']) ?></li>
                        <li><strong>Departure Date:</strong> <?= htmlspecialchars($trip_data['departure_date']) ?></li>
                        <li><strong>Return Date:</strong> <?= htmlspecialchars($trip_data['return_date']) ?: 'N/A' ?></li>
                        <li><strong>Trip Type:</strong> <?= htmlspecialchars($trip_data['trip_type']) ?></li>
                        <li><strong>Travellers:</strong> <?= htmlspecialchars($trip_data['travellers']) ?></li>
                        <li><strong>Preference:</strong> <?= htmlspecialchars($trip_data['preference']) ?></li>
                    </ul>
                </div>
                <div style="margin-top: 2rem; text-align: center;">
                    <p style="color: var(--text-muted); font-size: 0.9rem;">
                        // FUTURE API INTEGRATION — PHASE 7<br>
                        Results will be fetched from transit providers here.
                    </p>
                    <a href="travel.php" class="btn-nature-primary" style="display: inline-block; margin-top: 1rem;">New Search</a>
                </div>
            <?php else: ?>
                <p style="color: var(--text-muted);">No trip search data found in session.</p>
                <a href="travel.php" class="btn-nature-primary" style="display: inline-block; margin-top: 1rem;">Start a Search</a>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

