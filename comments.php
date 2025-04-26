<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['video_id'])) {
    $comment = trim($_POST['comment']);
    $video_id = (int)$_POST['video_id'];
    $user_id = getUserId();
    
    if (!empty($comment) && $video_id > 0) {
        $query = "INSERT INTO comments (user_id, video_id, comment) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $user_id, $video_id, $comment);
        $stmt->execute();
    }
}

// Redirect back to the video page
header("Location: index.php");
exit;
?>