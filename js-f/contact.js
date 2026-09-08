// ============================================================
//   contact.js — Contact form submit handler + replies box
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    checkLoginAndLoadReplies();

    const form = document.getElementById('contactForm');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const name    = document.getElementById('contact-name').value.trim();
        const phone   = document.getElementById('contact-phone').value.trim();
        const message = document.getElementById('contact-message').value.trim();
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
                    loadMyReplies();
                } else if (data.need_login) {
                    window.location.href = 'login.html';
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

// Contact page login-only hai — agar session nahi hai to login.html bhej do,
// warna user ke purane replies load kar do
function checkLoginAndLoadReplies() {
    fetch('includes/check-session.php')
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) {
                window.location.href = 'login.html';
                return;
            }
            loadMyReplies();
        })
        .catch(() => { /* session check fail ho to form dikhne do, submit pe dobara check hoga */ });
}

function loadMyReplies() {
    fetch('includes/get-my-replies.php')
        .then(res => res.json())
        .then(replies => {
            const box = document.getElementById('reply-box');
            if (!box) return;

            if (!replies.length) {
                box.style.display = 'none';
                return;
            }

            let html = '<h3>Our Replies</h3>';
            replies.forEach(r => {
                html += `<div style="margin-bottom:14px; padding:12px; background:#f0fdf4; border-radius:8px;">
                    <p style="font-size:13px; color:#555; margin-bottom:6px;"><strong>You asked:</strong> ${escapeHtml(r.message)}</p>
                    <p style="font-size:14px; margin:0;"><strong>Admin:</strong> ${escapeHtml(r.admin_reply)}</p>
                </div>`;
            });
            box.innerHTML = html;
            box.style.display = 'block';
        })
        .catch(() => { /* silently ignore */ });
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}