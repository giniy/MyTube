<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$userId = getUserId();
$userData = getUserData($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);

    // Validate inputs
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) > 30) {
        $errors[] = "Username must be less than 30 characters";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    // Check if username/email already exists (excluding current user)
    $checkUser = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $checkUser->bind_param("ssi", $username, $email, $userId);
    $checkUser->execute();
    if ($checkUser->get_result()->num_rows > 0) {
        $errors[] = "Username or email already in use";
    }

    // Handle profile picture upload
    $profilePicture = $userData['profile_picture'] ?? '';
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['profile_picture']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $targetDir = "uploads/profile_pictures/";
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $targetFile = $targetDir . $userId . ".jpg";

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                $profilePicture = $targetFile;
                // resizeImage($targetFile, 300, 300);
            } else {
                $errors[] = "Failed to upload profile picture";
            }
        } else {
            $errors[] = "Only JPG, PNG, and GIF files are allowed";
        }
    }

    // Get social media links
    $twitter = trim($_POST['twitter'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $youtube = trim($_POST['youtube'] ?? '');
    $other = trim($_POST['other'] ?? '');

    // Update database if no errors
    if (empty($errors)) {
        $query = "UPDATE users SET 
                  username = ?, email = ?, bio = ?, profile_picture = ?,
                  twitter = ?, instagram = ?, youtube = ?, other = ?
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssi", 
                         $username, $email, $bio, $profilePicture,
                         $twitter, $instagram, $youtube, $other,
                         $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully!";
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Failed to update profile";
        }
    }
}

// Handle password change if fields are filled
if (!empty($_POST['current_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!password_verify($current, $user['password'])) {
        $errors[] = "Current password is incorrect";
    } elseif ($new !== $confirm) {
        $errors[] = "New passwords don't match";
    } elseif (strlen($new) < 8) {
        $errors[] = "Password must be at least 8 characters";
    } else {
        // Update password
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $userId);
        $update->execute();
        $_SESSION['success'] = "Password updated successfully!";
    }
}
?>

<div class="edit-profile-container">
    <h1>Edit Profile</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error-messages">
            <?php foreach ($errors as $error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form action="edit_profile.php" method="POST" enctype="multipart/form-data" class="edit-profile-form">
        <div class="form-group">
            <label for="profile-picture">Profile Picture</label>
            <div class="profile-picture-preview">
                <img src="<?= getProfilePicture($userId) ?>" alt="Current Profile Picture" id="profile-preview">
            </div>
            <input type="file" id="profile-picture" name="profile_picture" accept="image/*">
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($userData['username']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($userData['bio'] ?? '') ?></textarea>
        </div>
        
        <!-- Social Media Links -->
        <div class="social-media-section">
            <h3>Social Media Links</h3>
            <div class="form-group">
                <label for="twitter">Twitter</label>
                <input type="url" id="twitter" name="twitter" 
                       value="<?= htmlspecialchars($userData['twitter'] ?? '') ?>"
                       placeholder="https://twitter.com/username">
            </div>
            <div class="form-group">
                <label for="instagram">Instagram</label>
                <input type="url" id="instagram" name="instagram" 
                       value="<?= htmlspecialchars($userData['instagram'] ?? '') ?>"
                       placeholder="https://instagram.com/username">
            </div>
            <div class="form-group">
                <label for="youtube">YouTube</label>
                <input type="url" id="youtube" name="youtube" 
                       value="<?= htmlspecialchars($userData['youtube'] ?? '') ?>"
                       placeholder="https://youtube.com/username">
            </div>
            <div class="form-group">
                <label for="other">Other Link</label>
                <input type="url" id="other" name="other" 
                       value="<?= htmlspecialchars($userData['other'] ?? '') ?>"
                       placeholder="https://example.com">
                <small class="form-text">Any other website or social media link</small>
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="password-change-section">
            <h3>Change Password (Leave blank if you don't want to change the password)</h3>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="save-btn">Save Changes</button>
            <a href="profile.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
</div>

<script>
// Preview profile picture before upload
document.getElementById('profile-picture').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('profile-preview').src = event.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>