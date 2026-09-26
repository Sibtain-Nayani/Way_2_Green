<?php
// admin/dashboard.php - Way2Green Admin Command Center
require_once 'auth.php';
require_login();
require_once '../db.php';

// Fetch live metrics
$destCount = $pdo->query("SELECT COUNT(*) FROM destinations")->fetchColumn();
$hotelCount = $pdo->query("SELECT COUNT(*) FROM hotels")->fetchColumn();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$bookingCount = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();

$waterStats = $pdo->query("SELECT SUM(water_saved_liters) as total_water, SUM(power_saved_kwh) as total_power FROM hotels")->fetch(PDO::FETCH_ASSOC);
$totalWater = $waterStats['total_water'] ?? 0;
$totalPower = $waterStats['total_power'] ?? 0;

// Fetch latest bookings
$recentBookings = $pdo->query("SELECT b.*, h.name as hotel_name, u.name as user_name 
    FROM bookings b 
    JOIN hotels h ON b.hotel_id = h.id 
    JOIN users u ON b.user_id = u.id 
    ORDER BY b.id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Way2Green</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 2rem; }
        .stat-card { background: #ffffff; border-radius: var(--radius-md); padding: 1.6rem; border: 1px solid var(--border-soft); box-shadow: var(--shadow-card); transition: transform 0.25s; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .icon { font-size: 2rem; margin-bottom: 6px; }
        .stat-card .num { font-size: 2rem; font-weight: 800; color: var(--primary); }
        .stat-card .lbl { font-size: 0.85rem; color: var(--text-muted); font-weight: 700; }
    </style>
</head>
<body>

    <!-- Reusable Responsive Sidebar & Mobile Bar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="admin-main-content">
        <div class="admin-header-row">
            <div>
                <span style="font-size: 0.82rem; font-weight: 800; color: var(--primary-light); text-transform: uppercase;">Overview</span>
                <h1 style="color: var(--primary); font-size: 1.8rem; margin: 2px 0;">Platform Telemetry & Metrics</h1>
            </div>
            <a href="bookings.php" style="background: var(--primary); color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 99px; font-weight: 700; font-size: 0.9rem;">
                View User Bookings ➔
            </a>
        </div>

        <!-- 4 Stat Counters -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="icon">📍</div>
                <div class="num"><?= $destCount ?></div>
                <div class="lbl">Active Destinations</div>
            </div>

            <div class="stat-card">
                <div class="icon">🏨</div>
                <div class="num"><?= $hotelCount ?></div>
                <div class="lbl">Verified Eco-Stays</div>
            </div>

            <div class="stat-card">
                <div class="icon">👥</div>
                <div class="num"><?= $userCount ?></div>
                <div class="lbl">Registered Travelers</div>
            </div>

            <div class="stat-card">
                <div class="icon">📜</div>
                <div class="num"><?= $bookingCount ?></div>
                <div class="lbl">Issued Eco-Passports</div>
            </div>
        </div>

        <!-- Impact Summary Banner -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #ffffff; border-radius: var(--radius-lg); padding: 2.2rem; margin-bottom: 2.5rem; display: flex; flex-wrap: wrap; justify-content: space-around; align-items: center; gap: 20px; box-shadow: var(--shadow-card);">
            <div style="text-align: center;">
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--mint);">💧 <?= number_format($totalWater) ?> Liters</div>
                <div style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">Annual Water Preserved across Partner Resorts</div>
            </div>
            <div style="height: 50px; width: 1px; background: rgba(255,255,255,0.2);"></div>
            <div style="text-align: center;">
                <div style="font-size: 2.2rem; font-weight: 800; color: var(--mint);">⚡ <?= number_format($totalPower) ?> kWh</div>
                <div style="font-size: 0.9rem; color: rgba(255,255,255,0.85); font-weight: 600;">Clean Solar & Hydro Energy Generated</div>
            </div>
        </div>

        <!-- Recent Traveler Bookings Table -->
        <div class="admin-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 style="color: var(--primary); font-size: 1.3rem;">Recent Traveler Bookings</h2>
                <a href="bookings.php" style="color: var(--primary-light); font-weight: 700; font-size: 0.9rem; text-decoration: none;">View All Bookings ➔</a>
            </div>

            <?php if (!empty($recentBookings)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking Code</th>
                                <th>Traveler</th>
                                <th>Property</th>
                                <th>Route</th>
                                <th>CO₂ Avoided</th>
                                <th>Dates</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $b): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 800; color: var(--primary);"><?= htmlspecialchars($b['booking_code']) ?></td>
                                <td><?= htmlspecialchars($b['user_name']) ?></td>
                                <td><?= htmlspecialchars($b['hotel_name']) ?></td>
                                <td style="font-size: 0.85rem;"><?= htmlspecialchars($b['origin']) ?> ➔ <?= htmlspecialchars($b['destination']) ?></td>
                                <td style="color: #059669; font-weight: 700;">🌱 <?= $b['co2_saved_kg'] ?> kg</td>
                                <td style="font-size: 0.82rem;"><?= date('M d', strtotime($b['check_in'])) ?> – <?= date('M d, Y', strtotime($b['check_out'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2.5rem; color: var(--text-muted); font-size: 0.92rem;">
                    No bookings logged yet. Test by reserving a stay through the traveler site!
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
