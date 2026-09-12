// ============================================================
//   js-f/my-orders.js — User Orders Page & Database Integration Logic
//   Yeh file database se user ke orders fetch karke unhein dynamically display karti hai
// ============================================================

// Har order status ke liye corresponding CSS class define kar rahe hain.
const statusClass = {
    placed:     'status-placed',
    confirmed:  'status-confirmed',
    processing: 'status-processing',
    shipped:    'status-shipped',
    delivered:  'status-delivered',
    cancelled:  'status-cancelled',
};

// Har order status ka readable label define kar rahe hain.
const statusLabel = {
    placed:     'Order Placed',
    confirmed:  'Confirmed',
    processing: 'Processing',
    shipped:    'Shipped',
    delivered:  'Delivered',
    cancelled:  'Cancelled',
};

// Har status ke according estimated delivery time define kar rahe hain.
const deliveryEstimate = {
    placed:     '3 - 5 business days',
    confirmed:  '2 - 4 business days',
    processing: '2 - 3 business days',
    shipped:    '1 - 2 business days',
    delivered:  'Delivered ✓',
    cancelled:  'Order Cancelled',
};

// ── Build one order card ──
// Ek order ka complete HTML card generate karne ka function.
function buildOrderCard(order) {
    // Order status ke according CSS class, label aur delivery estimate le rahe hain.
    const badge = statusClass[order.status] || 'status-placed';
    const label = statusLabel[order.status] || order.status;
    const eta   = deliveryEstimate[order.status] || '3 - 5 business days';

    // Items HTML
    // Order ke andar available items ko read kar rahe hain.
    const items = order.items || [];
    let itemsHtml = '';

    // Har order item ka HTML prepare kar rahe hain.
    items.forEach((item, i) => {
        // Product ka naam different possible property names se le rahe hain.
        const name  = item.product_name || item.name || 'Item';

        // Product quantity available na ho to default 1 use kar rahe hain.
        const qty   = item.quantity     || item.qty  || 1;

        // Item ki total price le rahe hain, warna price × quantity calculate kar rahe hain.
        const price = item.total_price  || item.price * qty;

        // Item ka HTML order items list mein add kar rahe hain.
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

    // Order ki basic information aur totals read kar rahe hain.
    const date     = order.date || formatDate(order.created_at);
    const city     = order.city || 'N/A';
    const payment  = order.payment_method || order.payment || 'cod';
    const subtotal = order.subtotal || 0;
    const delivery = order.delivery_charge || order.delivery || 50;
    const grand    = order.grand_total || order.grand || (subtotal + delivery);

    // Complete order card ka HTML return kar rahe hain.
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
// Selected order ki ID sessionStorage mein save karke tracking page open kar rahe hain.
function trackOrder(orderId) {
    sessionStorage.setItem('tsOrderId', orderId);
    window.location.href = 'order-track.html';
}

// ── Format date ──
// Database se milne wali date ko readable format mein convert kar rahe hain.
function formatDate(dateStr) {
    // Date available na ho to N/A return kar rahe hain.
    if (!dateStr) return 'N/A';

    // Date ko DD Mon YYYY format mein convert kar rahe hain.
    return new Date(dateStr).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

// ── Load orders: try DB first, fallback localStorage ──
// Pehle database se orders load karte hain, agar fail ho to localStorage use karte hain.
async function loadOrders() {
    // Orders page ke required HTML elements select kar rahe hain.
    const container = document.getElementById('orders-list');
    const noOrders  = document.getElementById('no-orders');
    const countEl   = document.getElementById('total-orders-count');

    // Orders ko temporarily store karne ke liye empty array.
    let orders = [];

    try {
       // Backend endpoint se current user ke orders fetch kar rahe hain.
       const res  = await fetch('orders/get-my-orders.php');

        // Response ko JSON data mein convert kar rahe hain.
        const data = await res.json();

        // Agar database se valid orders milein to unhein use kar rahe hain.
        if (Array.isArray(data) && data.length > 0) {
            orders = data;
        } else {
            // Fallback to localStorage
            // Database mein orders na milne par localStorage se orders load kar rahe hain.
            orders = JSON.parse(localStorage.getItem('tsOrders')) || [];
        }
    } catch (e) {
        // Offline fallback
        // API request fail hone par localStorage ko fallback ke taur par use kar rahe hain.
        orders = JSON.parse(localStorage.getItem('tsOrders')) || [];
    }

    // Total orders ki count page par show kar rahe hain.
    if (countEl) countEl.textContent = orders.length;

    // Agar koi order nahi hai to empty orders message show kar rahe hain.
    if (orders.length === 0) {
        if (noOrders) noOrders.style.display = 'block';
        return;
    }

    // Har order ka HTML card generate karke ek single HTML string bana rahe hain.
    let html = '';
    orders.forEach(order => { html += buildOrderCard(order); });

    // Generated orders ko page ke container mein display kar rahe hain.
    if (container) container.innerHTML = html;
}

// Page load hone ke baad orders automatically load kar rahe hain.
document.addEventListener('DOMContentLoaded', loadOrders);