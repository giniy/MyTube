<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="about-container">
    <section class="hero-section">
        <div class="hero-content">
            <h1>About MyTube</h1>
            <p class="tagline">Redefining video sharing for creators and viewers alike</p>
        </div>
        <div class="hero-image">
            <img src="static/images/team1.jpg" alt="MyTube Platform">
        </div>
    </section>

    <section class="mission-section">
        <h2>Our Mission</h2>
        <div class="mission-statement">
            <p>At MyTube, we believe in empowering creators and connecting communities through video. Our platform is built on three core principles:</p>
            <ul class="mission-values">
                <li>
                    <i class="fas fa-video"></i>
                    <span>Quality content for everyone</span>
                </li>
                <li>
                    <i class="fas fa-users"></i>
                    <span>Community-driven experience</span>
                </li>
                <li>
                    <i class="fas fa-lightbulb"></i>
                    <span>Innovation in video technology</span>
                </li>
            </ul>
        </div>
    </section>

    <section class="history-section">
        <h2>Our Story</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">2020</div>
                <div class="timeline-content">
                    <h3>Founded in a Dorm Room</h3>
                    <p>MyTube began as a college project between three friends who wanted to create a better video sharing platform.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2021</div>
                <div class="timeline-content">
                    <h3>First 1,000 Users</h3>
                    <p>We hit our first major milestone with 1,000 active users and 10,000 video uploads.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2022</div>
                <div class="timeline-content">
                    <h3>New Features Launched</h3>
                    <p>Introduced creator monetization, 4K streaming, and our mobile apps.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2023</div>
                <div class="timeline-content">
                    <h3>1 Million Users</h3>
                    <p>Celebrated reaching 1 million active users worldwide with a complete platform redesign.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="team-section">
        <h2>Meet The Team</h2>
        <div class="team-grid">
            <div class="team-member">
                <img src="static/images/team1.jpg" alt="Mr Gulab">
                <h3>Mr Gulab</h3>
                <p class="position">CEO & Co-Founder</p>
                <p class="bio">Visionary leader with a passion for video technology and creator empowerment.</p>
            </div>
            <div class="team-member">
                <img src="static/images/team2.png" alt="Mr Gulab">
                <h3>Mr Gulab</h3>
                <p class="position">CTO & Co-Founder</p>
                <p class="bio">Tech wizard who built our core streaming infrastructure from scratch.</p>
            </div>
            <div class="team-member">
                <img src="static/images/team1.jpg" alt="Mr Gulab">
                <h3>Mr Gulab</h3>
                <p class="position">CPO & Co-Founder</p>
                <p class="bio">User experience expert focused on making MyTube intuitive and enjoyable.</p>
            </div>
            <div class="team-member">
                <img src="static/images/team2.png" alt="Mr Singh">
                <h3>Mr Singh</h3>
                <p class="position">Head of Community</p>
                <p class="bio">Connects with creators and ensures MyTube remains a positive space for all.</p>
            </div>
        </div>
    </section>

    <section class="stats-section">
        <h2>By The Numbers</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="userCount">1.5M+</div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="videoCount">10M+</div>
                <div class="stat-label">Videos Uploaded</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="watchTime">500M+</div>
                <div class="stat-label">Hours Watched</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" id="creatorCount">50K+</div>
                <div class="stat-label">Content Creators</div>
            </div>
        </div>
    </section>

    <section class="join-section">
        <h2>Join Our Community</h2>
        <p>Whether you're a creator looking to share your passion or a viewer discovering amazing content, we'd love to have you.</p>
        <div class="cta-buttons">
            <a href="/mytube/auth/signup.php" class="btn btn-primary">Sign Up Free</a>
            <a href="contact.php" class="btn btn-secondary">Contact Us</a>
        </div>
    </section>
</div>

<script>
// Animate counting stats
document.addEventListener('DOMContentLoaded', function() {
    const animateValue = (id, start, end, duration) => {
        const obj = document.getElementById(id);
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = value.toLocaleString() + (id === 'userCount' || id === 'creatorCount' ? '+' : '');
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };

    // Start animations when stats section is in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateValue('userCount', 0, 1500000, 2000);
                animateValue('videoCount', 0, 10000000, 2000);
                animateValue('watchTime', 0, 500000000, 2000);
                animateValue('creatorCount', 0, 50000, 2000);
                observer.unobserve(entry.target);
            }
        });
    }, {threshold: 0.5});

    observer.observe(document.querySelector('.stats-section'));
});
</script>

<?php require_once 'includes/footer.php'; ?>

<style>
.about-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Hero Section */
.hero-section {
    display: flex;
    align-items: center;
    gap: 40px;
    margin: 40px 0 60px;
}

.hero-content {
    flex: 1;
}

.hero-content h1 {
    font-size: 3rem;
    margin-bottom: 15px;
    color: #ff0000;
}

.hero-content .tagline {
    font-size: 1.3rem;
    color: #555;
    margin-bottom: 20px;
}

.hero-image {
    flex: 1;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.hero-image img {
    width: 100%;
    height: auto;
    display: block;
}

/* Mission Section */
.mission-section {
    margin: 80px 0;
    text-align: center;
}

.mission-section h2 {
    font-size: 2.2rem;
    margin-bottom: 30px;
    color: #333;
}

.mission-statement {
    max-width: 800px;
    margin: 0 auto 40px;
    font-size: 1.1rem;
    line-height: 1.6;
}

.mission-values {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 40px;
    list-style: none;
    padding: 0;
}

.mission-values li {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 200px;
}

.mission-values i {
    font-size: 2.5rem;
    color: #ff0000;
    margin-bottom: 15px;
}

.mission-values span {
    font-weight: 500;
    font-size: 1.1rem;
}

/* History Section */
.history-section {
    margin: 80px 0;
}

.history-section h2 {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 50px;
}

.timeline {
    position: relative;
    max-width: 800px;
    margin: 0 auto;
    padding-left: 100px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 100px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #ff0000;
}

.timeline-item {
    position: relative;
    margin-bottom: 40px;
    padding-left: 40px;
}

.timeline-date {
    position: absolute;
    left: -100px;
    width: 80px;
    padding: 5px;
    text-align: center;
    background: #ff0000;
    color: white;
    font-weight: bold;
    border-radius: 4px;
}

.timeline-content {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.timeline-content h3 {
    margin-top: 0;
    color: #ff0000;
}

/* Team Section */
.team-section {
    margin: 80px 0;
    text-align: center;
}

.team-section h2 {
    font-size: 2.2rem;
    margin-bottom: 50px;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.team-member {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.3s;
}

.team-member:hover {
    transform: translateY(-10px);
}

.team-member img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 20px;
    border: 3px solid #ff0000;
}

.team-member h3 {
    margin: 10px 0 5px;
}

.position {
    color: #ff0000;
    font-weight: 500;
    margin-bottom: 15px;
}

.bio {
    color: #666;
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Stats Section */
.stats-section {
    margin: 80px 0;
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #ff0000, #cc0000);
    color: white;
    border-radius: 8px;
}

.stats-section h2 {
    font-size: 2.2rem;
    margin-bottom: 50px;
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    max-width: 1000px;
    margin: 0 auto;
}

.stat-item {
    padding: 20px;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 1.1rem;
    opacity: 0.9;
}

/* Join Section */
.join-section {
    margin: 80px 0;
    text-align: center;
    padding: 0 20px;
}

.join-section h2 {
    font-size: 2.2rem;
    margin-bottom: 20px;
}

.join-section p {
    max-width: 600px;
    margin: 0 auto 30px;
    font-size: 1.1rem;
    line-height: 1.6;
    color: #555;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
}

.btn {
    display: inline-block;
    padding: 12px 30px;
    border-radius: 4px;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s;
}

.btn-primary {
    background: #ff0000;
    color: white;
}

.btn-primary:hover {
    background: #cc0000;
    transform: translateY(-2px);
}

.btn-secondary {
    background: white;
    color: #ff0000;
    border: 2px solid #ff0000;
}

.btn-secondary:hover {
    background: #f8f8f8;
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section {
        flex-direction: column;
    }
    
    .hero-content, .hero-image {
        width: 100%;
    }
    
    .mission-values {
        flex-direction: column;
        align-items: center;
        gap: 30px;
    }
    
    .timeline {
        padding-left: 0;
    }
    
    .timeline::before {
        left: 20px;
    }
    
    .timeline-item {
        padding-left: 60px;
    }
    
    .timeline-date {
        left: 0;
        top: 0;
        width: 40px;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 200px;
        text-align: center;
    }
}
</style>