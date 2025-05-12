<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function verifyUserSession() {
    if (!isset($_SESSION['user_id'], $_SESSION['user_email'])) {
        return false;
    }
    
    // Additional verification with database
    $stmt = $GLOBALS['conn']->prepare("SELECT id FROM users WHERE id = ? AND email = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $_SESSION['user_email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 1;
}

// In config.php or your authentication handler
function secureSession() {
    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);
    
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Enable if using HTTPS
    ini_set('session.use_strict_mode', 1);
    
    // Set session timeout (30 minutes)
    $_SESSION['last_activity'] = time();
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

// OTP expiration time (5 minutes)
define('OTP_EXPIRY', 300);

// Helper function to check if user is logged in

// Helper function to get current user ID
function getUserId() {
    if (!isset($_SESSION['user_email'])) {
        return null;
    }
    
    global $conn;
    $email = $_SESSION['user_email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }
    
    return null;
}

// Function to generate and store OTP
function generateOTP($email) {
    $otp = rand(100000, 999999);
    $_SESSION['login_otp'] = $otp;
    $_SESSION['login_email'] = $email;
    $_SESSION['otp_time'] = time();
    return $otp;
}

// Function to verify OTP
function verifyOTP($userOTP) {
    if (!isset($_SESSION['login_otp']) || !isset($_SESSION['otp_time'])) {
        return false;
    }
    
    // Check OTP expiry
    if (time() - $_SESSION['otp_time'] > OTP_EXPIRY) {
        unset($_SESSION['login_otp']);
        unset($_SESSION['login_email']);
        unset($_SESSION['otp_time']);
        return false;
    }
    
    if ($userOTP == $_SESSION['login_otp']) {
        $_SESSION['user_email'] = $_SESSION['login_email'];
        unset($_SESSION['login_otp']);
        unset($_SESSION['otp_time']);
        return true;
    }
    
    return false;
}
// Add this near the top of config.php
function validateSession() {
    // Check if session is expired (30 minutes inactivity)
    $inactive = 1800; // 30 minutes in seconds
    if (isset($_SESSION['last_activity'])) {
        $session_life = time() - $_SESSION['last_activity'];
        if ($session_life > $inactive) {
            session_unset();
            session_destroy();
            header("Location: auth/login.php?expired=1");
            exit();
        }
    }
    $_SESSION['last_activity'] = time();
}

// Call this function after session_start()
validateSession();