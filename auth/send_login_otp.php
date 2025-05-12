
<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'config.php'; // Adjust path as needed
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

$conn = new mysqli("localhost", "root", "root", "mytube");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $check = $conn->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $name = $row['username'];  // ✅ Get the username
        $otp = rand(100000, 999999);
        $_SESSION['login_otp'] = $otp;
        $_SESSION['login_email'] = $email;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USER;
            $mail->Password = SMTP_PASS;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            // $mail->setFrom(SMTP_USER, 'Login OTP');
            $mail->setFrom('noreply@gmail.com', 'Login OTP');
            $mail->addAddress($email);

            // $mail->Subject = 'Your Login OTP';
            // $mail->Body = "Your OTP for login is: $otp";
            $mail->isHTML(true);
            $mail->Subject = "Your One-Time Password (OTP) for Login";
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
                  background-color: #f9f9f9;
                  border: 1px solid #e0e0e0;
                  border-radius: 10px;
                }
                .header {
                  font-size: 24px;
                  font-weight: bold;
                  color: #333;
                  margin-bottom: 20px;
                }
                .otp-box {
                  font-size: 32px;
                  font-weight: bold;
                  color: #ffffff;
                  background-color: #007bff;
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
                <div class='header'>Login Verification</div>
                <p>Dear $name,</p>
                <p>To proceed with your login, please use the following One-Time Password (OTP):</p>
                <div class='otp-box'>$otp</div>
                <p>This OTP is valid for only a few minutes. Please do not share it with anyone.</p>
                <p>If you did not request this OTP, please ignore this message or contact support.</p>
                <div class='footer'>
                  &copy; " . date("Y") . " MyTube. All rights reserved.
                </div>
              </div>
            </body>
            </html>
            ";

            $mail->send();
            header("Location: verify_login_otp.php");

        } catch (Exception $e) {
            $_SESSION['message'] = "Error sending OTP";
            $_SESSION['message_type'] = "info"; // or "danger", "info"
            header("Location: login.php");
            // echo "Error sending OTP: {$mail->ErrorInfo}";
        exit();
        }
    } else {
        $_SESSION['message'] = "User not found or not verified.";
        $_SESSION['message_type'] = "info"; // or "danger", "info"
        header("Location: login.php");
        exit();
        // echo "<p class='error'>User not found or not verified.</p>";
    }
}
?>