// ============================================
//   agent-dashboard.js — FULLY DYNAMIC
// ============================================

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
function setDate() {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const el = document.getElementById('page-date');
    if (el) el.textContent = new Date().toLocaleDateString('en-PK', options);
}

// ===== SHOW SECTION =====
function showSection(sectionId) {
    document.querySelectorAll('.section').forEach(sec => sec.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));

    const target = document.getElementById('section-' + sectionId);
    if (target) target.classList.add('active');

    const navItem = document.querySelector(`.nav-item[data-section="${sectionId}"]`);
    if (navItem) navItem.classList.add('active');

    const titleEl = document.getElementById('page-title');
    if (titleEl) titleEl.textContent = pageTitles[sectionId] || 'Dashboard';

    const msg = document.getElementById('seed-success');
    if (msg && sectionId !== 'add-seed') msg.style.display = 'none';

    window.scrollTo({ top: 0, behavior: 'smooth' });

    if (sectionId === 'dashboard') loadDashboard();
    if (sectionId === 'my-seeds')  loadMySeeds();
    if (sectionId === 'orders')    loadMyOrders();
    if (sectionId === 'earnings')  loadEarnings();
    if (sectionId === 'settings')  loadMyProfile();
}

// ===== LOAD DASHBOARD STATS =====
function loadDashboard() {
    fetch('agent/get-my-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            const active  = seeds.filter(s => s.status === 'approved').length;
            const pending = seeds.filter(s => s.status === 'pending').length;
            setEl('stat-active-seeds',  active);
            setEl('stat-pending-seeds', pending);
            setEl('stat-total-seeds',   seeds.length);

            // Recent seeds in dashboard
            const tbody = document.getElementById('dash-seeds-tbody');
            if (!tbody) return;
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#888;">No seeds yet</td></tr>';
                return;
            }
            let html = '';
            seeds.slice(0, 4).forEach(seed => {
                const badge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';
                html += `<tr>
                    <td><strong>${seed.name}</strong></td>
                    <td>Rs ${seed.price}</td>
                    <td>${seed.stock} packs</td>
                    <td><span class="badge ${badge}">${seed.status}</span></td>
                </tr>`;
            });
            tbody.innerHTML = html;
        })
        .catch(() => console.log('Dashboard seeds failed'));

    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            setEl('stat-total-orders', orders.length);
            const pending = orders.filter(o => o.status === 'placed' || o.status === 'confirmed').length;
            setEl('stat-pending-orders', pending);

            const tbody = document.getElementById('dash-orders-tbody');
            if (!tbody) return;
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No orders yet</td></tr>';
                return;
            }
            let html = '';
            orders.slice(0, 4).forEach(order => {
                const badge = { placed:'b-pending', confirmed:'b-processing', processing:'b-processing', shipped:'b-pending', delivered:'b-delivered', cancelled:'b-cancelled' }[order.status] || 'b-pending';
                const date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                const firstItem = order.items && order.items[0] ? order.items[0].product_name : 'N/A';
                html += `<tr>
                    <td>${order.order_number}</td>
                    <td><strong>${firstItem}</strong></td>
                    <td>${order.farmer_name || 'N/A'}</td>
                    <td><strong>Rs ${order.grand_total}</strong></td>
                    <td><span class="badge ${badge}">${order.status}</span></td>
                    <td>${date}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        })
        .catch(() => console.log('Dashboard orders failed'));
}

// ===== LOAD MY SEEDS =====
function loadMySeeds() {
    const tbody = document.getElementById('my-seeds-tbody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">Loading...</td></tr>';

    fetch('agent/get-my-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            if (!tbody) return;
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No seeds found. Add your first seed!</td></tr>';
                return;
            }
            let html = '';
            seeds.forEach(seed => {
                const badge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';
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
            tbody.innerHTML = html;
            setEl('my-seeds-count', seeds.length + ' seeds');
        })
        .catch(() => {
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:red;">Failed to load seeds</td></tr>';
        });
}

// ===== DELETE SEED =====
function deleteSeed(seedId) {
    if (!confirm('Are you sure you want to delete this seed?')) return;

    const formData = new FormData();
    formData.append('seed_id', seedId);

    fetch('agent/delete-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Seed deleted!', 'success');
                loadMySeeds();
            } else {
                showAlert('Delete failed: ' + (data.msg || 'Error'), 'error');
            }
        });
}

// ===== EDIT SEED =====
function editSeed(seedId, price, stock) {
    const newPrice = prompt('New Price (Rs):', price);
    if (!newPrice) return;
    const newStock = prompt('New Stock (packs):', stock);
    if (!newStock) return;

    const formData = new FormData();
    formData.append('seed_id', seedId);
    formData.append('price',   newPrice);
    formData.append('stock',   newStock);

    fetch('agent/update-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Seed updated!', 'success');
                loadMySeeds();
            } else {
                showAlert('Update failed', 'error');
            }
        });
}

// ===== ADD SEED FORM =====
function handleAddSeed(event) {
    event.preventDefault();

    const name     = document.getElementById('seed-name').value.trim();
    const category = document.getElementById('seed-category').value;
    const price    = document.getElementById('seed-price').value;
    const stock    = document.getElementById('seed-stock').value;

    if (!name || !category || !price || !stock) {
        alert('Please fill all required fields.');
        return;
    }

    const form = document.getElementById('add-seed-form');
    const formData = new FormData(form); // sab named fields + image yahin se uth jate hain

    fetch('agent/add-product.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const msg = document.getElementById('seed-success');
                if (msg) msg.style.display = 'flex';
                form.reset();
                if (msg) msg.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert('Error: ' + (data.msg || 'Failed to add seed.'));
            }
        })
        .catch(() => alert('Failed to add seed. Please try again.'));
}

// ===== LOAD MY ORDERS =====
function loadMyOrders() {
    const tbody = document.getElementById('orders-tbody');
    if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">Loading...</td></tr>';

    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            if (!tbody) return;
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">No orders yet</td></tr>';
                setEl('order-count', '0 orders');
                return;
            }
            let html = '';
            orders.forEach(order => {
                const badge = { placed:'b-pending', confirmed:'b-processing', processing:'b-processing', shipped:'b-pending', delivered:'b-delivered', cancelled:'b-cancelled' }[order.status] || 'b-pending';
                const date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                const firstItem = order.items && order.items[0] ? order.items[0].product_name : 'N/A';
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
            tbody.innerHTML = html;
            setEl('order-count', orders.length + ' orders');
        })
        .catch(() => {
            if (tbody) tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:red;">Failed to load orders</td></tr>';
        });
}

// ===== LOAD EARNINGS =====
function loadEarnings() {
    fetch('agent/get-my-orders.php')
        .then(res => res.json())
        .then(orders => {
            const delivered = orders.filter(o => o.status === 'delivered');
            const total = delivered.reduce((s, o) => s + parseFloat(o.grand_total || 0), 0);
            const avg   = delivered.length > 0 ? Math.round(total / delivered.length) : 0;

            setEl('earn-total',    'Rs ' + total.toFixed(0));
            setEl('earn-orders',   delivered.length);
            setEl('earn-avg',      'Rs ' + avg);
        })
        .catch(() => console.log('Earnings load failed'));
}

// ===== SETTINGS: LOAD MY PROFILE (NEW) =====
function loadMyProfile() {
    fetch('includes/get-profile.php')
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in) return;
            const nameEl  = document.getElementById('profile-name');
            const emailEl = document.getElementById('profile-email');
            const phoneEl = document.getElementById('profile-phone');
            if (nameEl)  nameEl.value  = data.name  || '';
            if (emailEl) emailEl.value = data.email || '';
            if (phoneEl) phoneEl.value = data.phone || '';
        })
        .catch(() => console.log('Profile load failed'));
}

// ===== SETTINGS: SAVE PROFILE (NEW) =====
function saveProfile() {
    const name  = document.getElementById('profile-name').value.trim();
    const email = document.getElementById('profile-email').value.trim();
    const phone = document.getElementById('profile-phone').value.trim();

    if (!name || !email) {
        showAlert('Name and email are required', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('name',  name);
    formData.append('email', email);
    formData.append('phone', phone);

    fetch('includes/update-profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showAlert(data.msg, data.success ? 'success' : 'error');
        })
        .catch(() => showAlert('Update failed', 'error'));
}

// ===== SETTINGS: CHANGE PASSWORD (NEW) =====
function changeMyPassword() {
    const current = document.getElementById('current-password').value.trim();
    const newPass = document.getElementById('new-password').value.trim();
    const confirm = document.getElementById('confirm-password').value.trim();

    if (!current || !newPass || !confirm) {
        showAlert('Please fill all password fields', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password',     newPass);
    formData.append('confirm_password', confirm);

    fetch('includes/change-password.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showAlert(data.msg, data.success ? 'success' : 'error');
            if (data.success) {
                document.getElementById('current-password').value = '';
                document.getElementById('new-password').value = '';
                document.getElementById('confirm-password').value = '';
            }
        })
        .catch(() => showAlert('Password update failed', 'error'));
}

// ===== FILTER ORDERS =====
function filterOrders(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    let count = 0;
    document.querySelectorAll('#orders-tbody tr').forEach(row => {
        const s = row.getAttribute('data-status') || '';
        if (status === 'all' || s === status) { row.style.display = ''; count++; }
        else row.style.display = 'none';
    });
    setEl('order-count', count + ' orders');
}

// ===== HELPER =====
function setEl(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

// ===== ALERT =====
function showAlert(message, type) {
    const existing = document.getElementById('toast-alert');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'toast-alert';
    toast.textContent = message;
    toast.style.cssText = `position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:8px;
        font-size:13px;font-weight:700;font-family:'Spartan',sans-serif;z-index:9999;opacity:1;transition:opacity 0.3s;`;

    if (type === 'success') {
        toast.style.background = '#dcfce7'; toast.style.color = '#15803d'; toast.style.border = '1px solid #86efac';
    } else {
        toast.style.background = '#fee2e2'; toast.style.color = '#991b1b'; toast.style.border = '1px solid #fca5a5';
    }

    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 2500);
}

// ===== SIDEBAR NAV =====
function initNavItems() {
    document.querySelectorAll('.nav-item[data-section]').forEach(item => {
        item.addEventListener('click', function() {
            showSection(this.getAttribute('data-section'));
        });
    });
}

// ===== INIT =====
document.addEventListener('DOMContentLoaded', function() {
    setDate();
    initNavItems();
    showSection('dashboard');
});