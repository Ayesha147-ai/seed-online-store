// ============================================
//   FRUCAT.JS — Fruit Seeds Page
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];
    const total = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = total;
}