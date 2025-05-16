export function initializeUiAnimations() {
    // Animacje fade-in wyzwalane przewijaniem
    const fadeElements = document.querySelectorAll('.fade-in');
    if (fadeElements.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });

        fadeElements.forEach(element => {
            observer.observe(element);
        });
    }

    // Funkcja obsługująca przycisk "Powrót do góry"
    const backToTopButton = document.getElementById('backToTop');

    if (backToTopButton) {
        // Pokaż/ukryj przycisk podczas przewijania
        window.addEventListener('scroll', () => {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        // Przewiń do góry po kliknięciu
        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}
