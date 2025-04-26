<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

if (!isAdmin()) {
    header("Location: forum.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topic_id']) && isset($_POST['action'])) {
    $topicId = intval($_POST['topic_id']);
    $action = $_POST['action'];
    
    switch ($action) {
        case 'mark_faq':
            $conn->query("UPDATE forum_topics SET is_faq = 1, updated_at = NOW() WHERE id = $topicId");
            break;
        case 'unmark_faq':
            $conn->query("UPDATE forum_topics SET is_faq = 0, updated_at = NOW() WHERE id = $topicId");
            break;
        case 'close':
            $conn->query("UPDATE forum_topics SET is_closed = 1, updated_at = NOW() WHERE id = $topicId");
            break;
        case 'reopen':
            $conn->query("UPDATE forum_topics SET is_closed = 0, updated_at = NOW() WHERE id = $topicId");
            break;
    }
}

header("Location: view_topic.php?id=" . $_POST['topic_id']);
exit;