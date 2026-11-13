/**
 * CINEMA BOOKING - Global Master JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar Scroll Effect
    const navbar = document.querySelector('.cinematic-navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 40) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // 2. Initialize Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // 3. Trailer Modal Handler (dynamic iframe insertion & cleanup)
    const trailerModal = document.getElementById('trailerModal');
    if (trailerModal) {
        const trailerIframe = document.getElementById('trailerVideoFrame');
        
        trailerModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const videoUrl = button ? button.getAttribute('data-trailer-url') : '';
            if (videoUrl && trailerIframe) {
                // Convert youtube watch URL to embed URL
                let embedUrl = videoUrl;
                if (videoUrl.includes('youtube.com/watch?v=')) {
                    embedUrl = videoUrl.replace('watch?v=', 'embed/') + '?autoplay=1';
                } else if (videoUrl.includes('youtu.be/')) {
                    embedUrl = videoUrl.replace('youtu.be/', 'youtube.com/embed/') + '?autoplay=1';
                }
                trailerIframe.src = embedUrl;
            }
        });

        trailerModal.addEventListener('hidden.bs.modal', () => {
            if (trailerIframe) {
                trailerIframe.src = '';
            }
        });
    }

    // 4. Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.cinematic-alert');
    alerts.forEach(alertEl => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            if (bsAlert) bsAlert.close();
        }, 6000);
    });
});

