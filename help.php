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
    <title>Help Center - MyTube</title>
    <link href="static/css/vid.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #ff0000;
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            color: #ff0000;
            margin-top: 40px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        h3 {
            margin-top: 25px;
            color: #555;
        }
        .search-help {
            text-align: center;
            margin: 30px 0;
        }
        .search-help input {
            padding: 12px;
            width: 60%;
            max-width: 500px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-help button {
            padding: 12px 20px;
            background: #ff0000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .help-categories {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .help-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .help-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .help-card h3 {
            color: #ff0000;
            margin-top: 0;
        }
        .faq-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .faq-question {
            font-weight: bold;
            color: #ff0000;
            cursor: pointer;
        }
        .faq-answer {
            margin-top: 10px;
            display: none;
        }
        .contact-options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 30px 0;
        }
        .contact-card {
            flex: 1;
            min-width: 250px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .contact-card i {
            font-size: 2em;
            color: #ff0000;
            margin-bottom: 15px;
        }
        a{
            color: #493939;
        }

#searchResults {
    margin-top: 20px;
    padding: 0;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #eee;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: none;
    max-height: 500px;
    overflow-y: auto;
}

.results-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.results-header h3 {
    margin: 0;
    color: #333;
}

.results-list {
    padding: 10px 0;
}

.search-result {
    padding: 15px 20px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.search-result:hover {
    background-color: #f9f9f9;
}

.result-type {
    font-size: 12px;
    color: #ff0000;
    text-transform: uppercase;
    font-weight: bold;
    margin-bottom: 5px;
}

.result-title {
    margin: 0 0 5px 0;
    color: #333;
    font-size: 16px;
}

.result-content {
    color: #666;
    font-size: 14px;
    line-height: 1.4;
}

.no-results {
    padding: 20px;
    text-align: center;
    color: #666;
}

.highlight {
    background-color: #fff8e1;
    font-weight: bold;
    padding: 0 2px;
    border-radius: 3px;
}

    </style>
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
        <h1>MyTube Help Center</h1>
        <p style="text-align: center;">Find answers to common questions or contact our support team</p>
        
        <div class="search-help">
            <form id="helpSearchForm">
                <input type="text" id="helpSearchInput" placeholder="Search help articles..." autocomplete="off">
                <button type="submit">Search</button>
            </form>
            <div id="searchResults" style="display: none;"></div>
        </div>
        
        <h2>Popular Help Topics</h2>
        <div class="help-categories">
            <div class="help-card">
                <h3>Getting Started</h3>
                <ul>
                    <li><a href="#create-account">Creating an account</a></li>
                    <li><a href="#upload-video">Uploading your first video</a></li>
                    <li><a href="#channel-setup">Setting up your channel</a></li>
                </ul>
            </div>
            
            <div class="help-card">
                <h3>Uploading & Videos</h3>
                <ul>
                    <li><a href="#video-formats">Supported video formats</a></li>
                    <li><a href="#thumbnails">Adding custom thumbnails</a></li>
                    <li><a href="#edit-video">Editing video details</a></li>
                </ul>
            </div>
            
            <div class="help-card">
                <h3>Account Settings</h3>
                <ul>
                    <li><a href="#change-password">Changing your password</a></li>
                    <li><a href="#update-email">Updating email address</a></li>
                    <li><a href="#delete-account">Deleting your account</a></li>
                </ul>
            </div>
            
            <div class="help-card">
                <h3>Troubleshooting</h3>
                <ul>
                    <li><a href="#upload-issues">Upload problems</a></li>
                    <li><a href="#playback-issues">Playback issues</a></li>
                    <li><a href="#login-problems">Login troubles</a></li>
                </ul>
            </div>
            
            <div class="help-card">
                <h3>Community & Safety</h3>
                <ul>
                    <li><a href="#report-content">Reporting content</a></li>
                    <li><a href="#privacy-settings">Privacy settings</a></li>
                    <li><a href="#block-users">Blocking users</a></li>
                </ul>
            </div>
            
            <div class="help-card">
                <h3>Monetization</h3>
                <ul>
                    <li><a href="#earn-money">Earning money on MyTube</a></li>
                    <li><a href="#ad-requirements">Ad requirements</a></li>
                    <li><a href="#payment-info">Payment information</a></li>
                </ul>
            </div>
        </div>
        
        <h2>Frequently Asked Questions</h2>
        
        <div class="faq-item" id="create-account">
            <div class="faq-question">How do I create a MyTube account?</div>
            <div class="faq-answer">
                <p>To create an account:</p>
                <ol>
                    <li>Click "Sign Up" in the top right corner</li>
                    <li>Enter your email address and choose a password</li>
                    <li>Complete the verification process</li>
                    <li>Set up your profile information</li>
                </ol>
            </div>
        </div>
        
        <div class="faq-item" id="upload-video">
            <div class="faq-question">How do I upload a video?</div>
            <div class="faq-answer">
                <p>Uploading videos is easy:</p>
                <ol>
                    <li>Click the "Upload" button in the top navigation</li>
                    <li>Select your video file or drag-and-drop it</li>
                    <li>Add a title, description, and tags</li>
                    <li>Choose visibility settings (Public, Unlisted, or Private)</li>
                    <li>Click "Publish"</li>
                </ol>
            </div>
        </div>
        
        <div class="faq-item" id="video-formats">
            <div class="faq-question">What video formats does MyTube support?</div>
            <div class="faq-answer">
                <p>MyTube supports most common video formats including:</p>
                <ul>
                    <li>MP4 (recommended)</li>
                    <li>MOV</li>
                    <li>AVI</li>
                    <li>WMV</li>
                    <li>FLV</li>
                </ul>
                <p>Maximum file size: 2GB. For best results, use MP4 with H.264 codec.</p>
            </div>
        </div>
        
        <div class="faq-item" id="change-password">
            <div class="faq-question">How do I change my password?</div>
            <div class="faq-answer">
                <p>To change your password:</p>
                <ol>
                    <li>Go to your Account Settings</li>
                    <li>Select "Security"</li>
                    <li>Click "Change Password"</li>
                    <li>Enter your current password and new password</li>
                    <li>Save changes</li>
                </ol>
            </div>
        </div>
        
        <div class="faq-item" id="report-content">
            <div class="faq-question">How do I report inappropriate content?</div>
            <div class="faq-answer">
                <p>To report content:</p>
                <ol>
                    <li>Below the video player, click the "Report" button</li>
                    <li>Select the reason for your report</li>
                    <li>Add any additional details</li>
                    <li>Submit the report</li>
                </ol>
                <p>Our team reviews all reports and takes appropriate action.</p>
            </div>
        </div>
        
        <h2>Contact Support</h2>
        <p>Can't find what you're looking for? Contact our support team:</p>
        
        <div class="contact-options">
            <div class="contact-card">
                <i class="fas fa-envelope"></i>
                <h3>Email Support</h3>
                <p>For general inquiries</p>
                <p><a href="mailto:support@mytube.com">support@mytube.com</a></p>
                <p>Response time: 24-48 hours</p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Copyright Issues</h3>
                <p>DMCA and copyright claims</p>
                <p><a href="mailto:copyright@mytube.com">copyright@mytube.com</a></p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-shield-alt"></i>
                <h3>Safety Concerns</h3>
                <p>Reporting abuse or safety issues</p>
                <p><a href="mailto:abuse@mytube.com">abuse@mytube.com</a></p>
            </div>
            
            <div class="contact-card">
                <i class="fas fa-comments"></i>
                <h3>Community Forum</h3>
                <p>Get help from other users</p>
                <p><a href="forum.php" style="color: #ff0303;">Visit our forum</a></p>
            </div>
        </div>
        
        <script>
            // Simple FAQ toggle functionality
            document.querySelectorAll('.faq-question').forEach(question => {
                question.addEventListener('click', () => {
                    const answer = question.nextElementSibling;
                    answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
                });
            });
            
            // Expand FAQ if linked directly
            window.addEventListener('load', () => {
                if (window.location.hash) {
                    const target = document.querySelector(window.location.hash);
                    if (target && target.classList.contains('faq-item')) {
                        target.querySelector('.faq-answer').style.display = 'block';
                        target.scrollIntoView();
                    }
                }
            });
        </script>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // FAQ toggle functionality
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
        });
    });
    
    // Expand FAQ if linked directly
    if (window.location.hash) {
        const target = document.querySelector(window.location.hash);
        if (target && target.classList.contains('faq-item')) {
            target.querySelector('.faq-answer').style.display = 'block';
            target.scrollIntoView();
        }
    }
    
    // Search functionality
    const searchInput = document.getElementById('helpSearchInput');
    const searchResults = document.getElementById('searchResults');
    
    if (searchInput) {
        // Real-time search with debounce
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query === '') {
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300); // 300ms debounce delay
        });
        
        // Handle form submission
        document.getElementById('helpSearchForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch(searchInput.value.trim());
        });
    }
    
    function performSearch(query) {
        if (query === '') {
            searchResults.style.display = 'none';
            return;
        }
        
        // Get all searchable content
        const faqItems = document.querySelectorAll('.faq-item');
        const helpCards = document.querySelectorAll('.help-card li a');
        const helpSections = document.querySelectorAll('h2, h3');
        
        const results = [];
        const queryLower = query.toLowerCase();
        
        // Search FAQ items
        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question').textContent.toLowerCase();
            const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
            const id = item.id;
            
            if (question.includes(queryLower)) {
                results.push({
                    type: 'FAQ',
                    title: item.querySelector('.faq-question').textContent,
                    id: id,
                    content: highlightText(item.querySelector('.faq-answer').textContent, query),
                    score: question.indexOf(queryLower) >= 0 ? 2 : 1
                });
            } else if (answer.includes(queryLower)) {
                const answerText = item.querySelector('.faq-answer').textContent;
                results.push({
                    type: 'FAQ',
                    title: item.querySelector('.faq-question').textContent,
                    id: id,
                    content: highlightText(answerText, query),
                    score: 1
                });
            }
        });
        
        // Search help card links
        helpCards.forEach(link => {
            const text = link.textContent.toLowerCase();
            if (text.includes(queryLower)) {
                results.push({
                    type: 'Help Topic',
                    title: link.textContent,
                    id: link.getAttribute('href').substring(1),
                    content: 'Related help topic',
                    score: 1
                });
            }
        });
        
        // Search section headings
        helpSections.forEach(section => {
            const text = section.textContent.toLowerCase();
            if (text.includes(queryLower)) {
                let id = section.id;
                if (!id) {
                    id = text.trim().toLowerCase().replace(/\s+/g, '-');
                    section.id = id;
                }
                
                results.push({
                    type: 'Section',
                    title: section.textContent,
                    id: id,
                    content: 'Jump to this section',
                    score: text === queryLower ? 3 : 2
                });
            }
        });
        
        // Sort results by relevance
        results.sort((a, b) => b.score - a.score);
        
        displayResults(results);
    }
    
    function highlightText(text, query) {
        if (!query) return text;
        const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }
    
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    function displayResults(results) {
        const resultsContainer = document.getElementById('searchResults');
        
        if (results.length === 0) {
            resultsContainer.innerHTML = '<div class="no-results">No results found. Try different keywords.</div>';
            resultsContainer.style.display = 'block';
            return;
        }
        
        let html = '<div class="results-header">';
        html += `<h3>Found ${results.length} ${results.length === 1 ? 'result' : 'results'}</h3>`;
        html += '</div><div class="results-list">';
        
        results.forEach(result => {
            html += `
                <div class="search-result" onclick="scrollToResult('${result.id}')">
                    <div class="result-type">${result.type}</div>
                    <h4 class="result-title">${highlightText(result.title, searchInput.value.trim())}</h4>
                    <div class="result-content">${result.content}</div>
                </div>
            `;
        });
        
        html += '</div>';
        resultsContainer.innerHTML = html;
        resultsContainer.style.display = 'block';
    }
});

// Global function for scrolling to results
function scrollToResult(id) {
    const target = document.getElementById(id);
    if (target) {
        if (target.classList.contains('faq-item')) {
            target.querySelector('.faq-answer').style.display = 'block';
        }
        target.scrollIntoView({ behavior: 'smooth' });
        target.style.backgroundColor = '#fff8e1';
        setTimeout(() => {
            target.style.backgroundColor = '';
        }, 2000);
    }
}
</script>
    <?php require_once 'includes/footer.php'; ?>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</body>
</html>