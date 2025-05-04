<?php
try {
    require_once 'includes/config.php';
    require_once 'includes/header.php';
    require_once 'includes/functions.php';

    if (!isset($_GET['username'])) {
        throw new Exception("No user selected.");
    }

    $username = $_GET['username'];

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new Exception("Invalid username format.");
    }

    try {
        // Get user profile information
        $stmt = $conn->prepare("SELECT id, username, email, profile_picture, bio, gender FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Database prepare statement failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Database query execution failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception("User not found.");
        }

        $user = $result->fetch_assoc();
        
        // Get user's videos
        $videoStmt = $conn->prepare("SELECT id, title, description, video_file, thumbnail_file, uploaded_at, view_count FROM videos WHERE user_id = ? ORDER BY uploaded_at DESC");
        if (!$videoStmt) {
            throw new Exception("Database prepare statement failed: " . $conn->error);
        }
        
        $videoStmt->bind_param("i", $user['id']);
        if (!$videoStmt->execute()) {
            throw new Exception("Database query execution failed: " . $videoStmt->error);
        }
        
        $videosResult = $videoStmt->get_result();
        $videos = [];
        while ($video = $videosResult->fetch_assoc()) {
            $videos[] = $video;
        }

    } catch (Exception $dbException) {
        error_log("Database error: " . $dbException->getMessage());
        throw new Exception("Error retrieving user information. Please try again later.");
    }

} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <title>Error</title>
        <link rel='stylesheet' href='styles.css'>
    </head>
    <body>
        <div class='error-message'>
            <h2>Error</h2>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
            <a href='index.php'>Return to homepage</a>
        </div>
    </body>
    </html>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <link rel="stylesheet" href="static/css/user.css">
</head>
<body>
    <div class="main-container">
        <div class="user-header">
            <div class="avatar-container">
                <?php
                // Get the profile picture filename from database
                $profilePicture = trim($user['profile_picture']);
                $imageToShow = '';
                $cacheBuster = '?v=' . time();
                
                // First try the user's actual profile picture
                if (!empty($profilePicture)) {
                    // Clean the path and ensure it's in the correct directory
                    $cleanFilename = basename($profilePicture);
                    $fullPath = 'uploads/profile_pictures/' . $cleanFilename;
                    
                    // Check if file exists and is readable
                    if (file_exists($fullPath) && is_readable($fullPath)) {
                        $imageToShow = $fullPath;
                    }
                }
                
                // If no valid image yet, use gender-based default
                if (empty($imageToShow)) {
                    $gender = strtolower(trim($user['gender']));
                    $defaultImage = 'other.jpg'; // Default neutral image
                    
                    if ($gender === 'female') {
                        $defaultImage = 'she.jpg';
                    } elseif ($gender === 'male') {
                        $defaultImage = 'he.jpg';
                    }
                    
                    $imageToShow = 'uploads/profile_pictures/' . $defaultImage;
                    
                    // Final fallback if default images are missing
                    if (!file_exists($imageToShow)) {
                        $imageToShow = 'uploads/profile_pictures/default.jpg';
                    }
                }
                ?>
                
                <img src="<?= htmlspecialchars($imageToShow . $cacheBuster) ?>" 
                     alt="Profile picture of <?= htmlspecialchars($user['username']) ?>"
                     class="avatar-image"
                     onerror="this.onerror=null; this.src='uploads/profile_pictures/default.jpg<?= $cacheBuster ?>'">
            </div>

            <div class="user-details">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <!-- <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p> -->

                <?php if (!empty($user['gender'])): ?>
                    <!-- <p class="user-gender"><strong></strong> <?= htmlspecialchars($user['gender']) ?></p> -->
                <?php endif; ?>

                <?php if (!empty($user['bio'])): ?>
                    <p class="user-bio"><strong>Bio:</strong> <?= htmlspecialchars($user['bio']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="section-heading">Uploaded Videos</h2>
        
        <?php if (!empty($videos)): ?>
            <div class="video-grid">
                <?php foreach ($videos as $video): ?>
                    <?php
                    // Handle thumbnail path
                    $thumbnailPath = trim($video['thumbnail_file']);
                    if (strpos($thumbnailPath, 'uploads/thumbnails/') !== 0) {
                        $thumbnailPath = 'uploads/thumbnails/' . $thumbnailPath;
                    }
                    if (!file_exists($thumbnailPath) || empty($video['thumbnail_file'])) {
                        $thumbnailPath = 'uploads/thumbnails/default.jpg';
                    }
                    
                    // Format view count and date
                    $viewCount = number_format($video['view_count']);
                    $uploadDate = date('M j, Y', strtotime($video['uploaded_at']));
                    ?>
                    <div class="video-item">
                        <a href="watch.php?v=<?= htmlspecialchars($video['id']) ?>" style="text-decoration: none; color: inherit;">
                            <div class="thumbnail-wrapper">
                                <img src="<?= htmlspecialchars($thumbnailPath) ?>" 
                                     alt="Thumbnail for <?= htmlspecialchars($video['title']) ?>"
                                     class="thumbnail-image"
                                     onerror="this.onerror=null; this.src='uploads/thumbnails/default.jpg'">
                            </div>
                            <div class="video-details">
                                <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                                <?php if (!empty($video['description'])): ?>
                                    <p class="video-description"><?= htmlspecialchars($video['description']) ?></p>
                                <?php endif; ?>
                                <div class="video-meta">
                                    <span><?= $viewCount ?> views</span>
                                    <span><?= $uploadDate ?></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-content">
                <p>This user hasn't uploaded any videos yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'includes/footer.php'; ?>
</body>
</html>