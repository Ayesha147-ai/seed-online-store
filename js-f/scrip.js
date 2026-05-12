// ============================================
//   SCRIP.JS — Index Page (Home)
// ============================================

// 1. Cart data initialization
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// 2. Product Database — index page ke 6 products
// Yeh names bilkul index.html ke cards se match karte hain
const products = [
    { id: 'idx-1', name: 'Tomato Round Red Seed',    price: 100, category: 'Vegetable Seeds', img: 'css-f/img/v1.jpg' },
    { id: 'idx-2', name: 'Cucumber Seed',            price: 150, category: 'Vegetable Seeds', img: 'css-f/img/v2.jpg' },
    { id: 'idx-3', name: 'Watermelon Seed',          price: 80,  category: 'Fruit Seeds',     img: 'css-f/img/fru1.jpg' },
    { id: 'idx-4', name: 'Muskmelon Seed',           price: 80,  category: 'Fruit Seeds',     img: 'css-f/img/fru2.jpg' },
    { id: 'idx-5', name: 'Niazbo Seed Organic',      price: 120, category: 'Herb Seeds',      img: 'css-f/img/herb1.jpg' },
    { id: 'idx-6', name: 'Fennel Leaf Seed',         price: 100, category: 'Herb Seeds',      img: 'css-f/img/herb2.jpg' },
];

// 3. Cart badge update
function updateCartBadge() {
    const badges = document.querySelectorAll('.cart-count');
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    badges.forEach(badge => badge.textContent = totalQty);
}

// 4. Add to cart function
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cartItems.find(item => item.id === productId);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cartItems.push({ ...product, qty: 1 });
    }

    localStorage.setItem('tsCart', JSON.stringify(cartItems));
    updateCartBadge();
}

// 5. Page load pe sab setup karo
document.addEventListener('DOMContentLoaded', () => {

    updateCartBadge();

    // Har "Add to Cart" button ko product se link karo
    document.querySelectorAll('.cart-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const productId = 'idx-' + (index + 1);
            addToCart(productId);

            // Button feedback
            const originalText = btn.textContent;
            btn.textContent = '✓ Added!';
            btn.classList.add('active-btn');

            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('active-btn');
            }, 1200);
        });
    });

    // Buy Seeds button — scroll to products
    const buyBtn = document.querySelector('.btn-primary');
    const productSection = document.getElementById('product1');
    if (buyBtn && productSection) {
        buyBtn.addEventListener('click', () => {
            productSection.scrollIntoView({ behavior: 'smooth' });
        });
    }
});