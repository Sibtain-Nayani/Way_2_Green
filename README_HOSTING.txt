========================================================================
Way2Green - Quick Hosting Guide (InfinityFree / cPanel / Apache)
========================================================================
Mahdi edition
1. CREATE DATABASE
   - In your hosting control panel, create a new MySQL database.
   - Open phpMyAdmin for that database.
   - Click "Import" and upload 'way2green_database.sql' (or paste its content in the SQL tab).

2. CONFIGURE DATABASE CONNECTION
   - Create a file named 'config.php' in the root directory (or rename 'config.example.php' to 'config.php').
   - Enter your hosting database credentials:
     <?php
     $host = 'sqlXXX.infinityfree.com'; // Your MySQL Hostname
     $dbname = 'if0_xxxxxxx_way2green'; // Your Database Name
     $username = 'if0_xxxxxxx';          // Your Database Username
     $password = 'your_account_password'; // Your Database Password
     ?>

3. DEFAULT CREDENTIALS
   - Admin Portal: http://<your-domain>/admin/login.php
     * Username: admin
     * Password: hackathon2026
   - Traveler Demo Account:
     * Email: traveler@way2green.com
     * Password: password (or register any new traveler account)

4. DONE!
   Visit http://<your-domain>/ and enjoy Way2Green!
========================================================================
