// Plik do obsługi przełączania zakładek (tabsów)

// Funkcja inicjalizująca zakładki w danym kontenerze
// tabContainerSelector - selektor CSS dla kontenera z zakładkami (np. '.tab-container')
function initializeTabs(tabContainerSelector) {
    const tabContainers = document.querySelectorAll(tabContainerSelector);

    tabContainers.forEach(container => {
        // Znajdź wszystkie zakładki (przyciski) będące bezpośrednimi dziećmi kontenera
        const tabs = container.querySelectorAll(':scope > .tab');
        // Odczytaj z atrybutu data-, jakiego atrybutu użyć do znalezienia panelu treści (domyślnie 'id')
        const contentAttribute = container.dataset.contentTarget || 'id';
        // Odczytaj z atrybutu data-, jaki prefix dodać do selektora panelu treści (domyślnie '#' dla ID)
        const contentSelectorPrefix = container.dataset.contentSelectorPrefix || '#';
        // Znajdź kontener, w którym są panele treści (może być inny niż kontener zakładek, określony przez data-content-container, domyślnie rodzic kontenera zakładek)
        const contentContainer = container.dataset.contentContainer ? document.querySelector(container.dataset.contentContainer) : container.parentElement;

        if (!contentContainer) {
            console.error('Nie mogę znaleźć kontenera z treścią dla zakładek:', container);
            return; // Przejdź do następnego kontenera zakładek
        }

        // Dodaj obsługę kliknięcia dla każdej zakładki w tym kontenerze
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Pobierz identyfikator zakładki z atrybutu data-tab
                const tabId = this.dataset.tab;

                // Dezaktywuj wszystkie zakładki w tym kontenerze (usuń klasę 'active')
                tabs.forEach(t => t.classList.remove('active'));

                // Aktywuj klikniętą zakładkę (dodaj klasę 'active')
                this.classList.add('active');

                // Znajdź i dezaktywuj wszystkie panele treści powiązane z tym zestawem zakładek
                // Szukamy paneli będących bezpośrednimi dziećmi kontenera treści
                const contentPanels = contentContainer.querySelectorAll(':scope > .tab-content');
                contentPanels.forEach(panel => {
                    // Sprawdź, czy panel należy do tego zestawu zakładek
                    // (porównując jego atrybut (np. id) z data-tab zakładek)
                    const panelId = panel.getAttribute(contentAttribute) || panel.id;
                    let belongsToSet = false;
                    tabs.forEach(t => {
                        if (panelId === t.dataset.tab) {
                            belongsToSet = true;
                        }
                    });

                    // Jeśli panel należy do tego zestawu, dezaktywuj go
                    if (belongsToSet) {
                        panel.classList.remove('active');
                    }
                });

                // Aktywuj odpowiedni panel treści
                // Zbuduj selektor (np. '#login-form')
                const targetPanelSelector = `${contentSelectorPrefix}${tabId}`;
                const targetPanel = contentContainer.querySelector(targetPanelSelector);
                // Sprawdź, czy panel istnieje i ma klasę 'tab-content'
                if (targetPanel && targetPanel.classList.contains('tab-content')) {
                    targetPanel.classList.add('active'); // Pokaż panel
                } else {
                    console.warn(`Nie znaleziono panelu treści dla selektora: ${targetPanelSelector} w kontenerze:`, contentContainer);
                }
            });
        });

        // Opcjonalnie: Aktywuj pierwszą zakładkę domyślnie, jeśli żadna nie jest aktywna
        const activeTab = container.querySelector('.tab.active');
        if (!activeTab && tabs.length > 0) {
            // Aktywujemy ręcznie, zamiast symulować kliknięcie, co jest bezpieczniejsze
            tabs[0].classList.add('active');
            const firstTabId = tabs[0].dataset.tab;
            const firstPanelSelector = `${contentSelectorPrefix}${firstTabId}`;
            const firstPanel = contentContainer.querySelector(firstPanelSelector);
            if (firstPanel && firstPanel.classList.contains('tab-content')) {
                // Najpierw upewnij się, że inne panele z tego zestawu są nieaktywne
                const contentPanels = contentContainer.querySelectorAll(':scope > .tab-content');
                contentPanels.forEach(panel => {
                    const panelId = panel.getAttribute(contentAttribute) || panel.id;
                    let belongsToSet = false;
                    tabs.forEach(t => {
                        if (panelId === t.dataset.tab) {
                            belongsToSet = true;
                        }
                    });
                    // Dezaktywuj, jeśli należy do zestawu i nie jest pierwszym panelem
                    if (belongsToSet && panel !== firstPanel) {
                        panel.classList.remove('active');
                    }
                });
                // Aktywuj pierwszy panel
                firstPanel.classList.add('active');
            }
        }
    });
}

// Funkcja do zainicjalizowania WSZYSTKICH zakładek na stronie
export function initializeAllTabs() {
    // Inicjalizuj główne zakładki (np. logowanie/rejestracja)
    // Zakładamy, że nie mają specjalnej klasy, więc wykluczamy te od aktywności użytkownika
    initializeTabs('.tab-container:not(.activity-tabs)');
    // Inicjalizuj zakładki aktywności użytkownika (na stronie profilu)
    initializeTabs('.tab-container.activity-tabs');
}

// Funkcja do inicjalizacji zakładek dla formularzy logowania/rejestracji
