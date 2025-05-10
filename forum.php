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
    <div class="logo-container" style="display: inline-block; width: 100px; height: 30px;">
        <a class="nav-link" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php" 
           style="text-decoration: none; display: flex; align-items: center; height: 100%; position: relative;">
            <!-- Animated Logo -->
            <img src="/mytube/static/images/play.png" 
                 alt="MyTube Logo" 
                 style="height: 24px; width: auto;
                        position: absolute;
                        left: 0;
                        margin-left: 70px;
                        animation: logoSwap 5s infinite ease-in-out;">
            <!-- Animated Text -->
            <span style="font-weight: bold; color: #ff0000;
                        position: absolute;
                        margin-left: 70px;
                        left: 30px; /* 24px logo + 6px gap */
                        animation: textSwap 5s infinite ease-in-out;">MyTube</span>
        </a>
    </div>
</header>
</body>

<?php
require_once 'includes/config.php';
// require_once 'includes/header.php';

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

    <!-- Add search form -->
    <div class="forum-search">
        <form id="forumSearchForm">
            <input type="text" id="forumSearchInput" placeholder="Search forum topics..." autocomplete="off">
            <button type="submit" class="btn">Search</button>
        </form>
        <div id="forumSearchResults" style="display: none;"></div>
    </div>

    <div class="forum-layout">
        <div class="forum-main">
            <?php foreach ($categories as $category): ?>
                <div class="forum-category" id="category-<?= $category['id'] ?>">
                    <h2><?= htmlspecialchars($category['name']) ?></h2>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                    
                    <div class="topic-list">
                        <?php if (empty($category['topics'])): ?>
                            <p>No topics yet. Be the first to post!</p>
                        <?php else: ?>
                            <?php foreach ($category['topics'] as $topic): ?>
                                <div class="topic-item" id="topic-<?= $topic['id'] ?>">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('forumSearchInput');
    const searchResults = document.getElementById('forumSearchResults');
    
    if (searchInput) {
        // Real-time search with debounce
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query === '') {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performForumSearch(query);
            }, 300);
        });
        
        // Handle form submission
        document.getElementById('forumSearchForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            performForumSearch(searchInput.value.trim());
        });
    }
    
    function performForumSearch(query) {
        if (query === '') {
            searchResults.style.display = 'none';
            return;
        }
        
        const categories = document.querySelectorAll('.forum-category');
        const topics = document.querySelectorAll('.topic-item');
        const recentActivities = document.querySelectorAll('.activity-list li');
        
        const results = [];
        const queryLower = query.toLowerCase();
        
        // Search categories
        categories.forEach(category => {
            const title = category.querySelector('h2').textContent.toLowerCase();
            const desc = category.querySelector('p').textContent.toLowerCase();
            const id = category.id;
            
            if (title.includes(queryLower) || desc.includes(queryLower)) {
                results.push({
                    type: 'Category',
                    title: category.querySelector('h2').textContent,
                    id: id,
                    content: category.querySelector('p').textContent,
                    score: title.includes(queryLower) ? 2 : 1
                });
            }
        });
        
        // Search topics
        topics.forEach(topic => {
            const title = topic.querySelector('h3 a').textContent.toLowerCase();
            const author = topic.querySelector('.topic-info p').textContent.toLowerCase();
            const id = topic.id;
            
            if (title.includes(queryLower)) {
                results.push({
                    type: 'Topic',
                    title: topic.querySelector('h3 a').textContent,
                    id: id,
                    content: 'Posted by ' + topic.querySelector('.topic-info p').textContent,
                    score: 2
                });
            } else if (author.includes(queryLower)) {
                results.push({
                    type: 'Topic',
                    title: topic.querySelector('h3 a').textContent,
                    id: id,
                    content: 'Posted by ' + topic.querySelector('.topic-info p').textContent,
                    score: 1
                });
            }
        });
        
        // Search recent activity
        recentActivities.forEach(activity => {
            const text = activity.textContent.toLowerCase();
            if (text.includes(queryLower)) {
                const link = activity.querySelector('a');
                results.push({
                    type: 'Activity',
                    title: link.textContent,
                    id: 'activity-' + results.length,
                    content: activity.textContent,
                    score: 1
                });
            }
        });
        
        // Sort results by relevance
        results.sort((a, b) => b.score - a.score);
        
        displayForumResults(results);
    }
    
    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }
    
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function displayForumResults(results) {
        const resultsContainer = document.getElementById('forumSearchResults');
        
        if (results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found. Try different keywords.</div>';
            resultsContainer.style.display = 'block';
            return;
        }
        
        let html = '<div class="results-header">';
        html += `<h3>Found ${results.length} ${results.length === 1 ? 'result' : 'results'}</h3>`;
        html += '</div><div class="results-list">';
        
        results.forEach(result => {
            // Determine the link URL based on result type
            let linkUrl = '#';
            if (result.type === 'Topic') {
                const topicId = result.id.replace('topic-', '');
                linkUrl = `view_topic.php?id=${topicId}`;
            } else if (result.type === 'Category') {
                linkUrl = `#${result.id}`;
            } else if (result.type === 'Activity') {
                // Extract topic ID from activity link if possible
                const activityLink = result.content.match(/view_topic\.php\?id=(\d+)/);
                if (activityLink && activityLink[1]) {
                    linkUrl = `view_topic.php?id=${activityLink[1]}`;
                }
            }
            
            html += `
                <div class="search-result">
                    <a href="${linkUrl}" class="result-link">
                        <div class="result-type">${result.type}</div>
                        <h4 class="result-title">${highlightText(result.title, searchInput.value.trim())}</h4>
                        <div class="result-content">${highlightText(result.content, searchInput.value.trim())}</div>
                    </a>
                </div>
            `;
        });
        
        html += '</div>';
        resultsContainer.innerHTML = html;
        resultsContainer.style.display = 'block';
    }
});

// Global function for scrolling to results
function scrollToForumResult(id) {
    const target = document.getElementById(id);
    if (target) {
        target.scrollIntoView({ behavior: 'smooth' });
        target.style.backgroundColor = '#fff8e1';
        setTimeout(() => {
            target.style.backgroundColor = '';
        }, 2000);
    }
}
</script>

<style>
/* Add these styles to your existing CSS */
.forum-search {
    margin-bottom: 30px;
    position: relative;
}

.forum-search input {
    padding: 12px;
    width: 70%;
    max-width: 600px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-right: 10px;
}

.forum-search button {
    padding: 12px 20px;
}

#forumSearchResults {
    position: absolute;
    width: 70%;
    max-width: 600px;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 4px 4px;
    z-index: 100;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.results-header {
    padding: 10px 15px;
    background: #f5f5f5;
    border-bottom: 1px solid #ddd;
}

.results-list {
    max-height: 400px;
    overflow-y: auto;
}

.search-result {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}

.search-result:hover {
    background-color: #f9f9f9;
}

.result-type {
    font-size: 12px;
    color: #ff0000;
    font-weight: bold;
    margin-bottom: 5px;
}

.result-title {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.result-content {
    font-size: 14px;
    color: #666;
}

.no-results {
    padding: 15px;
    text-align: center;
    color: #666;
}

.highlight {
    background-color: #fff8e1;
    font-weight: bold;
    padding: 0 2px;
    border-radius: 3px;
}
</style>

<?php require_once 'includes/footer.php'; ?>