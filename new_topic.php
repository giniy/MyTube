<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = false;

// Fetch categories from database
$categories = [];
$categoryQuery = $conn->query("SELECT * FROM forum_categories ORDER BY name");
if ($categoryQuery) {
    $categories = $categoryQuery->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error loading categories: " . $conn->error;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $is_faq = isset($_POST['is_faq']) && isAdmin() ? 1 : 0; // Only admins can mark as FAQ

    // Validation
    if (empty($title)) {
        $errors[] = "Title is required";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters";
    }

    if (empty($content)) {
        $errors[] = "Content is required";
    }

    if ($category_id <= 0) {
        $errors[] = "Please select a category";
    }

    // Check if category exists
    $categoryExists = false;
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $categoryExists = true;
            break;
        }
    }
    if (!$categoryExists) {
        $errors[] = "Invalid category selected";
    }

    // If no errors, insert into database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO forum_topics (category_id, user_id, title, content, is_faq) 
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $category_id, $_SESSION['user_id'], $title, $content, $is_faq);
        
        if ($stmt->execute()) {
            $topic_id = $stmt->insert_id;
            header("Location: view_topic.php?id=$topic_id");
            exit;
        } else {
            $errors[] = "Error creating topic: " . $conn->error;
        }
    }
}
?>

<div class="forum-container">
    <h1>Create New Topic</h1>
    
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
    
    <form method="POST" class="topic-form">
        <div class="form-group">
            <label for="category_id">Category *</label>
            <select name="category_id" id="category_id" required>
                <option value="">-- Select a Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" 
                        <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="help-text">Choose the most relevant category for your topic</p>
        </div>
        
        <div class="form-group">
            <label for="title">Title *</label>
            <input type="text" name="title" id="title" required 
                   value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>"
                   maxlength="255">
            <p class="help-text">Be specific about your question or topic</p>
        </div>
        
        <div class="form-group">
            <label for="content">Content *</label>
            <textarea name="content" id="content" rows="10" required><?= 
                isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' 
            ?></textarea>
            <p class="help-text">Provide as much detail as possible</p>
        </div>
        
        <?php if (isAdmin()): ?>
            <div class="form-group checkbox-group">
                <input type="checkbox" name="is_faq" id="is_faq" 
                    <?= isset($_POST['is_faq']) ? 'checked' : '' ?>>
                <label for="is_faq">Mark as FAQ (Visible to all users)</label>
            </div>
        <?php endif; ?>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Topic</button>
            <a href="forum.php" class="btn btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

<style>
.forum-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 4px;
    color: #721c24;
}

.alert-danger h3 {
    margin-top: 0;
    color: #721c24;
}

.topic-form {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 5px;
}

.form-group input[type="text"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 16px;
}

.form-group textarea {
    min-height: 200px;
    resize: vertical;
}

.help-text {
    margin-top: 5px;
    font-size: 0.9em;
    color: #666;
}

.checkbox-group {
    display: flex;
    align-items: center;
}

.checkbox-group input {
    margin-right: 10px;
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background-color: #ff0000;
    color: white;
}

.btn-primary:hover {
    background-color: #cc0000;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background-color: #5a6268;
}

@media (max-width: 600px) {
    .forum-container {
        padding: 10px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>