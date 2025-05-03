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

    // Sprawdź, czy upuszczono pliki
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
            // Usuń ewentualny komunikat o błędzie Firefoxa
            const existingError = dropZone.querySelector('.upload-error');
            if (existingError) existingError.remove();
            // Ukryj wskazówkę po upuszczeniu pliku
            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
        } else {
            alert('Proszę upuścić plik obrazka (np. PNG, JPG, GIF).');
        }
    } else if (items && items.length > 0 && items[0].kind === 'string' && items[0].type === 'text/uri-list') {
        // Obsługa przeciągania obrazka bezpośrednio ze strony (często problematyczne w Firefox)
        console.log('Próba przeciągnięcia obrazka z innej strony (URI)');
        if (isFirefox()) {
            // W Firefoksie to często nie działa poprawnie, pokażmy błąd
            console.warn('Firefox może mieć problem z przeciąganiem obrazków z URI.');
            showFirefoxError(dropZone);
        } else {
            // W innych przeglądarkach spróbujmy pobrać URL
            items[0].getAsString(function (url) {
                console.log('Przeciągnięty URL obrazka:', url);
                // Tutaj można by spróbować pobrać obrazek z URL, ale to bardziej skomplikowane
                // Na razie pokażmy błąd, bo nie mamy pewności, czy to zadziała
                alert('Przeciąganie obrazków bezpośrednio ze stron internetowych może nie działać. Spróbuj zapisać obraz na dysku i wtedy go przeciągnąć lub wybrać.');
            });
        }
    } else if (items && items.length > 0 && items[0].kind === 'string' && items[0].type.startsWith('image/')) {
        // Obsługa wklejania obrazka (np. ze schowka)
        console.log('Próba wklejenia obrazka ze schowka.');
        const file = items[0].getAsFile();
        if (file) {
            console.log('Wklejony plik:', file);
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            logoInput.files = dataTransfer.files;
            previewImage(file, dropZone, logoInput);
            const existingError = dropZone.querySelector('.upload-error');
            if (existingError) existingError.remove();
            // Ukryj wskazówkę po wklejeniu
            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
            foundImage = true;
            // break; // USUNIĘTO: Nieprawidłowe użycie break poza pętlą
        } else {
            console.warn('Nie udało się uzyskać pliku z wklejonych danych obrazka.');
            alert('Nie udało się przetworzyć wklejonego obrazka.');
        }
    } else {
        console.log('Upuszczono coś innego niż plik lub obsługiwany typ danych.');
        alert('Proszę upuścić plik obrazka.');
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
                if (uploadHint) {
                    uploadHint.style.display = 'none';
                }
                foundImage = true;
                break; // Wystarczy nam pierwszy znaleziony obrazek
            }
        }
    }

    if (!foundImage) {
        console.log('Nie znaleziono obrazka w schowku.');
        // Można by tu dodać komunikat dla użytkownika, ale może to być irytujące,
        // jeśli wklejał coś innego celowo.
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
                console.log('Plik wybrany przez okno dialogowe:', this.files[0]);
                previewImage(this.files[0], dropZone, logoInput);
                // Usuń ewentualny komunikat o błędzie Firefoxa
                const existingError = dropZone.querySelector('.upload-error');
                if (existingError) existingError.remove();
                // Ukryj wskazówkę po wybraniu pliku
                if (uploadHint) {
                    uploadHint.style.display = 'none';
                }
            }
        });

        // --- Obsługa przeciągania i upuszczania (Drag & Drop) ---

        // Kiedy coś jest przeciągane NAD strefę
        dropZone.addEventListener('dragover', (event) => {
            event.preventDefault(); // Niezbędne, aby umożliwić 'drop'
            event.stopPropagation();
            dropZone.classList.add('dragover'); // Dodaj wizualne podświetlenie
        });

        // Kiedy coś opuszcza strefę przeciągania (bez upuszczenia)
        dropZone.addEventListener('dragleave', (event) => {
            event.preventDefault();
            event.stopPropagation();
            // Sprawdź, czy kursor faktycznie opuścił strefę, a nie wszedł na jej element potomny
            if (!dropZone.contains(event.relatedTarget)) {
                dropZone.classList.remove('dragover');
            }
        });

        // Kiedy coś zostaje upuszczone NA strefę
        dropZone.addEventListener('drop', (event) => handleDrop(event, dropZone, logoInput));

        // --- Obsługa wklejania (Paste) ---
        dropZone.addEventListener('paste', function (e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Wykryto wklejenie');
            dropZone.classList.remove('drag-over');

            const items = (e.clipboardData || window.clipboardData).items;
            let foundImage = false;

            if (items) {
                // Użyj pętli for...of zamiast forEach, aby móc użyć break
                for (const item of items) {
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        console.log('Wklejony plik obrazka:', file);

                        // Ustaw plik w inpucie
                        try {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            logoInput.files = dataTransfer.files;
                            console.log('Plik ustawiony w inpucie po wklejeniu.');
                        } catch (err) {
                            console.error('Błąd podczas ustawiania pliku w inpucie po wklejeniu:', err);
                            // Można dodać komunikat dla użytkownika
                            showUploadError(dropZone, 'Nie udało się przetworzyć wklejonego obrazka.');
                            continue; // Przejdź do następnego elementu, jeśli wystąpił błąd
                        }

                        // Wygeneruj podgląd
                        previewImage(file, dropZone, logoInput);
                        // Usuń ewentualny komunikat o błędzie Firefoxa
                        const existingError = dropZone.querySelector('.upload-error');
                        if (existingError) existingError.remove();
                        // Ukryj wskazówkę po wklejeniu
                        if (uploadHint) {
                            uploadHint.style.display = 'none';
                        }
                        foundImage = true;
                        break; // Znaleziono i przetworzono obrazek, przerwij pętlę
                    }
                }
            }

            if (!foundImage) {
                console.log('Nie znaleziono obrazka w schowku.');
                // Opcjonalnie: Pokaż komunikat użytkownikowi
                // showUploadError(dropZone, 'Nie znaleziono obrazka w schowku.');
            }
        });

        // --- Inicjalizacja: Sprawdź, czy jest już istniejące logo (np. przy edycji) ---
        const existingLogoUrl = dropZone.dataset.existingLogo;
        const existingLogoName = dropZone.dataset.existingLogoName || 'Istniejące logo'; // Domyślna nazwa
        if (existingLogoUrl) {
            console.log('Znaleziono istniejące logo:', existingLogoUrl);
            // Utwórz "pseudo-plik" dla podglądu istniejącego logo
            // To nie jest prawdziwy plik, tylko obiekt z potrzebnymi danymi dla previewImage
            const pseudoFile = { name: existingLogoName }; // Potrzebujemy tylko nazwy dla podglądu

            // Usuń istniejący podgląd, jeśli istnieje
            const existingPreview = dropZone.querySelector('.image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }

            // Utwórz nowy podgląd dla istniejącego logo
            const preview = document.createElement('div');
            preview.className = 'image-preview existing-preview'; // Dodaj klasę dla odróżnienia
            preview.style.marginTop = '10px';

            preview.innerHTML = `
                < div class="preview-header" >
                    <span class="filename">${pseudoFile.name}</span>
                    <span class="existing-info">(aktualne)</span>
                    </div >
                <img src="${existingLogoUrl}" alt="Podgląd istniejącego logo" style="max-width: 100%; max-height: 200px;">
                    <p class="change-hint">Aby zmienić, wybierz lub przeciągnij nowy plik.</p>
                    `;

            dropZone.appendChild(preview);

            // Ukryj wskazówkę uploadowania, jeśli jest istniejące logo
            // const uploadHint = dropZone.querySelector('.upload-hint'); // Już zdefiniowane wyżej
            if (uploadHint) {
                uploadHint.style.display = 'none';
            }
        } else {
            // Jeśli nie ma istniejącego logo, upewnij się, że wskazówka jest widoczna
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

            // Usunięcie podglądu (kod przeniesiony z previewImage dla spójności)
            preview.remove();

            // Wyczyść pole input typu 'file'
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


            // Pokaż ponownie tekst zachęcający do przeciągnięcia pliku
            if (uploadHint) {
                uploadHint.style.display = '';
            }

            // Usuń klasę błędu z rodzica inputa, jeśli była dodana
            if (logoInput && logoInput.parentElement) {
                logoInput.parentElement.classList.remove('error-field');
            }

            console.log('Podgląd obrazka usunięty, pokazano wskazówkę.');
        }
    }
});
