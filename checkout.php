<?php
// checkout.php - Phase 3: Reservation Confirmation & Digital Eco-Passport
require_once 'db.php';
require_once 'user_auth.php';

require_user_login('checkout.php');
$user = get_logged_in_user();

$hotelId = intval($_GET['hotel_id'] ?? 1);
$hotelName = trim($_GET['hotel_name'] ?? 'Eco Sanctuary');
$destName = trim($_GET['dest_name'] ?? 'Munnar, Kerala');
$origin = trim($_GET['origin'] ?? 'Mumbai');
$mode = trim($_GET['mode'] ?? 'train');
$distance = floatval($_GET['distance'] ?? 450);
$co2Saved = floatval($_GET['co2_saved'] ?? 84.8);
$pricePerNight = intval($_GET['price'] ?? 3200);
$waterSaved = intval($_GET['water_saved'] ?? 120000);
$powerSaved = intval($_GET['power_saved'] ?? 28000);

$bookingConfirmed = false;
$bookingCode = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $checkIn = $_POST['check_in'] ?? '';
    $checkOut = $_POST['check_out'] ?? '';
    $guests = intval($_POST['guests'] ?? 1);
    $specialRequests = trim($_POST['special_requests'] ?? '');

    // Accessibility checkboxes
    $accNotes = [];
    if (!empty($_POST['acc_wheelchair'])) $accNotes[] = 'Wheelchair Ramp / Step-free';
    if (!empty($_POST['acc_sensory'])) $accNotes[] = 'Sensory Quiet Room';
    if (!empty($_POST['acc_braille'])) $accNotes[] = 'Braille / Tactile Navigation';
    if (!empty($_POST['acc_ground'])) $accNotes[] = 'Ground Floor Preferred';
    if (!empty($specialRequests)) $accNotes[] = "Notes: $specialRequests";

    $notesStr = implode(' | ', $accNotes);

    if (empty($checkIn) || empty($checkOut)) {
        $error = "Please select both check-in and check-out dates.";
    } else {
        $d1 = new DateTime($checkIn);
        $d2 = new DateTime($checkOut);
        $nights = max(1, $d1->diff($d2)->days);
        $totalPrice = $nights * $pricePerNight;

        $bookingCode = 'W2G-' . strtoupper(substr(md5(uniqid()), 0, 6)) . '-ECO';

        try {
            $stmt = $pdo->prepare("INSERT INTO bookings 
                (user_id, hotel_id, origin, destination, travel_mode, distance_km, co2_saved_kg, check_in, check_out, guests, booking_code, total_price, accessibility_notes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['id'],
                $hotelId,
                $origin,
                $destName,
                $mode,
                $distance,
                $co2Saved,
                $checkIn,
                $checkOut,
                $guests,
                $bookingCode,
                $totalPrice,
                $notesStr
            ]);

            $bookingConfirmed = true;
            header("Location: passport.php?code=" . urlencode($bookingCode) . "&success=1");
            exit;
        } catch (Exception $e) {
            $error = "Failed to save booking. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Step 3: Eco-Passport & Confirmation — Way2Green</title>
    <meta name="description" content="Finalize your inclusive eco-stay and receive your verified carbon offset passport certificate.">
    
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

    <!-- Drawer Overlay & Mobile Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="toggleDrawer()"></div>
    <div class="mobile-drawer" id="mobileDrawer">
        <button class="drawer-close" onclick="toggleDrawer()">✕</button>
        <div style="font-weight: 800; font-size: 1.3rem; color: var(--primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 1.4rem;">🌱</span> Way2Green
        </div>
        <a href="index.php" class="drawer-link">Home</a>
        <a href="travel.php" class="drawer-link">Plan Transit</a>
        <a href="hotels.php" class="drawer-link">Eco-Stays</a>
        <a href="about.php" class="drawer-link">Our Mission</a>
        <a href="my-trips.php" class="drawer-link" style="color: var(--primary);">My Passports</a>
        <a href="logout.php" class="drawer-link" style="color: #dc2626;">Sign Out (<?= htmlspecialchars($user['name']) ?>)</a>
        <hr style="border: none; border-top: 1px solid var(--border-subtle); margin: 0.5rem 0;">
        <a href="admin/login.php" class="drawer-link" style="font-size: 0.9rem; color: var(--text-muted);">Admin Portal</a>
    </div>

    <!-- Clean Header -->
    <?php include 'components/navbar.php'; ?>

    <main class="page-container" style="max-width: 860px;">
        <!-- Visual Multi-Step Tracker -->
        

        <?php if (!$bookingConfirmed): ?>
            <!-- Checkout Form Card -->
            <div class="card-box">
                <span class="section-tag">Step 3 of 3</span>
                <h1 style="color: var(--primary); font-size: 1.8rem; margin: 4px 0 6px;">Confirm Your Sustainable Stay</h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
                    Review your low-carbon route and property details to issue your verified digital Eco-Passport.
                </p>

                <?php if (!empty($error)): ?>
                    <div style="background: #fee2e2; border: 1px solid #f87171; color: #991b1b; padding: 10px 14px; border-radius: 8px; margin-bottom: 1.2rem;">
                        ⚠️ <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Summary Breakdown Box -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 1.4rem; margin-bottom: 1.8rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 800; color: var(--primary-accent); text-transform: uppercase;">Transit Route</span>
                        <div style="font-weight: 800; color: var(--primary); font-size: 1.05rem; margin-top: 2px;">
                            <?= htmlspecialchars($origin) ?> ➔ <?= htmlspecialchars($destName) ?>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--text-muted);">
                            <?= strtoupper($mode) ?> (<?= $distance ?> km) • <strong><?= $co2Saved ?> kg CO₂ Avoided</strong>
                        </div>
                    </div>

                    <div>
                        <span style="font-size: 0.75rem; font-weight: 800; color: var(--primary-accent); text-transform: uppercase;">Accommodating Property</span>
                        <div style="font-weight: 800; color: var(--primary); font-size: 1.05rem; margin-top: 2px;">
                            <?= htmlspecialchars($hotelName) ?>
                        </div>
                        <div style="font-size: 0.82rem; color: var(--text-muted);">
                            💧 <?= number_format($waterSaved) ?>L Water Saved • ⚡ <?= number_format($powerSaved) ?> kWh Clean
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 1.2rem;">
                        <div>
                            <label class="field-label" for="check_in">Check-in Date</label>
                            <input type="date" id="check_in" name="check_in" class="field-input" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label class="field-label" for="check_out">Check-out Date</label>
                            <input type="date" id="check_out" name="check_out" class="field-input" required value="<?= date('Y-m-d', strtotime('+2 days')) ?>">
                        </div>
                    </div>

                    <div style="margin-bottom: 1.2rem;">
                        <label class="field-label" for="guests">Number of Guests</label>
                        <select id="guests" name="guests" class="field-select">
                            <option value="1">1 Traveler</option>
                            <option value="2" selected>2 Travelers</option>
                            <option value="3">3 Travelers</option>
                            <option value="4">4+ Travelers (Family/Group)</option>
                        </select>
                    </div>

                    <!-- Universal Accessibility Checklist -->
                    <div style="margin-bottom: 1.8rem; background: #ffffff; border: 1.5px solid #d1e7dd; border-radius: var(--radius-md); padding: 1.2rem;">
                        <label class="field-label" style="margin-bottom: 8px;">♿ Universal Accessibility Requirements (Zero Extra Charge)</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; font-size: 0.88rem;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="acc_wheelchair" value="1"> Step-Free / Wheelchair Ramp
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="acc_sensory" value="1"> Sensory Quiet / Low Stimulation Room
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="acc_braille" value="1"> Braille / Audio Menus & Assistance
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="checkbox" name="acc_ground" value="1"> Ground Floor Room Assignment
                            </label>
                        </div>

                        <div style="margin-top: 10px;">
                            <input type="text" name="special_requests" class="field-input" placeholder="Any specific requirements (e.g. guide dog travel, dietary allergy)?">
                        </div>
                    </div>

                    <button type="submit" class="btn-nature-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.05rem;">
                        🌿 Confirm Stay & Issue My Eco-Passport
                    </button>
                </form>
            </div>

        <?php else: ?>
            <!-- Verified Eco-Passport Certificate -->
            <div class="passport-frame card-3d hologram-sheen">
                <div class="passport-stamp">Verified Net-Zero</div>
                
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: linear-gradient(135deg, var(--primary-glow) 0%, var(--primary) 100%); border-radius: 16px; color: #fff; font-size: 1.6rem; margin-bottom: 8px; box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);">
                        🌱
                    </div>
                    <h2 style="color: var(--primary); font-size: 1.8rem; margin: 4px 0;">Official Way2Green Eco-Passport</h2>
                    <p style="color: var(--text-muted); font-size: 0.88rem;">
                        Certified Sustainable & Inclusive Hospitality Record • Global Tourism Code
                    </p>
                </div>

                <div class="passport-grid">
                    <div class="passport-field">
                        <div class="label">Eco Traveler</div>
                        <div class="val"><?= htmlspecialchars($user['name']) ?></div>
                    </div>

                    <div class="passport-field">
                        <div class="label">Booking Reference</div>
                        <div class="val" style="font-family: monospace;"><?= htmlspecialchars($bookingCode) ?></div>
                    </div>

                    <div class="passport-field">
                        <div class="label">Sanctuary Accommodation</div>
                        <div class="val"><?= htmlspecialchars($hotelName) ?></div>
                    </div>

                    <div class="passport-field">
                        <div class="label">Destination Corridor</div>
                        <div class="val"><?= htmlspecialchars($destName) ?></div>
                    </div>

                    <div class="passport-field">
                        <div class="label">Transit Carbon Mitigated</div>
                        <div class="val" style="color: var(--primary-accent); font-weight: 800;"><?= $co2Saved ?> kg CO₂ Avoided</div>
                    </div>

                    <div class="passport-field">
                        <div class="label">Resource Stewardship</div>
                        <div class="val">💧 <?= number_format($waterSaved) ?>L Water Preserved</div>
                    </div>
                </div>

                <div style="background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 12px; padding: 14px 18px; text-align: center; font-size: 0.9rem; color: var(--primary); margin-bottom: 1.5rem;">
                    <strong>Verified Carbon Savings:</strong> By choosing low-carbon transit and a certified eco-resort, your travel footprint is over <strong>80% lower</strong> than standard tourism.
                </div>

                <div class="no-print" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;">
                    <a href="passport.php?code=<?= urlencode($bookingCode) ?>&print=1" class="btn-nature-primary">
                        🖨️ Print / Save Eco-Passport (1-Page PDF)
                    </a>
                    <a href="passport.php?code=<?= urlencode($bookingCode) ?>" class="btn-nature-outline">
                        📜 View Full Digital Passport ➔
                    </a>
                    <a href="my-trips.php" class="btn-nature-outline">
                        View All My Trips
                    </a>
                </div>
            </div>
        <?php endif; ?>
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
                    <a href="hotels.php">Phase 2: Eco-Hotels</a>
                    <a href="my-trips.php">My Passports</a>
                </div>
            </div>
            <div>
                <h4 class="footer-heading">Account</h4>
                <div class="footer-links">
                    <a href="my-trips.php">Signed in as <?= htmlspecialchars($user['name']) ?></a>
                    <a href="logout.php">Sign Out</a>
                </div>
            </div>
        </div>
        <div class="footer-copyright">
            © 2026 Way2Green • Built for Hackathon 2026.
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
        <a href="hotels.php" class="mobile-nav-item">
            <span class="icon">🏨</span>
            <span class="label">Stays</span>
        </a>
        <a href="about.php" class="mobile-nav-item">
            <span class="icon">🌿</span>
            <span class="label">About</span>
        </a>
        <a href="my-trips.php" class="mobile-nav-item active">
            <span class="icon">📜</span>
            <span class="label">Passport</span>
        </a>
    </div>

    <script src="js/effects.js"></script>
    <script>
        function toggleDrawer() {
            document.getElementById('mobileDrawer').classList.toggle('open');
            document.getElementById('drawerOverlay').classList.toggle('active');
        }
    </script>
</body>
</html>


