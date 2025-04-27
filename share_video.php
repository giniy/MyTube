<?php
require_once 'includes/config.php'; // Your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'])) {
    if (!isLoggedIn()) {
        http_response_code(403);
        echo "Not logged in.";
        exit;
    }

    $video_id = intval($_POST['video_id']);

    $shareQuery = "UPDATE videos SET share_count = share_count + 1 WHERE id = ?";
    $stmt = $conn->prepare($shareQuery);

    if ($stmt) {
        $stmt->bind_param("i", $video_id);
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $stmt->close();
    } else {
        echo "error";
    }
} else {
    echo "invalid";
}
?>
