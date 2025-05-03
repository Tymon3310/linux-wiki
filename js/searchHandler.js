// Plik obsługujący wyszukiwarkę dystrybucji

import { getUrlParameter } from './utils.js';

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

    // Jeśli pole wyszukiwania jest puste, czyścimy wyniki i URL
    if (searchTerm === '') {
        resultsDiv.innerHTML = ''; // Wyczyść poprzednie wyniki
        // Zaktualizuj URL, usuwając parametr 'q'
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.delete('q');
        window.history.pushState({}, '', newUrl); // Zmień URL bez przeładowania strony
        // Można by tu przywrócić domyślną listę, ale na razie samo czyszczenie wystarczy
        return;
    }

    // Pokaż animację ładowania tylko, gdy coś faktycznie szukamy
    resultsDiv.innerHTML = '<div class="loading">Szukam dystrybucji... <i class="fas fa-spinner fa-spin"></i></div>';

    // Zaktualizuj URL strony, dodając parametr wyszukiwania (?q=...)
    // Dzięki temu można skopiować link z wynikami wyszukiwania
    const newUrl = new URL(window.location.href);
    newUrl.searchParams.set('q', searchTerm);
    window.history.pushState({}, '', newUrl);

    // Wyślij zapytanie do serwera (do pliku search.php)
    fetch(`search.php?q=${encodeURIComponent(searchTerm)}`)
        .then(response => {
            // Sprawdź, czy serwer odpowiedział poprawnie (status 200 OK)
            if (!response.ok) {
                throw new Error(`Coś poszło nie tak z zapytaniem! Status: ${response.status}`);
            }
            return response.json(); // Przekształć odpowiedź z JSON na obiekt JavaScript
        })
        .then(data => {
            // Mamy dane! Najpierw wyczyść komunikat "Szukam..."
            resultsDiv.innerHTML = '';

            // Sprawdź, czy serwer znalazł cokolwiek
            if (data.length === 0) {
                // Nic nie znaleziono :( Pokaż odpowiedni komunikat
                // Sprawdź, czy użytkownik jest zalogowany (korzystając ze zmiennej globalnej)
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
                                // W ostateczności można by przekierować na osobną stronę dodawania
                                // window.location.href = `add_distro.php?name=${encodeURIComponent(searchTerm)}`;
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
            } else {
                // Coś znaleziono! Wygeneruj HTML z wynikami
                let resultsHTML = `<h2 class="section-title">Wyniki wyszukiwania dla: "${escapeHTML(searchTerm)}"</h2>`;
                resultsHTML += '<div class="distro-grid search-results">'; // Użyj tej samej siatki co na stronie głównej
                data.forEach(distro => {
                    // Upewnij się, że ścieżka do obrazka jest bezpieczna i użyj domyślnego, jeśli brakuje
                    const imagePath = escapeHTML(distro.logo_path || 'img/default.png');
                    const distroName = escapeHTML(distro.name);
                    const distroDesc = escapeHTML(distro.description);
                    // Skróć opis, żeby nie był za długi na karcie
                    const shortDesc = distroDesc.length > 100 ? distroDesc.substring(0, 100) + '...' : distroDesc;

                    resultsHTML += `
                        <div class="distro-card">
                            <a href="details.php?id=${distro.id}" class="card-link">
                                <div class="card-image-container">
                                    <img src="${imagePath}" alt="Logo ${distroName}" class="distro-logo" loading="lazy">
                                </div>
                                <div class="card-content">
                                    <h3>${distroName}</h3>
                                    <p>${shortDesc}</p>
                                </div>
                            </a>
                            <div class="card-actions">
                                <a href="details.php?id=${distro.id}" class="btn-details"><i class="fas fa-info-circle"></i> Szczegóły</a>
                                <!-- Można by dodać przycisk edycji dla zalogowanych adminów -->
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
    const searchButton = document.getElementById('search-button'); // Zakładając, że masz przycisk

    if (!searchInput) return; // Jak nie ma pola, to nic nie inicjalizujemy

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
