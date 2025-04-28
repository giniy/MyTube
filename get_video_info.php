<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['video_id'])) {
    echo json_encode(['error' => 'Video ID not provided']);
    exit;
}

$video_id = intval($_GET['video_id']);
$query = "SELECT title, description FROM videos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'Video not found']);
}
?>