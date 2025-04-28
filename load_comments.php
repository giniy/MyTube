<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$video_id = isset($_GET['video_id']) ? (int)$_GET['video_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$commentsPerPage = 5;
$offset = ($page - 1) * $commentsPerPage;

if ($video_id > 0) {
    // Get paginated comments
    $query = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
              WHERE c.video_id = ? AND c.parent_id IS NULL ORDER BY c.created_at DESC
              LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $video_id, $commentsPerPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($comment = $result->fetch_assoc()) {
        displayComment($comment, $conn);
    }
}

function displayComment($comment, $conn, $depth = 0) {
    // Get comment likes count
    $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
    $stmt = $conn->prepare($likesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $likesResult = $stmt->get_result();
    $likeData = $likesResult->fetch_assoc();
    
    // Get replies (always load all replies for a parent comment)
    $repliesQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.parent_id = ? ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($repliesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $repliesResult = $stmt->get_result();
    
    $margin = $depth * 20;
    ?>
    <div class="comment" style="margin-left: <?= $margin ?>px;">
        <strong><?= htmlspecialchars($comment['username']) ?></strong>
        <p><?= htmlspecialchars($comment['comment']) ?></p>
        <small><?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?></small>
        
        <?php if (isLoggedIn()): ?>
            <div class="comment-actions">
                <button onclick="toggleReplyForm(<?= $comment['id'] ?>)">Reply</button>
                <button onclick="likeComment(<?= $comment['id'] ?>)">Like (<?= $likeData['like_count'] ?>)</button>
            </div>
            
            <div id="reply-form-<?= $comment['id'] ?>" class="reply-form" style="display: none;">
                <form action="comments.php" method="POST">
                    <input type="hidden" name="video_id" value="<?= $comment['video_id'] ?>">
                    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                    <textarea name="comment" placeholder="Write a reply..." required></textarea>
                    <button type="submit">Post Reply</button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php
        // Display replies recursively
        while ($reply = $repliesResult->fetch_assoc()) {
            displayComment($reply, $conn, $depth + 1);
        }
        ?>
    </div>
    <?php
}
?>