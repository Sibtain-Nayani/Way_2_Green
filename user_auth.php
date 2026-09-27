<?php
// user_auth.php - Traveler Authentication & Session Helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_user_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_logged_in_user() {
    if (!is_user_logged_in()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'Traveler',
        'email' => $_SESSION['user_email'] ?? ''
    ];
}

function require_user_login($redirectBack = '') {
    if (!is_user_logged_in()) {
        $target = 'login.php';
        if (!empty($redirectBack)) {
            $target .= '?go=' . urlencode($redirectBack);
        }
        header("Location: $target");
        exit;
    }
}
?>

