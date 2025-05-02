<?php
require_once 'includes/config.php'; // Your DB connection

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['video_id'])) {
    if (!isLoggedIn()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $video_id = intval($_POST['video_id']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Check if user has already shared this video
        $checkQuery = "SELECT id FROM video_shares WHERE user_id = ? AND video_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $user_id, $video_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            // Record the share
            $shareQuery = "INSERT INTO video_shares (user_id, video_id) VALUES (?, ?)";
            $shareStmt = $conn->prepare($shareQuery);
            $shareStmt->bind_param("ii", $user_id, $video_id);
            $shareStmt->execute();

            // Increment share_count
            $updateQuery = "UPDATE videos SET share_count = share_count + 1 WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $video_id);
            $updateStmt->execute();

            // Get updated share_count
            $countQuery = "SELECT share_count FROM videos WHERE id = ?";
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param("i", $video_id);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $newShareCount = $countResult->fetch_assoc()['share_count'];

            $conn->commit();
            echo json_encode(['status' => 'success', 'share_count' => $newShareCount]);
        } else {
            // User has already shared; return current share_count
            $countQuery = "SELECT share_count FROM videos WHERE id = ?";
            $countStmt = $conn->prepare($countQuery);
            $countStmt->bind_param("i", $video_id);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $shareCount = $countResult->fetch_assoc()['share_count'];

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Already shared', 'share_count' => $shareCount]);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to process share: ' . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>