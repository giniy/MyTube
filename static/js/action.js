function likeVideo(videoId) {
    fetch('like_video.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'video_id=' + videoId
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "success"){
            alert("Liked/Unliked successfully!");
            location.reload(); // Reload to update like count (optional)
        }
    });
}
function shareVideo(videoId) {
    fetch('share_video.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'video_id=' + videoId
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "shared"){
            alert("Thanks for sharing!");
            location.reload(); // Reload to update share count (optional)
        }
    });
}