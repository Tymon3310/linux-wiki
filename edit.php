<?php
// Include database configuration
include 'include/db_config.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator dystrybucji."));
    exit();
}

$id = (int)$_GET['id'];

// Fetch distribution details
$sql = "SELECT * FROM distributions WHERE id = $id";
$result = $conn->query($sql);

if (!$result || $result->num_rows === 0) {
    header("Location: index.php?status=error&message=" . urlencode("Nie znaleziono dystrybucji o podanym identyfikatorze."));
    exit();
}

$distro = $result->fetch_assoc();
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
</head>
<body>
    <div class="container">
        <header>
            <h1>Edycja Dystrybucji Linux</h1>
            <div class="header-buttons">
                <a href="index.php" class="btn-return">Powrót do strony głównej</a>
            </div>
        </header>
        
        <div class="add-form-section">
            <h2>Edytuj dystrybucję: <?php echo htmlspecialchars($distro['name']); ?></h2>
            
            <form id="edit-form" method="post" action="include/update_distro.php" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $distro['id']; ?>">
                
                <div class="form-group">
                    <label for="name">Nazwa dystrybucji:</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($distro['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Opis (min. 30 znaków):</label>
                    <textarea name="description" id="description" rows="5" required><?php echo htmlspecialchars($distro['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="website">Strona internetowa (opcjonalnie):</label>
                    <input type="url" name="website" id="website" placeholder="https://example.com" value="<?php echo htmlspecialchars($distro['website'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="youtube">Przykładowy filmik na Youtube o dystrybucji (opcjonalnie):</label>
                    <input type="url" name="youtube" id="youtube" placeholder="https://youtube.com/example" value="<?php echo htmlspecialchars($distro['youtube'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Aktualne logo:</label>
                    <div class="current-logo">
                        <img src="<?php echo htmlspecialchars($distro['logo_path']); ?>" alt="Aktualne logo" style="max-height: 100px;">
                        <p><?php echo htmlspecialchars(basename($distro['logo_path'])); ?></p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Zmień logo (opcjonalnie, maks. 2MB):</label>
                    <input type="file" name="logo" id="logo" accept="image/png, image/jpeg, image/gif, image/svg+xml">
                    <small>Akceptowane formaty: JPG, JPEG, PNG, GIF, SVG</small>
                    <small>Możesz przeciągnąć i upuścić plik lub wkleić obraz ze schowka (Ctrl+V)</small>
                </div>
                
                <div class="form-buttons">
                    <button type="submit" name="update" class="btn-primary">Zapisz zmiany</button>
                    <a href="details.php?id=<?php echo $distro['id']; ?>" class="btn-secondary">Anuluj</a>
                </div>
            </form>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Tymon3310</p>
        </footer>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>
<?php
$conn->close();
?>