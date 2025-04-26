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

<?php require_once 'includes/footer.php'; ?>

<style>

</style>