<?php
session_start();

// Redirect if no OTP session exists
if (!isset($_SESSION['login_otp']) || !isset($_SESSION['login_email'])) {
    $_SESSION['message'] = "OTP session expired or invalid.";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "root", "mytube");
if ($conn->connect_error) {
    $_SESSION['message'] = "Database connection failed.";
    $_SESSION['message_type'] = "danger";
    header("Location: login.php");
    exit();
}

$showSuccess = false;
$user = null; // Initialize user variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp']);

    if ($otp == $_SESSION['login_otp']) {
        $email = $_SESSION['login_email'];
        // Fetch user_id
        $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ? AND is_verified = 1");
        $stmt->bind_param("s", $email);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Secure session initialization
            session_regenerate_id(true);
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login_time'] = time();
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

            unset($_SESSION['login_otp'], $_SESSION['login_email']);
            $showSuccess = true;
        } else {
            $_SESSION['message'] = "User not found or not verified.";
            $_SESSION['message_type'] = "danger";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Invalid OTP. Please try again.";
        $_SESSION['message_type'] = "danger";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Login OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/mytube/static/css/auth2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .progress-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            position: relative;
            overflow: hidden;
            margin: 20px auto;
            max-width: 500px;
            text-align: center;
        }
        .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: #28a745;
            animation: progress 2.5s linear forwards;
        }
        .message-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .message-content i {
            color: #28a745;
            font-size: 20px;
        }
        @keyframes progress {
            from { width: 0%; }
            to { width: 100%; }
        }
    </style>
</head>
<body>
<div class="form-container">

<?php if ($showSuccess): ?>
    <div class="progress-message">
        <div class="progress-bar"></div>
        <div class="message-content">
            <i class="fas fa-check-circle"></i>
            <span>Welcome back, <?= htmlspecialchars($_SESSION['user_email']) ?>! Redirecting...</span>
        </div>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = "../index.php";
        }, 2500);
    </script>
<?php else: ?>
    <?php
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        echo "<div class='alert alert-$type'>" . $_SESSION['message'] . "</div>";
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
    ?>

    <h2>Enter OTP</h2>
    <form method="post">
        <label for="otp">OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
<?php endif; ?>
</div>
</body>
</html>