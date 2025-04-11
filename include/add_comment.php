<?php
// Start session to get user information
session_start();

// Włącz raportowanie błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("details.php?id=" . $_POST['distro_id']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobierz dane z formularza i oczyść je
    $distro_id = mysqli_real_escape_string($conn, $_POST['distro_id']);
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Get username from session
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    
    // Insert comment with user_id
    $sql = "INSERT INTO comments (distro_id, user_id, username, comment) VALUES ('$distro_id', '$user_id', '$username', '$comment')";

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