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
$user_id = $_SESSION['user_id'];

// Check if already liked
$check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
$check->bind_param("ii", $user_id, $video_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    die("already_liked");
}

// Add like
$stmt = $conn->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)");
$stmt->bind_param("ii", $user_id, $video_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
?>