// ============================================================
//   my-orders.js — Updated: DB se orders fetch karo
// ============================================================

const statusClass = {
    placed:     'status-placed',
    confirmed:  'status-confirmed',
    processing: 'status-processing',
    shipped:    'status-shipped',
    delivered:  'status-delivered',
    cancelled:  'status-cancelled',
};

const statusLabel = {
    placed:     'Order Placed',
    confirmed:  'Confirmed',
    processing: 'Processing',
    shipped:    'Shipped',
    delivered:  'Delivered',
    cancelled:  'Cancelled',
};

const deliveryEstimate = {
    placed:     '3 - 5 business days',
    confirmed:  '2 - 4 business days',
    processing: '2 - 3 business days',
    shipped:    '1 - 2 business days',
    delivered:  'Delivered ✓',
    cancelled:  'Order Cancelled',
};

// ── Build one order card ──
function buildOrderCard(order) {
    const badge = statusClass[order.status] || 'status-placed';
    const label = statusLabel[order.status] || order.status;
    const eta   = deliveryEstimate[order.status] || '3 - 5 business days';

    // Items HTML
    const items = order.items || [];
    let itemsHtml = '';
    items.forEach((item, i) => {
        const name  = item.product_name || item.name || 'Item';
        const qty   = item.quantity     || item.qty  || 1;
        const price = item.total_price  || item.price * qty;

        itemsHtml += `
        <div class="order-item-row">
            <div class="item-left">
                <div class="item-num">${i + 1}</div>
                <div>
                    <div class="item-name">${name}</div>
                    <div class="item-qty">x ${qty}</div>
                </div>
            </div>
            <div class="item-price">Rs ${price}</div>
        </div>`;
    });

    const date     = order.date || formatDate(order.created_at);
    const city     = order.city || 'N/A';
    const payment  = order.payment_method || order.payment || 'cod';
    const subtotal = order.subtotal || 0;
    const delivery = order.delivery_charge || order.delivery || 50;
    const grand    = order.grand_total || order.grand || (subtotal + delivery);

    return `
    <div class="order-card">
        <div class="order-top">
            <div class="order-id-wrap">
                <span class="order-id-label">Order ID</span>
                <span class="order-id-val">${order.order_number || order.orderId}</span>
            </div>
            <span class="status-badge ${badge}">${label}</span>
        </div>
        <div class="order-meta">
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-calendar-alt"></i> Order Date</span>
                <span class="meta-val">${date}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-map-marker-alt"></i> City</span>
                <span class="meta-val">${city}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-credit-card"></i> Payment</span>
                <span class="meta-val">${payment.toUpperCase()}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label"><i class="fas fa-truck"></i> Estimated Delivery</span>
                <span class="meta-val delivery">${eta}</span>
            </div>
        </div>
        <div class="order-items">${itemsHtml}</div>
        <div class="order-footer">
            <div class="total-info">
                <span>Subtotal Rs ${subtotal} + Delivery Rs ${delivery}</span>
                <strong>Grand Total: Rs ${grand}</strong>
            </div>
            <button class="btn-track-order" onclick="trackOrder('${order.order_number || order.orderId}')">
                <i class="fas fa-map-marker-alt"></i> Track This Order
            </button>
        </div>
    </div>`;
}

// ── Track button ──
function trackOrder(orderId) {
    sessionStorage.setItem('tsOrderId', orderId);
    window.location.href = 'order-track.html';
}

// ── Format date ──
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

// ── Load orders: try DB first, fallback localStorage ──
async function loadOrders() {
    const container = document.getElementById('orders-list');
    const noOrders  = document.getElementById('no-orders');
    const countEl   = document.getElementById('total-orders-count');

    let orders = [];

    try {
       const res  = await fetch('orders/get-my-orders.php');
        const data = await res.json();
        if (Array.isArray(data) && data.length > 0) {
            orders = data;
        } else {
            // Fallback to localStorage
            orders = JSON.parse(localStorage.getItem('tsOrders')) || [];
        }
    } catch (e) {
        // Offline fallback
        orders = JSON.parse(localStorage.getItem('tsOrders')) || [];
    }

    if (countEl) countEl.textContent = orders.length;

    if (orders.length === 0) {
        if (noOrders) noOrders.style.display = 'block';
        return;
    }

    let html = '';
    orders.forEach(order => { html += buildOrderCard(order); });
    if (container) container.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', loadOrders);
