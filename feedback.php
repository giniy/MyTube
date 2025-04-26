<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Initialize variables
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $feedback_type = trim(filter_input(INPUT_POST, 'feedback_type', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (!in_array($feedback_type, ['suggestion', 'bug', 'compliment', 'general'])) {
        $errors[] = "Please select a valid feedback type";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    if ($rating < 0 || $rating > 5) {
        $errors[] = "Invalid rating value";
    }

    // If no errors, process the form
    if (empty($errors)) {
        // Save to database
        $stmt = $conn->prepare("INSERT INTO feedback (name, email, feedback_type, message, rating, created_at) 
                               VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssi", $name, $email, $feedback_type, $message, $rating);
        
        if ($stmt->execute()) {
            $success = true;
            
            // Send email notification (client-side via EmailJS)
            // The JavaScript will handle this part
        } else {
            $errors[] = "Error saving your feedback. Please try again.";
        }
    }
}

// Create the feedback table if it doesn't exist
$conn->query("
    CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        feedback_type ENUM('suggestion', 'bug', 'compliment', 'general') NOT NULL,
        message TEXT NOT NULL,
        rating TINYINT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_processed BOOLEAN DEFAULT FALSE
    )
");
?>

<div class="feedback-container">
    <h1>Feedback & Suggestions</h1>
    <p class="subtitle">We value your opinion! Help us improve MyTube by sharing your thoughts.</p>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <h3>Please fix these issues:</h3>
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
            <p>Your feedback has been submitted successfully. We appreciate your input!</p>
        </div>
    <?php endif; ?>

    <div class="feedback-content">
        <div class="feedback-form-container">
            <form method="POST" id="feedbackForm">
                <div class="form-group">
                    <label for="name">Your Name *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Your Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="feedback_type">Feedback Type *</label>
                    <select id="feedback_type" name="feedback_type" class="form-control" required>
                        <option value="">-- Select a type --</option>
                        <option value="suggestion" <?= isset($_POST['feedback_type']) && $_POST['feedback_type'] === 'suggestion' ? 'selected' : '' ?>>Suggestion</option>
                        <option value="bug" <?= isset($_POST['feedback_type']) && $_POST['feedback_type'] === 'bug' ? 'selected' : '' ?>>Bug Report</option>
                        <option value="compliment" <?= isset($_POST['feedback_type']) && $_POST['feedback_type'] === 'compliment' ? 'selected' : '' ?>>Compliment</option>
                        <option value="general" <?= isset($_POST['feedback_type']) && $_POST['feedback_type'] === 'general' ? 'selected' : '' ?>>General Feedback</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>How would you rate your experience? (optional)</label>
                    <div class="rating-container">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" 
                                   <?= isset($_POST['rating']) && $_POST['rating'] == $i ? 'checked' : '' ?>>
                            <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Your Feedback *</label>
                    <textarea id="message" name="message" class="form-control" rows="6" required><?= 
                        isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' 
                    ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-submit" id="submitButton">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>

        <div class="feedback-info">
            <h2>Why Your Feedback Matters</h2>
            <div class="info-card">
                <i class="fas fa-lightbulb"></i>
                <h3>Suggest Features</h3>
                <p>Tell us what features you'd like to see in future updates.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-bug"></i>
                <h3>Report Issues</h3>
                <p>Help us identify and fix bugs to improve the platform.</p>
            </div>
            <div class="info-card">
                <i class="fas fa-heart"></i>
                <h3>Share Your Thoughts</h3>
                <p>We appreciate both positive feedback and constructive criticism.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/emailjs-com@3/dist/email.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        emailjs.init("ZZLSDWRpVQ47uOfh2"); // Replace with your actual EmailJS Public Key
        
        document.getElementById("feedbackForm").addEventListener("submit", function(event) {
            event.preventDefault();
            
            const submitButton = document.getElementById("submitButton");
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            
            emailjs.sendForm("service_f3yw8gb", "template_lcn60zm", this)
                .then(function(response) {
                    console.log("✅ Feedback sent successfully!", response);
                    // Submit the form to PHP after email is sent
                    event.target.submit();
                })
                .catch(function(error) {
                    console.error("❌ Failed to send feedback:", error);
                    // Still submit to PHP to save in database
                    event.target.submit();
                });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>

<style>
.feedback-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.subtitle {
    color: #666;
    font-size: 1.1em;
    margin-bottom: 30px;
    text-align: center;
}

.feedback-content {
    display: flex;
    gap: 40px;
    margin-top: 20px;
}

.feedback-form-container {
    flex: 1;
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.feedback-info {
    width: 350px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #ff0000;
    outline: none;
    box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
}

textarea.form-control {
    min-height: 150px;
    resize: vertical;
}

.rating-container {
    display: flex;
    direction: rtl; /* Right to left for star rating */
    justify-content: flex-end;
}

.rating-container input {
    display: none;
}

.rating-container label {
    color: #ddd;
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s;
}

.rating-container input:checked ~ label,
.rating-container input:hover ~ label,
.rating-container label:hover,
.rating-container label:hover ~ label {
    color: #ffcc00;
}

.rating-container input:checked + label {
    color: #ffcc00;
}

.form-actions {
    text-align: center;
    margin-top: 30px;
}

.btn-submit {
    background-color: #ff0000;
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 4px;
    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-submit:hover {
    background-color: #cc0000;
}

.info-card {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #ff0000;
}

.info-card i {
    font-size: 24px;
    color: #ff0000;
    margin-bottom: 10px;
}

.info-card h3 {
    margin: 0 0 10px 0;
    color: #333;
}

.info-card p {
    margin: 0;
    color: #666;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 30px;
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
    .feedback-content {
        flex-direction: column;
    }
    
    .feedback-info {
        width: 100%;
        margin-top: 30px;
    }
    
    .rating-container {
        justify-content: center;
    }
}
</style>