<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: forum.php");
    exit;
}

$topicId = intval($_GET['id']);

// Get topic
$topicQuery = $conn->prepare("
    SELECT t.*, u.username 
    FROM forum_topics t 
    JOIN users u ON t.user_id = u.id 
    WHERE t.id = ?
");
$topicQuery->bind_param("i", $topicId);
$topicQuery->execute();
$topic = $topicQuery->get_result()->fetch_assoc();

if (!$topic || ($topic['user_id'] != $_SESSION['user_id'] && !isAdmin())) {
    header("Location: forum.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    // Validate
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($content)) {
        $errors[] = "Content is required";
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE forum_topics SET title = ?, content = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $title, $content, $topicId);
        
        if ($stmt->execute()) {
            header("Location: view_topic.php?id=$topicId");
            exit;
        } else {
            $errors[] = "Error updating topic: " . $conn->error;
        }
    }
}

// Get categories
$categories = $conn->query("SELECT * FROM forum_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<div class="forum-container">
    <h1>Edit Topic</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" class="topic-form">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" required 
                   value="<?= htmlspecialchars($topic['title']) ?>">
        </div>
        
        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" id="content" rows="10" required><?= htmlspecialchars($topic['content']) ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="view_topic.php?id=<?= $topic['id'] ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>