let profileData = {};

fetch('includes/get-profile.php')
    .then(res => res.json())
    .then(data => {
        if (!data.logged_in) {
            window.location.href = 'login.html';
            return;
        }
        profileData = data;

        document.getElementById('acc-name').textContent = data.name;
        document.getElementById('acc-role').textContent = data.role;
        document.getElementById('acc-name-val').textContent = data.name;
        document.getElementById('acc-email').textContent = data.email;
        document.getElementById('acc-phone').textContent = data.phone || '—';
        document.getElementById('acc-location').textContent = data.location || '—';
        document.getElementById('acc-since').textContent = data.member_since;

        document.getElementById('loading').style.display = 'none';
        document.getElementById('account-card').style.display = 'block';
    })
    .catch(() => window.location.href = 'login.html');

function enableEdit() {
    document.getElementById('acc-name-input').value  = profileData.name || '';
    document.getElementById('acc-email-input').value = profileData.email || '';
    document.getElementById('acc-phone-input').value = profileData.phone || '';

    document.getElementById('account-body').classList.add('edit-mode');
    document.getElementById('editBtn').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'block';
    document.getElementById('cancelBtn').style.display = 'block';
}

function cancelEdit() {
    document.getElementById('account-body').classList.remove('edit-mode');
    document.getElementById('editBtn').style.display = 'block';
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('cancelBtn').style.display = 'none';
    document.getElementById('msgBox').textContent = '';
}

function saveProfile() {
    const name  = document.getElementById('acc-name-input').value.trim();
    const email = document.getElementById('acc-email-input').value.trim();
    const phone = document.getElementById('acc-phone-input').value.trim();

    if (!name || !email) {
        document.getElementById('msgBox').style.color = '#dc2626';
        document.getElementById('msgBox').textContent = 'Name and Email are required.';
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);

    fetch('includes/update-profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            const msgBox = document.getElementById('msgBox');
            if (data.success) {
                msgBox.style.color = '#16a34a';
                msgBox.textContent = data.msg;

                profileData.name  = name;
                profileData.email = email;
                profileData.phone = phone;

                document.getElementById('acc-name').textContent = name;
                document.getElementById('acc-name-val').textContent = name;
                document.getElementById('acc-email').textContent = email;
                document.getElementById('acc-phone').textContent = phone || '—';

                cancelEdit();
            } else {
                msgBox.style.color = '#dc2626';
                msgBox.textContent = data.msg;
            }
        })
        .catch(() => {
            document.getElementById('msgBox').style.color = '#dc2626';
            document.getElementById('msgBox').textContent = 'Something went wrong. Try again.';
        });
}