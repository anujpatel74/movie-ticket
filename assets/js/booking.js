/**
 * CINEMA BOOKING - Movie & Booking Flow Interactivity
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Movie Genre and Status Filters (on movies.php)
    const filterButtons = document.querySelectorAll('.movie-filter-btn');
    const movieCards = document.querySelectorAll('.filterable-movie-col');

    if (filterButtons.length > 0 && movieCards.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                filterButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.getAttribute('data-filter');

                movieCards.forEach(card => {
                    const genres = card.getAttribute('data-genre') || '';
                    const status = card.getAttribute('data-status') || '';
                    const language = card.getAttribute('data-language') || '';

                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else if (status === filter) {
                        card.style.display = 'block';
                    } else if (genres.toLowerCase().includes(filter.toLowerCase())) {
                        card.style.display = 'block';
                    } else if (language.toLowerCase() === filter.toLowerCase()) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // 2. Interactive Date Pills for Showtimes (on movie-details.php & booking.php)
    const datePills = document.querySelectorAll('.date-pill');
    datePills.forEach(pill => {
        pill.addEventListener('click', function (e) {
            // If pill is an anchor link, allow browser navigation; otherwise switch view
            if (!this.getAttribute('href')) {
                e.preventDefault();
                datePills.forEach(p => p.classList.remove('active'));
                this.classList.add('active');

                const selectedDate = this.getAttribute('data-date');
                const showContainers = document.querySelectorAll('.date-showtimes-group');
                showContainers.forEach(group => {
                    if (group.getAttribute('data-date') === selectedDate) {
                        group.style.display = 'block';
                    } else {
                        group.style.display = 'none';
                    }
                });
            }
        });
    });
});

