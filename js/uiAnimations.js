export function initializeUiAnimations() {
    // Apply fade-in animation to card elements
    const distroCards = document.querySelectorAll('.distro-card');
    if (distroCards.length > 0) {
        distroCards.forEach((card, index) => {
            card.classList.add('fade-in');
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }
    
    // Enhance search section with subtle animation
    const searchSection = document.querySelector('.search-section');
    if (searchSection) {
        searchSection.classList.add('scale-in');
        
        // Add animation to search button when form is focused
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');
        
        if (searchInput && searchButton) {
            searchInput.addEventListener('focus', () => {
                searchSection.classList.add('search-active');
            });
            
            searchInput.addEventListener('blur', () => {
                searchSection.classList.remove('search-active');
            });
        }
    }

    // Animate section titles
    const sectionTitles = document.querySelectorAll('.section-title');
    if (sectionTitles.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        sectionTitles.forEach(title => {
            observer.observe(title);
        });
    }

    // Enhanced back to top button with smooth behavior
    const backToTopButton = document.getElementById('backToTop');
    if (backToTopButton) {
        // Show/hide button on scroll with smooth opacity transition
        window.addEventListener('scroll', () => {
            const scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
            
            if (scrollTop > 300) {
                backToTopButton.style.display = 'flex';
                setTimeout(() => {
                    backToTopButton.style.opacity = '1';
                }, 10);
            } else {
                backToTopButton.style.opacity = '0';
                setTimeout(() => {
                    backToTopButton.style.display = 'none';
                }, 300);
            }
        });

        // Scroll to top with smooth behavior
        backToTopButton.addEventListener('click', (e) => {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn, .btn-primary, .btn-secondary, .btn-details, .btn-edit, .btn-delete');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}
