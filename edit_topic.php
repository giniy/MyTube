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

<style type="text/css">
    
/* Base button style - applies to ALL buttons */
.btn {
    display: inline-flex;       /* Better alignment */
    align-items: center;
    justify-content: center;
    padding: 10px 24px;        /* Fixed padding */
    font-size: 14px;
    font-weight: 500;
    border-radius: 2px;        /* YouTube-style subtle rounding */
    cursor: pointer;
    text-decoration: none;
    border: none;
    min-width: 120px;          /* Forces equal width */
    height: 36px;              /* Fixed height */
    box-sizing: border-box;    /* Prevents sizing issues */
    transition: background 0.2s;
    margin: 0 8px;             /* Consistent spacing */
    text-transform: uppercase; /* YouTube-style caps */
    letter-spacing: 0.5px;
}

/* Primary button (red) */
.btn-primary {
    background: #ff0000;
    color: white;
}

/* Secondary button (gray) */
.btn-secondary {
    background: #f9f9f9;
    color: #606060;
    border: 1px solid #d3d3d3;
}

/* Hover states */
.btn-primary:hover {
    background: #cc0000;
}

.btn-secondary:hover {
    background: #f0f0f0;
    border-color: #c6c6c6;
}

/* Active/click effect */
.btn:active {
    transform: translateY(1px);
}
    
</style>
<?php require_once 'includes/footer.php'; ?>