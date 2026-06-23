<?php
// Start session securely if not already active
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// THIS IS THE MISSING FUNCTION PHP IS CRASHING FOR:
function check_auth() {
    if (!isset($_SESSION['user_id'])) {
        // Since index.php is in the root, it goes to auth/login.php directly
        header("Location: auth/login.php");
        exit();
    }
}
?>