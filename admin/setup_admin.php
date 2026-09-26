<?php
// RUN THIS ONCE TO CREATE THE DEFAULT ADMIN, THEN DELETE IT
require_once '../db.php';

$username = 'admin';
$password = 'hackathon2026';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    echo "Admin user created successfully! Username: admin | Password: hackathon2026";
} catch (PDOException $e) {
    echo "Error (user might already exist): " . $e->getMessage();
}
?>
