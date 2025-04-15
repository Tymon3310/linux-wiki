<?php
// Endpoint do wyszukiwania dystrybucji Linux poprzez AJAX


// Rozpoczęcie sesji dla uwierzytelniania użytkowników
session_start();

// Dołączenie konfiguracji bazy danych
include 'include/db_config.php';

// Włączenie logowania błędów do diagnostyki
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Sprawdzenie czy parametr wyszukiwania został podany
if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

// Pobranie i oczyszczenie frazy wyszukiwania
$search = $conn->real_escape_string($_GET['q']);

// Wyszukiwanie w bazie danych (nazwa, opis i wyszukiwanie rozszerzone)
$sql = "SELECT * FROM distributions WHERE 
        name LIKE '%$search%' OR 
        description LIKE '%$search%'
        ORDER BY 
        CASE 
            WHEN name LIKE '$search%' THEN 1
            WHEN name LIKE '%$search%' THEN 2
            WHEN description LIKE '%$search%' THEN 3
            ELSE 4
        END, 
        name ASC
        LIMIT 10";

$result = $conn->query($sql);

// Przygotowanie odpowiedzi
$distributions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Naprawa ścieżki do logo, jeśli potrzebna
        $logo_path = $row['logo_path'];
        if (!preg_match('/^img\//', $logo_path)) {
            $logo_path = 'img/' . $logo_path;
        }
        
        // Dodanie każdej dystrybucji do tablicy wyników
        $distributions[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'description' => htmlspecialchars($row['description']),
            'logo_path' => htmlspecialchars($logo_path),
            'website' => htmlspecialchars($row['website'] ?? '')
        ];
    }
}

// Zwrócenie odpowiedzi w formacie JSON
echo json_encode($distributions);

// Zamknięcie połączenia z bazą danych
$conn->close();
?>