<?php

error_log("validation_utils.php ZOSTAŁ PRZETWORZONY I WYKONANY");

function contains_emoji($string) {
    if (empty($string)) {
        return false;
    }
    // Regex do wykrywania popularnych bloków Unicode emoji
    // U+1F600–U+1F64F Emotikony
    // U+1F300–U+1F5FF Różne symbole i piktogramy
    // U+1F680–U+1F6FF Symbole transportu i map
    // U+1F1E0–U+1F1FF Flagi
    // U+2600–U+26FF Różne symbole
    // U+2700–U+27BF Dingbaty
    // U+1F900–U+1F9FF Dodatkowe symbole i piktogramy
    // U+1FA70–U+1FAFF Symbole i piktogramy rozszerzone A
    $emoji_pattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}]/u';
    return preg_match($emoji_pattern, $string) === 1;
}

error_log("UPROSZCZONA funkcja contains_emoji ZDEFINIOWANA w validation_utils.php");

?>