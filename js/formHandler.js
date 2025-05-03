// Plik obsługujący walidację formularzy

import { validateUrl } from './utils.js';

// Funkcja do wyświetlania błędów walidacji w formularzu
function displayValidationErrors(form, errorMessages) {
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
    const existingErrors = form.querySelectorAll('.validation-errors');
    existingErrors.forEach(el => el.remove());

    // Dodaj nowy komunikat z błędami na górę formularza
    form.insertBefore(errorContainer, form.firstChild);

    // Przewiń stronę do komunikatu, żeby użytkownik od razu go zobaczył
    errorContainer.scrollIntoView({ behavior: 'smooth' });
}

// Inicjalizuje walidację dla formularza dodawania dystrybucji
export function initializeAddFormValidation() {
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
            // Zakładamy, że formularz dodawania nie jest na stronie edit.php
            if (!window.location.pathname.includes('edit.php') && (!logoInput.files || logoInput.files.length === 0)) {
                // Sprawdź, czy istnieje już podgląd (plik został wybrany/przeciągnięty/wklejony)
                const dropZone = logoInput.closest('.file-upload-container');
                const existingPreview = dropZone ? dropZone.querySelector('.image-preview') : null;

                if (!existingPreview) {
                    isValid = false;
                    errorMessages.push('Proszę dodać logo dystrybucji');
                    // Dodaj klasę błędu do rodzica inputa, jeśli istnieje
                    if (logoInput.parentElement) {
                        logoInput.parentElement.classList.add('error-field');
                    }
                } else {
                    // Usuń klasę błędu, jeśli podgląd istnieje
                    if (logoInput.parentElement) {
                        logoInput.parentElement.classList.remove('error-field');
                    }
                }
            } else {
                // Usuń klasę błędu, jeśli plik jest wybrany (lub jesteśmy na stronie edycji)
                if (logoInput.parentElement) {
                    logoInput.parentElement.classList.remove('error-field');
                }
            }

            // Sprawdź, czy podany adres strony jest poprawny (jeśli w ogóle coś wpisano)
            if (websiteInput.value.trim() && !validateUrl(websiteInput.value)) {
                isValid = false;
                errorMessages.push('Proszę podać prawidłowy adres URL strony internetowej');
                websiteInput.classList.add('error-field');
            } else {
                websiteInput.classList.remove('error-field');
            }

            // Jeśli są błędy, nie wysyłaj formularza dalej i pokaż komunikaty
            if (!isValid) {
                event.preventDefault(); // Zatrzymaj domyślną akcję wysyłania formularza
                displayValidationErrors(addForm, errorMessages);
            }
        });
    }
}

// Inicjalizuje walidację dla formularza edycji dystrybucji
export function initializeEditFormValidation() {
    const editForm = document.getElementById('edit-form');
    if (editForm) {
        editForm.addEventListener('submit', function (event) {
            const nameInput = document.getElementById('name');
            const descriptionInput = document.getElementById('description');
            const websiteInput = document.getElementById('website');
            // Logo nie jest wymagane przy edycji, więc go nie sprawdzamy tutaj

            let isValid = true;
            let errorMessages = [];

            // Sprawdź nazwę
            if (!nameInput.value.trim()) {
                isValid = false;
                errorMessages.push('Proszę podać nazwę dystrybucji');
                nameInput.classList.add('error-field');
            } else {
                nameInput.classList.remove('error-field');
            }

            // Sprawdź opis
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

            // Sprawdź stronę WWW (jeśli podana)
            if (websiteInput.value.trim() && !validateUrl(websiteInput.value)) {
                isValid = false;
                errorMessages.push('Proszę podać prawidłowy adres URL strony internetowej');
                websiteInput.classList.add('error-field');
            } else {
                websiteInput.classList.remove('error-field');
            }

            // Jeśli są błędy, zatrzymaj wysyłanie i pokaż komunikaty
            if (!isValid) {
                event.preventDefault();
                displayValidationErrors(editForm, errorMessages);
            }
        });
    }
}
