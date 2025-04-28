<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Add this after require statements
if (isset($_GET['deleted'])) {
    $deletedId = intval($_GET['deleted']);
    // Force refresh of video list
    $query = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY uploaded_at DESC";
    $result = $conn->query($query);
}

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

// Check if current user can delete this video
$canDelete = false;
if (isLoggedIn() && $featuredVideo) {
    $user_id = $_SESSION['user_id'];
    // Allow deletion if user is owner or admin
    $canDelete = ($user_id == $featuredVideo['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
}


// Comments pagination
$commentsPerPage = 5;
$currentPage = isset($_GET['comments_page']) ? max(1, (int)$_GET['comments_page']) : 1;
$offset = ($currentPage - 1) * $commentsPerPage;

if ($featuredVideo) {
    // Get top-level comments count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM comments WHERE video_id = ? AND parent_id IS NULL";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("i", $featuredVideo['id']);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalComments = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalComments / $commentsPerPage);

    // Get paginated top-level comments for this video
    $commentsQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.video_id = ? AND c.parent_id IS NULL ORDER BY c.created_at DESC
                     LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($commentsQuery);
    $stmt->bind_param("iii", $featuredVideo['id'], $commentsPerPage, $offset);
    $stmt->execute();
    $commentsResult = $stmt->get_result();
}


function displayComment($comment, $conn, $depth = 0) {
    // Get comment likes count
    $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
    $stmt = $conn->prepare($likesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $likesResult = $stmt->get_result();
    $likeData = $likesResult->fetch_assoc();
    
    // Get replies (always load all replies for a parent comment)
    $repliesQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.parent_id = ? ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($repliesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $repliesResult = $stmt->get_result();
    
    $margin = $depth * 20;

    $canDeleteComment = false;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        // Allow deletion if user is comment author or admin
        $canDeleteComment = ($user_id == $comment['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
    }
    ?>
    <div class="comment" style="margin-left: <?= $margin ?>px;">
        <strong><?= htmlspecialchars($comment['username']) ?></strong>
        <p><?= htmlspecialchars($comment['comment']) ?></p>
        <small><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
        
        <?php if (isLoggedIn()): ?>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)">Reply</button>
                <button onclick="likeComment(<?= $comment['id'] ?>)">Like (<?= $likeData['like_count'] ?>)</button>
            <?php if ($canDeleteComment): ?>
                    <button onclick="confirmCommentDelete(<?= $comment['id'] ?>)" class="delete-comment-btn">Delete</button>
                <?php endif; ?>
            </div>
            <div id="reply-form-<?= $comment['id'] ?>" class="reply-form" style="display: none;">
                <form action="comments.php" method="POST">
                    <input type="hidden" name="video_id" value="<?= $comment['video_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php
        // Display replies recursively
        while ($reply = $repliesResult->fetch_assoc()) {
            displayComment($reply, $conn, $depth + 1);
        }
        ?>
    </div>
    <?php
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
            
            <!-- Actions on Video -->
    
            <div class="video-actions">
                <?php if (isLoggedIn()): ?>
                    <button id="like-button-<?= $featuredVideo['id'] ?>" onclick="likeVideo(<?= $featuredVideo['id'] ?>)">
                        👍 Like
                    </button>
                <?php endif; ?>
                
                <?php if (isLoggedIn()): ?>
                    <button id="share-button-<?= $featuredVideo['id'] ?>" onclick="shareVideo(<?= $featuredVideo['id'] ?>)">
                        🔗 Share
                    </button>
                <?php endif; ?>
                <?php if (isLoggedIn() && $canDelete): ?>
                    <button id="edit-button-<?= $featuredVideo['id'] ?>" onclick="showEditForm(<?= $featuredVideo['id'] ?>)" class="edit-btn">
                        ✏️ Edit
                    </button>
                <?php endif; ?>
                <?php if (isLoggedIn() && $canDelete): ?>
                <?php if ($canDelete): ?>
                    <button id="delete-button-<?= $featuredVideo['id'] ?>" onclick="confirmDelete(<?= $featuredVideo['id'] ?>)" class="delete-btn">
                        🗑️ Delete
                    </button>
                <?php endif; ?>
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

            <!-- EDIT MODAL -->
            <div id="edit-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">×</span>
                    <h3>Edit Video Information</h3>
                    <form id="edit-video-form">
                        <input type="hidden" id="edit-video-id" name="video_id">
                        <div class="form-group">
                            <label for="edit-title">Title</label>
                            <input type="text" id="edit-title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-description">Description</label>
                            <textarea id="edit-description" name="description" required></textarea>
                        </div>
                        <button type="submit">Save Changes</button>
                    </form>
                </div>
            </div>

        <!-- Updated Comments Section -->
        <div class="comments-section">
            <h4>Comments (<?= $totalComments ?>)</h4>
            
            <?php if (isLoggedIn()): ?>
                <form action="comments.php" method="POST">
                    <input type="hidden" name="video_id" value="<?= $featuredVideo['id'] ?>">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <button type="submit">Post Comment</button>
                </form>
            <?php else: ?>
                <p><a href="auth/login.php">Login</a> to post comments</p>
            <?php endif; ?>
            
            <div id="comments-container">
                <?php
                while ($comment = $commentsResult->fetch_assoc()) {
                    displayComment($comment, $conn);
                }
                ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <div class="comments-pagination">
                    <?php if ($currentPage > 1): ?>
                        <button onclick="loadComments(<?= $currentPage - 1 ?>)">Previous</button>
                    <?php endif; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <button onclick="loadComments(<?= $currentPage + 1 ?>)">Load More</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
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
            if ($featuredVideo && $video['id'] == $featuredVideo['id']) continue;
        ?>
            <div class="sidebar-video">
                <a href="?video_id=<?= $video['id'] ?>">
                    <!-- Keep your existing thumbnail -->
                    <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Video Thumbnail" class="video-thumb">
                    
                    <!-- Add hidden video element (same dimensions as thumbnail) -->
                    <video class="video-hover" 
                           muted 
                           loop 
                           playsinline 
                           preload="none"
                           style="display:none; border-radius: 5px; width:50%; height:auto;">
                        <source src="<?= VIDEO_UPLOAD_PATH . $video['video_file'] ?>" type="video/mp4">
                    </video>
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
    <h3>EARLIER</h3>
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
                <p><?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['uploaded_at']))) ?></p>
            </div>
        <?php endwhile; ?>
    </section>
</main>

<script>
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
        if (data.trim() === "success") {
            const likeButton = document.getElementById("like-button-" + videoId);
            likeButton.style.backgroundColor = "#ff0000";
            likeButton.innerText = "Liked";
            likeButton.disabled = true;
            likeButton.classList.add("liked");

            // UPDATE LIKES COUNT IN PAGE
            const likeShareText = document.querySelector(".like_share");
            if (likeShareText) {
                const parts = likeShareText.textContent.split('|');
                const likesPart = parts[0].trim();
                let currentLikes = parseInt(likesPart.replace('Likes:', '').trim());
                currentLikes += 1;
                likeShareText.innerHTML = `Likes: ${currentLikes} | ${parts[1].trim()}`;
            }
        }
    });
}

function shareVideo(videoId) {
    const shareLink = window.location.href.split('?')[0] + "?video_id=" + videoId;
    document.getElementById("share-link").value = shareLink;

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
            const shareButton = document.getElementById("share-button-" + videoId);
            if (shareButton) {
                shareButton.style.backgroundColor = "#008CBA";
                shareButton.innerText = "Shared";
            }

            // UPDATE SHARES COUNT IN PAGE
            const likeShareText = document.querySelector(".like_share");
            if (likeShareText) {
                const parts = likeShareText.textContent.split('|');
                const sharesPart = parts[1].trim();
                let currentShares = parseInt(sharesPart.replace('Shares:', '').trim());
                currentShares += 1;
                likeShareText.innerHTML = `${parts[0].trim()} | Shares: ${currentShares}`;
            }
        }
    });

    document.getElementById("share-modal").style.display = "block";
}


function confirmDelete(videoId) {
    if (confirm('Permanently delete this video?')) {
        fetch('delete_video.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Cache-Control': 'no-cache'
            },
            body: 'video_id=' + videoId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Force complete page reload with cache busting
                window.location.href = window.location.href.split('?')[0] + '?deleted=' + videoId + '&t=' + Date.now();
            } else {
                alert('Error: ' + (data.message || 'Deletion failed'));
            }
        });
    }
}

function closeModal() {
    document.getElementById("share-modal").style.display = "none";

    // Re-enable the share button for the current video
    const shareButton = document.querySelector('.shared'); // Find the shared button
    if (shareButton) {
        shareButton.disabled = false;
        shareButton.innerText = "🔗 Share"; // Reset the text
        shareButton.classList.remove("shared"); // Remove shared class
        shareButton.style.backgroundColor = ""; // Reset background color
    }
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
    
    mainVideo.addEventListener('loadedmetadata', syncVideos);
});

function adjustBlur(intensity) {
    const overlay = document.querySelector('.blur-overlay');
    overlay.style.backdropFilter = `blur(${intensity}px)`;
    overlay.style.webkitBackdropFilter = `blur(${intensity}px)`;
}

// comments 


// Add these new functions to your existing script section
function toggleReplyForm(commentId) {
    const form = document.getElementById("reply-form-" + commentId);
    form.style.display = form.style.display === "none" ? "block" : "none";
}

function likeComment(commentId) {
    fetch('comments.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'like_comment=1&comment_id=' + commentId
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            const buttons = document.querySelectorAll(`button[onclick="likeComment(${commentId})"]`);
            buttons.forEach(button => {
                const currentText = button.innerText;
                const currentCount = parseInt(currentText.match(/\((\d+)\)/)[1]) || 0;
                button.innerText = `Like (${currentCount + 1})`;
                button.disabled = true;
            });
        }
    });
}

function loadComments(page) {
    const videoId = <?= $featuredVideo['id'] ?>;
    const container = document.getElementById('comments-container');
    const loadButton = document.querySelector(`.comments-pagination button[onclick="loadComments(${page})"]`);
    
    if (loadButton) loadButton.textContent = 'Loading...';
    
    fetch(`load_comments.php?video_id=${videoId}&page=${page}`)
        .then(response => response.text())
        .then(html => {
            if (page > 1) {
                container.insertAdjacentHTML('beforeend', html);
            } else {
                container.innerHTML = html;
            }
            
            history.pushState(null, null, `?video_id=${videoId}&comments_page=${page}`);
            updatePaginationButtons(page);
        })
        .finally(() => {
            if (loadButton) loadButton.textContent = page > <?= $currentPage ?> ? 'Load More' : 'Previous';
        });
}

function updatePaginationButtons(currentPage) {
    const paginationDiv = document.querySelector('.comments-pagination');
    if (!paginationDiv) return;
    
    let html = '';
    if (currentPage > 1) {
        html += `<button onclick="loadComments(${currentPage - 1})">Previous</button>`;
    }
    if (currentPage < <?= $totalPages ?>) {
        html += `<button onclick="loadComments(${currentPage + 1})">Load More</button>`;
    }
    
    paginationDiv.innerHTML = html;
}

function showEditForm(videoId) {
    // Fetch current video info
    fetch(`get_video_info.php?video_id=${videoId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('edit-video-id').value = videoId;
            document.getElementById('edit-title').value = data.title;
            document.getElementById('edit-description').value = data.description;
            document.getElementById('edit-modal').style.display = 'block';
        });
}

function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

// Handle form submission
document.getElementById('edit-video-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('update_video.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update the displayed info without page reload
            document.querySelector('.video-player h3').textContent = data.title;
            document.querySelector('.video-player p:nth-of-type(1)').textContent = data.description;
            closeEditModal();
            alert('Video information updated successfully!');
        } else {
            closeEditModal();
            alert(data.message || 'Update failed');
        }
    })
    .catch(error => {
        closeEditModal();
        alert('An error occurred. Please try again.');
    });
});

function confirmCommentDelete(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        fetch('delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'comment_id=' + commentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Remove the comment from the DOM
                const commentElement = document.querySelector(`.comment [onclick="confirmCommentDelete(${commentId})"]`).closest('.comment');
                if (commentElement) {
                    commentElement.remove();
                }
                // Update comments count
                const commentsCountElement = document.querySelector('.comments-section h4');
                if (commentsCountElement) {
                    const currentCount = parseInt(commentsCountElement.textContent.match(/\d+/)[0]) || 0;
                    commentsCountElement.textContent = commentsCountElement.textContent.replace(/\d+/, currentCount - 1);
                }
            } else {
                alert('Error: ' + (data.message || 'Failed to delete comment'));
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const videoItems = document.querySelectorAll('.sidebar-video');
    
    videoItems.forEach(item => {
        const thumb = item.querySelector('.video-thumb');
        const video = item.querySelector('.video-hover');
        
        item.addEventListener('mouseenter', function() {
            thumb.style.display = 'none';
            video.style.display = 'block';
            video.play().catch(e => console.log('Autoplay prevented:', e));
        });
        
        item.addEventListener('mouseleave', function() {
            thumb.style.display = 'block';
            video.style.display = 'none';
            video.pause();
            video.currentTime = 0;
        });
    });
});
</script>
<?php require_once 'includes/footer.php'; ?>