<?php
// admin/bookings.php - User Bookings & Passport Records
require_once 'auth.php';
require_login();
require_once '../db.php';

$search = trim($_GET['search'] ?? '');

$query = "SELECT b.*, h.name as hotel_name, h.destination_id, u.name as user_name, u.email as user_email, d.name as dest_name
          FROM bookings b 
          JOIN hotels h ON b.hotel_id = h.id 
          JOIN users u ON b.user_id = u.id 
          LEFT JOIN destinations d ON h.destination_id = d.id";

$params = [];
if (!empty($search)) {
    $query .= " WHERE b.booking_code LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR h.name LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY b.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$totalCO2 = $pdo->query("SELECT SUM(co2_saved_kg) FROM bookings")->fetchColumn() ?: 0;
$totalRevenue = $pdo->query("SELECT SUM(total_price) FROM bookings")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Bookings — Way2Green Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .metric-cards-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 2rem;
        }
        .metric-card {
            background: #ffffff;
            border-radius: var(--radius-md);
            padding: 1.5rem;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 14px rgba(15, 61, 36, 0.05);
        }
        .metric-num {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            line-height: 1.1;
        }
        .metric-lbl {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 700;
            margin-top: 4px;
        }
        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
        }
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1.5px solid #d1e7dd;
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
        }
        .search-input:focus {
            border-color: var(--primary-glow);
        }
        .btn-search {
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 12px 22px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            cursor: pointer;
        }
        .code-badge {
            background: #f0fdf4;
            color: var(--primary);
            font-family: monospace;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 3px 8px;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body>

    <!-- Reusable Responsive Sidebar & Mobile Bar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="admin-main-content">
        <div class="admin-header-row">
            <div>
                <span style="font-size: 0.82rem; font-weight: 800; color: var(--primary-light); text-transform: uppercase;">Real-Time Records</span>
                <h1 style="color: var(--primary); font-size: 1.8rem; margin: 2px 0;">Recent Traveler Bookings & Passports</h1>
            </div>
            <a href="hotels.php" style="background: var(--primary); color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 99px; font-weight: 700; font-size: 0.9rem;">
                + Add Hotel Stay
            </a>
        </div>

        <!-- Metric Summary Counters -->
        <div class="metric-cards-row">
            <div class="metric-card">
                <div class="metric-num"><?= count($bookings) ?></div>
                <div class="metric-lbl">Total Verified Bookings</div>
            </div>
            <div class="metric-card">
                <div class="metric-num" style="color: #059669;"><?= number_format($totalCO2, 1) ?> kg</div>
                <div class="metric-lbl">Total Travel CO₂ Mitigated</div>
            </div>
            <div class="metric-card">
                <div class="metric-num">₹<?= number_format($totalRevenue) ?></div>
                <div class="metric-lbl">Sustainable Lodging Volume</div>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" class="search-bar">
            <input type="text" name="search" class="search-input" placeholder="Search by booking code, traveler name, email, or property..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn-search">Search</button>
            <?php if (!empty($search)): ?>
                <a href="bookings.php" style="padding: 12px 18px; background: #e2e8f0; color: var(--text-main); text-decoration: none; border-radius: var(--radius-sm); font-weight: 700;">Reset</a>
            <?php endif; ?>
        </form>

        <!-- Bookings Data Table -->
        <div class="admin-card" style="padding: 1rem;">
            <?php if (!empty($bookings)): ?>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Passport ID</th>
                                <th>Traveler</th>
                                <th>Property & Destination</th>
                                <th>Transit Route</th>
                                <th>CO₂ Saved</th>
                                <th>Stay Dates</th>
                                <th>Accessibility Notes</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>
                                    <span class="code-badge"><?= htmlspecialchars($b['booking_code']) ?></span>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        <?= date('M d, H:i', strtotime($b['created_at'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['user_name']) ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);"><?= htmlspecialchars($b['user_email']) ?></div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($b['hotel_name']) ?></strong>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">📍 <?= htmlspecialchars($b['dest_name'] ?: $b['destination']) ?></div>
                                </td>
                                <td>
                                    <div style="font-size: 0.86rem;"><?= htmlspecialchars($b['origin']) ?> ➔ <?= htmlspecialchars($b['destination']) ?></div>
                                    <div style="font-size: 0.76rem; color: var(--text-muted); text-transform: uppercase;">Via <?= htmlspecialchars($b['travel_mode']) ?> (<?= $b['distance_km'] ?> km)</div>
                                </td>
                                <td>
                                    <span style="color: #059669; font-weight: 800; font-size: 0.95rem;">
                                        🌱 <?= $b['co2_saved_kg'] ?> kg
                                    </span>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <?= date('M d, Y', strtotime($b['check_in'])) ?><br>
                                    <span style="color: var(--text-muted);">to</span> <?= date('M d, Y', strtotime($b['check_out'])) ?>
                                    <div style="font-size: 0.75rem; color: var(--primary); font-weight: 700;"><?= $b['guests'] ?> Guest(s)</div>
                                </td>
                                <td style="font-size: 0.8rem; max-width: 200px;">
                                    <?= !empty($b['accessibility_notes']) ? htmlspecialchars($b['accessibility_notes']) : '<span style="color:#94a3b8;">None</span>' ?>
                                </td>
                                <td>
                                    <strong>₹<?= number_format($b['total_price']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                    <div style="font-size: 2.5rem; margin-bottom: 8px;">📜</div>
                    <h3 style="color: var(--primary);">No Bookings Found</h3>
                    <p style="font-size: 0.9rem; margin-top: 4px;">No traveler reservations match your search criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
