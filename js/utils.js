// Plik z różnymi funkcjami pomocniczymi

// Sprawdza, czy przeglądarka to Firefox
export function isFirefox() {
    return navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
}

// Sprawdza, czy podany ciąg znaków jest poprawnym adresem URL
export function validateUrl(url) {
    try {
        new URL(url);
        return true;
    } catch (e) {
        return false;
    }
}

// Pobiera wartość parametru z adresu URL (query string)
export function getUrlParameter(name) {
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}
