// ============================================================
//   js-f/vegcat.js — Vegetable Seeds Category Page Cart Logic
//   Yeh file vegetable seeds category page par localStorage se cart items read karke navbar badge ki quantity update karne ka kaam karti hai
// ============================================================

// Cart count update karo localStorage se
// Page load hone ke baad cart ki current quantity update kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    // Cart count function call kar rahe hain.
    updateCartCount();

});

// LocalStorage se cart items read karke total quantity calculate karne ka function.
function updateCartCount() {

    // LocalStorage se saved cart data le rahe hain.
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];

    // Cart ke tamam products ki total quantity calculate kar rahe hain.
    const total = cart.reduce((sum, item) => sum + item.qty, 0);

    // Navbar mein cart count badge select kar rahe hain.
    const badge = document.querySelector('.cart-count');

    // Agar cart badge available hai to usmein total quantity show kar rahe hain.
    if (badge) badge.textContent = total;

}