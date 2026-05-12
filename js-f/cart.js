// 1. Data load karna
let cartItems = JSON.parse(localStorage.getItem('tsCart')) || [];

function renderCart() {
    const cartList = document.getElementById('cartItemsList');
    const emptyMsg = document.getElementById('emptyCart');
    const actions = document.getElementById('cartActions');
    const cartCountNav = document.getElementById('cartCount');

    if (!cartList) return;

    // Navbar badge update
    const totalQty = cartItems.reduce((sum, item) => sum + item.qty, 0);
    if (cartCountNav) cartCountNav.textContent = totalQty;

    if (cartItems.length === 0) {
        cartList.innerHTML = "";
        if (emptyMsg) emptyMsg.style.display = "block";
        if (actions) actions.style.display = "none";
        document.getElementById('subtotalAmt').textContent = "Rs 0";
        document.getElementById('grandTotalAmt').textContent = "Rs 50"; // delivery bhi
        return;
    }

    if (emptyMsg) emptyMsg.style.display = "none";
    if (actions) actions.style.display = "flex";

    let listHTML = "";
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

    cartList.innerHTML = listHTML;
    calculateTotal();
}

window.updateQty = function(index, change) {
    if (cartItems[index].qty + change > 0) {
        cartItems[index].qty += change;
        saveData();
    }
};

window.deleteItem = function(index) {
    cartItems.splice(index, 1);
    saveData();
};

window.clearCart = function() {
    if (confirm("Kya aap cart khali karna chahte hain?")) {
        cartItems = [];
        saveData();
    }
};

function saveData() {
    localStorage.setItem('tsCart', JSON.stringify(cartItems));
    renderCart();
}

function calculateTotal() {
    const subtotal = cartItems.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const delivery = 50;
    document.getElementById('subtotalAmt').textContent = `Rs ${subtotal}`;
    document.getElementById('grandTotalAmt').textContent = `Rs ${subtotal + delivery}`;
}

document.addEventListener('DOMContentLoaded', renderCart);

function proceedToCheckout() {
    if (cartItems.length === 0) {
        alert("Your cart is empty! Please add items first.");
        return; // stop execution here
    }

    // if cart is not empty, go to checkout page
    window.location.href = "checkout.html";
}