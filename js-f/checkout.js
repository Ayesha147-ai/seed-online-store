let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// ===== STRIPE INITIALIZE =====
const stripe = Stripe('pk_test_51UAQgmQodAeOwyHCweo0YyDiuLXBtVduAqPCC6bCPGnQYAj2hEwgGY8kQuyhsp58mHP3j00lmDCBkjrFbq8rPYoq00Y5pmPjgj');
const elements = stripe.elements();
const cardElement = elements.create('card', {
    hidePostalCode: true,
    style: {
        base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: '"Segoe UI", sans-serif',
            '::placeholder': { color: '#aab7c4' }
        },
        invalid: { color: '#fa755a' }
    }
});
cardElement.mount('#card-element');

function updateNavBadge() {
    const badge = document.getElementById('cartCount');
    if (badge) {
        const total = cartItems.reduce((sum, item) => sum + item.qty, 0);
        badge.textContent = total;
    }
}

function renderOrderSummary() {
    const list      = document.getElementById('orderItemsList');
    const itemCount = document.getElementById('itemCount');
    const subtotalEl= document.getElementById('summarySubtotal');
    const grandEl   = document.getElementById('summaryGrand');

    if (!list) return;

    if (cartItems.length === 0) {
        list.innerHTML = '<p style="color:#888;font-size:14px;padding:10px 0;">Cart is empty.</p>';
        if (itemCount)  itemCount.textContent  = '0';
        if (subtotalEl) subtotalEl.textContent = 'Rs 0';
        if (grandEl)    grandEl.textContent    = 'Rs 50';
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
                <div class="order-item-price">Rs ${itemTotal}</div>
            </div>`;
    });

    list.innerHTML = html;
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    if (itemCount)  itemCount.textContent  = totalQty;
    if (subtotalEl) subtotalEl.textContent = `Rs ${subtotal}`;
    if (grandEl)    grandEl.textContent    = `Rs ${subtotal + 50}`;
}

async function placeOrder() {
    const fullName  = document.getElementById('fullName').value.trim();
    const email     = document.getElementById('email').value.trim();
    const phone     = document.getElementById('phone').value.trim();
    const city      = document.getElementById('city').value.trim();
    const province  = document.getElementById('province').value;
    const warehouse = document.getElementById('warehouse').value;
    const address   = document.getElementById('address').value.trim();
    const terms     = document.getElementById('terms').checked;
    const paymentEl = document.querySelector('input[name="payment"]:checked');

    if (!fullName)  { alert('Full Name is required.');            return; }
    if (!email)     { alert('Email is required.');                return; }
    if (!phone)     { alert('Phone number is required.');         return; }
    if (!city)      { alert('City is required.');                 return; }
    if (!province)  { alert('Please select a province.');         return; }
    if (!warehouse) { alert('Please select a warehouse.');        return; }
    if (!address)   { alert('Address is required.');              return; }
    if (!terms)     { alert('Please accept Terms & Conditions.'); return; }
    if (cartItems.length === 0) { alert('Cart is empty!');        return; }

    const payment = paymentEl ? paymentEl.value : 'cod';

    const btn = document.querySelector('.place-order-btn');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...'; }

    // ===== STRIPE TOKEN GENERATION (Agar Stripe select ho) =====
    let stripeToken = '';
    if (payment === 'stripe') {
        const {token, error} = await stripe.createToken(cardElement);
        if (error) {
            alert(error.message);
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Place Order & Pay'; }
            return;
        }
        stripeToken = token.id;
    }

    const formData = new FormData();
    formData.append('fullName',   fullName);
    formData.append('email',      email);
    formData.append('phone',      phone);
    formData.append('city',       city);
    formData.append('province',   province);
    formData.append('warehouse',  warehouse);
    formData.append('address',    address);
    formData.append('payment',    payment);
    formData.append('stripeToken', stripeToken); // Stripe Token backend ke liye
    formData.append('cart',       JSON.stringify(cartItems));

    try {
        const res  = await fetch('orders/place-order.php', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            const existingOrders = JSON.parse(localStorage.getItem('tsOrders')) || [];
            existingOrders.unshift({
                orderId:  data.order_number,
                date:     new Date().toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }),
                status:   'placed',
                city:     city,
                payment:  payment,
                subtotal: data.grand_total - 50,
                delivery: 50,
                grand:    data.grand_total,
                items:    cartItems.map(i => ({ name: i.name, qty: i.qty, price: i.price }))
            });
            localStorage.setItem('tsOrders', JSON.stringify(existingOrders));
            localStorage.removeItem('tsCart');
            cartItems = [];
            document.getElementById('orderId').textContent = data.order_number;
            document.getElementById('successModal').classList.add('active');
        } else {
            alert('Order failed: ' + (data.msg || 'Please try again.'));
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Place Order & Pay'; }
        }
    } catch (err) {
        console.error("Asli Error Yeh Hai:", err);
        alert('Asli Error: ' + err.message);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-lock"></i> Place Order & Pay'; }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    updateNavBadge();
    renderOrderSummary();
});