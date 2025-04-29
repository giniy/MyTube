<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon/play.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MyTube</title>
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

if (isset($_SESSION['signup_success'])) {
    $success = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']); // Clear the message after displaying
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $query = "SELECT id, username, password FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header('Location: ../index.php');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// require_once '../includes/header.php';
?>

<main class="auth-container">
    <h2>Login</h2>

    <?php if ($success): ?>
         <div class="success"><?= $success ?></div>
    <?php endif; ?>     


     <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p class="form_p" >Don't have an account? <a href="signup.php" style="color: #ff0000;">Sign up</a></p>
</main>

<?php require_once '../includes/footer.php'; ?>