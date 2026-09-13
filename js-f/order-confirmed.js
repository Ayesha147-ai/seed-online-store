// ============================================================
//   js-f/order-confirmed.js — Order Confirmation & Summary Logic
//   Yeh file order confirmation page par unique order ID generate karne, cart items render karne, totals calculate karne aur cart clear karne ka kaam karti hai
// ============================================================

// Sample cart items (same as script.js products)
// PHP ke baad yeh localStorage ya session se aayega
// LocalStorage se cart items load kar rahe hain, aur data na milne par sample items use honge.
const cartItems = JSON.parse(localStorage.getItem('tsCart')) || [
    { id: 1, name: 'Fennel Leaf Seed',          price: 100, qty: 1 },
    { id: 2, name: 'Tomato Yellow Round Seed',   price: 150, qty: 1 },
    { id: 3, name: 'Sweet Corn F1 Hybrid Seed',  price: 100, qty: 1 },
    { id: 4, name: 'Portulaca Half Time Mix',     price: 80,  qty: 1 },
];

// Order ke total mein fixed delivery charge add kar rahe hain.
const DELIVERY_CHARGE = 50;

// Generate random Order ID
// Unique-looking random order ID generate karne ka function.
function generateOrderId() {
    // Agar session mein pehle se order ID hai to wahi return kar rahe hain.
    const stored = sessionStorage.getItem('tsOrderId');
    if (stored) return stored;

    // 4 digit ka random number generate kar rahe hain.
    const num = Math.floor(1000 + Math.random() * 9000);

    // Order ID ke format mein random number add kar rahe hain.
    const id = '#TS-' + num;

    // Generated order ID ko sessionStorage mein save kar rahe hain.
    sessionStorage.setItem('tsOrderId', id);
    return id;
}

// Render order items list
// Order ke tamam items ko page par display karne ka function.
function renderItems() {
    // Order items display karne wala container select kar rahe hain.
    const container = document.getElementById('order-items-list');
    if (!container) return;

    // Agar cart empty hai to no items message show kar rahe hain.
    if (cartItems.length === 0) {
        container.innerHTML = '<p style="color:#888;font-size:13px;padding:12px 0;">No items found.</p>';
        return;
    }

    // Har cart item ka HTML prepare kar rahe hain.
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

    // Generated items ko container mein display kar rahe hain.
    container.innerHTML = html;
}

// Calculate totals
// Subtotal, delivery aur grand total calculate karne ka function.
function renderTotals() {
    // Har item ki price aur quantity multiply karke subtotal calculate kar rahe hain.
    const subtotal = cartItems.reduce((sum, i) => sum + (i.price * i.qty), 0);

    // Subtotal mein delivery charge add karke grand total nikal rahe hain.
    const grand = subtotal + DELIVERY_CHARGE;

    // Cart ke tamam items ki total quantity calculate kar rahe hain.
    const totalQty = cartItems.reduce((sum, i) => sum + i.qty, 0);

    // Given element ID mein value set karne ke liye helper function.
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

    // Calculated totals ko respective HTML elements mein show kar rahe hain.
    set('subtotal-val', 'Rs ' + subtotal);
    set('grand-val',    'Rs ' + grand);
    set('grand-total',  'Rs ' + grand);
    set('item-count',   totalQty + ' item' + (totalQty !== 1 ? 's' : ''));
}

// On page load
// Page load hone ke baad order confirmation data display kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    // Set Order ID
    // Generated order ID ko page par show kar rahe hain.
    document.getElementById('order-id').textContent = generateOrderId();

    // Render items and totals
    // Order items aur totals ko page par render kar rahe hain.
    renderItems();
    renderTotals();

    // Clear cart after order placed
    // Order place hone ke baad localStorage se cart remove kar rahe hain.
    localStorage.removeItem('tsCart');

    // Update cart badge (will show 0 now)
    // Cart clear hone ke baad navbar cart badge ko zero show kar rahe hain.
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = '0';
});