<?php

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $comment_id = intval($_POST['id']);

    $check_sql = "SELECT * FROM comments WHERE id = $comment_id";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $comment_data = mysqli_fetch_assoc($check_result);
        $distro_id = $comment_data['distro_id'];
        
        // Usuń komentarz z bazy danych
        $sql = "DELETE FROM comments WHERE id = $comment_id";

        if (mysqli_query($conn, $sql)) {
            header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie usunięty."));
        } else {
            header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd podczas usuwania: " . mysqli_error($conn)));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Komentarz o podanym ID nie istnieje."));
    }
} else {
    // Przekieruj jeśli dostęp bezpośredni bez właściwych parametrów
    header("Location: ../index.php?status=error&message=" . urlencode("Nieprawidłowe żądanie usunięcia."));
}

mysqli_close($conn);