// Obsługa zdarzeń po załadowaniu strony
document.addEventListener('DOMContentLoaded', function () {
    // Inicjalizacja przycisku pokazującego formularz dodawania
    const showAddFormButton = document.getElementById('show-add-form');
    if (showAddFormButton) {
        showAddFormButton.addEventListener('click', function () {
            const searchInput = document.getElementById('search-input');
            const nameInput = document.getElementById('name');
            const hiddenInput = document.getElementById('distro-name-hidden');

            // Przekopiowanie wartości z pola wyszukiwania do pola nazwy w formularzu
            if (searchInput && nameInput && hiddenInput) {
                nameInput.value = searchInput.value;
                hiddenInput.value = searchInput.value;
            }

            // Wyświetlenie formularza dodawania
            const addFormContainer = document.getElementById('add-form-container');
            if (addFormContainer) {
                addFormContainer.style.display = 'block';

                // Płynne przewinięcie do formularza
                addFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

                // Ustawienie fokusu na odpowiednie pole
                setTimeout(() => {
                    if (!nameInput.value) {
                        nameInput.focus();
                    } else {
                        const descriptionInput = document.getElementById('description');
                        if (descriptionInput) descriptionInput.focus();
                    }
                }, 500);
            }
        });
    }

    // Funkcja przełączania między trybami jasnym i ciemnym
    function toggleTheme() {
        document.body.classList.toggle("light-mode");

        // Store theme preference
        const isLightMode = document.body.classList.contains("light-mode");
        localStorage.setItem('theme', isLightMode ? 'light' : 'dark');

        // Add transition class to animate theme change
        document.documentElement.classList.add('theme-transitioning');

        // Remove transition class after animation completes
        setTimeout(() => {
            document.documentElement.classList.remove('theme-transitioning');
        }, 300); // Match this to your CSS transition time

        // Update theme toggle button icon
        const themeToggleIcon = document.getElementById('theme-toggle-icon');
        if (themeToggleIcon) {
            themeToggleIcon.className = isLightMode ? 'fas fa-moon' : 'fas fa-sun';
            themeToggleIcon.title = isLightMode ? 'Przełącz na tryb ciemny' : 'Przełącz na tryb jasny';
        }
    }

    // Initialize theme toggle button
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }

    // Load theme preference from local storage
    function loadThemePreference() {
        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            toggleTheme(); // Apply light mode if it was saved
        }
    }

    // Load theme preference when page loads
    loadThemePreference();

    // Funkcja do wykonywania wyszukiwania dystrybucji
    window.performSearch = function () {
        const searchInput = document.getElementById('search-input');
        if (!searchInput) return;

        // Sprawdzenie czy zapytanie ma wystarczającą długość
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length < 2) return;

        // Pokaż animację ładowania
        const resultsDiv = document.getElementById('results');
        if (!resultsDiv) return;
        resultsDiv.innerHTML = '<div class="loading">Szukam dystrybucji...</div>';

        // Aktualizacja adresu URL z zapytaniem wyszukiwania
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('q', searchTerm);
        window.history.pushState({}, '', newUrl);

        // Wykonanie zapytania AJAX do serwera
        fetch(`search.php?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '';

                // Obsługa przypadku gdy nie znaleziono wyników
                if (data.length === 0) {
                    resultsDiv.innerHTML = `
                        <div class="no-results">
                            <p>Nie znaleziono dystrybucji "${searchTerm}".</p>
                            <p>Czy chcesz dodać tę dystrybucję do naszej bazy danych?</p>
                            <button id="add-missing-distro" class="btn">Dodaj nową dystrybucję</button>
                        </div>
                    `;

                    // Obsługa przycisku dodawania nowej dystrybucji
                    document.getElementById('add-missing-distro').addEventListener('click', function () {
                        const addFormContainer = document.getElementById('add-form-container');
                        const nameInput = document.getElementById('name');

                        if (addFormContainer && nameInput) {
                            nameInput.value = searchTerm;
                            addFormContainer.style.display = 'block';
                            addFormContainer.scrollIntoView({ behavior: 'smooth' });

                            const descriptionInput = document.getElementById('description');
                            if (descriptionInput) descriptionInput.focus();
                        }
                    });
                } else {
                    // Generowanie HTML z wynikami wyszukiwania
                    let resultsHTML = '<h2 class="section-title">Wyniki wyszukiwania dla: "' + searchTerm + '"</h2>';
                    resultsHTML += '<div class="search-results">';
                    data.forEach(distro => {
                        const imagePath = distro.logo_path || '';
                        resultsHTML += `
                            <div class="distro-card">
                                <img src="${imagePath}" alt="${distro.name}" class="distro-logo">
                                <h3>${distro.name}</h3>
                                <p>${distro.description.substring(0, 150)}${distro.description.length > 150 ? '...' : ''}</p>
                                <div class="card-buttons">
                                    <a href="details.php?id=${distro.id}" class="btn-details"><i class="fas fa-info-circle"></i> Szczegóły</a>
                                    <a href="edit.php?id=${distro.id}" class="btn-edit"><i class="fas fa-edit"></i> Edytuj</a>
                                </div>
                            </div>
                        `;
                    });
                    resultsHTML += '</div>';
                    resultsDiv.innerHTML = resultsHTML;
                }
            })
            .catch(error => {
                // Obsługa błędów zapytania AJAX
                resultsDiv.innerHTML = `<div class="error-message">Błąd wyszukiwania: ${error.message}</div>`;
            });
    }

    // Automatyczne wyszukiwanie jeśli w URL znajduje się parametr q
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('q');
    if (searchQuery) {
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = searchQuery;
            performSearch();
        }
    }

    // Obsługa przycisku wyszukiwania
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', function (e) {
            e.preventDefault();
            performSearch();
        });
    }

    // Wyszukiwanie podczas wpisywania z opóźnieniem
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        let typingTimer;
        const doneTypingInterval = 500; // czas w milisekundach po zatrzymaniu pisania

        searchInput.addEventListener('input', function () {
            clearTimeout(typingTimer);
            if (searchInput.value) {
                typingTimer = setTimeout(performSearch, doneTypingInterval);
            } else {
                window.location.href = window.location.pathname;
            }
        });
    }

    // Funkcja pomocnicza do pobierania parametrów z URL
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Obsługa komunikatów o sukcesie i błędach
    const status = getUrlParameter('status');
    const added = getUrlParameter('added');
    const message = getUrlParameter('message');

    // Wyświetlanie komunikatu o powodzeniu operacji
    if (status === 'success' && added) {
        const resultsDiv = document.getElementById('results');
        if (resultsDiv) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<p><strong>Sukces!</strong> Dystrybucja "${added}" została pomyślnie dodana do bazy danych!</p>`;

            resultsDiv.insertBefore(successDiv, resultsDiv.firstChild);

            // Automatyczne ukrywanie komunikatu po czasie
            setTimeout(function () {
                successDiv.style.opacity = '0';
                successDiv.style.transition = 'opacity 1s';

                setTimeout(function () {
                    successDiv.remove();
                }, 1000);
            }, 5000);
        }
    }
    // Wyświetlanie komunikatu o błędzie
    else if (status === 'error' && message) {
        const resultsDiv = document.getElementById('results');
        if (resultsDiv) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = `<p><strong>Błąd!</strong> ${message}</p>`;

            resultsDiv.insertBefore(errorDiv, resultsDiv.firstChild);

            // Automatyczne ukrywanie komunikatu po czasie
            setTimeout(function () {
                errorDiv.style.opacity = '0';
                errorDiv.style.transition = 'opacity 1s';

                setTimeout(function () {
                    errorDiv.remove();
                }, 1000);
            }, 8000);
        }
    }

    // Walidacja formularza przed wysłaniem
    const addForm = document.getElementById('add-form');
    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const logoInput = document.getElementById('logo');
            const websiteInput = document.getElementById('website');

            let isValid = true;
            let errorMessages = [];

            // Walidacja nazwy
            if (!nameInput.value.trim()) {
                isValid = false;
                errorMessages.push('Proszę podać nazwę dystrybucji');
                nameInput.classList.add('error-field');
            } else {
                nameInput.classList.remove('error-field');
            }

            // Walidacja opisu
            if (!descriptionInput.value.trim()) {
                isValid = false;
                errorMessages.push('Proszę podać opis dystrybucji');
                descriptionInput.classList.add('error-field');
            } else if (descriptionInput.value.trim().length < 30) {
                isValid = false;
                errorMessages.push('Opis powinien zawierać co najmniej 30 znaków');
                descriptionInput.classList.add('error-field');
            } else {
                descriptionInput.classList.remove('error-field');
            }

            // Walidacja logo
            if (!logoInput.files || logoInput.files.length === 0) {
                isValid = false;
                errorMessages.push('Proszę wybrać plik z logo');
                logoInput.classList.add('error-field');
            } else {
                const allowedTypes = ['image/png', 'image/jpeg', 'image/gif', 'image/svg+xml'];
                if (!allowedTypes.includes(logoInput.files[0].type)) {
                    isValid = false;
                    errorMessages.push('Logo musi być w formacie: PNG, JPEG, GIF lub SVG');
                    logoInput.classList.add('error-field');
                } else if (logoInput.files[0].size > 2 * 1024 * 1024) {
                    isValid = false;
                    errorMessages.push('Logo nie może przekraczać 2MB');
                    logoInput.classList.add('error-field');
                } else {
                    logoInput.classList.remove('error-field');
                }
            }

            // Walidacja adresu strony (opcjonalnie)
            if (websiteInput && websiteInput.value.trim()) {
                const urlPattern = /^(https?:\/\/)([\w-]+(\.[\w-]+)+)([\w.,@?^=%&:/~+#-]*[\w@?^=%&/~+#-])?$/;
                if (!urlPattern.test(websiteInput.value.trim())) {
                    isValid = false;
                    errorMessages.push('Proszę podać poprawny adres URL (np. https://example.com)');
                    websiteInput.classList.add('error-field');
                } else {
                    websiteInput.classList.remove('error-field');
                }
            }

            // Jeśli walidacja nie przeszła, zatrzymaj wysyłanie formularza i pokaż błędy
            if (!isValid) {
                event.preventDefault();

                const errorContainer = document.createElement('div');
                errorContainer.className = 'validation-errors';

                let errorHTML = '<ul>';
                errorMessages.forEach(msg => {
                    errorHTML += `<li>${msg}</li>`;
                });
                errorHTML += '</ul>';
                errorContainer.innerHTML = errorHTML;

                // Usuń poprzednie komunikaty o błędach
                const existingErrors = addForm.querySelectorAll('.validation-errors');
                existingErrors.forEach(el => el.remove());

                // Dodaj nowy komunikat o błędach
                addForm.insertBefore(errorContainer, addForm.firstChild);

                // Przewiń do komunikatu o błędach
                errorContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
});