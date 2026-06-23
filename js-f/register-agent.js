// ============================================================
//   register-agent.js
//   Naya design: account upgrade model
//   - Login zaroori hai (existing account use hota hai)
//   - Naya account NAHI banta — sirf agents table mein application save hoti hai
// ============================================================

function showStatusMessage(msg) {
    const el = document.getElementById('status-msg');
    el.textContent = msg;
    el.style.display = 'block';
    document.getElementById('agent-form-wrapper').style.display = 'none';
}

// ===== Pehle check karo login hai ya nahi, aur role kya hai =====
fetch('includes/get-profile.php')
    .then(res => res.json())
    .then(data => {
        if (!data.logged_in) {
            document.getElementById('not-logged-in-msg').style.display = 'block';
            document.getElementById('agent-form-wrapper').style.display = 'none';
            return;
        }

        if (data.role === 'agent') {
            showStatusMessage('You are already a registered agent! Visit your dashboard.');
            return;
        }

        if (data.role === 'admin') {
            showStatusMessage('Admin accounts cannot register as agents.');
            return;
        }

        // Farmer — form dikhao, account info fill karo
        const nameEl  = document.getElementById('applicant-name');
        const emailEl = document.getElementById('applicant-email');
        if (nameEl)  nameEl.textContent  = data.name;
        if (emailEl) emailEl.textContent = data.email;
    })
    .catch(() => {
        document.getElementById('not-logged-in-msg').style.display = 'block';
        document.getElementById('agent-form-wrapper').style.display = 'none';
    });

// ===== CNIC Auto-Format =====
const cnicInput = document.getElementById('cnic');
if (cnicInput) {
    cnicInput.addEventListener('input', function () {
        let val = this.value.replace(/[^0-9]/g, '');
        if (val.length > 5 && val.length <= 12) {
            val = val.slice(0, 5) + '-' + val.slice(5);
        } else if (val.length > 12) {
            val = val.slice(0, 5) + '-' + val.slice(5, 12) + '-' + val.slice(12, 13);
        }
        this.value = val;
    });
}

// ===== Submit Button Logic =====
document.getElementById('submitBtn').addEventListener('click', function() {
    const city     = document.getElementById('city').value.trim();
    const province = document.getElementById('province').value;
    const business = document.getElementById('businessName').value.trim();
    const cnic     = document.getElementById('cnic').value.trim();
    const terms    = document.getElementById('agreeTerms').checked;

    if (!city)     { alert('City required.');          return; }
    if (!province) { alert('Province required.');      return; }
    if (!business) { alert('Business name required.'); return; }
    if (!cnic)     { alert('CNIC required.');           return; }
    if (!terms)    { alert('Please agree to Terms.');  return; }

    const formData = new FormData();
    formData.append('city',         city);
    formData.append('province',     province);
    formData.append('businessName', business);
    formData.append('cnic',         cnic);

    fetch('auth/register-agent.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Application submitted! We will review it shortly.');
            window.location.href = 'index.html';
        } else {
            alert('Error: ' + (data.msg || 'Please try again.'));
        }
    })
    .catch(() => alert('Network error. Please try again.'));
});