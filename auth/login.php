<?php
session_start();

// Check for logout or session expiry messages
$message = '';
if (isset($_GET['logout'])) {
    $message = "You have been successfully logged out.";
} elseif (isset($_GET['expired'])) {
    $message = "Your session has expired. Please login again.";
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login with OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/mytube/static/css/auth2.css">

</head>
<body>

<div class="form-container">
<?php
if (isset($_SESSION['message'])) {
    $type = $_SESSION['message_type'] ?? 'info';
    echo "<div class='alert alert-$type'>" . $_SESSION['message'] . "</div>";
    unset($_SESSION['message'], $_SESSION['message_type']);
}
?>
    <h2>Login with OTP</h2>
    
    <?php if (!empty($message)): ?>
        <div class="message <?= strpos($message, 'success') !== false ? 'success' : (strpos($message, 'error') !== false ? 'error' : 'info') ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>
    
    <form action="send_login_otp.php" method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" required placeholder="Enter your email address">
        <button type="submit">Send OTP</button>
    </form>
    
    <div class="login-footer">
        <p>Don't have an account? <a href="signup.php">Sign up</a></p>
    </div>
</div>
</body>
</html>