<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to dislike a video']);
    exit;
}

$user_id = $_SESSION['user_id'];
$video_id = isset($_POST['video_id']) ? intval($_POST['video_id']) : 0;

if ($video_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid video ID']);
    exit;
}

// Check if the video exists
$videoCheckQuery = "SELECT id FROM videos WHERE id = ?";
$stmt = $conn->prepare($videoCheckQuery);
$stmt->bind_param("i", $video_id);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Video not found']);
    exit;
}

// Check if the user has already disliked this video
$dislikeCheckQuery = "SELECT id FROM video_dislikes WHERE user_id = ? AND video_id = ?";
$dislikeStmt = $conn->prepare($dislikeCheckQuery);
$dislikeStmt->bind_param("ii", $user_id, $video_id);
$dislikeStmt->execute();
$dislikeResult = $dislikeStmt->get_result();

if ($dislikeResult->num_rows > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Already disliked']);
    exit;
}

// Check if the user had previously liked the video and remove the like
$likeDeleteQuery = "DELETE FROM likes WHERE user_id = ? AND video_id = ?";
// $likeDeleteQuery = "DELETE FROM video_likes WHERE user_id = ? AND video_id = ?";
$likeDeleteStmt = $conn->prepare($likeDeleteQuery);
$likeDeleteStmt->bind_param("ii", $user_id, $video_id);
$likeDeleteStmt->execute();

// Insert dislike into video_dislikes table
$insertDislikeQuery = "INSERT INTO video_dislikes (user_id, video_id, created_at) VALUES (?, ?, NOW())";
$insertStmt = $conn->prepare($insertDislikeQuery);
$insertStmt->bind_param("ii", $user_id, $video_id);
$success = $insertStmt->execute();

// Update the dislike count in the videos table
$updateDislikeCountQuery = "UPDATE videos SET dislike_count = dislike_count + 1 WHERE id = ?";
$updateStmt = $conn->prepare($updateDislikeCountQuery);
$updateStmt->bind_param("i", $video_id);
$updateStmt->execute();

// Get the updated dislike count

// At the end of dislike_video.php, replace the response code with this:

if ($success) {
    // Get the FINAL dislike count after all operations
    $finalCountQuery = "SELECT dislike_count FROM videos WHERE id = ?";
    $finalStmt = $conn->prepare($finalCountQuery);
    $finalStmt->bind_param("i", $video_id);
    $finalStmt->execute();
    $finalResult = $finalStmt->get_result();
    $finalDislikeCount = $finalResult->fetch_assoc()['dislike_count'];

    echo json_encode([
        'status' => 'success',
        'dislike_count' => (int)$finalDislikeCount,
        'message' => 'Video disliked successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to dislike video'
    ]);
}

$conn->close();
?>