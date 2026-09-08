// ============================================================
//   contact.js — Contact form submit handler
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('contactForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const name    = document.getElementById('contact-name').value.trim();
        const phone   = document.getElementById('contact-phone').value.trim();
        const message = document.getElementById('contact-message').value.trim();
        const statusEl = document.getElementById('contact-status');
        const btn = form.querySelector('.main-btn');

        if (!name || !phone || !message) {
            showStatus('Please fill all fields.', 'error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Sending...';

        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('message', message);

        fetch('includes/process-contact.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showStatus('Message sent! We will get back to you soon.', 'success');
                    form.reset();
                } else {
                    showStatus(data.msg || 'Failed to send message. Please try again.', 'error');
                }
            })
            .catch(() => {
                showStatus('Network error. Please try again.', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Send Message';
            });
    });

    function showStatus(msg, type) {
        const statusEl = document.getElementById('contact-status');
        if (!statusEl) return;
        statusEl.textContent = msg;
        statusEl.style.display = 'block';
        statusEl.style.color = type === 'success' ? '#15803d' : '#991b1b';
    }
});