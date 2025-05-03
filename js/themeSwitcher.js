// Plik obsługujący przełączanie motywów (jasny/ciemny)

// Funkcja inicjalizująca - znajduje przycisk i dodaje nasłuchiwanie na kliknięcie
export function initializeThemeSwitcher() {
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }
    loadThemePreference(); // Wczytaj zapisany motyw przy starcie strony
}

// Funkcja przełączająca motyw
function toggleTheme() {
    // Dodaj lub usuń klasę 'light-mode' z elementu body
    document.body.classList.toggle("light-mode");

    // Sprawdź, czy teraz jest włączony tryb jasny
    const isLightMode = document.body.classList.contains("light-mode");

    // Zapisz wybór użytkownika w pamięci lokalnej przeglądarki (localStorage)
    localStorage.setItem('theme', isLightMode ? 'light' : 'dark');

    // Dodaj na chwilę klasę do <html>, żeby CSS mógł zrobić płynne przejście
    document.documentElement.classList.add('theme-transitioning');

    // Usuń klasę przejścia po zakończeniu animacji (czas musi pasować do CSS)
    setTimeout(() => {
        document.documentElement.classList.remove('theme-transitioning');
    }, 300);

    // Zaktualizuj ikonę i podpowiedź (title) na przycisku
    const themeToggleIcon = document.getElementById('theme-toggle-icon');
    if (themeToggleIcon) {
        themeToggleIcon.className = isLightMode ? 'fas fa-moon' : 'fas fa-sun'; // Zmień ikonę słońca/księżyca
        themeToggleIcon.title = isLightMode ? 'Przełącz na tryb ciemny' : 'Przełącz na tryb jasny'; // Zmień tekst podpowiedzi
    }
}

// Funkcja wczytująca zapisaną preferencję motywu przy ładowaniu strony
function loadThemePreference() {
    // Odczytaj zapisany motyw z localStorage
    const theme = localStorage.getItem('theme');
    const themeToggleIcon = document.getElementById('theme-toggle-icon');

    if (theme === 'light') {
        // Jeśli zapisano 'light', włącz tryb jasny
        document.body.classList.add("light-mode");
        // Ustaw odpowiednią ikonę i podpowiedź na przycisku
        if (themeToggleIcon) {
            themeToggleIcon.className = 'fas fa-moon';
            themeToggleIcon.title = 'Przełącz na tryb ciemny';
        }
    } else {
        // W każdym innym przypadku (brak zapisu lub zapisano 'dark') użyj trybu ciemnego (domyślny)
        document.body.classList.remove("light-mode");
        // Ustaw odpowiednią ikonę i podpowiedź na przycisku
        if (themeToggleIcon) {
            themeToggleIcon.className = 'fas fa-sun';
            themeToggleIcon.title = 'Przełącz na tryb jasny';
        }
    }
}
