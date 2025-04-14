<?php
// Rozpoczęcie sesji, aby uzyskać informacje o użytkowniku
session_start();

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $comment_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Pobranie komentarza do weryfikacji właściciela
    $check_sql = "SELECT c.*, d.id AS distro_id FROM comments c JOIN distributions d ON c.distro_id = d.id WHERE c.id = $comment_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $comment = $check_result->fetch_assoc();
        $distro_id = $comment['distro_id'];
        
        // Sprawdzenie czy użytkownik jest właścicielem komentarza
        if ($comment['user_id'] == $user_id) {
            // Użytkownik jest właścicielem komentarza, zezwól na usunięcie
            $sql = "DELETE FROM comments WHERE id = $comment_id";
            
            if ($conn->query($sql)) {
                header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie usunięty."));
            } else {
                header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd podczas usuwania komentarza: " . $conn->error));
            }
        } else {
            // Użytkownik nie jest właścicielem komentarza
            header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Nie masz uprawnień do usunięcia tego komentarza."));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie znaleziono komentarza."));
    }
} else {
    header("Location: ../index.php");
}

$conn->close();
?>