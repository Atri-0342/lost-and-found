<?php
session_name("student_session");

session_start();

// Clear user session
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// Destroy session completely if no other session variables exist
if (empty($_SESSION)) {
    session_destroy();
}

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Redirect to user login page
header("Location: user_login.php");
exit;
?>
