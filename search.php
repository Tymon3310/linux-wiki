<?php
// Endpoint API do wyszukiwania dystrybucji Linuksa za pomocą AJAX

// Rozpoczęcie sesji w celu uwierzytelniania użytkowników (jeśli potrzebne w przyszłości)
session_start();

// Dołączenie pliku konfiguracyjnego bazy danych
include 'include/db_config.php';

// Włączenie wyświetlania błędów PHP w celach diagnostycznych
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Sprawdzenie, czy parametr wyszukiwania (q) został przekazany i nie jest pusty
if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

// Pobranie i oczyszczenie (zabezpieczenie przed SQL injection) frazy wyszukiwania
$search = $conn->real_escape_string($_GET['q']);

// Zapytanie SQL do wyszukiwania w bazie danych (w nazwie, opisie)
// Sortowanie wyników: najpierw te, gdzie fraza pasuje do początku nazwy, 
// potem do dowolnej części nazwy, następnie do opisu.
// Ograniczenie liczby wyników do 10.
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

// Przygotowanie tablicy na wyniki
$distributions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Poprawienie ścieżki do logo, jeśli jest to konieczne (dodanie prefiksu "img/")
        $logo_path = $row['logo_path'];
        if (!preg_match('/^img\//', $logo_path)) {
            $logo_path = 'img/' . $logo_path;
        }
        
        // Dodanie każdej znalezionej dystrybucji do tablicy wyników
        $distributions[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'description' => htmlspecialchars($row['description']),
            'logo_path' => htmlspecialchars($logo_path),
            'website' => htmlspecialchars($row['website'] ?? '')
        ];
    }
}

// Zwrócenie wyników w formacie JSON
echo json_encode($distributions);

// Zamknięcie połączenia z bazą danych
$conn->close();
?>