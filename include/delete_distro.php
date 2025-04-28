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
        
        // Allow owner or admin (user_id 1) to delete
        if ($distro['added_by'] == $user_id || $user_id == 1) {
            // Usuwanie pliku logo, jeśli istnieje
            if (!empty($distro['logo_path']) && file_exists('../' . $distro['logo_path'])) {
                unlink('../' . $distro['logo_path']);
            }
            // Usuwanie dystrybucji z bazy
            $sql = "DELETE FROM distributions WHERE id = $id";
            if ($conn->query($sql)) {
                header("Location: ../index.php?status=success&message=" . urlencode("Dystrybucja została usunięta."));
            } else {
                header("Location: ../index.php?status=error&message=" . urlencode("Błąd podczas usuwania: " . $conn->error));
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