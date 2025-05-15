<?php

error_log("validation_utils.php WAS PARSED AND EXECUTED");

function contains_emoji($string) {
    if (empty($string)) {
        return false;
    }
    // Regex to detect common emoji Unicode blocks
    // U+1F600–U+1F64F Emoticons
    // U+1F300–U+1F5FF Miscellaneous Symbols and Pictographs
    // U+1F680–U+1F6FF Transport and Map Symbols
    // U+1F1E0–U+1F1FF Flags
    // U+2600–U+26FF Miscellaneous Symbols
    // U+2700–U+27BF Dingbats
    // U+1F900–U+1F9FF Supplemental Symbols and Pictographs
    // U+1FA70–U+1FAFF Symbols and Pictographs Extended-A
    $emoji_pattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E0}-\x{1F1FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1FA70}-\x{1FAFF}]/u';
    return preg_match($emoji_pattern, $string) === 1;
}

error_log("SIMPLIFIED contains_emoji function DEFINED in validation_utils.php");

?>