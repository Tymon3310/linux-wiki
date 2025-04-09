<?php
// Włącz raportowanie błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dołącz konfigurację bazy danych
require_once 'db_config.php';

// Przetwarzanie wysłanego formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobierz dane z formularza i oczyść je
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Sprawdź czy pole do strony www istnieje i oczyść je
    $website = isset($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : '';

    $youtube = isset($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : '';
    
    // Obsługa przesyłania pliku
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/img/";
    
    // Debugowanie - sprawdź czy mamy plik
    //error_log("File info: " . print_r($_FILES, true));
    
    // Sprawdź czy katalog docelowy istnieje, jeśli nie, spróbuj go utworzyć
    if (!file_exists($target_dir)) {
        if (!mkdir($target_dir, 0777, true)) {
            header("Location: ../index.php?status=error&message=" . urlencode("Nie można utworzyć katalogu docelowego dla przesłanego pliku."));
            exit;
        }
    }
    
    // Sprawdź czy mamy plik do przesłania
    if (!isset($_FILES["logo"]) || $_FILES["logo"]["error"] === UPLOAD_ERR_NO_FILE) {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie wybrano pliku logo."));
        exit;
    }
    
    // Generowanie unikalnej nazwy pliku
    $file_extension = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
    $file_name = preg_replace("/[^a-z0-9_.-]/", "_", strtolower($name)) . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $file_name;
    
    $logo_path = "img/" . $file_name; // Względna ścieżka do zapisania w bazie danych
    
    // Sprawdź czy plik faktycznie jest obrazem
    $check = getimagesize($_FILES["logo"]["tmp_name"]);
    if($check === false) {
        header("Location: ../index.php?status=error&message=" . urlencode("Przesłany plik nie jest obrazem."));
        exit;
    }
    
    // Sprawdź rozmiar pliku (maks. 2MB)
    if ($_FILES["logo"]["size"] > 2000000) {
        header("Location: ../index.php?status=error&message=" . urlencode("Plik jest za duży. Maksymalny rozmiar to 2MB."));
        exit;
    }
    
    // Zezwalaj tylko na określone formaty plików
    if($file_extension != "jpg" && $file_extension != "jpeg" && $file_extension != "png" && $file_extension != "gif" && $file_extension != "svg") {
        header("Location: ../index.php?status=error&message=" . urlencode("Dozwolone są tylko pliki JPG, JPEG, PNG, GIF i SVG."));
        exit;
    }
    
    // Sprawdź czy katalog docelowy ma prawa zapisu
    if (!is_writable(dirname($target_file))) {
        // Próba ustawienia bardziej bezpiecznych uprawnień 
            @chmod($target_dir, 0777);
    
        if (!is_writable(dirname($target_file))) {
            header("Location: ../index.php?status=error&message=" . urlencode("Katalog docelowy nie ma uprawnień do zapisu."));
            exit;
        }
    }
    
    // Spróbuj przesłać plik
    if (!move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
        $error_message = "Wystąpił błąd podczas przesyłania pliku.";
        
        // Podaj bardziej szczegółowe komunikaty błędów na podstawie kodu błędu
        switch ($_FILES["logo"]["error"]) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message = "Przesłany plik przekracza dyrektywę upload_max_filesize w php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = "Przesłany plik przekracza dyrektywę MAX_FILE_SIZE określoną w formularzu HTML.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = "Plik został przesłany tylko częściowo.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = "Żaden plik nie został przesłany.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = "Brak folderu tymczasowego.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = "Nie udało się zapisać pliku na dysku.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message = "Przesyłanie pliku zostało zatrzymane przez rozszerzenie.";
                break;
        }
        
        // Zapisz błąd
        error_log("File upload error: " . $error_message . " - Target: " . $target_file);
        
        header("Location: ../index.php?status=error&message=" . urlencode($error_message));
        exit;
    }
    
    // Debugowanie - plik przesłany pomyślnie
    //error_log("File uploaded successfully to: " . $target_file);
    
    // Wstaw dane do bazy danych
    $columns = "name, description, logo_path";
    $values = "VALUES ('$name', '$description', '$logo_path'";
    
    // Dodaj stronę jeśli podane
    if (!empty($website)) {
        $columns .= ", website";
        $values .= ", '$website'";
    }
    // Dodaj YouTube jeśli podane
    if (!empty($youtube)) {
        $columns .= ", youtube";
        $values .= ", '$youtube'";
    }
    
    $values .= ")";
    
    $sql = "INSERT INTO distributions ($columns) $values";
    
    // Debugowanie - pokaż zapytanie SQL
    //error_log("SQL query: " . $sql);
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../index.php?status=success&message=" . urlencode("Dystrybucja '$name' została pomyślnie dodana."));
    } else {
        // Jeśli dodanie do bazy danych nie powiedzie się, usuń przesłany plik
        if (file_exists($target_file)) {
            @unlink($target_file);
        }
        
        header("Location: ../index.php?status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
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