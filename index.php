<?php
// Rozpoczęcie sesji dla uwierzytelniania użytkowników
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baza Dystrybucji Linux</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <script>
        // Przekaż status uwierzytelnienia do JavaScript
        const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    </script>
    <script src="js/script.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Baza Dystrybucji Linux</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Użytkownik jest zalogowany -->
                    <button id="show-add-form" class="btn-primary">
                        <i class="fas fa-plus-circle"></i> Dodaj nową dystrybucję
                    </button>
                    <a href="account.php" class="btn-primary">
                        <i class="fas fa-user-circle"></i> Moje konto<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1) echo " <span class='admin-tag'>Admin</span>"; ?>
                    </a>
                    <a href="logout.php" class="btn-primary">
                        <i class="fas fa-sign-out-alt"></i> Wyloguj się
                    </a>
                <?php else: ?>
                    <!-- Użytkownik nie jest zalogowany -->
                    <a href="login.php" class="btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Zaloguj się
                    </a>
                <?php endif; ?>
            </div>
        </header>
        <?php include 'include/messages.php'; ?>

        <!-- Wyszukiwanie dystrybucji -->
        <div class="search-section">
            <h2>Znajdź dystrybucję Linux</h2>
            <form id="search-form" onsubmit="performSearch(); return false;">
                <input type="text" name="search_distro" id="search-input" placeholder="Wpisz nazwę dystrybucji...">
                <button type="submit" id="search-button"><i class="fas fa-search"></i> Szukaj</button>
            </form>
        </div>

        <!-- Sekcja wyników -->
        <div id="results" class="results-section">
            <?php
            if (isset($_POST['search'])) {
                include 'include/db_config.php';
                
                $search = $conn->real_escape_string($_POST['search_distro']);
                $sql = "SELECT * FROM distributions WHERE name LIKE '%$search%'";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    echo '<div class="search-results">';
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="distro-card">
                            <img src="<?php echo htmlspecialchars($row['logo_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="distro-logo">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($row['description']), 0, 150); echo (strlen($row['description']) > 150) ? '...' : ''; ?></p>
                            <a href="details.php?id=<?php echo $row['id']; ?>" class="btn-details">Szczegóły</a>
                        </div>
                        <?php
                    }
                    echo '</div>';
                } else {
                    echo "<div class='not-found'>";
                    echo "<p>Nie znaleziono dystrybucji \"" . htmlspecialchars($search) . "\" w naszej bazie danych.</p>";
                    
                    // Pokaż przycisk "Dodaj nową dystrybucję" tylko zalogowanym użytkownikom
                    if (isset($_SESSION['user_id'])) {
                        echo "<p>Czy chcesz dodać tę dystrybucję?</p>";
                        echo "<button id='show-add-form'>Dodaj nową dystrybucję</button>";
                    } else {
                        echo "<p>Zaloguj się, aby móc dodać nową dystrybucję.</p>";
                        echo "<a href='login.php' class='btn-primary'><i class='fas fa-sign-in-alt'></i> Zaloguj się</a>";
                    }
                    
                    echo "</div>";
                }
                
                $conn->close();
            } else {
                // Pokazanie wszystkich dystrybucji
                include 'include/db_config.php';
                
                $sql = "SELECT * FROM distributions ORDER BY name ASC";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    echo '<h2 class="section-title">Wszystkie dystrybucje Linux</h2>';
                    echo '<div class="search-results">';
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <div class="distro-card">
                            <img src="<?php echo htmlspecialchars($row['logo_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="distro-logo">
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($row['description']), 0, 150); echo (strlen($row['description']) > 150) ? '...' : ''; ?></p>
                            <div class="card-buttons">
                                <a href="details.php?id=<?php echo $row['id']; ?>" class="btn-details"><i class="fas fa-info-circle"></i> Szczegóły</a>
                            </div>
                        </div>
                        <?php
                    }
                    echo '</div>';
                } else {
                    echo "<div class='no-distros'>";
                    echo "<p>Brak dystrybucji w bazie danych.</p>";
                    
                    // Pokaż przycisk "Dodaj nową dystrybucję" tylko zalogowanym użytkownikom
                    if (isset($_SESSION['user_id'])) {
                        echo "<button id='show-add-form'>Dodaj nową dystrybucję</button>";
                    } else {
                        echo "<p>Zaloguj się, aby móc dodać nową dystrybucję.</p>";
                        echo "<a href='login.php' class='btn-primary'><i class='fas fa-sign-in-alt'></i> Zaloguj się</a>";
                    }
                    
                    echo "</div>";
                }
                
                $conn->close();
            }
            ?>
        </div>
        
        <!-- Formularz dodawania nowej dystrybucji -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <div id="add-form-container" class="add-form-section" style="display: none;">
            <h2><i class="fas fa-plus-circle"></i> Dodaj nową dystrybucję Linux</h2>
            <form id="add-form" method="post" action="include/add_distro.php" enctype="multipart/form-data">
                <input type="hidden" name="distro_name" id="distro-name-hidden">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-tag"></i> Nazwa dystrybucji:</label>
                    <input type="text" name="name" id="name" required>
                </div>
                
                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Opis (min. 30 znaków):</label>
                    <textarea name="description" id="description" rows="5" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="website"><i class="fas fa-globe"></i> Strona internetowa (opcjonalnie):</label>
                    <input type="url" name="website" id="website" placeholder="https://example.com">
                </div>

                <div class="form-group">
                    <label for="youtube"><i class="fab fa-youtube"></i> Filmik na Youtube o dystrybucji (opcjonalnie):</label>
                    <input type="url" name="youtube" id="youtube" placeholder="https://youtube.com/example">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Logo dystrybucji (max 2MB):</label>
                    <div class="file-upload-container">
                        <!-- Oryginalny input zostaje, ale będzie ukryty przez JavaScript -->
                        <input type="file" name="logo" id="logo" accept="image/png, image/jpeg, image/gif, image/svg+xml" required>
                    </div>
                    <small><i class="fas fa-info-circle"></i> Akceptowane formaty: JPG, JPEG, PNG, GIF, SVG</small>
                    <small><i class="fas fa-hand-pointer"></i> Możesz przeciągnąć i upuścić plik lub wkleić obraz ze schowka (Ctrl+V)</small>
                </div>
                
                <button type="submit" name="add" id="add-button"><i class="fas fa-plus"></i> Dodaj dystrybucję</button>
            </form>
        </div>
        <?php else: ?>
        <div id="login-prompt" class="add-form-section" style="display: none;">
            <h2><i class="fas fa-user-lock"></i> Wymagane logowanie</h2>
            <p>Aby dodać nową dystrybucję Linux, musisz się najpierw zalogować.</p>
            <a href="login.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Zaloguj się</a>
        </div>
        <?php endif; ?>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Tymon3310</p>
        </footer>
        <button onclick="topFunction()" id="backToTop" class="back-to-top" title="Go to top">
        <i class="fas fa-arrow-up"></i>
    </button>
    </div>
    <script>
        // Scroll animations
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, { threshold: 0.1 });
            
            fadeElements.forEach(element => {
                observer.observe(element);
            });
            
            // Back to top button functionality
            const backToTopButton = document.getElementById('backToTop');
            
            window.onscroll = function() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    backToTopButton.style.display = 'block';
                } else {
                    backToTopButton.style.display = 'none';
                }
            };
        });

        // Funkcja przewijania do góry strony
       // Back to top button functionality
    function topFunction() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
        
    </script>
</body>
</html>