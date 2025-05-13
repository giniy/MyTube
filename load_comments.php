<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$video_id = isset($_GET['video_id']) ? (int)$_GET['video_id'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$commentsPerPage = 5;
$offset = ($page - 1) * $commentsPerPage;

if ($video_id > 0) {
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
    $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
    $stmt = $conn->prepare($likesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $likesResult = $stmt->get_result();
    $likeData = $likesResult->fetch_assoc();
    
    $likersQuery = "SELECT u.username FROM comment_likes cl JOIN users u ON cl.user_id = u.id 
                    WHERE cl.comment_id = ? ORDER BY cl.created_at DESC LIMIT 5";
    $likerStmt = $conn->prepare($likersQuery);
    $likerStmt->bind_param("i", $comment['id']);
    $likerStmt->execute();
    $likersResult = $likerStmt->get_result();
    $likers = [];
    while ($liker = $likersResult->fetch_assoc()) {
        $likers[] = $liker['username'];
    }
    
    $repliesQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id 
                     WHERE c.parent_id = ? ORDER BY c.created_at ASC";
    $stmt = $conn->prepare($repliesQuery);
    $stmt->bind_param("i", $comment['id']);
    $stmt->execute();
    $repliesResult = $stmt->get_result();
    
    $margin = $depth * 20;
    $canDeleteComment = false;
    $canEditComment = false;
    if (isLoggedIn())