<?php
// Suppress error display to ensure clean JSON output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

ob_start(); // Start output buffering to catch stray output

session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

$response = ['status' => 'error', 'message' => 'Unknown error'];

try {
    if (!isLoggedIn()) {
        $response = ['status' => 'error', 'message' => 'You must be logged in'];
        throw new Exception('Not logged in');
    }

    if (!isset($_POST['following_id']) || !is_numeric($_POST['following_id'])) {
        $response = ['status' => 'error', 'message' => 'Invalid user ID'];
        throw new Exception('Invalid following_id');
    }

    $follower_id = $_SESSION['user_id'];
    $following_id = (int)$_POST['following_id'];

    if ($follower_id == $following_id) {
        $response = ['status' => 'error', 'message' => 'You cannot subscribe to yourself'];
        throw new Exception('Self-subscription attempted');
    }

    // Check if already subscribed
    $checkQuery = "SELECT 1 FROM followers WHERE follower_id = ? AND following_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    if (!$checkStmt) {
        $response = ['status' => 'error', 'message' => 'Database error: Failed to prepare statement'];
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    $checkStmt->bind_param("ii", $follower_id, $following_id);
    $checkStmt->execute();
    $isSubscribed = $checkStmt->get_result()->num_rows > 0;

    if ($isSubscribed) {
        // Unsubscribe
        $deleteQuery = "DELETE FROM followers WHERE follower_id = ? AND following_id = ?";
        $deleteStmt = $conn->prepare($deleteQuery);
        if (!$deleteStmt) {
            $response = ['status' => 'error', 'message' => 'Database error: Failed to prepare statement'];
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $deleteStmt->bind_param("ii", $follower_id, $following_id);
        $deleteStmt->execute();
        $response = ['status' => 'success', 'action' => 'unfollowed'];
    } else {
        // Subscribe
        $insertQuery = "INSERT INTO followers (follower_id, following_id, created_at) VALUES (?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQuery);
        if (!$insertStmt) {
            $response = ['status' => 'error', 'message' => 'Database error: Failed to prepare statement'];
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $insertStmt->bind_param("ii", $follower_id, $following_id);
        $insertStmt->execute();
        $response = ['status' => 'success', 'action' => 'followed'];
    }
} catch (Exception $e) {
    error_log('follow_user.php error: ' . $e->getMessage());
} finally {
    ob_end_clean(); // Clear buffer to prevent stray output
    echo json_encode($response);
}
?>