<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get all categories with their topics
$categories = [];
$categoryQuery = $conn->query("SELECT * FROM forum_categories ORDER BY name");
while ($category = $categoryQuery->fetch_assoc()) {
    $categoryId = $category['id'];
    
    // Get topics for this category
    $topicQuery = $conn->prepare("SELECT t.*, u.username 
                                 FROM forum_topics t 
                                 JOIN users u ON t.user_id = u.id 
                                 WHERE t.category_id = ? 
                                 ORDER BY t.updated_at DESC");
    $topicQuery->bind_param("i", $categoryId);
    $topicQuery->execute();
    $topics = $topicQuery->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $category['topics'] = $topics;
    $categories[] = $category;
}

// Get recent activity
$recentActivity = $conn->query("
    SELECT 'topic' as type, t.id, t.title, t.updated_at, u.username 
    FROM forum_topics t 
    JOIN users u ON t.user_id = u.id 
    UNION 
    SELECT 'reply' as type, r.topic_id as id, t.title, r.updated_at, u.username 
    FROM forum_replies r 
    JOIN forum_topics t ON r.topic_id = t.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY updated_at DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="forum-container">
    <div class="forum-header">
        <h1>MyTube Community Forum</h1>
        <?php if (isLoggedIn()): ?>
            <a href="new_topic.php" class="btn btn-primary">New Topic</a>
        <?php endif; ?>
    </div>

    <div class="forum-layout">
        <div class="forum-main">
            <?php foreach ($categories as $category): ?>
                <div class="forum-category">
                    <h2><?= htmlspecialchars($category['name']) ?></h2>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                    
                    <div class="topic-list">
                        <?php if (empty($category['topics'])): ?>
                            <p>No topics yet. Be the first to post!</p>
                        <?php else: ?>
                            <?php foreach ($category['topics'] as $topic): ?>
                                <div class="topic-item">
                                    <div class="topic-info">
                                        <h3>
                                            <a href="view_topic.php?id=<?= $topic['id'] ?>">
                                                <?= htmlspecialchars($topic['title']) ?>
                                            </a>
                                            <?php if ($topic['is_faq']): ?>
                                                <span class="badge">FAQ</span>
                                            <?php endif; ?>
                                        </h3>
                                        <p>Posted by <?= htmlspecialchars($topic['username']) ?> 
                                        on <?= date('M j, Y g:i a', strtotime($topic['created_at'])) ?></p>
                                    </div>
                                    <div class="topic-stats">
                                        <span>Last updated: <?= date('M j, Y g:i a', strtotime($topic['updated_at'])) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="forum-sidebar">
            <div class="sidebar-section">
                <h3>Recent Activity</h3>
                <ul class="activity-list">
                    <?php foreach ($recentActivity as $activity): ?>
                        <li>
                            <?php if ($activity['type'] == 'topic'): ?>
                                New topic: <a href="view_topic.php?id=<?= $activity['id'] ?>"><?= htmlspecialchars($activity['title']) ?></a>
                            <?php else: ?>
                                Reply to: <a href="view_topic.php?id=<?= $activity['id'] ?>"><?= htmlspecialchars($activity['title']) ?></a>
                            <?php endif; ?>
                            <br>
                            <small>by <?= htmlspecialchars($activity['username']) ?> 
                            on <?= date('M j, Y g:i a', strtotime($activity['updated_at'])) ?></small>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="sidebar-section">
                <h3>Forum Rules</h3>
                <ol class="rules-list">
                    <li>Be respectful to other members</li>
                    <li>No spam or self-promotion</li>
                    <li>Keep discussions relevant</li>
                    <li>No offensive content</li>
                    <li>Report any issues to moderators</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
a {
    color: #ff0606;
    text-decoration: none;
}
    
.forum-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.forum-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.forum-layout {
    display: flex;
    gap: 30px;
}

.forum-main {
    flex: 1;
}

.forum-sidebar {
    width: 300px;
}

.forum-category {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.topic-list {
    margin-top: 20px;
}

.topic-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.topic-item:last-child {
    border-bottom: none;
}

.topic-info h3 {
    margin: 0 0 5px 0;
}

.topic-info p {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}

.topic-stats {
    text-align: right;
    font-size: 0.9em;
    color: #666;
}

.badge {
    background: #ff0000;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8em;
}

.sidebar-section {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.activity-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.activity-list li:last-child {
    border-bottom: none;
}

.rules-list {
    padding-left: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #ff0000;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    transition: background 0.3s;
}

.btn:hover {
    background: #cc0000;
}

@media (max-width: 768px) {
    .forum-layout {
        flex-direction: column;
    }
    
    .forum-sidebar {
        width: 100%;
    }
}
</style>