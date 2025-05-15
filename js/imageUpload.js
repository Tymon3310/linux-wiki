// Plik obsługujący przesyłanie i podgląd obrazków (logo dystrybucji)

import { isFirefox } from './utils.js';

// Ta funkcja pokaże specjalny komunikat, jeśli Firefox ma problem
// z przeciąganiem obrazka bezpośrednio ze strony internetowej.
function showFirefoxError(dropZone) {
    const errorMsg = document.createElement('div');
    errorMsg.className = 'upload-error';
    errorMsg.innerHTML = '<p><i class="fas fa-exclamation-triangle"></i> Nie można przeciągnąć tego obrazu. W przeglądarce Firefox zapisz obraz na dysku (prawy przycisk myszy -> Zapisz obraz jako...), a następnie go przeciągnij lub użyj przycisku wyboru pliku.</p>';

    // Najpierw usuń stare komunikaty o błędach, żeby nie było bałaganu
    const existingErrors = dropZone.querySelectorAll('.upload-error');
    existingErrors.forEach(err => err.remove());

    // Dodaj nowy komunikat
    dropZone.appendChild(errorMsg);

    // Schowaj komunikat po 7 sekundach, żeby nie przeszkadzał za długo
    setTimeout(() => {
        errorMsg.style.opacity = '0';
        setTimeout(() => errorMsg.remove(), 500);
    }, 7000);
}

// Funkcja do pokazywania niestandardowego komunikatu o błędzie uploadu w obrębie dropZone
function showCustomUploadAlert(dropZone, message) {
    // Najpierw usuń stare komunikaty o błędach, żeby nie było bałaganu
    const existingErrors = dropZone.querySelectorAll('.upload-error');
    existingErrors.forEach(err => err.remove());

    const errorMsg = document.createElement('div');
    errorMsg.className = 'upload-error'; // Użyj tej samej klasy co showFirefoxError dla spójności stylów
    errorMsg.innerHTML = `<p><i class="fas fa-exclamation-triangle"></i> ${message}</p>`;

    // Dodaj nowy komunikat
    dropZone.appendChild(errorMsg);

    // Opcjonalnie: schowaj komunikat po pewnym czasie
    setTimeout(() => {
        if (errorMsg && errorMsg.parentElement) { // Sprawdź, czy element wciąż istnieje w DOM
            errorMsg.style.opacity = '0';
            setTimeout(() => {
                if (errorMsg && errorMsg.parentElement) errorMsg.remove();
            }, 500);
        }
    }, 7000); // Taki sam czas jak w showFirefoxError
}

// Funkcja generująca podgląd wybranego obrazka
function previewImage(file, dropZone, logoInput) {
    console.log('Generuję podgląd dla:', file.name);
    const reader = new FileReader();
    reader.onload = function (e) {
        // Usuń istniejący podgląd, jeśli już jakiś jest
        const existingPreview = dropZone.querySelector('.image-preview');
        if (existingPreview) {
            existingPreview.remove();
        }

        // Utwórz nowy element dla podglądu
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.style.marginTop = '10px';

        // Wypełnij podgląd nazwą pliku, obrazkiem i przyciskiem do usunięcia
        preview.innerHTML = `
            <div class="preview-header">
                <span class="filename">${file.name}</span>
                <button type="button" class="remove-preview" title="Usuń wybrany obraz">&times;</button>
            </div>
            <img src="${e.target.result}" alt="Podgląd logo" style="max-width: 100%; max-height: 200px;">
        `;

        // Dodaj podgląd do strefy upuszczania
        dropZone.appendChild(preview);

        // Ukryj tekst zachęcający do przeciągnięcia pliku
        const uploadHint = dropZone.querySelector('.upload-hint');
        if (uploadHint) {
            uploadHint.style.display = 'none';
        }

        // Dodaj obsługę kliknięcia przycisku "usuń" w podglądzie
        const removeButton = preview.querySelector('.remove-preview');
        if (removeButton) {
            removeButton.addEventListener('click', function (evt) {
                evt.preventDefault(); // Zatrzymaj domyślną akcję
                evt.stopPropagation(); // Zatrzymaj propagację, żeby nie kliknąć na dropZone

                // Usuń element podglądu z DOM
                preview.remove();

                // Wyczyść pole input typu 'file', żeby formularz nie wysłał starego pliku
                try {
                    logoInput.value = ''; // Standardowe czyszczenie

                    // Dodatkowe sztuczki dla starszych przeglądarek (IE, Edge)
                    if (logoInput.value) {
                        // Dla IE i Edge
                        logoInput.type = '';
                        logoInput.type = 'file';
                    }

                    // Czyszczenie dla nowoczesnych przeglądarek przy użyciu DataTransfer
                    if ('files' in logoInput && logoInput.files.length > 0) {
                        try {
                            const dt = new DataTransfer();
                            logoInput.files = dt.files;
                        } catch (e) {
                            console.log('Could not clear file input using DataTransfer:', e);
                        }
                    }
                } catch (e) {
                    console.error('Błąd podczas czyszczenia pola input pliku:', e);
                }

                // Pokaż ponownie tekst zachęcający do przeciągnięcia pliku
                if (uploadHint) {
                    uploadHint.style.display = '';
                }

                // Usuń klasę błędu z rodzica inputa, jeśli była dodana
                if (logoInput.parentElement) {
                    logoInput.parentElement.classList.remove('error-field');
                }

                console.log('Obrazek usunięty pomyślnie');
            });
        }
    };
    // Rozpocznij wczytywanie pliku jako Data URL (potrzebne do wyświetlenia w <img>)
    reader.readAsDataURL(file);
}

// Funkcja obsługująca zdarzenie upuszczenia pliku na strefę
function handleDrop(event, dropZone, logoInput) {
    event.preventDefault(); // Zapobiegaj domyślnej akcji przeglądarki (otwieranie pliku)
    event.stopPropagation();
    dropZone.classList.remove('dragover'); // Usuń wizualne podświetlenie
    console.log('Plik upuszczony!');

    const files = event.dataTransfer.files;
    const items = event.dataTransfer.items;
    const types = event.dataTransfer.types;
    const uploadHint = dropZone.querySelector('.upload-hint'); // Pobierz referencję do uploadHint

    // Sprawdź, czy upuszczono pliki (np. z pulpitu)
    if (files && files.length > 0) {
        console.log('Upuszczono pliki:', files);
        // Weź tylko pierwszy plik, jeśli upuszczono ich więcej
        const file = files[0];
        // Sprawdź, czy to obrazek
        if (file.type.startsWith('image/')) {
            // Ustaw plik w inpucie
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            logoInput.files = dataTransfer.files;
            // Wygeneruj podgląd
            previewImage(file, dropZone, logoInput);
            // Usuń ewentualny komunikat o błędzie Firefoxa lub niestandardowy alert
            const existingError = dropZone.querySelector('.upload-error');
            if (existingError) existingError.remove();
            // Ukryj wskazówkę po upuszczeniu pliku
            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
        } else {
            showCustomUploadAlert(dropZone, 'Proszę upuścić plik obrazka (np. PNG, JPG, GIF).');
        }
    }
    // --- NOWA OBSŁUGA: Przeciąganie obrazka z tej samej strony w Firefox ---
    else if (isFirefox() && types.includes('text/uri-list') && types.includes('application/x-moz-nativeimage')) {
        console.log('[Firefox Intra-Page Drag] Wykryto przeciągnięcie obrazka z tej samej strony.');
        const imageUrl = event.dataTransfer.getData('text/uri-list');
        console.log('[Firefox Intra-Page Drag] URL obrazka:', imageUrl);

        if (imageUrl) {
            // Spróbuj pobrać obrazek z URL
            fetch(imageUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.blob(); // Pobierz jako Blob
                })
                .then(blob => {
                    console.log('[Firefox Intra-Page Drag] Pobrany Blob:', blob);
                    // Utwórz obiekt File z Bloba
                    // Spróbuj wyciągnąć nazwę pliku z URL
                    let filename = imageUrl.substring(imageUrl.lastIndexOf('/') + 1);
                    // Usuń ewentualne parametry URL z nazwy pliku
                    filename = filename.split('?')[0];
                    // Jeśli nazwa jest pusta lub domyślna, nadaj generyczną
                    if (!filename || filename.includes('.php') || filename.includes('.html')) {
                        const extension = blob.type.split('/')[1] || 'png'; // Domyślnie png
                        filename = `dragged-image.${extension}`;
                    }

                    const file = new File([blob], filename, { type: blob.type });
                    console.log('[Firefox Intra-Page Drag] Utworzony plik:', file);

                    // Ustaw plik w inpucie
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    logoInput.files = dataTransfer.files;

                    // Wygeneruj podgląd
                    previewImage(file, dropZone, logoInput);

                    // Usuń ewentualny komunikat o błędzie Firefoxa lub niestandardowy alert
                    const existingError = dropZone.querySelector('.upload-error');
                    if (existingError) existingError.remove();
                    // Ukryj wskazówkę po przetworzeniu
                    if (uploadHint) {
                        uploadHint.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('[Firefox Intra-Page Drag] Błąd podczas pobierania lub przetwarzania obrazka z URL:', error);
                    showCustomUploadAlert(dropZone, 'Nie udało się pobrać przeciągniętego obrazka. Spróbuj zapisać go najpierw na dysku.');
                });
        } else {
            console.warn('[Firefox Intra-Page Drag] Nie udało się uzyskać URL obrazka z dataTransfer.');
            showCustomUploadAlert(dropZone, 'Nie udało się przetworzyć przeciągniętego obrazka.');
        }
    }
    // --- Koniec nowej obsługi ---
    else if (items && items.length > 0 && items[0].kind === 'string' && items[0].type === 'text/uri-list') {
        // Obsługa przeciągania obrazka bezpośrednio ze strony ZEWNĘTRZNEJ (często problematyczne w Firefox)
        console.log('Próba przeciągnięcia obrazka z innej strony (URI)');
        if (isFirefox()) {
            // W Firefoksie to często nie działa poprawnie, pokażmy błąd
            console.warn('Firefox może mieć problem z przeciąganiem obrazków z URI.');
            showFirefoxError(dropZone); // Dedykowany komunikat dla FF
        } else {
            // W innych przeglądarkach spróbujmy pobrać URL
            items[0].getAsString(function (url) {
                console.log('Przeciągnięty URL obrazka:', url);
                showCustomUploadAlert(dropZone, 'Przeciąganie obrazków bezpośrednio ze stron internetowych może nie działać. Spróbuj zapisać obraz na dysku i wtedy go przeciągnąć lub wybrać.');
            });
        }
    } else if (items && items.length > 0 && items[0].kind === 'string' && items[0].type.startsWith('image/')) {
        // Obsługa wklejania obrazka LUB przeciągania danych obrazka (rzadsze)
        console.log('Próba przetworzenia elementu typu image/* (może być wklejenie lub nietypowy drag).');
        const file = items[0].getAsFile();
        if (file) {
            console.log('Wklejony plik:', file);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            logoInput.files = dataTransfer.files;
            previewImage(file, dropZone, logoInput);
            // Usuń ewentualny komunikat o błędzie Firefoxa lub niestandardowy alert
            const existingError = dropZone.querySelector('.upload-error');
            if (existingError) existingError.remove();
            // Ukryj wskazówkę po wklejeniu
            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
        } else {
            console.warn('Nie udało się uzyskać pliku z wklejonych danych obrazka.');
            showCustomUploadAlert(dropZone, 'Nie udało się przetworzyć wklejonego obrazka.');
        }
    } else {
        console.log('Upuszczono coś innego niż plik lub obsługiwany typ danych.');
        showCustomUploadAlert(dropZone, 'Proszę upuścić plik obrazka.');
    }
}

// Funkcja obsługująca zdarzenie wklejenia (Ctrl+V / Cmd+V)
function handlePaste(event, dropZone, logoInput) {
    console.log('Wykryto wklejenie!');
    const items = (event.clipboardData || window.clipboardData).items;
    let foundImage = false;

    for (let i = 0; i < items.length; i++) {
        // Szukamy elementu, który jest obrazkiem
        if (items[i].type.indexOf('image') !== -1) {
            const file = items[i].getAsFile();
            if (file) {
                console.log('Znaleziono obrazek w schowku:', file);
                // Ustaw plik w inpucie
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                logoInput.files = dataTransfer.files;
                // Wygeneruj podgląd
                previewImage(file, dropZone, logoInput);
                // Usuń ewentualny komunikat o błędzie Firefoxa
                const existingError = dropZone.querySelector('.upload-error');
                if (existingError) existingError.remove();
                // Ukryj wskazówkę po wklejeniu
                const uploadHint = dropZone.querySelector('.upload-hint');
                if (uploadHint) {
                    uploadHint.style.display = 'none';
                }
                foundImage = true;
                break; // Wystarczy nam pierwszy znaleziony obrazek
            } else {
                console.warn('item.getAsFile() zwrócił null dla wklejonego elementu typu image/*');
                showCustomUploadAlert(dropZone, 'Nie udało się odczytać wklejonego obrazka.');
            }
        }
    }

    if (!foundImage) {
        console.log('Nie znaleziono obrazka w schowku.');
    }
}

// Główna funkcja inicjalizująca obsługę przesyłania obrazków
export function initializeImageUpload() {
    const dropZones = document.querySelectorAll('.file-upload-container');

    dropZones.forEach(dropZone => {
        const logoInput = dropZone.querySelector('input[type="file"]');
        const fileSelectButton = dropZone.querySelector('.file-select-button');
        let uploadHint = dropZone.querySelector('.upload-hint'); // Znajdź istniejącą wskazówkę

        if (!logoInput) {
            console.error('Nie znaleziono inputa pliku w kontenerze:', dropZone);
            return; // Przejdź do następnej strefy, jeśli brakuje inputa
        }

        // --- Dynamiczne tworzenie i wstawianie wskazówki ---
        // Sprawdź, czy wskazówka już istnieje i czy jest pusta, lub czy nie istnieje wcale
        if (!uploadHint) {
            uploadHint = document.createElement('div');
            uploadHint.className = 'upload-hint';
            // Wstaw wskazówkę przed inputem pliku (lub innym pierwszym elementem, jeśli input jest ukryty)
            const firstChild = dropZone.firstElementChild;
            if (firstChild) {
                dropZone.insertBefore(uploadHint, firstChild);
            } else {
                dropZone.appendChild(uploadHint); // Fallback, jeśli kontener jest pusty
            }
        }

        // Ustaw tekst wskazówki, uwzględniając Firefox
        let hintText = `
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Przeciągnij i upuść obrazek tutaj, wklej ze schowka (Ctrl+V), lub</span>
        `;
        if (isFirefox()) {
            hintText += ` <p class="firefox-warning"><i class="fas fa-exclamation-triangle"></i> Uwaga: Firefox może nie obsługiwać przeciągania obrazów z innych stron. Zalecamy zapisać obraz na dysk i przeciągnąć go stąd.</p>`;
        }
        uploadHint.innerHTML = hintText;
        // Upewnij się, że wskazówka jest widoczna na początku
        uploadHint.style.display = '';

        // --- Obsługa przycisku "Wybierz plik" ---
        if (fileSelectButton) {
            fileSelectButton.addEventListener('click', (e) => {
                e.preventDefault(); // Zapobiegaj domyślnej akcji (jeśli to np. <button type="submit">)
                logoInput.click(); // Otwórz okno dialogowe wyboru pliku
            });
        }

        // --- Obsługa zmiany pliku w inpucie (po wybraniu przez okno dialogowe) ---
        logoInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const selectedFile = this.files[0];
                console.log('Plik wybrany przez okno dialogowe:', selectedFile);

                // Sprawdź typ pliku
                if (selectedFile.type.startsWith('image/')) {
                    previewImage(selectedFile, dropZone, logoInput);
                    // Usuń ewentualny komunikat o błędzie Firefoxa lub niestandardowy alert
                    const existingError = dropZone.querySelector('.upload-error');
                    if (existingError) existingError.remove();
                    // Ukryj wskazówkę po wybraniu pliku
                    if (uploadHint) {
                        uploadHint.style.display = 'none';
                    }
                } else {
                    showCustomUploadAlert(dropZone, 'Wybrany plik nie jest obrazkiem. Proszę wybrać plik graficzny (np. PNG, JPG, GIF).');
                    // Wyczyść input pliku, aby nie próbować wysłać nieprawidłowego pliku
                    this.value = ''; // Standardowe czyszczenie
                    // Dodatkowe sztuczki dla starszych przeglądarek (IE, Edge)
                    if (this.value) {
                        this.type = '';
                        this.type = 'file';
                    }
                    // Pokaż ponownie wskazówkę, jeśli była ukryta
                    if (uploadHint) {
                        uploadHint.style.display = '';
                    }
                    // Usuń istniejący podgląd, jeśli jakiś jest (np. po błędnym przeciągnięciu)
                    const existingPreview = dropZone.querySelector('.image-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                }
            }
        });

        // --- Obsługa przeciągania i upuszczania (Drag & Drop) ---
        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault(); // Niezbędne, aby umożliwić 'drop'
            event.stopPropagation();
            dropZone.classList.add('dragover'); // Dodaj wizualne podświetlenie
        });

        dropZone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            event.stopPropagation();
            if (!dropZone.contains(event.relatedTarget)) {
                dropZone.classList.remove('dragover');
            }
        });

        dropZone.addEventListener('drop', (event) => handleDrop(event, dropZone, logoInput));

        // --- Obsługa wklejania (Paste) ---
        dropZone.addEventListener('paste', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Wykryto wklejenie');
            dropZone.classList.remove('dragover');

            const items = (e.clipboardData || window.clipboardData).items;
            let foundImage = false;
            const currentUploadHint = dropZone.querySelector('.upload-hint');

            if (items) {
                for (const item of items) {
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        if (file) {
                            console.log('Wklejony plik obrazka:', file);

                            try {
                                const dataTransfer = new DataTransfer();
                                dataTransfer.items.add(file);
                                logoInput.files = dataTransfer.files;
                                console.log('Plik ustawiony w inpucie po wklejeniu.');
                            } catch (err) {
                                console.error('Błąd podczas ustawiania pliku w inpucie po wklejeniu:', err);
                                showCustomUploadAlert(dropZone, 'Nie udało się ustawić wklejonego obrazka.');
                                continue;
                            }

                            previewImage(file, dropZone, logoInput);
                            const existingError = dropZone.querySelector('.upload-error');
                            if (existingError) existingError.remove();
                            if (currentUploadHint) {
                                currentUploadHint.style.display = 'none';
                            }
                            foundImage = true;
                            break;
                        } else {
                            console.warn('item.getAsFile() zwrócił null dla wklejonego elementu typu image/*');
                            showCustomUploadAlert(dropZone, 'Nie udało się odczytać wklejonego obrazka.');
                        }
                    }
                }
            }

            if (!foundImage) {
                console.log('Nie znaleziono obrazka w schowku.');
            }
        });

        // --- Inicjalizacja: Sprawdź, czy jest już istniejące logo (np. przy edycji) ---
        const existingLogoUrl = dropZone.dataset.existingLogo;
        const existingLogoName = dropZone.dataset.existingLogoName || 'Istniejące logo';
        if (existingLogoUrl) {
            console.log('Znaleziono istniejące logo:', existingLogoUrl);
            const pseudoFile = { name: existingLogoName };

            const existingPreview = dropZone.querySelector('.image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }

            const preview = document.createElement('div');
            preview.className = 'image-preview existing-preview';
            preview.style.marginTop = '10px';

            preview.innerHTML = `
                <div class="preview-header">
                    <span class="filename">${pseudoFile.name}</span>
                    <span class="existing-info">(aktualne)</span>
                </div>
                <img src="${existingLogoUrl}" alt="Podgląd istniejącego logo" style="max-width: 100%; max-height: 200px;">
                <p class="change-hint">Aby zmienić, wybierz lub przeciągnij nowy plik.</p>
            `;

            dropZone.appendChild(preview);

            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
        } else {
            if (uploadHint) {
                uploadHint.style.display = '';
            }
        }
    });
}

// Dodaj obsługę usuwania podglądu, która pokaże wskazówkę z powrotem
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-preview')) {
        const preview = event.target.closest('.image-preview');
        const dropZone = event.target.closest('.file-upload-container');
        if (preview && dropZone) {
            const logoInput = dropZone.querySelector('input[type="file"]');
            const uploadHint = dropZone.querySelector('.upload-hint');

            preview.remove();

            try {
                logoInput.value = '';
                if (logoInput.value) {
                    logoInput.type = '';
                    logoInput.type = 'file';
                }
                if ('files' in logoInput && logoInput.files.length > 0) {
                    try {
                        const dt = new DataTransfer();
                        logoInput.files = dt.files;
                    } catch (e) { console.log('Could not clear file input using DataTransfer:', e); }
                }
            } catch (e) { console.error('Błąd podczas czyszczenia pola input pliku:', e); }

            if (uploadHint) {
                uploadHint.style.display = '';
            }

            if (logoInput && logoInput.parentElement) {
                logoInput.parentElement.classList.remove('error-field');
            }

            console.log('Podgląd obrazka usunięty, pokazano wskazówkę.');
        }
    }
});
