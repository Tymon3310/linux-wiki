<?php
// Rozpoczęcie sesji, aby uzyskać informacje o zalogowanym użytkowniku
session_start();

// Włączenie wyświetlania wszystkich błędów w celu łatwiejszego debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';
require_once __DIR__ . '/validation_utils.php'; // Dołączenie pliku z funkcją walidacji emoji

// Sprawdzenie, czy użytkownik jest zalogowany. Jeśli nie, przekierowanie do strony logowania z zapamiętaniem poprzedniej lokalizacji
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("details.php?id=" . $_POST['distro_id']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobranie danych z formularza i ich oczyszczenie w celu zapewnienia bezpieczeństwa
    $distro_id = mysqli_real_escape_string($conn, $_POST['distro_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // Walidacja obecności emoji w komentarzu
    if (contains_emoji($comment)) {
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Komentarz nie może zawierać emoji."));
        exit;
    }
    
    // Pobranie ID i nazwy użytkownika z sesji
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Dodanie komentarza do bazy danych wraz z informacjami o użytkowniku
    $sql = "INSERT INTO comments (distro_id, user_id, username, comment) VALUES ('$distro_id', '$user_id', '$username', '$comment')";

    if (mysqli_query($conn, $sql)) {
        // Sukces! Komentarz został pomyślnie dodany
        header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie dodany."));
    } else {
        // Wystąpił błąd podczas dodawania komentarza
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
    }
    
    exit;
} else {
    // Formularz nie został przesłany metodą POST.
}

// Jeśli skrypt został wywołany bezpośrednio, przekierowanie na stronę główną
header("Location: ../index.php");
exit;
?>