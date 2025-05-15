<?php
// include/messages.php
$messageDisplayed = false; // Flag to track if a message was shown

// Prioritize messages passed via URL parameters
if (isset($_GET['status'], $_GET['message'])) {
    $status = $_GET['status'];
    $raw_text = urldecode($_GET['message']);
    // Sanitize the text first
    $sanitized_text = htmlspecialchars($raw_text, ENT_QUOTES, 'UTF-8');
    // Then replace the newline placeholder with <br />
    $text = str_replace("__NEWLINE__", "<br />", $sanitized_text);
    $messageId = 'flash-message-' . uniqid(); // Generate a unique ID

    if ($status === 'error') {
        // Output error message div with unique ID
        echo "<div id=\"{$messageId}\" class=\"error-message\"><strong>Błąd!</strong> {$text}</div>";

        // Output inline script for timed fade-out
        echo <<<SCRIPT
        <script>
            (function() {
                const errorElement = document.getElementById('{$messageId}');
                if (errorElement) {
                    // Start fade out after 8 seconds
                    setTimeout(function() {
                        errorElement.style.opacity = '0';
                        errorElement.style.transition = 'opacity 1s ease-out';

                        // Remove element after transition (1 second)
                        setTimeout(function() {
                            errorElement.remove();
                        }, 1000);
                    }, 8000);
                }
            })();
        </script>
SCRIPT;
        $messageDisplayed = true; // Mark that a GET error message was shown and handled

    } elseif ($status === 'success') {
        // Handle success messages (can keep simple or add fade-out too if desired)
        $class = 'success-message';
        echo "<div id=\"{$messageId}\" class=\"{$class}\"><strong>Sukces!</strong> {$text}</div>";
         // Optional: Add similar fade-out script for success messages if needed
         echo <<<SCRIPT
         <script>
             (function() {
                 const successElement = document.getElementById('{$messageId}');
                 if (successElement) {
                     // Start fade out after 5 seconds for success
                     setTimeout(function() {
                         successElement.style.opacity = '0';
                         successElement.style.transition = 'opacity 1s ease-out';
 
                         // Remove element after transition (1 second)
                         setTimeout(function() {
                             successElement.remove();
                         }, 1000);
                     }, 5000); // Shorter duration for success
                 }
             })();
         </script>
 SCRIPT;

        $messageDisplayed = true; // Mark that a GET success message was shown
    }
}

// Only check for locally set PHP variables ($error, $message) if no GET message was displayed
// These won't have the fade-out effect unless explicitly added here too.
if (!$messageDisplayed && (isset($error) || isset($message))) {
    if (!empty($error)) {
        $sanitized_error = htmlspecialchars($error, ENT_QUOTES, 'UTF-8');
        $error_text = str_replace("__NEWLINE__", "<br />", $sanitized_error);
        echo "<div class=\"error-message\">{$error_text}</div>";
    }
    if (!empty($message)) {
        $sanitized_message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
        $message_text = str_replace("__NEWLINE__", "<br />", $sanitized_message);
        echo "<div class=\"success-message\">{$message_text}</div>";
    }
}
?>
