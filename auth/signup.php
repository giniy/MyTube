<?php
session_start();

// Optional message from URL (e.g., logout or error redirect)
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
    <title>Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/mytube/static/css/auth2.css">
</head>
<body>
<div class="form-container">
    <!-- Session-based alert -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?> text-center mb-3">
            <?= $_SESSION['message'] ?>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <!-- URL-based alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center mb-3">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <h2>Signup</h2>
    <form action="send_signup_otp.php" method="post">
        <label for="name">Name:</label>
        <input type="text" name="username" class="form-control" required placeholder="Name">

        <label for="email">Email:</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter your email address">

        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    </form>

    <div class="login-footer mt-3">
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
