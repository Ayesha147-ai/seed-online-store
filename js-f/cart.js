// ============================================================
//   js-f/cart.js — Shopping Cart Management
//   Yeh file user ke shopping cart items, quantities, aur totals ko manage karti hai
// ============================================================
// 1. Data load karna
// LocalStorage se cart data read kar rahe hain; agar data na mile to empty array use hoga.
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

function renderCart() {
    // Cart ke relevant HTML elements ko select kar rahe hain.
    const cartList = document.getElementById('cartItemsList');
    const emptyMsg = document.getElementById('emptyCart');
    const actions = document.getElementById('cartActions');
    const cartCountNav = document.getElementById('cartCount');

    // Agar cart list ka element page par nahi hai to function yahin stop ho jayega.
    if (!cartList) return;

    // Navbar badge update
    // Cart ki total quantity calculate karke navbar badge mein show kar rahe hain.
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    if (cartCountNav) cartCountNav.textContent = totalQty;

    // Agar cart empty hai to empty-cart message show aur actions hide kar rahe hain.
    if (cartItems.length === 0) {
        cartList.innerHTML = "";
        if (emptyMsg) emptyMsg.style.display = "block";
        if (actions) actions.style.display = "none";
        document.getElementById('subtotalAmt').textContent = "Rs 0";
        document.getElementById('grandTotalAmt').textContent = "Rs 50"; // delivery bhi
        return;
    }

    // Cart empty nahi hai, is liye empty message hide aur cart actions show kar rahe hain.
    if (emptyMsg) emptyMsg.style.display = "none";
    if (actions) actions.style.display = "flex";

    let listHTML = "";

    // Har cart item ka HTML dynamically generate kar rahe hain.
    cartItems.forEach((item, index) => {
        listHTML += `
            <div class="cart-item-row">
                <div class="item-product">
                    <img src="${item.img}" class="item-img">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-cat">${item.category || 'Seeds'}</div>
                    </div>
                </div>
                <div class="item-price">Rs ${item.price}</div>
                <div class="qty-box">
                    <button class="qty-btn" onclick="updateQty(${index}, -1)">-</button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn" onclick="updateQty(${index}, 1)">+</button>
                </div>
                <div class="item-total">Rs ${item.price * item.qty}</div>
                <button class="remove-btn" onclick="deleteItem(${index})">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;
    });

    // Generated cart HTML ko page par display kar rahe hain.
    cartList.innerHTML = listHTML;
    calculateTotal();
}

// Quantity ko increase ya decrease karne ka function.
window.updateQty = function(index, change) {
    // Quantity ko zero ya negative hone se rok rahe hain.
    if (cartItems[index].qty + change > 0) {
        cartItems[index].qty += change;
        saveData();
    }
};

// Cart se selected item remove karne ka function.
window.deleteItem = function(index) {
    cartItems.splice(index, 1);
    saveData();
};

// Pura cart empty karne ka function.
window.clearCart = function() {
    if (confirm("Kya aap cart khali karna chahte hain?")) {
        cartItems = [];
        saveData();
    }
};

// Updated cart data ko LocalStorage mein save karke cart dobara render kar rahe hain.
function saveData() {
    localStorage.setItem('tsCart', JSON.stringify(cartItems));
    renderCart();
}

// Subtotal aur delivery charge ke basis par grand total calculate kar rahe hain.
function calculateTotal() {
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const delivery = 50;
    document.getElementById('subtotalAmt').textContent = `Rs ${subtotal}`;
    document.getElementById('grandTotalAmt').textContent = `Rs ${subtotal + delivery}`;
}

// Page load hone ke baad cart ko render kar rahe hain.
document.addEventListener('DOMContentLoaded', renderCart);

function proceedToCheckout() {
    // Checkout par jaane se pehle check kar rahe hain ke cart empty to nahi.
    if (cartItems.length === 0) {
        alert("Your cart is empty! Please add items first.");
        return; // stop execution here
    }

    // if cart is not empty, go to checkout page
    window.location.href = "checkout.html";
}