<?php
// Rozpoczęcie sesji w celu uwierzytelniania użytkowników
session_start();

include 'include/db_config.php'; // Dołączenie konfiguracji bazy danych
include __DIR__ . '/include/validation_utils.php'; // Dołączenie funkcji walidacji emoji

// Sprawdzenie, czy użytkownik jest już zalogowany
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Przetwarzanie danych z formularzy logowania i rejestracji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obsługa formularza logowania
    if (isset($_POST['login'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];
        
        // Walidacja danych wejściowych
        if (empty($username) || empty($password)) {
            $error = "Proszę wypełnić wszystkie pola.";
        } elseif (contains_emoji($username)) { // Walidacja emoji dla nazwy użytkownika podczas logowania
            $error = "Nazwa użytkownika nie może zawierać emoji.";
        } elseif (contains_emoji($password)) { // Walidacja emoji dla hasła podczas logowania
            $error = "Hasło nie może zawierać emoji.";
        } else {
            // Sprawdzenie danych uwierzytelniających w bazie danych
            $sql = "SELECT id, username, password FROM accounts WHERE username = '$username'";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Weryfikacja hasła
                if (password_verify($password, $user['password'])) {
                    // Ustawienie zmiennych sesji po pomyślnym zalogowaniu
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Przekierowanie na stronę, z której użytkownik przyszedł, lub na stronę główną
                    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                    header("Location: $redirect");
                    exit;
                } else {
                    $error = "Niepoprawna nazwa użytkownika lub hasło.";
                }
            } else {
                $error = "Niepoprawna nazwa użytkownika lub hasło.";
            }
        }
    }
    
    // Obsługa formularza rejestracji
    if (isset($_POST['register'])) {
        $username = $conn->real_escape_string($_POST['username']);
        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Walidacja danych wejściowych
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = "Proszę wypełnić wszystkie pola.";
        } elseif (contains_emoji($username)) { // Walidacja emoji dla nazwy użytkownika
            $error = "Nazwa użytkownika nie może zawierać emoji.";
        } elseif (contains_emoji($email)) { // Walidacja emoji dla adresu email
            $error = "Adres email nie może zawierać emoji.";
        } elseif (contains_emoji($password)) { // Walidacja emoji dla hasła
            $error = "Hasło nie może zawierać emoji.";
        } elseif ($password !== $confirm_password) {
            $error = "Hasła nie są identyczne.";
        } elseif (strlen($password) < 6) {
            $error = "Hasło musi mieć co najmniej 6 znaków.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Podany adres email jest nieprawidłowy.";
        } else {
            // Sprawdzenie, czy nazwa użytkownika lub adres email już istnieją w bazie danych
            $check_sql = "SELECT id FROM accounts WHERE username = '$username' OR email = '$email'";
            $check_result = $conn->query($check_sql);
            
            if ($check_result && $check_result->num_rows > 0) {
                $error = "Nazwa użytkownika lub adres email jest już zajęty.";
            } else {
                // Haszowanie hasła przed zapisaniem do bazy danych
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Dodanie nowego użytkownika do bazy danych
                $insert_sql = "INSERT INTO accounts (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
                
                if ($conn->query($insert_sql)) {
                    $success = "Rejestracja przebiegła pomyślnie! Możesz się teraz zalogować.";
                } else {
                    $error = "Wystąpił błąd podczas rejestracji: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie - Baza Dystrybucji Linux</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
    <!-- Added class="login-register-page" -->
    <div class="container login-register-page">
        <header>
            <h1>Logowanie / Rejestracja</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                <a href="index.php" class="btn-return"><i class="fas fa-home"></i> Strona główna</a>
            </div>
        </header>

        <?php include 'include/messages.php'; ?>

        <div class="auth-container">
            <div class="auth-tabs">
                <div class="auth-tab active" data-tab="login">Logowanie</div>
                <div class="auth-tab" data-tab="register">Rejestracja</div>
            </div>

            <!-- Formularz logowania -->
            <form method="post" id="login-form" class="auth-form active">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Hasło</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Zaloguj się
                </button>
            </form>
            
            <!-- Formularz rejestracji -->
            <form method="post" id="register-form" class="auth-form">
                <div class="form-group">
                    <label for="reg-username"><i class="fas fa-user"></i> Nazwa użytkownika</label>
                    <input type="text" id="reg-username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Adres email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="reg-password"><i class="fas fa-lock"></i> Hasło</label>
                    <input type="password" id="reg-password" name="password" required>
                    <small>Minimum 6 znaków</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm-password"><i class="fas fa-lock"></i> Potwierdź hasło</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="register" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Utwórz konto
                </button>
            </form>
        </div>
    </div>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.auth-tab');
            const forms = document.querySelectorAll('.auth-form');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const target = this.getAttribute('data-tab');
                    
                    // Usunięcie klasy aktywnej ze wszystkich zakładek i formularzy
                    tabs.forEach(t => t.classList.remove('active'));
                    forms.forEach(f => f.classList.remove('active'));
                    
                    // Dodanie klasy aktywnej do bieżącej zakładki i formularza
                    this.classList.add('active');
                    document.getElementById(target + '-form').classList.add('active');
                });
            });
        });
    </script>
</body>
</html>