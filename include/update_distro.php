<?php
// Start session to get user information
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?redirect=" . urlencode("edit.php?id=" . $_POST['id']));
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];
    
    // First check if the user has permission to edit this distro
    $check_sql = "SELECT added_by FROM distributions WHERE id = $id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result && $check_result->num_rows > 0) {
        $distro = $check_result->fetch_assoc();
        
        // Check if user owns the distro or is an admin
        if ($distro['added_by'] != $user_id) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Nie masz uprawnień do edycji tej dystrybucji."));
            exit;
        }
    } else {
        header("Location: ../index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji o podanym identyfikatorze."));
        exit;
    }
    
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
    
    if (!empty($errors)) {
        $error_message = implode("<br>", $errors);
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode($error_message));
        exit;
    }
    
    // Start constructing the SQL query
    $sql_parts = [
        "name = '$name'",
        "description = '$description'",
        "website = " . ($website ? "'$website'" : "NULL"),
        "youtube = " . ($youtube ? "'$youtube'" : "NULL")
    ];
    
    // Handle logo upload if a new logo is provided
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Verify file type
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $file_type = $_FILES['logo']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG)."));
            exit;
        }
        
        // Check file size (max 2MB)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Rozmiar pliku nie może przekraczać 2MB."));
            exit;
        }
        
        // Process the new logo
        $upload_dir = "../img/";
        $original_filename = basename($_FILES['logo']['name']);
        $unique_filename = generate_unique_filename($original_filename);
        $upload_path = $upload_dir . $unique_filename;
        $db_path = "img/" . $unique_filename;
        
        if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_path)) {
            // Get the old logo path
            $old_logo_sql = "SELECT logo_path FROM distributions WHERE id = $id";
            $old_logo_result = $conn->query($old_logo_sql);
            
            if ($old_logo_result && $old_logo_result->num_rows > 0) {
                $old_logo = $old_logo_result->fetch_assoc()['logo_path'];
                $old_logo_path = "../" . $old_logo;
                
                // Delete old logo file
                if (file_exists($old_logo_path) && !strpos($old_logo, "default")) {
                    unlink($old_logo_path);
                }
            }
            
            // Add logo path to SQL update
            $sql_parts[] = "logo_path = '$db_path'";
        } else {
            header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Błąd przesyłania pliku."));
            exit;
        }
    }
    
    // Finalize and execute the SQL query
    $sql = "UPDATE distributions SET " . implode(", ", $sql_parts) . " WHERE id = $id";
    
    if ($conn->query($sql)) {
        header("Location: ../details.php?id=$id&status=success&message=" . urlencode("Dystrybucja została zaktualizowana."));
    } else {
        header("Location: ../edit.php?id=$id&status=error&message=" . urlencode("Błąd: " . $conn->error));
    }
    
    exit;
}

// Redirect if direct access
header("Location: ../index.php");
exit;
?>