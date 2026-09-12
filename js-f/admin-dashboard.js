// ============================================================
//   js-f/admin-dashboard.js — Admin Dashboard Dynamic Logic
//   Yeh file admin dashboard ke statistics, users, orders, aur settings ko dynamically manage karti hai
// ============================================================
// Page par current date set karne ke liye function.
function setDate() {
    // Date display karne wale element ko find kar rahe hain.
    var dateEl = document.getElementById('page-date');
    if (dateEl) {
        // Aaj ki date create kar rahe hain.
        var today = new Date();

        // Date ke display ka format define kar rahe hain.
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };

        // Formatted date ko page par show kar rahe hain.
        dateEl.textContent = today.toLocaleDateString('en-PK', options);
    }
}

// Dashboard ke different sections ko show karne ke liye function.
function showSection(sectionName) {
    // Sabhi sections se active class remove kar rahe hain.
    document.querySelectorAll('.section').forEach(function(sec) {
        sec.classList.remove('active');
    });

    // Selected section ko find kar rahe hain.
    var target = document.getElementById('section-' + sectionName);
    if (target) target.classList.add('active');

    // Har section ke liye page title define kar rahe hain.
    var titles = {
        'dashboard': 'Dashboard', 'users': 'All Users', 'sellers': 'Sellers',
        'buyers': 'Buyers', 'approve-agents': 'Approve Agents',
        'approve-seeds': 'Approve Seeds', 'all-seeds': 'All Seeds',
        'orders': 'All Orders', 'reports': 'Reports', 'complaints': 'Complaints',
        'messages': 'Contact Messages', 'settings': 'System Settings'
    };

    // Page title element ko find karke selected section ka title show kar rahe hain.
    var titleEl = document.getElementById('page-title');
    if (titleEl && titles[sectionName]) titleEl.textContent = titles[sectionName];

    // Sabhi navigation items se active class remove kar rahe hain.
    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.classList.remove('active');
    });

    // Current section ke navigation item ko find kar rahe hain.
    var activeNav = document.querySelector('[data-section="' + sectionName + '"]');
    if (activeNav) activeNav.classList.add('active');

    // Selected section ke according relevant data load kar rahe hain.
    if (sectionName === 'dashboard')      loadStats();
    if (sectionName === 'users')          loadUsers('');
    if (sectionName === 'sellers')        loadUsers('agent');
    if (sectionName === 'buyers')         loadUsers('farmer');
    if (sectionName === 'approve-agents') loadPendingAgents();
    if (sectionName === 'approve-seeds')  loadPendingSeeds();
    if (sectionName === 'all-seeds')      loadAllSeeds();
    if (sectionName === 'orders')         loadOrders();
    if (sectionName === 'messages')       loadMessages();
    if (sectionName === 'settings')       loadSettings();
}

// Dashboard statistics load karne ka function.
function loadStats() {
    // Admin stats endpoint se data fetch kar rahe hain.
    fetch('admin/get-stats.php')
        .then(res => res.json())
        .then(data => {
            // Dashboard ke different statistics update kar rahe hain.
            setEl('stat-farmers',  data.total_farmers  || 0);
            setEl('stat-agents',   data.total_agents   || 0);
            setEl('stat-orders',   data.total_orders   || 0);
            setEl('stat-pending',  data.pending_seeds  || 0);
            setEl('stat-revenue',  'Rs ' + (data.total_revenue || 0));

            // Pending seeds aur agents ke navigation badges update kar rahe hain.
            setBadge(document.querySelector('[data-section="approve-seeds"] .nav-badge'), data.pending_seeds);
            setBadge(document.querySelector('[data-section="approve-agents"] .nav-badge'), data.pending_agents);
        })
        .catch(() => console.log('Stats load failed'));
}

// Users ko role ke according load karne ka function.
function loadUsers(role) {
    // Role available ho to query parameter ke sath URL banate hain.
    var url = role ? 'admin/get-all-users.php?role=' + role : 'admin/get-all-users.php';

    // Role ke according correct table body select karte hain.
    var tbodyId = role === 'agent' ? 'sellers-tbody' : role === 'farmer' ? 'buyers-tbody' : 'users-tbody';

    // Users ka data server se fetch kar rahe hain.
    fetch(url)
        .then(res => res.json())
        .then(users => {
            // Relevant table body find kar rahe hain.
            var tbody = document.getElementById(tbodyId);
            if (!tbody) return;

            // Agar users available nahi hain to message show kar rahe hain.
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No users found</td></tr>';
                return;
            }

            var html = '';

            // Har user ka table row generate kar rahe hain.
            users.forEach(function(user) {
                // User status ke according badge class set kar rahe hain.
                var statusBadge = user.status === 'active' ? 'b-delivered' : user.status === 'blocked' ? 'b-cancelled' : 'b-pending';

                // User role ke according badge class set kar rahe hain.
                var roleBadge   = user.role === 'agent' ? 'seller' : 'buyer';

                // User creation date ko readable format me convert kar rahe hain.
                var date        = new Date(user.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Current status ke according button ka label set kar rahe hain.
                var banLabel    = user.status === 'active' ? 'Block user' : 'Unblock user';

                // User ka complete table row HTML me add kar rahe hain.
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

            // Generated rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Relevant users count element select kar rahe hain.
            var countEl = role === 'agent' ? document.getElementById('sellers-count') :
                          role === 'farmer' ? document.getElementById('buyers-count') :
                          document.getElementById('users-count');

            // Total users count show kar rahe hain.
            if (countEl) countEl.textContent = users.length + ' total';
        })
        .catch(() => console.log('Users load failed'));
}

// User ko block ya unblock karne ka function.
function toggleUserStatus(userId, currentStatus, role) {
    // Current status ke opposite new status set kar rahe hain.
    var newStatus = currentStatus === 'active' ? 'blocked' : 'active';

    // Alert ke liye action ka word set kar rahe hain.
    var actionWord = newStatus === 'blocked' ? 'block' : 'unblock';

    // Admin se confirmation le rahe hain.
    if (!confirm('Are you sure you want to ' + actionWord + ' this user?')) return;

    // Form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', newStatus);

    // User status update karne ke liye backend endpoint call kar rahe hain.
    fetch('admin/update-user-status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Response successful ho to user list reload kar rahe hain.
            if (data.success) {
                showAlert('User ' + actionWord + 'ed!', 'success');
                loadUsers(role);
            } else {
                // Backend error message show kar rahe hain.
                showAlert(data.msg || 'Update failed', 'error');
            }
        })
        .catch(() => showAlert('Update failed', 'error'));
}

// User delete karne ka function.
function deleteUser(userId, role) {
    // Permanent delete se pehle confirmation le rahe hain.
    if (!confirm('Are you sure you want to permanently delete this user? This cannot be undone.')) return;

    // Delete request ke liye form data create kar rahe hain.
    var formData = new FormData();
    formData.append('user_id', userId);

    // Backend ko user delete request send kar rahe hain.
    fetch('admin/delete-user.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Delete successful ho to list reload kar rahe hain.
            if (data.success) {
                showAlert('User deleted!', 'success');
                loadUsers(role);
            } else {
                // Delete failure ka message show kar rahe hain.
                showAlert(data.msg || 'Delete failed', 'error');
            }
        })
        .catch(() => showAlert('Delete failed', 'error'));
}

// Pending agent applications load karne ka function.
function loadPendingAgents() {
    // Pending agents ka data backend se fetch kar rahe hain.
    fetch('admin/get-pending-agents.php')
        .then(res => res.json())
        .then(agents => {
            // Pending agents ki table body find kar rahe hain.
            var tbody = document.getElementById('approve-agents-tbody');
            if (!tbody) return;

            // Agar pending agents nahi hain to message show kar rahe hain.
            if (agents.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No pending agent applications</td></tr>';
                setEl('pending-agents-count', '0 pending');
                return;
            }

            var html = '';

            // Har pending agent ka table row create kar rahe hain.
            agents.forEach(function(agent) {
                // Agent ki creation date ko readable format me convert kar rahe hain.
                var date = new Date(agent.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Agent ki information aur action buttons ka row generate kar rahe hain.
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

            // Generated rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Pending agents ka count show kar rahe hain.
            setEl('pending-agents-count', agents.length + ' pending');
        })
        .catch(() => console.log('Pending agents load failed'));
}

// Agent ko approve ya reject karne ka function.
function approveAgentDB(userId, action, rowId) {
    // Approval/rejection request ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', action);

    // Backend approval endpoint ko request send kar rahe hain.
    fetch('admin/approve-agent.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Action successful hone par selected row remove kar rahe hain.
            if (data.success) {
                var row = document.getElementById(rowId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.3s';

                    // Animation complete hone ke baad row remove kar rahe hain.
                    setTimeout(() => {
                        row.remove();

                        // Remaining pending agents ka count calculate kar rahe hain.
                        var count = document.querySelectorAll('#approve-agents-tbody tr').length;
                        setEl('pending-agents-count', count + ' pending');

                        // Navigation badge ko bhi update kar rahe hain.
                        setBadge(document.querySelector('[data-section="approve-agents"] .nav-badge'), count);
                    }, 300);
                }

                // Action ke according success/rejection alert show kar rahe hain.
                showAlert(action === 'approve' ? 'Agent approved!' : 'Agent rejected.', action === 'approve' ? 'success' : 'error');
            } else {
                // Backend se failure message aaye to show kar rahe hain.
                showAlert(data.msg || 'Action failed', 'error');
            }
        })
        .catch(() => showAlert('Action failed', 'error'));
}

// Pending seeds load karne ka function.
function loadPendingSeeds() {
    // Pending seeds ka data backend se fetch kar rahe hain.
    fetch('admin/get-pending-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            // Pending seeds ki table body find kar rahe hain.
            var tbody = document.getElementById('approve-tbody');
            if (!tbody) return;

            // Agar pending seeds nahi hain to message show kar rahe hain.
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No pending seeds</td></tr>';
                setEl('pending-count', '0 pending');
                return;
            }

            var html = '';

            // Har seed ka table row create kar rahe hain.
            seeds.forEach(function(seed) {
                // Seed ki creation date ko readable format me convert kar rahe hain.
                var date = new Date(seed.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Seed information aur approval buttons ka row generate kar rahe hain.
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

            // Generated seed rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Pending seeds ka count show kar rahe hain.
            setEl('pending-count', seeds.length + ' pending');
        })
        .catch(() => console.log('Seeds load failed'));
}

// Seed ko approve ya reject karne ka function.
function approveSeedDB(productId, action, rowId) {
    // Approval/rejection request ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('product_id', productId);
    formData.append('action', action);

    // Backend seed approval endpoint ko request send kar rahe hain.
    fetch('admin/approve-seed.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Action successful hone par seed row remove kar rahe hain.
            if (data.success) {
                var row = document.getElementById(rowId);
                if (row) {
                    row.style.opacity = '0';
                    row.style.transition = 'opacity 0.3s';

                    // Animation ke baad row remove karke pending count update kar rahe hain.
                    setTimeout(() => { row.remove(); updatePendingCount(); }, 300);
                }

                // Approval ya rejection ka alert show kar rahe hain.
                showAlert(action === 'approved' ? 'Seed approved!' : 'Seed rejected.', action === 'approved' ? 'success' : 'error');
            }
        });
}

// All seeds load karne ka function.
function loadAllSeeds() {
    // All seeds ka data backend se fetch kar rahe hain.
    fetch('admin/get-all-seeds.php')
        .then(res => res.json())
        .then(seeds => {
            // Seeds table body find kar rahe hain.
            var tbody = document.getElementById('seeds-tbody');
            if (!tbody) return;

            // Agar seeds nahi hain to message show kar rahe hain.
            if (seeds.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#888;">No seeds found</td></tr>';
                setEl('seeds-count', '0 seeds');
                return;
            }

            var html = '';

            // Har seed ka table row generate kar rahe hain.
            seeds.forEach(function(seed) {
                // Seed status ke according badge class set kar rahe hain.
                var statusBadge = seed.status === 'approved' ? 'b-delivered' : seed.status === 'rejected' ? 'b-cancelled' : 'b-pending';

                // Seed information ka table row create kar rahe hain.
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

            // Generated rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Total seeds ka count show kar rahe hain.
            setEl('seeds-count', seeds.length + ' seeds');
        })
        .catch(() => console.log('All seeds load failed'));
}

// Orders load karne ka function.
function loadOrders() {
    // All orders ka data backend se fetch kar rahe hain.
    fetch('admin/get-all-orders.php')
        .then(res => res.json())
        .then(orders => {
            // Orders table body find kar rahe hain.
            var tbody = document.getElementById('orders-tbody');
            if (!tbody) return;

            // Agar orders nahi hain to message show kar rahe hain.
            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#888;">No orders found</td></tr>';
                setEl('order-count', '0 orders');
                return;
            }

            // Allowed order statuses define kar rahe hain.
            var statuses = ['placed','confirmed','processing','shipped','delivered','cancelled'];

            var html = '';

            // Har order ka table row generate kar rahe hain.
            orders.forEach(function(order) {
                // Order status ke according CSS class select kar rahe hain.
                var statusClass = {
                    placed: 'b-pending', confirmed: 'b-processing', processing: 'b-processing',
                    shipped: 'b-pending', delivered: 'b-delivered', cancelled: 'b-cancelled'
                }[order.status] || 'b-pending';

                // Order ki creation date ko readable format me convert kar rahe hain.
                var date = new Date(order.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Dropdown ke liye status options generate kar rahe hain.
                var options = statuses.map(function(s) {
                    // Status ka first letter uppercase kar rahe hain.
                    var label = s.charAt(0).toUpperCase() + s.slice(1);

                    // Current status ko selected mark kar rahe hain.
                    var sel = (s === order.status) ? 'selected' : '';

                    // Dropdown option return kar rahe hain.
                    return `<option value="${s}" ${sel}>${label}</option>`;
                }).join('');

                // Check kar rahe hain ke order already paid hai ya nahi.
                var isPaid = order.payment_status === 'Paid';

                // Payment button ka style paid/unpaid status ke according set kar rahe hain.
                var payBtnStyle = isPaid
                    ? 'background:#dcfce7;color:#15803d;border:1px solid #86efac;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:default;'
                    : 'background:#fef9c3;color:#854d0e;border:1px solid #fde68a;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;';

                // Payment button ka label set kar rahe hain.
                var payBtnLabel = isPaid ? '✅ Paid' : '💰 Pay Agent';

                // Paid order ke liye click action empty aur unpaid order ke liye payment function set kar rahe hain.
                var payBtnClick = isPaid ? '' : `onclick="payAgent(${order.id})"`;

                // Order ki complete row generate kar rahe hain.
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

            // Generated order rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Total orders ka count show kar rahe hain.
            setEl('order-count', orders.length + ' orders');
        })
        .catch(() => console.log('Orders load failed'));
}

// Order ka status update karne ka function.
function updateOrderStatus(orderId) {
    // Selected order ka status dropdown find kar rahe hain.
    var select = document.getElementById('status-select-' + orderId);
    if (!select) return;

    // Dropdown se new status le rahe hain.
    var newStatus = select.value;

    // Status update request ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);

    // Backend ko order status update request send kar rahe hain.
    fetch('admin/update-order-status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Update successful ho to orders reload kar rahe hain.
            if (data.success) {
                showAlert('Order status updated!', 'success');
                loadOrders();
            } else {
                // Update failure ka alert show kar rahe hain.
                showAlert('Failed to update order status', 'error');
            }
        })
        .catch(() => showAlert('Failed to update order status', 'error'));
}

// Agent payment mark karne ka function.
function payAgent(orderId) {
    // Payment mark karne se pehle admin se confirmation le rahe hain.
    if (!confirm('Mark this order as Paid? 3% platform commission will be deducted, and the remaining amount will be recorded as agent revenue.')) return;

    // Payment request ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('order_id', orderId);

    // Backend ko agent payment request send kar rahe hain.
    fetch('admin/pay-agent.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Payment successful ho to orders reload kar rahe hain.
            if (data.success) {
                showAlert('Agent payment marked as Paid!', 'success');
                loadOrders();
            } else {
                // Backend error message show kar rahe hain.
                showAlert(data.msg || 'Failed', 'error');
            }
        })
        .catch(() => showAlert('Failed to process payment', 'error'));
}

// Contact messages load karne ka function.
function loadMessages() {
    // Contact messages backend se fetch kar rahe hain.
    fetch('admin/get-contact-messages.php')
        .then(res => res.json())
        .then(messages => {
            // Messages table body find kar rahe hain.
            var tbody = document.getElementById('messages-tbody');
            if (!tbody) return;

            // Agar messages nahi hain to message show kar rahe hain.
            if (messages.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#888;">No messages yet</td></tr>';
                setEl('messages-count', '0 messages');
                return;
            }

            var html = '';
            var unreadCount = 0;

            // Har message ko process kar rahe hain.
            messages.forEach(function(msg) {
                // New messages ka unread count increase kar rahe hain.
                if (msg.status === 'new') unreadCount++;

                // Message status ke according badge class set kar rahe hain.
                var statusBadge = msg.status === 'new' ? 'b-pending' : msg.status === 'replied' ? 'b-delivered' : 'b-processing';

                // Message status ka readable label set kar rahe hain.
                var statusLabel = msg.status === 'new' ? 'New' : msg.status === 'replied' ? 'Replied' : 'Read';

                // Message creation date ko readable format me convert kar rahe hain.
                var date = new Date(msg.created_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });

                // Message ki main table row generate kar rahe hain.
                html += `<tr>
                    <td><strong>${msg.name}</strong></td>
                    <td>${msg.phone}</td>
                    <td style="max-width:300px;">${msg.message}</td>
                    <td>${date}</td>
                    <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                    <td>
                        ${msg.status === 'new' ? `<button class="act-btn approve" onclick="markMessageRead(${msg.id})"><i class="fas fa-check"></i> Mark Read</button>` : ''}
                    </td>
                </tr>`;

                // Jab tak reply nahi hua, isi row ke neeche reply-form dikhao
                // Unreplied message ke liye reply textarea aur send button create kar rahe hain.
                if (msg.status !== 'replied') {
                    html += `<tr id="reply-row-${msg.id}">
                        <td colspan="6" style="background:#f9fafb;">
                            <div style="display:flex;gap:8px;align-items:flex-start;padding:8px 4px;">
                                <textarea id="reply-text-${msg.id}" rows="2" placeholder="Type a reply for ${msg.name}..." style="flex:1;padding:8px;border:1px solid #ddd;border-radius:6px;font-family:inherit;font-size:13px;"></textarea>
                                <button class="act-btn approve" onclick="sendReply(${msg.id})"><i class="fas fa-paper-plane"></i> Send Reply</button>
                            </div>
                        </td>
                    </tr>`;
                }
            });

            // Generated message rows ko table me insert kar rahe hain.
            tbody.innerHTML = html;

            // Total messages count show kar rahe hain.
            setEl('messages-count', messages.length + ' messages');

            // Unread messages ka badge update kar rahe hain.
            setBadge(document.getElementById('unread-messages-badge'), unreadCount);
        })
        .catch(() => console.log('Messages load failed'));
}

// Message ko read mark karne ka function.
function markMessageRead(msgId) {
    // Message ID ke sath form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('msg_id', msgId);

    // Backend ko read status update request send kar rahe hain.
    fetch('admin/mark-message-read.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Successful update ke baad messages reload kar rahe hain.
            if (data.success) {
                loadMessages();
            } else {
                showAlert('Failed to update message', 'error');
            }
        })
        .catch(() => showAlert('Failed to update message', 'error'));
}

// Message ka reply send karne ka function.
function sendReply(msgId) {
    // Relevant textarea find kar rahe hain.
    var textarea = document.getElementById('reply-text-' + msgId);
    if (!textarea) return;

    // Reply text ko trim karke le rahe hain.
    var reply = textarea.value.trim();

    // Empty reply ko prevent kar rahe hain.
    if (!reply) {
        showAlert('Please type a reply first', 'error');
        return;
    }

    // Reply request ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('msg_id', msgId);
    formData.append('reply', reply);

    // Backend ko reply send request kar rahe hain.
    fetch('admin/reply-message.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Reply successful hone par messages reload kar rahe hain.
            if (data.success) {
                showAlert('Reply sent!', 'success');
                loadMessages();
            } else {
                showAlert(data.msg || 'Failed to send reply', 'error');
            }
        })
        .catch(() => showAlert('Failed to send reply', 'error'));
}

// Current admin ka password change karne ka function.
function changeMyPassword() {
    // Password fields se values read kar rahe hain.
    var current = document.getElementById('current-password').value.trim();
    var newPass = document.getElementById('new-password').value.trim();
    var confirm = document.getElementById('confirm-password').value.trim();

    // Check kar rahe hain ke sab fields filled hain.
    if (!current || !newPass || !confirm) {
        showAlert('Please fill all password fields', 'error');
        return;
    }

    // Password update ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('current_password', current);
    formData.append('new_password',     newPass);
    formData.append('confirm_password', confirm);

    // Backend password change endpoint ko request send kar rahe hain.
    fetch('includes/change-password.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Backend response ka message show kar rahe hain.
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

// Platform settings load karne ka function.
function loadSettings() {
    // Settings backend se fetch kar rahe hain.
    fetch('admin/get-settings.php')
        .then(res => res.json())
        .then(data => {
            // Settings ki values input fields me set kar rahe hain.
            setInputVal('settings-platform-name', data.platform_name);
            setInputVal('settings-support-email', data.support_email);
            setInputVal('settings-support-phone', data.support_phone);
        })
        .catch(() => console.log('Settings load failed'));
}

// Platform settings save karne ka function.
function savePlatformSettings() {
    // Settings fields se values read aur trim kar rahe hain.
    var name  = document.getElementById('settings-platform-name').value.trim();
    var email = document.getElementById('settings-support-email').value.trim();
    var phone = document.getElementById('settings-support-phone').value.trim();
    var statusEl = document.getElementById('settings-status');

    // Check kar rahe hain ke sab platform fields filled hain.
    if (!name || !email || !phone) {
        showAlert('Please fill all platform fields', 'error');
        return;
    }

    // Settings update ke liye form data prepare kar rahe hain.
    var formData = new FormData();
    formData.append('platform_name', name);
    formData.append('support_email', email);
    formData.append('support_phone', phone);

    // Backend ko settings save request send kar rahe hain.
    fetch('admin/save-settings.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // Backend response ka status alert show kar rahe hain.
            showAlert(data.msg, data.success ? 'success' : 'error');

            // Settings status element available ho to usme response message show kar rahe hain.
            if (statusEl) {
                statusEl.textContent = data.msg;
                statusEl.style.display = 'block';
                statusEl.style.color = data.success ? '#15803d' : '#991b1b';
            }
        })
        .catch(() => showAlert('Failed to save settings', 'error'));
}

// Input element ki value set karne ka helper function.
function setInputVal(id, val) {
    // Given ID ka input element find kar rahe hain.
    var el = document.getElementById(id);

    // Element aur value available ho to value set kar rahe hain.
    if (el && val !== undefined) el.value = val;
}

// Element ka text content set karne ka helper function.
function setEl(id, val) {
    // Given ID ka element find kar rahe hain.
    var el = document.getElementById(id);

    // Element available ho to text update kar rahe hain.
    if (el) el.textContent = val;
}

// Badge count update karne ka helper function.
function setBadge(el, count) {
    // Element available na ho to function stop kar rahe hain.
    if (!el) return;

    // Agar count zero se zyada hai to badge show kar rahe hain.
    if (count > 0) {
        el.textContent = count;
        el.style.display = 'inline-block';
    } else {
        // Zero count hone par badge hide kar rahe hain.
        el.textContent = '';
        el.style.display = 'none';
    }
}

// Table data ko filter karne ka function.
function filterTable(tbodyId, filterValue, clickedBtn) {
    // Filter buttons me se active class remove kar rahe hain.
    clickedBtn.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));

    // Clicked filter button ko active kar rahe hain.
    clickedBtn.classList.add('active');

    // Table ki sabhi rows ko check kar rahe hain.
    document.querySelectorAll('#' + tbodyId + ' tr').forEach(function(row) {
        // All filter select hone par sab rows show kar rahe hain.
        if (filterValue === 'all') {
            row.style.display = '';
        } else {
            // Row ke filter data ko read kar rahe hain.
            var f = row.getAttribute('data-filter') || '';

            // Matching filter wali rows show aur baqi hide kar rahe hain.
            row.style.display = f.includes(filterValue) ? '' : 'none';
        }
    });
}

// Orders ko status ke according filter karne ka function.
function filterOrders(status, clickedBtn) {
    // Filter buttons me se active class remove kar rahe hain.
    clickedBtn.parentElement.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));

    // Selected filter button ko active kar rahe hain.
    clickedBtn.classList.add('active');

    var count = 0;

    // Orders ki sabhi rows ko check kar rahe hain.
    document.querySelectorAll('#orders-tbody tr').forEach(function(row) {
        // Row ka status read kar rahe hain.
        var s = row.getAttribute('data-status') || '';

        // Matching status wali rows show kar rahe hain aur count increase kar rahe hain.
        if (status === 'all' || s === status) { row.style.display = ''; count++; }
        else row.style.display = 'none';
    });

    // Filtered orders ka count show kar rahe hain.
    setEl('order-count', count + ' orders');
}

// Pending seeds ka count update karne ka function.
function updatePendingCount() {
    // Pending seeds table me remaining rows count kar rahe hain.
    var count = document.querySelectorAll('#approve-tbody tr').length;

    // Pending count text update kar rahe hain.
    setEl('pending-count', count + ' pending');

    // Navigation badge bhi update kar rahe hain.
    setBadge(document.querySelector('[data-section="approve-seeds"] .nav-badge'), count);
}

// Toast alert show karne ka function.
function showAlert(message, type) {
    // Agar pehle se alert maujood hai to usko remove kar rahe hain.
    var existing = document.getElementById('toast-alert');
    if (existing) existing.remove();

    // Naya alert element create kar rahe hain.
    var alert = document.createElement('div');
    alert.id = 'toast-alert';
    alert.textContent = message;

    // Alert ki basic styling set kar rahe hain.
    alert.style.cssText = `position:fixed;bottom:24px;right:24px;padding:12px 20px;border-radius:8px;
        font-size:13px;font-weight:700;font-family:'Spartan',sans-serif;z-index:9999;opacity:1;transition:opacity 0.3s;`;

    // Success aur error ke liye different styling apply kar rahe hain.
    if (type === 'success') {
        alert.style.background = '#dcfce7'; alert.style.color = '#15803d'; alert.style.border = '1px solid #86efac';
    } else {
        alert.style.background = '#fee2e2'; alert.style.color = '#991b1b'; alert.style.border = '1px solid #fca5a5';
    }

    // Alert ko page ke body me add kar rahe hain.
    document.body.appendChild(alert);

    // Kuch seconds baad alert ko fade out karke remove kar rahe hain.
    setTimeout(() => { alert.style.opacity = '0'; setTimeout(() => alert.remove(), 300); }, 2500);
}

// Navigation items ko click events ke sath initialize karne ka function.
function initNavItems() {
    // Sirf woh nav items select kar rahe hain jinke paas data-section attribute hai.
    document.querySelectorAll('.nav-item[data-section]').forEach(function(item) {
        // Har nav item par click event add kar rahe hain.
        item.addEventListener('click', function() {
            // Clicked item ke section ko show kar rahe hain.
            showSection(this.getAttribute('data-section'));
        });
    });
}

// User authentication check karne ka function.
function checkAuth() {
    // Backend se current session aur user role verify kar rahe hain.
    fetch('includes/check-session.php')
        .then(res => res.json())
        .then(data => {
            // Agar user logged in nahi hai ya admin nahi hai to login page par redirect kar rahe hain.
            if (!data.logged_in || data.role !== 'admin') {
                window.location.href = 'login.html';
                return;
            }

            // Authentication successful hone ke baad date set kar rahe hain.
            setDate();

            // Navigation events initialize kar rahe hain.
            initNavItems();

            // Dashboard ko default section ke taur par show kar rahe hain.
            showSection('dashboard');

            // Sidebar badge ko immediately show karne ke liye messages load kar rahe hain.
            loadMessages(); // sidebar badge turant dikh jaye
        })
        .catch(() => {
            // Authentication request fail hone par login page par redirect kar rahe hain.
            window.location.href = 'login.html';
        });
}

// Page completely load hone ke baad authentication check start kar rahe hain.
document.addEventListener('DOMContentLoaded', function() {
    checkAuth();
});