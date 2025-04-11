<?php
// Start session for user authentication
session_start();

// Dołączenie konfiguracji bazy danych
include 'include/db_config.php';

// Sprawdzenie czy parametr ID istnieje
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator dystrybucji."));
    exit();
}

$id = (int)$_GET['id'];

// Pobranie szczegółów dystrybucji
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
                <a href="index.php" class="btn-return"><i class="fas fa-home"></i> Powrót do strony głównej</a>
            </div>
        </header>
        
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
                    <!-- YouTube embed będzie tutaj wstawiony przez JavaScript -->
                </div>
            </div>
            <?php endif; ?>
            
            <div class="actions">
                <a href="edit.php?id=<?php echo $distro['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edytuj</a>
            </div>
            <br>

            <div class="comments-section">
                <h3><i class="far fa-comments"></i> Komentarze</h3>
                
                <?php
                // Pobranie komentarzy dla tej dystrybucji
                $comment_sql = "SELECT * FROM comments WHERE distro_id = $id ORDER BY date_added DESC";
                $comment_result = $conn->query($comment_sql);
                
                if ($comment_result && $comment_result->num_rows > 0) {
                    echo "<div class='comments-count'><i class='fas fa-comment-alt'></i> {$comment_result->num_rows} " . 
                         ($comment_result->num_rows == 1 ? "komentarz" : "komentarzy") . "</div>";
                    
                    echo "<div class='comments-container'>";
                    while ($comment = $comment_result->fetch_assoc()) {
                        echo "<div class='comment'>";
                        echo "<div class='comment-header'>";
                        echo "<strong class='comment-author'><i class='fas fa-user'></i> " . htmlspecialchars($comment['username']) . "</strong>";
                        echo "<span class='comment-date'><i class='far fa-clock'></i> ". date('d.m.Y H:i', strtotime($comment['date_added'])) . "</span>";
                        echo "</div>";
                        echo "<div class='comment-body'>" . nl2br(htmlspecialchars($comment['comment'])) . "</div>";
                        if (isset($_SESSION['username']) && $_SESSION['username'] === $comment['username']) {
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

    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funkcja konwertująca URL YouTube na format embed
            function getYoutubeEmbedUrl(url) {
                if (!url) return null;
                
                // Obsługa różnych formatów URL YouTube
                let videoId = null;
                
                // Format standardowy: https://www.youtube.com/watch?v=VIDEO_ID
                const watchMatch = url.match(/youtube\.com\/watch\?v=([^&]+)/);
                if (watchMatch) {
                    videoId = watchMatch[1];
                }
                
                // Format skrócony: https://youtu.be/VIDEO_ID
                const shortMatch = url.match(/youtu\.be\/([^?&]+)/);
                if (shortMatch) {
                    videoId = shortMatch[1];
                }
                
                // Format embed: https://www.youtube.com/embed/VIDEO_ID
                const embedMatch = url.match(/youtube\.com\/embed\/([^?&]+)/);
                if (embedMatch) {
                    videoId = embedMatch[1];
                }
                
                // Jeśli znaleziono ID filmu, zwróć URL do embedowania
                if (videoId) {
                    return `https://www.youtube.com/embed/${videoId}`;
                }
                
                // W przypadku nieprawidłowego URL, zwróć null
                return null;
            }
            
            // Wstaw YouTube embed
            const youtubeContainer = document.getElementById('youtube-embed-container');
            if (youtubeContainer) {
                const youtubeUrl = youtubeContainer.getAttribute('data-youtube-url');
                const embedUrl = getYoutubeEmbedUrl(youtubeUrl);
                
                if (embedUrl) {
                    const iframe = document.createElement('iframe');
                    iframe.setAttribute('width', '100%');
                    iframe.setAttribute('height', '480');
                    iframe.setAttribute('src', embedUrl);
                    iframe.setAttribute('frameborder', '0');
                    iframe.setAttribute('allowfullscreen', '');
                    iframe.setAttribute('loading', 'lazy');
                    
                    youtubeContainer.appendChild(iframe);
                } else {
                    // W przypadku nieprawidłowego URL YouTube, wyświetl komunikat
                    youtubeContainer.innerHTML = '<p class="video-error">Nieprawidłowy URL wideo</p>';
                }
            }
            
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
            
            // Funkcjonalność usuwania komentarzy
            const deleteCommentButtons = document.querySelectorAll('.btn-delete-comment');
            const deleteCommentModal = document.getElementById('delete-comment-modal');
            const cancelDeleteComment = document.getElementById('cancel-delete-comment');
            const commentUsernameToDelete = document.getElementById('comment-username-to-delete');
            const commentIdToDelete = document.getElementById('comment-id-to-delete');
            
            deleteCommentButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const commentId = this.getAttribute('data-comment-id');
                    const username = this.getAttribute('data-username');
                    
                    // Ustawienie wartości w popupie usuwania komentarza
                    commentUsernameToDelete.textContent = username;
                    commentIdToDelete.value = commentId;
                    
                    // Wyświetlenie popupu usuwania komentarza
                    deleteCommentModal.style.display = 'block';
                });
            });
            
            // Zamknięcie popupu komentarza po kliknięciu Anuluj
            if (cancelDeleteComment) {
                cancelDeleteComment.addEventListener('click', function() {
                    deleteCommentModal.style.display = 'none';
                });
            }
            
            // Zamknięcie popupu komentarza po kliknięciu poza nim
            window.addEventListener('click', function(event) {
                if (event.target === deleteCommentModal) {
                    deleteCommentModal.style.display = 'none';
                }
            });
            
            // Zamknięcie popupu komentarza po naciśnięciu klawisza Escape
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && deleteCommentModal.style.display === 'block') {
                    deleteCommentModal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
<?php
// Zamknięcie połączenia z bazą danych
$conn->close();
?>