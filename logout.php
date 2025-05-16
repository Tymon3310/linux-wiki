<?php
// Rozpoczęcie sesji w celu zarządzania stanem zalogowania użytkownika
session_start();

// Usunięcie wszystkich zmiennych sesji
$_SESSION = array();

// Jeśli używane jest ciasteczko sesyjne, usunięcie go
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// Zniszczenie sesji
session_destroy();

// Przekierowanie użytkownika na stronę główną po wylogowaniu
header("Location: index.php");
exit;
?>