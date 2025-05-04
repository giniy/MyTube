<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    http_response_code(403);
    echo "Not logged in.";
    exit;
}

$user_id = $_SESSION['user_id'];
$videosPerPage = 4;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $videosPerPage;

$watchedQuery = "SELECT v.*, u.username, vv.viewed_at 
                FROM video_views vv 
                JOIN videos v ON vv.video_id = v.id 
                JOIN users u ON v.user_id = u.id 
                WHERE vv.user_id = ? 
                ORDER BY vv.viewed_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($watchedQuery);
$stmt->bind_param("iii", $user_id, $videosPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();

while ($video = $result->fetch_assoc()): ?>
    <div class="recent-watched-video">
        <a href="?video_id=<?= $video['id'] ?>">
            <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>">
        </a>
        <h3><?= htmlspecialchars($video['title']) ?></h3>
        <p>Uploaded by: <?= htmlspecialchars($video['username']) ?></p>
        <p class="watched-date">Watched: <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($video['viewed_at']))) ?></p>
    </div>
<?php endwhile;

$stmt->close();
?>