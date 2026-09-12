// ============================================
//   agent-dashboard.js — FULLY DYNAMIC
// ============================================

// Dashboard ke different sections ke page titles define kar rahe hain.
const pageTitles = {
    'dashboard' : 'Dashboard',
    'my-seeds'  : 'My Seeds',
    'add-seed'  : 'Add New Seed',
    'orders'    : 'Orders',
    'earnings'  : 'Earnings',
    'reviews'   : 'Reviews',
    'settings'  : 'Settings'
};

// ===== DATE =====
// Current date page par show karne ke liye function.
function setDate() {
    // Date ko display karne ke liye required format define kar rahe hain.
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

    // Page par date show karne wala element find kar rahe hain.
    const el = document.getElementById('page-date');

    // Agar element available ho to current date display kar rahe hain.
    if (el) el.textContent = new Date().toLocaleDateString('en-PK', options);
}

// ===== SHOW SECTION =====
// Dashboard ka selected section show karne ka function.
function showSection(sectionId) {
    // Sabhi sections se active class remove kar rahe hain.
    document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));

    // Sabhi navigation items se active class remove kar rahe hain.
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));

    // Selected section ko find kar rahe hain.
    const target = document.getElementById('section-' + sectionId);

    // Selected section ko active bana rahe hain.
    if (target) target.classList.add('active');

    // Selected section ka navigation item find kar rahe hain.
    const navItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);

    // Selected navigation item ko active bana rahe hain.
    if (navItem) navItem.classList.add('active');

    // Page title element ko find kar rahe hain.
    const titleEl = document.getElementById('page-title');

    // Selected section ka title page par show kar rahe hain.
    if (titleEl) titleEl.textContent = pageTitles[sectionId] || 'Dashboard';

    // Seed add success message ka element find kar rahe hain.
    const msg = document.getElementById('seed-success');

    // Add seed section ke ilawa success message ko hide kar rahe hain.
    if (msg && sectionId !== 'add-seed') msg.style.display = 'none';

    // New section par smoothly top par scroll kar rahe hain.
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Selected section ke according relevant data load kar rahe hain.
    if (sectionId === 'dashboard') loadDashboard();
    if (sectionId === 'my-seeds')  loadMySeeds();
    if (sectionId === 'orders')    loadMyOrders();
    if (sectionId === 'earnings')  loadEarnings();
    if (sectionId === 'settings')  loadMyProfile();
}

// ===== LOAD DASHBOARD STATS =====
// Agent dashboard ki statistics aur recent data load karne ka function.
function loadDashboard() {
    // Agent ke seeds backend se fetch kar rahe hain.
    fetch('agent/get-my-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            // Approved seeds ko active seeds ke taur par count kar rahe hain.
            const active  = seeds.filter(s => s.status === 'approved').length;

            // Pending seeds ko separately count kar rahe hain.
            const pending = seeds.filter(s => s.status === 'pending').length;

            // Dashboard par seed statistics show kar rahe hain.
            setEl('stat-active-seeds',  active);
            setEl('stat-pending-seeds', pending);
            setEl('stat-total-seeds',   seeds.length);

            // Recent seeds in dashboard
            // Dashboard ki recent seeds table body find kar rahe hain.
            const tbody = document.getElementById('dash-seeds-tbody');
            if (!tbody) return;

            // Agar koi seed nahi hai to empty message show kar rahe hain.
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;">No seeds yet</td></tr>';
                return;
            }

            let html = '';

            // Sirf pehli 4 seeds dashboard par show kar rahe hain.
            seeds.slice(0, 4).forEach(seed => {
                // Seed status ke according badge class select kar rahe hain.
                const badge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';

                // Seed ka row HTML me add kar rahe hain.
                html += `<tr>
                    <td><strong>${seed.name}</strong></td>
                    <td>Rs ${seed.price}</td>
                    <td>${seed.stock} packs</td>
                    <td><span class="badge ${badge}">${seed.status}</span></td>
                </tr>`;
            });

            // Generated rows ko dashboard table me insert kar rahe hain.
            tbody.innerHTML = html;
        })
        .catch(() => console.log('Dashboard seeds failed'));

    // Agent ke orders backend se fetch kar rahe hain.
    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            // Total orders dashboard statistic me show kar rahe hain.
            setEl('stat-total-orders', orders.length);

            // Placed aur confirmed orders ko pending orders count kar rahe hain.
            const pending = orders.filter(o => o.status === 'placed' || o.status === 'confirmed').length;
            setEl('stat-pending-orders', pending);

            // Dashboard orders table body find kar rahe hain.
            const tbody = document.getElementById('dash-orders-tbody');
            if (!tbody) return;

            // Agar orders nahi hain to empty message show kar rahe hain.
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No orders yet</td></tr>';
                return;
            }

            let html = '';

            // Sirf recent 4 orders dashboard par show kar rahe hain.
            orders.slice(0, 4).forEach(order => {
                // Order status ke according badge class select kar rahe hain.
                const badge = { placed:'b-pending', confirmed:'b-processing', processing:'b-processing', shipped:'b-pending', delivered:'b-delivered', cancelled:'b-cancelled' }[order.status] || 'b-pending';

                // Order ki date ko readable format me convert kar rahe hain.
                const date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Order ki first item ka naam le rahe hain.
                const firstItem = order.items && order.items[0] ? order.items[0].product_name : 'N/A';

                // Order ka row HTML me add kar rahe hain.
                html += `<tr>
                    <td>${order.order_number}</td>
                    <td><strong>${firstItem}</strong></td>
                    <td>${order.farmer_name || 'N/A'}</td>
                    <td><strong>Rs ${order.grand_total}</strong></td>
                    <td><span class="badge ${badge}">${order.status}</span></td>
                    <td>${date}</td>
                </tr>`;
            });

            // Generated order rows ko dashboard table me insert kar rahe hain.
            tbody.innerHTML = html;
        })
        .catch(() => console.log('Dashboard orders failed'));
}

// ===== LOAD MY SEEDS =====
// Agent ke apne seeds load karne ka function.
function loadMySeeds() {
    // Seeds table body ko loading message show karwa rahe hain.
    const tbody = document.getElementById('my-seeds-tbody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">Loading...</td></tr>';

    // Agent ke seeds backend se fetch kar rahe hain.
    fetch('agent/get-my-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            if (!tbody) return;

            // Agar seeds nahi hain to message show kar rahe hain.
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No seeds found. Add your first seed!</td></tr>';
                return;
            }

            let html = '';

            // Har seed ka table row generate kar rahe hain.
            seeds.forEach(seed => {
                // Seed status ke according badge class set kar rahe hain.
                const badge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';

                // Seed information aur action buttons ka row create kar rahe hain.
                html += `<tr>
                    <td><strong>${seed.name}</strong></td>
                    <td>${seed.category_name || 'N/A'}</td>
                    <td>Rs ${seed.price}</td>
                    <td>${seed.stock} packs</td>
                    <td><span class="badge ${badge}">${seed.status}</span></td>
                    <td>
                        <button class="act-btn e" onclick="editSeed(${seed.id}, ${seed.price}, ${seed.stock})"><i class="fas fa-pen"></i></button>
                        <button class="act-btn d" onclick="deleteSeed(${seed.id})"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });

            // Generated seed rows table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Total seeds ka count show kar rahe hain.
            setEl('my-seeds-count', seeds.length + ' seeds');
        })
        .catch(() => {
            // Agar seeds load na ho saken to error message show kar rahe hain.
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red;">Failed to load seeds</td></tr>';
        });
}

// ===== DELETE SEED =====
// Seed delete karne ka function.
function deleteSeed(seedId) {
    // Delete se pehle confirmation le rahe hain.
    if (!confirm('Are you sure you want to delete this seed?')) return;

    // Delete request ke liye form data prepare kar rahe hain.
    const formData = new FormData();
    formData.append('seed_id', seedId);

    // Backend ko seed delete request send kar rahe hain.
    fetch('agent/delete-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Delete successful hone par seed list reload kar rahe hain.
            if (data.success) {
                showAlert('Seed deleted!', 'success');
                loadMySeeds();
            } else {
                // Delete fail hone par error message show kar rahe hain.
                showAlert('Delete failed: ' + (data.msg || 'Error'), 'error');
            }
        });
}

// ===== EDIT SEED =====
// Seed ki price aur stock edit karne ka function.
function editSeed(seedId, price, stock) {
    // New price user se prompt ke through le rahe hain.
    const newPrice = prompt('New Price (Rs):', price);
    if (!newPrice) return;

    // New stock user se prompt ke through le rahe hain.
    const newStock = prompt('New Stock (packs):', stock);
    if (!newStock) return;

    // Update request ke liye form data prepare kar rahe hain.
    const formData = new FormData();
    formData.append('seed_id', seedId);
    formData.append('price',   newPrice);
    formData.append('stock',   newStock);

    // Backend ko seed update request send kar rahe hain.
    fetch('agent/update-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Update successful hone par list reload kar rahe hain.
            if (data.success) {
                showAlert('Seed updated!', 'success');
                loadMySeeds();
            } else {
                // Update fail hone par error alert show kar rahe hain.
                showAlert('Update failed', 'error');
            }
        });
}

// ===== ADD SEED FORM =====
// Add seed form submit handle karne ka function.
function handleAddSeed(event) {
    // Default form submission prevent kar rahe hain.
    event.preventDefault();

    // Form fields se seed information read kar rahe hain.
    const name     = document.getElementById('seed-name').value.trim();
    const category = document.getElementById('seed-category').value;
    const price    = document.getElementById('seed-price').value;
    const stock    = document.getElementById('seed-stock').value;

    // Required fields empty hain to form submit nahi hoga.
    if (!name || !category || !price || !stock) {
        alert('Please fill all required fields.');
        return;
    }

    // Complete add seed form ko find kar rahe hain.
    const form = document.getElementById('add-seed-form');

    // Form ke named fields aur image ko FormData me collect kar rahe hain.
    const formData = new FormData(form); // sab named fields + image yahin se uth jate hain

    // Backend ko new seed add karne ki request send kar rahe hain.
    fetch('agent/add-product.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Seed successfully add ho to success message show kar rahe hain.
            if (data.success) {
                const msg = document.getElementById('seed-success');
                if (msg) msg.style.display = 'flex';

                // Form ko reset kar rahe hain.
                form.reset();

                // Success message tak smoothly scroll kar rahe hain.
                if (msg) msg.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Backend error message show kar rahe hain.
                alert('Error: ' + (data.msg || 'Failed to add seed.'));
            }
        })
        .catch(() => alert('Failed to add seed. Please try again.'));
}

// ===== LOAD MY ORDERS =====
// Agent ke orders load karne ka function.
function loadMyOrders() {
    // Orders table body ko loading message show karwa rahe hain.
    const tbody = document.getElementById('orders-tbody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">Loading...</td></tr>';

    // Agent ke orders backend se fetch kar rahe hain.
    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            if (!tbody) return;

            // Agar orders nahi hain to empty message show kar rahe hain.
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">No orders yet</td></tr>';
                setEl('order-count', '0 orders');
                return;
            }

            let html = '';

            // Har order ka table row generate kar rahe hain.
            orders.forEach(order => {
                // Order status ke according badge class select kar rahe hain.
                const badge = { placed:'b-pending', confirmed:'b-processing', processing:'b-processing', shipped:'b-pending', delivered:'b-delivered', cancelled:'b-cancelled' }[order.status] || 'b-pending';

                // Order date ko readable format me convert kar rahe hain.
                const date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Order ki first item ka naam retrieve kar rahe hain.
                const firstItem = order.items && order.items[0] ? order.items[0].product_name : 'N/A';

                // Order row generate kar rahe hain.
                html += `<tr data-status="${order.status}">
                    <td>${order.order_number}</td>
                    <td><strong>${firstItem}</strong></td>
                    <td>${order.farmer_name || 'N/A'}</td>
                    <td>${order.items ? order.items.reduce((s,i) => s + i.quantity, 0) : 0} packs</td>
                    <td><strong>Rs ${order.grand_total}</strong></td>
                    <td><span class="badge ${badge}">${order.status}</span></td>
                    <td>${date}</td>
                </tr>`;
            });

            // Generated rows ko orders table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Total orders count show kar rahe hain.
            setEl('order-count', orders.length + ' orders');
        })
        .catch(() => {
            // Orders load fail hone par error message show kar rahe hain.
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:red;">Failed to load orders</td></tr>';
        });
}

// ===== LOAD EARNINGS =====
// Agent ki earnings calculate karne ka function.
function loadEarnings() {
    // Agent ke orders fetch kar rahe hain.
    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            // Sirf delivered orders ko earnings ke liye select kar rahe hain.
            const delivered = orders.filter(o => o.status === 'delivered');
            
            // Har order ke grand total ka 97% nikal kar sum kar rahe hain (3% admin commission minus ho gaya)
            const total = delivered.reduce((s, o) => {
                // Order ka grand total numeric value me convert kar rahe hain.
                const grandTotal = parseFloat(o.grand_total || 0);

                // 3% commission minus kar ke 97% agent ko mil raha hai.
                const agentShare = grandTotal * 0.97; // 3% minus kar ke 97% agent ko mil raha hai

                // Agent share ko total earnings me add kar rahe hain.
                return s + agentShare;
            }, 0);

            // Average earning per delivered order calculate kar rahe hain.
            const avg = delivered.length > 0 ? Math.round(total / delivered.length) : 0;

            // Earnings values page par show kar rahe hain.
            setEl('earn-total', 'Rs ' + total.toFixed(0));
            setEl('earn-orders', delivered.length);
            setEl('earn-avg', 'Rs ' + avg);
        })
        .catch(() => console.log('Earnings load failed'));
}

// ===== SETTINGS: LOAD MY PROFILE =====
// Agent ki profile information load karne ka function.
function loadMyProfile() {
    // Profile data backend se fetch kar rahe hain.
    fetch('includes/get-profile.php')
        .then(res => res.json())
        .then(data => {
            // Agar user logged in nahi hai to function stop kar rahe hain.
            if (!data.logged_in) return;

            // Profile fields ke elements find kar rahe hain.
            const nameEl  = document.getElementById('profile-name');
            const emailEl = document.getElementById('profile-email');
            const phoneEl = document.getElementById('profile-phone');

            // Profile values inputs me set kar rahe hain.
            if (nameEl)  nameEl.value  = data.name  || '';
            if (emailEl) emailEl.value = data.email || '';
            if (phoneEl) phoneEl.value = data.phone || '';
        })
        .catch(() => console.log('Profile load failed'));
}

// ===== SETTINGS: SAVE PROFILE (NEW) =====
// Agent ki profile information save karne ka function.
function saveProfile() {
    // Profile fields se current values read kar rahe hain.
    const name  = document.getElementById('profile-name').value.trim();
    const email = document.getElementById('profile-email').value.trim();
    const phone = document.getElementById('profile-phone').value.trim();

    // Name aur email required fields hain.
    if (!name || !email) {
        showAlert('Name and email are required', 'error');
        return;
    }

    // Profile update ke liye form data prepare kar rahe hain.
    const formData = new FormData();
    formData.append('name',  name);
    formData.append('email', email);
    formData.append('phone', phone);

    // Backend ko profile update request send kar rahe hain.
    fetch('includes/update-profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Backend response ke according alert show kar rahe hain.
            showAlert(data.msg, data.success ? 'success' : 'error');
        })
        .catch(() => showAlert('Update failed', 'error'));
}

// ===== SETTINGS: CHANGE PASSWORD (NEW) =====
// Agent ka password change karne ka function.
function changeMyPassword() {
    // Password fields se values read kar rahe hain.
    const current = document.getElementById('current-password').value.trim();
    const newPass = document.getElementById('new-password').value.trim();
    const confirm = document.getElementById('confirm-password').value.trim();

    // Check kar rahe hain ke tamam password fields filled hain.
    if (!current || !newPass || !confirm) {
        showAlert('Please fill all password fields', 'error');
        return;
    }

    // Password update ke liye form data prepare kar rahe hain.
    const formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password',     newPass);
    formData.append('confirm_password', confirm);

    // Backend password change endpoint ko request send kar rahe hain.
    fetch('includes/change-password.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Password update ka response alert me show kar rahe hain.
            showAlert(data.msg, data.success ? 'success' : 'error');

            // Password successfully change ho to fields clear kar rahe hain.
            if (data.success) {
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            }
        })
        .catch(() => showAlert('Password update failed', 'error'));
}

// ===== FILTER ORDERS =====
// Orders ko status ke according filter karne ka function.
function filterOrders(status, btn) {
    // Sabhi filter buttons se active class remove kar rahe hain.
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));

    // Selected filter button ko active kar rahe hain.
    btn.classList.add('active');

    let count = 0;

    // Orders ki tamam rows ko filter kar rahe hain.
    document.querySelectorAll('#orders-tbody tr').forEach(row => {
        // Row ka status read kar rahe hain.
        const s = row.getAttribute('data-status') || '';

        // Matching status wali rows show aur baqi hide kar rahe hain.
        if (status === 'all' || s === status) { row.style.display = ''; count++; }
        else row.style.display = 'none';
    });

    // Filtered orders ka count update kar rahe hain.
    setEl('order-count', count + ' orders');
}

// ===== HELPER =====
// Page ke kisi element ka text update karne ka helper function.
function setEl(id, val) {
    // Given ID ka element find kar rahe hain.
    const el = document.getElementById(id);

    // Element available ho to text content update kar rahe hain.
    if (el) el.textContent = val;
}

// ===== ALERT =====
// Toast alert show karne ka function.
function showAlert(message, type) {
    // Existing toast ko remove kar rahe hain taake duplicate alerts na hon.
    const existing = document.getElementById('toast-alert');
    if (existing) existing.remove();

    // Naya toast element create kar rahe hain.
    const toast = document.createElement('div');
    toast.id = 'toast-alert';
    toast.textContent = message;

    // Toast ki basic styling set kar rahe hain.
    toast.style.cssText = `position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:8px;
        font-size:13px;font-weight:700;font-family:'Spartan',sans-serif;z-index:9999;opacity:1;transition:opacity 0.3s;`;

    // Success aur error ke liye different styling apply kar rahe hain.
    if (type === 'success') {
        toast.style.background = '#dcfce7'; toast.style.color = '#15803d'; toast.style.border = '1px solid #86efac';
    } else {
        toast.style.background = '#fee2e2'; toast.style.color = '#991b1b'; toast.style.border = '1px solid #fca5a5';
    }

    // Toast ko page ke body me add kar rahe hain.
    document.body.appendChild(toast);

    // 2.5 seconds ke baad toast ko fade out karke remove kar rahe hain.
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2500);
}

// ===== SIDEBAR NAV =====
// Sidebar navigation items ko click events ke sath initialize karne ka function.
function initNavItems() {
    // Sirf woh navigation items select kar rahe hain jin me data-section attribute hai.
    document.querySelectorAll('.nav-item[data-section]').forEach(item => {
        // Har navigation item par click event add kar rahe hain.
        item.addEventListener('click', function() {
            // Clicked item ka section show kar rahe hain.
            showSection(this.getAttribute('data-section'));
        });
    });
}

// ===== AUTH CHECK =====
// Agent ki login aur role authentication check karne ka function.
function checkAuth() {
    // Backend se current session check kar rahe hain.
    fetch('includes/check-session.php')
        .then(res => res.json())
        .then(data => {
            // Agar user logged in nahi hai ya role agent nahi hai to login page par redirect kar rahe hain.
            if (!data.logged_in || data.role !== 'agent') {
                window.location.href = 'login.html';
                return;
            }

            // Login confirmed — ab dashboard load karo
            // Authentication successful hone ke baad date set kar rahe hain.
            setDate();

            // Sidebar navigation initialize kar rahe hain.
            initNavItems();

            // Dashboard ko default section ke taur par load kar rahe hain.
            showSection('dashboard');
        })
        .catch(() => {
            // Authentication request fail hone par login page par redirect kar rahe hain.
            window.location.href = 'login.html';
        });
}

// ===== INIT =====
// Page load hone ke baad authentication check start kar rahe hain.
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
});