<?php
// Rozpoczęcie sesji, aby uzyskać informacje o zalogowanym użytkowniku
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany. Jeśli nie, przekierowanie do strony logowania z zapamiętaniem poprzedniej lokalizacji
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=index.php");
    exit;
}

require_once 'db_config.php';
require_once __DIR__ . '/validation_utils.php'; // Dołączenie pliku z funkcją walidacji emoji

// Włączenie wyświetlania wszystkich błędów w celu łatwiejszego debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Pobranie ID użytkownika z sesji
    $user_id = $_SESSION['user_id'];
    
    // Pobranie danych z formularza i ich oczyszczenie
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $website = !empty($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : NULL;
    $youtube = !empty($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : NULL;
    
    // Walidacja danych wejściowych
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Nazwa dystrybucji jest wymagana.";
    }
    if (contains_emoji($name)) {
        $errors[] = "Nazwa dystrybucji nie może zawierać emoji.";
    }
    
    if (empty($description) || strlen($description) < 30) {
        $errors[] = "Opis musi zawierać co najmniej 30 znaków.";
    }
    if (contains_emoji($description)) {
        $errors[] = "Opis dystrybucji nie może zawierać emoji.";
    }
    
    // Walidacja adresu URL strony internetowej
    if (!empty($website)) {
        $website = trim($website);
        // Dodanie "http://" jeśli brakuje protokołu
        if (!preg_match('~^(?:f|ht)tps?://~i', $website)) {
            $website = 'http://' . $website;
            $_POST['website'] = $website;
        }
        if (!filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = "Adres strony internetowej jest nieprawidłowy.";
        }
    }
    
    // Walidacja adresu URL filmu na YouTube
    if (!empty($youtube)) {
        $youtube = trim($youtube);
        // Obsługa różnych formatów linków YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $youtube, $matches)) {
            // Konwersja do standardowego formatu https://www.youtube.com/watch?v=VIDEO_ID
            $youtube_id = $matches[1];
            $youtube = 'https://www.youtube.com/watch?v=' . $youtube_id;
            $_POST['youtube'] = $youtube;
        } else {
            $errors[] = "Adres filmu na YouTube jest nieprawidłowy. Upewnij się, że podajesz pełny adres URL.";
        }
    }
    
    // Sprawdzenie, czy plik z logo został przesłany
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Logo dystrybucji jest wymagane.";
    } else {
        // Walidacja typu pliku
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['logo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG).";
        }
        
        // Walidacja rozmiaru pliku (maksymalnie 2MB)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Rozmiar pliku nie może przekraczać 2MB.";
        }
    }
}


// Sprawdzenie, czy nazwa dystrybucji już istnieje w bazie danych
if (empty($errors)) { // Sprawdzenie tylko jeśli inne walidacje przeszły pomyślnie
    $check_name_sql = "SELECT id FROM distributions WHERE name = ?";
    $stmt_check = $conn->prepare($check_name_sql);
    if ($stmt_check) {
        $stmt_check->bind_param("s", $name);
        $stmt_check->execute();
        $stmt_check->store_result();
        if ($stmt_check->num_rows > 0) {
            $errors[] = "Dystrybucja o nazwie '" . htmlspecialchars($name) . "' już istnieje w bazie danych.";
        }
        $stmt_check->close();
    } else {
        // Błąd podczas przygotowywania zapytania - dodaj ogólny błąd lub zaloguj to
        $errors[] = "Błąd podczas sprawdzania nazwy dystrybucji.";
        error_log("Prepare failed for name check: (" . $conn->errno . ") " . $conn->error);
    }
}


if (!empty($errors)) {
    $error_message = implode("__NEWLINE__", $errors);
    header("Location: ../index.php?status=error&message=" . urlencode($error_message));
    exit;
}

// Obsługujemy przesyłanie pliku z logo
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/img/";
// Sprawdź czy katalog docelowy istnieje, jeśli nie, utwórz
if (!file_exists($target_dir)) {
    if (!mkdir($target_dir, 0777, true)) {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie można utworzyć katalogu docelowego dla przesłanego pliku."));
        exit;
    }
}
// Sprawdź czy wybrano plik logo
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
    header("Location: ../index.php?status=error&message=" . urlencode("Nie wybrano pliku logo."));
    exit;
}
// Generowanie unikalnej nazwy pliku
$file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
$file_name = preg_replace("/[^a-z0-9_.-]/", "_", strtolower($name)) . "." . $file_extension;
$target_file = $target_dir . $file_name;
$logo_path = "img/" . $file_name; // Względna ścieżka do zapisania w bazie danych
// Sprawdź czy plik jest obrazem
$check = getimagesize($_FILES['logo']['tmp_name']);
if ($check === false) {
    header("Location: ../index.php?status=error&message=" . urlencode("Przesłany plik nie jest obrazem."));
    exit;
}
// Sprawdź rozmiar pliku (maks. 2MB)
if ($_FILES['logo']['size'] > 2000000) {
    header("Location: ../index.php?status=error&message=" . urlencode("Plik jest za duży. Maksymalny rozmiar to 2MB."));
    exit;
}
// Zezwalaj tylko na określone formaty plików
$allowed_ext = ['jpg','jpeg','png','gif','svg'];
if (!in_array($file_extension, $allowed_ext)) {
    header("Location: ../index.php?status=error&message=" . urlencode("Dozwolone są tylko pliki JPG, JPEG, PNG, GIF i SVG."));
    exit;
}
// Sprawdź prawa zapisu katalogu
if (!is_writable(dirname($target_file))) {
    @chmod($target_dir, 0777);
    if (!is_writable(dirname($target_file))) {
        header("Location: ../index.php?status=error&message=" . urlencode("Katalog docelowy nie ma uprawnień do zapisu."));
        exit;
    }
}
// Przesuń przesłany plik
if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
    $error_message = "Wystąpił błąd podczas przesłania pliku.";
    switch ($_FILES['logo']['error']) {
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
    error_log("File upload error: " . $error_message . " - Target: " . $target_file);
    header("Location: ../index.php?status=error&message=" . urlencode($error_message));
    exit;
}

// Dodajemy dystrybucję do bazy danych za pomocą przygotowanego zapytania
$sql = "INSERT INTO distributions (name, description, website, youtube, logo_path, added_by) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Powiązanie parametrów (s - string, i - integer)
    $stmt->bind_param("sssssi", $name, $description, $website, $youtube, $logo_path, $user_id);

    if ($stmt->execute()) {
        // Sukces
        header("Location: ../index.php?status=success&added=" . urlencode($name));
    } else {
        // Błąd wykonania
        // Zapisz szczegółowy błąd do logów
        error_log("Error inserting distribution: (" . $stmt->errno . ") " . $stmt->error);
        // Podaj przyjazny komunikat o błędzie
        header("Location: ../index.php?status=error&message=" . urlencode("Wystąpił błąd podczas dodawania dystrybucji. Spróbuj ponownie."));
    }
    $stmt->close();
} else {
    // Błąd przygotowania zapytania
    // Zapisz szczegółowy błąd do logów
    error_log("Prepare failed for insert: (" . $conn->errno . ") " . $conn->error);
    // Podaj przyjazny komunikat o błędzie
    header("Location: ../index.php?status=error&message=" . urlencode("Wystąpił błąd serwera podczas przygotowywania zapytania."));
}

$conn->close(); // Zamknij połączenie po użyciu
exit; // Upewnij się, że skrypt zatrzymuje się tutaj

// Przekierowujemy w przypadku bezpośredniego dostępu do pliku
header("Location: ../index.php");
exit;
?>