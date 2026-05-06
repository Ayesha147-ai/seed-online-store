// Function to toggle between Agent and Admin forms
function toggleFields() {
    const role = document.querySelector('input[name="userRole"]:checked').value;
    const adminFields = document.getElementById('admin-only-fields');
    const agentFields = document.getElementById('agent-only-fields');
    const formTitle = document.getElementById('form-title');

    if (role === 'admin') {
        formTitle.innerText = "Admin Registration Form";
        adminFields.style.display = 'block';
        agentFields.style.display = 'none';
    } else {
        formTitle.innerText = "Agent Registration Form";
        adminFields.style.display = 'none';
        agentFields.style.display = 'block';
    }
}

// CNIC Auto-Format (Aapka logic)
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

// Submit Button Logic
document.getElementById('submitBtn').addEventListener('click', function() {
    const role = document.querySelector('input[name="userRole"]:checked').value;
    const name = document.getElementById('fullName').value;

    if (name.trim() === "") {
        alert("Please enter Full Name");
        return;
    }

    if (role === 'admin') {
        const pass = document.getElementById('adminPass').value;
        if (pass.trim() === "") {
            alert("Please enter Admin Password");
        } else {
            alert("Admin Registration Successful!");
            window.location.href = 'index.html'; 
        }
    } else {
        alert("Agent Application Submitted! We will review it.");
        window.location.href = 'index.html';
    }
});