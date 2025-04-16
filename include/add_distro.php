<?php
// Rozpoczęcie sesji dla uwierzytelniania użytkowników
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=index.php");
    exit;
}

require_once 'db_config.php';

// Włączenie raportowania błędów do celów diagnostycznych
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Funkcja generująca unikalną nazwę pliku
function generate_unique_filename($original_filename) {
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $base_name = strtolower(preg_replace("/[^a-zA-Z0-9_]/", "_", pathinfo($original_filename, PATHINFO_FILENAME)));
    return $base_name . "_" . uniqid() . "." . $extension;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Pobranie ID użytkownika z sesji
    $user_id = $_SESSION['user_id'];
    
    // Pobranie danych z formularza
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $website = !empty($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : NULL;
    $youtube = !empty($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : NULL;
    
    // Walidacja wprowadzonych danych
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Nazwa dystrybucji jest wymagana.";
    }
    
    if (empty($description) || strlen($description) < 30) {
        $errors[] = "Opis musi zawierać co najmniej 30 znaków.";
    }
    
    // Ulepszona walidacja adresu strony internetowej
    if (!empty($website)) {
        $website = trim($website);
        // Dodaj protokół http:// jeśli nie istnieje
        if (!preg_match('~^(?:f|ht)tps?://~i', $website)) {
            $website = 'http://' . $website;
            $_POST['website'] = $website; // Aktualizacja wartości w $_POST
        }
        
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = "Adres strony internetowej jest nieprawidłowy.";
        }
    }
    
    // Ulepszona walidacja adresu YouTube
    if (!empty($youtube)) {
        $youtube = trim($youtube);
        // Obsługa różnych formatów linków YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube, $matches)) {
            // Przekształć do standardowej formy https://www.youtube.com/watch?v=VIDEO_ID
            $youtube_id = $matches[1];
            $youtube = 'https://www.youtube.com/watch?v=' . $youtube_id;
            $_POST['youtube'] = $youtube; // Aktualizacja wartości w $_POST
        } else {
            $errors[] = "Adres filmu na YouTube jest nieprawidłowy. Upewnij się, że podajesz pełny adres URL.";
        }
    }
    
    // Sprawdzenie, czy przesłano plik z logo
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Logo dystrybucji jest wymagane.";
    } else {
        // Weryfikacja typu pliku
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['logo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG).";
        }
        
        // Sprawdzenie rozmiaru pliku (maksymalnie 2MB)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Rozmiar pliku nie może przekraczać 2MB.";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        header("Location: ../index.php?status=error&message=" . urlencode($error_message));
        exit;
    }
    
    // Obsługa przesyłania pliku z logo
    $upload_dir = "../img/";
    $original_filename = basename($_FILES['logo']['name']);
    $unique_filename = generate_unique_filename($original_filename);
    $upload_path = $upload_dir . $unique_filename;
    $db_path = "img/" . $unique_filename;
    
    // Sprawdzenie czy katalog istnieje i czy ma odpowiednie uprawnienia
    if (!is_dir($upload_dir)) {
        header("Location: ../index.php?status=error&message=" . urlencode("Błąd: Katalog docelowy nie istnieje."));
        exit;
    }
    
    if (!is_writable($upload_dir)) {
        header("Location: ../index.php?status=error&message=" . urlencode("Błąd: Brak uprawnień do zapisu w katalogu."));
        exit;
    }
    
    // Sprawdzenie kodu błędu przesyłania pliku
    if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Błąd przesyłania pliku: ";
        switch ($_FILES['logo']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $error_message .= "Przekroczono maksymalny rozmiar pliku ustawiony w php.ini.";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "Przekroczono maksymalny rozmiar pliku ustawiony w formularzu.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "Plik został przesłany tylko częściowo.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= "Brak tymczasowego katalogu.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= "Nie udało się zapisać pliku na dysku.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= "Przesyłanie pliku zostało zatrzymane przez rozszerzenie PHP.";
                break;
            default:
                $error_message .= "Nieznany błąd.";
        }
        header("Location: ../index.php?status=error&message=" . urlencode($error_message));
        exit;
    }
    
    // Próba przesłania pliku z bardziej szczegółową obsługą błędów
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
        // Dodanie dystrybucji do bazy danych
        $sql = "INSERT INTO distributions (name, description, website, youtube, logo_path, added_by) 
                VALUES ('$name', '$description', " . ($website ? "'$website'" : "NULL") . ", " . 
                ($youtube ? "'$youtube'" : "NULL") . ", '$db_path', $user_id)";
        
        if ($conn->query($sql)) {
            header("Location: ../index.php?status=success&added=" . urlencode($name));
        } else {
            header("Location: ../index.php?status=error&message=" . urlencode("Błąd: " . $conn->error));
        }
    } else {
        $error_message = "Błąd przesyłania pliku. Sprawdź uprawnienia katalogu i maksymalny rozmiar pliku.";
        header("Location: ../index.php?status=error&message=" . urlencode($error_message));
    }
    
    exit;
}

// Przekierowanie w przypadku bezpośredniego dostępu do pliku
header("Location: ../index.php");
exit;
?>