// Poczekaj, aż strona w pełni się załaduje, zanim zaczniemy działać!
document.addEventListener('DOMContentLoaded', function () {
    // Przygotuj przycisk, który pokaże formularz do dodania nowej dystrybucji
    const showAddFormButtons = document.querySelectorAll('#show-add-form');
    const addFormContainer = document.getElementById('add-form-container');
    const loginPrompt = document.getElementById('login-prompt');

    showAddFormButtons.forEach(button => {
        button.addEventListener('click', function () {
            if (isUserLoggedIn && addFormContainer) {
                addFormContainer.style.display = 'block';
                addFormContainer.scrollIntoView({ behavior: 'smooth' });

                const nameInput = document.getElementById('name');
                if (nameInput) nameInput.focus();
            } else if (loginPrompt) {
                loginPrompt.style.display = 'block';
                loginPrompt.scrollIntoView({ behavior: 'smooth' });
            } else {
                window.location.href = 'login.php';
            }
        });
    });

    // Sprawdź, czy korzystasz z przeglądarki Firefox (przyda się później)
    function isFirefox() {
        return navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    }

    // Ta funkcja pokaże specjalny komunikat, jeśli Firefox nie radzi sobie z przeciąganiem obrazka
    function showFirefoxError(dropZone) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'upload-error';
        errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Nie można przeciągnąć tego obrazu. W przeglądarce Firefox zapisz obraz na dysku (prawy przycisk myszy -> Zapisz obraz jako...), a następnie go przeciągnij lub użyj przycisku wyboru pliku.</p>';

        // Najpierw usuń stare komunikaty o błędach, żeby nie było bałaganu
        const existingErrors = dropZone.querySelectorAll('.upload-error');
        existingErrors.forEach(err => err.remove());

        dropZone.appendChild(errorMsg);

        // Schowaj komunikat po 7 sekundach, żeby nie przeszkadzał za długo
        setTimeout(() => {
            errorMsg.style.opacity = '0';
            setTimeout(() => errorMsg.remove(), 500);
        }, 7000);
    }

    // Obsługujemy tutaj formularz dodawania nowej dystrybucji Linuxa
    const addForm = document.getElementById('add-form');
    if (addForm) {
        addForm.addEventListener('submit', function (event) {
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const logoInput = document.getElementById('logo');
            const websiteInput = document.getElementById('website');

            let isValid = true;
            let errorMessages = [];

            // Sprawdź, czy użytkownik wpisał nazwę dystrybucji
            if (!nameInput.value.trim()) {
                isValid = false;
                errorMessages.push('Proszę podać nazwę dystrybucji');
                nameInput.classList.add('error-field');
            } else {
                nameInput.classList.remove('error-field');
            }

            // Sprawdź, czy użytkownik dodał opis
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

            // Sprawdź, czy użytkownik dodał logo (tylko przy dodawaniu, nie przy edycji)
            if (!window.location.pathname.includes('edit.php') && (!logoInput.files || logoInput.files.length === 0)) {
                isValid = false;
                errorMessages.push('Proszę dodać logo dystrybucji');
                logoInput.parentElement.classList.add('error-field');
            } else {
                logoInput.parentElement.classList.remove('error-field');
            }

            // Sprawdź, czy podany adres strony jest poprawny (jeśli w ogóle coś wpisano)
            if (websiteInput.value.trim() && !validateUrl(websiteInput.value)) {
                isValid = false;
                errorMessages.push('Proszę podać prawidłowy adres URL strony internetowej');
                websiteInput.classList.add('error-field');
            } else {
                websiteInput.classList.remove('error-field');
            }

            // Jeśli są błędy, nie wysyłaj formularza dalej
            if (!isValid) {
                event.preventDefault();

                // Przygotuj ładny komunikat z listą błędów
                const errorContainer = document.createElement('div');
                errorContainer.className = 'validation-errors';
                let errorHTML = '<h3>Proszę poprawić następujące błędy:</h3><ul>';
                errorMessages.forEach(msg => {
                    errorHTML += `<li>${msg}</li>`;
                });
                errorHTML += '</ul>';
                errorContainer.innerHTML = errorHTML;

                // Najpierw usuń stare komunikaty o błędach, żeby nie było ich za dużo
                const existingErrors = addForm.querySelectorAll('.validation-errors');
                existingErrors.forEach(el => el.remove());

                // Dodaj nowy komunikat z błędami na górę formularza
                addForm.insertBefore(errorContainer, addForm.firstChild);

                // Przewiń stronę do komunikatu, żeby użytkownik od razu go zobaczył
                errorContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Obsługujemy też formularz edycji dystrybucji, żeby było spójnie
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const websiteInput = document.getElementById('website');

            let isValid = true;
            let errorMessages = [];

            // Sprawdź, czy użytkownik wpisał nazwę dystrybucji (edycja)
            if (!nameInput.value.trim()) {
                isValid = false;
                errorMessages.push('Proszę podać nazwę dystrybucji');
                nameInput.classList.add('error-field');
            } else {
                nameInput.classList.remove('error-field');
            }

            // Sprawdź, czy użytkownik dodał opis (edycja)
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

            // Sprawdź, czy podany adres strony jest poprawny (edycja)
            if (websiteInput.value.trim() && !validateUrl(websiteInput.value)) {
                isValid = false;
                errorMessages.push('Proszę podać prawidłowy adres URL strony internetowej');
                websiteInput.classList.add('error-field');
            } else {
                websiteInput.classList.remove('error-field');
            }

            // Jeśli są błędy, nie wysyłaj formularza dalej
            if (!isValid) {
                event.preventDefault();

                // Przygotuj ładny komunikat z listą błędów
                const errorContainer = document.createElement('div');
                errorContainer.className = 'validation-errors';
                let errorHTML = '<h3>Proszę poprawić następujące błędy:</h3><ul>';
                errorMessages.forEach(msg => {
                    errorHTML += `<li>${msg}</li>`;
                });
                errorHTML += '</ul>';
                errorContainer.innerHTML = errorHTML;

                // Najpierw usuń stare komunikaty o błędach, żeby nie było ich za dużo
                const existingErrors = editForm.querySelectorAll('.validation-errors');
                existingErrors.forEach(el => el.remove());

                // Dodaj nowy komunikat z błędami na górę formularza
                editForm.insertBefore(errorContainer, editForm.firstChild);

                // Przewiń stronę do komunikatu, żeby użytkownik od razu go zobaczył
                errorContainer.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }

    // Obsługa Drag & Drop oraz wklejania obrazów do pola logo
    const logoInput = document.getElementById('logo');
    const dropZones = document.querySelectorAll('.file-upload-container');

    // Jeśli mamy logoInput i przynajmniej jeden dropZone
    if (logoInput && dropZones.length > 0) {
        console.log('Image upload handlers initialized');

        // Iterujemy przez wszystkie dropZones (może być jeden w indeksie i jeden w edit)
        dropZones.forEach(dropZone => {
            // Dodanie wizualnej informacji - using the same text as the old drop zone
            let uploadHint = document.createElement('div');
            uploadHint.className = 'upload-hint';

            // Specjalne ostrzeżenie dla Firefoksa
            if (isFirefox()) {
                uploadHint.innerHTML = '<p>Przeciągnij i upuść logo tutaj lub kliknij, aby wybrać plik<br>Możesz też wkleić obraz ze schowka (Ctrl+V)</p>' +
                    '<p class="firefox-warning"><i class="fas fa-exclamation-triangle"></i> Uwaga: Firefox może nie obsługiwać przeciągania obrazów z innych stron. Zalecamy zapisać obraz na dysk i przeciągnąć go stąd.</p>';
            } else {
                uploadHint.innerHTML = '<p>Przeciągnij i upuść logo tutaj lub kliknij, aby wybrać plik<br>Możesz też wkleić obraz ze schowka (Ctrl+V)</p>';
            }

            // Remove any existing upload hints first to prevent duplicates
            const existingHints = dropZone.querySelectorAll('.upload-hint');
            existingHints.forEach(hint => hint.remove());

            dropZone.appendChild(uploadHint);

            // Styling dla drop zone
            dropZone.style.position = 'relative';
            dropZone.style.border = '2px dashed #ccc';
            dropZone.style.borderRadius = '4px';
            dropZone.style.padding = '20px';
            dropZone.style.textAlign = 'center';
            dropZone.style.cursor = 'pointer';

            // Drag & drop dla obrazów
            dropZone.addEventListener('dragover', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.border = '2px dashed #4CAF50';
                this.style.backgroundColor = 'rgba(76, 175, 80, 0.1)';
            });

            dropZone.addEventListener('dragleave', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.border = '2px dashed #ccc';
                this.style.backgroundColor = '';
            });

            dropZone.addEventListener('drop', function (e) {
                e.preventDefault();
                e.stopPropagation();
                this.style.border = '2px dashed #ccc';
                this.style.backgroundColor = '';

                // Najpierw sprawdź, czy mamy pliki bezpośrednio (działa w większości przypadków)
                if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                    const file = e.dataTransfer.files[0];

                    // Sprawdź, czy to plik obrazu
                    if (file.type.match(/^image\/(jpeg|png|gif|svg\+xml)$/i)) {
                        logoInput.files = e.dataTransfer.files;
                        previewImage(file, dropZone);
                        return;
                    } else {
                        // Pokaż błąd dla plików niebędących obrazami
                        const errorMsg = document.createElement('div');
                        errorMsg.className = 'upload-error';
                        errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Błąd! Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG).</p>';

                        // Remove existing error messages
                        const existingErrors = dropZone.querySelectorAll('.upload-error');
                        existingErrors.forEach(err => err.remove());

                        dropZone.appendChild(errorMsg);

                        // Ukryj błąd po 5 sekundach
                        setTimeout(() => {
                            errorMsg.style.opacity = '0';
                            setTimeout(() => errorMsg.remove(), 500);
                        }, 5000);
                        return;
                    }
                }

                // Obsługa specyficzna dla Firefoksa, gdy bezpośredni dostęp do pliku zawiedzie
                if (isFirefox()) {
                    try {
                        // Dla Firefoksa sprawdź, czy są jakieś elementy z adresami URL
                        const items = e.dataTransfer.items;

                        // Logowanie debugowania, aby pomóc w diagnozowaniu problemów
                        console.log('Wykryto przeciąganie w Firefoksie. Elementy:', items ? items.length : 'brak');

                        if (items && items.length) {
                            let foundImage = false;

                            for (let i = 0; i < items.length; i++) {
                                console.log('Typ elementu:', items[i].type, 'rodzaj:', items[i].kind);

                                if (items[i].kind === 'string' && items[i].type.match('^text/uri-list')) {
                                    foundImage = true;
                                    items[i].getAsString(function (url) {
                                        // Check if URL ends with a common image extension
                                        if (url.match(/\.(jpeg|jpg|png|gif|svg)(\?.*)?$/i)) {
                                            // Display loading message
                                            const loadingMsg = document.createElement('div');
                                            loadingMsg.className = 'loading-message';
                                            loadingMsg.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Próba pobrania obrazu...</p>';

                                            // Remove existing messages
                                            const existingMsgs = dropZone.querySelectorAll('.loading-message');
                                            existingMsgs.forEach(msg => msg.remove());

                                            dropZone.appendChild(loadingMsg);

                                            // Try to fetch the image using a proxy or direct fetch
                                            try {
                                                // Create an Image element to check if loading works
                                                const img = new Image();
                                                img.crossOrigin = 'anonymous'; // Try to handle CORS

                                                img.onload = function () {
                                                    loadingMsg.remove();

                                                    // Create a canvas to convert the image to a blob
                                                    const canvas = document.createElement('canvas');
                                                    canvas.width = img.width;
                                                    canvas.height = img.height;
                                                    const ctx = canvas.getContext('2d');
                                                    ctx.drawImage(img, 0, 0);

                                                    // Convert to blob
                                                    canvas.toBlob(function (blob) {
                                                        // Create a File object from the Blob
                                                        const filename = url.split('/').pop().split('?')[0] || "image.png";
                                                        const file = new File([blob], filename, { type: "image/png" });

                                                        // Set the file to the input and show preview
                                                        try {
                                                            const dt = new DataTransfer();
                                                            dt.items.add(file);
                                                            logoInput.files = dt.files;
                                                            previewImage(file, dropZone);
                                                        } catch (e) {
                                                            console.error('Error creating DataTransfer:', e);
                                                            showFirefoxError(dropZone);
                                                        }
                                                    }, 'image/png');
                                                };

                                                img.onerror = function () {
                                                    console.log('Image failed to load from URL:', url);
                                                    loadingMsg.remove();
                                                    showFirefoxError(dropZone);
                                                };

                                                // Try to load the image
                                                img.src = url;
                                            } catch (error) {
                                                console.error('Error processing image URL:', error);
                                                loadingMsg.remove();
                                                showFirefoxError(dropZone);
                                            }
                                            return;
                                        } else {
                                            // Not an image URL
                                            const errorMsg = document.createElement('div');
                                            errorMsg.className = 'upload-error';
                                            errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Błąd! Dozwolone są tylko pliki obrazów (JPG, PNG, GIF, SVG).</p>';
                                            dropZone.appendChild(errorMsg);

                                            setTimeout(() => {
                                                errorMsg.style.opacity = '0';
                                                setTimeout(() => errorMsg.remove(), 500);
                                            }, 5000);
                                        }
                                    });
                                    return;
                                }
                            }

                            // If we found items but no valid image URLs
                            if (!foundImage) {
                                showFirefoxError(dropZone);
                            }
                        } else {
                            // No items found in dataTransfer
                            showFirefoxError(dropZone);
                        }
                    } catch (error) {
                        console.error('Firefox drag & drop error:', error);
                        showFirefoxError(dropZone);
                    }
                } else {
                    // For non-Firefox browsers with no files in dataTransfer
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'upload-error';
                    errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Nie można przeciągnąć tego obrazu. Spróbuj zapisać obraz na dysku, a następnie go przeciągnij lub użyj przycisku wyboru pliku.</p>';
                    dropZone.appendChild(errorMsg);

                    setTimeout(() => {
                        errorMsg.style.opacity = '0';
                        setTimeout(() => errorMsg.remove(), 500);
                    }, 5000);
                }
            });

            // Kliknięcie w drop zone otwiera okno wyboru pliku
            dropZone.addEventListener('click', function (event) {
                if (event.target === dropZone || event.target.className === 'upload-hint' || event.target.parentElement.className === 'upload-hint') {
                    logoInput.click();
                }
            });
        });

        // Wklejanie obrazów ze schowka (Ctrl+V) - działa globalnie dla wszystkich dropZones
        document.addEventListener('paste', function (e) {
            console.log('Paste detected, active element:', document.activeElement);

            // Znajdź aktywny dropZone (ten, który zawiera aktywny element lub jest najbliżej)
            let activeDropZone = null;

            // Sprawdź czy focus jest na polu logo lub obszarze formularza
            if (document.activeElement === logoInput ||
                document.activeElement.tagName === 'BODY') {
                // Użyj pierwszego dropZone, jeśli focus jest globalny
                activeDropZone = dropZones[0];
            } else {
                // Znajdź dropZone, który zawiera aktywny element
                dropZones.forEach(dropZone => {
                    if (dropZone.contains(document.activeElement)) {
                        activeDropZone = dropZone;
                    }
                });

                // Jeśli nadal nie znaleziono, użyj pierwszego
                if (!activeDropZone && dropZones.length > 0) {
                    activeDropZone = dropZones[0];
                }
            }

            // Jeśli mamy aktywny dropZone, obsłuż wklejanie
            if (activeDropZone) {
                const items = (e.clipboardData || e.originalEvent.clipboardData).items;
                console.log('Clipboard items:', items.length);

                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        console.log('Image found in clipboard');
                        const file = items[i].getAsFile();
                        console.log('Image file:', file.name, file.type, file.size);

                        // Utworzenie obiektu DataTransfer i dodanie pliku
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            logoInput.files = dt.files;

                            // Podgląd wklejonego obrazka
                            previewImage(file, activeDropZone);
                        } catch (error) {
                            console.error('Error handling pasted image:', error);
                            const errorMsg = document.createElement('div');
                            errorMsg.className = 'upload-error';
                            errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Wystąpił błąd podczas wklejania obrazu. Spróbuj zapisać obraz na dysku i przeciągnij go tutaj.</p>';
                            activeDropZone.appendChild(errorMsg);

                            setTimeout(() => {
                                errorMsg.style.opacity = '0';
                                setTimeout(() => errorMsg.remove(), 500);
                            }, 5000);
                        }
                        break;
                    }
                }
            }
        });

        // Funkcja podglądu obrazu
        function previewImage(file, dropZone) {
            console.log('Generating preview for:', file.name);
            const reader = new FileReader();
            reader.onload = function (e) {
                // Usuń istniejący podgląd, jeśli istnieje
                const existingPreview = dropZone.querySelector('.image-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }

                // Utwórz nowy podgląd
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                preview.style.marginTop = '10px';

                preview.innerHTML = `
                    <div class="preview-header">
                        <span class="filename">${file.name}</span>
                        <button type="button" class="remove-preview" title="Usuń wybrany obraz">&times;</button>
                    </div>
                    <img src="${e.target.result}" alt="Podgląd logo" style="max-width: 100%; max-height: 200px;">
                `;

                dropZone.appendChild(preview);

                // Ukryj wskazówkę uploadowania
                const uploadHint = dropZone.querySelector('.upload-hint');
                if (uploadHint) {
                    uploadHint.style.display = 'none';
                }

                // Obsługa usuwania podglądu i czyszczenia pola input
                const removeButton = preview.querySelector('.remove-preview');
                if (removeButton) {
                    removeButton.addEventListener('click', function (evt) {
                        evt.preventDefault();
                        evt.stopPropagation(); // Zapobiegaj uruchomieniu click na dropZone

                        // Usuń podgląd
                        preview.remove();

                        // Wyczyść input pliku
                        try {
                            logoInput.value = ''; // Standardowe czyszczenie

                            // Dodatkowe czyszczenie dla kompatybilności ze wszystkimi przeglądarkami
                            if (logoInput.value) {
                                // Dla IE i Edge
                                logoInput.type = '';
                                logoInput.type = 'file';
                            }

                            // Czyszczenie dla nowoczesnych przeglądarek
                            if ('files' in logoInput && logoInput.files.length > 0) {
                                try {
                                    const dt = new DataTransfer();
                                    logoInput.files = dt.files;
                                } catch (e) {
                                    console.log('Could not clear file input using DataTransfer:', e);
                                }
                            }
                        } catch (e) {
                            console.error('Error clearing file input:', e);
                        }

                        // Pokaż ponownie wskazówkę uploadowania
                        if (uploadHint) {
                            uploadHint.style.display = '';
                        }

                        console.log('Image removed successfully');
                    });
                }
            };
            reader.readAsDataURL(file);
        }

        // Obsługa standardowego inputa plików
        logoInput.addEventListener('change', function (e) {
            if (this.files && this.files[0]) {
                console.log('File selected via input:', this.files[0].name);

                // Znajdź najbliższy kontener dropZone dla tego inputa
                let parentDropZone = null;
                dropZones.forEach(dropZone => {
                    if (dropZone.contains(this)) {
                        parentDropZone = dropZone;
                    }
                });

                // Jeśli nie znaleziono, użyj pierwszego dostępnego
                if (!parentDropZone && dropZones.length > 0) {
                    parentDropZone = dropZones[0];
                }

                if (parentDropZone) {
                    previewImage(this.files[0], parentDropZone);
                }
            }
        });
    }

    // Funkcja walidująca URL
    function validateUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Funkcja przełączająca między jasnym i ciemnym motywem
    function toggleTheme() {
        document.body.classList.toggle("light-mode");

        // Zapisanie preferencji motywu w pamięci przeglądarki
        const isLightMode = document.body.classList.contains("light-mode");
        localStorage.setItem('theme', isLightMode ? 'light' : 'dark');

        // Dodanie klasy przejścia dla płynnej animacji zmiany motywu
        document.documentElement.classList.add('theme-transitioning');

        // Usunięcie klasy przejścia po zakończeniu animacji
        setTimeout(() => {
            document.documentElement.classList.remove('theme-transitioning');
        }, 300); // Czas musi być zgodny z czasem przejścia w CSS

        // Aktualizacja ikony przycisku zmiany motywu
        const themeToggleIcon = document.getElementById('theme-toggle-icon');
        if (themeToggleIcon) {
            themeToggleIcon.className = isLightMode ? 'fas fa-moon' : 'fas fa-sun';
            themeToggleIcon.title = isLightMode ? 'Przełącz na tryb ciemny' : 'Przełącz na tryb jasny';
        }
    }

    // Inicjalizacja przycisku zmiany motywu
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', toggleTheme);
    }

    // Wczytanie zapisanej preferencji motywu z pamięci lokalnej
    function loadThemePreference() {
        const theme = localStorage.getItem('theme');
        if (theme === 'light') {
            toggleTheme(); // Zastosowanie jasnego motywu, jeśli był zapisany
        }
    }

    // Wczytanie preferencji motywu przy ładowaniu strony
    loadThemePreference();

    // Funkcja wykonująca wyszukiwanie dystrybucji
    window.performSearch = function () {
        const searchInput = document.getElementById('search-input');
        if (!searchInput) return;

        // Sprawdzenie czy zapytanie ma wystarczającą długość
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length < 2) return;

        // Pokazanie animacji ładowania podczas wyszukiwania
        const resultsDiv = document.getElementById('results');
        if (!resultsDiv) return;
        resultsDiv.innerHTML = '<div class="loading">Szukam dystrybucji...</div>';

        // Aktualizacja adresu URL z parametrem wyszukiwania
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('q', searchTerm);
        window.history.pushState({}, '', newUrl);

        // Wykonanie zapytania AJAX do serwera
        fetch(`search.php?q=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                resultsDiv.innerHTML = '';

                // Obsługa przypadku, gdy nie znaleziono żadnych wyników
                if (data.length === 0) {
                    if (isUserLoggedIn) {
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
                        resultsDiv.innerHTML = `
                            <div class="no-results">
                                <p>Nie znaleziono dystrybucji "${searchTerm}".</p>
                                <p>Zaloguj się, aby dodać nową dystrybucję do naszej bazy danych.</p>
                                <a href="login.php" class="btn-primary"><i class="fas fa-sign-in-alt"></i> Zaloguj się</a>
                            </div>
                        `;
                    }
                } else {
                    // Generowanie HTML z wynikami wyszukiwania
                    let resultsHTML = '<h2 class="section-title">Wyniki wyszukiwania dla: "' + searchTerm + '"</h2>';
                    resultsHTML += '<div class="search-results">';
                    data.forEach(distro => {
                        const imagePath = distro.logo_path || 'img/default.png';
                        resultsHTML += `
                            <div class="distro-card">
                                <img src="${imagePath}" alt="${distro.name}" class="distro-logo">
                                <h3>${distro.name}</h3>
                                <p>${distro.description.substring(0, 150)}${distro.description.length > 150 ? '...' : ''}</p>
                                <div class="card-buttons">
                                    <a href="details.php?id=${distro.id}" class="btn-details">Szczegóły</a>
                                    <a href="edit.php?id=${distro.id}" class="btn-edit">Edytuj</a>
                                </div>
                            </div>
                        `;
                    });
                    resultsHTML += '</div>';
                    resultsDiv.innerHTML = resultsHTML;
                }
            })
            .catch(error => {
                // Obsługa błędów podczas zapytania AJAX
                resultsDiv.innerHTML = `<div class="error-message">Błąd wyszukiwania: ${error.message}</div>`;
            });
    }

    // Automatyczne wyszukiwanie, jeśli w URL znajduje się parametr 'q'
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

    // Wyszukiwanie dynamiczne podczas wpisywania z opóźnieniem
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

    // Pomocnicza funkcja do pobierania parametrów z adresu URL
    function getUrlParameter(name) {
        name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
        var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
        var results = regex.exec(location.search);
        return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
    }

    // Obsługa komunikatów o sukcesie i błędach z URL
    const status = getUrlParameter('status');
    const added = getUrlParameter('added');
    const message = getUrlParameter('message');

    // Wyświetlanie komunikatu o pomyślnym dodaniu dystrybucji
    if (status === 'success' && added) {
        const resultsDiv = document.getElementById('results');
        if (resultsDiv) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = `<p><strong>Sukces!</strong> Dystrybucja "${added}" została pomyślnie dodana do bazy danych!</p>`;

            resultsDiv.insertBefore(successDiv, resultsDiv.firstChild);

            // Automatyczne ukrywanie komunikatu po określonym czasie
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

            // Automatyczne ukrywanie komunikatu o błędzie po dłuższym czasie
            setTimeout(function () {
                errorDiv.style.opacity = '0';
                errorDiv.style.transition = 'opacity 1s';

                setTimeout(function () {
                    errorDiv.remove();
                }, 1000);
            }, 8000);
        }
    }

});