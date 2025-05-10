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
    <title>Terms of Service - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/terms.css" rel="stylesheet">
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
        <h1>MyTube Terms of Service</h1>
        <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
        
        <h2>1. Acceptance of Terms</h2>
        <p>By accessing or using the MyTube platform ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, please do not use our Service.</p>
        
        <h2>2. Description of Service</h2>
        <p>MyTube is a video sharing platform that allows users to upload, share, and view videos. The Service may include certain communications from MyTube, such as service announcements and administrative messages.</p>
        
        <h2>3. User Accounts</h2>
        <p>a. You must be at least 13 years old to use this Service.</p>
        <p>b. You are responsible for maintaining the confidentiality of your account and password.</p>
        <p>c. You agree to immediately notify MyTube of any unauthorized use of your account.</p>
        
        <h2>4. User Conduct</h2>
        <p>You agree not to:</p>
        <p>a. Upload, post, or transmit any content that is unlawful, harmful, threatening, abusive, harassing, defamatory, vulgar, obscene, or otherwise objectionable.</p>
        <p>b. Impersonate any person or entity or falsely state or otherwise misrepresent your affiliation with a person or entity.</p>
        <p>c. Upload content that infringes any patent, trademark, trade secret, copyright, or other proprietary rights of any party.</p>
        <p>d. Transmit any worms, viruses, or any code of a destructive nature.</p>
        
        <h2>5. Content Ownership</h2>
        <p>a. You retain ownership of any content you submit, post, or display on or through the Service.</p>
        <p>b. By submitting content, you grant MyTube a worldwide, non-exclusive, royalty-free license to use, reproduce, distribute, and display the content in connection with the Service.</p>
        
        <h2>6. Copyright Policy</h2>
        <p>MyTube respects copyright law and expects users to do the same. We will respond to notices of alleged copyright infringement that comply with applicable law.</p>
        <p>If you believe your content has been copied in a way that constitutes copyright infringement, please contact us at copyright@mytube.com.</p>
        
        <h2>7. Termination</h2>
        <p>MyTube may terminate or suspend your account immediately, without prior notice, for any reason, including without limitation if you breach these Terms.</p>
        
        <h2>8. Disclaimer of Warranties</h2>
        <p>The Service is provided "as is" and "as available" without warranty of any kind. MyTube does not guarantee that the Service will be uninterrupted or error-free.</p>
        
        <h2>9. Limitation of Liability</h2>
        <p>MyTube shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the Service.</p>
        
        <h2>10. Changes to Terms</h2>
        <p>MyTube reserves the right to modify these Terms at any time. We will notify users of significant changes through the Service. Continued use after such changes constitutes acceptance of the new Terms.</p>
        
        <h2>11. Governing Law</h2>
        <p>These Terms shall be governed by the laws of [Your Country/State] without regard to its conflict of law provisions.</p>
        
        <h2>12. Contact Information</h2>
        <p>For questions about these Terms, please contact us at <a href="mailto:legal@mytube.com" style="color:red;">legal@mytube.com</a>.</p>
    </div>
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>