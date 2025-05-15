<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$video_id = intval($_POST['video_id']);
$title = trim($_POST['title']);
$description = trim($_POST['description']);
$is_private = isset($_POST['is_private']) ? 1 : 0;
$age_restricted = isset($_POST['age_restricted']) ? 1 : 0;
$content_warning = !empty($_POST['content_warning']) ? trim($_POST['content_warning']) : null;

// Get video info including owner
$query = "SELECT user_id FROM videos WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $video_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'Video not found']);
    exit;
}

$video = $result->fetch_assoc();
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

// Verify user owns the video or is admin
if ($video['user_id'] != $user_id && !$is_admin) {
    echo json_encode(['status' => 'error', 'message' => 'You can only edit your own videos']);
    exit;
}

// Validate input
if (empty($title) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Title and description cannot be empty']);
    exit;
}

// Update video info
$updateQuery = "UPDATE videos SET title = ?, description = ?, is_private = ?, age_restricted = ?, content_warning = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("ssiisi", $title, $description, $is_private, $age_restricted, $content_warning, $video_id);

if ($updateStmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'title' => htmlspecialchars($title),
        'description' => htmlspecialchars($description),
        'is_private' => $is_private,
        'age_restricted' => $age_restricted,
        'content_warning' => $content_warning ? htmlspecialchars($content_warning) : null
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
?>