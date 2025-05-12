function updateVideoStats(videoId) {
    const statsElement = document.querySelector('.like_share');
    if (statsElement) statsElement.classList.add('updating');
    
    fetch(`get_video_stats.php?video_id=${videoId}`)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;
                if (statsElement) {
                    statsElement.innerHTML = `
                        ${data.like_count} Likes | 
                        ${data.share_count} Shares | 
                        ${data.view_count} Views
                    `;
                }
            } else {
                console.error('Error:', result.message);
            }
        })
        .catch(error => console.error('Error fetching stats:', error))
        .finally(() => {
            if (statsElement) statsElement.classList.remove('updating');
        });
}