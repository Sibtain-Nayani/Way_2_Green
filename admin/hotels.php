<?php
// admin/hotels.php - Smart Admin Hotel Management with File Uploads & Validation
require_once 'auth.php';
require_login();
require_once '../db.php';

$error = '';
$success = '';

// Handle Add Hotel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $destinationId = intval($_POST['destination_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $waterSaved = intval($_POST['water_saved_liters'] ?? 0);
    $powerSaved = intval($_POST['power_saved_kwh'] ?? 0);
    $ecoRating = floatval($_POST['eco_rating'] ?? 4.5);
    $price = intval($_POST['price_per_night'] ?? 3000);
    $imageUrl = trim($_POST['fallback_image_url'] ?? '');

    // Collect accessibility checkboxes
    $accList = $_POST['accessibility_tags'] ?? [];
    $customAcc = trim($_POST['custom_accessibility'] ?? '');
    if (!empty($customAcc)) $accList[] = $customAcc;
    $accessibilityTags = implode(', ', array_filter($accList));

    // Collect eco badges checkboxes
    $badgeList = $_POST['eco_badges'] ?? [];
    $customBadge = trim($_POST['custom_badge'] ?? '');
    if (!empty($customBadge)) $badgeList[] = $customBadge;
    $ecoBadges = implode(', ', array_filter($badgeList));

    // Server-Side Validation
    if ($destinationId <= 0) {
        $error = "Please select a valid destination.";
    } elseif (empty($name) || strlen($name) < 3) {
        $error = "Hotel name must be at least 3 characters long.";
    } elseif ($ecoRating < 0 || $ecoRating > 5.0) {
        $error = "Eco rating must be between 0.0 and 5.0.";
    } else {
        // Handle Image File Upload
        if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['hotel_image'];
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $fileInfo = pathinfo($file['name']);
            $ext = strtolower($fileInfo['extension'] ?? '');

            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $error = "Uploaded image exceeds 5MB limit.";
            } elseif (!in_array($ext, $allowedExts)) {
                $error = "Invalid image format. Allowed: JPG, PNG, WEBP, GIF.";
            } else {
                $uploadDir = '../uploads/hotels/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = 'hotel_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $imageUrl = 'uploads/hotels/' . $newFileName;
                } else {
                    $error = "Failed to save uploaded image.";
                }
            }
        }

        // If no upload and no URL, use attractive nature fallback
        if (empty($imageUrl)) {
            $imageUrl = 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80';
        }

        if (empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO hotels 
                    (destination_id, name, description, image_url, water_saved_liters, power_saved_kwh, accessibility_tags, eco_rating, price_per_night, eco_badges) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $destinationId,
                    $name,
                    $description,
                    $imageUrl,
                    $waterSaved,
                    $powerSaved,
                    $accessibilityTags,
                    $ecoRating,
                    $price,
                    $ecoBadges
                ]);
                $success = "Eco-Hotel added successfully!";
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Handle Delete Hotel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $hotelId = intval($_POST['id'] ?? 0);
    if ($hotelId > 0) {
        $stmt = $pdo->prepare("DELETE FROM hotels WHERE id = ?");
        $stmt->execute([$hotelId]);
        $success = "Hotel removed successfully.";
    }
}

// Fetch hotels and destinations
$hotels = $pdo->query("SELECT h.*, d.name as dest_name FROM hotels h JOIN destinations d ON h.destination_id = d.id ORDER BY h.id DESC")->fetchAll(PDO::FETCH_ASSOC);
$destinations = $pdo->query("SELECT id, name FROM destinations ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Eco-Hotels — Way2Green Admin</title>
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
                <span style="font-size: 0.82rem; font-weight: 800; color: var(--primary-light); text-transform: uppercase;">Lodging Management</span>
                <h1 style="color: var(--primary); font-size: 1.8rem; margin: 2px 0;">Manage Eco-Hotels & Sanctuaries</h1>
            </div>
            <a href="#addHotelForm" class="btn-nature-primary" style="padding: 10px 20px; font-size: 0.88rem;">+ Add New Property</a>
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

        <!-- Smart Hotel Registration Form with Image Upload -->
        <div class="admin-card" id="addHotelForm">
            <h2 style="color: var(--primary); font-size: 1.35rem; margin-bottom: 1.2rem;">Register New Eco-Friendly Hotel</h2>

            <form method="POST" enctype="multipart/form-data" onsubmit="return validateHotelForm()">
                <input type="hidden" name="action" value="add">

                <div class="grid-2">
                    <div>
                        <label class="field-label" for="destination_id">Destination Region *</label>
                        <select name="destination_id" id="destination_id" class="field-select" required>
                            <option value="">-- Choose Destination --</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?= $d['id'] ?>">📍 <?= htmlspecialchars($d['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="field-label" for="name">Hotel / Sanctuary Name *</label>
                        <input type="text" id="name" name="name" class="field-input" placeholder="e.g. Whispering Pines Bio-Retreat" required minlength="3">
                    </div>
                </div>

                <div style="margin-top: 14px;">
                    <label class="field-label" for="description">Property Description & Sustainability Practices</label>
                    <textarea id="description" name="description" class="field-input" rows="3" placeholder="Describe the property, architecture (mud/bamboo/timber), and local community engagement..."></textarea>
                </div>

                <!-- Smart Image Upload & Preview -->
                <div class="grid-2" style="margin-top: 14px;">
                    <div>
                        <label class="field-label" for="hotel_image">Upload Hotel Photo (JPG, PNG, WEBP - Max 5MB)</label>
                        <input type="file" id="hotel_image" name="hotel_image" class="field-input" accept="image/*" onchange="previewImage(event)">
                        <div style="margin-top: 6px; font-size: 0.8rem; color: var(--text-muted);">
                            Or provide an online image URL:
                        </div>
                        <input type="url" name="fallback_image_url" class="field-input" placeholder="https://images.unsplash.com/..." style="margin-top: 4px; padding: 10px;">
                    </div>

                    <div>
                        <label class="field-label">Image Live Preview</label>
                        <div class="img-preview-box" id="previewContainer">
                            <span style="color: var(--text-muted); font-size: 0.85rem;" id="previewPlaceholder">No photo selected</span>
                            <img id="previewImg" src="" alt="Preview" style="display: none;">
                        </div>
                    </div>
                </div>

                <!-- Numerical Metrics -->
                <div class="grid-2" style="margin-top: 14px;">
                    <div>
                        <label class="field-label" for="water_saved_liters">💧 Annual Water Saved (Liters)</label>
                        <input type="number" id="water_saved_liters" name="water_saved_liters" class="field-input" value="120000" min="0">
                    </div>

                    <div>
                        <label class="field-label" for="power_saved_kwh">⚡ Annual Clean Power Generated (kWh)</label>
                        <input type="number" id="power_saved_kwh" name="power_saved_kwh" class="field-input" value="28000" min="0">
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 14px;">
                    <div>
                        <label class="field-label" for="eco_rating">Eco Rating (0.0 to 5.0)</label>
                        <input type="number" step="0.1" min="0" max="5" id="eco_rating" name="eco_rating" class="field-input" value="4.8">
                    </div>

                    <div>
                        <label class="field-label" for="price_per_night">Nightly Rate (₹)</label>
                        <input type="number" id="price_per_night" name="price_per_night" class="field-input" value="3400" min="500">
                    </div>
                </div>

                <!-- Smart Accessibility Checkboxes -->
                <div style="margin-top: 16px;">
                    <label class="field-label">♿ Verified Universal Accessibility Features:</label>
                    <div class="check-grid">
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Wheelchair Accessible, Step-free Entry" checked> ♿ Wheelchair & Step-free</label>
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Sensory Quiet Rooms"> 🧠 Sensory Quiet Rooms</label>
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Braille Signage & Menus"> 👁️ Braille Signage & Menus</label>
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Elevator & Wide Doorways"> 🚪 Elevator & Wide Doors</label>
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Guide Dog Friendly"> 🐕 Guide Dog Friendly</label>
                        <label class="check-pill"><input type="checkbox" name="accessibility_tags[]" value="Roll-in Shower with Grab Bars"> 🚿 Roll-in Accessible Shower</label>
                    </div>
                    <input type="text" name="custom_accessibility" class="field-input" placeholder="Any additional accessibility feature (optional)">
                </div>

                <!-- Smart Eco Badges Checkboxes -->
                <div style="margin-top: 16px;">
                    <label class="field-label">🌱 Sustainability Credentials & Practices:</label>
                    <div class="check-grid">
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="100% Solar & Renewable" checked> ☀️ 100% Solar / Renewable</label>
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="Zero Single-Use Plastic" checked> 🚫 Zero Single-Use Plastic</label>
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="Rainwater Harvesting"> 🌧️ Rainwater Harvesting</label>
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="Organic Farm-to-Table"> 🥗 Organic Farm-to-Table</label>
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="Certified Net Zero"> 🌿 Certified Net-Zero</label>
                        <label class="check-pill"><input type="checkbox" name="eco_badges[]" value="Wildlife Corridor Protection"> 🦌 Wildlife Protection</label>
                    </div>
                    <input type="text" name="custom_badge" class="field-input" placeholder="Any additional eco badge (optional)">
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn-nature-primary" style="padding: 14px 32px;">
                        Save & Publish Property ➔
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing Hotels Catalog Table -->
        <div class="admin-card">
            <h2 style="color: var(--primary); font-size: 1.35rem; margin-bottom: 1rem;">Existing Properties Catalog (<?= count($hotels) ?>)</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Property Name</th>
                            <th>Destination</th>
                            <th>Rating</th>
                            <th>Water & Energy</th>
                            <th>Nightly</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hotels as $h): ?>
                        <tr>
                            <td>
                                <img src="../<?= htmlspecialchars($h['image_url']) ?>" alt="Hotel" style="width: 54px; height: 54px; border-radius: 8px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=150&q=80'">
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($h['name']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($h['dest_name']) ?></td>
                            <td>★ <?= htmlspecialchars($h['eco_rating']) ?></td>
                            <td style="font-size: 0.82rem;">
                                💧 <?= number_format($h['water_saved_liters']) ?>L<br>
                                ⚡ <?= number_format($h['power_saved_kwh']) ?> kWh
                            </td>
                            <td>₹<?= number_format($h['price_per_night'] ?? 3000) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to remove this property?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #dc2626; font-weight: 700; cursor: pointer;">
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

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('previewImg');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    document.getElementById('previewPlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        function validateHotelForm() {
            const name = document.getElementById('name').value.trim();
            const dest = document.getElementById('destination_id').value;
            if (!dest) {
                alert("Please select a destination.");
                return false;
            }
            if (name.length < 3) {
                alert("Please enter a valid hotel name (minimum 3 characters).");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
