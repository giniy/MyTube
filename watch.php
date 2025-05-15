<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/functions.php';

if (!isset($_GET['v']) || !is_numeric($_GET['v'])) {
    header("Location: index.php");
    exit;
}

$video_id = intval($_GET['v']);

try {
    // First fetch the video info
    $stmt = $conn->prepare("SELECT v.*, u.username, u.profile_picture 
                           FROM videos v 
                           JOIN users u ON v.user_id = u.id 
                           WHERE v.id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Video not found.");
    }
    
    $video = $result->fetch_assoc();
    
    // Check private video access - MOVED AFTER FETCHING VIDEO DATA
    if ($video['is_private']) {
        if (!isLoggedIn() || ($_SESSION['user_id'] != $video['user_id'] && !(isset($_SESSION['is_admin']) && $_SESSION['is_admin']))) {
            die('This video is private');
        }
    }

    // Check age restriction
    if ($video['age_restricted']) {
        if (!isLoggedIn() || !isset($_SESSION['age_verified']) || !$_SESSION['age_verified']) {
            die('This video is age-restricted. Please verify your age in account settings.');
        }
    }

    // Update view count AFTER access checks
    $updateStmt = $conn->prepare("UPDATE videos SET view_count = view_count + 1 WHERE id = ?");
    $updateStmt->bind_param("i", $video_id);
    $updateStmt->execute();
    
    // Handle video file path
    $videoPath = trim($video['video_file']);
    if (strpos($videoPath, 'uploads/videos/') !== 0) {
        $videoPath = 'uploads/videos/' . $videoPath;
    }
    
    if (!file_exists($videoPath)) {
        throw new Exception("Video file not found.");
    }

} catch (Exception $e) {
    echo "<div class='error-container'><h2>Error</h2><p>" . 
         htmlspecialchars($e->getMessage()) . "</p></div>";
    require_once 'includes/footer.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> - MyTube</title>
    <link rel="stylesheet" href="static/css/watch.css">
</head>
<body>
    <div class="video-container">
        <?php if ($video['age_restricted'] || $video['content_warning']): ?>
        <div class="content-warning-overlay">
            <div class="warning-box">
                <h3>Content Warning</h3>
                <?php if ($video['age_restricted']): ?>
                    <p>🔞 Age-Restricted Content</p>
                <?php endif; ?>
                <?php if ($video['content_warning']): ?>
                    <p>⚠️ <?= htmlspecialchars($video['content_warning']) ?></p>
                <?php endif; ?>
                <button onclick="this.parentNode.parentNode.style.display='none'">Continue</button>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="video-player">
            <video id="main-video" controls autoplay muted width="100%">
                <source src="<?= htmlspecialchars($videoPath) ?>" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div class="video-info">
            <h1><?= htmlspecialchars($video['title']) ?></h1>
            
            <div class="video-creator">
                <img src="<?= htmlspecialchars('uploads/profile_pictures/' . $video['profile_picture']) ?>" 
                     alt="<?= htmlspecialchars($video['username']) ?>" 
                     onerror="this.src='uploads/profile_pictures/default.jpg'">
                <span><?= htmlspecialchars($video['username']) ?></span>
            </div>
            
            <div class="video-stats">
                <span><?= number_format($video['view_count']) ?> views</span>
                <span>•</span>
                <span><?= date('M j, Y', strtotime($video['uploaded_at'])) ?></span>
            </div>
            
            <div class="video-description">
                <p><?= nl2br(htmlspecialchars($video['description'])) ?></p>
            </div>
        </div>
    </div>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>