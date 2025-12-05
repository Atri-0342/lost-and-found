<?php
session_name("admin_session");

session_start();

// Clear session data
$_SESSION = [];

// Destroy session
session_destroy();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
// Redirect to login page
header("Location: login.php");
exit;
?>