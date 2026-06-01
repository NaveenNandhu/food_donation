document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    const requestBtns = document.querySelectorAll('.request-btn');
    const modal = document.querySelector('.request-modal');
    const modalClose = document.querySelector('.modal-close');
    const donationIdInput = document.querySelector('#donation_id');

    requestBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const donationId = this.dataset.id;
            if (donationIdInput) {
                donationIdInput.value = donationId;
            }
            if (modal) {
                modal.classList.add('active');
            }
        });
    });

    if (modalClose) {
        modalClose.addEventListener('click', function() {
            modal.classList.remove('active');
        });
    }

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    }

    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    const expiryDate = document.querySelector('input[name="expiry_date"]');
    if (expiryDate) {
        const today = new Date().toISOString().split('T')[0];
        expiryDate.setAttribute('min', today);
    }
});

function confirmAction(message) {
    return confirm(message);
}

function deleteItem(url, itemName) {
    if (confirm(`Are you sure you want to delete this ${itemName}?`)) {
        window.location.href = url;
    }
}
