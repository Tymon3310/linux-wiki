<?php
// Zaczynamy sesję, żeby wiedzieć, kto jest zalogowany
session_start();

// Włączamy wyświetlanie błędów, żeby łatwiej było znaleźć problemy
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';
require_once __DIR__ . '/validation_utils.php'; // Dołączamy plik z funkcją walidacji emoji

// Sprawdzamy, czy użytkownik jest zalogowany. Jeśli nie, wracamy do logowania i zapamiętujemy, gdzie był
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("details.php?id=" . $_POST['distro_id']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobieramy dane z formularza i czyścimy je, żeby było bezpiecznie
    $distro_id = mysqli_real_escape_string($conn, $_POST['distro_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Walidacja emoji w komentarzu
    if (contains_emoji($comment)) {
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Komentarz nie może zawierać emoji."));
        exit;
    }
    
    // Pobieramy nazwę użytkownika z sesji
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Dodajemy komentarz do bazy razem z informacją o użytkowniku
    $sql = "INSERT INTO comments (distro_id, user_id, username, comment) VALUES ('$distro_id', '$user_id', '$username', '$comment')";

    if (mysqli_query($conn, $sql)) {
        // Sukces! Komentarz został dodany
        header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie dodany."));
    } else {
        // Coś poszło nie tak przy dodawaniu komentarza
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
    }
    
    exit;
} else {
    // Formularz nie został przesłany poprawnie
}

// Jeśli ktoś próbuje wejść tu bezpośrednio, odsyłamy go na stronę główną
header("Location: ../index.php");
exit;
?>