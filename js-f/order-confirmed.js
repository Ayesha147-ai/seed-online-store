// ============================================
//   ORDER CONFIRM — JavaScript
// ============================================

// Sample cart items (same as script.js products)
// PHP ke baad yeh localStorage ya session se aayega
const cartItems = JSON.parse(localStorage.getItem('tsCart')) || [
    { id: 1, name: 'Fennel Leaf Seed',          price: 100, qty: 1 },
    { id: 2, name: 'Tomato Yellow Round Seed',   price: 150, qty: 1 },
    { id: 3, name: 'Sweet Corn F1 Hybrid Seed',  price: 100, qty: 1 },
    { id: 4, name: 'Portulaca Half Time Mix',     price: 80,  qty: 1 },
];

const DELIVERY_CHARGE = 50;

// Generate random Order ID
function generateOrderId() {
    const stored = sessionStorage.getItem('tsOrderId');
    if (stored) return stored;
    const num = Math.floor(1000 + Math.random() * 9000);
    const id = '#TS-' + num;
    sessionStorage.setItem('tsOrderId', id);
    return id;
}

// Render order items list
function renderItems() {
    const container = document.getElementById('order-items-list');
    if (!container) return;

    if (cartItems.length === 0) {
        container.innerHTML = '<p style="color:#888;font-size:13px;padding:12px 0;">No items found.</p>';
        return;
    }

    let html = '';
    cartItems.forEach((item, index) => {
        html += `
        <div class="order-item-row">
            <div class="item-left">
                <div class="item-num">${index + 1}</div>
                <div>
                    <div class="item-name">${item.name}</div>
                    <div class="item-qty">x ${item.qty}</div>
                </div>
            </div>
            <div class="item-price">Rs ${item.price * item.qty}</div>
        </div>`;
    });

    container.innerHTML = html;
}

// Calculate totals
function renderTotals() {
    const subtotal = cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0);
    const grand = subtotal + DELIVERY_CHARGE;
    const totalQty = cartItems.reduce((sum, i) => sum + i.qty, 0);

    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

    set('subtotal-val', 'Rs ' + subtotal);
    set('grand-val',    'Rs ' + grand);
    set('grand-total',  'Rs ' + grand);
    set('item-count',   totalQty + ' item' + (totalQty !== 1 ? 's' : ''));
}

// On page load
document.addEventListener('DOMContentLoaded', () => {

    // Set Order ID
    document.getElementById('order-id').textContent = generateOrderId();

    // Render items and totals
    renderItems();
    renderTotals();

    // Clear cart after order placed
    localStorage.removeItem('tsCart');

    // Update cart badge (will show 0 now)
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = '0';
});