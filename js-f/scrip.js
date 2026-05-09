// 1. Cart data initialization
// localStorage se purana data uthana ya khali array rakhna
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// 2. Product Database
const products = [
    { id: 1, name: 'Sweet Corn F1 Hybrid Seed',   price: 100, category: 'Vegetable Seeds', img: 'css-f/img/v1.jpg' },
    { id: 2, name: 'Tomato Yellow Round Seed',    price: 150, category: 'Vegetable Seeds', img: 'css-f/img/v2.jpg' },
    { id: 3, name: 'Zinnia Lilliput Formula Mix', price: 80,  category: 'Flower Seeds',    img: 'css-f/img/flow1.jpg' },
    { id: 4, name: 'Portulaca Half Time Mix',     price: 80,  category: 'Flower Seeds',    img: 'css-f/img/flow2.jpg' },
    { id: 5, name: 'Dill Seed Organic',           price: 120, category: 'Herb Seeds',      img: 'css-f/img/herb1.jpg' },
    { id: 6, name: 'Fennel Leaf Seed',            price: 100, category: 'Herb Seeds',      img: 'css-f/img/herb2.jpg' },
];

// 3. Navbar mein cart count update karne ka function
function updateCartBadge() {
    const badges = document.querySelectorAll('.cart-count');
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    badges.forEach(badge => badge.textContent = totalQty);
}

// 4. Product add karne ka main function
function addToCart(productId) {
    // Array mein se product dhoondna
    const product = products.find(p => p.id === productId);
    if (!product) return;

    // Check karna ke kya ye product pehle se cart mein hai?
    const existingItem = cartItems.find(item => item.id === productId);

    if (existingItem) {
        // Agar pehle se hai to sirf quantity barhao
        existingItem.qty += 1;
    } else {
        // Agar naya hai to naye object ke taur par add karo
        cartItems.push({ ...product, qty: 1 });
    }

    // Local Storage mein save karna taake refresh par bhi cart na urray
    localStorage.setItem('tsCart', JSON.stringify(cartItems));
    
    // UI update karna
    updateCartBadge();
}

// 5. Setup everything on page load
document.addEventListener('DOMContentLoaded', () => {
    
    // Update badge based on existing data
    updateCartBadge();

    // Add event listener to all 'Add to Cart' buttons
    document.querySelectorAll('.cart-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const productId = index + 1; 
            
            addToCart(productId);

            // Button animation and feedback
            const originalText = btn.textContent;
            btn.textContent = '✓ Added!';
            btn.classList.add('active-btn');
            
            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('active-btn');
            }, 1200);
        });
    });

    // ✅ Buy Seeds button scroll logic (CORRECT)
const buyBtn = document.querySelector('.btn-primary'); // FIXED
const productSection = document.getElementById('product1');

if (buyBtn && productSection) {
    buyBtn.addEventListener('click', () => {
        productSection.scrollIntoView({ behavior: 'smooth' });
    });
}
});