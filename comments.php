<?php
// Start output buffering
ob_start();

// Configure error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);


try {
    // Include dependencies
    require_once 'includes/config.php';
    require_once 'includes/functions.php';

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Log request data

    // Set JSON header
    header('Content-Type: application/json');

    // Check if POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate user session
    if (!isLoggedIn()) {
        throw new Exception('Unauthorized');
    }

    // Validate CSRF token
    $received_csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    $expected_csrf = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if ($received_csrf !== $expected_csrf) {
        throw new Exception('Invalid CSRF token');
    }

    // Handle comment posting
    if (isset($_POST['comment'], $_POST['video_id'])) {
        $comment = trim($_POST['comment']);
        $video_id = (int)$_POST['video_id'];
        $user_id = getUserId();
        $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

        if (empty($comment) || $video_id <= 0 || $user_id <= 0) {
            throw new Exception('Invalid comment or video ID');
        }


        // Insert comment
        $query = "INSERT INTO comments (user_id, video_id, comment, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("iisi", $user_id, $video_id, $comment, $parent_id);

        if ($stmt->execute()) {
            $comment_id = $stmt->insert_id;

            // Fetch new comment
            $fetchQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?";
            $fetchStmt = $conn->prepare($fetchQuery);
            $fetchStmt->bind_param("i", $comment_id);
            $fetchStmt->execute();
            $result = $fetchStmt->get_result();
            $newComment = $result->fetch_assoc();
            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'comment' => $newComment
            ]);
        } else {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        exit;
    }

    // Handle comment editing
    if (isset($_POST['comment'], $_POST['comment_id'])) {
        $comment = trim($_POST['comment']);
        $comment_id = (int)$_POST['comment_id'];
        $user_id = getUserId();

        if (empty($comment) || $comment_id <= 0 || $user_id <= 0) {
            throw new Exception('Invalid comment or comment ID');
        }

        // Check if user is authorized to edit
        $authQuery = "SELECT user_id FROM comments WHERE id = ?";
        $authStmt = $conn->prepare($authQuery);
        $authStmt->bind_param("i", $comment_id);
        $authStmt->execute();
        $authResult = $authStmt->get_result();
        $commentData = $authResult->fetch_assoc();

        if (!$commentData) {
            throw new Exception('Comment not found');
        }

        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
        if ($commentData['user_id'] != $user_id && !$isAdmin) {
            throw new Exception('Unauthorized to edit this comment');
        }

        // Update comment
        $updateQuery = "UPDATE comments SET comment = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param("si", $comment, $comment_id);

        if ($stmt->execute()) {

            // Fetch updated comment
            $fetchQuery = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?";
            $fetchStmt = $conn->prepare($fetchQuery);
            $fetchStmt->bind_param("i", $comment_id);
            $fetchStmt->execute();
            $result = $fetchStmt->get_result();
            $updatedComment = $result->fetch_assoc();

            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'comment' => $updatedComment
            ]);
        } else {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        exit;
    }

    // Handle comment liking
    if (isset($_POST['like_comment'], $_POST['comment_id'])) {
        $comment_id = (int)$_POST['comment_id'];
        $user_id = getUserId();

        if ($comment_id <= 0 || $user_id <= 0) {
            throw new Exception('Invalid comment ID or user');
        }

        // Verify comment exists
        $checkCommentQuery = "SELECT id FROM comments WHERE id = ?";
        $checkStmt = $conn->prepare($checkCommentQuery);
        $checkStmt->bind_param("i", $comment_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            throw new Exception('Comment not found');
        }

        // Check if already liked
        $checkLikeQuery = "SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?";
        $stmt = $conn->prepare($checkLikeQuery);
        $stmt->bind_param("ii", $user_id, $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Insert like
            $likeQuery = "INSERT INTO comment_likes (user_id, comment_id, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($likeQuery);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $comment_id);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }

            // Update like count
            $updateQuery = "UPDATE comments SET like_count = like_count + 1 WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            $stmt->bind_param("i", $comment_id);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }

            // Fetch likers
            $likersQuery = "SELECT u.username FROM comment_likes cl JOIN users u ON cl.user_id = u.id 
                            WHERE cl.comment_id = ? ORDER BY cl.created_at DESC LIMIT 5";
            $likerStmt = $conn->prepare($likersQuery);
            $likerStmt->bind_param("i", $comment_id);
            $likerStmt->execute();
            $likersResult = $likerStmt->get_result();
            $likers = [];
            while ($liker = $likersResult->fetch_assoc()) {
                $likers[] = $liker['username'];
            }

            // Get total likes
            $likesQuery = "SELECT COUNT(*) as like_count FROM comment_likes WHERE comment_id = ?";
            $stmt = $conn->prepare($likesQuery);
            $stmt->bind_param("i", $comment_id);
            $stmt->execute();
            $likesResult = $stmt->get_result();
            $likeData = $likesResult->fetch_assoc();

            ob_end_clean();
            echo json_encode([
                'status' => 'success',
                'like_count' => $likeData['like_count'],
                'likers' => $likers
            ]);
        } else {
            throw new Exception('You have already liked this comment');
        }
        exit;
    }

    throw new Exception('Invalid request');

} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>