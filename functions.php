<?php  
function isAdmin() {
    // Check if user is logged in and has 'user_role' set in session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return false;
    }
    return $_SESSION['user_role'] === 'admin';
}
function isAdmin() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin'; // Adjust based on your role system
}
?>