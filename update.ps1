$files = Get-ChildItem -Path . -Filter *.php
foreach ($file in $files) {
    if ($file.Name -eq "update.php" -or $file.Name -eq "db.php" -or $file.Name -eq "user_auth.php") { continue }
    $content = Get-Content $file.FullName -Raw
    
    # Replace Logo
    $content = $content -replace '(?s)<a href="index\.php" class="brand">\s*<span class="brand-leaf">🌱</span>\s*<span>Way2Green</span>\s*</a>', '<a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>'
    
    # Rebuild nav based on file but keeping PHP logic
    $navStart = $content.IndexOf('<nav class="desktop-nav">')
    if ($navStart -ge 0) {
        $navEnd = $content.IndexOf('</nav>', $navStart)
        if ($navEnd -gt $navStart) {
            $filename = $file.Name
            
            $activeHome = if ($filename -eq "index.php") { ' class="active"' } else { '' }
            $activeStays = if ($filename -eq "hotels.php") { ' class="active"' } else { '' }
            $activeTransit = if ($filename -eq "travel.php") { ' class="active"' } else { '' }
            $activeAbout = if ($filename -eq "about.php") { ' class="active"' } else { '' }
            $activePassports = if ($filename -eq "my-trips.php" -or $filename -eq "passport.php") { ' class="active"' } else { '' }
            
            # Keep existing PHP blocks by replacing specific lines if they exist, or just insert the new order
            # The original structure:
            # <a href="index.php">Home</a>
            # <a href="travel.php">Plan Transit</a>
            # <a href="hotels.php">Eco-Stays</a>
            # <a href="about.php">About Us</a>
            # <?php if ($user): ? >
            # <a href="my-trips.php">My Passports</a>
            # <a href="logout.php">Sign Out</a>
            # <?php else: ? >
            # <a href="login.php">Sign In</a>
            # ...
            
            $newNav = "<nav class=`"desktop-nav`">
            <a href=`"index.php`"$activeHome>Home</a>
            <a href=`"hotels.php`"$activeStays>Eco-Stays</a>
            <a href=`"travel.php`"$activeTransit>Plan Transit</a>
            <a href=`"about.php`"$activeAbout>About Us</a>
            <?php if (`$user): ?>
                <a href=`"my-trips.php`"$activePassports>My Passports</a>
                <a href=`"logout.php`" style=`"color: #dc2626;`">Sign Out</a>
            <?php else: ?>
                <a href=`"login.php`">Sign In</a>
                <a href=`"register.php`" class=`"nav-cta`">Get Started</a>
            <?php endif; ?>"
            
            $content = $content.Substring(0, $navStart) + $newNav + $content.Substring($navEnd)
        }
    }
    
    Set-Content -Path $file.FullName -Value $content
}
