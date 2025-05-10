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
    <title>Copyright Policy - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <link href="static/css/copyright.css" rel="stylesheet">
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
        <h1>MyTube Copyright Policy</h1>
        <p class="last-updated">Last Updated: <?php echo date("F j, Y"); ?></p>
        
        <div class="notice-box">
            <strong>Important:</strong> MyTube respects intellectual property rights and expects users to do the same. Unauthorized use of copyrighted material may result in content removal and account termination.
        </div>
        
        <h2>1. Copyright Basics</h2>
        <p>Copyright protects original works of authorship including videos, music, artwork, and other creative content. You generally need permission to use someone else's copyrighted work.</p>
        
        <h2>2. What Constitutes Infringement</h2>
        <p>Copyright infringement occurs when you upload content containing:</p>
        <ul class="policy-list">
            <li>Full-length copyrighted movies/TV shows</li>
            <li>Copyrighted music without authorization</li>
            <li>Video clips from copyrighted content without transformative use</li>
            <li>Reproductions of copyrighted artwork/photos</li>
            <li>Content you've copied from others without permission</li>
        </ul>
        
        <h2>3. Fair Use Considerations</h2>
        <p>Some uses of copyrighted material may qualify as "fair use" under copyright law, including:</p>
        <ul class="policy-list">
            <li>Criticism or commentary</li>
            <li>Educational purposes</li>
            <li>News reporting</li>
            <li>Parody or satire</li>
        </ul>
        <p>However, fair use is complex and determined case-by-case. When in doubt, obtain permission.</p>
        
        <h2>4. Reporting Copyright Infringement</h2>
        <p>To file a DMCA takedown notice, you must provide:</p>
        
        <div class="dmca-requirements">
            <ol>
                <li>Your contact information (name, address, phone, email)</li>
                <li>Identification of the copyrighted work claimed to be infringed</li>
                <li>URL(s) of the allegedly infringing material</li>
                <li>A statement that you have a good faith belief the use is unauthorized</li>
                <li>A statement that the information is accurate, under penalty of perjury</li>
                <li>Your physical or electronic signature</li>
            </ol>
            <p>Send notices to: <a href="mailto:copyright@mytube.com">copyright@mytube.com</a></p>
        </div>
        
        <h2>5. Counter-Notification</h2>
        <p>If you believe your content was removed in error, you may submit a counter-notification containing:</p>
        <ul class="policy-list">
            <li>Your contact information</li>
            <li>Identification of the removed content</li>
            <li>A statement under penalty of perjury that the removal was mistaken</li>
            <li>Your consent to the jurisdiction of federal court in your district</li>
            <li>Your physical or electronic signature</li>
        </ul>
        
        <h2>6. Repeat Infringer Policy</h2>
        <p>Accounts that receive multiple valid copyright complaints may be:</p>
        <ul class="policy-list">
            <li>Temporarily suspended from uploading</li>
            <li>Permanently terminated</li>
            <li>Subject to legal action by copyright holders</li>
        </ul>
        
        <h2>7. Copyright Claims and Monetization</h2>
        <p>For content that remains up with a valid copyright claim:</p>
        <ul class="policy-list">
            <li>Ads may run on the video with revenue going to the copyright owner</li>
            <li>The video may be blocked in certain countries</li>
            <li>Viewership data may be shared with the copyright owner</li>
        </ul>
        
        <h2>8. Copyright Education</h2>
        <p>We recommend these resources to learn more:</p>
        <ul class="policy-list">
            <li><a href="https://www.copyright.gov" target="_blank">U.S. Copyright Office</a></li>
            <li><a href="https://www.eff.org/issues/intellectual-property" target="_blank">Electronic Frontier Foundation</a></li>
            <li><a href="https://www.youtube.com/about/copyright" target="_blank">YouTube Copyright Center</a> (for reference)</li>
        </ul>
        
        <h2>9. Contact Information</h2>
        <p>For copyright-related inquiries:</p>
        <p>Email: <a href="mailto:copyright@mytube.com">copyright@mytube.com</a><br>
        Physical Address:<br>
        MyTube Copyright Agent<br>
        Workie Tech Park, Vijay Nagar, AB Road <br>
        Indore, MP, 452010</p>
    </div>
    
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>