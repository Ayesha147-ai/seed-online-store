// ============================================================
//   js-f/order-track.js — Order Tracking & Status Timeline Logic
//   Yeh file database ya localStorage se order details fetch karke tracking status timeline aur order items display karti hai
// ============================================================

// Order ID ke through order tracking data fetch karne ka function.
async function trackOrder() {
    // User se Order ID input le rahe hain aur extra spaces remove kar rahe hain.
    const input   = document.getElementById('order-input').value.trim();

    // Error message aur result section ke elements select kar rahe hain.
    const errorEl = document.getElementById('search-error');
    const resultSection = document.getElementById('result-section');

    // Agar Order ID empty ho to error message show karke function stop kar rahe hain.
    if (!input) {
        errorEl.style.display = 'flex';
        errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter an Order ID.';
        return;
    }

    // Normalize
    // Agar Order ID mein # nahi hai to beginning mein # add kar rahe hain.
    const orderId = input.startsWith('#') ? input : '#' + input;

    // Previous error message hide kar rahe hain.
    errorEl.style.display = 'none';

    try {
        // First try DB
       // Pehle database se order tracking information fetch kar rahe hain.
       const res  = await fetch('orders/track-order.php?order_number=' + encodeURIComponent(orderId));

        // Server response ko JSON data mein convert kar rahe hain.
        const data = await res.json();

        // Agar database se order successfully mil gaya hai to result display kar rahe hain.
        if (data.success && data.order) {
            displayResult(orderId, data.order);
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            // Fallback: check localStorage
            // Database mein order na mile to localStorage mein order search kar rahe hain.
            const localOrder = getLocalOrder(orderId);

            // Agar localStorage mein order mil jaye to uska result display kar rahe hain.
            if (localOrder) {
                displayLocalResult(orderId, localOrder);
                resultSection.style.display = 'block';
                resultSection.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Agar kahin bhi order na mile to not found error show kar rahe hain.
                errorEl.style.display = 'flex';
                errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Order ID not found. Please check and try again.';
                resultSection.style.display = 'none';
            }
        }
    } catch (err) {
        // If offline — use localStorage
        // Database request fail hone par localStorage ko fallback ke taur par use kar rahe hain.
        const localOrder = getLocalOrder(orderId);

        // Agar localStorage mein order mil jaye to local result display kar rahe hain.
        if (localOrder) {
            displayLocalResult(orderId, localOrder);
            resultSection.style.display = 'block';
            resultSection.scrollIntoView({ behavior: 'smooth' });
        } else {
            // Connection fail hone aur local order na milne par error show kar rahe hain.
            errorEl.style.display = 'flex';
            errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Could not connect. Please try again.';
        }
    }
}

// ── Display result from DB ──
// Database se milne wale order ka result page par display kar rahe hain.
function displayResult(orderId, order) {
    // Order ki basic information HTML elements mein set kar rahe hain.
    document.getElementById('res-order-id').textContent = orderId;
    document.getElementById('res-date').textContent     = formatDate(order.created_at);
    document.getElementById('res-city').textContent     = order.city || 'N/A';
    document.getElementById('res-total').textContent    = 'Rs ' + order.grand_total;

    // Order status timeline aur items ko display kar rahe hain.
    updateTimeline(order.status, order.created_at);
    renderItems(order.items || []);
}

// ── Display result from localStorage ──
// LocalStorage se milne wale order ka result page par display kar rahe hain.
function displayLocalResult(orderId, order) {
    // Local order ki basic information page par show kar rahe hain.
    document.getElementById('res-order-id').textContent = orderId;
    document.getElementById('res-date').textContent     = order.date || 'N/A';
    document.getElementById('res-city').textContent     = order.city || 'N/A';
    document.getElementById('res-total').textContent    = 'Rs ' + order.grand;

    // Local order ka status timeline aur items render kar rahe hain.
    updateTimeline(order.status || 'placed', null);
    renderItems(order.items || []);
}

// ── Update Timeline ──
// Order ke current status ke according tracking timeline update kar rahe hain.
const stepOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];

function updateTimeline(currentStatus, orderDate) {
    // Current status timeline mein kis position par hai woh find kar rahe hain.
    const currentIdx = stepOrder.indexOf(currentStatus);

    // Timeline ke har step ko process kar rahe hain.
    stepOrder.forEach((step, idx) => {
        // Current step aur uski date display karne wale elements select kar rahe hain.
        const el     = document.getElementById('step-' + step);
        const dateEl = document.getElementById('date-' + step);
        if (!el) return;

        // Pehle se applied status classes remove kar rahe hain.
        el.classList.remove('done', 'active');

        // Current status se pehle wale steps ko completed mark kar rahe hain.
        if (idx < currentIdx) {
            el.classList.add('done');
            if (dateEl) dateEl.textContent = orderDate ? formatDate(orderDate) : 'Completed';

        // Current status ko completed aur active dono mark kar rahe hain.
        } else if (idx === currentIdx) {
            el.classList.add('done', 'active');
            if (dateEl) dateEl.textContent = orderDate ? formatDate(orderDate) : 'In Progress';

        // Future steps ko waiting state mein show kar rahe hain.
        } else {
            if (dateEl) dateEl.textContent = 'Waiting...';
        }
    });
}

// ── Render Items ──
// Order ke products ko tracking page par display karne ka function.
function renderItems(items) {
    // Items display karne wala container select kar rahe hain.
    const container = document.getElementById('track-items-list');
    if (!container) return;

    // Agar items available nahi hain to no items message show kar rahe hain.
    if (!items || items.length === 0) {
        container.innerHTML = '<p style="color:#888;font-size:13px;">No items found.</p>';
        return;
    }

    // Har order item ka HTML generate kar rahe hain.
    let html = '';
    items.forEach((item, idx) => {
        // Product name ke liye possible property names check kar rahe hain.
        const name  = item.product_name || item.name || 'Item';

        // Quantity available na ho to default 1 use kar rahe hain.
        const qty   = item.quantity     || item.qty  || 1;

        // Item ki total price le rahe hain, warna price × quantity calculate kar rahe hain.
        const price = item.total_price  || (item.price * qty) || 0;

        // Item ka HTML tracking list mein add kar rahe hain.
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

    // Generated items ko container mein display kar rahe hain.
    container.innerHTML = html;
}

// ── Get order from localStorage ──
// LocalStorage mein saved orders mein specified Order ID search kar rahe hain.
function getLocalOrder(orderId) {
    // Saved orders ko localStorage se read kar rahe hain.
    const orders = JSON.parse(localStorage.getItem('tsOrders')) || [];

    // Matching Order ID wala order return kar rahe hain.
    return orders.find(o => o.orderId === orderId) || null;
}

// ── Format date ──
// Date ko readable format mein convert karne ka helper function.
function formatDate(dateStr) {
    // Date available na ho to N/A return kar rahe hain.
    if (!dateStr) return 'N/A';

    // Provided date string ko JavaScript Date object mein convert kar rahe hain.
    const d = new Date(dateStr);

    // Date ko DD Mon YYYY format mein display kar rahe hain.
    return d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
}

// ── Init ──
// Page load hone par tracking page initialize kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {
    // Auto-fill from sessionStorage
    // Agar sessionStorage mein Order ID saved hai to input automatically fill kar rahe hain.
    const savedId = sessionStorage.getItem('tsOrderId');
    if (savedId) {
        const input = document.getElementById('order-input');
        if (input) {
            input.value = savedId;

            // Saved Order ID ke saath automatically tracking start kar rahe hain.
            trackOrder();
        }
    }

    // Enter key
    // Input field mein Enter press karne par order tracking start kar rahe hain.
    const input = document.getElementById('order-input');
    if (input) {
        input.addEventListener('keypress', e => {
            if (e.key === 'Enter') trackOrder();
        });
    }

    // Cart badge
    // LocalStorage se cart ki total quantity nikal kar navbar badge update kar rahe hain.
    const cart  = JSON.parse(localStorage.getItem('tsCart')) || [];
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = cart.reduce((s, i) => s + i.qty, 0);
});