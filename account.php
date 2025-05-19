<?php
// Rozpoczynamy sesję, żeby wiedzieć, kto jest zalogowany
session_start();
include 'include/db_config.php';
include __DIR__ . '/include/validation_utils.php'; // Dodanie walidacji emoji

// Sprawdzamy, czy użytkownik jest zalogowany. Jeśli nie, przekierowujemy go do logowania
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Pobieramy dane użytkownika z bazy
$user_sql = "SELECT * FROM accounts WHERE id = $userId";
$user_result = $conn->query($user_sql);

if (!$user_result || $user_result->num_rows === 0) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$user = $user_result->fetch_assoc();

// Obsługa zmiany hasła przez użytkownika
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Wszystkie pola są wymagane.";
    } elseif (contains_emoji($current_password) || contains_emoji($new_password)) { // Walidacja emoji dla haseł
        $error = "Hasła nie mogą zawierać emoji.";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Aktualne hasło jest nieprawidłowe.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Nowe hasła nie są identyczne.";
    } elseif (strlen($new_password) < 6) {
        $error = "Nowe hasło musi mieć co najmniej 6 znaków.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE accounts SET password = '$hashed_password' WHERE id = $userId";
        
        if ($conn->query($update_sql)) {
            $message = "Hasło zostało zmienione pomyślnie.";
        } else {
            $error = "Wystąpił błąd podczas zmiany hasła: " . $conn->error;
        }
    }
}
// Admin: obsługa zarządzania użytkownikami
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $userId == 1) {
    // Usuń użytkownika
    if (isset($_POST['remove_user'])) {
        $tid = (int)$_POST['target_user_id'];
        if ($tid !== 1) {
            $conn->query("DELETE FROM accounts WHERE id = $tid");
            $message = "Użytkownik został usunięty.";
        } else {
            $error = "Nie można usunąć administratora.";
        }
    }
    // Zmień nazwę użytkownika
    if (isset($_POST['change_username_admin'])) {
        $tid = (int)$_POST['target_user_id'];
        $new_username = trim($_POST['new_username']);
        
        // Sprawdź, czy użytkownik o takiej nazwie już istnieje
        $check_sql = "SELECT id FROM accounts WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $new_username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0 && $check_result->fetch_assoc()['id'] != $tid) {
            $error = "Użytkownik o takiej nazwie już istnieje.";
        } else {
            if ($new_username !== '' && !contains_emoji($new_username)) {
                // Używamy prepared statement dla bezpieczeństwa
                $update_sql = "UPDATE accounts SET username = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_username, $tid);
                
                if ($update_stmt->execute()) {
                    $message = "Nazwa użytkownika została zmieniona.";
                } else {
                    $error = "Wystąpił błąd podczas zmiany nazwy: " . $conn->error;
                }
            } else {
                $error = "Nieprawidłowa nazwa użytkownika.";
            }
        }
    }
    // Zmień hasło użytkownika
    if (isset($_POST['change_password_admin'])) {
        $tid = (int)$_POST['target_user_id'];
        $np = $_POST['new_password_admin'];
        $cp = $_POST['confirm_password_admin'];
        
        if (empty($np) || empty($cp)) {
            $error = "Pola hasła nie mogą być puste.";
        } 
        elseif ($np !== $cp) {
            $error = "Hasła nie są identyczne.";
        } 
        elseif (strlen($np) < 6) {
            $error = "Hasło musi mieć co najmniej 6 znaków.";
        } 
        elseif (contains_emoji($np)) {
            $error = "Hasło nie może zawierać emoji.";
        } 
        else {
            // Używamy prepared statement dla bezpieczeństwa
            $hp = password_hash($np, PASSWORD_DEFAULT);
            $update_sql = "UPDATE accounts SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hp, $tid);
            
            if ($update_stmt->execute()) {
                $message = "Hasło użytkownika zostało zmienione.";
            } else {
                $error = "Wystąpił błąd podczas zmiany hasła: " . $conn->error;
            }
        }
    }
}

// Pobieramy listę dystrybucji dodanych przez użytkownika
$distros_sql = "SELECT id, name, date_added FROM distributions WHERE added_by = $userId ORDER BY date_added DESC";
$distros_result = $conn->query($distros_sql);

// Pobieramy komentarze użytkownika
$comments_sql = "SELECT c.id, c.comment, c.date_added, d.id as distro_id, d.name as distro_name 
                FROM comments c JOIN distributions d ON c.distro_id = d.id 
                WHERE c.user_id = $userId ORDER BY c.date_added DESC";
$comments_result = $conn->query($comments_sql);
// Jeśli administrator, pobieramy listę wszystkich użytkowników
if ($userId == 1) {
    $users_sql = "SELECT id, username, email, date_added FROM accounts";
    $users_result = $conn->query($users_sql);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje konto - Baza Dystrybucji Linux</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="favicon.png">
    <script type="module" src="js/script.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Baza Dystrybucji Linux</h1>
            <div class="header-buttons">
                <button id="theme-toggle" class="btn-theme-toggle" title="Przełącz tryb jasny/ciemny">
                    <i id="theme-toggle-icon" class="fas fa-sun"></i>
                </button>
                <a href="index.php" class="btn-return"><i class="fas fa-home"></i> Strona główna</a>
                <a href="logout.php" class="btn-return"><i class="fas fa-sign-out-alt"></i> Wyloguj się</a>
            </div>
        </header>
        
        <main class="account-container">
            <h2><i class="fas fa-user-circle"></i> Moje konto</h2>
            
            <?php include 'include/messages.php'; ?>
            
            <div class="tab-container">
                <div class="tab active" data-tab="profile">Profil</div>
                <div class="tab" data-tab="password">Zmień hasło</div>
                <div class="tab" data-tab="activity">Moja aktywność</div>
                <?php if ($userId == 1): // Dodaj zakładkę admina ?>
                <div class="tab" data-tab="admin">Zarządzaj użytkownikami</div>
                <?php endif; ?>
            </div>
            
            <!-- Profil -->
            <div id="profile" class="account-section tab-content active">
                <h3><i class="fas fa-id-card"></i> Informacje o koncie</h3>
                <div class="user-info">
                    <div class="info-label">Nazwa użytkownika:</div>
                    <div><?php echo htmlspecialchars($user['username']); ?><?php if ($userId == 1) echo " <span class='admin-tag'>Admin</span>"; ?></div>
                    
                    <div class="info-label">Email:</div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                    
                    <div class="info-label">Data rejestracji:</div>
                    <div><?php echo date('d.m.Y H:i', strtotime($user['date_added'])); ?></div>
                </div>
            </div>
            
            <!-- Zmiana hasła -->
            <div id="password" class="account-section tab-content">
                <h3><i class="fas fa-key"></i> Zmień hasło</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="current_password"><i class="fas fa-lock"></i> Aktualne hasło:</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password"><i class="fas fa-lock"></i> Nowe hasło:</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>Minimum 6 znaków</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password"><i class="fas fa-lock"></i> Potwierdź nowe hasło:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-primary">
                        <i class="fas fa-save"></i> Zmień hasło
                    </button>
                </form>
            </div>
            
            <!-- Aktywność -->
            <div id="activity" class="account-section tab-content">
                <h3><i class="fas fa-history"></i> Moja aktywność</h3>
                
                <div class="tab-container activity-tabs">
                    <div class="tab active" data-tab="distros">Dodane dystrybucje (<?php echo $distros_result->num_rows; ?>)</div>
                    <div class="tab" data-tab="comments">Moje komentarze (<?php echo $comments_result->num_rows; ?>)</div>
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
                                <div class="comment-excerpt">
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
                </div> <!-- #comments -->
            </div> <!-- #activity -->
            <?php if ($userId == 1): // Panel admina ?>
            <div id="admin" class="account-section tab-content">
                <h3><i class="fas fa-users-cog"></i> Zarządzanie użytkownikami</h3>
                
                <div class="admin-controls">
                    <div class="admin-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="user-search" placeholder="Szukaj użytkowników..." oninput="filterUsers()">
                    </div>
                </div>
                <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-user"></i> Użytkownik</th>
                                <th><i class="fas fa-envelope"></i> Email</th>
                                <th><i class="fas fa-calendar-alt"></i> Data</th>
                                <th><i class="fas fa-tools"></i> Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($u = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?><?php if ($u['id']==1) echo ' <span class="admin-tag">Admin</span>'; ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo date('d.m.Y', strtotime($u['date_added'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($u['id'] != 1): ?>
                                            <form method="post" class="admin-form">
                                                <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                                <button type="submit" name="remove_user" class="btn-delete" title="Usuń użytkownika" onclick="return confirm('Czy na pewno chcesz usunąć tego użytkownika?')"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <button type="button" class="btn-edit" onclick="toggleEdit(<?php echo $u['id']; ?>)" title="Edytuj użytkownika"><i class="fas fa-edit"></i></button>
                                    </div>
                                    <div id="edit-<?php echo $u['id']; ?>" class="admin-edit-panel" style="display:none;">
                                        <form method="post" action="" class="admin-form">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                            <div class="form-input">
                                                <input type="text" name="new_username" placeholder="Nowa nazwa użytkownika" required minlength="3">
                                            </div>
                                            <button type="submit" name="change_username_admin" class="btn-primary">
                                                <i class="fas fa-check"></i> Zmień nazwę
                                            </button>
                                        </form>
                                        <form method="post" action="" class="admin-form">
                                            <input type="hidden" name="target_user_id" value="<?php echo $u['id']; ?>">
                                            <div class="form-input">
                                                <input type="password" name="new_password_admin" placeholder="Nowe hasło" required>
                                            </div>
                                            <div class="form-input">
                                                <input type="password" name="confirm_password_admin" placeholder="Potwierdź hasło" required>
                                            </div>
                                            <button type="submit" name="change_password_admin" class="btn-primary">
                                                <i class="fas fa-key"></i> Zmień hasło
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-message">Brak użytkowników.</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Tymon3310</p>
        </footer>
    </div>
    
    
</body>
</html>