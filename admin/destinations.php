<?php
// admin/destinations.php - Way2Green Destinations Management
require_once 'auth.php';
require_login();
require_once '../db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['name'] ?? '');
        $lat = floatval($_POST['lat'] ?? 0);
        $lng = floatval($_POST['lng'] ?? 0);

        if (empty($name) || strlen($name) < 2) {
            $error = "Please enter a valid destination name.";
        } elseif ($lat == 0 && $lng == 0) {
            $error = "Please enter valid GPS coordinates (Latitude & Longitude).";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO destinations (name, latitude, longitude) VALUES (?, ?, ?)");
                $stmt->execute([$name, $lat, $lng]);
                $success = "Destination '$name' added successfully!";
            } catch (Exception $e) {
                $error = "Error adding destination: " . $e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM destinations WHERE id = ?");
                $stmt->execute([$id]);
                $success = "Destination deleted successfully.";
            } catch (Exception $e) {
                $error = "Could not delete destination.";
            }
        }
    }
}

$destinations = $pdo->query("SELECT d.*, COUNT(h.id) as hotel_count 
    FROM destinations d 
    LEFT JOIN hotels h ON d.id = h.destination_id 
    GROUP BY d.id 
    ORDER BY d.name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations — Way2Green Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <!-- Reusable Responsive Sidebar & Mobile Bar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content Area -->
    <main class="admin-main-content">
        <div class="admin-header-row">
            <div>
                <span style="font-size: 0.82rem; font-weight: 800; color: var(--primary-light); text-transform: uppercase;">Regions & Corridors</span>
                <h1 style="color: var(--primary); font-size: 1.8rem; margin: 2px 0;">Manage Eco-Destinations</h1>
            </div>
            <a href="hotels.php" class="btn-nature-primary" style="font-size: 0.88rem; padding: 10px 20px;">
                Manage Hotels ➔
            </a>
        </div>

        <?php if (!empty($success)): ?>
            <div style="background: #e8f5e9; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 700;">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 700;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Add Destination Form -->
        <div class="admin-card">
            <h2 style="color: var(--primary); font-size: 1.3rem; margin-bottom: 1.2rem;">Add New Eco-Destination</h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="grid-3">
                    <div>
                        <label class="field-label" for="name">Region / City Name *</label>
                        <input type="text" id="name" name="name" class="field-input" placeholder="e.g. Coorg, Karnataka" required minlength="2">
                    </div>
                    <div>
                        <label class="field-label" for="lat">Latitude (GPS) *</label>
                        <input type="number" step="0.000001" id="lat" name="lat" class="field-input" placeholder="e.g. 12.3375" required>
                    </div>
                    <div>
                        <label class="field-label" for="lng">Longitude (GPS) *</label>
                        <input type="number" step="0.000001" id="lng" name="lng" class="field-input" placeholder="e.g. 75.8069" required>
                    </div>
                </div>

                <div style="margin-top: 18px;">
                    <button type="submit" class="btn-nature-primary">
                        + Register Destination
                    </button>
                </div>
            </form>
        </div>

        <!-- Destination List Table -->
        <div class="admin-card">
            <h2 style="color: var(--primary); font-size: 1.3rem; margin-bottom: 1rem;">Registered Eco-Destinations (<?= count($destinations) ?>)</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Destination Name</th>
                            <th>GPS Coordinates</th>
                            <th>Linked Stays</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($destinations as $d): ?>
                        <tr>
                            <td>#<?= $d['id'] ?></td>
                            <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                            <td style="font-family: monospace; font-size: 0.88rem; color: var(--text-muted);"><?= htmlspecialchars($d['latitude']) ?>, <?= htmlspecialchars($d['longitude']) ?></td>
                            <td><span class="badge-tag-eco"><?= $d['hotel_count'] ?> Eco-Stays</span></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Deleting this destination will also remove associated hotels. Continue?');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #dc2626; font-weight: 700; cursor: pointer; padding: 4px 8px;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
