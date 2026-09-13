// ============================================================
//   register-agent.js
//   Naya design: account upgrade model
//   - Login zaroori hai (existing account use hota hai)
//   - Naya account NAHI banta — sirf agents table mein application save hoti hai
// ============================================================

// Status message show karne aur agent form hide karne ka function.
function showStatusMessage(msg) {
    const el = document.getElementById('status-msg');
    el.textContent = msg;
    el.style.display = 'block';
    document.getElementById('agent-form-wrapper').style.display = 'none';
}

// ===== Pehle check karo login hai ya nahi, aur role kya hai =====
// Backend se current user ka login status aur role check kar rahe hain.
fetch('includes/get-profile.php')
    .then(res => res.json())
    .then(data => {
        // Agar user logged in nahi hai to login message show kar rahe hain.
        if (!data.logged_in) {
            document.getElementById('not-logged-in-msg').style.display = 'block';
            document.getElementById('agent-form-wrapper').style.display = 'none';
            return;
        }

        // Agar user already agent hai to agent registration form show nahi kar rahe.
        if (data.role === 'agent') {
            showStatusMessage('You are already a registered agent! Visit your dashboard.');
            return;
        }

        // Admin ko agent registration se prevent kar rahe hain.
        if (data.role === 'admin') {
            showStatusMessage('Admin accounts cannot register as agents.');
            return;
        }

        // Farmer — form dikhao, account info fill karo
        // Farmer ke existing account ki name aur email form par show kar rahe hain.
        const nameEl  = document.getElementById('applicant-name');
        const emailEl = document.getElementById('applicant-email');
        if (nameEl)  nameEl.textContent  = data.name;
        if (emailEl) emailEl.textContent = data.email;
    })
    .catch(() => {
        // Profile request fail hone par registration form hide kar rahe hain.
        document.getElementById('not-logged-in-msg').style.display = 'block';
        document.getElementById('agent-form-wrapper').style.display = 'none';
    });

// ===== CNIC Auto-Format =====
// CNIC input ko automatically standard format mein convert kar rahe hain.
const cnicInput = document.getElementById('cnic');
if (cnicInput) {
    cnicInput.addEventListener('input', function () {
        // Input mein se sirf numeric characters rakh rahe hain.
        let val = this.value.replace(/[^0-9]/g, '');

        // Pehle 5 digits ke baad dash add kar rahe hain.
        if (val.length > 5 && val.length <= 12) {
            val = val.slice(0, 5) + '-' + val.slice(5);

        // CNIC ke last digit se pehle second dash add kar rahe hain.
        } else if (val.length > 12) {
            val = val.slice(0, 5) + '-' + val.slice(5, 12) + '-' + val.slice(12, 13);
        }

        // Formatted CNIC ko input field mein wapas set kar rahe hain.
        this.value = val;
    });
}

// ===== Submit Button Logic =====
// Agent registration application submit karne ka logic.
document.getElementById('submitBtn').addEventListener('click', function() {
    // Form fields se applicant ki information read kar rahe hain.
    const city     = document.getElementById('city').value.trim();
    const province = document.getElementById('province').value;
    const business = document.getElementById('businessName').value.trim();
    const cnic     = document.getElementById('cnic').value.trim();
    const terms    = document.getElementById('agreeTerms').checked;

    // Required fields validate kar rahe hain.
    if (!city)     { alert('City required.');          return; }
    if (!province) { alert('Province required.');      return; }
    if (!business) { alert('Business name required.'); return; }
    if (!cnic)     { alert('CNIC required.');           return; }
    if (!terms)    { alert('Please agree to Terms.');  return; }

    // Form data ko backend request ke liye prepare kar rahe hain.
    const formData = new FormData();
    formData.append('city',         city);
    formData.append('province',     province);
    formData.append('businessName', business);
    formData.append('cnic',         cnic);

    // Agent registration application backend ko POST request se bhej rahe hain.
    fetch('auth/register-agent.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        // Agar application successfully submit ho jaye to home page par redirect kar rahe hain.
        if (data.success) {
            alert('Application submitted! We will review it shortly.');
            window.location.href = 'index.html';
        } else {
            // Backend se error aaye to error message show kar rahe hain.
            alert('Error: ' + (data.msg || 'Please try again.'));
        }
    })
    .catch(() => alert('Network error. Please try again.'));
});