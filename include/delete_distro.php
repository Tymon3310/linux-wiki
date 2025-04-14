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
    $id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // Sprawdzenie czy użytkownik ma uprawnienia do usunięcia tej dystrybucji
    $check_sql = "SELECT added_by, logo_path FROM distributions WHERE id = $id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $distro = $check_result->fetch_assoc();
        
        // Sprawdzenie czy użytkownik jest właścicielem dystrybucji
        if ($distro['added_by'] == $user_id) {
            // Użytkownik jest właścicielem dystrybucji, usuń ją
            $sql = "DELETE FROM distributions WHERE id = $id";
            
            if ($conn->query($sql)) {
                // Usuń plik logo jeśli istnieje
                $logo_path = "../" . $distro['logo_path'];
                
                if (file_exists($logo_path) && basename($logo_path) != "default.png") {
                    unlink($logo_path);
                }
                
                // Przekierowanie z komunikatem sukcesu
                header("Location: ../index.php?status=success&message=" . urlencode("Dystrybucja została pomyślnie usunięta."));
            } else {
                header("Location: ../index.php?status=error&message=" . urlencode("Błąd podczas usuwania dystrybucji: " . $conn->error));
            }
        } else {
            // Użytkownik nie jest właścicielem dystrybucji
            header("Location: ../index.php?status=error&message=" . urlencode("Nie masz uprawnień do usunięcia tej dystrybucji."));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji."));
    }
} else {
    header("Location: ../index.php");
}

$conn->close();
?>