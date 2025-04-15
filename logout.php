<?php
// Rozpoczęcie sesji dla uwierzytelniania użytkowników
session_start();

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, destroy the cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Destroy the session
session_destroy();

// Redirect to the homepage
header("Location: index.php");
exit;
?>