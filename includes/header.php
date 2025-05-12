<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="favicon/play.png">
    <!-- <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyTube</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/auth.css" rel="stylesheet">
    <link href="static/css/profile.css" rel="stylesheet">
    <link href="static/css/watched_video.css" rel="stylesheet">
    <link href="static/css/user.css" rel="stylesheet">
    <link href="static/css/logo.css" rel="stylesheet">
    <link href="static/css/index.css" rel="stylesheet">
    <link href="static/css/hamburger_menu.css" rel="stylesheet">
    <link href="static/css/user_info_card.css" rel="stylesheet">
    <link href="static/css/home.css" rel="stylesheet">
    <script src="static/js/script.js"></script>
    <script src="static/js/live_count.js"></script>
    <script src="static/js/side_menu.js"></script>
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
                    <li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="profile.php?username=<?= urlencode($_SESSION['user_id']) ?>">
                            <i class="fa fa-user" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>
                    </li>
                    <li><a href="auth/logout.php"><i class="fa fa-sign-out" aria-hidden="true"></i></a></li>
                    <li><a href="auth/login.php"><i class="fa fa-sign-in" aria-hidden="true"></i></a></li>
                    <li><a href="auth/signup.php"><i class="fa fa-user-plus" aria-hidden="true"></i></a></li>
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

    <!-- <div class="search-container"> -->
    <div class="yt-search">
        <form action="search.php" method="GET" class="search-form">
            <input type="text" id="search-input" name="q" placeholder="Search" 
                   value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
            <button type="submit" id="search-button">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>

<?php require_once 'includes/functions.php'; ?>
<!-- In the user-menu section -->
<nav class="user-menu">
    <?php if (isLoggedIn()): ?>
        <?php
        $userId = getUserId();
        $userEmail = $_SESSION['user_email'] ?? '';
        $username = getUsername($userId); // assuming this function exists
        ?>
        <a href="upload.php">Upload Video</a>

        <div class="profile-container1" id="profile-toggle">
            <img src="<?= htmlspecialchars(getProfilePicture($userId)) ?>" 
                 alt="Profile"
                 onerror="this.onerror=null; this.src='uploads/profile_pictures/play.png?v=<?= time() ?>'">
        </div>

        <!-- Info Card -->
        <div id="user-info-card">
            <strong><?= htmlspecialchars($username) ?></strong><br>
            <small><?= htmlspecialchars($userEmail) ?></small><br>
            <hr>
            <a href="profile.php">View Profile</a>
            <a href="/mytube/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/mytube/auth/login.php">Login with OTP</a>
        <?php endif; ?>
        </div>

</nav>

<script>
document.getElementById("profile-toggle")?.addEventListener("click", function (e) {
    e.stopPropagation();
    const card = document.getElementById("user-info-card");
    card.style.display = card.style.display === "block" ? "none" : "block";
});

document.addEventListener("click", function () {
    const card = document.getElementById("user-info-card");
    if (card) card.style.display = "none";
});
</script>

<script>
document.getElementById("profile-img")?.addEventListener("click", function (e) {
    e.stopPropagation();
    const card = document.getElementById("user-info-card");
    card.style.display = card.style.display === "block" ? "none" : "block";
});

document.addEventListener("click", function () {
    const card = document.getElementById("user-info-card");
    if (card) card.style.display = "none";
});
</script>
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