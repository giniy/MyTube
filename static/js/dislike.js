function dislikeVideo(videoId) {
    const dislikeButton = document.getElementById(`dislike-button-${videoId}`);
    if (!dislikeButton) {
        console.error(`Dislike button with ID dislike-button-${videoId} not found`);
        return;
    }

    fetch('dislike_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + videoId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            dislikeButton.style.backgroundColor = '#ff4444'; // Red to indicate disliked
            dislikeButton.innerHTML = '<i class="fa fa-thumbs-down" aria-hidden="true"></i> Disliked';
            dislikeButton.disabled = true;
            dislikeButton.classList.add('disliked');

            // Update the video stats
            const statsElement = document.getElementById(`video-stats-${videoId}`);
            if (statsElement) {
                const parts = statsElement.textContent.split('|');
                const currentLikes = parseInt(parts[0].trim()) || 0;
                const currentShares = parseInt(parts[1].trim()) || 0;
                const currentViews = parseInt(parts[2].trim()) || 0;
                statsElement.textContent = `${currentLikes} Likes | ${currentShares} Shares | ${currentViews} Views | ${data.dislike_count} Dislikes`;
            }
        } else {
            showNotification(dislikeButton, data.message || 'Failed to dislike video');
        }
    })
    .catch(error => {
        console.error('Error disliking video:', error);
        showNotification(dislikeButton, 'Failed to dislike video');
    });
}