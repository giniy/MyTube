<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Guidelines - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/guidelines.css" rel="stylesheet">
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
    
    <div class="container">
        <h1>MyTube Community Guidelines</h1>
        <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
        
        <p>Welcome to MyTube! Our community is built on creativity, respect, and shared experiences. These guidelines help ensure MyTube remains a safe and enjoyable space for everyone.</p>
        
        <div class="warning-box">
            <strong>Important:</strong> Violations of these guidelines may result in content removal, account restrictions, or permanent bans. We may also report illegal content to law enforcement.
        </div>
        
        <h2>1. What We Allow</h2>
        <p>We encourage:</p>
        <ul class="guidelines-list">
            <li><strong>Original content</strong> that you created or have permission to share</li>
            <li><strong>Respectful discussions</strong> and constructive feedback</li>
            <li><strong>Creative expression</strong> within appropriate boundaries</li>
            <li><strong>Educational content</strong> that provides value to viewers</li>
            <li><strong>Cultural exchange</strong> that promotes understanding</li>
        </ul>
        
        <h2>2. What We Don't Allow</h2>
        
        <h3>Harmful or Dangerous Content</h3>
        <ul class="guidelines-list">
            <li>Content that incites violence or promotes harmful acts</li>
            <li>Dangerous challenges or stunts that risk serious injury</li>
            <li>Instructions for illegal activities or self-harm</li>
        </ul>
        
        <h3>Hateful Content</h3>
        <ul class="guidelines-list">
            <li>Content promoting hatred or violence against individuals/groups based on race, ethnicity, religion, gender, sexual orientation, disability, etc.</li>
            <li>Nazi ideology, terrorist propaganda, or other extremist content</li>
        </ul>
        
        <h3>Violent or Graphic Content</h3>
        <ul class="guidelines-list">
            <li>Gratuitous violence, gore, or shock content</li>
            <li>Content that exploits or endangers minors</li>
            <li>Footage of real-world violence with no educational context</li>
        </ul>
        
        <h3>Nudity and Sexual Content</h3>
        <ul class="guidelines-list">
            <li>Pornography or sexually explicit content</li>
            <li>Sexualization of minors</li>
            <li>Non-consensual intimate imagery (revenge porn)</li>
        </ul>
        
        <h3>Spam and Scams</h3>
        <ul class="guidelines-list">
            <li>Repetitive, misleading, or automated content</li>
            <li>Phishing attempts or financial scams</li>
            <li>View count manipulation or other metric tampering</li>
        </ul>
        
        <h3>Copyright and Legal Issues</h3>
        <ul class="guidelines-list">
            <li>Content you don't own or have permission to use</li>
            <li>Unauthorized sharing of private information (doxxing)</li>
            <li>Impersonation of others</li>
        </ul>
        
        <h2>3. Do's and Don'ts</h2>
        <div class="dos-donts">
            <div class="dos">
                <h3>✓ Do</h3>
                <ul class="guidelines-list">
                    <li>Be respectful to others</li>
                    <li>Use appropriate language</li>
                    <li>Credit original creators</li>
                    <li>Report violations you encounter</li>
                    <li>Follow all applicable laws</li>
                </ul>
            </div>
            <div class="donts">
                <h3>✗ Don't</h3>
                <ul class="guidelines-list">
                    <li>Harass or bully others</li>
                    <li>Use hate speech or slurs</li>
                    <li>Share personal information</li>
                    <li>Circumvent suspensions</li>
                    <li>Encourage rule-breaking</li>
                </ul>
            </div>
        </div>
        
        <h2>4. Age Restrictions</h2>
        <ul class="guidelines-list">
            <li>You must be at least 13 years old to use MyTube</li>
            <li>Some content may be age-restricted based on local laws</li>
            <li>Parents should supervise children's use of the platform</li>
        </ul>
        
        <h2>5. Reporting Violations</h2>
        <p>Help us keep MyTube safe by reporting content that violates these guidelines. Use the "Report" button on videos and comments, or email us at <a href="mailto:abuse@mytube.com">abuse@mytube.com</a>.</p>
        
        <h2>6. Enforcement</h2>
        <p>We may take these actions against violating content/accounts:</p>
        <ul class="guidelines-list">
            <li>Removal of content</li>
            <li>Age-restricting content</li>
            <li>Disabling monetization</li>
            <li>Temporary account restrictions</li>
            <li>Permanent account termination</li>
        </ul>
        
        <h2>7. Appeals</h2>
        <p>If you believe we made a mistake in enforcing these guidelines, you may appeal by contacting <a href="mailto:appeals@mytube.com">appeals@mytube.com</a>.</p>
    </div>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>