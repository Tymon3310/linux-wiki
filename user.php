<?php
session_start();
include 'include/db_config.php';

// Validate user ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php?status=error&message=" . urlencode("Nieprawidłowy identyfikator użytkownika."));
    exit;
}
$user_id = (int)$_GET['id'];

// Fetch user info
$user_sql = "SELECT id, username, date_added FROM accounts WHERE id = $user_id";
$user_result = $conn->query($user_sql);
if (!$user_result || $user_result->num_rows === 0) {
    header("Location: index.php?status=error&message=" . urlencode("Użytkownik nie został znaleziony."));
    exit;
}
$user = $user_result->fetch_assoc();

// Fetch distributions added by user
$distros_sql = "SELECT id, name, date_added FROM distributions WHERE added_by = $user_id ORDER BY date_added DESC";
$distros_result = $conn->query($distros_sql);

// Fetch comments by user
$comments_sql = "SELECT c.id, c.comment, c.date_added, d.id AS distro_id, d.name AS distro_name
                 FROM comments c
                 JOIN distributions d ON c.distro_id = d.id
                 WHERE c.user_id = $user_id
                 ORDER BY c.date_added DESC";
$comments_result = $conn->query($comments_sql);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil użytkownika: <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="favicon.png">
</head>
<body>
    <div class="container">
        <header>
            <h1>Profil użytkownika</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                <a href="index.php" class="btn-return"><i class="fas fa-home"></i> Strona główna</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn-primary"><i class="fas fa-sign-out-alt"></i>  Wyloguj się</a>
                <?php else: ?>
                    <a href="login.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Zaloguj się</a>
                <?php endif; ?>
            </div>
        </header>
        <?php include 'include/messages.php'; ?>
        <main class="account-container">
            <h2><?php echo htmlspecialchars($user['username']); ?><?php if ($user['id'] == 1) echo ' <span class="admin-tag"><i class="fa-solid fa-user-tie"></i> Admin</span>'; ?></h2>
            <div class="user-info">
                <div class="info-label">Nazwa użytkownika:</div>
                <div><?php echo htmlspecialchars($user['username']); ?><?php if ($user['id'] == 1) echo ' <span class="admin-tag">Admin</span>'; ?></div>
                <div class="info-label">Data rejestracji:</div>
                <div><?php echo date('d.m.Y H:i', strtotime($user['date_added'])); ?></div>
            </div>
            <br>
            <div id="activity" >
                <h3><i class="fas fa-history"></i> Aktywność użytkownika</h3>
                
                <div class="tab-container" style="margin-top: 20px;">
                    <div class="tab active" data-tab="distros">Dodane dystrybucje</div>
                    <div class="tab" data-tab="comments">Dodane komentarze</div>
                </div>
                
                <div id="distros" class="tab-content active">
                    <?php if ($distros_result && $distros_result->num_rows > 0): ?>
                        <?php while ($distro = $distros_result->fetch_assoc()): ?>
                            <div class="activity-item">
                                <a href="details.php?id=<?php echo $distro['id']; ?>">
                                    <?php echo htmlspecialchars($distro['name']); ?>
                                </a>
                                <div class="activity-date">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('d.m.Y H:i', strtotime($distro['date_added'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-message">Nie dodałeś jeszcze żadnych dystrybucji.</div>
                    <?php endif; ?>
                </div>
                
                <div id="comments" class="tab-content">
                    <?php if ($comments_result && $comments_result->num_rows > 0): ?>
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="activity-item">
                                <div>
                                    <strong>Komentarz do:</strong> 
                                    <a href="details.php?id=<?php echo $comment['distro_id']; ?>">
                                        <?php echo htmlspecialchars($comment['distro_name']); ?>
                                    </a>
                                </div>
                                <div style="margin-top: 5px;">
                                    <?php 
                                        $comment_text = htmlspecialchars($comment['comment']);
                                        echo (strlen($comment_text) > 100) 
                                            ? substr($comment_text, 0, 100) . '...' 
                                            : $comment_text; 
                                    ?>
                                </div>
                                <div class="activity-date">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('d.m.Y H:i', strtotime($comment['date_added'])); ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-message">Nie dodałeś jeszcze żadnych komentarzy.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Tymon3310</p>
        </footer>
    </div>
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Obsługa zakładek
            const handleTabs = (tabContainerSelector, tabContentSelector) => {
                const tabs = document.querySelectorAll(tabContainerSelector + ' .tab');
                const contents = document.querySelectorAll(tabContentSelector);
                
                tabs.forEach(tab => {
                    tab.addEventListener('click', function() {
                        const tabId = this.getAttribute('data-tab');
                        
                        // Usuwanie klasy aktywnej
                        tabs.forEach(t => t.classList.remove('active'));
                        contents.forEach(c => c.classList.remove('active'));
                        
                        // Dodawanie klasy aktywnej
                        this.classList.add('active');
                        document.getElementById(tabId).classList.add('active');
                    });
                });
            };
            
            // Inicjalizacja głównych zakładek
            handleTabs('.account-container > .tab-container', '.account-section.tab-content');
            
            // Inicjalizacja podzakładek aktywności
            handleTabs('#activity > .tab-container', '#activity .tab-content');
        });
    </script>
</body>
</html>