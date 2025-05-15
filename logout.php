<?php
// Rozpoczęcie sesji dla uwierzytelniania użytkowników
session_start();

// Wyczyść wszystkie zmienne sesji
$_SESSION = array();

// Jeśli używane jest ciasteczko sesji, zniszcz je
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Zniszcz sesję
session_destroy();

// Przekieruj na stronę główną
header("Location: index.php");
exit;
?>