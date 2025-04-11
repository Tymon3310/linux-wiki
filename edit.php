<?php
// Start session for user authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode("edit.php?id=" . $_GET['id']));
    exit;
}

// Include database configuration
include 'include/db_config.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator dystrybucji."));
    exit();
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch distribution details
$sql = "SELECT * FROM distributions WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji o podanym identyfikatorze."));
    exit();
}

$distro = $result->fetch_assoc();

// Check if user owns this distribution
if ($distro['added_by'] != $user_id) {
    header("Location: details.php?id=$id&status=error&message=" . urlencode("Nie masz uprawnień do edycji tej dystrybucji."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj <?php echo htmlspecialchars($distro['name']); ?> - Baza Dystrybucji Linux</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edycja Dystrybucji Linux</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                <a href="index.php" class="btn-return">Powrót do strony głównej</a>
            </div>
        </header>
        
        <div class="add-form-section">
            <h2><i class="fas fa-edit"></i> Edytuj dystrybucję: <?php echo htmlspecialchars($distro['name']); ?></h2>
            
            <form id="edit-form" method="post" action="include/update_distro.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $distro['id']; ?>">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Nazwa dystrybucji:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($distro['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Opis (min. 30 znaków):</label>
                    <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($distro['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="website"><i class="fas fa-globe"></i> Strona internetowa (opcjonalnie):</label>
                    <input type="url" name="website" id="website" placeholder="https://example.com" value="<?php echo htmlspecialchars($distro['website'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="youtube"><i class="fab fa-youtube"></i> Przykładowy filmik na Youtube o dystrybucji (opcjonalnie):</label>
                    <input type="url" name="youtube" id="youtube" placeholder="https://youtube.com/example" value="<?php echo htmlspecialchars($distro['youtube'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Aktualne logo:</label>
                    <div class="current-logo">
                        <img src="<?php echo htmlspecialchars($distro['logo_path']); ?>" alt="Aktualne logo" style="max-height: 100px;">
                        <p><?php echo htmlspecialchars(basename($distro['logo_path'])); ?></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-upload"></i> Zmień logo (opcjonalnie, maks. 2MB):</label>
                    <input type="file" name="logo" id="logo" accept="image/png, image/jpeg, image/gif, image/svg+xml">
                    <small><i class="fas fa-info-circle"></i> Akceptowane formaty: JPG, JPEG, PNG, GIF, SVG</small>
                    <small><i class="fas fa-hand-pointer"></i> Możesz przeciągnąć i upuścić plik lub wkleić obraz ze schowka (Ctrl+V)</small>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="update" class="btn-primary"><i class="fas fa-save"></i> Zapisz zmiany</button>
                    <button type="button" id="delete-button" class="btn-delete" data-id="<?php echo $distro['id']; ?>" 
                        data-name="<?php echo htmlspecialchars($distro['name']); ?>"><i class="fas fa-trash-alt"></i> Usuń</button>
                    <a href="details.php?id=<?php echo $distro['id']; ?>" class="btn-secondary"><i class="fas fa-times"></i> Anuluj</a>
                </div>
            </form>
        </div>
        
        <!-- Popup potwierdzenia usunięcia -->
        <div id="delete-modal" class="modal">
            <div class="modal-content">
                <h3><i class="fas fa-exclamation-triangle"></i> Potwierdź usunięcie</h3>
                <p>Czy na pewno chcesz usunąć dystrybucję <strong id="distro-name-to-delete"></strong>?</p>
                <p class="warning"><i class="fas fa-exclamation-circle"></i> Ta operacja jest nieodwracalna!</p>
                
                <div class="modal-actions">
                    <form id="delete-form" method="post" action="include/delete_distro.php">
                        <input type="hidden" id="distro-id-to-delete" name="id">
                        <button type="button" id="cancel-delete" class="btn-secondary"><i class="fas fa-ban"></i> Anuluj</button>
                        <button type="submit" name="delete" class="btn-delete"><i class="fas fa-trash-alt"></i> Usuń</button>
                    </form>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Tymon3310</p>
        </footer>
    </div>
    
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funkcjonalność potwierdzenia usunięcia dystrybucji
            const deleteButton = document.getElementById('delete-button');
            const deleteModal = document.getElementById('delete-modal');
            const cancelDelete = document.getElementById('cancel-delete');
            const distroNameToDelete = document.getElementById('distro-name-to-delete');
            const distroIdToDelete = document.getElementById('distro-id-to-delete');
            
            if (deleteButton) {
                deleteButton.addEventListener('click', function() {
                    const distroId = this.getAttribute('data-id');
                    const distroName = this.getAttribute('data-name');
                    
                    // Ustawienie wartości w popupie
                    distroNameToDelete.textContent = distroName;
                    distroIdToDelete.value = distroId;
                    
                    // Wyświetlenie popupu
                    deleteModal.style.display = 'block';
                });
            }
            
            // Zamknięcie popupu po kliknięciu Anuluj
            if (cancelDelete) {
                cancelDelete.addEventListener('click', function() {
                    deleteModal.style.display = 'none';
                });
            }
            
            // Zamknięcie popupu po kliknięciu poza nim
            window.addEventListener('click', function(event) {
                if (event.target === deleteModal) {
                    deleteModal.style.display = 'none';
                }
            });
            
            // Zamknięcie popupu po naciśnięciu klawisza Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && deleteModal.style.display === 'block') {
                    deleteModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>