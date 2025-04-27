<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    http_response_code(403);
    echo "Not logged in.";
    exit;
}

if (isset($_POST['video_id'])) {
    $video_id = intval($_POST['video_id']);
    $user_id = $_SESSION['user_id'];

    // Check if user already liked
    $checkQuery = "SELECT id FROM likes WHERE user_id = ? AND video_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $user_id, $video_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Insert like
        $insertQuery = "INSERT INTO likes (user_id, video_id, created_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();
    } else {
        // Optional: Remove like if already liked (toggle)
        $deleteQuery = "DELETE FROM likes WHERE user_id = ? AND video_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $user_id, $video_id);
        $stmt->execute();
    }
}

echo "success";
?>
