// ============================================
//   FORGOT PASSWORD — JavaScript (Updated)
// ============================================

let userEmail = '';

// ===== STEP 1: Send Code via PHP =====
function sendCode() {
    const emailInput = document.getElementById('email-input');
    const emailError = document.getElementById('email-error');
    const email = emailInput.value.trim();

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        emailError.style.display = 'flex';
        emailInput.parentElement.style.borderColor = '#dc2626';
        return;
    }

    emailError.style.display = 'none';
    emailInput.parentElement.style.borderColor = '#cce7d0';
    userEmail = email;

    const formData = new FormData();
    formData.append('email', email);

    // Path points to includes folder
    fetch('includes/send-otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('email-display').textContent = email;

            // Demo OTP ab yahan persistently dikhega — koi timeout nahi,
            // jab tak step badal na jaye ya dobara code bheja na jaye
            const demoDisplay = document.getElementById('demo-otp-display');
            if (demoDisplay) {
                demoDisplay.textContent = 'Demo OTP: ' + data.demo_otp;
            }
            console.log('Demo OTP:', data.demo_otp);

            showStep('step-otp');

            setTimeout(() => {
                document.querySelectorAll('.otp-box')[0].focus();
            }, 100);
        } else {
            emailError.style.display = 'flex';
            emailError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Something went wrong!');
    });
}

// ===== OTP BOXES EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', () => {
    const otpBoxes = document.querySelectorAll('.otp-box');

    otpBoxes.forEach((box, index) => {
        box.addEventListener('input', (e) => {
            box.value = box.value.replace(/[^0-9]/g, '');

            if (box.value) {
                box.classList.add('filled');
                if (index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            } else {
                box.classList.remove('filled');
            }
        });

        box.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !box.value && index > 0) {
                otpBoxes[index - 1].focus();
                otpBoxes[index - 1].value = '';
                otpBoxes[index - 1].classList.remove('filled');
            }
        });

        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            pasted.split('').forEach((char, i) => {
                if (otpBoxes[i]) {
                    otpBoxes[i].value = char;
                    otpBoxes[i].classList.add('filled');
                }
            });
            otpBoxes[Math.min(pasted.length, 5)].focus();
        });
    });

    const newPassInput = document.getElementById('new-pass');
    if (newPassInput) {
        newPassInput.addEventListener('input', checkStrength);
    }
});

// ===== STEP 2: Verify OTP via PHP =====
function verifyOtp() {
    const otpBoxes = document.querySelectorAll('.otp-box');
    const enteredOtp = Array.from(otpBoxes).map(b => b.value).join('');
    const otpError = document.getElementById('otp-error');

    if (enteredOtp.length < 6) {
        otpError.style.display = 'flex';
        otpError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter all 6 digits.';
        return;
    }

    const formData = new FormData();
    formData.append('email', userEmail);
    formData.append('otp', enteredOtp);

    fetch('includes/verify-otp.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            otpError.style.display = 'none';
            showStep('step-newpass');
            setTimeout(() => {
                document.getElementById('new-pass').focus();
            }, 100);
        } else {
            otpError.style.display = 'flex';
            otpError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
            otpBoxes.forEach(b => {
                b.style.borderColor = '#dc2626';
                b.classList.remove('filled');
            });
        }
    });
}

// ===== Resend Code =====
function resendCode() {
    sendCode();
    document.querySelectorAll('.otp-box').forEach(b => {
        b.value = '';
        b.classList.remove('filled');
        b.style.borderColor = '#cce7d0';
    });
    document.getElementById('otp-error').style.display = 'none';
}

// ===== Password Strength Checker =====
function checkStrength() {
    const pass = document.getElementById('new-pass').value;
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');

    let score = 0;
    if (pass.length >= 8)          score++;
    if (/[A-Z]/.test(pass))        score++;
    if (/[0-9]/.test(pass))        score++;
    if (/[^A-Za-z0-9]/.test(pass)) score++;

    const levels = [
        { width: '0%',   color: '#eee',    label: '' },
        { width: '25%',  color: '#dc2626', label: 'Weak' },
        { width: '50%',  color: '#f59e0b', label: 'Fair' },
        { width: '75%',  color: '#22c55e', label: 'Good' },
        { width: '100%', color: '#16a34a', label: 'Strong' },
    ];

    const level = levels[score];
    bar.style.width = level.width;
    bar.style.background = level.color;
    text.textContent = level.label;
    text.style.color = level.color;
}

// ===== STEP 3: Reset Password via PHP =====
function resetPassword() {
    const newPass     = document.getElementById('new-pass').value;
    const confirmPass = document.getElementById('confirm-pass').value;
    const passError   = document.getElementById('pass-error');

    if (newPass.length < 8) {
        passError.style.display = 'flex';
        passError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Password must be at least 8 characters.';
        return;
    }

    if (newPass !== confirmPass) {
        passError.style.display = 'flex';
        passError.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match.';
        return;
    }

    passError.style.display = 'none';

    const formData = new FormData();
    formData.append('email', userEmail);
    formData.append('password', newPass);

    fetch('includes/reset-password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showStep('step-success');
        } else {
            passError.style.display = 'flex';
            passError.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.message}`;
        }
    });
}

function showStep(stepId) {
    const steps = ['step-email', 'step-otp', 'step-newpass', 'step-success'];
    steps.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.style.display = (id === stepId) ? 'block' : 'none';
    });
}

function goBack(stepId) {
    showStep(stepId);
}

function togglePass(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function showToast(message) {
    const existing = document.getElementById('ts-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'ts-toast';
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        background: #16a34a;
        color: #fff;
        padding: 12px 28px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 600;
        font-family: 'Spartan', sans-serif;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 9999;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.remove(), 3000);
}