<?php
// include/messages.php
if (isset($_GET['status'], $_GET['message'])) {
    $class = $_GET['status'] === 'error' ? 'error-message' : 'success-message';
    $text  = htmlspecialchars(urldecode($_GET['message']));
    echo "<div class=\"{$class}\">{$text}</div>";
} elseif (isset($error) || isset($message)) {
    if (!empty($error)) {
        echo "<div class=\"error-message\">" . htmlspecialchars($error) . "</div>";
    }
    if (!empty($message)) {
        echo "<div class=\"success-message\">" . htmlspecialchars($message) . "</div>";
    }
}
