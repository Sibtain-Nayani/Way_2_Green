$files = Get-ChildItem -Path . -Filter *.php
foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    
    # Replace Logo
    $content = $content -replace '(?s)<a href="index\.php" class="brand">\s*<span class="brand-leaf">🌱</span>\s*<span>Way2Green</span>\s*</a>', '<a href="index.php" class="brand"><img src="assets/img/logo.png" alt="Way2Green Logo" style="height: 32px; width: auto;"></a>'
    
    # Rebuild nav based on file
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
            
            $newNav = "<nav class=`"desktop-nav`">
            <a href=`"index.php`"$activeHome>Home</a>
            <a href=`"hotels.php`"$activeStays>Eco-Stays</a>
            <a href=`"travel.php`"$activeTransit>Plan Transit</a>
            <a href=`"about.php`"$activeAbout>About Us</a>
            <a href=`"my-trips.php`"$activePassports>My Passports</a>
            <a href=`"logout.php`" style=`"color: #dc2626;`">Sign Out</a>"
            
            $content = $content.Substring(0, $navStart) + $newNav + $content.Substring($navEnd)
        }
    }
    
    Set-Content -Path $file.FullName -Value $content
}
