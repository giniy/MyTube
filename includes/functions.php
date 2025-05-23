<?php
require_once 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Call CSRF token generation
generateCsrfToken();
?>

<?php

function isLoggedIn() {
    // Check if all required session variables exist
    return isset($_SESSION['user_id'], $_SESSION['user_email'], $_SESSION['login_time'], 
           $_SESSION['ip_address'], $_SESSION['user_agent']) &&
           // Verify session hasn't expired (1 hour timeout)
           (time() - $_SESSION['login_time'] < 3600) &&
           // Verify IP and User Agent haven't changed
           ($_SESSION['ip_address'] === $_SERVER['REMOTE_ADDR']) &&
           ($_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT']);
}
?>

<?php
function formatUserDate($datetime) {
    $timezone = $_COOKIE['user_timezone'] ?? 'UTC';
    
    try {
        $date = new DateTime($datetime, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));
        
        // Timezone → Abbreviation mapping (with DST support)
        $tzAbbrMap = [
            // Americas
            'America/New_York'      => $date->format('I') ? 'EDT' : 'EST',
            'America/Chicago'        => $date->format('I') ? 'CDT' : 'CST',
            'America/Denver'        => $date->format('I') ? 'MDT' : 'MST',
            'America/Los_Angeles'   => $date->format('I') ? 'PDT' : 'PST',
            'America/Sao_Paulo'     => $date->format('I') ? 'BRST' : 'BRT',
            
            // Europe
            'Europe/London'         => $date->format('I') ? 'BST' : 'GMT',
            'Europe/Berlin'         => $date->format('I') ? 'CEST' : 'CET',
            'Europe/Paris'         => $date->format('I') ? 'CEST' : 'CET',
            'Europe/Moscow'         => 'MSK',
            
            // Asia
            'Asia/Tokyo'           => 'JST',
            'Asia/Shanghai'        => 'CST',
            'Asia/Seoul'          => 'KST',
            'Asia/Kolkata'         => 'IST',
            'Asia/Dubai'           => 'GST',
            'Asia/Jerusalem'       => $date->format('I') ? 'IDT' : 'IST',
            'Asia/Bangkok'         => 'ICT',
            'Asia/Hong_Kong'       => 'HKT',
            'Asia/Singapore'       => 'SGT',
            
            // Australia/Oceania
            'Australia/Sydney'     => $date->format('I') ? 'AEDT' : 'AEST',
            'Australia/Melbourne'  => $date->format('I') ? 'AEDT' : 'AEST',
            'Pacific/Auckland'     => $date->format('I') ? 'NZDT' : 'NZST',
            
            // Africa
            'Africa/Cairo'         => 'EET',
            'Africa/Johannesburg'  => 'SAST',
            'Africa/Casablanca'    => 'WET',
            
            // Other
            'Asia/Riyadh'          => 'AST',
            'Asia/Tehran'          => 'IRST',
            'Asia/Karachi'        => 'PKT',
            'Asia/Dhaka'          => 'BST',
        ];
        
        $abbr = $tzAbbrMap[$timezone] ?? $date->format('T');  // Fallback to PHP's abbreviation
        
        return $date->format('F j, Y, g:i A') . " " . $abbr;
        
    } catch (Exception $e) {
        // Fallback if timezone conversion fails
        return date('F j, Y, g:i A', strtotime($datetime)) . " (UTC)";
    }
}

function getUserData($userId) {
    global $conn;
    $query = "SELECT id, username, email, bio, profile_picture, created_at, 
              twitter, instagram, youtube, other, gender FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserVideos($userId) {
    global $conn;
    $query = "SELECT * FROM videos WHERE user_id = ? ORDER BY uploaded_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserComments($userId) {
    global $conn;
    $query = "SELECT c.*, v.title as video_title 
              FROM comments c 
              JOIN videos v ON c.video_id = v.id 
              WHERE c.user_id = ? 
              ORDER BY c.created_at DESC 
              LIMIT 20";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getUserLikes($userId) {
    global $conn;
    $query = "SELECT v.* 
              FROM likes l 
              JOIN videos v ON l.video_id = v.id 
              WHERE l.user_id = ? 
              ORDER BY l.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function getProfilePicture($userId) {
    $default = 'static/images/default-profile.png';
    $custom = 'uploads/profile_pictures/' . $userId . '.jpg';
    return file_exists($custom) ? $custom : $default;
    global $conn;
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return $user['profile_picture'] ?? 'default.jpg'; // Fallback to a default image
}

function getUsername($userId) {
    global $conn;
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    return $user['username'] ?? $_SESSION['user_email'] ?? 'User';
}

// Add this function to get user by email
function getUserByEmail($email) {
    global $conn;
    $query = "SELECT id, username, email, bio, profile_picture, created_at, 
              twitter, instagram, youtube, other, gender FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function formatDuration($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $seconds = $seconds % 60;
    
    if ($hours > 0) {
        return sprintf("%d:%02d:%02d", $hours, $minutes, $seconds);
    } else {
        return sprintf("%d:%02d", $minutes, $seconds);
    }
}

function timeElapsedString($datetime) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

function getWatchedVideos($userId, $limit = 20) {
    global $conn;
    
    $query = "SELECT v.*, u.username, vv.viewed_at 
              FROM video_views vv 
              JOIN videos v ON vv.video_id = v.id 
              JOIN users u ON v.user_id = u.id 
              WHERE vv.user_id = ? 
              ORDER BY vv.viewed_at DESC 
              LIMIT ?";
              
    $stmt = $conn->prepare($query);
    $limit = (int)$limit;
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $watchedVideos = [];
    while ($row = $result->fetch_assoc()) {
        $watchedVideos[] = $row;
    }
    
    $stmt->close();
    return $watchedVideos;
}