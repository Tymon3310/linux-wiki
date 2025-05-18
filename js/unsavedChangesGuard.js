// Plik: /opt/lampp/htdocs/js/unsavedChangesGuard.js

// Globalne flagi
window.formIsDirty = false; // Ogólna flaga: czy była interakcja z formularzem?
window.userConfirmedUnload = false; // Czy użytkownik potwierdził opuszczenie strony przez customowy modal?
window.isDestructiveActionInProgress = false; // Czy trwa akcja destrukcyjna (np. usuwanie)?
let G_lastNavigationEvent = null; // Przechowuje ostatnie zdarzenie nawigacyjne, które próbowało opuścić stronę
let G_targetHref = null; // Przechowuje href celu nawigacji

// Przechowuje początkowe dane formularza do porównania.
let initialFormData = {};
// Przechowuje referencje do formularzy, które obserwujemy.
let formsToWatchGlobally = [];

// Funkcja pomocnicza do tworzenia modala, jeśli nie istnieje
function ensureCustomModalExists() {
    if (document.getElementById('custom-unsaved-modal')) return;

    const modalHTML = `
        <div id="custom-unsaved-modal">
            <div class="custom-unsaved-modal-content">
                <h3>Niezapisane zmiany</h3>
                <p>Masz niezapisane zmiany. Czy na pewno chcesz opuścić stronę?</p>
                <div>
                    <button id="custom-unsaved-confirm-leave">Opuść stronę</button>
                    <button id="custom-unsaved-cancel-stay">Pozostań</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

// Funkcja do oznaczania formularza jako "brudny" (zmieniony) - ogólna interakcja
function G_markFormDirty() {
    window.formIsDirty = true;
}

// Funkcja do oznaczania formularza jako "czysty" (niezmieniony lub zapisany)
function G_markFormClean(formContext) {
    if (formContext) {
        initialFormData[formContext.id] = serializeForm(formContext);
    }
    let anyOtherFormIsStillDirty = false;
    for (const form of formsToWatchGlobally) {
        if (form !== formContext && isFormActuallyDirty(form)) {
            anyOtherFormIsStillDirty = true;
            break;
        }
    }
    if (!anyOtherFormIsStillDirty) {
        window.formIsDirty = false;
    }
}

// Funkcja do serializacji danych formularza.
function serializeForm(form) {
    const formData = new FormData(form);
    const data = {};
    for (const [key, value] of formData.entries()) {
        data[key] = value;
    }
    return JSON.stringify(data);
}

// Funkcja do sprawdzania, czy formularz jest rzeczywiście brudny poprzez porównanie danych.
function isFormActuallyDirty(form) {
    if (!form) return false;
    const currentData = serializeForm(form);
    return currentData !== initialFormData[form.id];
}

// Główny handler dla zdarzenia beforeunload
function beforeUnloadHandler(event) {
    if (window.isDestructiveActionInProgress) {
        window.isDestructiveActionInProgress = false; // Zresetuj flagę
        return; // Zezwól na akcję destrukcyjną
    }

    if (window.userConfirmedUnload) {
        window.userConfirmedUnload = false; // Zresetuj flagę na następny raz
        return; // Zezwól na opuszczenie
    }

    let actuallyDirty = false;
    if (window.formIsDirty) {
        for (const form of formsToWatchGlobally) {
            if (isFormActuallyDirty(form)) {
                actuallyDirty = true;
                break;
            }
        }
    }

    if (actuallyDirty) {
        event.preventDefault();
        event.returnValue = ''; // Wymagane dla niektórych przeglądarek, aby uruchomić niestandardowy przepływ dialogu

        const customModal = document.getElementById('custom-unsaved-modal');
        if (customModal) {
            customModal.style.display = 'flex';
        }
        return event.returnValue;
    }
}

// Funkcja inicjująca ochronę przed niezapisanymi zmianami.
export function initUnsavedChangesGuard() {
    ensureCustomModalExists();

    const customModal = document.getElementById('custom-unsaved-modal');
    const confirmLeaveBtn = document.getElementById('custom-unsaved-confirm-leave');
    const cancelStayBtn = document.getElementById('custom-unsaved-cancel-stay');

    if (customModal && confirmLeaveBtn && cancelStayBtn) {
        confirmLeaveBtn.addEventListener('click', () => {
            window.userConfirmedUnload = true;
            customModal.style.display = 'none';

            if (G_targetHref) {
                window.location.href = G_targetHref;
                G_targetHref = null;
                G_lastNavigationEvent = null;
            } else if (G_lastNavigationEvent) {
                G_lastNavigationEvent = null;
            }
        });

        cancelStayBtn.addEventListener('click', () => {
            window.userConfirmedUnload = false;
            G_lastNavigationEvent = null;
            G_targetHref = null;
            customModal.style.display = 'none';
        });
    }

    const formsToMonitor = [
        document.getElementById('add-form'),
        document.getElementById('edit-form'),
        document.getElementById('comment-form')
    ].filter(form => form !== null);

    formsToWatchGlobally = formsToMonitor;

    if (formsToWatchGlobally.length === 0) {
        return;
    }

    formsToWatchGlobally.forEach(form => {
        initialFormData[form.id] = serializeForm(form);

        const setDirtyBasedOnComparison = () => {
            if (isFormActuallyDirty(form)) {
                G_markFormDirty();
            }
        };

        form.addEventListener('input', setDirtyBasedOnComparison);
        form.addEventListener('change', setDirtyBasedOnComparison);

        form.addEventListener('submit', () => G_markFormClean(form));
        form.addEventListener('reset', () => {
            setTimeout(() => {
                G_markFormClean(form);
            }, 0);
        });
    });

    const destructiveForms = [
        document.getElementById('delete-form'),
        document.getElementById('delete-comment-form')
    ].filter(form => form !== null);

    destructiveForms.forEach(form => {
        form.addEventListener('submit', () => {
            window.isDestructiveActionInProgress = true;
        });
    });

    window.removeEventListener('beforeunload', beforeUnloadHandler);
    window.addEventListener('beforeunload', beforeUnloadHandler);

    document.body.addEventListener('click', (event) => {
        let target = event.target;
        while (target && target !== document.body) {
            if (target.tagName === 'A' && target.href &&
                target.href !== '#' &&
                !target.href.startsWith('javascript:') &&
                !target.classList.contains('btn-delete') &&
                !target.classList.contains('btn-delete-comment') &&
                target.target !== '_blank' &&
                !target.hasAttribute('data-bs-toggle') &&
                !target.closest('.modal')
            ) {
                let actuallyDirty = false;
                if (window.formIsDirty) {
                    for (const form of formsToWatchGlobally) {
                        if (isFormActuallyDirty(form)) {
                            actuallyDirty = true;
                            break;
                        }
                    }
                }

                if (actuallyDirty) {
                    event.preventDefault();
                    G_lastNavigationEvent = event;
                    G_targetHref = target.href;

                    const customModal = document.getElementById('custom-unsaved-modal');
                    if (customModal) {
                        customModal.style.display = 'flex';
                    }
                }
                break;
            }
            target = target.parentNode;
        }
    }, true);
}

// Globalne funkcje dostępne dla innych skryptów, jeśli potrzebne
window.G_markFormDirty = G_markFormDirty;
window.G_markFormClean = G_markFormClean;
