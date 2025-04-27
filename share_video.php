<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    http_response_code(403);
    echo "Not logged in.";
    exit;
}

if (isset($_POST['video_id'])) {
    $video_id = intval($_POST['video_id']);
    
    // Update share count
    $shareQuery = "UPDATE videos SET share_count = share_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($shareQuery);
    $stmt->bind_param("i", $video_id);
    $stmt->execute();

    echo "shared";
}
?>
