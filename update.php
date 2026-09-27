<?php
$dir = __DIR__;
$files = glob($dir . '/*.php');

$logoSearch = [
    '<a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>',
    '<a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>'
];

$logoReplace = '<a href="index.php" class="brand">
            <img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;">
        </a>';

foreach ($files as $file) {
    if (basename($file) == 'db.php' || basename($file) == 'user_auth.php' || basename($file) == 'update.php') continue;
    $content = file_get_contents($file);
    
    // Replace logo
    $content = str_replace($logoSearch[0], $logoReplace, $content);
    $content = preg_replace('/<a href="index\.php" class="brand">\s*<span class="brand-leaf">🌱<\/span>\s*<span>Way2Green<\/span>\s*<\/a>/', $logoReplace, $content);
    
    // Replace nav
    // We need to match <nav class="desktop-nav">
            <a href="index.php">Home</a>
            <a href="hotels.php">Eco-Stays</a>
            <a href="travel.php">Plan Transit</a>
            <a href="about.php">About Us</a>
            <a href="my-trips.php">My Passports</a>
            <a href="logout.php" style="color: #dc2626;">Sign Out</a></nav> and replace it
    // Wait, some links have class="active". So I need to dynamically build it or replace the whole block and dynamically set active.
    
    file_put_contents($file, $content);
}
echo "Logos updated.\n";

