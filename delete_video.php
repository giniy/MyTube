<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start logging
$log = "[" . date('Y-m-d H:i:s') . "] DELETE PROCESS STARTED\n";

// Verify authentication
if (!isLoggedIn()) {
    file_put_contents('delete_debug.log', $log . "ERROR: Unauthorized access\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Get and validate video ID
$video_id = intval($_POST['video_id'] ?? 0);
if ($video_id <= 0) {
    file_put_contents('delete_debug.log', $log . "ERROR: Invalid video ID\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Invalid video ID']));
}

// Get video details
$stmt = $conn->prepare("SELECT user_id, video_file, thumbnail_file FROM videos WHERE id = ?");
$stmt->bind_param("i", $video_id);
if (!$stmt->execute()) {
    file_put_contents('delete_debug.log', $log . "DB ERROR: " . $stmt->error . "\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Database error']));
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    file_put_contents('delete_debug.log', $log . "ERROR: Video not found\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Video not found']));
}

$video = $result->fetch_assoc();
$user_id = $_SESSION['user_id'];

// Verify ownership or admin status
if ($video['user_id'] != $user_id && !(isset($_SESSION['is_admin']) && $_SESSION['is_admin'])) {
    file_put_contents('delete_debug.log', $log . "ERROR: Unauthorized user $user_id\n", FILE_APPEND);
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

// Build file paths
$videoPath = VIDEO_UPLOAD_PATH . $video['video_file'];
$thumbPath = THUMBNAIL_UPLOAD_PATH . $video['thumbnail_file'];

$log .= "Video path: $videoPath\n";
$log .= "Thumbnail path: $thumbPath\n";

// Delete files
$videoDeleted = file_exists($videoPath) ? unlink($videoPath) : true;
$thumbDeleted = file_exists($thumbPath) ? unlink($thumbPath) : true;

// Delete from database (with transaction)
$conn->begin_transaction();
try {
    // Delete dependent records first
    $conn->query("DELETE FROM likes WHERE video_id = $video_id");
    $conn->query("DELETE FROM comments WHERE video_id = $video_id");
    
    // Delete video
    $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception("No rows affected - video not deleted");
    }
    
    $conn->commit();
    $dbDeleted = true;
} catch (Exception $e) {
    $conn->rollback();
    $dbDeleted = false;
    $log .= "DB ERROR: " . $e->getMessage() . "\n";
}

$log .= "RESULTS:\n";
$log .= "Video deleted: " . ($videoDeleted ? 'Yes' : 'No') . "\n";
$log .= "Thumbnail deleted: " . ($thumbDeleted ? 'Yes' : 'No') . "\n";
$log .= "DB record deleted: " . ($dbDeleted ? 'Yes' : 'No') . "\n";

file_put_contents('delete_debug.log', $log, FILE_APPEND);

if ($dbDeleted && $videoDeleted && $thumbDeleted) {
    // Add this right before the success response
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        echo json_encode([
            'status' => 'success',
            'timestamp' => time() // Add timestamp to force refresh
        ]);
    // echo json_encode(['status' => 'success']);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Deletion incomplete',
        'details' => [
            'files' => ($videoDeleted && $thumbDeleted),
            'database' => $dbDeleted
        ]
    ]);
}
?>