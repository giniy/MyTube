<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    die(json_encode(['status' => 'error', 'message' => 'Not logged in']));
}

if (!isset($_POST['video_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Video ID missing']));
}

$video_id = intval($_POST['video_id']);

// Update share count
$stmt = $conn->prepare("UPDATE videos SET share_count = share_count + 1 WHERE id = ?");
$stmt->bind_param("i", $video_id);

if ($stmt->execute()) {
    // Get updated share count
    $count = $conn->query("SELECT share_count FROM videos WHERE id = $video_id")->fetch_assoc();
    echo json_encode(['status' => 'success', 'share_count' => $count['share_count']]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>