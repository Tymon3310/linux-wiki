// Plik z funkcjami pomocniczymi dotyczącymi interfejsu użytkownika (UI)

import { getUrlParameter } from './utils.js';

// Inicjalizuje przycisk "Dodaj nową dystrybucję" (lub podobny)
// Pokazuje formularz dodawania dla zalogowanych użytkowników,
// lub prośbę o zalogowanie / przekierowanie do logowania dla niezalogowanych.
export function initializeShowAddFormButton(isUserLoggedIn) {
    const showAddFormButtons = document.querySelectorAll('#show-add-form'); // Może być więcej niż jeden taki przycisk
    const addFormContainer = document.getElementById('add-form-container'); // Kontener z formularzem dodawania
    const loginPrompt = document.getElementById('login-prompt'); // Komunikat "zaloguj się, aby dodać"

    showAddFormButtons.forEach(button => {
        button.addEventListener('click', function () {
            if (isUserLoggedIn && addFormContainer) {
                // Użytkownik zalogowany i formularz istnieje? Pokaż go i przewiń
                addFormContainer.style.display = 'block';
                addFormContainer.scrollIntoView({ behavior: 'smooth' });

                // Ustaw fokus na pierwszym polu (nazwa), żeby było wygodniej
                const nameInput = document.getElementById('name');
                if (nameInput) nameInput.focus();
            } else if (!isUserLoggedIn && loginPrompt) {
                // Użytkownik niezalogowany, ale jest specjalny komunikat? Pokaż go
                loginPrompt.style.display = 'block';
                loginPrompt.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Użytkownik niezalogowany i nie ma komunikatu? Przekieruj na stronę logowania
                window.location.href = 'login.php';
            }
        });
    });
}

// Inicjalizuje liczniki znaków dla pól tekstowych (textarea)
export function initializeCharacterCounters() {
    // Licznik dla pola opisu dystrybucji
    const descriptionTextarea = document.getElementById('description');
    const descriptionCounter = document.getElementById('description-counter');
    if (descriptionTextarea && descriptionCounter) {
        // Ustaw początkową wartość licznika
        descriptionCounter.textContent = `${descriptionTextarea.value.length} znaków`;
        // Aktualizuj licznik przy każdym wpisaniu znaku
        descriptionTextarea.addEventListener('input', () => {
            descriptionCounter.textContent = `${descriptionTextarea.value.length} znaków`;
        });
    }

    // Licznik dla pola komentarza
    const commentTextarea = document.getElementById('comment');
    const commentCounter = document.getElementById('comment-counter');
    if (commentTextarea && commentCounter) {
        // Ustaw początkową wartość licznika
        commentCounter.textContent = `${commentTextarea.value.length} znaków`;
        // Aktualizuj licznik przy każdym wpisaniu znaku
        commentTextarea.addEventListener('input', () => {
            commentCounter.textContent = `${commentTextarea.value.length} znaków`;
        });
    }
}

// Wyświetla komunikaty o statusie (np. po dodaniu dystrybucji)
// Odczytuje parametry 'status' i 'added' (lub 'message') z adresu URL
export function displayStatusMessages() {
    const status = getUrlParameter('status');
    const added = getUrlParameter('added'); // Nazwa dodanej dystrybucji
    const message = getUrlParameter('message'); // Ogólny komunikat (np. błędu)

    // Znajdź miejsce na wyświetlenie komunikatu (najlepiej div#results, awaryjnie body)
    const resultsDiv = document.getElementById('results') || document.body;

    // Komunikat o sukcesie (np. ?status=success&added=NazwaDystrybucji)
    if (status === 'success' && added) {
        if (resultsDiv) {
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message'; // Nadaj odpowiednią klasę CSS
            // Użyj `textContent` dla bezpieczeństwa, chociaż `added` powinno być już oczyszczone przez PHP
            const p = document.createElement('p');
            p.innerHTML = `<strong>Sukces!</strong> Dystrybucja "${added}" została pomyślnie dodana! <i class="fas fa-check-circle"></i>`;
            successDiv.appendChild(p);

            // Wstaw komunikat na górze diva z wynikami (lub body)
            resultsDiv.insertBefore(successDiv, resultsDiv.firstChild);

            // Automatycznie ukryj komunikat po 5 sekundach z ładnym zanikaniem
            setTimeout(function () {
                successDiv.style.opacity = '0';
                successDiv.style.transition = 'opacity 1s ease-out'; // Dodaj płynne przejście

                // Poczekaj na zakończenie animacji zanikania i dopiero usuń element z DOM
                setTimeout(function () {
                    successDiv.remove();
                }, 1000); // Czas musi być równy lub dłuższy niż transition duration
            }, 5000);
        }
    }

    // Komunikat o błędzie (np. ?status=error&message=TekstBledu)
    if (status === 'error' && message) {
        if (resultsDiv) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message'; // Nadaj odpowiednią klasę CSS
            const p = document.createElement('p');
            // Użyj textContent dla bezpieczeństwa, bo message może pochodzić z różnych źródeł
            p.innerHTML = `<strong>Błąd!</strong> ${message} <i class="fas fa-exclamation-triangle"></i>`;
            errorDiv.appendChild(p);

            resultsDiv.insertBefore(errorDiv, resultsDiv.firstChild);

            // Błędy zostawiamy widoczne dłużej, np. 10 sekund
            setTimeout(function () {
                errorDiv.style.opacity = '0';
                errorDiv.style.transition = 'opacity 1s ease-out';
                setTimeout(function () {
                    errorDiv.remove();
                }, 1000);
            }, 10000);
        }
    }
}
