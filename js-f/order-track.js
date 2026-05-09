// ============================================
//   ORDER TRACK — JavaScript
// ============================================

// Sample orders data
// PHP ke baad yeh database se aayega
const ordersData = {
    '#TS-1042': {
        date:      '07 May 2026',
        city:      'Gujranwala',
        total:     'Rs 480',
        status:    'placed',   // placed | confirmed | processing | shipped | delivered
        items: [
            { name: 'Fennel Leaf Seed',         price: 100, qty: 1 },
            { name: 'Tomato Yellow Round Seed',  price: 150, qty: 1 },
            { name: 'Sweet Corn F1 Hybrid Seed', price: 100, qty: 1 },
            { name: 'Portulaca Half Time Mix',   price: 80,  qty: 1 },
        ],
        timeline: {
            placed:     '07 May 2026, 12:15 PM',
            confirmed:  null,
            processing: null,
            shipped:    null,
            delivered:  null,
        }
    },
    '#TS-1041': {
        date:      '24 Apr 2026',
        city:      'Lahore',
        total:     'Rs 500',
        status:    'delivered',
        items: [
            { name: 'Sweet Corn F1 Hybrid', price: 100, qty: 5 },
        ],
        timeline: {
            placed:     '24 Apr 2026, 09:00 AM',
            confirmed:  '24 Apr 2026, 10:30 AM',
            processing: '25 Apr 2026, 11:00 AM',
            shipped:    '26 Apr 2026, 02:00 PM',
            delivered:  '28 Apr 2026, 05:00 PM',
        }
    },
    '#TS-1040': {
        date:      '23 Apr 2026',
        city:      'Faisalabad',
        total:     'Rs 450',
        status:    'processing',
        items: [
            { name: 'Tomato Yellow Round', price: 150, qty: 3 },
        ],
        timeline: {
            placed:     '23 Apr 2026, 03:00 PM',
            confirmed:  '23 Apr 2026, 04:00 PM',
            processing: '24 Apr 2026, 10:00 AM',
            shipped:    null,
            delivered:  null,
        }
    }
};

// Status step order
const stepOrder = ['placed', 'confirmed', 'processing', 'shipped', 'delivered'];

// Map step name to HTML element id
const stepIds = {
    placed:     'step-placed',
    confirmed:  'step-confirmed',
    processing: 'step-processing',
    shipped:    'step-shipped',
    delivered:  'step-delivered',
};

const dateIds = {
    placed:     'date-placed',
    confirmed:  'date-confirmed',
    processing: 'date-processing',
    shipped:    'date-shipped',
    delivered:  'date-delivered',
};

// Main track function
function trackOrder() {
    const input = document.getElementById('order-input').value.trim().toUpperCase();
    const errorEl = document.getElementById('search-error');
    const resultSection = document.getElementById('result-section');

    // Normalize: add # if missing
    const orderId = input.startsWith('#') ? input : '#' + input;

    const order = ordersData[orderId];

    if (!order) {
        errorEl.style.display = 'flex';
        resultSection.style.display = 'none';
        return;
    }

    errorEl.style.display = 'none';

    // Fill order info bar
    document.getElementById('res-order-id').textContent = orderId;
    document.getElementById('res-date').textContent     = order.date;
    document.getElementById('res-city').textContent     = order.city;
    document.getElementById('res-total').textContent    = order.total;

    // Reset all steps
    stepOrder.forEach(step => {
        const el = document.getElementById(stepIds[step]);
        const dateEl = document.getElementById(dateIds[step]);
        if (el) {
            el.classList.remove('done', 'active');
            if (dateEl) dateEl.textContent = 'Waiting...';
        }
    });

    // Mark done / active steps
    const currentIndex = stepOrder.indexOf(order.status);

    stepOrder.forEach((step, index) => {
        const el = document.getElementById(stepIds[step]);
        const dateEl = document.getElementById(dateIds[step]);

        if (!el) return;

        if (index < currentIndex) {
            el.classList.add('done');
        } else if (index === currentIndex) {
            el.classList.add('done', 'active');
        }

        // Set date text
        const dateVal = order.timeline[step];
        if (dateEl) {
            dateEl.textContent = dateVal ? dateVal : 'Waiting...';
        }
    });

    // Render items
    renderTrackItems(order.items);

    // Show result
    resultSection.style.display = 'block';
    resultSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// Render items list
function renderTrackItems(items) {
    const container = document.getElementById('track-items-list');
    if (!container || !items) return;

    let html = '';
    items.forEach((item, index) => {
        html += `
        <div class="track-item-row">
            <div class="ti-left">
                <div class="ti-num">${index + 1}</div>
                <div>
                    <div class="ti-name">${item.name}</div>
                    <div class="ti-qty">x ${item.qty}</div>
                </div>
            </div>
            <div class="ti-price">Rs ${item.price * item.qty}</div>
        </div>`;
    });

    container.innerHTML = html;
}

// Allow Enter key to search
document.addEventListener('DOMContentLoaded', () => {
    // Auto-fill from sessionStorage if coming from order-confirm page
    const savedId = sessionStorage.getItem('tsOrderId');
    if (savedId) {
        const input = document.getElementById('order-input');
        if (input) {
            input.value = savedId;
            trackOrder();
        }
    }

    // Enter key support
    const input = document.getElementById('order-input');
    if (input) {
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') trackOrder();
        });
    }

    // Cart badge
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = cart.reduce((s, i) => s + i.qty, 0);
});