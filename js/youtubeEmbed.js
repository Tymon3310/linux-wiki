// Plik do obsługi osadzania wideo z YouTube

// Funkcja konwertująca różne formaty URL YouTube na standardowy URL do osadzania (embed)
function getYoutubeEmbedUrl(url) {
    if (!url) return null; // Jak nie ma URL, to nic nie zrobimy

    let videoId = null;

    // Spróbuj dopasować standardowy format: https://www.youtube.com/watch?v=VIDEO_ID
    const watchMatch = url.match(/youtube\.com\/watch\?v=([^&]+)/);
    if (watchMatch) {
        videoId = watchMatch[1];
    }

    // Spróbuj dopasować skrócony format: https://youtu.be/VIDEO_ID
    const shortMatch = url.match(/youtu\.be\/([^?&]+)/);
    if (shortMatch) {
        videoId = shortMatch[1];
    }

    // Spróbuj dopasować format embed: https://www.youtube.com/embed/VIDEO_ID
    const embedMatch = url.match(/youtube\.com\/embed\/([^?&]+)/);
    if (embedMatch) {
        videoId = embedMatch[1];
    }

    // Jeśli udało się wyciągnąć ID filmu, zwróć gotowy URL do osadzenia
    if (videoId) {
        // Dodajemy parametry, żeby było ładniej i nowocześniej
        // rel=0 - nie pokazuj powiązanych filmów z innych kanałów po zakończeniu
        // modestbranding=1 - mniejsze logo YouTube
        // iv_load_policy=3 - ukryj adnotacje
        return `https://www.youtube.com/embed/${videoId}?rel=0&modestbranding=1&iv_load_policy=3`;
    }

    // Nie udało się rozpoznać URL? Zwróć null
    console.warn("Nie udało się rozpoznać formatu URL YouTube:", url);
    return null;
}

// Funkcja inicjalizująca osadzanie wideo na stronie
export function initializeYoutubeEmbed() {
    // Znajdź kontener, w którym ma się pojawić wideo
    const youtubeContainer = document.getElementById('youtube-embed-container');

    // Sprawdź, czy taki kontener w ogóle istnieje na tej stronie
    if (youtubeContainer) {
        // Odczytaj oryginalny URL YouTube z atrybutu data-youtube-url
        const youtubeUrl = youtubeContainer.dataset.youtubeUrl; // Używamy camelCase dla dataset
        // Przekonwertuj URL na format embed
        const embedUrl = getYoutubeEmbedUrl(youtubeUrl);

        // Jeśli konwersja się udała
        if (embedUrl) {
            // Utwórz element iframe
            const iframe = document.createElement('iframe');
            iframe.setAttribute('width', '560'); // Standardowa szerokość, CSS może to nadpisać
            iframe.setAttribute('height', '315'); // Standardowa wysokość, CSS może to nadpisać
            iframe.setAttribute('src', embedUrl);
            iframe.setAttribute('title', 'Odtwarzacz wideo YouTube'); // Dla dostępności
            iframe.setAttribute('frameborder', '0');
            iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            iframe.setAttribute('allowfullscreen', ''); // Pozwól na pełny ekran
            iframe.setAttribute('loading', 'lazy'); // Ładuj iframe dopiero, gdy będzie blisko widoku

            // Wyczyść kontener (na wszelki wypadek) i dodaj do niego iframe
            youtubeContainer.innerHTML = '';
            youtubeContainer.appendChild(iframe);
            console.log("Osadzono wideo YouTube:", embedUrl);
        } else {
            // Jeśli URL był nieprawidłowy, pokaż komunikat o błędzie w kontenerze
            youtubeContainer.innerHTML = '<p class="video-error"><i class="fas fa-exclamation-triangle"></i> Nieprawidłowy lub nieobsługiwany adres URL wideo YouTube.</p>';
        }
    } else {
        // Jeśli nie ma kontenera, nic nie rób (po prostu ta strona nie ma wideo)
        // console.log("Brak kontenera youtube-embed-container na tej stronie.");
    }
}
