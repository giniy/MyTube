<?php
require_once 'includes/home_config.php';
require_once 'includes/home_function.php';

// Get popular videos
$query = "SELECT v.*, u.username, 
          (SELECT COUNT(*) FROM likes WHERE video_id = v.id) as like_count,
          (SELECT COUNT(*) FROM video_views WHERE video_id = v.id) as view_count
          FROM videos v
          JOIN users u ON v.user_id = u.id
          ORDER BY v.uploaded_at DESC
          LIMIT 40";
$videos = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Get categories
$categories = $conn->query("SELECT * FROM categories LIMIT 10")->fetch_all(MYSQLI_ASSOC);
require_once 'includes/header.php';
?>

<!-- Main Content -->
<main class="yt-main">
    <!-- Categories Bar -->
    <div class="yt-categories">
        <?php foreach ($categories as $category): ?>
            <button class="yt-category"><?= htmlspecialchars($category['name']) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Videos Grid -->
    <div class="yt-videos-grid">
        <?php foreach ($videos as $video): ?>
            <div class="yt-video-card">
                <a href="watch.php?v=<?= $video['id'] ?>" class="video-link">
                    <div class="thumbnail-container">
                        <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" 
                             alt="<?= htmlspecialchars($video['title']) ?>" 
                             class="video-thumbnail">
                        <span class="video-duration">10:30</span> <!-- Calculate this dynamically -->
                    </div>
                    <div class="video-info">
                        <img src="<?= getProfilePicture($video['user_id']) ?>" 
                             class="channel-icon">
                        <div class="video-details">
                            <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                            <p class="video-channel"><?= htmlspecialchars($video['username']) ?></p>
                            <p class="video-stats">
                                <?= number_format($video['view_count']) ?> views • 
                                <?= time_elapsed_string($video['uploaded_at']) ?>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>