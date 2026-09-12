// ============================================================
//   js-f/myaccount.js — User Account Profile Management Logic
//   Yeh file user ki profile details fetch karne, edit mode enable karne, aur updated data backend par save karne ka kaam karti hai
// ============================================================
// Profile data ko temporarily store karne ke liye empty object.
let profileData = {};

// Backend se logged-in user ka profile data fetch kar rahe hain.
fetch('includes/get-profile.php')
    .then(res => res.json())
    .then(data => {
        // Agar user logged in nahi hai to login page par redirect kar rahe hain.
        if (!data.logged_in) {
            window.location.href = 'login.html';
            return;
        }

        // Backend se milne wala profile data save kar rahe hain.
        profileData = data;

        // Profile ki basic information page par display kar rahe hain.
        document.getElementById('acc-name').textContent = data.name;
        document.getElementById('acc-role').textContent = data.role;
        document.getElementById('acc-name-val').textContent = data.name;
        document.getElementById('acc-email').textContent = data.email;
        document.getElementById('acc-phone').textContent = data.phone || '—';
        document.getElementById('acc-location').textContent = data.location || '—';
        document.getElementById('acc-since').textContent = data.member_since;

        // Loading message hide karke account card show kar rahe hain.
        document.getElementById('loading').style.display = 'none';
        document.getElementById('account-card').style.display = 'block';
    })
    .catch(() => window.location.href = 'login.html');

// Profile editing mode enable karne ka function.
function enableEdit() {
    // Existing profile data ko edit fields mein fill kar rahe hain.
    document.getElementById('acc-name-input').value  = profileData.name || '';
    document.getElementById('acc-email-input').value = profileData.email || '';
    document.getElementById('acc-phone-input').value = profileData.phone || '';

    // Account body par edit mode class add kar rahe hain.
    document.getElementById('account-body').classList.add('edit-mode');

    // Edit button hide aur save/cancel buttons show kar rahe hain.
    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'block';
    document.getElementById('cancelBtn').style.display = 'block';
}

// Editing cancel karke normal profile view par wapas la rahe hain.
function cancelEdit() {
    // Edit mode remove kar rahe hain.
    document.getElementById('account-body').classList.remove('edit-mode');

    // Buttons ko original state mein la rahe hain.
    document.getElementById('editBtn').style.display = 'block';
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';

    // Previous message clear kar rahe hain.
    document.getElementById('msgBox').textContent = '';
}

// Updated profile ko backend par save karne ka function.
function saveProfile() {
    // Input fields se updated values le rahe hain aur extra spaces remove kar rahe hain.
    const name  = document.getElementById('acc-name-input').value.trim();
    const email = document.getElementById('acc-email-input').value.trim();
    const phone = document.getElementById('acc-phone-input').value.trim();

    // Name aur email required fields hain, isliye empty hone par save stop kar rahe hain.
    if (!name || !email) {
        document.getElementById('msgBox').style.color = '#dc2626';
        document.getElementById('msgBox').textContent = 'Name and Email are required.';
        return;
    }

    // Updated profile data ko FormData mein prepare kar rahe hain.
    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);

    // Updated profile data backend endpoint ko POST request se bhej rahe hain.
    fetch('includes/update-profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Message display karne wala element select kar rahe hain.
            const msgBox = document.getElementById('msgBox');

            // Agar profile successfully update ho gaya hai.
            if (data.success) {
                // Success message show kar rahe hain.
                msgBox.style.color = '#16a34a';
                msgBox.textContent = data.msg;

                // Local profile data ko bhi updated values se sync kar rahe hain.
                profileData.name  = name;
                profileData.email = email;
                profileData.phone = phone;

                // Page par updated profile information immediately show kar rahe hain.
                document.getElementById('acc-name').textContent = name;
                document.getElementById('acc-name-val').textContent = name;
                document.getElementById('acc-email').textContent = email;
                document.getElementById('acc-phone').textContent = phone || '—';

                // Save ke baad edit mode close kar rahe hain.
                cancelEdit();
            } else {
                // Backend se error aaye to error message show kar rahe hain.
                msgBox.style.color = '#dc2626';
                msgBox.textContent = data.msg;
            }
        })
        .catch(() => {
            // Network ya request error hone par error message show kar rahe hain.
            document.getElementById('msgBox').style.color = '#dc2626';
            document.getElementById('msgBox').textContent = 'Something went wrong. Try again.';
        });
}