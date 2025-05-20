// Główny plik JavaScript aplikacji - taki trochę dyrygent :)

// Importujemy potrzebne funkcje z innych plików (modułów)
import { initializeShowAddFormButton, initializeCharacterCounters, initializeDeleteModals } from './uiUtils.js';
import { initializeAddFormValidation, initializeEditFormValidation } from './formHandler.js';
import { initializeImageUpload } from './imageUpload.js';
import { initializeThemeSwitcher } from './themeSwitcher.js';
import { initializeSearch } from './searchHandler.js';
import { initializeAllTabs } from './tabs.js';
import { initializeYoutubeEmbed } from './youtubeEmbed.js';
import { initUnsavedChangesGuard } from './unsavedChangesGuard.js';
import { loadBadAppleFramesConsole, playBadAppleConsole, stopBadAppleConsole } from './easterEgg.js';
import { initializeUiAnimations } from './uiAnimations.js';
import { initializeAdmin, toggleEdit, filterUsers } from './admin.js';

// Czekamy, aż cała strona (DOM) się załaduje
document.addEventListener('DOMContentLoaded', function () {
    console.log("Strona gotowa, można działać!");

    // Sprawdzamy, czy PHP ustawiło nam flagę zalogowania użytkownika
    // Domyślnie zakładamy, że nie jest zalogowany, jeśli zmienna nie istnieje
    const isUserLoggedIn = typeof window.isUserLoggedIn !== 'undefined' ? window.isUserLoggedIn : false;

    // Uruchamiamy inicjalizację różnych części interfejsu
    initializeShowAddFormButton(isUserLoggedIn); // Przycisk "Dodaj dystrybucję"
    initializeCharacterCounters(); // Liczniki znaków w polach tekstowych

    // Uruchamiamy walidację formularzy
    initializeAddFormValidation(); // Formularz dodawania
    initializeEditFormValidation(); // Formularz edycji

    // Uruchamiamy obsługę przesyłania obrazków (logo)
    initializeImageUpload();

    // Uruchamiamy przełącznik motywów (jasny/ciemny)
    initializeThemeSwitcher();

    // Uruchamiamy wyszukiwarkę
    initializeSearch();

    // Uruchamiamy obsługę zakładek
    initializeAllTabs();

    // Uruchamiamy osadzanie wideo YouTube (jeśli jest na stronie)
    initializeYoutubeEmbed();

    // Inicjalizujemy obsługę modali usuwania
    initializeDeleteModals();

    // Uruchamiamy ochronę przed niezapisanymi zmianami
    initUnsavedChangesGuard();

    // Inicjalizujemy animacje UI (fade-in, back-to-top)
    initializeUiAnimations();

    // Inicjalizujemy Easter Egg
    loadBadAppleFramesConsole(); // Ładujemy klatki przy starcie

    initializeAdmin(); // Inicjalizujemy panel admina

    console.log("Wszystkie skrypty startowe wykonane.");
    // Funkcja globalna do rozwijania panelu edycji admina

});