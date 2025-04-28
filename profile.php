<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

// Get profile user ID (default to current user)
$profileUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : getUserId();

// Get user data
$userData = getUserData($profileUserId);
$videos = getUserVideos($profileUserId);
$comments = getUserComments($profileUserId);
$likes = getUserLikes($profileUserId);

// Check if user exists
if (!$userData) {
    header('Location: index.php');
    exit;
}
?>

<div class="profile-container">
    <!-- User Info Section -->
    <section class="profile-info">
        <div class="profile-header">
            <div class="profile-picture">
                <img src="<?= getProfilePicture($userData['id']) ?>" alt="Profile Picture">
            </div>
            <div class="profile-details">
                <h1><?= htmlspecialchars($userData['username']) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($userData['email']) ?></p>
                <div class="profile-bio">
                    <p>
                        <?= !empty($userData['bio']) 
                            ? nl2br(htmlspecialchars($userData['bio'])) 
                            : '<em>No bio yet</em>' ?>
                    </p>
                </div>
                <p class="profile-join-date">Member since: <?= date('F Y', strtotime($userData['created_at'])) ?></p>
                
                <!-- Social Media Links - Updated with Other Link -->
                <?php if (!empty($userData['twitter']) || !empty($userData['instagram']) || !empty($userData['youtube']) || !empty($userData['other'])): ?>
                    <div class="profile-social-links">
                        <h3>Connect</h3>
                        <div class="social-icons">
                            <?php if (!empty($userData['twitter'])): ?>
                                <a href="<?= htmlspecialchars($userData['twitter']) ?>" target="_blank" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userData['instagram'])): ?>
                                <a href="<?= htmlspecialchars($userData['instagram']) ?>" target="_blank" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userData['youtube'])): ?>
                                <a href="<?= htmlspecialchars($userData['youtube']) ?>" target="_blank" title="YouTube">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php if (!empty($userData['other'])): ?>
                                <a href="<?= htmlspecialchars($userData['other']) ?>" target="_blank" title="Other Link">
                                    <i class="fas fa-link"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($profileUserId === getUserId()): ?>
                    <a href="edit_profile.php" class="edit-profile-btn">Edit Profile</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?= count($videos) ?></span>
                <span class="stat-label">Videos</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($comments) ?></span>
                <span class="stat-label">Comments</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($likes) ?></span>
                <span class="stat-label">Likes</span>
            </div>
        </div>
    </section>

    <!-- User Activity Section -->
    <section class="profile-activity">
        <div class="activity-tabs">
            <button class="tab-btn active" data-tab="videos">Uploaded Videos</button>
            <button class="tab-btn" data-tab="comments">Recent Comments</button>
            <button class="tab-btn" data-tab="likes">Liked Videos</button>
        </div>
        
        <div class="tab-content active" id="videos-tab">
            <?php if (!empty($videos)): ?>
                <div class="video-grid">
                    <?php foreach ($videos as $video): ?>
                        <?php include 'includes/video_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content">No videos uploaded yet.</p>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="comments-tab">
            <?php if (!empty($comments)): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="profile-comment">
                            <p>On video: <a href="index.php?video_id=<?= $comment['video_id'] ?>"><?= htmlspecialchars($comment['video_title']) ?></a></p>
                            <p class="comment-text"><?= htmlspecialchars($comment['comment']) ?></p>
                            <p class="comment-date"><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content">No comments yet.</p>
            <?php endif; ?>
        </div>
        
        <div class="tab-content" id="likes-tab">
            <?php if (!empty($likes)): ?>
                <div class="video-grid">
                    <?php foreach ($likes as $video): ?>
                        <?php include 'includes/video_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content">No liked videos yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>