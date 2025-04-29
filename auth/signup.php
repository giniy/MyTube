<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon/play.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - MyTube</title>
    <!-- Using relative path -->
    <link href="../static/css/vid.css" rel="stylesheet">
    <link href="../static/css/auth.css" rel="stylesheet">
    
    <!-- OR using absolute path (better) -->
    <!-- <link href="/static/css/auth.css" rel="stylesheet"> -->
</head>
    <header>
    <div class="logo">
        <a class="nav-link" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php" 
           style="text-decoration: none; color: #ff0000; font-weight: bold;">
            MyTube
        </a>
    </div>
        <nav class="user-menu">
        <!--  -->
        </nav>
    </header>

<style type="text/css">
   body{

        background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.7) 0%,
        rgba(0, 0, 0, 0.5) 30%,
        rgba(0, 0, 0, 0.3) 60%,
        rgba(0, 0, 0, 0.1) 80%,
        rgba(0, 0, 0, 0) 200%
    );
    backdrop-filter: blur(50px);
    -webkit-backdrop-filter: blur(50px);
} 
</style>

<?php
require_once '../includes/config.php';

if (isLoggedIn()) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insertStmt->execute()) {
                // Changed from auto-login to success message
                $_SESSION['signup_success'] = 'Registration successful! Please login.';
                header('Location: login.php');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
// require_once '../includes/header.php';
?>

<main class="auth-container">
    <h2>Sign Up</h2>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit">Sign Up</button>
    </form>
    <p class="form_p">Already have an account? <a href="login.php" style="color: #ff0000;" >Login</a></p>
</main>

<?php require_once '../includes/footer.php'; ?>