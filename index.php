<?php
// Start session and ensure CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle deleted video
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

    $watchedCountQuery = "SELECT COUNT(DISTINCT video_id) as total FROM video_views WHERE user_id = ?";
    $watchedCountStmt = $conn->prepare($watchedCountQuery);
    $watchedCountStmt->bind_param("i", $user_id);
    $watchedCountStmt->execute();
    $watchedCountResult = $watchedCountStmt->get_result();
    $totalWatchedVideos = $watchedCountResult->fetch_assoc()['total'];

    $watchedQuery = "SELECT v.*, u.username, COUNT(*) as view_count, MAX(vv.viewed_at) as last_viewed 
                    FROM video_views vv 
                    JOIN videos v ON vv.video_id = v.id 
                    JOIN users u ON v.user_id = u.id 
                    WHERE vv.user_id = ? 
                    GROUP BY vv.video_id 
                    ORDER BY last_viewed DESC 
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
    $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
    $stmt = $conn->prepare($likesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $likesResult = $stmt->get_result();
    $likeData = $likesResult->fetch_assoc();

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

    $repliesQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.parent_id = ? ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($repliesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $repliesResult = $stmt->get_result();

    $margin = $depth * 20;
    $canDeleteComment = false;
    $canEditComment = false;
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $canDeleteComment = ($user_id == $comment['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
        $canEditComment = ($user_id == $comment['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));
    }
    ?>

    <div class="comment" style="margin-left: <?= $margin ?>px; color: #8d8d8d;">
        <strong><a href="user.php?username=<?= urlencode($comment['username']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($comment['username']) ?></a></strong>
        <p class="comment-text" data-comment-id="<?= $comment['id'] ?>"><?= htmlspecialchars($comment['comment']) ?></p>
        <small><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
        <?php if ($comment['updated_at']): ?>
            <small>(Edited: <?= date('M j, Y g:i a', strtotime($comment['updated_at'])) ?>)</small>
        <?php endif; ?>
        <?php if ($likeData['like_count'] > 0): ?>
            <p class="likers" style="font-size: 0.85rem; color: #6b6b6b;" data-comment-id="<?= $comment['id'] ?>">
                Liked by: 
                <?php 
                $likersCount = $likeData['like_count'];
                $displayedLikers = array_slice($likers, 0, 3);
                $remainingCount = $likersCount - count($displayedLikers);
                echo implode(', ', array_map(function($username) {
                    return '<a href="user.php?username=' . urlencode($username) . '" style="color: #007bff; text-decoration: none;">' . htmlspecialchars($username) . '</a>';
                }, $displayedLikers));
                if ($remainingCount > 0) {
                    echo ' and ' . $remainingCount . ' other' . ($remainingCount > 1 ? 's' : '');
                }
                ?>
            </p>
        <?php else: ?>
            <p class="likers" style="font-size: 0.85rem; color: #6b6b6b; display: none;" data-comment-id="<?= $comment['id'] ?>"></p>
        <?php endif; ?>
        <?php if (isLoggedIn()): ?>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)"><i class="fa-solid fa-reply"></i></button>
                <button onclick="likeComment(<?= $comment['id'] ?>)" class="like-button" data-comment-id="<?= $comment['id'] ?>"><i class="fa-solid fa-heart"></i> (<?= $likeData['like_count'] ?>)</button>
                <?php if ($canEditComment): ?>
                    <button onclick="toggleEditForm(<?= $comment['id'] ?>)" class="edit-comment-btn">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                <?php endif; ?>
                <?php if ($canDeleteComment): ?>
                    <button onclick="confirmCommentDelete(<?= $comment['id'] ?>)" class="delete-comment-btn">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                <?php endif; ?>
            </div>
            <div id="reply-form-<?= $comment['id'] ?>" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="reply-form">
                    <input type="hidden" name="video_id" value="<?= $comment['video_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
            <div id="edit-form-<?= $comment['id'] ?>" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="edit-form">
                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Edit your comment..." required><?= htmlspecialchars($comment['comment']) ?></textarea>
                    <button type="submit">Save</button>
                    <button type="button" onclick="toggleEditForm(<?= $comment['id'] ?>)">Cancel</button>
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
                    <button id="like-button-<?= $featuredVideo['id'] ?>" onclick="likeVideo(<?= $featuredVideo['id'] ?>)">
                        <i class="fa-solid fa-thumbs-up"></i>
                    </button>
                    <button id="share-button-<?= $featuredVideo['id'] ?>" onclick="shareVideo(<?= $featuredVideo['id'] ?>)">
                        <i class="fa-solid fa-share-nodes"></i>
                    </button>
                    <?php if ($canDelete): ?>
                        <button id="edit-button-<?= $featuredVideo['id'] ?>" onclick="showEditForm(<?= $featuredVideo['id'] ?>)" class="edit-btn">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <button id="delete-button-<?= $featuredVideo['id'] ?>" onclick="confirmDelete(<?= $featuredVideo['id'] ?>)" class="delete-btn">
                            <i class="fa-solid fa-trash"></i>
                        </button>
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
            <!-- Share Modal -->
            <div id="share-modal" class="modal">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">×</span>
                    <h3>Share This Video</h3>
                    <input type="text" id="share-link" readonly>
                    <button onclick="copyLink()" style="margin-top:18px;">Copy Link</button>
                    <div class="social-share" style="margin-top: 18px;">
                        <button onclick="shareOnFacebook()">
                            <i class="fa fa-facebook" aria-hidden="true"></i>                            
                        </button>
                        <button onclick="shareOnTwitter()">
                            <i class="fa fa-twitter" aria-hidden="true"></i>
                        </button>
                        <button onclick="shareOnLinkedin()">
                            <i class="fa fa-linkedin" aria-hidden="true"></i>
                        </button>
                    </div>
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
            <h4>Comments (<span id="comments-count"><?= $totalComments ?></span>)</h4>
            <?php if (isLoggedIn()): ?>
                <form id="comment-form" method="POST">
                    <input type="hidden" name="video_id" value="<?= $featuredVideo['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Add a comment..." required></textarea>
                    <button type="submit">Post Comment</button>
                </form>
            <?php else: ?>
                <div class="login-prompt">
                    <p>Please <a href="auth/login.php">login</a> to post comments</p>
                </div>
            <?php endif; ?>

            <div class="comments-container-wrapper" style="max-height: 400px; overflow-y: auto;">
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
                            <p style="font-size: 0.85rem; color: #6b6b6b; margin-top: 5px; font-style: italic;">
                                <?= htmlspecialchars($video['view_count']) ?> Views | 
                                Uploaded by:
                                <a href="user.php?username=<?= urlencode($video['username']) ?>"><?= htmlspecialchars($video['username']) ?></a>
                            </p>
                            <p class="watched-date">Last watched: <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['last_viewed']))) ?></p>
                        </div>
                    <?php endforeach; ?>
                    <div class="loading">Loading...</div>
                </div>
            </div>
            <?php endif; ?>
        </aside>
    </div>
    <h3>EARLIER</h3>

<section class="video-gallery" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(338px, 1fr)); gap: 18px; padding: 24px; max-width: 1450px; margin: 0 auto;">
    <?php 
    $result->data_seek(0);
    while ($video = $result->fetch_assoc()): ?>
        <div class="video-card" style="background: #ffffff; border: 0px solid #e0e0e0; border-radius: 12px; height: 380px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease, box-shadow 0.3s ease;">
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
<?php if (isset($_SESSION['login_required'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: true,
        timer: 5000,
        timerProgressBar: true,
        width: '400px',
        padding: '1em',
        customClass: {
            popup: 'custom-swal-popup'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    Toast.fire({
        icon: 'warning',
        title: 'Login Required',
        html: 'You must be logged in to upload videos<br><small><a href="auth/login.php" style="color: #3085d6">Click to login</a></small>',
        confirmButtonText: 'OK',
        confirmButtonColor: '#3085d6',
    });
    
    <?php unset($_SESSION['login_required']); ?>
});
</script>

<style>
.custom-swal-popup {
    font-size: 14px !important;
}
.swal2-title {
    font-size: 18px !important;
    margin-bottom: 10px !important;
}
</style>
<?php endif; ?>
<script>
// Load Font Awesome
document.head.insertAdjacentHTML('beforeend', '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">');

function likeVideo(videoId) {
    console.log('Like button clicked for video:', videoId);
    fetch('like_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        console.log('Like response:', data);
        if (data.trim() === "success") {
            const likeButton = document.getElementById("like-button-" + videoId);
            likeButton.style.backgroundColor = "#ff0000";
            likeButton.innerText = "Liked";
            likeButton.disabled = true;
            const likeCountElement = document.querySelector(".like_share");
            if (likeCountElement) {
                const parts = likeCountElement.textContent.split('|');
                const currentLikes = parseInt(parts[0]) || 0;
                likeCountElement.innerHTML = `${currentLikes + 1} Likes | ${parts[1]} | ${parts[2]}`;
            }
        } else {
            alert('Failed to like video: ' + data);
        }
    })
    .catch(error => {
        console.error('Error liking video:', error);
        alert('Failed to like video: ' + error.message);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Log CSRF token for debugging
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
    console.log('CSRF Token:', csrfToken || 'Not found');

    document.getElementById('share-modal').style.display = 'none';
    
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('share-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(commentForm);
            const submitButton = commentForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.textContent = 'Posting...';

            console.log('Posting comment with data:', Object.fromEntries(formData));

            fetch('comments.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                    });
                }
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        appendComment(data.comment);
                        commentForm.querySelector('textarea').value = '';
                        const commentsCount = document.getElementById('comments-count');
                        if (commentsCount) {
                            const currentCount = parseInt(commentsCount.textContent) || 0;
                            commentsCount.textContent = currentCount + 1;
                        }
                    } else {
                        alert(data.message || 'Failed to post comment');
                    }
                } catch (e) {
                    console.error('JSON parse error:', e, 'Response text:', text);
                    alert('Failed to post comment: Invalid server response');
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
                alert('Failed to post comment: ' + error.message);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = 'Post Comment';
            });
        });
    }

    document.querySelectorAll('.reply-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const parentId = this.querySelector('input[name="parent_id"]').value;
            handleReplySubmission(this, parentId);
        });
    });

    document.querySelectorAll('.edit-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const commentId = this.querySelector('input[name="comment_id"]').value;
            handleEditSubmission(this, commentId);
        });
    });

    function appendComment(comment) {
        const container = document.getElementById('comments-container');
        if (!container) return;

        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment';
        commentDiv.style.marginLeft = '0px';

        commentDiv.innerHTML = `
            <strong><a href="user.php?username=${encodeURIComponent(comment.username)}" style="color: #007bff; text-decoration: none;">${escapeHtml(comment.username)}</a></strong>
            <p class="comment-text" data-comment-id="${comment.id}">${escapeHtml(comment.comment)}</p>
            <small>${formatDate(comment.created_at)}</small>
            ${comment.updated_at ? `<small>(Edited: ${formatDate(comment.updated_at)})</small>` : ''}
            <p class="likers" style="font-size: 0.85rem; color: #6b6b6b; display: none;" data-comment-id="${comment.id}"></p>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(${comment.id})"><i class="fa-solid fa-reply"></i>
                </button>
                <button onclick="likeComment(${comment.id})" class="like-button" data-comment-id="${comment.id}"><i class="fa-solid fa-heart"></i> (0)
                </button>
                <button onclick="toggleEditForm(${comment.id})" class="edit-comment-btn">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button onclick="confirmCommentDelete(${comment.id})" class="delete-comment-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div id="reply-form-${comment.id}" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="reply-form">
                    <input type="hidden" name="video_id" value="${comment.video_id}">
                    <input type="hidden" name="parent_id" value="${comment.id}">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
            <div id="edit-form-${comment.id}" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="edit-form">
                    <input type="hidden" name="comment_id" value="${comment.id}">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Edit your comment..." required>${escapeHtml(comment.comment)}</textarea>
                    <button type="submit">Save</button>
                    <button type="button" onclick="toggleEditForm(${comment.id})">Cancel</button>
                </form>
            </div>
        `;

        container.insertBefore(commentDiv, container.firstChild);

        const replyForm = commentDiv.querySelector(`#reply-form-${comment.id} form`);
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleReplySubmission(replyForm, comment.id);
        });

        const editForm = commentDiv.querySelector(`#edit-form-${comment.id} form`);
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEditSubmission(editForm, comment.id);
        });
    }

    function handleReplySubmission(replyForm, parentCommentId) {
        const formData = new FormData(replyForm);
        const submitButton = replyForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Posting...';

        console.log('Posting reply with data:', Object.fromEntries(formData));

        fetch('comments.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            console.log('Reply response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.text();
        })
        .then(text => {
            console.log('Reply raw response:', text);
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    appendReply(data.comment, parentCommentId);
                    replyForm.querySelector('textarea').value = '';
                    const replyFormContainer = replyForm.closest('.comment-actions');
                    replyFormContainer.style.display = 'none';
                    const commentsCount = document.getElementById('comments-count');
                    if (commentsCount) {
                        const currentCount = parseInt(commentsCount.textContent) || 0;
                        commentsCount.textContent = currentCount + 1;
                    }
                } else {
                    alert(data.message || 'Failed to post reply');
                }
            } catch (e) {
                console.error('Reply JSON parse error:', e, 'Response text:', text);
                alert('Failed to post reply: Invalid server response');
            }
        })
        .catch(error => {
            console.error('Error posting reply:', error);
            alert('Failed to post reply: ' + error.message);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Post Reply';
        });
    }

    function appendReply(reply, parentCommentId) {
        const parentComment = document.querySelector(`.comment [onclick="toggleReplyForm(${parentCommentId})"]`).closest('.comment');
        if (!parentComment) return;

        const parentMargin = parseInt(parentComment.style.marginLeft) || 0;
        const margin = parentMargin + 20;

        const replyDiv = document.createElement('div');
        replyDiv.className = 'comment';
        replyDiv.style.marginLeft = `${margin}px`;

        replyDiv.innerHTML = `
            <strong><a href="user.php?username=${encodeURIComponent(reply.username)}" style="color: #007bff; text-decoration: none;">${escapeHtml(reply.username)}</a></strong>
            <p class="comment-text" data-comment-id="${reply.id}">${escapeHtml(reply.comment)}</p>
            <small>${formatDate(reply.created_at)}</small>
            ${reply.updated_at ? `<small>(Edited: ${formatDate(reply.updated_at)})</small>` : ''}
            <p class="likers" style="font-size: 0.85rem; color: #6b6b6b; display: none;" data-comment-id="${reply.id}"></p>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(${reply.id})"><i class="fa-solid fa-reply"></i></button>
                <button onclick="likeComment(${reply.id})" class="like-button" data-comment-id="${reply.id}"><i class="fa-solid fa-heart"></i> (0)</button>
                <button onclick="toggleEditForm(${reply.id})" class="edit-comment-btn">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button onclick="confirmCommentDelete(${reply.id})" class="delete-comment-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div id="reply-form-${reply.id}" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="reply-form">
                    <input type="hidden" name="video_id" value="${reply.video_id}">
                    <input type="hidden" name="parent_id" value="${reply.id}">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
            <div id="edit-form-${reply.id}" class="comment-actions" style="display: none; color: #8d8d8d;">
                <form class="edit-form">
                    <input type="hidden" name="comment_id" value="${reply.id}">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <textarea name="comment" placeholder="Edit your comment..." required>${escapeHtml(reply.comment)}</textarea>
                    <button type="submit">Save</button>
                    <button type="button" onclick="toggleEditForm(${reply.id})">Cancel</button>
                </form>
            </div>
        `;

        parentComment.appendChild(replyDiv);

        const nestedReplyForm = replyDiv.querySelector(`#reply-form-${reply.id} form`);
        nestedReplyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleReplySubmission(nestedReplyForm, reply.id);
        });

        const nestedEditForm = replyDiv.querySelector(`#edit-form-${reply.id} form`);
        nestedEditForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleEditSubmission(nestedEditForm, reply.id);
        });
    }

    function handleEditSubmission(editForm, commentId) {
        const formData = new FormData(editForm);
        const submitButton = editForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';

        console.log('Editing comment with data:', Object.fromEntries(formData));

        fetch('comments.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            console.log('Edit response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.text();
        })
        .then(text => {
            console.log('Edit raw response:', text);
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    updateComment(data.comment);
                    const editFormContainer = editForm.closest('.comment-actions');
                    editFormContainer.style.display = 'none';
                } else {
                    alert(data.message || 'Failed to edit comment');
                }
            } catch (e) {
                console.error('Edit JSON parse error:', e, 'Response text:', text);
                alert('Failed to edit comment: Invalid server response');
            }
        })
        .catch(error => {
            console.error('Error editing comment:', error);
            alert('Failed to edit comment: ' + error.message);
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.textContent = 'Save';
        });
    }

    function updateComment(comment) {
        const commentText = document.querySelector(`.comment-text[data-comment-id="${comment.id}"]`);
        if (commentText) {
            commentText.textContent = comment.comment;
        }
        const commentDiv = commentText.closest('.comment');
        if (commentDiv) {
            const editedLabel = commentDiv.querySelector('small:nth-of-type(2)');
            if (comment.updated_at) {
                if (editedLabel) {
                    editedLabel.textContent = `(Edited: ${formatDate(comment.updated_at)})`;
                } else {
                    const createdAt = commentDiv.querySelector('small');
                    createdAt.insertAdjacentHTML('afterend', `<small>(Edited: ${formatDate(comment.updated_at)})</small>`);
                }
            }
        }
    }

// like comment

    function likeComment(commentId) {
        const button = document.querySelector(`button.like-button[data-comment-id="${commentId}"]`);
        if (!button || button.disabled) {
            console.log(`Like button for comment ${commentId} is disabled or not found`);
            return;
        }

        button.disabled = true;
        button.innerHTML = `<i class="fa-solid fa-heart"></i> (Loading...)`;

        const formData = new FormData();
        formData.append('like_comment', '1');
        formData.append('comment_id', commentId);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

        console.log('Liking comment:', commentId, 'with data:', Object.fromEntries(formData));

        fetch('comments.php', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            console.log('Like response status:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${text}`);
                });
            }
            return response.text();
        })
        .then(text => {
            console.log('Like raw response:', text);
            try {
                const data = JSON.parse(text);
                if (data.status === 'success') {
                    button.innerHTML = `<i class="fa-solid fa-heart"></i> (${data.like_count})`;
                    button.style.color = '#ff0000';
                    const likersElement = document.querySelector(`.likers[data-comment-id="${commentId}"]`);
                    if (likersElement) {
                        if (data.likers && data.likers.length > 0) {
                            const displayedLikers = data.likers.slice(0, 3);
                            const remainingCount = data.like_count - displayedLikers.length;
                            const likersHtml = `Liked by: ${displayedLikers.map(username => 
                                `<a href="user.php?username=${encodeURIComponent(username)}" style="color: #007bff; text-decoration: none;">${escapeHtml(username)}</a>`
                            ).join(', ')}${remainingCount > 0 ? ` and ${remainingCount} other${remainingCount > 1 ? 's' : ''}` : ''}`;
                            likersElement.innerHTML = likersHtml;
                            likersElement.style.display = 'block';
                        } else {
                            likersElement.style.display = 'none';
                        }
                    }
                } else {
                    console.error('Like failed:', data.message);
                    alert(data.message || 'Failed to like comment');
                    button.disabled = false;
                    const currentCount = parseInt(button.innerText.match(/\((\d+)\)/)?.[1]) || 0;
                    button.innerHTML = `<i class="fa-solid fa-heart"></i> (${currentCount})`;
                }
            } catch (e) {
                console.error('Like JSON parse error:', e, 'Response text:', text);
                alert('Failed to like comment: Invalid server response');
                button.disabled = false;
                const currentCount = parseInt(button.innerText.match(/\((\d+)\)/)?.[1]) || 0;
                button.innerHTML = `<i class="fa-solid fa-heart"></i> (${currentCount})`;
            }
        })
        .catch(error => {
            console.error('Error liking comment:', error);
            alert('Failed to like comment: ' + error.message);
            button.disabled = false;
            const currentCount = parseInt(button.innerText.match(/\((\d+)\)/)?.[1]) || 0;
            button.innerHTML = `<i class="fa-solid fa-heart"></i> (${currentCount})`;
        });
    }

// end like comment

    function escapeHtml(unsafe) {
        return String(unsafe || '')
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }
});

function shareVideo(videoId) {    
    sessionStorage.setItem('modalOpened', 'true');
    const cleanUrl = window.location.href.split('?')[0];
    const shareLink = cleanUrl + "?video_id=" + videoId;
    document.getElementById("share-link").value = shareLink;
    document.getElementById("share-modal").style.display = "block";
    
    fetch('share_video.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'video_id=' + encodeURIComponent(videoId)
    })
    .then(response => response.json())
    .then(data => {
        console.log('Share response:', data);
        if (data.status === 'success') {
            const shareButton = document.getElementById("share-button-" + videoId);
            if (shareButton) {
                shareButton.classList.add("shared");
            }
        }
    })
    .catch(error => {
        console.error('Error sharing video:', error);
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
            body: 'video_id=' + encodeURIComponent(videoId)
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
    document.getElementById('share-modal').style.display = 'none';
    if (window.location.search.includes('video_id')) {
        const cleanUrl = window.location.href.split('?')[0];
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMenu');
    const hamburger = document.querySelector('.hamburger-menu');
    const overlay = document.querySelector('.sidebar-overlay') || document.createElement('div');

    if (!overlay.classList.contains('sidebar-overlay')) {
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    sidebar.classList.toggle('active');
    hamburger.classList.toggle('active');
    overlay.classList.toggle('active');
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

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('sidebar-overlay')) {
            toggleSidebar();
        }
    });
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

function toggleEditForm(commentId) {
    const form = document.getElementById("edit-form-" + commentId);
    form.style.display = form.style.display === "none" ? "block" : "none";
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
            document.querySelectorAll('.reply-form').forEach(form => {
                form.removeEventListener('submit', handleReplySubmission);
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const parentId = this.querySelector('input[name="parent_id"]').value;
                    handleReplySubmission(this, parentId);
                });
            });
            document.querySelectorAll('.edit-form').forEach(form => {
                form.removeEventListener('submit', handleEditSubmission);
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const commentId = this.querySelector('input[name="comment_id"]').value;
                    handleEditSubmission(this, commentId);
                });
            });
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
        const commentElement = document.querySelector(`.comment [onclick="confirmCommentDelete(${commentId})"]`).closest('.comment');
        
        if (commentElement) {
            commentElement.style.transition = 'all 0.6s ease-in-out';
            commentElement.style.transformOrigin = 'center';
            commentElement.style.opacity = '0';
            commentElement.style.transform = 'scale(0.3) rotate(10deg)';
            commentElement.style.filter = 'blur(2px)';
            
            setTimeout(() => {
                fetch('delete_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'comment_id=' + encodeURIComponent(commentId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        if (commentElement) commentElement.remove();
                        const commentsCountElement = document.querySelector('.comments-section h4');
                        if (commentsCountElement) {
                            const currentCount = parseInt(commentsCountElement.textContent.match(/\d+/)[0]) || 0;
                            commentsCountElement.textContent = commentsCountElement.textContent.replace(/\d+/, currentCount - 1);
                        }
                    } else {
                        commentElement.style.opacity = '1';
                        commentElement.style.transform = 'scale(1) rotate(0deg)';
                        commentElement.style.filter = 'none';
                        alert('Error: ' + (data.message || 'Failed to delete comment'));
                    }
                })
                .catch(error => {
                    commentElement.style.opacity = '1';
                    commentElement.style.transform = 'scale(1) rotate(0deg)';
                    commentElement.style.filter = 'none';
                    alert('Failed to delete comment: ' + error.message);
                });
            }, 600);
        } else {
            alert('Error: Comment element not found');
        }
    }
}
</script>
<?php require_once 'includes/footer.php'; ?>