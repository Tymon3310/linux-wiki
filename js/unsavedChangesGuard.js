// Plik: /opt/lampp/htdocs/js/unsavedChangesGuard.js

// Globalne flagi
window.formIsDirty = false; // Ogólna flaga: czy była interakcja z formularzem?
window.userConfirmedUnload = false; // Czy użytkownik potwierdził opuszczenie strony przez customowy modal?
window.isDestructiveActionInProgress = false; // Czy trwa akcja destrukcyjna (np. usuwanie)?

// Przechowuje początkowe dane formularza do porównania.
let initialFormData = {};
// Przechowuje referencje do formularzy, które obserwujemy.
let formsToWatchGlobally = [];

// Funkcja pomocnicza do tworzenia modala, jeśli nie istnieje
function ensureCustomModalExists() {
    if (document.getElementById('custom-unsaved-modal')) return;

    const modalHTML = `
        <div id="custom-unsaved-modal" style="display:none; position:fixed; z-index:10000; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
            <div style="background-color:#fff; color:#333; margin:auto; padding:20px; border:1px solid #888; width:90%; max-width:450px; text-align:center; border-radius:8px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                <h3 style="margin-top:0; font-size:1.5em;">Niezapisane zmiany</h3>
                <p style="font-size:1.1em;">Masz niezapisane zmiany. Czy na pewno chcesz opuścić stronę?</p>
                <div style="margin-top:25px;">
                    <button id="custom-unsaved-confirm-leave" style="padding:10px 18px; margin-right:10px; background-color:#d9534f; color:white; border:none; border-radius:5px; cursor:pointer; font-size:1em;">Opuść stronę</button>
                    <button id="custom-unsaved-cancel-stay" style="padding:10px 18px; background-color:#6c757d; color:white; border:none; border-radius:5px; cursor:pointer; font-size:1em;">Pozostań</button>
                </div>
            </div>
        </div>
    `;
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        #custom-unsaved-modal {
            display: none;
        }
    `;
    document.head.appendChild(styleElement);
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
        window.isDestructiveActionInProgress = false;
        return;
    }

    if (window.userConfirmedUnload) {
        window.userConfirmedUnload = false;
        return;
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
        event.returnValue = '';

        const customModal = document.getElementById('custom-unsaved-modal');
        if (customModal) {
            customModal.style.display = 'flex';
        }
        return '';
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
        });

        cancelStayBtn.addEventListener('click', () => {
            window.userConfirmedUnload = false;
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
}

// Globalne funkcje dostępne dla innych skryptów, jeśli potrzebne
window.G_markFormDirty = G_markFormDirty;
window.G_markFormClean = G_markFormClean;
