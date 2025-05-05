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
            <h3 style="color: white;" ><?= htmlspecialchars($video['title']) ?></h3>
            <h4><?= htmlspecialchars($video['description']) ?><h4>
            <div class="video-meta">
                <span class="upload-date" style="color: #625656;" ><?= timeElapsedString($video['uploaded_at']) ?></span>
                <span class="views" style="color: #625656;" ><?= htmlspecialchars($video['view_count']) ?> views</span>
            </div>
        </div>
    </a>
</div>