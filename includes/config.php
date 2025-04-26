
<?php
session_start();

function isAdmin() {
    // Check if user is logged in and has 'user_role' set in session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return false;
    }
    return $_SESSION['user_role'] === 'admin';
}
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'mytube');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// File upload paths
define('VIDEO_UPLOAD_PATH', 'uploads/videos/');
define('THUMBNAIL_UPLOAD_PATH', 'uploads/thumbnails/');

// Create upload directories if they don't exist
if (!file_exists(VIDEO_UPLOAD_PATH)) {
    mkdir(VIDEO_UPLOAD_PATH, 0777, true);
}
if (!file_exists(THUMBNAIL_UPLOAD_PATH)) {
    mkdir(THUMBNAIL_UPLOAD_PATH, 0777, true);
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper function to get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>