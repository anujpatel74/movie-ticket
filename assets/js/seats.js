/**
 * CINEMA BOOKING - Interactive Seat Layout & Real-time Calculation
 */

document.addEventListener('DOMContentLoaded', () => {
    const seatGrid = document.getElementById('seatGrid');
    if (!seatGrid) return;

    const showId = seatGrid.getAttribute('data-show-id');
    const regularPrice = parseFloat(seatGrid.getAttribute('data-regular-price') || 0);
    const premiumPrice = parseFloat(seatGrid.getAttribute('data-premium-price') || 0);
    const reclinerPrice = parseFloat(seatGrid.getAttribute('data-recliner-price') || 0);
    const convenienceFee = parseFloat(seatGrid.getAttribute('data-fee') || 35.00);

    const maxSeats = 8;
    let selectedSeats = new Map(); // seatId -> { code, type, price }

    // Summary UI Elements
    const selectedCountEl = document.getElementById('selectedSeatCount');
    const selectedListEl = document.getElementById('selectedSeatList');
    const subtotalEl = document.getElementById('summarySubtotal');
    const feeEl = document.getElementById('summaryFee');
    const taxEl = document.getElementById('summaryTax');
    const grandTotalEl = document.getElementById('summaryGrandTotal');
    const checkoutBtn = document.getElementById('btnProceedToPayment');
    const hiddenSeatsInput = document.getElementById('hiddenSelectedSeats');

    // 1. Fetch live booked seats from server to ensure database sync
    async function syncBookedSeats() {
        try {
            const resp = await fetch(`backend/seat-process.php?action=get_booked_seats&show_id=${encodeURIComponent(showId)}`);
            if (resp.ok) {
                const data = await resp.json();
                if (data.success && Array.isArray(data.booked_seat_ids)) {
                    data.booked_seat_ids.forEach(seatId => {
                        const seatElem = document.querySelector(`.seat[data-seat-id="${seatId}"]`);
                        if (seatElem) {
                            seatElem.classList.add('booked');
                            seatElem.classList.remove('selected');
                            seatElem.setAttribute('title', 'Already Booked');
                            // If it was selected locally, remove it
                            selectedSeats.delete(seatId.toString());
                        }
                    });
                    updateSummary();
                }
            }
        } catch (err) {
            console.warn('Could not sync live booked seats:', err);
        }
    }

    // Initial sync
    syncBookedSeats();

    // 2. Handle Seat Click
    seatGrid.addEventListener('click', (e) => {
        const seat = e.target.closest('.seat');
        if (!seat || seat.classList.contains('booked')) return;

        const seatId = seat.getAttribute('data-seat-id');
        const seatCode = seat.getAttribute('data-seat-code');
        const seatType = seat.getAttribute('data-seat-type');

        if (seat.classList.contains('selected')) {
            // Deselect
            seat.classList.remove('selected');
            selectedSeats.delete(seatId);
        } else {
            // Check seat limit
            if (selectedSeats.size >= maxSeats) {
                alert(`You can select a maximum of ${maxSeats} seats per booking.`);
                return;
            }

            // Determine price
            let price = regularPrice;
            if (seatType === 'premium') price = premiumPrice;
            if (seatType === 'recliner') price = reclinerPrice;

            seat.classList.add('selected');
            selectedSeats.set(seatId, {
                id: seatId,
                code: seatCode,
                type: seatType,
                price: price
            });
        }

        updateSummary();
    });

    // 3. Update Summary and Calculations
    function updateSummary() {
        const count = selectedSeats.size;
        if (selectedCountEl) selectedCountEl.textContent = count;

        let subtotal = 0;
        const codes = [];
        const seatDataToSubmit = [];

        selectedSeats.forEach(seat => {
            subtotal += seat.price;
            codes.push(seat.code);
            seatDataToSubmit.push({
                id: seat.id,
                code: seat.code,
                type: seat.type,
                price: seat.price
            });
        });

        // Calculate tax (18% GST standard on cinema tickets)
        const tax = subtotal > 0 ? (subtotal * 0.18) : 0;
        const currentFee = count > 0 ? convenienceFee : 0;
        const grandTotal = subtotal > 0 ? (subtotal + currentFee + tax) : 0;

        // Render seat list badges
        if (selectedListEl) {
            if (codes.length === 0) {
                selectedListEl.innerHTML = '<span class="text-secondary small fst-italic">No seats selected yet</span>';
            } else {
                selectedListEl.innerHTML = codes.map(code => 
                    `<span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1">${code}</span>`
                ).join(' ');
            }
        }

        // Render financial summary
        if (subtotalEl) subtotalEl.textContent = '₹' + subtotal.toFixed(2);
        if (feeEl) feeEl.textContent = '₹' + currentFee.toFixed(2);
        if (taxEl) taxEl.textContent = '₹' + tax.toFixed(2);
        if (grandTotalEl) grandTotalEl.textContent = '₹' + grandTotal.toFixed(2);

        // Update hidden form inputs
        if (hiddenSeatsInput) {
            hiddenSeatsInput.value = JSON.stringify(seatDataToSubmit);
        }

        // Enable / Disable Checkout
        if (checkoutBtn) {
            if (count > 0) {
                checkoutBtn.removeAttribute('disabled');
                checkoutBtn.innerHTML = `Book ${count} Seat(s) &bull; ₹${grandTotal.toFixed(2)}`;
            } else {
                checkoutBtn.setAttribute('disabled', 'disabled');
                checkoutBtn.innerHTML = 'Select Seats to Proceed';
            }
        }
    }
});

