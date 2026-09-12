// ============================================================
//   js-f/scrip.js — Index Page Navigation, Session & Cart Logic
//   Yeh file homepage par session status check karne, navbar elements ko manage karne, aur products ko cart mein add karne ka kaam karti hai
// ============================================================


// ============================================================
//   Session check — har page load pe chalega
//   Agent/Admin ko "Hi, naam" aur "My Cart" nahi dikhne chahiye
//   (unka shopping cart hota hi nahi) — sirf Dashboard/My Account/Logout
// ============================================================

// Backend se current user ka login status aur role check kar rahe hain.
fetch('includes/check-session.php')
    .then(res => res.json())
    .then(data => {
        // Navbar ke different elements ko select kar rahe hain.
        const authBtns = document.getElementById('auth-buttons');
        const userInfo = document.getElementById('user-info');
        const greeting = document.getElementById('user-greeting');
        const dashLink = document.getElementById('dashboard-link');
        const cartBtn  = document.querySelector('.cart-btn-nav');

        // Agar user logged in hai aur required navbar elements available hain.
        if (data.logged_in && authBtns && userInfo) {
            // Login/signup buttons hide karke user information show kar rahe hain.
            authBtns.style.display = 'none';
            userInfo.style.display = 'flex';

            // Agent aur admin ko staff roles ke taur par identify kar rahe hain.
            const isStaff = (data.role === 'agent' || data.role === 'admin');

            // Greeting — ab kisi bhi role ke liye nahi dikhana
            if (greeting) {
                // User greeting ko hide kar rahe hain.
                greeting.style.display = 'none';
            }

            // My Cart — sirf farmer ke liye dikhe
            if (cartBtn) {
                // Agent/admin ke liye cart hide aur farmer ke liye show kar rahe hain.
                cartBtn.style.display = isStaff ? 'none' : '';
            }

            // Dashboard link — sirf agent/admin ke liye
            if (dashLink) {
                // Agent ko agent dashboard ka link de rahe hain.
                if (data.role === 'agent') {
                    dashLink.href = 'agent-dashboard.html';
                    dashLink.style.display = 'inline-block';
                } else if (data.role === 'admin') {
                    // Admin ko admin dashboard ka link de rahe hain.
                    dashLink.href = 'admin-dashboard.html';
                    dashLink.style.display = 'inline-block';
                } else {
                    // Farmer ke liye dashboard link hide kar rahe hain.
                    dashLink.style.display = 'none';
                }
            }

            // "Register as Agent" button — state ke hisaab se dikhana/chhupana
            // Agent registration button ka current status check kar rahe hain.
            checkAgentButtonVisibility();
        }  else {
            // Logged out — simple navbar dikhao
            // Logged-out user ke liye normal authentication buttons show kar rahe hain.
            if (authBtns) authBtns.style.display = 'flex';
            if (userInfo) userInfo.style.display = 'none';
            if (cartBtn)  cartBtn.style.display = '';

            // Bina login — button chhupa rakho
            // Logged-out user ke liye agent registration button hide kar rahe hain.
            const agentBtn = document.getElementById('register-agent-btn');
            if (agentBtn) agentBtn.style.display = 'none';
        }
    })
    .catch(err => console.log('Session check failed:', err));

// ===== REGISTER AS AGENT BUTTON — 4-state visibility check =====
// Backend status ke according Register as Agent button ki visibility control kar rahe hain.
function checkAgentButtonVisibility() {
    // Register as Agent button ko select kar rahe hain.
    const agentBtn = document.getElementById('register-agent-btn');
    if (!agentBtn) return;

    // Backend se agent status fetch kar rahe hain.
    fetch('includes/get-agent-status.php')
        .then(res => res.json())
        .then(data => {
            // Backend ke show_button result ke according button show/hide kar rahe hain.
            agentBtn.style.display = data.show_button ? 'inline-flex' : 'none';
        })
        .catch(() => {
            // Request fail hone par button ko hide kar rahe hain.
            agentBtn.style.display = 'none';
        });
}


// 1. Cart data initialization
// LocalStorage se existing cart data load kar rahe hain.
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// 2. Product Database — index page ke 6 products
// Yeh names bilkul index.html ke cards se match karte hain
// Index page ke products ka basic data yahan store hai.
const products = [
    { id: 'idx-1', name: 'Tomato Round Red Seed',    price: 100, category: 'Vegetable Seeds', img: 'css-f/img/v1.jpg' },
    { id: 'idx-2', name: 'Cucumber Seed',            price: 150, category: 'Vegetable Seeds', img: 'css-f/img/v2.jpg' },
    { id: 'idx-3', name: 'Watermelon Seed',          price: 80,  category: 'Fruit Seeds',     img: 'css-f/img/fru1.jpg' },
    { id: 'idx-4', name: 'Muskmelon Seed',           price: 80,  category: 'Fruit Seeds',     img: 'css-f/img/fru2.jpg' },
    { id: 'idx-5', name: 'Niazbo Seed Organic',      price: 120, category: 'Herb Seeds',      img: 'css-f/img/herb1.jpg' },
    { id: 'idx-6', name: 'Fennel Leaf Seed',         price: 100, category: 'Herb Seeds',      img: 'css-f/img/herb2.jpg' },
];

// 3. Cart badge update
// Navbar par cart ki total quantity show karne ka function.
function updateCartBadge() {
    // Page ke tamam cart count badges select kar rahe hain.
    const badges = document.querySelectorAll('.cart-count');

    // Cart ke tamam items ki total quantity calculate kar rahe hain.
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);

    // Har badge mein total quantity display kar rahe hain.
    badges.forEach(badge => badge.textContent = totalQty);
}

// 4. Add to cart function
// Product ko cart mein add karne ka function.
function addToCart(productId) {
    // Product ID ke through product list se matching product find kar rahe hain.
    const product = products.find(p => p.id === productId);
    if (!product) return;

    // Check kar rahe hain ke product pehle se cart mein maujood hai ya nahi.
    const existingItem = cartItems.find(item => item.id === productId);

    if (existingItem) {
        // Agar product already cart mein hai to uski quantity increase kar rahe hain.
        existingItem.qty += 1;
    } else {
        // Agar product cart mein nahi hai to quantity 1 ke saath add kar rahe hain.
        cartItems.push({ ...product, qty: 1 });
    }

    // Updated cart ko LocalStorage mein save kar rahe hain.
    localStorage.setItem('tsCart', JSON.stringify(cartItems));

    // Cart badge ko updated quantity ke saath refresh kar rahe hain.
    updateCartBadge();
}

// 5. Page load pe sab setup karo
// DOM load hone ke baad cart aur buttons ki functionality initialize kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    // Page load par cart badge ki quantity update kar rahe hain.
    updateCartBadge();

    // Har "Add to Cart" button ko product se link karo
    // Page ke tamam Add to Cart buttons select kar rahe hain.
    document.querySelectorAll('.cart-btn').forEach((btn, index) => {
        btn.addEventListener('click', () => {
            // Button ke index se corresponding product ID bana rahe hain.
            const productId = 'idx-' + (index + 1);
            addToCart(productId);

            // Button feedback
            // Product add hone ke baad temporary success feedback show kar rahe hain.
            const originalText = btn.textContent;
            btn.textContent = '✓ Added!';
            btn.classList.add('active-btn');

            // 1.2 seconds baad button ko original state mein wapas la rahe hain.
            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('active-btn');
            }, 1200);
        });
    });

    // Buy Seeds button — scroll to products
    // Buy Seeds button aur product section ko select kar rahe hain.
    const buyBtn = document.querySelector('.btn-primary');
    const productSection = document.getElementById('product1');
    if (buyBtn && productSection) {
        // Button click par products section tak smooth scrolling kar rahe hain.
        buyBtn.addEventListener('click', () => {
            productSection.scrollIntoView({ behavior: 'smooth' });
        });
    }
});