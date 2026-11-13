<?php
/**
 * CINEMA BOOKING - Demo Payment Gateway
 * Production checkout flow (Simulated)
 */

require_once __DIR__ . '/includes/auth-check.php';

$checkout = $_SESSION['checkout_data'] ?? null;
if (!$checkout || empty($checkout['seats'])) {
    set_flash('warning', 'Your booking session has expired or no seats were selected. Please choose your seats again.');
    redirect(base_url('movies.php'));
}

$pageTitle = 'Secure Demo Payment';

$extraJs = '<script src="' . asset_url('js/validation.js') . '"></script>';

include __DIR__ . '/includes/header.php';
?>

<div class="container mb-5">
    <div class="page-header-wrap text-center">
        <span class="badge bg-dark border border-danger text-danger px-3 py-1 fs-xs text-uppercase letter-spacing mb-2">Step 3 of 4</span>
        <h1 class="page-title">Demo Payment Gateway</h1>
        <p class="page-subtitle">Simulation mode only. Never enter real banking credentials or real card numbers.</p>
    </div>

    <div class="row g-4 justify-content-center">
        <!-- Order Summary Column -->
        <div class="col-lg-4 order-lg-2">
            <div class="booking-summary-card">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-secondary border-opacity-25">
                    <h5 class="text-white fw-bold m-0" style="font-size: 1.1rem;">Order Summary</h5>
                    <span class="badge bg-dark border border-danger text-danger">
                        <?= (int)$checkout['total_seats'] ?> Seat(s)
                    </span>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <img src="<?= movie_poster_url($checkout['poster_url']) ?>" alt="" style="width: 58px; height: 82px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);" onerror="this.onerror=null; this.src='<?= asset_url('images/poster-placeholder.svg') ?>';">
                    <div>
                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.95rem;"><?= htmlspecialchars($checkout['movie_title']) ?></h6>
                        <small class="text-secondary d-block"><?= htmlspecialchars($checkout['theatre_name']) ?></small>
                        <small class="text-light"><?= htmlspecialchars($checkout['screen_name']) ?></small>
                        <small class="text-secondary d-block mt-1">
                            <i class="bi bi-clock me-1"></i><?= format_date($checkout['show_date'], 'D, d M') ?>, <?= format_time($checkout['show_time']) ?>
                        </small>
                    </div>
                </div>

                <!-- Seat Pills -->
                <div class="mb-3">
                    <span class="text-secondary small d-block mb-1">Booked Seats:</span>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($checkout['seats'] as $st): ?>
                            <span class="badge bg-dark border border-secondary text-light px-2 py-1 fs-xs">
                                <?= htmlspecialchars($st['seat_code']) ?> (<?= ucfirst($st['seat_type']) ?> - ₹<?= number_format($st['price']) ?>)
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="price-breakdown small pt-3 border-top border-secondary border-opacity-25 mb-3">
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Tickets Base Total</span>
                        <span class="text-white"><?= format_currency($checkout['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>Convenience Fee</span>
                        <span class="text-white"><?= format_currency($checkout['convenience_fee']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary mb-1">
                        <span>GST / Taxes (18%)</span>
                        <span class="text-white"><?= format_currency($checkout['tax_amount']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-white fw-bold fs-5 pt-2 border-top border-secondary border-opacity-25 mt-2">
                        <span>Payable Total</span>
                        <span class="text-danger"><?= format_currency($checkout['grand_total']) ?></span>
                    </div>
                </div>

                <div class="alert alert-dark border-secondary text-secondary fs-xs mb-0">
                    <i class="bi bi-shield-lock-fill text-success me-1"></i> 256-bit Encrypted Demo Checkout. Real transactions will not occur.
                </div>
            </div>
        </div>

        <!-- Payment Methods Column -->
        <div class="col-lg-7 order-lg-1">
            <div class="p-4 p-md-5 rounded-3" style="background: var(--bg-card); border: 1px solid var(--border);">
                <ul class="nav nav-pills payment-tabs nav-fill mb-4" id="paymentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="card-tab" data-bs-toggle="tab" data-bs-target="#cardTabPane" type="button" role="tab">
                            <i class="bi bi-credit-card me-2"></i>Card (Demo)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="upi-tab" data-bs-toggle="tab" data-bs-target="#upiTabPane" type="button" role="tab">
                            <i class="bi bi-qr-code-scan me-2"></i>UPI (Demo)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="netbanking-tab" data-bs-toggle="tab" data-bs-target="#netbankingTabPane" type="button" role="tab">
                            <i class="bi bi-bank me-2"></i>Net Banking
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="paymentTabContent">
                    <!-- Tab 1: Card Demo -->
                    <div class="tab-pane fade show active" id="cardTabPane" role="tabpanel">
                        <!-- Card Mockup Visual -->
                        <div class="card-mockup">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold tracking-wider fs-6">CINEMA LUX CARD</span>
                                <i class="bi bi-wifi fs-5 opacity-75"></i>
                            </div>
                            <div class="card-chip"></div>
                            <div class="card-number-display" id="displayCardNumber">4242 4242 4242 4242</div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="fs-xs opacity-75 text-uppercase">Cardholder</div>
                                    <div class="fw-bold fs-6" id="displayCardHolder"><?= strtoupper(htmlspecialchars(current_user()['full_name'])) ?></div>
                                </div>
                                <div>
                                    <div class="fs-xs opacity-75 text-uppercase">Expires</div>
                                    <div class="fw-bold fs-6" id="displayCardExpiry">12/28</div>
                                </div>
                                <div>
                                    <i class="bi bi-credit-card-2-front-fill fs-3 text-warning"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Card Form -->
                        <form action="<?= base_url('backend/payment-process.php') ?>" method="POST" id="cardPaymentForm">
                            <?= csrf_input() ?>
                            <input type="hidden" name="payment_method" value="card">

                            <div class="mb-3">
                                <label class="form-label text-white small fw-bold">Card Number (Simulated)</label>
                                <input type="text" name="card_number" id="demoCardNumber" class="form-control cinematic-input font-monospace" placeholder="4242 4242 4242 4242" value="4242 4242 4242 4242" required maxlength="19">
                                <span class="text-secondary fs-xs">Sample test number prefilled. No real credit card will be billed.</span>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-white small fw-bold">Name on Card</label>
                                <input type="text" name="card_holder" id="demoCardHolder" class="form-control cinematic-input" placeholder="JOHN DOE" value="<?= htmlspecialchars(current_user()['full_name']) ?>" required>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="form-label text-white small fw-bold">Expiry Date</label>
                                    <input type="text" name="card_expiry" id="demoCardExpiry" class="form-control cinematic-input" placeholder="MM/YY" value="12/28" required maxlength="5">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-white small fw-bold">CVV (Demo)</label>
                                    <input type="password" name="card_cvv" id="demoCardCvv" class="form-control cinematic-input" placeholder="•••" value="123" required maxlength="4">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-3 fw-bold fs-6">
                                <i class="bi bi-check-circle-fill me-2"></i>Pay <?= format_currency($checkout['grand_total']) ?> (Simulate)
                            </button>
                        </form>
                    </div>

                    <!-- Tab 2: UPI Demo -->
                    <div class="tab-pane fade" id="upiTabPane" role="tabpanel">
                        <div class="text-center p-4 rounded-2 mb-4 bg-dark border border-secondary border-opacity-25">
                            <div class="p-3 bg-white d-inline-block rounded-2 mb-3 shadow">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=upi://pay?pa=cinema@demo&pn=CinemaBooking&am=<?= $checkout['grand_total'] ?>" alt="Demo UPI QR" style="width: 140px; height: 140px; display: block;">
                            </div>
                            <h6 class="text-white fw-semibold mb-1">Scan QR with any UPI App</h6>
                            <p class="text-secondary fs-xs mb-0">Google Pay, PhonePe, Paytm, or BHIM (Mock Simulation)</p>
                        </div>

                        <form action="<?= base_url('backend/payment-process.php') ?>" method="POST">
                            <?= csrf_input() ?>
                            <input type="hidden" name="payment_method" value="upi">

                            <div class="mb-4">
                                <label class="form-label text-white small fw-bold">Or Enter Demo UPI ID / VPA</label>
                                <div class="input-group">
                                    <input type="text" name="upi_id" class="form-control cinematic-input" placeholder="username@okhdfcbank" value="<?= strtolower(explode(' ', current_user()['full_name'])[0]) ?>@okcinema" required>
                                    <span class="input-group-text bg-dark border-secondary text-secondary">@upi</span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-3 fw-bold fs-6">
                                <i class="bi bi-arrow-right-circle-fill me-2"></i>Approve & Pay <?= format_currency($checkout['grand_total']) ?>
                            </button>
                        </form>
                    </div>

                    <!-- Tab 3: Net Banking Demo -->
                    <div class="tab-pane fade" id="netbankingTabPane" role="tabpanel">
                        <form action="<?= base_url('backend/payment-process.php') ?>" method="POST">
                            <?= csrf_input() ?>
                            <input type="hidden" name="payment_method" value="netbanking">

                            <label class="form-label text-white small fw-bold mb-3">Select Popular Bank (Demo)</label>
                            <div class="row row-cols-2 row-cols-sm-3 g-2 mb-4">
                                <?php 
                                $banks = ['HDFC Bank', 'State Bank of India', 'ICICI Bank', 'Axis Bank', 'Kotak Mahindra', 'Punjab National Bank'];
                                foreach ($banks as $i => $bank):
                                ?>
                                    <div class="col">
                                        <label class="d-flex align-items-center gap-2 p-3 rounded-2 border border-secondary border-opacity-50 text-light w-100" style="background: var(--bg-secondary); cursor: pointer;">
                                            <input type="radio" name="bank_name" value="<?= $bank ?>" <?= $i === 0 ? 'checked' : '' ?> class="form-check-input mt-0">
                                            <span class="small text-truncate"><?= $bank ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button type="submit" class="btn btn-accent w-100 py-3 fw-bold fs-6">
                                <i class="bi bi-bank2 me-2"></i>Continue with Net Banking
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
