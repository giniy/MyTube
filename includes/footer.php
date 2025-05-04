<footer class="site-footer">
    <div class="footer-container">
    <div class="footer-section">
        <!-- Added logo with text -->
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
            <img src="/mytube/static/images/play.png" 
                 alt="MyTube Logo" 
                 style="height: 30px; width: auto;">
            <h3>MyTube</h3>
        </div>
        
        <p>The best platform for sharing your videos with the world.</p>
        <div class="social-links">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>

        <div class="footer-section">
            <h4>Navigation</h4>
            <ul>
                <li><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/index.php">Home</a></li>
                <li><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/coming.php">Trending</a></li>
                <li><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/subscriptions.php">Subscriptions</a></li>
                <li><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/coming.php">Library</a></li>
                <li><a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/upload.php">Upload</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Legal</h4>
            <ul>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/terms.php">Terms of Service</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/privacy.php">Privacy Policy</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/copyright.php">Copyright</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/guidelines.php">Community Guidelines</a>
                </li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Help & Support</h4>
            <ul>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/help.php">Help Center</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/contact.php">Contact Us</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/feedback.php">Send Feedback</a>
                </li>
                <li>
                <a href="<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?>/mytube/about.php">About</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> MyTube. All rights reserved.</p>
        <p class="footer-lang">Language : English</p>
    </div>
</footer>

<style>
/* Footer Styles */
.site-footer {
    background-color: #333;
    color: #fff;
    padding: 40px 0 0;
    margin-top: 50px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    padding: 0 20px;
}

.footer-section h3, 
.footer-section h4 {
    color: #fff;
    margin-bottom: 20px;
    font-size: 1.2rem;
}

.footer-section p {
    color: #bbb;
    line-height: 1.6;
    margin-bottom: 15px;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 10px;
}

.footer-section ul li a {
    color: #bbb;
    text-decoration: none;
    transition: color 0.3s;
}

.footer-section ul li a:hover {
    color: #ff0000;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    color: #fff;
    font-size: 1.2rem;
    transition: color 0.3s;
}

.social-links a:hover {
    color: #ff0000;
}

.footer-bottom {
    background-color: #222;
    padding: 20px 0;
    margin-top: 40px;
    text-align: center;
    border-top: 1px solid #444;
}

.footer-bottom p {
    color: #bbb;
    margin: 5px 0;
    font-size: 0.9rem;
}

.footer-lang a {
    color: #bbb;
    text-decoration: none;
}

.footer-lang a:hover {
    text-decoration: underline;
}

/* Font Awesome icons (you need to include FA in your project) */
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css');

/* Responsive adjustments */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .footer-section {
        text-align: center;
    }
    
    .social-links {
        justify-content: center;
    }
}
</style>