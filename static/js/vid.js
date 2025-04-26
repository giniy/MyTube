document.addEventListener('DOMContentLoaded', function() {
    // Handle video thumbnail clicks
    const thumbnails = document.querySelectorAll('.video-thumbnail');
    const mainVideo = document.getElementById('main-video');
    const videoSource = mainVideo.querySelector('source');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            const videoSrc = this.getAttribute('data-video-src');
            videoSource.setAttribute('src', videoSrc);
            mainVideo.load();
            mainVideo.play();
            
            // Scroll to the video player
            mainVideo.scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    // Handle search
    const searchButton = document.getElementById('search-button');
    if (searchButton) {
        searchButton.addEventListener('click', function() {
            const searchTerm = document.getElementById('search-input').value.trim();
            if (searchTerm) {
                window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
            }
        });
    }
});