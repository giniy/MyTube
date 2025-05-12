<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (isset($_GET['video_id'])) {
    $videoId = intval($_GET['video_id']);
    
    try {
        $stmt = $conn->prepare("SELECT 
            (SELECT COUNT(*) FROM likes WHERE video_id = ?) as like_count,
            (SELECT share_count FROM videos WHERE id = ?) as share_count,
            (SELECT view_count FROM videos WHERE id = ?) as view_count
        ");
        $stmt->bind_param("iii", $videoId, $videoId, $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Video ID required'
    ]);
}
?>