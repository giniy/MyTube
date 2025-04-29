<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/profile.css" rel="stylesheet">
</head>
<body>

<header>
<div class="logo">
    <a class="nav-link" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php" 
       style="text-decoration: none; color: #ff0000; font-weight: bold;">
        MyTube
    </a>

</div>
</header>
</body>

<?php
require_once 'includes/config.php';
// require_once 'includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: forum.php");
    exit;
}

$topicId = intval($_GET['id']);

// Get topic
$topicQuery = $conn->prepare("
    SELECT t.*, u.username, u.id as user_id, c.name as category_name 
    FROM forum_topics t 
    JOIN users u ON t.user_id = u.id 
    JOIN forum_categories c ON t.category_id = c.id 
    WHERE t.id = ?
");
$topicQuery->bind_param("i", $topicId);
$topicQuery->execute();
$topic = $topicQuery->get_result()->fetch_assoc();

if (!$topic) {
    header("Location: forum.php");
    exit;
}

// Get replies
$repliesQuery = $conn->prepare("
    SELECT r.*, u.username, u.id as user_id 
    FROM forum_replies r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.topic_id = ? 
    ORDER BY r.created_at ASC
");
$repliesQuery->bind_param("i", $topicId);
$repliesQuery->execute();
$replies = $repliesQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle reply submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content']) && isLoggedIn()) {
    $content = trim($_POST['reply_content']);
    
    if (empty($content)) {
        $errors[] = "Reply content cannot be empty";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO forum_replies (topic_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $topicId, $_SESSION['user_id'], $content);
        
        if ($stmt->execute()) {
            // Update topic's updated_at timestamp
            $conn->query("UPDATE forum_topics SET updated_at = NOW() WHERE id = $topicId");
            
            header("Location: view_topic.php?id=$topicId");
            exit;
        } else {
            $errors[] = "Error posting reply: " . $conn->error;
        }
    }
}

// Handle reply edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
    
    $replyId = intval($_POST['reply_id']);
    $userId = $_SESSION['user_id'];
    
    // Verify user owns the reply or is admin
    $ownershipCheck = $conn->prepare("SELECT user_id FROM forum_replies WHERE id = ?");
    $ownershipCheck->bind_param("i", $replyId);
    $ownershipCheck->execute();
    $ownerId = $ownershipCheck->get_result()->fetch_assoc()['user_id'];
    
    if ($ownerId == $userId || isAdmin()) {
        if ($_POST['action'] == 'delete') {
            $deleteStmt = $conn->prepare("DELETE FROM forum_replies WHERE id = ?");
            $deleteStmt->bind_param("i", $replyId);
            $deleteStmt->execute();
        } elseif ($_POST['action'] == 'edit' && isset($_POST['edited_content'])) {
            $content = trim($_POST['edited_content']);
            $editStmt = $conn->prepare("UPDATE forum_replies SET content = ?, updated_at = NOW() WHERE id = ?");
            $editStmt->bind_param("si", $content, $replyId);
            $editStmt->execute();
        }
        
        // Update topic's updated_at timestamp
        $conn->query("UPDATE forum_topics SET updated_at = NOW() WHERE id = $topicId");
        
        header("Location: view_topic.php?id=$topicId");
        exit;
    }
}
?>

<div class="forum-container">
    <nav class="breadcrumb">
        <a href="forum.php">Forum</a> &raquo;
        <a href="forum.php?category=<?= $topic['category_id'] ?>"><?= htmlspecialchars($topic['category_name']) ?></a> &raquo;
        <span><?= htmlspecialchars($topic['title']) ?></span>
    </nav>
    
    <div class="topic-header">
        <h1><?= htmlspecialchars($topic['title']) ?></h1>
        <?php if ($topic['is_faq']): ?>
            <span class="badge">FAQ</span>
        <?php endif; ?>
        <?php if ($topic['is_closed']): ?>
            <span class="badge closed">Closed</span>
        <?php endif; ?>
        
        <div class="topic-meta">
            <span>Posted by <?= htmlspecialchars($topic['username']) ?></span>
            <span>on <?= date('M j, Y g:i a', strtotime($topic['created_at'])) ?></span>
            <?php if ($topic['created_at'] != $topic['updated_at']): ?>
                <span>Last edited on <?= date('M j, Y g:i a', strtotime($topic['updated_at'])) ?></span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="topic-content">
        <?= nl2br(htmlspecialchars($topic['content'])) ?>
    </div>
    
    <?php if (isLoggedIn() && ($topic['user_id'] == $_SESSION['user_id'] || isAdmin())): ?>
        <div class="topic-actions">
            <a href="edit_topic.php?id=<?= $topic['id'] ?>" class="btn btn-edit">Edit Topic</a>
            <?php if (isAdmin()): ?>
                <form method="POST" action="moderate_topic.php" style="display: inline;">
                    <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                    <input type="hidden" name="action" value="<?= $topic['is_faq'] ? 'unmark_faq' : 'mark_faq' ?>">
                    <button type="submit" class="btn btn-faq">
                        <?= $topic['is_faq'] ? 'Unmark as FAQ' : 'Mark as FAQ' ?>
                    </button>
                </form>
                <form method="POST" action="moderate_topic.php" style="display: inline;">
                    <input type="hidden" name="topic_id" value="<?= $topic['id'] ?>">
                    <input type="hidden" name="action" value="<?= $topic['is_closed'] ? 'reopen' : 'close' ?>">
                    <button type="submit" class="btn btn-close">
                        <?= $topic['is_closed'] ? 'Reopen Topic' : 'Close Topic' ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="replies">
        <h2>Replies (<?= count($replies) ?>)</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (empty($replies)): ?>
            <p>No replies yet. Be the first to reply!</p>
        <?php else: ?>
            <?php foreach ($replies as $reply): ?>
                <div class="reply" id="reply-<?= $reply['id'] ?>">
                    <div class="reply-header">
                        <div class="reply-author">
                            <?= htmlspecialchars($reply['username']) ?>
                        </div>
                        <div class="reply-date">
                            Posted on <?= date('M j, Y g:i a', strtotime($reply['created_at'])) ?>
                            <?php if ($reply['created_at'] != $reply['updated_at']): ?>
                                <br>Edited on <?= date('M j, Y g:i a', strtotime($reply['updated_at'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="reply-content">
                        <?= nl2br(htmlspecialchars($reply['content'])) ?>
                    </div>
                    
                    <?php if (isLoggedIn() && ($reply['user_id'] == $_SESSION['user_id'] || isAdmin())): ?>
                        <div class="reply-actions">
                            <button class="btn-edit-reply" data-reply-id="<?= $reply['id'] ?>">Edit</button>
                            <form method="POST" class="delete-reply-form" style="display: inline;">
                                <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn-delete-reply">Delete</button>
                            </form>
                        </div>
                        
                        <form method="POST" class="edit-reply-form" style="display: none;" id="edit-form-<?= $reply['id'] ?>">
                            <input type="hidden" name="reply_id" value="<?= $reply['id'] ?>">
                            <input type="hidden" name="action" value="edit">
                            <textarea name="edited_content" class="edit-reply-textarea"><?= htmlspecialchars($reply['content']) ?></textarea>
                            <button type="submit" class="btn-save-edit">Save</button>
                            <button type="button" class="btn-cancel-edit">Cancel</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php if (isLoggedIn() && !$topic['is_closed']): ?>
        <div class="reply-form">
            <h3>Post a Reply</h3>
            <form method="POST">
                <textarea name="reply_content" id="reply_content" rows="5" required></textarea>
                <button type="submit" class="btn btn-primary">Post Reply</button>
            </form>
        </div>
    <?php elseif ($topic['is_closed']): ?>
        <div class="alert alert-info">
            This topic is closed and no longer accepting replies.
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Please <a href="login.php">login</a> to post a reply.
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit reply functionality
    document.querySelectorAll('.btn-edit-reply').forEach(button => {
        button.addEventListener('click', function() {
            const replyId = this.getAttribute('data-reply-id');
            const replyDiv = document.getElementById(`reply-${replyId}`);
            
            // Hide the content and show the edit form
            replyDiv.querySelector('.reply-content').style.display = 'none';
            replyDiv.querySelector('.reply-actions').style.display = 'none';
            replyDiv.querySelector('.edit-reply-form').style.display = 'block';
        });
    });
    
    // Cancel edit functionality
    document.querySelectorAll('.btn-cancel-edit').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('.edit-reply-form');
            const replyDiv = form.closest('.reply');
            
            // Show the content and hide the edit form
            replyDiv.querySelector('.reply-content').style.display = 'block';
            replyDiv.querySelector('.reply-actions').style.display = 'block';
            form.style.display = 'none';
        });
    });
    
    // Confirm before deleting
    document.querySelectorAll('.delete-reply-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this reply?')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<style>
.breadcrumb {
    margin-bottom: 20px;
    font-size: 0.9em;
    color: #666;
}

.breadcrumb a {
    color: #ff0000;
    text-decoration: none;
}

.breadcrumb a:hover {
    text-decoration: underline;
}

.topic-header {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.topic-header h1 {
    margin: 0 0 10px 0;
    display: inline-block;
}

.topic-meta {
    color: #666;
    font-size: 0.9em;
    margin-top: 10px;
}

.topic-meta span {
    margin-right: 15px;
}

.topic-content {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    line-height: 1.6;
}

.topic-actions {
    margin-bottom: 20px;
}

.badge {
    background: #ff0000;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8em;
    margin-left: 10px;
}

.badge.closed {
    background: #666;
}

.replies {
    margin-top: 30px;
}

.reply {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.reply-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.reply-author {
    font-weight: bold;
}

.reply-date {
    color: #666;
    font-size: 0.9em;
}

.reply-content {
    line-height: 1.6;
}

.reply-actions {
    margin-top: 15px;
    text-align: right;
}

.reply-actions button {
    background: none;
    border: none;
    color: #ff0000;
    cursor: pointer;
    padding: 5px 10px;
}

.reply-actions button:hover {
    text-decoration: underline;
}

.edit-reply-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    font-family: inherit;
    min-height: 100px;
}

.edit-reply-form button {
    padding: 5px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-save-edit {
    background: #4CAF50;
    color: white;
}

.btn-cancel-edit {
    background: #f44336;
    color: white;
    margin-left: 10px;
}

.reply-form {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.reply-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    font-family: inherit;
    min-height: 100px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-danger {
    background: #ffebee;
    border-left: 4px solid #f44336;
}

.alert-info {
    background: #e3f2fd;
    border-left: 4px solid #2196F3;
}

.alert-info a {
    color: #0d47a1;
    text-decoration: none;
}

.alert-info a:hover {
    text-decoration: underline;
}

.btn-edit, .btn-faq, .btn-close {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
}

.btn-edit {
    background: #2196F3;
    color: white;
}

.btn-faq {
    background: #FFC107;
    color: #000;
}

.btn-close {
    background: #f44336;
    color: white;
}
</style>