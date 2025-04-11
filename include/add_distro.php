<?php
// Start session to get user information
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=index.php");
    exit;
}

require_once 'db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to generate a unique filename
function generate_unique_filename($original_filename) {
    $extension = pathinfo($original_filename, PATHINFO_EXTENSION);
    $base_name = strtolower(preg_replace("/[^a-zA-Z0-9_]/", "_", pathinfo($original_filename, PATHINFO_FILENAME)));
    return $base_name . "_" . uniqid() . "." . $extension;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get POST data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $website = !empty($_POST['website']) ? mysqli_real_escape_string($conn, $_POST['website']) : NULL;
    $youtube = !empty($_POST['youtube']) ? mysqli_real_escape_string($conn, $_POST['youtube']) : NULL;
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Nazwa dystrybucji jest wymagana.";
    }
    
    if (empty($description) || strlen($description) < 30) {
        $errors[] = "Opis musi zawierać co najmniej 30 znaków.";
    }
    
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Adres strony internetowej jest nieprawidłowy.";
    }
    
    if (!empty($youtube) && !preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/', $youtube)) {
        $errors[] = "Adres filmu na YouTube jest nieprawidłowy.";
    }
    
    // Check if logo was uploaded
    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Logo dystrybucji jest wymagane.";
    } else {
        // Verify file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['logo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG).";
        }
        
        // Check file size (max 2MB)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Rozmiar pliku nie może przekraczać 2MB.";
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        header("Location: ../index.php?status=error&message=" . urlencode($error_message));
        exit;
    }
    
    // Handle logo upload
    $upload_dir = "../img/";
    $original_filename = basename($_FILES['logo']['name']);
    $unique_filename = generate_unique_filename($original_filename);
    $upload_path = $upload_dir . $unique_filename;
    $db_path = "img/" . $unique_filename;
    
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
        // Insert distribution into database
        $sql = "INSERT INTO distributions (name, description, website, youtube, logo_path, added_by) 
                VALUES ('$name', '$description', " . ($website ? "'$website'" : "NULL") . ", " . 
                ($youtube ? "'$youtube'" : "NULL") . ", '$db_path', $user_id)";
        
        if ($conn->query($sql)) {
            header("Location: ../index.php?status=success&added=" . urlencode($name));
        } else {
            header("Location: ../index.php?status=error&message=" . urlencode("Błąd: " . $conn->error));
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Błąd przesyłania pliku."));
    }
    
    exit;
}

// Redirect if direct access
header("Location: ../index.php");
exit;
?>