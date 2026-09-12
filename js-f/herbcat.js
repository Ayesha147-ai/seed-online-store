// ============================================================
//   js-f/herbcat.js — Herb Seeds Page & Cart Count Logic
//   Yeh file herb seeds page par cart count badge ko dynamically update karti hai
// ============================================================

// Page load hone ke baad cart count ko update kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    // LocalStorage se cart ki quantity count karne ke liye function call kar rahe hain.
    updateCartCount();

});

// Cart mein total items ki quantity ko navbar badge mein show karne ka function.
function updateCartCount() {

    // LocalStorage se cart data le rahe hain, agar data na mile to empty array use hoga.
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];

    // Cart ke tamam items ki quantity ko add karke total nikal rahe hain.
    const total = cart.reduce((sum, item) => sum + item.qty, 0);

    // Navbar mein cart count wala element select kar rahe hain.
    const badge = document.querySelector('.cart-count');

    // Agar cart count element exist karta hai to usmein total quantity show kar rahe hain.
    if (badge) badge.textContent = total;

}