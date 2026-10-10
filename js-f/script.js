// ============================================
//   SCRIP.JS — Index Page (Home)
// ============================================


// ============================================================
//   Session check — har page load pe chalega
//   Agent/Admin ko "Hi, naam" aur "My Cart" nahi dikhne chahiye
//   (unka shopping cart hota hi nahi) — sirf Dashboard/My Account/Logout
// ============================================================
fetch('includes/check-session.php')
    .then(res => res.json())
    .then(data => {
        const authBtns = document.getElementById('auth-buttons');
        const userInfo = document.getElementById('user-info');
        const greeting = document.getElementById('user-greeting');
        const dashLink = document.getElementById('dashboard-link');
        const cartBtn  = document.querySelector('.cart-btn-nav');

        if (data.logged_in && authBtns && userInfo) {
            authBtns.style.display = 'none';
            userInfo.style.display = 'flex';

            const isStaff = (data.role === 'agent' || data.role === 'admin');

            // Greeting — ab kisi bhi role ke liye nahi dikhana
            if (greeting) {
                greeting.style.display = 'none';
            }

            // My Cart — sirf farmer ke liye dikhe
            if (cartBtn) {
                cartBtn.style.display = isStaff ? 'none' : '';
            }

            // Dashboard link — sirf agent/admin ke liye
            if (dashLink) {
                if (data.role === 'agent') {
                    dashLink.href = 'agent-dashboard.html';
                    dashLink.style.display = 'inline-block';
                } else if (data.role === 'admin') {
                    dashLink.href = 'admin-dashboard.html';
                    dashLink.style.display = 'inline-block';
                } else {
                    dashLink.style.display = 'none';
                }
            }

            // "Register as Agent" button — state ke hisaab se dikhana/chhupana
            checkAgentButtonVisibility();
        }  else {
            // Logged out — simple navbar dikhao
            if (authBtns) authBtns.style.display = 'flex';
            if (userInfo) userInfo.style.display = 'none';
            if (cartBtn)  cartBtn.style.display = '';

            // Bina login — button chhupa rakho
            const agentBtn = document.getElementById('register-agent-btn');
            if (agentBtn) agentBtn.style.display = 'none';
        }
    })
    .catch(err => console.log('Session check failed:', err));

// ===== REGISTER AS AGENT BUTTON — 4-state visibility check =====
function checkAgentButtonVisibility() {
    const agentBtn = document.getElementById('register-agent-btn');
    if (!agentBtn) return;

    fetch('includes/get-agent-status.php')
        .then(res => res.json())
        .then(data => {
            agentBtn.style.display = data.show_button ? 'inline-flex' : 'none';
        })
        .catch(() => {
            agentBtn.style.display = 'none';
        });
}


// 1. Cart data initialization
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

// Cart badge update
function updateCartBadge() {
    const badges = document.querySelectorAll('.cart-count');
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    badges.forEach(badge => badge.textContent = totalQty);
}

function syncCartFromStorage() {
    cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];
    updateCartBadge();
}

window.addEventListener('pageshow', syncCartFromStorage);
window.addEventListener('storage', event => {
    if (event.key === 'tsCart') syncCartFromStorage();
});

// Database product ko cart mein add karo.
function addToCart(product) {
    const productId = `db-${product.id}`;
    const existingItem = cartItems.find(item => item.id === productId);
    if (existingItem) {
        existingItem.qty += 1;
    } else {
        cartItems.push({
            id: productId,
            name: product.name,
            price: Number(product.price),
            category: product.category,
            img: product.image || product.fallbackImage,
            qty: 1
        });
    }

    localStorage.setItem('tsCart', JSON.stringify(cartItems));
    updateCartBadge();
}

async function loadHomeProducts() {
    const container = document.querySelector('#product1 .pro-container');
    if (!container) return;

    const categories = [
        { name: 'Vegetable', label: 'Vegetable Seeds', fallbackImage: 'css-f/img/v1.jpg' },
        { name: 'Fruit', label: 'Fruit Seeds', fallbackImage: 'css-f/img/fru1.jpg' },
        { name: 'Herb', label: 'Herb Seeds', fallbackImage: 'css-f/img/herb1.jpg' }
    ];

    try {
        const results = await Promise.all(categories.map(async category => {
            const response = await fetch(`includes/get-category-products.php?category=${category.name}`);
            if (!response.ok) throw new Error(`Could not load ${category.name.toLowerCase()} seeds (HTTP ${response.status}).`);
            const products = await response.json();
            if (!Array.isArray(products)) throw new Error(`Invalid ${category.name.toLowerCase()} seed response.`);
            return products.map(product => ({
                ...product,
                category: category.label,
                fallbackImage: category.fallbackImage
            }));
        }));

        const products = results.flatMap(categoryProducts => categoryProducts.slice(0, 2));
        container.replaceChildren();

        if (products.length === 0) {
            container.textContent = 'No approved seeds are available right now.';
            return;
        }

        products.forEach(product => {
            const card = document.createElement('div');
            card.className = 'pro';

            const image = document.createElement('img');
            image.src = product.image || product.fallbackImage;
            image.alt = product.name;
            image.onerror = () => {
                image.onerror = null;
                image.src = product.fallbackImage;
            };

            const description = document.createElement('div');
            description.className = 'des';

            const category = document.createElement('span');
            category.textContent = product.category;

            const name = document.createElement('h5');
            name.textContent = product.name;

            const price = document.createElement('h4');
            price.textContent = `Rs ${product.price}`;

            const button = document.createElement('button');
            button.className = 'cart-btn';
            button.textContent = 'Add to Cart';
            button.addEventListener('click', () => {
                addToCart(product);
                button.textContent = '✓ Added!';
                button.classList.add('active-btn');
                setTimeout(() => {
                    button.textContent = 'Add to Cart';
                    button.classList.remove('active-btn');
                }, 1200);
            });

            description.append(category, name, price);
            card.append(image, description, button);
            container.appendChild(card);
        });
    } catch (err) {
        console.error('Home products failed to load:', err);
        container.textContent = 'Could not load seeds. Please refresh the page and try again.';
    }
}

// Page load par cart aur homepage products initialize karo.
document.addEventListener('DOMContentLoaded', () => {

    syncCartFromStorage();
    loadHomeProducts();

    // Buy Seeds button — scroll to products
    const buyBtn = document.querySelector('.btn-primary');
    const productSection = document.getElementById('product1');
    if (buyBtn && productSection) {
        buyBtn.addEventListener('click', () => {
            productSection.scrollIntoView({ behavior: 'smooth' });
        });
    }
});