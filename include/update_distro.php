<?php
// Włącz raportowanie błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Dołącz konfigurację bazy danych
require_once 'db_config.php';

// Przetwarzanie wysłanego formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pobierz dane z formularza i oczyść je
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Sprawdź czy pole website istnieje i oczyść je
    $website = isset($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : '';
    
    // Sprawdź czy pole youtube istnieje i oczyść je
    $youtube = isset($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : '';

    // Najpierw sprawdź czy dystrybucja istnieje
    $check_sql = "SELECT * FROM distributions WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) == 0) {
        header("Location: ../index.php?status=error&message=" . urlencode("Dystrybucja o podanym ID nie istnieje."));
        exit;
    }
    
    $distro = mysqli_fetch_assoc($check_result);
    $old_logo = $distro['logo_path'];
    
    // Obsługa przesyłania pliku jeśli nowe logo zostało dostarczone
    $logo_path = $old_logo; // Domyślnie użyj istniejącego logo
    
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] != UPLOAD_ERR_NO_FILE) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/img/";
        
        // Debugowanie - sprawdź czy mamy plik
        //error_log("File info for update: " . print_r($_FILES, true));
        
        // Sprawdź czy katalog docelowy istnieje
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Nie można utworzyć katalogu docelowego dla przesłanego pliku."));
                exit;
            }
        }
        
        // Generowanie unikalnej nazwy pliku
        $file_extension = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $file_name = preg_replace("/[^a-z0-9_.-]/", "_", strtolower($name)) . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        $logo_path = "img/" . $file_name; // Względna ścieżka do zapisania w bazie danych
        
        // Sprawdź czy plik faktycznie jest obrazem
        $check = getimagesize($_FILES["logo"]["tmp_name"]);
        if($check === false) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Przesłany plik nie jest obrazem."));
            exit;
        }
        
        // Sprawdź rozmiar pliku (maks. 2MB)
        if ($_FILES["logo"]["size"] > 2000000) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Plik jest za duży. Maksymalny rozmiar to 2MB."));
            exit;
        }
        
        // Zezwalaj tylko na określone formaty plików
        if($file_extension != "jpg" && $file_extension != "jpeg" && $file_extension != "png" && $file_extension != "gif" && $file_extension != "svg") {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Dozwolone są tylko pliki JPG, JPEG, PNG, GIF i SVG."));
            exit;
        }
        
        // Sprawdź czy katalog docelowy ma prawa zapisu
        if (!is_writable(dirname($target_file))) {
            // Próba ustawienia uprawnień
            @chmod($target_dir, 0777); // Tymczasowo ustaw najwyższe uprawnienia, aby upewnić się że działa
            
            if (!is_writable(dirname($target_file))) {
                header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Katalog docelowy nie ma uprawnień do zapisu."));
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
            error_log("File upload error during update: " . $error_message . " - Target: " . $target_file);
            
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode($error_message));
            exit;
        }
    } else {
        // Nie przesłano nowego logo, użyj istniejącego
        $logo_path = $old_logo;
    }
    
    // Aktualizuj dane w bazie danych
    $sql = "UPDATE distributions SET 
            name = '$name',
            description = '$description',
            logo_path = '$logo_path',
            website = '$website'";
    
    // Dodaj youtube jeśli podane
    if (isset($_POST['youtube'])) {
        $sql .= ", youtube = '$youtube'";
    }
    
    $sql .= " WHERE id = $id";
    
    // Debugowanie - pokaż zapytanie SQL
    //error_log("Update SQL query: " . $sql);
    
    if (mysqli_query($conn, $sql)) {
        // Jeśli logo zostało zaktualizowane i jest inne od starego, usuń stary plik logo
        if ($logo_path !== $old_logo) {
            $old_logo_path = $_SERVER['DOCUMENT_ROOT'] . "/" . $old_logo;
            
            if (file_exists($old_logo_path)) {
                @unlink($old_logo_path);
            }
        }
        
        header("Location: ../details.php?id=$id&status=success&message=" . urlencode("Dystrybucja '$name' została pomyślnie zaktualizowana."));
    } else {
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Błąd: " . mysqli_error($conn)));
    }
    
    exit;
} else {
    // Debugowanie - formularz nie został przesłany poprawnie
    //error_log("Update form not submitted or wrong method");
}

// Przekieruj jeśli dostęp bezpośredni
header("Location: ../index.php");
exit;
?>