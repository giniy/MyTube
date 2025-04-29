<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/terms.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="logo">
            <a class="nav-link" href="index.php" style="text-decoration: none; color: #ff0000; font-weight: bold;">MyTube</a>
        </div>
        <nav class="user-menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="upload.php">Upload Video</a>
                <a href="auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="auth/login.php">Login</a>
                <a href="auth/signup.php">Sign Up</a>
            <?php endif; ?>
        </nav>
    </header>
</body>
<?php
require_once 'includes/config.php';
// require_once 'includes/header.php';
// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim(filter_input(INPUT_POST, 'from_name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'from_email', FILTER_SANITIZE_EMAIL));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // If no errors, process the form
    if (empty($errors)) {
        // Save to database
        // Create the contact_messages table if it doesn't exist
        $conn->query("
            CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                is_read BOOLEAN DEFAULT FALSE
            )
        ");
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Send email using EmailJS (client-side)
            // The JavaScript will handle this part
        } else {
            $errors[] = "Error saving your message. Please try again.";
        }
    }
}

?>

<div class="contact-container">
    <h1>Contact Us</h1>
    <p>Have questions or feedback? We'd love to hear from you!</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h3>Error!</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <h3>Thank You!</h3>
            <p>Your message has been sent successfully. We'll get back to you soon.</p>
        </div>
    <?php endif; ?>

    <div class="contact-content">
        <div class="contact-info">
            <h2>Our Information</h2>
            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <span>support@mytube.com</span>
            </div>
            <div class="info-item">
                <i class="fas fa-phone"></i>
                <span>+91 9244330044</span>
            </div>
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>Workie Tech Park, Vijay Nagar, AB Road, Indore, MP, 452010</span>
            </div>
        </div>

        <div class="contact-form-container">
            <h2>Send Us a Message</h2>
            <form method="POST" id="contactForm">
                <div class="app-form-group">
                    <input class="app-form-control" id="name" placeholder="Name" name="from_name" 
                           value="<?= isset($_POST['from_name']) ? htmlspecialchars($_POST['from_name']) : '' ?>" required>
                </div>
                <div class="app-form-group">
                    <input class="app-form-control" id="email" type="email" placeholder="Email" name="from_email"
                           value="<?= isset($_POST['from_email']) ? htmlspecialchars($_POST['from_email']) : '' ?>" required>
                </div>
                <div class="app-form-group">
                    <textarea class="app-form-control" id="message" placeholder="Message" name="message" required><?= 
                        isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' 
                    ?></textarea>
                </div>
                <div class="app-form-group buttons">
                    <button class="app-form-button" type="submit" id="sendButton">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        emailjs.init("ZZLSDWRpVQ47uOfh2"); // Replace with your actual EmailJS Public Key
        
        document.getElementById("contactForm").addEventListener("submit", function(event) {
            event.preventDefault();
            
            const submitButton = document.getElementById("sendButton");
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            emailjs.sendForm("service_f3yw8gb", "template_lcn60zm", this)
                .then(function(response) {
                    console.log("✅ Message sent successfully!", response);
                    // Submit the form to PHP after email is sent
                    event.target.submit();
                })
                .catch(function(error) {
                    console.error("❌ Message Failed!", error);
                    // Still submit to PHP to save in database
                    event.target.submit();
                });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>

<style>

p {
    color: #a73aff;
    margin-left: 24px;
    margin-top: -18px;
}
    
.contact-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.contact-content {
    display: flex;
    gap: 40px;
    margin-top: 30px;
}

.contact-info, .contact-form-container {
    flex: 1;
}

.contact-info {
    background-color: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.contact-form-container {
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.info-item {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

.info-item i {
    font-size: 20px;
    color: #ff0000;
    margin-right: 15px;
    width: 30px;
    text-align: center;
}

.app-form-group {
    margin-bottom: 20px;
}

.app-form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: border-color 0.3s;
}

.app-form-control:focus {
    border-color: #ff0000;
    outline: none;
}

.app-form-control::placeholder {
    color: #999;
}

textarea.app-form-control {
    min-height: 150px;
    resize: vertical;
    color: #514545;
}

.buttons {
    text-align: right;
}

.app-form-button {
    background-color: #ff0000;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.app-form-button:hover {
    background-color: #cc0000;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

.alert-danger {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    border-left: 4px solid #28a745;
    color: #155724;
}

@media (max-width: 768px) {
    .contact-content {
        flex-direction: column;
    }
    
    .contact-info, .contact-form-container {
        width: 100%;
    }
}
</style>