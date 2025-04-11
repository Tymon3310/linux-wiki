<?php
// Start session to get user information
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $comment_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Get the comment to verify ownership
    $check_sql = "SELECT c.*, d.id AS distro_id FROM comments c JOIN distributions d ON c.distro_id = d.id WHERE c.id = $comment_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $comment = $check_result->fetch_assoc();
        $distro_id = $comment['distro_id'];
        
        // Check if user owns the comment
        if ($comment['user_id'] == $user_id) {
            // User owns the comment, allow deletion
            $sql = "DELETE FROM comments WHERE id = $comment_id";
            
            if ($conn->query($sql)) {
                header("Location: ../details.php?id=$distro_id&status=success&message=" . urlencode("Komentarz został pomyślnie usunięty."));
            } else {
                header("Location: ../details.php?id=$distro_id&status=error&message=" . urlencode("Błąd podczas usuwania komentarza: " . $conn->error));
            }
        } else {
            // User doesn't own the comment
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