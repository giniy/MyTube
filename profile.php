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
$watchedVideos = getWatchedVideos($profileUserId); // New function to get watched videos

// Check if user exists
if (!$userData) {
    header('Location: index.php');
    exit;
}

// Function to get proper profile picture path
function getProfilePicturePath($userId, $profilePicture, $gender) {
    $imageToShow = '';
    $cacheBuster = '?v=' . time();
    
    // First try the user's actual profile picture
    if (!empty($profilePicture)) {
        // Clean the path and ensure it's in the correct directory
        $cleanFilename = basename($profilePicture);
        $fullPath = 'uploads/profile_pictures/' . $cleanFilename;
        
        // Check if file exists and is readable
        if (file_exists($fullPath) && is_readable($fullPath)) {
            return $fullPath . $cacheBuster;
        }
    }
    
    // If no valid image yet, use gender-based default
    $gender = strtolower(trim($gender));
    $defaultImage = 'other.jpg'; // Default neutral image
    
    if ($gender === 'female') {
        $defaultImage = 'she.jpg';
    } elseif ($gender === 'male') {
        $defaultImage = 'he.jpg';
    }
    
    $imageToShow = 'uploads/profile_pictures/' . $defaultImage;
    
    // Final fallback if default images are missing
    if (!file_exists($imageToShow)) {
        $imageToShow = 'uploads/profile_pictures/default.jpg';
    }
    
    return $imageToShow . $cacheBuster;
}
?>

<div class="profile-container">
    <!-- User Info Section -->
    <section class="profile-info">
        <div class="profile-header">
            <div class="profile-picture">
                <img src="<?= getProfilePicturePath($userData['id'], $userData['profile_picture'], $userData['gender']) ?>" 
                     alt="Profile Picture"
                     onerror="this.onerror=null; this.src='uploads/profile_pictures/default.jpg?v=<?= time() ?>'">
            </div>
            <div class="profile-details">
                <h1><?= htmlspecialchars($userData['username']) ?></h1>
                <p class="profile-email"><?= htmlspecialchars($userData['email']) ?></p>
                <br>
                <div class="profile-bio">
                    <p>
                        <?= !empty($userData['bio']) 
                            ? nl2br(htmlspecialchars($userData['bio'])) 
                            : '<em>No bio yet</em>' ?>
                    </p>
                    <p>
                        <?= !empty($userData['gender']) 
                            ? nl2br(htmlspecialchars($userData['gender'])) 
                            : '<em>No gender</em>' ?>
                    </p>
                </div>
                <p class="profile-join-date">Member since: <?= date('F Y', strtotime($userData['created_at'])) ?></p>
                
                <!-- Social Media Links -->
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
            <div class="stat-item">
                <span class="stat-number"><?= count($watchedVideos) ?></span>
                <span class="stat-label">Watched</span>
            </div>

        </div>
    </section>

    <!-- User Activity Section -->
    <section class="profile-activity">
        <div class="activity-tabs">
            <button class="tab-btn active" data-tab="videos">Uploaded Videos</button>
            <button class="tab-btn" data-tab="comments">Recent Comments</button>
            <button class="tab-btn" data-tab="likes">Liked Videos</button>
            <button class="tab-btn" data-tab="watched">Watched Videos</button>
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

        <div class="tab-content" id="watched-tab">
            <?php if (!empty($watchedVideos)): ?>
                <div class="video-grid">
                    <?php foreach ($watchedVideos as $video): ?>
                        <div class="recent-watched-video">
                            <a href="?video_id=<?= $video['id'] ?>">
                                <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>">
                            </a>
                            <h3 style="color: #ffffff;" ><?= htmlspecialchars($video['title']) ?></h3>
                            <p style="color: #484746;" >Uploaded by: <?= htmlspecialchars($video['username']) ?></p>
                            <p class="watched-date" style="color: #ffffff;">Watched: <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['viewed_at']))) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-content">No watched videos yet.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>