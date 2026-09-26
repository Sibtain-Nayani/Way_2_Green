<?php
// db.php - Database Connection Setup with Production & Local Fallback
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'way2green';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

// Optional external config file for deployment (e.g. InfinityFree / cPanel / Shared Hosting)
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:24px; border:2px solid #ef4444; border-radius:12px; background:#fef2f2; color:#991b1b;'>
        <h3 style='margin-top:0;'>⚠️ Database Connection Failed</h3>
        <p>Please check your database credentials in <code>db.php</code> or provide environment variables.</p>
        <p style='font-size:0.85rem; color:#6b7280;'>Error details: " . htmlspecialchars($e->getMessage()) . "</p>
    </div>");
}
?>
