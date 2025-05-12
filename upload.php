<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_required'] = 'You must be logged in to upload videos';
    header('Location: index.php');
    exit;
    // die('You must be logged in to upload videos');
}
require_once 'includes/config.php';

// Temporarily increase limits (remove in production)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '101M');
ini_set('max_execution_time', 300);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($title) || empty($_FILES['video']['name']) || empty($_FILES['thumbnail']['name'])) {
        $error = 'Please fill in all required fields';
    } else {
        // Process video upload
        $videoFile = $_FILES['video'];
        $videoExt = strtolower(pathinfo($videoFile['name'], PATHINFO_EXTENSION));
        $allowedVideoExts = ['mp4', 'webm', 'ogg'];
        
        // Process thumbnail upload
        $thumbnailFile = $_FILES['thumbnail'];
        $thumbnailExt = strtolower(pathinfo($thumbnailFile['name'], PATHINFO_EXTENSION));
        $allowedThumbnailExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($videoExt, $allowedVideoExts)) {
            $error = 'Invalid video file format. Allowed formats: ' . implode(', ', $allowedVideoExts);
        } elseif (!in_array($thumbnailExt, $allowedThumbnailExts)) {
            $error = 'Invalid thumbnail file format. Allowed formats: ' . implode(', ', $allowedThumbnailExts);
        } else {
            // Generate unique filenames
            $videoFileName = uniqid('video_', true) . '.' . $videoExt;
            $thumbnailFileName = uniqid('thumb_', true) . '.' . $thumbnailExt;
            
            $videoPath = VIDEO_UPLOAD_PATH . $videoFileName;
            $thumbnailPath = THUMBNAIL_UPLOAD_PATH . $thumbnailFileName;
            
            if (move_uploaded_file($videoFile['tmp_name'], $videoPath) && 
                move_uploaded_file($thumbnailFile['tmp_name'], $thumbnailPath)) {
                // Insert into database
                $query = "INSERT INTO videos (user_id, title, description, video_file, thumbnail_file) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $_SESSION['user_id'], $title, $description, $videoFileName, $thumbnailFileName);
                
                if ($stmt->execute()) {
                    $success = 'Video uploaded successfully!';
                } else {
                    $error = 'Failed to save video details. Please try again.' . $stmt->error;
                    // Clean up uploaded files if DB insert failed
                    unlink($videoPath);
                    unlink($thumbnailPath);
                }
            } else {
                $error = 'File upload failed. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<main class="upload-container">
    <h2>Upload Video</h2>
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="video">Video File (MP4, WebM, OGG)</label>
            <input type="file" id="video" name="video" accept="video/*" required>
        </div>
        <div class="form-group">
            <label for="thumbnail">Thumbnail Image (JPG, PNG, GIF)</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*" required>
        </div>
        <button type="submit">Upload</button>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>