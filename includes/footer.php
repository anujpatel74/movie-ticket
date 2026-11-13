</main>

<!-- CINEMA BOOKING Global Footer -->
<footer class="cinematic-footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4 mb-4">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <span class="brand-icon me-2">
                        <i class="bi bi-film"></i>
                    </span>
                    <span class="brand-text">
                        <span class="brand-white">CINEMA</span>
                        <span class="brand-accent">BOOKING</span>
                    </span>
                </div>
                <p class="text-secondary small pe-lg-4">
                    CINEMA BOOKING is your premier destination for next-generation cinematic experiences. Reserve premium IMAX, Dolby Atmos, and luxury recliner seats with zero hassle and instant digital tickets.
                </p>
                <div class="d-flex gap-3 text-secondary footer-social mt-3">
                    <a href="#" class="social-icon" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon" aria-label="Twitter / X"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Movies Column -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3">Movies</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= base_url('movies.php?status=now_showing') ?>">Now Showing</a></li>
                    <li><a href="<?= base_url('movies.php?status=coming_soon') ?>">Coming Soon</a></li>
                    <li><a href="<?= base_url('movies.php?genre=Action') ?>">Action & Sci-Fi</a></li>
                    <li><a href="<?= base_url('movies.php?genre=Drama') ?>">Drama & Thriller</a></li>
                    <li><a href="<?= base_url('theatres.php') ?>">Multiplex Theatres</a></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6 class="footer-heading text-white fw-bold mb-3">Quick Links</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= base_url('my-bookings.php') ?>">My Bookings</a></li>
                    <li><a href="<?= base_url('theatres.php') ?>">Find Theatres</a></li>
                    <li><a href="<?= base_url('login.php') ?>">User Login</a></li>
                    <li><a href="<?= base_url('register.php') ?>">Register Account</a></li>
                    <li><a href="<?= base_url('admin/index.php') ?>" target="_blank">Admin Portal</a></li>
                </ul>
            </div>

            <!-- Newsletter & Demo Notice -->
            <div class="col-lg-4 col-md-6">
                <h6 class="footer-heading text-white fw-bold mb-3">Stay Updated</h6>
                <p class="text-secondary small">Subscribe to get notifications about blockbuster releases, advance ticket bookings, and exclusive movie premieres.</p>
                <form class="newsletter-form mb-3" onsubmit="event.preventDefault(); alert('Thank you for subscribing to CINEMA BOOKING updates!');">
                    <div class="input-group">
                        <input type="email" class="form-control bg-dark border-secondary text-white" placeholder="Enter your email" required>
                        <button class="btn btn-accent" type="submit">Join</button>
                    </div>
                </form>
                <div class="payment-badges-box p-2 rounded bg-black bg-opacity-40 border border-secondary border-opacity-25">
                    <div class="d-flex align-items-center justify-content-between text-secondary fs-xs">
                        <span><i class="bi bi-shield-check text-success me-1"></i> Demo Payment Gateway</span>
                        <div class="d-flex gap-2">
                            <span class="badge bg-dark border border-secondary text-light">UPI</span>
                            <span class="badge bg-dark border border-secondary text-light">Cards</span>
                            <span class="badge bg-dark border border-secondary text-light">NetBanking</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                <p class="text-secondary small mb-1">&copy; <?= date('Y') ?> CINEMA BOOKING System. All rights reserved.</p>
                <!-- TMDB Official Attribution -->
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mt-1">
                    <img src="<?= asset_url('images/tmdb-logo.svg') ?>" alt="TMDB Logo" style="height: 11px; opacity: 0.7;">
                    <span class="text-secondary fs-xs" style="font-size: 0.68rem;">
                        This product uses the TMDB API but is not endorsed or certified by TMDB.
                    </span>
                </div>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <span class="badge bg-dark border border-danger text-danger me-2">
                    <i class="bi bi-laptop me-1"></i> XAMPP Ready
                </span>
                <span class="badge bg-dark border border-secondary text-secondary">
                    <i class="bi bi-lock-fill me-1"></i> Secure Sessions
                </span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3.3 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Global Main JS -->
<script src="<?= asset_url('js/main.js') ?>"></script>

<!-- Smart Movie Search & TMDB JS -->
<script src="<?= asset_url('js/movie-search.js') ?>"></script>

<?php if (isset($extraJs)): ?>
    <?= $extraJs ?>
<?php endif; ?>

</body>
</html>

