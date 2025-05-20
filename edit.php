<?php
// Rozpoczęcie sesji w celu uwierzytelniania użytkowników
session_start();

// Sprawdzenie, czy użytkownik jest zalogowany
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=" . urlencode("edit.php?id=" . $_GET['id']));
    exit;
}

// Dołączenie pliku konfiguracyjnego bazy danych
include 'include/db_config.php';

// Sprawdzenie, czy parametr ID istnieje i jest numeryczny
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator dystrybucji."));
    exit();
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Pobranie szczegółów dystrybucji z bazy danych
$sql = "SELECT * FROM distributions WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji o podanym identyfikatorze."));
    exit();
}

$distro = $result->fetch_assoc();

// Sprawdzenie, czy użytkownik jest właścicielem dystrybucji lub administratorem
if ($distro['added_by'] != $user_id && $_SESSION['user_id'] != 1) {
    header("Location: details.php?id=$id&status=error&message=" . urlencode("Nie masz uprawnień do edycji tej dystrybucji."));
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Edytuj <?php echo htmlspecialchars($distro['name']); ?> - Baza Dystrybucji Linux</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
</head>
<body>
    <div class="container">
        <header>
            <h1>Edycja Dystrybucji Linux</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                <a href="index.php" class="btn-return"><i class="fas fa-home"></i> Strona główna</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Użytkownik zalogowany -->
                    <a href="logout.php" class="btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Wyloguj się
                    </a>
                <?php else: ?>
                    <!-- Użytkownik niezalogowany -->
                    <a href="login.php" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Zaloguj się
                    </a>
                <?php endif; ?>
            </div>
        </header>
        <?php include 'include/messages.php'; ?>

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
                    <small id="description-counter" class="char-counter">0 znaków</small>
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
                        <img src="<?php echo htmlspecialchars($distro['logo_path']); ?>" alt="Aktualne logo">
                        <p><?php echo htmlspecialchars(basename($distro['logo_path'])); ?></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="logo"><i class="fas fa-upload"></i> Zmień logo (opcjonalnie, maks. 2MB):</label>
                    <div class="file-upload-container" data-existing-logo="<?php echo htmlspecialchars($distro['logo_path']); ?>" data-existing-logo-name="<?php echo htmlspecialchars($distro['logo_path'] ? basename($distro['logo_path']) : ''); ?>">
                        <!-- Wskazówka i podgląd zostaną dodane dynamicznie przez JS -->
                        <input type="file" id="logo" name="logo" accept="image/png, image/jpeg, image/gif, image/svg+xml" style="display: none;">
                        <button type="button" class="file-select-button">Wybierz plik</button>
                    </div>
                    <small><i class="fas fa-info-circle"></i> Akceptowane formaty: JPG, JPEG, PNG, GIF, SVG</small>
                    <small><i class="fas fa-hand-pointer"></i> Możesz przeciągnąć i upuścić plik lub wkleić obraz ze schowka (Ctrl+V)</small>
                    <!-- Komunikaty o błędach będą dodawane tutaj przez JS -->
                </div>
                
                <div class="form-buttons">
                    <a href="details.php?id=<?php echo $distro['id']; ?>" class="btn-secondary"><i class="fas fa-times"></i> Anuluj</a>
                    <div class="action-group">
                        <button type="submit" name="update" class="btn-primary"><i class="fas fa-save"></i> Zapisz zmiany</button>
                        <button type="button" id="delete-button" class="btn-delete" data-id="<?php echo $distro['id']; ?>" 
                            data-name="<?php echo htmlspecialchars($distro['name']); ?>"><i class="fas fa-trash-alt"></i> Usuń</button>
                    </div>
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
    
    <script>
        window.isUserLoggedIn = <?php echo json_encode(isset($_SESSION['user_id'])); ?>;
    </script>
    <script type="module" src="js/script.js"></script>
</body>
</html>
<?php
$conn->close();
?>