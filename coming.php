<?php
// Include necessary files first
require_once 'includes/config.php';
require_once 'includes/header.php';

// Customize these variables for different pages
$pageTitle = "Service Coming Soon";
$serviceName = "This Service"; // Change this for each page
$icon = "🔧"; // Change the icon as needed
$description = "We're working hard to bring you an amazing $serviceName that will provide even more value to your experience.";
$additionalText = "In the meantime, feel free to explore all the great features we currently offer.";
?>
<div class="coming-soon-container">
    <div class="icon"><?= htmlspecialchars($icon) ?></div>
    <h1><?= htmlspecialchars($serviceName) ?> Coming Soon!</h1>
    <p><?= htmlspecialchars($description) ?></p>
    <p>Stay tuned for updates - we'll notify you as soon as it's ready!</p>
    
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    
    <p><?= htmlspecialchars($additionalText) ?></p>
</div>

<style type="text/css">
    h1 {
    font-size: 2em;
    margin: .67em 0;
    color: #ff0000;
}
p {
    color: #1d1c1a;
    margin-left: 24px;
    margin-top: -18px;
}

</style>

<?php require_once 'includes/footer.php'; ?>