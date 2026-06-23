// ============================================================
//   order-track.js — Updated: DB se order track karo
// ============================================================

async function trackOrder() {
    const input   = document.getElementById('order-input').value.trim();
    const errorEl = document.getElementById('search-error');
    const resultSection = document.getElementById('result-section');

    if (!input) {
        errorEl.style.display = 'flex';
        errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter an Order ID.';
        return;
    }

    // Normalize
    const orderId = input.startsWith('#') ? input : '#' + input;

    errorEl.style.display = 'none';

    try {
        // First try DB
       const res  = await fetch('orders/track-order.php?order_number=' + encodeURIComponent(orderId));
        const data = await res.json();

        if (data.success && data.order) {
            displayResult(orderId, data.order);
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            // Fallback: check localStorage
            const localOrder = getLocalOrder(orderId);
            if (localOrder) {
                displayLocalResult(orderId, localOrder);
                resultSection.style.display = 'block';
                resultSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                errorEl.style.display = 'flex';
                errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Order ID not found. Please check and try again.';
                resultSection.style.display = 'none';
            }
        }
    } catch (err) {
        // If offline — use localStorage
        const localOrder = getLocalOrder(orderId);
        if (localOrder) {
            displayLocalResult(orderId, localOrder);
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            errorEl.style.display = 'flex';
            errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Could not connect. Please try again.';
        }
    }
}

// ── Display result from DB ──
function displayResult(orderId, order) {
    document.getElementById('res-order-id').textContent = orderId;
    document.getElementById('res-date').textContent     = formatDate(order.created_at);
    document.getElementById('res-city').textContent     = order.city || 'N/A';
    document.getElementById('res-total').textContent    = 'Rs ' + order.grand_total;

    updateTimeline(order.status, order.created_at);
    renderItems(order.items || []);
}

// ── Display result from localStorage ──
function displayLocalResult(orderId, order) {
    document.getElementById('res-order-id').textContent = orderId;
    document.getElementById('res-date').textContent     = order.date || 'N/A';
    document.getElementById('res-city').textContent     = order.city || 'N/A';
    document.getElementById('res-total').textContent    = 'Rs ' + order.grand;

    updateTimeline(order.status || 'placed', null);
    renderItems(order.items || []);
}

// ── Update Timeline ──
const stepOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];

function updateTimeline(currentStatus, orderDate) {
    const currentIdx = stepOrder.indexOf(currentStatus);

    stepOrder.forEach((step, idx) => {
        const el     = document.getElementById('step-' + step);
        const dateEl = document.getElementById('date-' + step);
        if (!el) return;

        el.classList.remove('done', 'active');

        if (idx < currentIdx) {
            el.classList.add('done');
            if (dateEl) dateEl.textContent = orderDate ? formatDate(orderDate) : 'Completed';
        } else if (idx === currentIdx) {
            el.classList.add('done', 'active');
            if (dateEl) dateEl.textContent = orderDate ? formatDate(orderDate) : 'In Progress';
        } else {
            if (dateEl) dateEl.textContent = 'Waiting...';
        }
    });
}

// ── Render Items ──
function renderItems(items) {
    const container = document.getElementById('track-items-list');
    if (!container) return;

    if (!items || items.length === 0) {
        container.innerHTML = '<p style="color:#888;font-size:13px;">No items found.</p>';
        return;
    }

    let html = '';
    items.forEach((item, idx) => {
        const name  = item.product_name || item.name || 'Item';
        const qty   = item.quantity     || item.qty  || 1;
        const price = item.total_price  || (item.price * qty) || 0;

        html += `
        <div class="track-item-row">
            <div class="ti-left">
                <div class="ti-num">${idx + 1}</div>
                <div>
                    <div class="ti-name">${name}</div>
                    <div class="ti-qty">x ${qty}</div>
                </div>
            </div>
            <div class="ti-price">Rs ${price}</div>
        </div>`;
    });
    container.innerHTML = html;
}

// ── Get order from localStorage ──
function getLocalOrder(orderId) {
    const orders = JSON.parse(localStorage.getItem('tsOrders')) || [];
    return orders.find(o => o.orderId === orderId) || null;
}

// ── Format date ──
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    // Auto-fill from sessionStorage
    const savedId = sessionStorage.getItem('tsOrderId');
    if (savedId) {
        const input = document.getElementById('order-input');
        if (input) {
            input.value = savedId;
            trackOrder();
        }
    }

    // Enter key
    const input = document.getElementById('order-input');
    if (input) {
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') trackOrder();
        });
    }

    // Cart badge
    const cart  = JSON.parse(localStorage.getItem('tsCart')) || [];
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = cart.reduce((s, i) => s + i.qty, 0);
});
