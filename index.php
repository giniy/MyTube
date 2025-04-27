<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get all videos
$query = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY uploaded_at DESC";
$result = $conn->query($query);

// Check if a specific video ID was requested
$featuredVideo = null;
if (isset($_GET['video_id'])) {
    $video_id = intval($_GET['video_id']);
    $featuredQuery = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id WHERE v.id = ?";
    $stmt = $conn->prepare($featuredQuery);
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $featuredResult = $stmt->get_result();
    $featuredVideo = $featuredResult->fetch_assoc();
}

// If no specific video or invalid ID, get the most recent video
if (!$featuredVideo) {
    $featuredQuery = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY uploaded_at DESC LIMIT 1";
    $featuredResult = $conn->query($featuredQuery);
    $featuredVideo = $featuredResult->fetch_assoc();
}
?>

<div class="video-page-background">
    <video id="background-video" autoplay muted loop>
        <source src="<?= VIDEO_UPLOAD_PATH . $featuredVideo['video_file'] ?>" type="video/mp4">
    </video>
    <div class="blur-overlay"></div>
</div>

<main>
    <div class="video-container">
        <?php if ($featuredVideo): ?>
        <section class="video-player">
            <video id="main-video" controls autoplay muted>
                <source src="<?= VIDEO_UPLOAD_PATH . $featuredVideo['video_file'] ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <h3><?= htmlspecialchars($featuredVideo['title']) ?></h3>
            <p><?= htmlspecialchars($featuredVideo['description']) ?></p>
            <p>Uploaded by: <?= htmlspecialchars($featuredVideo['username']) ?></p>  
        
            <div class="video-actions">
                <?php if (isLoggedIn()): ?>
                    <button id="like-button-<?= $featuredVideo['id'] ?>" onclick="likeVideo(<?= $featuredVideo['id'] ?>)">
                        👍 Like
                    </button>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <!-- SHARE BUTTON -->
                    <button id="share-button-<?= $featuredVideo['id'] ?>" onclick="shareVideo(<?= $featuredVideo['id'] ?>)">
                        🔗 Share
                    </button>
                <?php endif; ?>

                

                <?php
                // Get likes count
                $likesQuery = "SELECT COUNT(*) as like_count FROM likes WHERE video_id = ?";
                $stmt = $conn->prepare($likesQuery);
                $stmt->bind_param("i", $featuredVideo['id']);
                $stmt->execute();
                $likesResult = $stmt->get_result();
                $likeData = $likesResult->fetch_assoc();
                ?>
                <p class="like_share">Likes: <?= $likeData['like_count'] ?> | Shares: <?= $featuredVideo['share_count'] ?></p>
            </div>
            <!-- SHARE MODAL -->
            <div id="share-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">×</span>
                    <input type="text" id="share-link" readonly style="width: 100%;">
                    <button onclick="copyLink()">Copy Link</button>
                </div>
            </div>
            <!-- Comments Section -->
            <div class="comments-section">
                <h4>Comments</h4>
                <?php if (isLoggedIn()): ?>
                    <form action="comments.php" method="POST">
                        <input type="hidden" name="video_id" value="<?= $featuredVideo['id'] ?>">
                        <textarea name="comment" placeholder="Add a comment..." required></textarea>
                        <button type="submit">Post Comment</button>
                    </form>
                <?php else: ?>
                    <p><a href="auth/login.php">Login</a> to post comments</p>
                <?php endif; ?>
                
                <?php
                // Get comments for this video
                $commentsQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                                 WHERE c.video_id = ? ORDER BY c.created_at DESC";
                $stmt = $conn->prepare($commentsQuery);
                $stmt->bind_param("i", $featuredVideo['id']);
                $stmt->execute();
                $commentsResult = $stmt->get_result();
                
                while ($comment = $commentsResult->fetch_assoc()): ?>
                    <div class="comment">
                        <strong><?= htmlspecialchars($comment['username']) ?></strong>
                        <p><?= htmlspecialchars($comment['comment']) ?></p>
                        <small><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
        <?php endif; ?>

        <aside class="video-sidebar">
            <h3>More Videos</h3>
            <div class="sidebar-video-list">
                <?php 
                // Reset the result pointer to loop through videos again
                $result->data_seek(0);
                while ($video = $result->fetch_assoc()): 
                    if ($featuredVideo && $video['id'] == $featuredVideo['id']) continue; // Skip the currently playing video
                ?>
                    <div class="sidebar-video">
                        <a href="?video_id=<?= $video['id'] ?>">
                            <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Video Thumbnail">
                            <div class="sidebar-video-info">
                                <h4><?= htmlspecialchars($video['title']) ?></h4>
                                <p><?= htmlspecialchars($video['username']) ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </aside>
    </div>

    <section class="video-list">
        <?php 
        // Reset the result pointer again for the bottom grid
        $result->data_seek(0);
        while ($video = $result->fetch_assoc()): ?>
            <div class="video-thumbnail">
                <a href="?video_id=<?= $video['id'] ?>">
                    <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Video Thumbnail">
                </a>
                <h3><?= htmlspecialchars($video['title']) ?></h3>
                <p><?= htmlspecialchars($video['description']) ?></p>
                <p>Uploaded by: <?= htmlspecialchars($video['username']) ?></p>
            </div>
        <?php endwhile; ?>
    </section>
</main>

<script>
function likeVideo(videoId) {
    // Make AJAX request to like the video
    fetch('like_video.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'video_id=' + videoId
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            const likeButton = document.getElementById("like-button-" + videoId);
            likeButton.style.backgroundColor = "#ff0000"; // Change to red or any color
            likeButton.innerText = "Liked"; // Update text
            likeButton.disabled = true; // Disable the button after click (optional)
            // You can add animation like a pulse here
            likeButton.classList.add("liked"); // Apply class for pulse animation
        }
    });
}

function shareVideo(videoId) {
    // First, generate the share link
    const shareLink = window.location.href.split('?')[0] + "?video_id=" + videoId;
    document.getElementById("share-link").value = shareLink;
    
    // Now send a POST request to increase share count
    fetch('share_video.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            // Optionally, you can change the Share button style here
            const shareButton = document.getElementById("share-button-" + videoId);
            if (shareButton) {
                shareButton.style.backgroundColor = "#008CBA"; // Blue
                shareButton.innerText = "Shared";
                shareButton.disabled = true;
                shareButton.classList.add("shared"); // Optional class for animation
            }
        } else {
            console.error('Share failed:', data);
        }
    })
    .catch(error => {
        console.error('Error sharing:', error);
    });

    // Finally, show the Share modal
    document.getElementById("share-modal").style.display = "block";
}

function closeModal() {
    document.getElementById("share-modal").style.display = "none";
}

function copyLink() {
    const shareLink = document.getElementById("share-link");
    shareLink.select();
    shareLink.setSelectionRange(0, 99999);
    document.execCommand("copy");
    closeModal();
}


// Sync background video with main video
document.addEventListener('DOMContentLoaded', function() {
    const mainVideo = document.getElementById('main-video');
    const bgVideo = document.getElementById('background-video');
    
    // Sync playback
    function syncVideos() {
        bgVideo.currentTime = mainVideo.currentTime;
    }
    
    mainVideo.addEventListener('play', function() {
        syncVideos();
        bgVideo.play();
    });
    
    mainVideo.addEventListener('pause', function() {
        bgVideo.pause();
    });
    
    mainVideo.addEventListener('seeked', syncVideos);
    
    mainVideo.addEventListener('ended', function() {
        bgVideo.currentTime = 0;
        bgVideo.pause();
    });
    
    // Handle video source changes
    mainVideo.addEventListener('loadedmetadata', syncVideos);
});



// JavaScript function
function adjustBlur(intensity) {
    const overlay = document.querySelector('.blur-overlay');
    overlay.style.backdropFilter = `blur(${intensity}px)`;
    overlay.style.webkitBackdropFilter = `blur(${intensity}px)`;
}
</script>
<?php require_once 'includes/footer.php'; ?>

<style>

</style>