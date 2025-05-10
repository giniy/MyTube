<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    echo 'You must be logged in to view this content.';
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT v.*, u.username 
          FROM videos v 
          JOIN users u ON v.user_id = u.id 
          JOIN followers f ON v.user_id = f.following_id 
          WHERE f.follower_id = ? 
          ORDER BY v.uploaded_at DESC 
          LIMIT 8";
$stmt = $conn->prepare($query);
if (!$stmt) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Database error: Failed to prepare statement';
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$videos = [];
while ($video = $result->fetch_assoc()) {
    $videos[] = $video;
}

if (empty($videos)) {
    echo '<p style="text-align: center; color: #6b6b6b;">No videos from subscribed users yet.</p>';
    exit;
}

foreach ($videos as $video) {
    ?>
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
                Uploaded by: <a href="user.php?username=<?= urlencode($video['username']) ?>"><?= htmlspecialchars($video['username']) ?></a>
            </p>
            <p class="video-date"><?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['uploaded_at']))) ?></p>
        </div>
    </div>
    <?php
}
?>