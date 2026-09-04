// ============================================
//   admin-dashboard.js — FULLY DYNAMIC
// ============================================

function setDate() {
    var dateEl = document.getElementById('page-date');
    if (dateEl) {
        var today = new Date();
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateEl.textContent = today.toLocaleDateString('en-PK', options);
    }
}

function showSection(sectionName) {
    document.querySelectorAll('.section').forEach(function(sec) {
        sec.classList.remove('active');
    });
    var target = document.getElementById('section-' + sectionName);
    if (target) target.classList.add('active');

    var titles = {
        'dashboard': 'Dashboard', 'users': 'All Users', 'sellers': 'Sellers',
        'buyers': 'Buyers', 'approve-agents': 'Approve Agents',
        'approve-seeds': 'Approve Seeds', 'all-seeds': 'All Seeds',
        'orders': 'All Orders', 'reports': 'Reports', 'complaints': 'Complaints',
        'settings': 'System Settings'
    };
    var titleEl = document.getElementById('page-title');
    if (titleEl && titles[sectionName]) titleEl.textContent = titles[sectionName];

    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.classList.remove('active');
    });
    var activeNav = document.querySelector('[data-section="' + sectionName + '"]');
    if (activeNav) activeNav.classList.add('active');

    if (sectionName === 'dashboard')      loadStats();
    if (sectionName === 'users')          loadUsers('');
    if (sectionName === 'sellers')        loadUsers('agent');
    if (sectionName === 'buyers')         loadUsers('farmer');
    if (sectionName === 'approve-agents') loadPendingAgents();
    if (sectionName === 'approve-seeds')  loadPendingSeeds();
    if (sectionName === 'all-seeds')      loadAllSeeds();
    if (sectionName === 'orders')         loadOrders();
}

function loadStats() {
    fetch('admin/get-stats.php')
        .then(res => res.json())
        .then(data => {
            setEl('stat-farmers',  data.total_farmers  || 0);
            setEl('stat-agents',   data.total_agents   || 0);
            setEl('stat-orders',   data.total_orders   || 0);
            setEl('stat-pending',  data.pending_seeds  || 0);
            setEl('stat-revenue',  'Rs ' + (data.total_revenue || 0));

            setBadge(document.querySelector('[data-section="approve-seeds"] .nav-badge'), data.pending_seeds);
            setBadge(document.querySelector('[data-section="approve-agents"] .nav-badge'), data.pending_agents);
        })
        .catch(() => console.log('Stats load failed'));
}

function loadUsers(role) {
    var url = role ? 'admin/get-all-users.php?role=' + role : 'admin/get-all-users.php';
    var tbodyId = role === 'agent' ? 'sellers-tbody' : role === 'farmer' ? 'buyers-tbody' : 'users-tbody';

    fetch(url)
        .then(res => res.json())
        .then(users => {
            var tbody = document.getElementById(tbodyId);
            if (!tbody) return;

            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No users found</td></tr>';
                return;
            }

            var html = '';
            users.forEach(function(user) {
                var statusBadge = user.status === 'active' ? 'b-delivered' : user.status === 'blocked' ? 'b-cancelled' : 'b-pending';
                var roleBadge   = user.role === 'agent' ? 'seller' : 'buyer';
                var date        = new Date(user.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                var banLabel    = user.status === 'active' ? 'Block user' : 'Unblock user';

                html += `<tr data-filter="${user.role} ${user.status}">
                    <td><strong>${user.name}</strong></td>
                    <td>${user.email}</td>
                    <td><span class="role-badge ${roleBadge}">${user.role}</span></td>
                    <td>${date}</td>
                    <td><span class="badge ${statusBadge}">${user.status}</span></td>
                    <td>
                        <button class="act-btn warn" title="${banLabel}" onclick="toggleUserStatus(${user.id}, '${user.status}', '${role}')">
                            <i class="fas fa-ban"></i>
                        </button>
                        <button class="act-btn d" title="Delete user" onclick="deleteUser(${user.id}, '${role}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;

            var countEl = role === 'agent' ? document.getElementById('sellers-count') :
                          role === 'farmer' ? document.getElementById('buyers-count') :
                          document.getElementById('users-count');
            if (countEl) countEl.textContent = users.length + ' total';
        })
        .catch(() => console.log('Users load failed'));
}

function toggleUserStatus(userId, currentStatus, role) {
    var newStatus = currentStatus === 'active' ? 'blocked' : 'active';
    var actionWord = newStatus === 'blocked' ? 'block' : 'unblock';
    if (!confirm('Are you sure you want to ' + actionWord + ' this user?')) return;

    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', newStatus);

    fetch('admin/update-user-status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('User ' + actionWord + 'ed!', 'success');
                loadUsers(role);
            } else {
                showAlert(data.msg || 'Update failed', 'error');
            }
        })
        .catch(() => showAlert('Update failed', 'error'));
}

function deleteUser(userId, role) {
    if (!confirm('Are you sure you want to permanently delete this user? This cannot be undone.')) return;

    var formData = new FormData();
    formData.append('user_id', userId);

    fetch('admin/delete-user.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('User deleted!', 'success');
                loadUsers(role);
            } else {
                showAlert(data.msg || 'Delete failed', 'error');
            }
        })
        .catch(() => showAlert('Delete failed', 'error'));
}

function loadPendingAgents() {
    fetch('admin/get-pending-agents.php')
        .then(res => res.json())
        .then(agents => {
            var tbody = document.getElementById('approve-agents-tbody');
            if (!tbody) return;

            if (agents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No pending agent applications</td></tr>';
                setEl('pending-agents-count', '0 pending');
                return;
            }

            var html = '';
            agents.forEach(function(agent) {
                var date = new Date(agent.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                html += `<tr id="agent-row-${agent.id}">
                    <td><strong>${agent.name}</strong></td>
                    <td>${agent.agency_name || 'N/A'}</td>
                    <td>${agent.city}, ${agent.province}</td>
                    <td>${agent.cnic || 'N/A'}</td>
                    <td>${date}</td>
                    <td>
                        <button class="act-btn approve" onclick="approveAgentDB(${agent.id}, 'approve', 'agent-row-${agent.id}')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="act-btn d" onclick="approveAgentDB(${agent.id}, 'reject', 'agent-row-${agent.id}')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            setEl('pending-agents-count', agents.length + ' pending');
        })
        .catch(() => console.log('Pending agents load failed'));
}

function approveAgentDB(userId, action, rowId) {
    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', action);

    fetch('admin/approve-agent.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                var row = document.getElementById(rowId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.3s';
                    setTimeout(() => {
                        row.remove();
                        var count = document.querySelectorAll('#approve-agents-tbody tr').length;
                        setEl('pending-agents-count', count + ' pending');
                        setBadge(document.querySelector('[data-section="approve-agents"] .nav-badge'), count);
                    }, 300);
                }
                showAlert(action === 'approve' ? 'Agent approved!' : 'Agent rejected.', action === 'approve' ? 'success' : 'error');
            } else {
                showAlert(data.msg || 'Action failed', 'error');
            }
        })
        .catch(() => showAlert('Action failed', 'error'));
}

function loadPendingSeeds() {
    fetch('admin/get-pending-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            var tbody = document.getElementById('approve-tbody');
            if (!tbody) return;

            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No pending seeds</td></tr>';
                setEl('pending-count', '0 pending');
                return;
            }

            var html = '';
            seeds.forEach(function(seed) {
                var date = new Date(seed.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
                html += `<tr id="seed-row-${seed.id}">
                    <td><strong>${seed.name}</strong></td>
                    <td>${seed.agent_name}</td>
                    <td>${seed.category_name}</td>
                    <td>Rs ${seed.price}</td>
                    <td>${date}</td>
                    <td>
                        <button class="act-btn approve" onclick="approveSeedDB(${seed.id}, 'approved', 'seed-row-${seed.id}')">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button class="act-btn d" onclick="approveSeedDB(${seed.id}, 'rejected', 'seed-row-${seed.id}')">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            setEl('pending-count', seeds.length + ' pending');
        })
        .catch(() => console.log('Seeds load failed'));
}

function approveSeedDB(productId, action, rowId) {
    var formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', action);

    fetch('admin/approve-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                var row = document.getElementById(rowId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.3s';
                    setTimeout(() => { row.remove(); updatePendingCount(); }, 300);
                }
                showAlert(action === 'approved' ? 'Seed approved!' : 'Seed rejected.', action === 'approved' ? 'success' : 'error');
            }
        });
}

function loadAllSeeds() {
    fetch('admin/get-all-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            var tbody = document.getElementById('seeds-tbody');
            if (!tbody) return;

            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">No seeds found</td></tr>';
                setEl('seeds-count', '0 seeds');
                return;
            }

            var html = '';
            seeds.forEach(function(seed) {
                var statusBadge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';
                html += `<tr data-filter="${seed.category_name}">
                    <td><strong>${seed.name}</strong></td>
                    <td>${seed.agent_name}</td>
                    <td>${seed.category_name}</td>
                    <td>Rs ${seed.price}</td>
                    <td>${seed.stock} packs</td>
                    <td><span class="badge ${statusBadge}">${seed.status}</span></td>
                    <td>
                        <button class="act-btn e"><i class="fas fa-eye"></i></button>
                        <button class="act-btn d"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            setEl('seeds-count', seeds.length + ' seeds');
        })
        .catch(() => console.log('All seeds load failed'));
}

function loadOrders() {
    fetch('admin/get-all-orders.php')
        .then(res => res.json())
        .then(orders => {
            var tbody = document.getElementById('orders-tbody');
            if (!tbody) return;

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#888;">No orders found</td></tr>';
                setEl('order-count', '0 orders');
                return;
            }

            var statuses = ['placed','confirmed','processing','shipped','delivered','cancelled'];

            var html = '';
            orders.forEach(function(order) {
                var statusClass = {
                    placed: 'b-pending', confirmed: 'b-processing', processing: 'b-processing',
                    shipped: 'b-pending', delivered: 'b-delivered', cancelled: 'b-cancelled'
                }[order.status] || 'b-pending';

                var date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                var options = statuses.map(function(s) {
                    var label = s.charAt(0).toUpperCase() + s.slice(1);
                    var sel = (s === order.status) ? 'selected' : '';
                    return `<option value="${s}" ${sel}>${label}</option>`;
                }).join('');

                var isPaid = order.payment_status === 'Paid';
                var payBtnStyle = isPaid
                    ? 'background:#dcfce7;color:#15803d;border:1px solid #86efac;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:default;'
                    : 'background:#fef9c3;color:#854d0e;border:1px solid #fde68a;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;';
                var payBtnLabel = isPaid ? '✅ Paid' : '💰 Pay Agent';
                var payBtnClick = isPaid ? '' : `onclick="payAgent(${order.id})"`;

                html += `<tr data-status="${order.status}">
                    <td>${order.order_number}</td>
                    <td><strong>${order.farmer_name || 'N/A'}</strong></td>
                    <td>${order.city || 'N/A'}</td>
                    <td>${order.payment_method ? order.payment_method.toUpperCase() : 'N/A'}</td>
                    <td><strong>Rs ${order.grand_total}</strong></td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                    <td>${date}</td>
                    <td>
                        <button style="${payBtnStyle}" ${payBtnClick}>${payBtnLabel}</button>
                    </td>
                    <td>
                        <select id="status-select-${order.id}" style="padding:4px;border-radius:4px;font-size:12px;margin-right:4px;">
                            ${options}
                        </select>
                        <button class="act-btn e" onclick="updateOrderStatus(${order.id})"><i class="fas fa-check"></i></button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
            setEl('order-count', orders.length + ' orders');
        })
        .catch(() => console.log('Orders load failed'));
}

function updateOrderStatus(orderId) {
    var select = document.getElementById('status-select-' + orderId);
    if (!select) return;
    var newStatus = select.value;

    var formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);

    fetch('admin/update-order-status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Order status updated!', 'success');
                loadOrders();
            } else {
                showAlert('Failed to update order status', 'error');
            }
        })
        .catch(() => showAlert('Failed to update order status', 'error'));
}

function payAgent(orderId) {
    if (!confirm('Mark this order as Paid? 3% platform commission will be deducted, and the remaining amount will be recorded as agent revenue.')) return;

    var formData = new FormData();
    formData.append('order_id', orderId);

    fetch('admin/pay-agent.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showAlert('Agent payment marked as Paid!', 'success');
                loadOrders();
            } else {
                showAlert(data.msg || 'Failed', 'error');
            }
        })
        .catch(() => showAlert('Failed to process payment', 'error'));
}

function changeMyPassword() {
    var current = document.getElementById('current-password').value.trim();
    var newPass = document.getElementById('new-password').value.trim();
    var confirm = document.getElementById('confirm-password').value.trim();

    if (!current || !newPass || !confirm) {
        showAlert('Please fill all password fields', 'error');
        return;
    }

    var formData = new FormData();
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

function setEl(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
}

function setBadge(el, count) {
    if (!el) return;
    if (count > 0) {
        el.textContent = count;
        el.style.display = 'inline-block';
    } else {
        el.textContent = '';
        el.style.display = 'none';
    }
}

function filterTable(tbodyId, filterValue, clickedBtn) {
    clickedBtn.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    clickedBtn.classList.add('active');

    document.querySelectorAll('#' + tbodyId + ' tr').forEach(function(row) {
        if (filterValue === 'all') {
            row.style.display = '';
        } else {
            var f = row.getAttribute('data-filter') || '';
            row.style.display = f.includes(filterValue) ? '' : 'none';
        }
    });
}

function filterOrders(status, clickedBtn) {
    clickedBtn.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    clickedBtn.classList.add('active');

    var count = 0;
    document.querySelectorAll('#orders-tbody tr').forEach(function(row) {
        var s = row.getAttribute('data-status') || '';
        if (status === 'all' || s === status) { row.style.display = ''; count++; }
        else row.style.display = 'none';
    });
    setEl('order-count', count + ' orders');
}

function updatePendingCount() {
    var count = document.querySelectorAll('#approve-tbody tr').length;
    setEl('pending-count', count + ' pending');
    setBadge(document.querySelector('[data-section="approve-seeds"] .nav-badge'), count);
}

function showAlert(message, type) {
    var existing = document.getElementById('toast-alert');
    if (existing) existing.remove();

    var alert = document.createElement('div');
    alert.id = 'toast-alert';
    alert.textContent = message;
    alert.style.cssText = `position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:8px;
        font-size:13px;font-weight:700;font-family:'Spartan',sans-serif;z-index:9999;opacity:1;transition:opacity 0.3s;`;

    if (type === 'success') {
        alert.style.background = '#dcfce7'; alert.style.color = '#15803d'; alert.style.border = '1px solid #86efac';
    } else {
        alert.style.background = '#fee2e2'; alert.style.color = '#991b1b'; alert.style.border = '1px solid #fca5a5';
    }

    document.body.appendChild(alert);
    setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 300); }, 2500);
}

function initNavItems() {
    document.querySelectorAll('.nav-item[data-section]').forEach(function(item) {
        item.addEventListener('click', function() {
            showSection(this.getAttribute('data-section'));
        });
    });
}

function checkAuth() {
    fetch('includes/check-session.php')
        .then(res => res.json())
        .then(data => {
            if (!data.logged_in || data.role !== 'admin') {
                window.location.href = 'login.html';
                return;
            }
            setDate();
            initNavItems();
            showSection('dashboard');
        })
        .catch(() => {
            window.location.href = 'login.html';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
});