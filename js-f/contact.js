// ============================================================
//   js-f/contact.js — Contact Form & Replies Dynamic Logic
//   Yeh file contact form submission, session authentication, aur admin replies ko dynamically handle karti hai
// ============================================================

// Page load hone ke baad login status check aur replies load kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {
    checkLoginAndLoadReplies();

    // Contact form ko select kar rahe hain.
    const form = document.getElementById('contactForm');
    if (!form) return;

    // Form submit hone par default page reload ko rok kar custom submit process chala rahe hain.
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Form se user ki entered information read aur trim kar rahe hain.
        const name    = document.getElementById('contact-name').value.trim();
        const phone   = document.getElementById('contact-phone').value.trim();
        const message = document.getElementById('contact-message').value.trim();
        const btn = form.querySelector('.main-btn');

        // Required fields empty hon to error message show karke process stop kar rahe hain.
        if (!name || !phone || !message) {
            showStatus('Please fill all fields.', 'error');
            return;
        }

        // Submit button ko temporarily disable karke sending status show kar rahe hain.
        btn.disabled = true;
        btn.textContent = 'Sending...';

        // Form data ko backend request ke liye prepare kar rahe hain.
        const formData = new FormData();
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('message', message);

        // Contact message ko backend endpoint par POST request ke through send kar rahe hain.
        fetch('includes/process-contact.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                // Message successfully send hone par success message, form reset aur replies reload kar rahe hain.
                if (data.success) {
                    showStatus('Message sent! We will get back to you soon.', 'success');
                    form.reset();
                    loadMyReplies();
                // Agar backend login require kare to user ko login page par bhej rahe hain.
                } else if (data.need_login) {
                    window.location.href = 'login.html';
                // Kisi aur backend error ki surat mein returned message show kar rahe hain.
                } else {
                    showStatus(data.msg || 'Failed to send message. Please try again.', 'error');
                }
            })
            // Network/request error ki surat mein error message show kar rahe hain.
            .catch(() => {
                showStatus('Network error. Please try again.', 'error');
            })
            // Request complete hone ke baad button ko dobara enable kar rahe hain.
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Send Message';
            });
    });

    // Contact form ke status message ko display karne ka helper function.
    function showStatus(msg, type) {
        const statusEl = document.getElementById('contact-status');
        if (!statusEl) return;

        // Message text aur visibility set kar rahe hain.
        statusEl.textContent = msg;
        statusEl.style.display = 'block';

        // Success aur error ke liye different text colors apply kar rahe hain.
        statusEl.style.color = type === 'success' ? '#15803d' : '#991b1b';
    }
});

// Contact page login-only hai — agar session nahi hai to login.html bhej do,
// warna user ke purane replies load kar do
function checkLoginAndLoadReplies() {
    // Backend se current user ka login/session status check kar rahe hain.
    fetch('includes/check-session.php')
        .then(res => res.json())
        .then(data => {
            // User logged in nahi hai to login page par redirect kar rahe hain.
            if (!data.logged_in) {
                window.location.href = 'login.html';
                return;
            }

            // Login confirmed hone ke baad user ke replies load kar rahe hain.
            loadMyReplies();
        })
        // Session check fail hone par form ko accessible rehne de rahe hain.
        .catch(() => { /* session check fail ho to form dikhne do, submit pe dobara check hoga */ });
}

// Logged-in user ke previous contact replies load karne ka function.
function loadMyReplies() {
    // Backend se current user ke replies fetch kar rahe hain.
    fetch('includes/get-my-replies.php')
        .then(res => res.json())
        .then(replies => {
            const box = document.getElementById('reply-box');
            if (!box) return;

            // Agar koi reply available nahi hai to reply box hide kar rahe hain.
            if (!replies.length) {
                box.style.display = 'none';
                return;
            }

            // Replies section ka initial heading create kar rahe hain.
            let html = '<h3>Our Replies</h3>';

            // Har user message aur admin reply ko dynamically display kar rahe hain.
            replies.forEach(r => {
                html += `<div style="margin-bottom:14px; padding:12px; background:#f0fdf4; border-radius:8px;">
                    <p style="font-size:13px; color:#555; margin-bottom:6px;"><strong>You asked:</strong> ${escapeHtml(r.message)}</p>
                    <p style="font-size:14px; margin:0;"><strong>Admin:</strong> ${escapeHtml(r.admin_reply)}</p>
                </div>`;
            });

            // Generated replies HTML ko box mein insert karke visible kar rahe hain.
            box.innerHTML = html;
            box.style.display = 'block';
        })
        // Replies load karne mein error aaye to silently ignore kar rahe hain.
        .catch(() => { /* silently ignore */ });
}

// User/admin text ko HTML-safe banane ke liye special characters escape kar raha hai.
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}