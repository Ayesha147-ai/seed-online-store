// Added: shared feedback loading, star rating, and submission behavior.
let feedbackProductId = null;
let selectedRating = 0;

function loadFeedback(productId) {
    const list = document.getElementById('feedback-list');
    const summary = document.getElementById('review-summary');

    list.replaceChildren();
    const loading = document.createElement('p');
    loading.className = 'feedback-loading';
    loading.textContent = 'Loading feedback...';
    list.appendChild(loading);

    fetch('feedback/get-product-feedback.php?product_id=' + encodeURIComponent(productId))
        .then(response => {
            if (!response.ok) throw new Error('Could not load feedback');
            return response.json();
        })
        .then(data => {
            const reviews = Array.isArray(data.reviews) ? data.reviews : [];
            summary.textContent = `${data.avg_rating || 0} / 5 from ${data.count || 0} review${data.count === 1 ? '' : 's'}`;
            list.replaceChildren();

            if (reviews.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'feedback-empty';
                empty.textContent = 'No feedback yet. Be the first to share your experience.';
                list.appendChild(empty);
                return;
            }

            reviews.forEach(review => {
                const item = document.createElement('article');
                item.className = 'review-item';

                const name = document.createElement('p');
                name.className = 'review-name';
                name.textContent = review.farmer_name || 'Customer';

                const stars = document.createElement('p');
                stars.className = 'review-stars';
                const rating = Math.min(5, Math.max(0, Number(review.rating) || 0));
                stars.textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);

                const comment = document.createElement('p');
                comment.className = 'review-comment';
                comment.textContent = review.comment || 'No comment provided.';

                item.append(name, stars, comment);
                list.appendChild(item);
            });
        })
        .catch(() => {
            summary.textContent = '';
            list.replaceChildren();
            const error = document.createElement('p');
            error.className = 'feedback-empty';
            error.textContent = 'Feedback could not be loaded right now.';
            list.appendChild(error);
        });
}

function showFeedback(productId) {
    const section = document.getElementById('feedback-section');
    feedbackProductId = productId || null;
    section.hidden = !feedbackProductId;

    if (feedbackProductId) loadFeedback(feedbackProductId);
}

function selectRating(rating) {
    selectedRating = rating;
    document.querySelectorAll('.star-button').forEach(button => {
        const buttonRating = Number(button.dataset.rating);
        const isSelected = buttonRating <= rating;
        button.classList.toggle('selected', isSelected);
        button.setAttribute('aria-checked', isSelected && buttonRating === rating ? 'true' : 'false');
    });
}

function setupFeedbackForm() {
    document.querySelectorAll('.star-button').forEach(button => {
        button.addEventListener('click', () => selectRating(Number(button.dataset.rating)));
    });

    document.getElementById('feedback-form').addEventListener('submit', event => {
        event.preventDefault();
        const message = document.getElementById('feedback-message');
        const submitButton = event.target.querySelector('.feedback-submit');

        message.className = 'feedback-message';
        if (!selectedRating) {
            message.classList.add('error');
            message.textContent = 'Please choose a star rating.';
            return;
        }

        const formData = new FormData(event.target);
        formData.append('product_id', feedbackProductId);
        formData.append('rating', selectedRating);
        submitButton.disabled = true;

        fetch('feedback/submit-feedback.php', { method: 'POST', body: formData })
            .then(response => response.json().then(data => ({ response, data })))
            .then(({ response, data }) => {
                if (!response.ok || !data.success) throw new Error(data.msg || data.error || 'Feedback could not be submitted.');
                message.classList.add('success');
                message.textContent = data.msg;
                event.target.reset();
                selectRating(0);
                loadFeedback(feedbackProductId);
            })
            .catch(error => {
                message.classList.add('error');
                message.textContent = error.message;
            })
            .finally(() => {
                submitButton.disabled = false;
            });
    });
}

setupFeedbackForm();
