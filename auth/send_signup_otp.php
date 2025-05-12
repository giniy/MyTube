<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php'; // Contains DB and SMTP constants
require_once '../includes/config.php';

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['username'] ?? '');
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email || empty($name)) {
        $_SESSION['message'] = "Invalid input.";
        $_SESSION['message_type'] = "danger";
        header("Location: signup.php");
        exit();
    }

    // Connect to the database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {

        $_SESSION['message'] = "Email already exists. Please login or use a different email.";
        $_SESSION['message_type'] = "warning";
        $stmt->close();
        $conn->close();
        header("Location: signup.php");
        exit();
    }
    $stmt->close();

    // Generate OTP and send email
    $otp = rand(100000, 999999);
    $_SESSION['signup_otp'] = $otp;
    $_SESSION['signup_email'] = $email;
    $_SESSION['signup_name'] = $name;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        // $mail->setFrom(SMTP_USER, 'Signup OTP');
        $mail->setFrom('noreply@gmail.com', 'Signup OTP');
        $mail->addAddress($email);      
        // $mail->Subject = 'Your Signup OTP';
        // $mail->Body = "Your OTP for signup is: $otp";
        $mail->isHTML(true);
        $mail->Subject = "Verify Your Email Address - OTP for Sign Up";
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset='UTF-8'>
          <style>
            .container {
              font-family: Arial, sans-serif;
              padding: 20px;
              max-width: 600px;
              margin: auto;
              background-color: #fdfdfd;
              border: 1px solid #e0e0e0;
              border-radius: 10px;
            }
            .header {
              font-size: 24px;
              font-weight: bold;
              color: #2c3e50;
              margin-bottom: 20px;
            }
            .otp-box {
              font-size: 32px;
              font-weight: bold;
              color: #ffffff;
              background-color: #28a745;
              padding: 15px 30px;
              display: inline-block;
              border-radius: 8px;
              margin: 20px 0;
            }
            .footer {
              font-size: 12px;
              color: #888;
              margin-top: 30px;
            }
          </style>
        </head>
        <body>
          <div class='container'>
            <div class='header'>Email Verification Required</div>
            <p>Hi there,</p>
            <p>Thank you for signing up! To complete your registration, please verify your email address by entering the OTP below:</p>
            <div class='otp-box'>$otp</div>
            <p>This One-Time Password (OTP) is valid for a limited time. Please do not share it with anyone.</p>
            <p>If you didn't sign up for an account, you can safely ignore this email.</p>
            <div class='footer'>
              &copy; " . date("Y") . " MyTube. All rights reserved.
            </div>
          </div>
        </body>
        </html>
        ";
        $mail->send();
        $conn->close();
        header("Location: verify_signup_otp.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = "Message could not be sent. Please try again later.";
        $_SESSION['message_type'] = "danger";
        $conn->close();
        header("Location: signup.php");
        exit();
    }
}
?>
