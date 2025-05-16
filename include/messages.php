<?php
// Plik do obsługi wyświetlania komunikatów (błędów, sukcesów)

$messageDisplayed = false; // Flaga śledząca, czy komunikat został już wyświetlony

// Priorytet dla komunikatów przekazywanych przez parametry URL (GET)
if (isset($_GET['status'], $_GET['message'])) {
    $status = $_GET['status'];
    $raw_text = urldecode($_GET['message']);
    // Najpierw oczyszczenie tekstu (zabezpieczenie przed XSS)
    $sanitized_text = htmlspecialchars($raw_text, ENT_QUOTES, 'UTF-8');
    // Następnie zamiana znacznika nowej linii na tag <br />
    $text = str_replace("__NEWLINE__", "<br />", $sanitized_text);
    $messageId = 'flash-message-' . uniqid(); // Wygenerowanie unikalnego ID dla elementu komunikatu

    if ($status === 'error') {
        // Wyświetlenie diva z komunikatem o błędzie, używając unikalnego ID
        echo "<div id=\"{$messageId}\" class=\"error-message\"><strong>Błąd!</strong> {$text}</div>";

        // Wyświetlenie skryptu JavaScript (inline) do animowanego ukrycia i usunięcia komunikatu
        echo <<<SCRIPT
        <script>
            (function() {
                const errorElement = document.getElementById('{$messageId}');
                if (errorElement) {
                    // Rozpoczęcie animacji zanikania po 8 sekundach
                    setTimeout(function() {
                        errorElement.style.opacity = '0';
                        errorElement.style.transition = 'opacity 1s ease-out';

                        // Usunięcie elementu z DOM po zakończeniu animacji (1 sekunda)
                        setTimeout(function() {
                            errorElement.remove();
                        }, 1000);
                    }, 8000);
                }
            })();
        </script>
SCRIPT;
        $messageDisplayed = true; // Ustawienie flagi, że komunikat błędu z GET został wyświetlony i obsłużony

    } elseif ($status === 'success') {
        // Obsługa komunikatów o sukcesie (można je uprościć lub dodać podobne wygaszanie)
        $class = 'success-message';
        echo "<div id=\"{$messageId}\" class=\"{$class}\"><strong>Sukces!</strong> {$text}</div>";
         echo <<<SCRIPT
         <script>
             (function() {
                 const successElement = document.getElementById('{$messageId}');
                 if (successElement) {
                     // Rozpoczęcie animacji zanikania po 5 sekundach dla komunikatu o sukcesie
                     setTimeout(function() {
                         successElement.style.opacity = '0';
                         successElement.style.transition = 'opacity 1s ease-out';
 
                         // Usunięcie elementu z DOM po zakończeniu animacji (1 sekunda)
                         setTimeout(function() {
                             successElement.remove();
                         }, 1000);
                     }, 5000); // Krótszy czas trwania dla komunikatu o sukcesie
                 }
             })();
         </script>
 SCRIPT;

        $messageDisplayed = true; // Ustawienie flagi, że komunikat sukcesu z GET został wyświetlony
    }
}

// Sprawdzenie lokalnie ustawionych zmiennych PHP ($error, $message), jeśli nie wyświetlono komunikatu z GET
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
