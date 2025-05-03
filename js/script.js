// Główny plik JavaScript aplikacji - taki trochę dyrygent :)

// Importujemy potrzebne funkcje z innych plików (modułów)
import { initializeShowAddFormButton, initializeCharacterCounters, displayStatusMessages } from './uiUtils.js';
import { initializeAddFormValidation, initializeEditFormValidation } from './formHandler.js';
import { initializeImageUpload } from './imageUpload.js';
import { initializeThemeSwitcher } from './themeSwitcher.js';
import { initializeSearch } from './searchHandler.js';
import { initializeAllTabs } from './tabs.js'; // Moduł do obsługi zakładek
import { initializeYoutubeEmbed } from './youtubeEmbed.js'; // Importujemy nowy moduł YouTube

// Czekamy, aż cała strona (DOM) się załaduje
document.addEventListener('DOMContentLoaded', function () {
    console.log("Strona gotowa, można działać!");

    // Sprawdzamy, czy PHP ustawiło nam flagę zalogowania użytkownika
    // Domyślnie zakładamy, że nie jest zalogowany, jeśli zmienna nie istnieje
    const isUserLoggedIn = typeof window.isUserLoggedIn !== 'undefined' ? window.isUserLoggedIn : false;

    // Uruchamiamy inicjalizację różnych części interfejsu
    initializeShowAddFormButton(isUserLoggedIn); // Przycisk "Dodaj dystrybucję"
    initializeCharacterCounters(); // Liczniki znaków w polach tekstowych
    displayStatusMessages(); // Komunikaty o sukcesie/błędzie (np. po dodaniu dystrybucji)

    // Uruchamiamy walidację formularzy
    initializeAddFormValidation(); // Formularz dodawania
    initializeEditFormValidation(); // Formularz edycji

    // Uruchamiamy obsługę przesyłania obrazków (logo)
    initializeImageUpload();

    // Uruchamiamy przełącznik motywów (jasny/ciemny)
    initializeThemeSwitcher();

    // Uruchamiamy wyszukiwarkę
    initializeSearch();

    // Uruchamiamy obsługę zakładek (np. na stronie logowania)
    initializeAllTabs();

    // Uruchamiamy osadzanie wideo YouTube (jeśli jest na stronie)
    initializeYoutubeEmbed();

    console.log("Wszystkie skrypty startowe wykonane.");
});