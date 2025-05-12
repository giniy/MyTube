<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "mytube");

if ($conn->connect_error) {
    error_log("Signup: Database connection failed: " . $conn->connect_error);
    $_SESSION['message'] = "Database connection failed.";
    $_SESSION['message_type'] = "danger";
    header("Location: signup.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = trim($_POST['otp']);
    if ($user_otp == $_SESSION['signup_otp']) {
        $email = $_SESSION['signup_email'];
        $name = $_SESSION['signup_name'];
        $otp = $_SESSION['signup_otp']; // Use OTP as password

        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Signup: Failed to prepare username/email check query: " . $conn->error);
            $_SESSION['message'] = "Signup failed. Please try again.";
            $_SESSION['message_type'] = "danger";
            header("Location: signup.php");
            exit();
        }
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            error_log("Signup: Username or email already exists: username=$name, email=$email");
            $_SESSION['message'] = "Username or email already exists.";
            $_SESSION['message_type'] = "danger";
            header("Location: signup.php");
            exit();
        }

        // Insert user with auto-incremented ID, using OTP as password
        $hashed_password = password_hash($otp, PASSWORD_DEFAULT);
        $is_verified = 1;
        $query = "INSERT INTO users (username, email, password, is_verified) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Signup: Failed to prepare insert query: " . $conn->error);
            $_SESSION['message'] = "Signup failed. Please try again.";
            $_SESSION['message_type'] = "danger";
            header("Location: signup.php");
            exit();
        }
        $stmt->bind_param("sssi", $name, $email, $hashed_password, $is_verified);

        if ($stmt->execute()) {
            // Get the auto-generated ID
            $new_id = $conn->insert_id;
            error_log("Signup: Successfully created user with id=$new_id, email=$email");

            // Set session for auto-login
            $_SESSION['user_id'] = $new_id;
            $_SESSION['user_email'] = $email;

            // Clear signup session data
            unset($_SESSION['signup_otp'], $_SESSION['signup_email'], $_SESSION['signup_name']);

            $_SESSION['message'] = "Signup successful! Welcome!";
            $_SESSION['message_type'] = "success";
            header("Location: ../index.php");
            exit();
        } else {
            error_log("Signup: Failed to insert user: " . $stmt->error);
            $_SESSION['message'] = "Signup failed: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
            header("Location: signup.php");
            exit();
        }
        $stmt->close();
    } else {
        error_log("Signup: Invalid OTP for email=" . ($_SESSION['signup_email'] ?? 'unknown'));
        $_SESSION['message'] = "Invalid OTP!";
        $_SESSION['message_type'] = "danger";
        header("Location: verify_signup_otp.php");
        exit();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Signup OTP</title>
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
    <h2>Verify OTP</h2>
    <form method="post">
        <label for="otp">Enter OTP:</label>
        <input type="text" name="otp" required>
        <button type="submit">Verify</button>
    </form>
</div>
</body>
</html>