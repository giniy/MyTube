<?php
// This component expects $video to be passed from the parent template
if (!isset($video)) return;
?>

<div class="video-card">
    <a href="index.php?video_id=<?= $video['id'] ?>">
        <div class="video-thumbnail">
            <img src="<?= THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'] ?>" alt="<?= htmlspecialchars($video['title']) ?>">
            <div class="video-duration">
            </div>
        </div>
        <div class="video-info">
            <h3><?= htmlspecialchars($video['title']) ?></h3>
            <h5><?= htmlspecialchars($video['description']) ?></h5>
            <div class="video-meta">
                <span class="upload-date"><?= timeElapsedString($video['uploaded_at']) ?></span>
            </div>
        </div>
    </a>
</div>