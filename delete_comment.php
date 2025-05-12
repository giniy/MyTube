<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment_id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$comment_id = intval($_POST['comment_id']);

// First get the current user's ID from their email
$current_user_id = null;
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['user_email']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $current_user_id = $user['id'];
}

if (!$current_user_id) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
    exit;
}

// Get comment info to verify ownership
$query = "SELECT user_id FROM comments WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('HTTP/1.1 404 Not Found');
    echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
    exit;
}

$comment = $result->fetch_assoc();

// Check if user is owner or admin - now comparing IDs
$canDelete = ($current_user_id == $comment['user_id'] || (isset($_SESSION['is_admin']) && $_SESSION['is_admin']));

if (!$canDelete) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Delete comment likes first (due to foreign key constraint)
$deleteLikesQuery = "DELETE FROM comment_likes WHERE comment_id = ?";
$stmt = $conn->prepare($deleteLikesQuery);
$stmt->bind_param("i", $comment_id);
$stmt->execute();

// Delete comment replies first (due to foreign key constraint)
$deleteRepliesQuery = "DELETE FROM comments WHERE parent_id = ?";
$stmt = $conn->prepare($deleteRepliesQuery);
$stmt->bind_param("i", $comment_id);
$stmt->execute();

// Now delete the comment
$deleteQuery = "DELETE FROM comments WHERE id = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $comment_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment']);
}