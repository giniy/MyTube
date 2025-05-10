<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon/play.png">
    <!-- <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/auth.css" rel="stylesheet">
    <link href="static/css/profile.css" rel="stylesheet">
    <link href="static/css/watched_video.css" rel="stylesheet">
    <link href="static/css/user.css" rel="stylesheet">
    <link href="static/css/logo.css" rel="stylesheet">
    <link href="static/css/hamburger_menu.css" rel="stylesheet">
    <script src="static/js/script.js"></script>
    <script src="static/js/live_count.js"></script>
    <script src="static/js/side_menu.js"></script>
    <script src="static/js/dislike.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
    <!-- Hamburger Menu Button -->
    <button class="hamburger-menu" onclick="toggleSidebar()" 
    style="
    text-decoration: none; 
    background: none; 
    border: none; 
    cursor: pointer; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between; 
    width: 15px; 
    height: 25px; 
    padding: 8px 0;
    ">
    <span style="width: 100%; height: 1px; background-color: #ffffff; transition: all 0.3s ease;"></span>
    <span style="width: 100%; height: 1px; background-color: #ffffff; transition: all 0.3s ease;"></span>
    <span style="width: 100%; height: 1px; background-color: #ffffff; transition: all 0.3s ease;"></span>
    </button>
    <!-- Sidebar Menu -->
    <div class="sidebar-menu" id="sidebarMenu" style="width:50px">
        <br>
        <br>
        <div class="sidebar-header">
            <!-- <h2>MyTube</h2> -->
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php"><i class="fa fa-home" aria-hidden="true"></i></a></li>
                <li><a href="upload.php"><i class="fa-solid fa-cloud-arrow-up"></i></a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="profile.php?username=<?= urlencode($_SESSION['username']) ?>"><i class="fa fa-user" aria-hidden="true"></i>
</a></li>
                    <li><a href="auth/logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i></a></li>
                <?php else: ?>
                    <li><a href="auth/login.php"><i class="fa fa-sign-in" aria-hidden="true"></i></a></li>
                    <li><a href="auth/signup.php"><i class="fa fa-user-plus" aria-hidden="true"></i></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>


    <div class="logo-container" style="display: inline-block; width: 100px; height: 30px;">
        <a class="nav-link" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php" 
           style="text-decoration: none; display: flex; align-items: center; height: 100%; position: relative;">
            <!-- Animated Logo -->
            <img src="/mytube/static/images/play.png" 
                 alt="MyTube Logo" 
                 style="height: 24px; width: auto;
                        position: absolute;
                        left: 0;
                        margin-left: 70px;
                        animation: logoSwap 5s infinite ease-in-out;">
            <!-- Animated Text -->
            <span style="font-weight: bold; color: #ff0000;
                        position: absolute;
                        margin-left: 70px;
                        left: 30px; /* 24px logo + 6px gap */
                        animation: textSwap 5s infinite ease-in-out;">MyTube</span>
        </a>
    </div>

    <div class="search-container">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" id="search-input" name="q" placeholder="Search videos..." 
                   value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <button type="submit" id="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
<?php require_once 'includes/functions.php'; ?>
<nav class="user-menu">
    <?php if (isLoggedIn()): ?>
        <a href="upload.php">Upload Video</a>


<a href="profile.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: inherit;">
    <?php
    // Get current user's data
    $userId = getUserId();
    $userData = getUserData($userId);
    $profilePicture = $userData['profile_picture'] ?? '';
    $gender = $userData['gender'] ?? '';
    
    // Determine profile picture path
    $profilePicPath = '';
    if (!empty($profilePicture)) {
        $cleanFilename = basename($profilePicture);
        $fullPath = 'uploads/profile_pictures/' . $cleanFilename;
        if (file_exists($fullPath) && is_readable($fullPath)) {
            $profilePicPath = $fullPath;
        }
    }
    
    // Use gender-based default if no valid picture
    if (empty($profilePicPath)) {
        $gender = strtolower(trim($gender));
        if ($gender === 'female') {
            $defaultImage = 'she.jpg';
        } elseif ($gender === 'male') {
            $defaultImage = 'he.jpg';
        } else {
            $defaultImage = 'other.jpg';
        }
        $profilePicPath = 'uploads/profile_pictures/' . $defaultImage;
        
        // Final fallback
        if (!file_exists($profilePicPath)) {
            $profilePicPath = 'uploads/profile_pictures/default.jpg';
        }
    }
    
    // Add cache buster
    $profilePicPath .= '?v=' . time();
    ?>
    
    <img src="<?= htmlspecialchars($profilePicPath) ?>" 
         alt="Profile"
         style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd; vertical-align: middle;"
         onerror="this.onerror=null; this.src='uploads/profile_pictures/default.jpg?v=<?= time() ?>'">
    <span>
        <!-- username Display -->
        <!-- <?= htmlspecialchars(getUsername($userId)) ?> -->
    </span>
</a>

        <a href="auth/logout.php">Logout</a>
    <?php else: ?>
        <a href="/mytube/auth/login.php">Login</a>
        <a href="/mytube/auth/signup.php">Sign Up</a>
    <?php endif; ?>
</nav>
    </header>

    <!-- Your existing HTML content -->

    <script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Handle search
        const searchButton = document.getElementById('search-button');
        const searchInput = document.getElementById('search-input');
        
        function performSearch() {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                window.location.href = `search.php?q=${encodeURIComponent(searchTerm)}`;
            }
        }
        
        if (searchButton) {
            searchButton.addEventListener('click', performSearch);
        }
        
        // Also allow Enter key to trigger search
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        }
    });
    </script>
</body>
</html>