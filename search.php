<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get search query
$searchQuery = '';
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $searchQuery = trim($conn->real_escape_string($_GET['q']));
}

// Prepare the query with search filter
$query = "SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id 
          WHERE v.title LIKE '%$searchQuery%' 
          OR v.description LIKE '%$searchQuery%' 
          OR u.username LIKE '%$searchQuery%'
          ORDER BY uploaded_at DESC";
$result = $conn->query($query);
?>

<main>
    <section class="search-results">
        <h2>Search Results for "<?= htmlspecialchars($searchQuery) ?>"</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <div class="video-grid">
                <?php while ($video = $result->fetch_assoc()): ?>
                    <div class="video-thumbnail">
                        <a href="index.php?video_id=<?= $video['id'] ?>">
                            <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="<?= htmlspecialchars($video['title']) ?>">
                        </a>
                        <h3><?= htmlspecialchars($video['title']) ?></h3>
                        <p class="video-description"><?= htmlspecialchars($video['description']) ?></p>
                        <p class="video-uploader">Uploaded by: <?= htmlspecialchars($video['username']) ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>No videos found matching your search.</p>
                <p><a href="index.php">Browse all videos</a></p>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>