// ============================================================
//   js-f/frucat.js — Fruit Seeds Page & Cart Count Logic
//   Yeh file fruit seeds page par cart count badge ko dynamically update karti hai
// ================================================================

// Page load hone ke baad cart count update kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    updateCartCount();

});

// Navbar ya cart badge mein total cart quantity show karne ka function.
function updateCartCount() {

    // LocalStorage se cart data load kar rahe hain; agar data na ho to empty array use hoga.
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];

    // Cart ke tamam items ki quantities ko add karke total quantity calculate kar rahe hain.
    const total = cart.reduce((sum, item) => sum + item.qty, 0);

    // Cart count badge ko select kar rahe hain.
    const badge = document.querySelector('.cart-count');

    // Agar badge page par available ho to calculated total display kar rahe hain.
    if (badge) badge.textContent = total;

}