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
    $id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if user has permission to delete this distro
    $check_sql = "SELECT added_by, logo_path FROM distributions WHERE id = $id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $distro = $check_result->fetch_assoc();
        
        // Check if user owns the distro
        if ($distro['added_by'] == $user_id) {
            // User owns the distro, delete it
            $sql = "DELETE FROM distributions WHERE id = $id";
            
            if ($conn->query($sql)) {
                // Delete the logo file if it exists
                $logo_path = "../" . $distro['logo_path'];
                if (file_exists($logo_path) && !strpos($logo_path, "default")) {
                    unlink($logo_path);
                }
                
                // Also delete related comments
                $conn->query("DELETE FROM comments WHERE distro_id = $id");
                
                header("Location: ../index.php?status=success&message=" . urlencode("Dystrybucja została pomyślnie usunięta."));
            } else {
                header("Location: ../index.php?status=error&message=" . urlencode("Błąd podczas usuwania dystrybucji: " . $conn->error));
            }
        } else {
            // User doesn't own the distro
            header("Location: ../index.php?status=error&message=" . urlencode("Nie masz uprawnień do usunięcia tej dystrybucji."));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji o podanym identyfikatorze."));
    }
} else {
    header("Location: ../index.php");
}

$conn->close();
?>