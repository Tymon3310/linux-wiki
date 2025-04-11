<?php
/**
 * Endpoint do wyszukiwania dystrybucji Linux przez AJAX
 */

// Include database configuration
include 'include/db_config.php';

// Enable error logging for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Check if search parameter is provided
if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit;
}

// Get and sanitize search term
$search = $conn->real_escape_string($_GET['q']);

// Search in database (name, description and extended search)
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

// Prepare response
$distributions = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Fix logo path if needed
        $logo_path = $row['logo_path'];
        if (!preg_match('/^img\//', $logo_path)) {
            $logo_path = 'img/' . $logo_path;
        }
        
        // Add each distribution to the results array
        $distributions[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'description' => htmlspecialchars($row['description']),
            'logo_path' => htmlspecialchars($logo_path),
            'website' => htmlspecialchars($row['website'] ?? '')
        ];
    }
}

// Return JSON response
echo json_encode($distributions);

// Close database connection
$conn->close();
?>