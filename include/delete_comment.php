<?php
// Zaczynamy sesję, żeby wiedzieć, kto jest zalogowany
session_start();

// Sprawdzamy, czy użytkownik jest zalogowany. Jeśli nie, przekierowujemy go do logowania
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $comment_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Pobieramy komentarz, żeby sprawdzić, czy należy do użytkownika
    $check_sql = "SELECT c.*, d.id AS distro_id FROM comments c JOIN distributions d ON c.distro_id = d.id WHERE c.id = $comment_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $comment = $check_result->fetch_assoc();
        $distro_id = $comment['distro_id'];
        // Sprawdzamy, czy użytkownik jest właścicielem komentarza lub admin
        if ($comment['user_id'] == $user_id || $user_id == 1) {
            // Użytkownik jest właścicielem komentarza, więc pozwalamy na usunięcie
            $sql = "DELETE FROM comments WHERE id = $comment_id";
            
            if ($conn->query($sql)) {
                // Komentarz usunięty pomyślnie
                header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie usunięty."));
            } else {
                // Coś poszło nie tak przy usuwaniu komentarza
                header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd podczas usuwania komentarza: " . $conn->error));
            }
        } else {
            // Użytkownik nie jest właścicielem komentarza, więc nie może go usunąć
            header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Nie masz uprawnień do usunięcia tego komentarza."));
        }
    } else {
        // Nie znaleziono komentarza o podanym ID
        header("Location: ../index.php?status=error&message=" . urlencode("Nie znaleziono komentarza."));
    }
} else {
    // Jeśli ktoś próbuje wejść tu bezpośrednio, odsyłamy go na stronę główną
    header("Location: ../index.php");
}

$conn->close();
?>