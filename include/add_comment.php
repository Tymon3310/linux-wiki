<?php

// Włącz raportowanie błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobierz dane z formularza i oczyść je
    $distro_id = mysqli_real_escape_string($conn, $_POST['distro_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Since we want to allow multiple comments per distribution, let's modify the SQL query
    $sql = "INSERT INTO comments (distro_id, username, comment) VALUES ('$distro_id', '$username', '$comment')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie dodany."));
    } else {
        header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
    }
    
    exit;
} else {
    // Debugowanie - formularz nie został przesłany poprawnie
    //error_log("Form not submitted or wrong method");
}

// Przekieruj jeśli dostęp bezpośredni
header("Location: ../index.php");
exit;
?>