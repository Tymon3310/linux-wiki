<?php
// Konfiguracja połączenia z bazą danych MySQL

$host = "localhost";
$username = "root";
$password = "";    
$database = "linux_distributions";

// Nawiązanie połączenia z serwerem bazy danych
$conn = new mysqli($host, $username, $password);

// Sprawdzenie czy połączenie się powiodło
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

// Automatyczne utworzenie bazy danych, jeśli jeszcze nie istnieje
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) !== TRUE) {
    die("Błąd przy tworzeniu bazy danych: " . $conn->error);
}

// Wybór bazy danych do pracy
$conn->select_db($database);

// Utworzenie tabeli dla dystrybucji, jeśli jeszcze nie istnieje
$sql = "CREATE TABLE IF NOT EXISTS distributions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    youtube VARCHAR(255),
    logo_path VARCHAR(255) NOT NULL,
    website VARCHAR(255),
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Błąd przy tworzeniu tabeli distributions: " . $conn->error);
}

// Utworzenie tabeli dla komentarzy, jeśli jeszcze nie istnieje
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    distro_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    comment TEXT NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Błąd przy tworzeniu tabeli comments: " . $conn->error);
}

// Utworzenie tabeli dla kont użytkowników, jeśli jeszcze nie istnieje
$sql = "CREATE TABLE IF NOT EXISTS accounts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    die("Błąd przy tworzeniu tabeli accounts: " . $conn->error);
}
?>
