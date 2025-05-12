<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(403);
    echo "Not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    echo "User not found.";
    exit;
}

$videosPerPage = 4;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $videosPerPage;
$featured_id = isset($_GET['featured_id']) ? (int)$_GET['featured_id'] : null;

$watchedQuery = "SELECT v.*, u.username, COUNT(vv.video_id) as user_view_count, MAX(vv.viewed_at) as last_viewed 
                FROM video_views vv 
                LEFT JOIN videos v ON vv.video_id = v.id 
                LEFT JOIN users u ON v.user_id = u.id 
                WHERE vv.user_id = ? " . ($featured_id ? "AND v.id != ?" : "") . " 
                GROUP BY vv.video_id 
                ORDER BY last_viewed DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($watchedQuery);
if (!$stmt) {
    error_log("Load Watched Videos: Failed to prepare query: " . $conn->error);
    http_response_code(500);
    echo "Error loading videos.";
    exit;
}

if ($featured_id) {
    $stmt->bind_param("iiii", $user_id, $featured_id, $videosPerPage, $offset);
} else {
    $stmt->bind_param("iii", $user_id, $videosPerPage, $offset);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    error_log("Load Watched Videos: Fetched " . $result->num_rows . " videos for user_id=$user_id, page=$page");
    
    if ($result->num_rows > 0) {
        while ($video = $result->fetch_assoc()) {
            if ($video['id'] === null || $video['username'] === null) {
                error_log("Load Watched Videos: Missing video or user data for video_id=" . $video['video_id']);
                continue;
            }
            ?>
            <div class="recent-watched-video">
                <a href="?video_id=<?= $video['id'] ?>">
                    <img src="<?= THUMBNAIL_UPLOAD_PATH . ($video['thumbnail_file'] ?? 'default.jpg') ?>" alt="Thumbnail for <?= htmlspecialchars($video['title'] ?? 'Untitled') ?>">
                </a>
                <h3><?= htmlspecialchars($video['title'] ?? 'Untitled') ?></h3>
                <p style="font-size: 0.85rem; color: #6b6b6b; margin-top: 5px; font-style: italic;">
                    <?= htmlspecialchars($video['view_count'] ?? 0) ?> Total Views | 
                    <?= htmlspecialchars($video['user_view_count'] ?? 0) ?> Views by You | 
                    Uploaded by: <a href="user.php?username=<?= urlencode($video['username'] ?? 'Unknown') ?>"><?= htmlspecialchars($video['username'] ?? 'Unknown') ?></a>
                </p>
                <p class="watched-date">Last watched: <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['last_viewed'] ?? 'now'))) ?></p>
            </div>
            <?php
        }
    } else {
        error_log("Load Watched Videos: No videos found for user_id=$user_id, page=$page");
        echo "<p>No more recently watched videos found.</p>";
    }
} else {
    error_log("Load Watched Videos: Query execution failed: " . $stmt->error);
    http_response_code(500);
    echo "Error loading videos.";
}

$stmt->close();
$conn->close();
?>