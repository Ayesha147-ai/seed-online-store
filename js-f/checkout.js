// ===== CHECKOUT.JS =====

// 1. Load cart data
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// 2. Update navbar badge
function updateNavBadge() {
    const badge = document.getElementById('cartCount');
    if (badge) {
        const total = cartItems.reduce((sum, item) => sum + item.qty, 0);
        badge.textContent = total;
    }
}

// 3. Render order summary (right side)
function renderOrderSummary() {
    const list = document.getElementById('orderItemsList');
    const itemCount = document.getElementById('itemCount');
    const subtotalEl = document.getElementById('summarySubtotal');
    const grandEl = document.getElementById('summaryGrand');

    if (!list) return;

    if (cartItems.length === 0) {
        list.innerHTML = '<p style="color:#888;font-size:14px;padding:10px 0;">Cart is empty.</p>';
        if (itemCount) itemCount.textContent = '0';
        if (subtotalEl) subtotalEl.textContent = 'Rs 0';
        if (grandEl) grandEl.textContent = 'Rs 50';
        return;
    }

    let html = '';
    let subtotal = 0;

    cartItems.forEach((item, index) => {
        const itemTotal = item.price * item.qty;
        subtotal += itemTotal;

        html += `
            <div class="order-item">
                <div class="order-item-num">${index + 1}</div>
                <img src="${item.img}" alt="${item.name}" onerror="this.src='css-f/img/v1.jpg'">
                <div class="order-item-info">
                    <div class="order-item-name">${item.name}</div>
                    <div class="order-item-qty">x ${item.qty}</div>
                </div>
                <div class="order-item-price">Rs. ${itemTotal}</div>
            </div>
        `;
    });

    list.innerHTML = html;

    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    if (itemCount) itemCount.textContent = totalQty;
    if (subtotalEl) subtotalEl.textContent = `Rs ${subtotal}`;
    if (grandEl) grandEl.textContent = `Rs ${subtotal + 50}`;
}

// 4. Function to place order
function placeOrder() {
    // Form validation
    const fullName = document.getElementById('fullName').value.trim();
    const email    = document.getElementById('email').value.trim();
    const phone    = document.getElementById('phone').value.trim();
    const city     = document.getElementById('city').value.trim();
    const province = document.getElementById('province').value;
    const warehouse= document.getElementById('warehouse').value;
    const address  = document.getElementById('address').value.trim();
    const terms    = document.getElementById('terms').checked;
    const payment  = document.querySelector('input[name="payment"]:checked');

    if (!fullName) { alert('Full Name is required.'); return; }
    if (!email)    { alert('Email is required.'); return; }
    if (!phone)    { alert('Phone number is required.'); return; }
    if (!city)     { alert('City is required.'); return; }
    if (!province) { alert('Please select a province.'); return; }
    if (!warehouse){ alert('Please select a warehouse.'); return; }
    if (!address)  { alert('Address is required.'); return; }
    if (!terms)    { alert('You must accept the Terms & Conditions.'); return; }
    if (cartItems.length === 0) { alert('Cart is empty!'); return; }

    // Generate random Order ID
    const orderId = 'TS-' + Date.now().toString().slice(-6);
    document.getElementById('orderId').textContent = orderId;

    // Clear cart after order
    localStorage.removeItem('tsCart');

    // Show success modal
    document.getElementById('successModal').classList.add('active');
}

// 5. Setup everything on page load
document.addEventListener('DOMContentLoaded', () => {
    updateNavBadge();
    renderOrderSummary();
});