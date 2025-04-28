<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle new comment
    if (isset($_POST['comment'], $_POST['video_id'])) {
        $comment = trim($_POST['comment']);
        $video_id = (int)$_POST['video_id'];
        $user_id = getUserId();
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        
        if (!empty($comment) && $video_id > 0) {
            $query = "INSERT INTO comments (user_id, video_id, comment, parent_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iisi", $user_id, $video_id, $comment, $parent_id);
            $stmt->execute();
        }
    }
    // Handle comment like
    elseif (isset($_POST['like_comment'], $_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        $user_id = getUserId();
        
        // Check if user already liked this comment
        $checkQuery = "SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $user_id, $comment_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            // Add like
            $likeQuery = "INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)";
            $stmt = $conn->prepare($likeQuery);
            $stmt->bind_param("ii", $user_id, $comment_id);
            $stmt->execute();
            
            // Update comment like count
            $updateQuery = "UPDATE comments SET like_count = like_count + 1 WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("i", $comment_id);
            $stmt->execute();
            
            echo "success";
            exit;
        }
    }
}

// Redirect back to the video page
header("Location: index.php?video_id=" . (isset($_POST['video_id']) ? (int)$_POST['video_id'] : ''));
exit;
?>