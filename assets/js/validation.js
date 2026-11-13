/**
 * CINEMA BOOKING - Form Validation & Payment Formatters
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Confirmation Match
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        const pass = document.getElementById('password');
        const confirmPass = document.getElementById('confirmPassword');
        const errorMsg = document.getElementById('passMatchError');

        regForm.addEventListener('submit', (e) => {
            if (pass && confirmPass && pass.value !== confirmPass.value) {
                e.preventDefault();
                if (errorMsg) {
                    errorMsg.classList.remove('d-none');
                } else {
                    alert('Passwords do not match. Please verify.');
                }
                confirmPass.focus();
            }
        });
    }

    // 2. Demo Card Number Formatter & Card Preview
    const cardInput = document.getElementById('demoCardNumber');
    const cardDisplay = document.getElementById('displayCardNumber');
    const cardHolderInput = document.getElementById('demoCardHolder');
    const cardHolderDisplay = document.getElementById('displayCardHolder');
    const cardExpiryInput = document.getElementById('demoCardExpiry');
    const cardExpiryDisplay = document.getElementById('displayCardExpiry');

    if (cardInput) {
        cardInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 16);
            let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
            e.target.value = formatted;
            if (cardDisplay) {
                cardDisplay.textContent = formatted || '•••• •••• •••• ••••';
            }
        });
    }

    if (cardHolderInput && cardHolderDisplay) {
        cardHolderInput.addEventListener('input', (e) => {
            cardHolderDisplay.textContent = e.target.value.toUpperCase() || 'YOUR NAME';
        });
    }

    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', (e) => {
            let val = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (val.length >= 2) {
                e.target.value = val.substring(0, 2) + '/' + val.substring(2);
            } else {
                e.target.value = val;
            }
            if (cardExpiryDisplay) {
                cardExpiryDisplay.textContent = e.target.value || 'MM/YY';
            }
        });
    }
});

