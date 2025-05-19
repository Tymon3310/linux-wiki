// Plik obsługujący wyszukiwarkę dystrybucji

import { getUrlParameter } from './utils.js';

let initialResultsHTML = ''; // Zmienna do przechowywania początkowego HTML wyników

// Funkcja do "oczyszczania" tekstu przed wstawieniem go do HTML
// Zapobiega to atakom XSS (Cross-Site Scripting)
function escapeHTML(str) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// Główna funkcja wykonująca wyszukiwanie
function performSearch() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return; // Jak nie ma pola wyszukiwania, to nic nie robimy

    const searchTerm = searchInput.value.trim();

    const resultsDiv = document.getElementById('results');
    if (!resultsDiv) return; // Jak nie ma gdzie wyświetlić wyników, to też nic nie robimy

    // Jeśli pole wyszukiwania jest puste, przywróć początkowy HTML i zaktualizuj URL
    if (searchTerm === '') {
        resultsDiv.innerHTML = initialResultsHTML; // Przywróć oryginalną zawartość
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.delete('q');
        window.history.pushState({}, '', newUrl);
        return; // Zakończ funkcję, nie wykonuj fetch
    }

    // Pokaż animację ładowania tylko, gdy coś faktycznie szukamy
    resultsDiv.innerHTML = '<div class="loading">Ładuję... <i class="fas fa-spinner fa-spin"></i></div>';

    // Wyślij zapytanie do serwera (do pliku search.php)
    // encodeURIComponent(searchTerm) będzie pustym stringiem jeśli searchTerm jest pusty,
    // co jest prawidłowe dla search.php, aby zwrócić domyślną listę.
    fetch(`search.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            // Sprawdź, czy serwer odpowiedział poprawnie (status 200 OK)
            if (!response.ok) {
                throw new Error(`Coś poszło nie tak z zapytaniem! Status: ${response.status}`);
            }
            return response.json(); // Przekształć odpowiedź z JSON na obiekt JavaScript
        })
        .then(data => {
            // Mamy dane! Najpierw wyczyść komunikat "Ładuję..."
            resultsDiv.innerHTML = '';

            // Sprawdź, czy serwer znalazł cokolwiek
            if (data.length === 0) {
                if (searchTerm === '') {
                    // Jeśli searchTerm był pusty i nic nie wróciło (co nie powinno się zdarzyć z obecnym backendem)
                    resultsDiv.innerHTML = '<div class="no-results"><p><i class="fas fa-info-circle"></i> Brak dystrybucji do wyświetlenia.</p></div>';
                } else {
                    // Nic nie znaleziono dla konkretnego zapytania :( Pokaż odpowiedni komunikat
                    const isUserLoggedIn = typeof window.isUserLoggedIn !== 'undefined' ? window.isUserLoggedIn : false;
                    if (isUserLoggedIn) {
                        // Zalogowany? Zaproponuj dodanie nowej dystrybucji
                        resultsDiv.innerHTML = `
                            <div class="no-results">
                                <p><i class="fas fa-ghost"></i> Ups! Nie znaleźliśmy nic pasującego do "${escapeHTML(searchTerm)}".</p>
                                <p>Może chcesz dodać tę dystrybucję do naszej bazy?</p>
                                <button id="add-missing-distro" class="btn"><i class="fas fa-plus-circle"></i> Dodaj nową dystrybucję</button>
                            </div>
                        `;

                        // Dodaj obsługę kliknięcia przycisku "Dodaj nową dystrybucję"
                        const addMissingButton = document.getElementById('add-missing-distro');
                        if (addMissingButton) {
                            addMissingButton.addEventListener('click', function () {
                                const addFormContainer = document.getElementById('add-form-container');
                                const nameInput = document.getElementById('name');

                                if (addFormContainer && nameInput) {
                                    nameInput.value = searchTerm; // Wypełnij pole nazwy tym, czego szukał użytkownik
                                    addFormContainer.style.display = 'block'; // Pokaż formularz dodawania
                                    addFormContainer.scrollIntoView({ behavior: 'smooth' }); // Przewiń do formularza

                                    // Ustaw fokus na polu opisu, żeby od razu można było pisać
                                    const descriptionInput = document.getElementById('description');
                                    if (descriptionInput) descriptionInput.focus();
                                } else {
                                    console.error("Nie znaleziono kontenera formularza dodawania lub pola nazwy.");
                                }
                            });
                        }
                    } else {
                        // Niezalogowany? Poinformuj, że trzeba się zalogować, żeby dodawać
                        resultsDiv.innerHTML = `
                            <div class="no-results">
                                <p><i class="fas fa-search-minus"></i> Nie znaleźliśmy nic pasującego do "${escapeHTML(searchTerm)}".</p>
                                <p>Zaloguj się, aby móc dodawać nowe dystrybucje.</p>
                                <a href="login.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Zaloguj się</a>
                            </div>
                        `;
                    }
                }
            } else {
                // Coś znaleziono! Wygeneruj HTML z wynikami
                let resultsHTML = '';
                if (searchTerm === '') {
                    resultsHTML = `<h2 class="section-title">Przeglądaj dystrybucje</h2>`; // Tytuł dla domyślnej listy
                } else {
                    resultsHTML = `<h2 class="section-title">Wyniki wyszukiwania dla: "${escapeHTML(searchTerm)}"</h2>`;
                }
                resultsHTML += '<div class="distro-grid search-results">'; // Użyj tej samej siatki co na stronie głównej
                data.forEach(distro => {
                    // Upewnij się, że ścieżka do obrazka jest bezpieczna i użyj domyślnego, jeśli brakuje
                    const imagePath = escapeHTML(distro.logo_path || 'img/default.png');
                    const distroName = escapeHTML(distro.name);
                    const distroDesc = escapeHTML(distro.description);
                    // Skróć opis, żeby nie był za długi na karcie
                    const shortDesc = distroDesc.length > 150 ? distroDesc.substring(0, 150) + '...' : distroDesc;

                    resultsHTML += `
                        <div class="distro-card">
                            <img src="${imagePath}" alt="${distroName}" class="distro-logo">
                            <h3>${distroName}</h3>
                            <p>${shortDesc}</p>
                            <div class="card-buttons">
                                <a href="details.php?id=${distro.id}" class="btn-details"><i class="fas fa-info-circle"></i> Szczegóły</a>
                            </div>
                        </div>
                    `;
                });
                resultsHTML += '</div>'; // Zamknij distro-grid
                resultsDiv.innerHTML = resultsHTML; // Wstaw gotowy HTML do diva z wynikami
            }
        })
        .catch(error => {
            // Oj, coś poszło nie tak podczas komunikacji z serwerem
            console.error('Błąd podczas wyszukiwania:', error);
            resultsDiv.innerHTML = `<div class="error-message"><p><i class="fas fa-exclamation-triangle"></i> Wystąpił błąd podczas wyszukiwania. Spróbuj ponownie później.</p><p><small>${error.message}</small></p></div>`;
        });
}

// Funkcja inicjalizująca wyszukiwarkę
export function initializeSearch() {
    const searchInput = document.getElementById('search-input');
    const searchButton = document.getElementById('search-button');
    const resultsDiv = document.getElementById('results');

    if (!searchInput || !resultsDiv) return; // Jak nie ma pola lub sekcji wyników, to nic nie inicjalizujemy

    // Zapisz początkową zawartość sekcji wyników, jeśli jeszcze nie została zapisana
    if (initialResultsHTML === '') {
        initialResultsHTML = resultsDiv.innerHTML;
    }

    // Wypełnij pole wyszukiwania wartością z URL, jeśli jest (np. po odświeżeniu strony z ?q=...)
    const initialQuery = getUrlParameter('q');
    if (initialQuery) {
        searchInput.value = initialQuery;
        performSearch(); // Od razu wykonaj wyszukiwanie
    }

    // Użyjemy "debouncingu", żeby nie wysyłać zapytania po każdej wpisanej literze,
    // tylko chwilę po tym, jak użytkownik przestanie pisać.
    let debounceTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer); // Anuluj poprzedni timer, jeśli jeszcze nie minął
        // Ustaw nowy timer - wyszukiwanie wykona się 300ms po ostatnim wpisanym znaku
        debounceTimer = setTimeout(performSearch, 300);
    });

    // Dodaj obsługę kliknięcia przycisku wyszukiwania (jeśli istnieje)
    if (searchButton) {
        searchButton.addEventListener('click', (event) => {
            event.preventDefault(); // Zapobiegaj domyślnej akcji przycisku (np. wysłaniu formularza)
            clearTimeout(debounceTimer); // Anuluj ewentualny timer z inputa
            performSearch(); // Wykonaj wyszukiwanie od razu
        });
    }

    // Można też dodać obsługę wciśnięcia Enter w polu wyszukiwania
    searchInput.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault(); // Zapobiegaj domyślnej akcji (np. wysłaniu formularza)
            clearTimeout(debounceTimer);
            performSearch();
        }
    });
}
