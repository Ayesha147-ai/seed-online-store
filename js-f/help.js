// ============================================================
//   js-f/help.js — Help FAQ Accordion Logic
//   Yeh file help page par FAQ items ko dynamically expand aur collapse karne ka kaam karti hai
// ============================================================

// Page load hone ke baad FAQ buttons ko select karke click events add kar rahe hain.
document.addEventListener('DOMContentLoaded', () => {

    // Har FAQ question button par click event attach kar rahe hain.
    document.querySelectorAll('.faq-question').forEach(btn => {

        // Button click hone par related FAQ item ko open ya close kar rahe hain.
        btn.addEventListener('click', () => {

            // Clicked button ke nearest FAQ item ko find kar rahe hain.
            const item = btn.closest('.faq-item');

            // 'open' class toggle karke FAQ answer ko expand/collapse kar rahe hain.
            item.classList.toggle('open');

        });

    });

});