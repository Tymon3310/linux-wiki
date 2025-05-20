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

// Get the search query parameter, trim whitespace. Default to empty string if not set.
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search_query === '') {
    // If the search query is empty, fetch a default list of distributions
    // Ordered by name, limited to 20 results as an example
    $sql = "SELECT id, name, description, logo_path, website 
            FROM distributions 
            ORDER BY name ASC 
            LIMIT 20";
} else {
    // If there is a search query, proceed with the existing search logic
    $escaped_search_query = $conn->real_escape_string($search_query);

    // Zapytanie SQL do wyszukiwania w bazie danych (w nazwie, opisie)
    // Sortowanie wyników: najpierw te, gdzie fraza pasuje do początku nazwy, 
    // potem do dowolnej części nazwy, następnie do opisu.
    // Ograniczenie liczby wyników do 10.
    $sql = "SELECT id, name, description, logo_path, website 
            FROM distributions 
            WHERE 
                name LIKE '%$escaped_search_query%' OR 
                description LIKE '%$escaped_search_query%'
            ORDER BY 
            CASE 
                WHEN name LIKE '$escaped_search_query%' THEN 1
                WHEN name LIKE '%$escaped_search_query%' THEN 2
                WHEN description LIKE '%$escaped_search_query%' THEN 3
                ELSE 4
            END, 
            name ASC
            LIMIT 10";
}

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