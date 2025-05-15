<?php
// Rozpoczęcie sesji, aby uzyskać informacje o użytkowniku
session_start();

// Sprawdzenie czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("edit.php?id=" . $_POST['id']));
    exit;
}

require_once 'db_config.php';
require_once __DIR__ . '/validation_utils.php'; // Dodanie walidacji emoji

// Włączenie raportowania błędów do debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $website = isset($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : '';
    $youtube = isset($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : '';

    // Najpierw sprawdź czy dystrybucja istnieje i pobierz stare logo i autora
    $check_sql = "SELECT * FROM distributions WHERE id = $id";
    $check_result = mysqli_query($conn, $check_sql);
    if (!$check_result || mysqli_num_rows($check_result) == 0) {
        header("Location: ../index.php?status=error&message=" . urlencode("Dystrybucja o podanym ID nie istnieje."));
        exit;
    }
    $distro = mysqli_fetch_assoc($check_result);
    $old_logo = $distro['logo_path'];

    // Sprawdzenie uprawnień właściciela
    if ($distro['added_by'] != $_SESSION['user_id'] && $_SESSION['user_id'] != 1) {
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Nie masz uprawnień do edycji tej dystrybucji."));
        exit;
    }

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
    
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Adres strony internetowej jest nieprawidłowy.";
    }
    
    if (!empty($youtube) && !preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube)) {
        $errors[] = "Adres filmu na YouTube jest nieprawidłowy.";
    }
    
    if (!empty($errors)) {
        $error_message = implode("__NEWLINE__", $errors);
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode($error_message));
        exit;
    }

    // Domyślnie użyj starej ścieżki logo
    $logo_path = $old_logo;
    // Obsługa przesyłania logo, jeśli nowe zostało dostarczone
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/img/";
        if (!file_exists($target_dir) && !mkdir($target_dir, 0777, true)) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Nie można utworzyć katalogu docelowego dla przesłanego pliku.")); exit;
        }
        $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $file_name = preg_replace("/[^a-z0-9_.-]/", "_", strtolower($name)) . "." . $ext;
        $target_file = $target_dir . $file_name;
        if (!getimagesize($_FILES['logo']['tmp_name'])) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Przesłany plik nie jest obrazem.")); exit;
        }
        if ($_FILES['logo']['size'] > 2000000) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Plik jest za duży. Maksymalny rozmiar to 2MB.")); exit;
        }
        $allowed_ext = ['jpg','jpeg','png','gif','svg'];
        if (!in_array($ext, $allowed_ext)) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Dozwolone są tylko pliki JPG, JPEG, PNG, GIF i SVG.")); exit;
        }
        if (!is_writable($target_dir)) {
            @chmod($target_dir, 0777);
            if (!is_writable($target_dir)) {
                header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Katalog docelowy nie ma uprawnień do zapisu.")); exit;
            }
        }
        // Jeśli plik o tej samej nazwie istnieje, usuń go, aby move_uploaded_file mógł go nadpisać
        if (file_exists($target_file)) {
            unlink($target_file);
        }
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
            $err = "Wystąpił błąd podczas przesyłania pliku.";
            switch ($_FILES['logo']['error']) {
                case UPLOAD_ERR_INI_SIZE: $err = "Przesłany plik przekracza upload_max_filesize w php.ini."; break;
                case UPLOAD_ERR_FORM_SIZE: $err = "Przesłany plik przekracza MAX_FILE_SIZE formularza."; break;
                case UPLOAD_ERR_PARTIAL: $err = "Plik został przesłany tylko częściowo."; break;
                case UPLOAD_ERR_NO_TMP_DIR: $err = "Brak folderu tymczasowego."; break;
                case UPLOAD_ERR_CANT_WRITE: $err = "Nie udało się zapisać pliku na dysku."; break;
                case UPLOAD_ERR_EXTENSION: $err = "Przesyłanie pliku zostało zatrzymane przez rozszerzenie."; break;
            }
            error_log("File upload error: $err");
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode($err)); exit;
        }
        // Ustal ścieżkę nowego logo
        $new_logo_rel = "img/" . $file_name;
        $logo_path = $new_logo_rel;
        // Usuń stare logo tylko jeśli różni się od nowego i nie jest domyślne
        if ($old_logo !== $new_logo_rel && strpos($old_logo, 'default') === false) {
            $old_file = $_SERVER['DOCUMENT_ROOT'] . "/" . $old_logo;
            if (file_exists($old_file)) {
                unlink($old_file);
            }
        }
    }

    // Aktualizuj dane w bazie
    $sql = "UPDATE distributions SET 
                name = '$name',
                description = '$description',
                logo_path = '$logo_path'";
    if (!empty($website)) { $sql .= ", website = '$website'"; }
    if (!empty($youtube)) { $sql .= ", youtube = '$youtube'"; }
    $sql .= " WHERE id = $id";

    if ($conn->query($sql)) {
        header("Location: ../details.php?id=$id&status=success&message=" . urlencode("Dystrybucja została zaktualizowana."));
    } else {
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Błąd: " . $conn->error));
    }
    
    exit;
}

// Przekierowanie w przypadku bezpośredniego dostępu
header("Location: ../index.php");
exit;
?>