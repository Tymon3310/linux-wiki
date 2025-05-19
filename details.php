<?php
// Rozpoczęcie sesji w celu uwierzytelniania użytkowników
session_start();

// Dołączenie pliku konfiguracyjnego bazy danych
include 'include/db_config.php';

// Sprawdzenie, czy parametr ID istnieje i jest numeryczny
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator dystrybucji."));
    exit();
}

$id = (int)$_GET['id'];

// Pobranie szczegółów dystrybucji z bazy danych
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
    <title><?php echo htmlspecialchars($distro['name']); ?> - Szczegóły dystrybucji Linux</title>
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
            <h1>Informacje o dystrybucji Linux</h1>
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
        <main class="distro-details">
            <h2><?php echo htmlspecialchars($distro['name']); ?></h2>
            
            <div class="distro-content">
                <div class="distro-image">
                    <div class="logo-container">
                        <img src="<?php echo htmlspecialchars($distro['logo_path']); ?>" 
                            alt="Logo <?php echo htmlspecialchars($distro['name']); ?>" 
                            class="distro-logo-large">
                    </div>
                        
                    <?php if (!empty($distro['website'])): ?>
                    <div class="website-link">
                        <a href="<?php echo htmlspecialchars($distro['website']); ?>" 
                           target="_blank" rel="noopener noreferrer">
                            <i class="fas fa-globe"></i> 
                           Oficjalna strona
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="added-date">
                        Dodano: <?php echo date('d.m.Y', strtotime($distro['date_added'])); ?>   
                    </div>
                    <div class="added-by">
                        Dodane przez: 
                        <?php
                        // Pobranie nazwy użytkownika, który dodał dystrybucję
                        $user_sql = "SELECT username FROM accounts WHERE id = " . (int)$distro['added_by'];
                        $user_result = $conn->query($user_sql);
                        
                        if ($user_result && $user_result->num_rows > 0) {
                            $user = $user_result->fetch_assoc();
                            echo '<a href="user.php?id=' . (int)$distro['added_by'] . '">' . htmlspecialchars($user['username']) . '</a>';
                        } else {
                            echo "Nieznany użytkownik";
                        }
                        ?>
                </div>
                </div>
                
                <div class="distro-description">
                    <h3>Opis dystrybucji</h3>
                    <div class="description-text">
                        <?php echo nl2br(htmlspecialchars($distro['description'])); ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($distro['youtube'])): ?>
            <div class="distro-video">
                <h3>Prezentacja wideo</h3>
                <div class="video-container" id="youtube-embed-container" data-youtube-url="<?php echo htmlspecialchars($distro['youtube']); ?>">
                    <!-- Osadzenie YouTube zostanie tutaj wstawione przez JavaScript -->
                </div>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="edit.php?id=<?php echo $distro['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edytuj</a>
            </div>
            <br>

            <div class="comments-section">
                <h3><i class="far fa-comments"></i> Komentarze</h3>
                
                <?php
                // Pobranie komentarzy dla tej dystrybucji wraz z nazwami użytkowników
                $comment_sql = "SELECT c.*, a.username FROM comments c JOIN accounts a ON c.user_id = a.id WHERE c.distro_id = $id ORDER BY c.date_added DESC";
                $comment_result = $conn->query($comment_sql);
                
                if ($comment_result && $comment_result->num_rows > 0) {
                    echo "<div class='comments-count'><i class='fas fa-comment-alt'></i> {$comment_result->num_rows} " . 
                         ($comment_result->num_rows == 1 ? "komentarz" : "komentarzy") . "</div>";
                    
                    echo "<div class='comments-container'>";
                    while ($comment = $comment_result->fetch_assoc()) {
                        echo "<div class='comment'>";
                        echo "<div class='comment-header'>";
                        echo "<strong class='comment-author'><i class='fas fa-user'></i> <a href='user.php?id=" . (int)
                              $comment['user_id'] . "'>" . htmlspecialchars($comment['username']) . "</a>" .
                              ($comment['user_id'] == 1 ? " <span class='admin-tag'>Admin</span>" : "") .
                              "</strong>";
                        echo "<span class='comment-date'><i class='far fa-clock'></i> ". date('d.m.Y H:i', strtotime($comment['date_added'])) . "</span>";
                        echo "</div>";
                        echo "<div class='comment-body'>" . nl2br(htmlspecialchars($comment['comment'])) . "</div>";
                        if (isset($_SESSION['username']) && (($_SESSION['username'] === $comment['username']) || ($_SESSION['user_id'] == 1))) {
                            echo "<div class='comment-actions'>";
                            echo "<button class='btn-delete-comment' data-comment-id='{$comment['id']}' data-username='" . htmlspecialchars($comment['username']) . "'><i class='fas fa-trash-alt'></i> Usuń</button>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<div class='no-comments'><i class='fas fa-comment-slash'></i> Brak komentarzy do tej dystrybucji. Bądź pierwszy!</div>";
                }
                ?>
                
                <div class="add-comment-form">
                    <h4><i class="far fa-comment-dots"></i> Dodaj komentarz</h4>
                    <?php if (isset($_SESSION['username'])): ?>
                    <form method="post" action="include/add_comment.php" id="comment-form">
                        <input type="hidden" name="distro_id" value="<?php echo $distro['id']; ?>">
                        <div class="form-group">
                            <label for="comment"><i class="fas fa-pen"></i> Komentarz</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Twój komentarz..." required></textarea>
                        <small id="comment-counter" class="char-counter">0 znaków</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Dodaj komentarz</button>
                    </form>
                    <?php else: ?>
                    <p class="login-required"><i class="fas fa-info-circle"></i> Musisz być zalogowany, aby dodać komentarz.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        
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
        
        <!-- Popup potwierdzenia usunięcia komentarza -->
        <div id="delete-comment-modal" class="modal">
            <div class="modal-content">
                <h3><i class="fas fa-exclamation-triangle"></i> Potwierdź usunięcie komentarza</h3>
                <p>Czy na pewno chcesz usunąć komentarz użytkownika <strong id="comment-username-to-delete"></strong>?</p>
                <p class="warning"><i class="fas fa-exclamation-circle"></i> Ta operacja jest nieodwracalna!</p>
                
                <div class="modal-actions">
                    <form id="delete-comment-form" method="post" action="include/delete_comment.php">
                        <input type="hidden" id="comment-id-to-delete" name="id">
                        <button type="button" id="cancel-delete-comment" class="btn-secondary"><i class="fas fa-ban"></i> Anuluj</button>
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
// Zamknięcie połączenia z bazą danych
$conn->close();
?>