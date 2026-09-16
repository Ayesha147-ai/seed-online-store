// ============================================
//   VEGCAT.JS — Vegetable Seeds Page
// ============================================

// Cart count update karo localStorage se
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('tsCart')) || [];
    const total = cart.reduce((sum, item) => sum + item.qty, 0);
    const badge = document.querySelector('.cart-count');
    if (badge) badge.textContent = total;
}
document.getElementById("submitReview").addEventListener("click", async () => {
    const rating = document.getElementById("rating").value;
    const reviewText = document.getElementById("reviewText").value;

    if (!rating || !reviewText.trim()) {
        alert("Please provide a rating and review.");
        return;
    }

    const response = await fetch("api/add-review.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            product_id: productId,
            rating: rating,
            review: reviewText
        })
    });

    const result = await response.json();

    if (result.success) {
        alert("Thanks! Your review has been submitted.");
    } else {
        alert(result.message);
    }
});
