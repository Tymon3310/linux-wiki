<?php
// Dołącz konfigurację bazy danych
require_once 'db_config.php';

// Przetwarzanie żądania usunięcia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Najpierw, pobierz informacje o dystrybucji aby usunąć plik loga
    $check_sql = "SELECT * FROM distributions WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $distro = mysqli_fetch_assoc($check_result);
        $logo_path = $distro['logo_path'];
        $name = $distro['name'];
        
        // Usuń z bazy danych
        $sql = "DELETE FROM distributions WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            // Usuń logo po pomyślnym usunięciu z bazy danych
            $full_logo_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $logo_path;
            
            // Debugowanie - zapisz ścieżkę do loga
            error_log("Próba usunięcia pliku: " . $full_logo_path);
            
            if (file_exists($full_logo_path)) {
                if (unlink($full_logo_path)) {
                    error_log("Plik usunięty pomyślnie: " . $full_logo_path);
                } else {
                    error_log("Nie można usunąć pliku: " . $full_logo_path . " - " . error_get_last()['message']);
                }
            } else {
                // Sprawdź ścieżkę bezwzględną bez $_SERVER['DOCUMENT_ROOT']
                $alternative_path = dirname(dirname(__FILE__)) . "/" . $logo_path;
                error_log("Plik nie istnieje pod ścieżką: " . $full_logo_path . ". Próba alternatywnej ścieżki: " . $alternative_path);
                
                if (file_exists($alternative_path)) {
                    if (unlink($alternative_path)) {
                        error_log("Plik usunięty pomyślnie z alternatywnej ścieżki: " . $alternative_path);
                    } else {
                        error_log("Nie można usunąć pliku z alternatywnej ścieżki: " . $alternative_path . " - " . error_get_last()['message']);
                    }
                } else {
                    error_log("Plik nie istnieje również pod alternatywną ścieżką: " . $alternative_path);
                }
            }
            
            header("Location: ../index.php?status=success&message=" . urlencode("Dystrybucja '$name' została pomyślnie usunięta."));
        } else {
            header("Location: ../details.php?id=$id&status=error&message=" . urlencode("Błąd podczas usuwania: " . mysqli_error($conn)));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Dystrybucja o podanym ID nie istnieje."));
    }
} else {
    // Przekieruj jeśli dostęp bezpośredni bez właściwych parametrów
    header("Location: ../index.php?status=error&message=" . urlencode("Nieprawidłowe żądanie usunięcia."));
}

// Zamknij połączenie z bazą danych
mysqli_close($conn);
?>