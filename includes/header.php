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
    <script src="static/js/script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
    <div class="logo">
        <a class="nav-link" href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php" 
           style="text-decoration: none; color: #ff0000; font-weight: bold;">
            MyTube
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

    <nav class="user-menu">
        <?php if (isLoggedIn()): ?>
            <a href="upload.php">Upload</a>
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