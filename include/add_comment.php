<?php
// Rozpoczęcie sesji, aby uzyskać informacje o użytkowniku
session_start();

// Włącz raportowanie błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("details.php?id=" . $_POST['distro_id']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobierz dane z formularza i oczyść je
    $distro_id = mysqli_real_escape_string($conn, $_POST['distro_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Pobranie nazwy użytkownika z sesji
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Dodanie komentarza z identyfikatorem użytkownika
    $sql = "INSERT INTO comments (distro_id, user_id, username, comment) VALUES ('$distro_id', '$user_id', '$username', '$comment')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie dodany."));
    } else {
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
    }
    
    exit;
} else {
    // Debugowanie - formularz nie został przesłany poprawnie
    //error_log("Formularz nie został przesłany lub użyto nieprawidłowej metody");
}

// Przekieruj jeśli dostęp bezpośredni
header("Location: ../index.php");
exit;
?>