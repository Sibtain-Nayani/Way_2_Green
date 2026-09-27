<?php
// register.php - Traveler Sign Up
require_once 'db.php';
require_once 'user_auth.php';

if (is_user_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';
$redirect = $_GET['go'] ?? $_GET['redirect'] ?? 'travel.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An account with this email address already exists. Please sign in.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");
            if ($insert->execute([$name, $email, $hash])) {
                // Auto-login upon registration
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                header("Location: " . (!empty($redirect) ? $redirect : 'travel.php'));
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account — Way2Green</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            min-height: 85vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem 6rem;
        }
        .auth-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-soft);
            width: 100%;
            max-width: 440px;
            padding: 2.2rem;
        }
        .auth-header {
            text-align: center;
            margin-bottom: 1.8rem;
        }
        .auth-logo {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: inline-block;
            margin-bottom: 6px;
        }
        .auth-subtitle {
            color: var(--text-secondary);
            font-size: 0.92rem;
        }
        .alert-box {
            background: #fee2e2;
            border: 1px solid #f87171;
            color: #991b1b;
            padding: 10px 14px;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            margin-bottom: 1.2rem;
        }
        .form-group {
            margin-bottom: 1.1rem;
        }
        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 5px;
            color: var(--text-primary);
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #d1e7dd;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: all 0.2s;
        }
        .form-group input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(32, 201, 151, 0.15);
        }
        .btn-auth {
            width: 100%;
            background: var(--primary);
            color: #ffffff;
            border: none;
            padding: 13px;
            border-radius: var(--radius-md);
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }
        .btn-auth:hover {
            background: var(--primary-light);
        }
        .auth-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.88rem;
            color: var(--text-muted);
        }
        .auth-footer a {
            color: var(--primary-light);
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <!-- Ambient Glowing 3D Moving Scene Layer -->
    <div class="ambient-scene">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <!-- Minimal Clean Header -->
    <header class="header-top">
        <a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>
        <div style="display: flex; gap: 14px; align-items: center;">
            <a href="index.php" style="text-decoration: none; color: var(--text-main); font-weight: 600; font-size: 0.92rem;">Home</a>
            <a href="login.php" class="btn-nature-primary" style="padding: 8px 18px; font-size: 0.88rem;">Sign In</a>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-card card-3d">
            <div class="auth-header">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-glow) 0%, var(--primary) 100%); border-radius: 14px; color: #fff; font-size: 1.4rem; margin-bottom: 10px; box-shadow: 0 6px 16px rgba(34, 197, 94, 0.3);">
                    🌱
                </div>
                <h2 style="font-size: 1.45rem; color: var(--primary); font-weight: 800;">Join Conscious Travel</h2>
                <p class="auth-subtitle">Create an account to calculate personal carbon footprints and book verified inclusive eco-stays.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-box">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php?go=<?= urlencode($redirect) ?>">
                <div class="form-group">
                    <label for="name">Your Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Alex Morgan" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="alex@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password <span style="font-weight: normal; color: var(--text-muted);">(min. 6 chars)</span></label>
                    <input type="password" id="password" name="password" placeholder="••••••••" minlength="6" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" minlength="6" required>
                </div>

                <button type="submit" class="btn-auth">Create Account & Continue ➔</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php?go=<?= urlencode($redirect) ?>">Sign In here</a>
            </div>
        </div>
    </div>

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
        <a href="login.php" class="mobile-nav-item active">
            <span class="icon">👤</span>
            <span class="label">Sign In</span>
        </a>
    </div>

    <script src="js/effects.js"></script>
</body>
</html>

