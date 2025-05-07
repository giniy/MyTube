<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Add this after require statements
if (isset($_GET['deleted'])) {
    $deletedId = intval($_GET['deleted']);
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

    if (isLoggedIn() && $featuredVideo) {
        $user_id = $_SESSION['user_id'];
        $viewQuery = "INSERT INTO video_views (user_id, video_id, viewed_at) VALUES (?, ?, NOW()) 
                      ON DUPLICATE KEY UPDATE viewed_at = NOW()";
        $viewStmt = $conn->prepare($viewQuery);
        $viewStmt->bind_param("ii", $user_id, $video_id);
        $viewStmt->execute();
    }
}

if (!$featuredVideo) {
    $featuredQuery = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY uploaded_at DESC LIMIT 1";
    $featuredResult = $conn->query($featuredQuery);
    $featuredVideo = $featuredResult->fetch_assoc();
}

$canDelete = false;
if (isLoggedIn() && $featuredVideo) {
    $user_id = $_SESSION['user_id'];
    $canDelete = ($user_id == $featuredVideo['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
}

$commentsPerPage = 5;
$currentPage = isset($_GET['comments_page']) ? max(1, (int)$_GET['comments_page']) : 1;
$offset = ($currentPage - 1) * $commentsPerPage;

if ($featuredVideo) {
    $countQuery = "SELECT COUNT(*) as total FROM comments WHERE video_id = ? AND parent_id IS NULL";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param("i", $featuredVideo['id']);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalComments = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalComments / $commentsPerPage);

    $commentsQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.video_id = ? AND c.parent_id IS NULL ORDER BY c.created_at DESC
                     LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($commentsQuery);
    $stmt->bind_param("iii", $featuredVideo['id'], $commentsPerPage, $offset);
    $stmt->execute();
    $commentsResult = $stmt->get_result();
}

$videosPerPage = 4;
$watchedVideos = [];
$totalWatchedVideos = 0;

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $watchedCountQuery = "SELECT COUNT(*) as total FROM video_views WHERE user_id = ?";
    $watchedCountStmt = $conn->prepare($watchedCountQuery);
    $watchedCountStmt->bind_param("i", $user_id);
    $watchedCountStmt->execute();
    $watchedCountResult = $watchedCountStmt->get_result();
    $totalWatchedVideos = $watchedCountResult->fetch_assoc()['total'];

    $watchedQuery = "SELECT v.*, u.username, vv.viewed_at 
                    FROM video_views vv 
                    JOIN videos v ON vv.video_id = v.id 
                    JOIN users u ON v.user_id = u.id 
                    WHERE vv.user_id = ? 
                    ORDER BY vv.viewed_at DESC 
                    LIMIT ?";
    $watchedStmt = $conn->prepare($watchedQuery);
    $watchedStmt->bind_param("ii", $user_id, $videosPerPage);
    $watchedStmt->execute();
    $watchedResult = $watchedStmt->get_result();
    while ($video = $watchedResult->fetch_assoc()) {
        $watchedVideos[] = $video;
    }
}

function displayComment($comment, $conn, $depth = 0) {
    // Get like count
    $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
    $stmt = $conn->prepare($likesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $likesResult = $stmt->get_result();
    $likeData = $likesResult->fetch_assoc();

    // Get users who liked the comment (limit to 5 for performance)
    $likersQuery = "SELECT u.username FROM comment_likes cl JOIN users u ON cl.user_id = u.id 
                    WHERE cl.comment_id = ? ORDER BY cl.created_at DESC LIMIT 5";
    $likerStmt = $conn->prepare($likersQuery);
    $likerStmt->bind_param("i", $comment['id']);
    $likerStmt->execute();
    $likersResult = $likerStmt->get_result();
    $likers = [];
    while ($liker = $likersResult->fetch_assoc()) {
        $likers[] = $liker['username'];
    }

    // Get replies
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
        $canDeleteComment = ($user_id == $comment['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
    }
    ?>

    <div class="comment" style="margin-left: <?= $margin ?>px; color: #8d8d8d;">
        <strong><a href="user.php?username=<?= urlencode($comment['username']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($comment['username']) ?></a></strong>
        <p><?= htmlspecialchars($comment['comment']) ?></p>
        <small><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
        <?php if ($likeData['like_count'] > 0): ?>
            <p class="likers" style="font-size: 0.85rem; color: #6b6b6b;">
                Liked by: 
                <?php 
                $likersCount = $likeData['like_count'];
                $displayedLikers = array_slice($likers, 0, 3); // Show up to 3 likers
                $remainingCount = $likersCount - count($displayedLikers);
                echo implode(', ', array_map(function($username) {
                    return '<a href="user.php?username=' . urlencode($username) . '" style="color: #007bff; text-decoration: none;">' . htmlspecialchars($username) . '</a>';
                }, $displayedLikers));
                if ($remainingCount > 0) {
                    echo ' and ' . $remainingCount . ' other' . ($remainingCount > 1 ? 's' : '');
                }
                ?>
            </p>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)">Reply</button>
                <button onclick="likeComment(<?= $comment['id'] ?>)">Like (<?= $likeData['like_count'] ?>)</button>
                <?php if ($canDeleteComment): ?>
                    <button onclick="confirmCommentDelete(<?= $comment['id'] ?>)" class="delete-comment-btn">Delete</button>
                <?php endif; ?>
            </div>
            <div id="reply-form-<?= $comment['id'] ?>" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form action="comments.php" method="POST">
                    <input type="hidden" name="video_id" value="<?= $comment['video_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
        <?php endif; ?>
        <?php while ($reply = $repliesResult->fetch_assoc()) { displayComment($reply, $conn, $depth + 1); } ?>
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
            <p>Uploaded by: <a href="user.php?username=<?= urlencode($featuredVideo['username']) ?>"><?= htmlspecialchars($featuredVideo['username']) ?></a></p>

            <div class="video-actions">
                <?php if (isLoggedIn()): ?>
                    <button id="like-button-<?= $featuredVideo['id'] ?>" onclick="likeVideo(<?= $featuredVideo['id'] ?>)">👍 Like</button>
                    <button id="share-button-<?= $featuredVideo['id'] ?>" onclick="shareVideo(<?= $featuredVideo['id'] ?>)">🔗 Share</button>
                    <?php if ($canDelete): ?>
                        <button id="edit-button-<?= $featuredVideo['id'] ?>" onclick="showEditForm(<?= $featuredVideo['id'] ?>)" class="edit-btn">✏️ Edit</button>
                        <button id="delete-button-<?= $featuredVideo['id'] ?>" onclick="confirmDelete(<?= $featuredVideo['id'] ?>)" class="delete-btn">🗑️ Delete</button>
                    <?php endif; ?>
                <?php endif; ?>
                <?php
                    $likesQuery = "SELECT COUNT(*) as like_count FROM likes WHERE video_id = ?";
                    $stmt = $conn->prepare($likesQuery);
                    $stmt->bind_param("i", $featuredVideo['id']);
                    $stmt->execute();
                    $likesResult = $stmt->get_result();
                    $likeData = $likesResult->fetch_assoc();
                ?>
                <p class="like_share">
                    <?= $likeData['like_count'] ?> Likes |
                    <?= $featuredVideo['share_count'] ?> Shares |
                    <?= $featuredVideo['view_count'] ?> Views
                </p>
            </div>
            <div id="share-modal" class="modal" style="display: none;">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">×</span>
                    <input type="text" id="share-link" readonly style="width: 100%;">
                    <button onclick="copyLink()">Copy Link</button>
                </div>
            </div>
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
            
            <div class="comments-container-wrapper" style="max-height: 400px; overflow-y: auto; border: 0px solid #ddd; padding: 10px; margin: 10px 0;">
                <div id="comments-container">
                    <?php while ($comment = $commentsResult->fetch_assoc()) { displayComment($comment, $conn); } ?>
                </div>
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
            <div class="sidebar-video-container">
                <div class="sidebar-video-list">
                    <?php 
                    $result->data_seek(0);
                    while ($video = $result->fetch_assoc()): 
                        if ($featuredVideo && $video['id'] == $featuredVideo['id']) continue;
                    ?>
                        <div class="sidebar-video">
                            <a href="?video_id=<?= $video['id'] ?>">
                                <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>" class="video-thumb">
                                <video class="video-hover" muted loop playsinline preload="none" style="display:none; border-radius: 5px; width:50%; height:auto;">
                                    <source src="<?= VIDEO_UPLOAD_PATH . $video['video_file'] ?>" type="video/mp4">
                                </video>
                                <div class="sidebar-video-info">
                                    <h4><?= htmlspecialchars($video['title']) ?></h4>
                                    <p><?= htmlspecialchars($video['username']) ?></p>
                                    <p><?= htmlspecialchars($video['view_count']) ?> Views</p>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <br><br><br>
            <?php if (isLoggedIn() && !empty($watchedVideos)): ?>
            <h3>Recently Watched</h3>
            <div class="watched-container">
                <div class="watched-videos" data-total="<?= $totalWatchedVideos ?>" data-loaded="<?= count($watchedVideos) ?>">
                    <?php foreach ($watchedVideos as $video): ?>
                        <div class="recent-watched-video">
                            <a href="?video_id=<?= $video['id'] ?>">
                                <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>">
                            </a>
                            <h3><?= htmlspecialchars($video['title']) ?></h3>
                            <p>
                                <?= htmlspecialchars($video['view_count']) ?> Views | 
                                Uploaded by: <?= htmlspecialchars($video['username']) ?>
                            </p>
                            <p class="watched-date">Watched: <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['viewed_at']))) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="loading">Loading...</div>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
    <h3>EARLIER</h3>

<section class="video-gallery"
    style="
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(338px, 1fr));
    gap: 18px;
    padding: 24px;
    max-width: 1450px;
    margin: 0 auto;">
    <?php 
    $result->data_seek(0);
    while ($video = $result->fetch_assoc()): ?>
        <div class="video-card"
        style="
        background: #ffffff;
        border: 0px solid #e0e0e0;
        border-radius: 12px;
        height: 380px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        ">
            <a href="?video_id=<?= $video['id'] ?>" class="video-link">
                <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" 
                     alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>" 
                     class="thumbnail-img">
            </a>
            <div class="card-content">
                <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                <p class="video-description"><?= htmlspecialchars($video['description']) ?></p>
                <p class="video-author">
                    <?= htmlspecialchars($video['view_count']) ?> Views |
                    Uploaded by: <?= htmlspecialchars($video['username']) ?>
                </p>
                <p class="video-date"><?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['uploaded_at']))) ?></p>
            </div>
        </div>
    <?php endwhile; ?>
</section>

<style>
.video-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
    padding: 24px;
    max-width: 1240px;
    margin: 0 auto;
}

.video-card {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.video-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.video-link {
    display: block;
}

.thumbnail-img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-bottom: 1px solid #e0e0e0;
}

.card-content {
    padding: 16px;
}

.video-title {
    font-size: 1.25rem;
    margin: 0 0 8px;
    color: #1a1a1a;
    font-weight: 600;
    line-height: 1.3;
}

.video-description {
    font-size: 0.9rem;
    margin: 0 0 12px;
    color: #4a4a4a;
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.video-author {
    font-size: 0.85rem;
    margin: 0 0 8px;
    color: #6b6b6b;
    font-style: italic;
}

.video-date {
    font-size: 0.85rem;
    margin: 0;
    color: #6b6b6b;
}
</style>

</main>

<script>
function likeVideo(videoId) {
    fetch('like_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
            const likeShareText = document.querySelector(".like_share");
            if (likeShareText) {
                const parts = likeShareText.textContent.split('|');
                let currentLikes = parseInt(parts[0].replace('Likes:', '').trim());
                likeShareText.innerHTML = `Likes: ${currentLikes + 1} | ${parts[1].trim()}`;
            }
        }
    });
}

function shareVideo(videoId) {
    const shareLink = window.location.href.split('?')[0] + "?video_id=" + videoId;
    document.getElementById("share-link").value = shareLink;
    document.getElementById("share-modal").style.display = "block";
    fetch('share_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.message === 'Already shared') {
            const shareButton = document.getElementById("share-button-" + videoId);
            if (shareButton) {
                shareButton.style.backgroundColor = "#008CBA";
                shareButton.innerText = "Shared";
                shareButton.classList.add("shared");
            }
        }
    });
}

function copyLink() {
    const shareLink = document.getElementById("share-link");
    const videoId = new URLSearchParams(window.location.search).get('video_id') || shareLink.value.match(/video_id=(\d+)/)?.[1];
    if (!videoId) {
        alert('Error: Video ID not found');
        return;
    }
    fetch('share_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const shareButton = document.getElementById("share-button-" + videoId);
            if (shareButton && !shareButton.classList.contains("shared")) {
                shareButton.style.backgroundColor = "#008CBA";
                shareButton.innerText = "Shared";
                shareButton.classList.add("shared");
            }
            const likeShareText = document.querySelector(".like_share");
            if (likeShareText) {
                const parts = likeShareText.textContent.split('|');
                likeShareText.innerHTML = `${parts[0].trim()} | Shares: ${data.share_count}`;
            }
            shareLink.select();
            shareLink.setSelectionRange(0, 99999);
            document.execCommand("copy");
            closeModal();
        } else {
            alert('Error: ' + (data.message || 'Failed to process share'));
        }
    });
}

function confirmDelete(videoId) {
    if (confirm('Permanently delete this video?')) {
        fetch('delete_video.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Cache-Control': 'no-cache' },
            body: 'video_id=' + videoId
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = window.location.href.split('?')[0] + '?deleted=' + videoId + '&t=' + Date.now();
            } else {
                alert('Error: ' + (data.message || 'Deletion failed'));
            }
        });
    }
}

function closeModal() {
    document.getElementById("share-modal").style.display = "none";
    const shareButton = document.querySelector('.shared');
    if (shareButton && !shareButton.classList.contains("shared")) {
        shareButton.disabled = false;
        shareButton.innerText = "🔗 Share";
        shareButton.classList.remove("shared");
        shareButton.style.backgroundColor = "";
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const mainVideo = document.getElementById('main-video');
    const bgVideo = document.getElementById('background-video');
    function syncVideos() { bgVideo.currentTime = mainVideo.currentTime; }
    mainVideo.addEventListener('play', function() { syncVideos(); bgVideo.play(); });
    mainVideo.addEventListener('pause', function() { bgVideo.pause(); });
    mainVideo.addEventListener('seeked', syncVideos);
    mainVideo.addEventListener('ended', function() { bgVideo.currentTime = 0; bgVideo.pause(); });
    mainVideo.addEventListener('loadedmetadata', syncVideos);

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

    const watchedSection = document.querySelector('.watched-videos');
    if (watchedSection) {
        let isLoading = false;
        let currentPage = 1;
        const videosPerPage = 4;
        const totalVideos = parseInt(watchedSection.dataset.total);
        let loadedVideos = parseInt(watchedSection.dataset.loaded);
        window.addEventListener('scroll', function() {
            if (isLoading || loadedVideos >= totalVideos) return;
            const sectionRect = watchedSection.getBoundingClientRect();
            const windowHeight = window.innerHeight;
            if (sectionRect.bottom <= windowHeight + 200) {
                isLoading = true;
                const loadingDiv = watchedSection.querySelector('.loading');
                loadingDiv.style.display = 'block';
                currentPage++;
                fetch(`load_watched_videos.php?page=${currentPage}`)
                    .then(response => response.text())
                    .then(html => {
                        if (html.trim()) {
                            watchedSection.insertAdjacentHTML('beforeend', html);
                            loadedVideos += videosPerPage;
                            watchedSection.dataset.loaded = loadedVideos;
                        }
                    })
                    .catch(error => console.error('Error loading videos:', error))
                    .finally(() => {
                        isLoading = false;
                        loadingDiv.style.display = 'none';
                    });
            }
        });
    }
});

function adjustBlur(intensity) {
    const overlay = document.querySelector('.blur-overlay');
    overlay.style.backdropFilter = `blur(${intensity}px)`;
    overlay.style.webkitBackdropFilter = `blur(${intensity}px)`;
}

function toggleReplyForm(commentId) {
    const form = document.getElementById("reply-form-" + commentId);
    form.style.display = form.style.display === "none" ? "block" : "none";
}

function likeComment(commentId) {
    fetch('comments.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'like_comment=1&comment_id=' + commentId
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "success") {
            const buttons = document.querySelectorAll(`button[onclick="likeComment(${commentId})"]`);
            buttons.forEach(button => {
                const currentCount = parseInt(button.innerText.match(/\((\d+)\)/)[1]) || 0;
                button.innerText = `Like (${currentCount + 1})`;
                button.disabled = true;
            });
            // Reload the page to update the likers list
            window.location.reload();
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
            document.querySelector('.video-player h3').textContent = data.title;
            document.querySelector('.video-player p:nth-of-type(1)').textContent = data.description;
            closeEditModal();
            alert('Video information updated successfully!');
        } else {
            closeEditModal();
            alert(data.message || 'Update failed');
        }
    });
});

function confirmCommentDelete(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        // Get the comment element
        const commentElement = document.querySelector(`.comment [onclick="confirmCommentDelete(${commentId})"]`).closest('.comment');
        
        // Apply magical animation before deletion
        if (commentElement) {
            // Set initial styles for animation
            commentElement.style.transition = 'all 0.6s ease-in-out';
            commentElement.style.transformOrigin = 'center';
            
            // Trigger the magical animation
            commentElement.style.opacity = '0';
            commentElement.style.transform = 'scale(0.3) rotate(10deg)';
            commentElement.style.filter = 'blur(2px)';
            
            // Wait for animation to complete before removing
            setTimeout(() => {
                fetch('delete_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'comment_id=' + commentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove the comment after animation
                        if (commentElement) commentElement.remove();
                        
                        // Update comments count
                        const commentsCountElement = document.querySelector('.comments-section h4');
                        if (commentsCountElement) {
                            const currentCount = parseInt(commentsCountElement.textContent.match(/\d+/)[0]) || 0;
                            commentsCountElement.textContent = commentsCountElement.textContent.replace(/\d+/, currentCount - 1);
                        }
                    } else {
                        // Revert animation and show error if deletion fails
                        commentElement.style.opacity = '1';
                        commentElement.style.transform = 'scale(1) rotate(0deg)';
                        commentElement.style.filter = 'none';
                        alert('Error: ' + (data.message || 'Failed to delete comment'));
                    }
                })
                .catch(error => {
                    // Revert animation on fetch error
                    commentElement.style.opacity = '1';
                    commentElement.style.transform = 'scale(1) rotate(0deg)';
                    commentElement.style.filter = 'none';
                    alert('Network error: Failed to delete comment');
                });
            }, 600); // Match the transition duration
        } else {
            alert('Error: Comment element not found');
        }
    }
}
</script>
<?php require_once 'includes/footer.php'; ?>