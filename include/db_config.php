<?php
// Konfiguracja połączenia z bazą danych

$host = "localhost";
$username = "root";
$password = "";    
$database = "linux_distributions";

// Utworzenie połączenia
$conn = new mysqli($host, $username, $password);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}

//  Utworzenie bazy danych jeśli nie istnieje
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) !== TRUE) {
    die("Błąd przy tworzeniu bazy danych: " . $conn->error);
}

//Wybór bazy danych
$conn->select_db($database);

// Utworzenie tabeli jeśli nie istnieje
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
// Utworzenie tabeli komentarzy jeśli nie istnieje
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
?>
