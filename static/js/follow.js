function toggleSubscription(userId) {
    const button = document.querySelector(`button.subscribe-btn[data-user-id="${userId}"]`);
    if (!button) return;

    button.disabled = true;
    const originalText = button.textContent;
    button.textContent = 'Processing...';

    fetch('follow_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `following_id=${userId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            button.textContent = data.action === 'followed' ? 'Subscribed' : 'Subscribe';
            if (data.action === 'followed') {
                button.style.backgroundColor = '#4CAF50';
                button.style.color = 'white';
            } else {
                button.style.backgroundColor = '';
                button.style.color = '';
            }
        } else {
            alert(data.message || 'Error processing subscription');
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.textContent = originalText;
    })
    .finally(() => {
        button.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const subscriptionsContainer = document.querySelector('.subscriptions-container');
    if (subscriptionsContainer) {
        // Prevent vertical scroll when horizontal scrolling
        subscriptionsContainer.addEventListener('wheel', function(e) {
            if (Math.abs(e.deltaX) < Math.abs(e.deltaY)) {
                e.preventDefault();
                this.scrollLeft += e.deltaY;
            }
        });

        // Add shadow indicators when scrolled
        function updateScrollIndicators() {
            const scrollLeft = this.scrollLeft;
            const scrollWidth = this.scrollWidth;
            const clientWidth = this.clientWidth;
            
            if (scrollLeft > 10) {
                this.classList.add('scrolled');
            } else {
                this.classList.remove('scrolled');
            }
            
            if (scrollLeft + clientWidth < scrollWidth - 10) {
                this.classList.add('can-scroll-right');
            } else {
                this.classList.remove('can-scroll-right');
            }
        }

        subscriptionsContainer.addEventListener('scroll', updateScrollIndicators);
        updateScrollIndicators.call(subscriptionsContainer);
    }
});