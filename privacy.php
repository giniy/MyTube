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
    <title>Privacy Policy - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/privacy.css" rel="stylesheet">
</head>
<body>
    <header>
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
    </header>
    
    <div class="container">
        <h1>MyTube Privacy Policy</h1>
        <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
        
        <p>Welcome to MyTube! We respect your privacy and are committed to protecting your personal data. This Privacy Policy explains how we collect, use, and safeguard your information when you use our platform.</p>
        
        <h2>1. Information We Collect</h2>
        
        <h3>Information You Provide:</h3>
        <ul>
            <li><strong>Account Information:</strong> When you register, we collect your username, email address, and password.</li>
            <li><strong>Profile Information:</strong> You may choose to provide additional information like a profile picture or bio.</li>
            <li><strong>Content:</strong> Videos, comments, and other content you upload or post.</li>
            <li><strong>Communications:</strong> Messages you send through our platform.</li>
        </ul>
        
        <h3>Information Collected Automatically:</h3>
        <ul>
            <li><strong>Usage Data:</strong> How you interact with our platform (videos watched, time spent, etc.).</li>
            <li><strong>Device Information:</strong> IP address, browser type, device type, and operating system.</li>
            <li><strong>Cookies:</strong> We use cookies to enhance your experience and analyze site traffic.</li>
        </ul>
        
        <h2>2. How We Use Your Information</h2>
        <ul>
            <li>To provide and maintain our service</li>
            <li>To personalize your experience</li>
            <li>To improve our platform</li>
            <li>To communicate with you</li>
            <li>To enforce our terms and policies</li>
            <li>For security and fraud prevention</li>
        </ul>
        
        <h2>3. Sharing of Information</h2>
        <p>We may share your information in these circumstances:</p>
        <ul>
            <li><strong>Public Content:</strong> Videos and comments you post are publicly visible.</li>
            <li><strong>Service Providers:</strong> With vendors who help us operate our platform.</li>
            <li><strong>Legal Requirements:</strong> When required by law or to protect rights.</li>
            <li><strong>Business Transfers:</strong> In connection with a merger or acquisition.</li>
        </ul>
        
        <h2>4. Data Security</h2>
        <p>We implement appropriate technical and organizational measures to protect your personal data. However, no internet transmission is 100% secure.</p>
        
        <h2>5. Your Rights</h2>
        <p>Depending on your location, you may have rights to:</p>
        <ul>
            <li>Access, correct, or delete your personal data</li>
            <li>Object to or restrict processing</li>
            <li>Data portability</li>
            <li>Withdraw consent (where applicable)</li>
        </ul>
        <p>To exercise these rights, please contact us at privacy@mytube.com.</p>
        
        <h2>6. Children's Privacy</h2>
        <p>MyTube is not intended for children under 13. We do not knowingly collect personal information from children under 13.</p>
        
        <h2>7. International Data Transfers</h2>
        <p>Your information may be transferred to and processed in countries other than your own.</p>
        
        <h2>8. Changes to This Policy</h2>
        <p>We may update this policy from time to time. We'll notify you of significant changes through our platform.</p>
        
        <h2>9. Contact Us</h2>
        <p>For questions about this Privacy Policy, please contact us at <a href="mailto:privacy@mytube.com" style="color:red;" >privacy@mytube.com</a>.</p>
    </div>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>