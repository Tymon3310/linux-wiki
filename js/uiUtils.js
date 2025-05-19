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
    // Licznik dla pola hasła logowania
    const loginPasswordInput = document.getElementById('password');
    const loginPasswordCounter = document.getElementById('password-counter');
    if (loginPasswordInput && loginPasswordCounter) {
        loginPasswordCounter.textContent = `${loginPasswordInput.value.length} znaków`;
        loginPasswordInput.addEventListener('input', () => {
            loginPasswordCounter.textContent = `${loginPasswordInput.value.length} znaków`;
        });
    }

    // Licznik dla pola hasła rejestracji
    const regPasswordInput = document.getElementById('reg-password');
    const regPasswordCounter = document.getElementById('reg-password-counter');
    if (regPasswordInput && regPasswordCounter) {
        regPasswordCounter.textContent = `${regPasswordInput.value.length} znaków`;
        regPasswordInput.addEventListener('input', () => {
            regPasswordCounter.textContent = `${regPasswordInput.value.length} znaków`;
        });
    }

    // Licznik dla pola potwierdzenia hasła rejestracji
    const confirmPasswordInput = document.getElementById('confirm-password');
    const confirmPasswordCounter = document.getElementById('confirm-password-counter');
    if (confirmPasswordInput && confirmPasswordCounter) {
        confirmPasswordCounter.textContent = `${confirmPasswordInput.value.length} znaków`;
        confirmPasswordInput.addEventListener('input', () => {
            confirmPasswordCounter.textContent = `${confirmPasswordInput.value.length} znaków`;
        });
    }
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

// Inicjalizuje modale potwierdzenia usunięcia (dystrybucji i komentarzy)
export function initializeDeleteModals() {
    // --- Modal usuwania dystrybucji (edit.php) ---
    const deleteButton = document.getElementById('delete-button');
    const deleteModal = document.getElementById('delete-modal');
    const cancelDeleteButton = document.getElementById('cancel-delete');
    const distroNameSpan = document.getElementById('distro-name-to-delete');
    const distroIdInput = document.getElementById('distro-id-to-delete');

    if (deleteButton && deleteModal && cancelDeleteButton && distroNameSpan && distroIdInput) {
        deleteButton.addEventListener('click', function () {
            const distroId = this.dataset.id;
            const distroName = this.dataset.name;

            distroNameSpan.textContent = distroName;
            distroIdInput.value = distroId;
            deleteModal.style.display = 'block';
        });

        cancelDeleteButton.addEventListener('click', function () {
            deleteModal.style.display = 'none';
        });

        // Zamknij modal, jeśli użytkownik kliknie poza nim
        window.addEventListener('click', function (event) {
            if (event.target === deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    }

    // --- Modal usuwania komentarzy (details.php) ---
    const deleteCommentButtons = document.querySelectorAll('.btn-delete-comment');
    const deleteCommentModal = document.getElementById('delete-comment-modal');
    const cancelDeleteCommentButton = document.getElementById('cancel-delete-comment');
    const commentUsernameSpan = document.getElementById('comment-username-to-delete');
    const commentIdInput = document.getElementById('comment-id-to-delete');

    if (deleteCommentModal && cancelDeleteCommentButton && commentUsernameSpan && commentIdInput) {
        deleteCommentButtons.forEach(button => {
            button.addEventListener('click', function () {
                const commentId = this.dataset.commentId;
                const username = this.dataset.username;

                commentUsernameSpan.textContent = username;
                commentIdInput.value = commentId;
                deleteCommentModal.style.display = 'block';
            });
        });

        cancelDeleteCommentButton.addEventListener('click', function () {
            deleteCommentModal.style.display = 'none';
        });

        // Zamknij modal, jeśli użytkownik kliknie poza nim
        window.addEventListener('click', function (event) {
            if (event.target === deleteCommentModal) {
                deleteCommentModal.style.display = 'none';
            }
        });
    }
}
