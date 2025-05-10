            function fetchStats(videoId) {
                fetch(`get_video_stats.php?video_id=${videoId}`)
                    .then(response => response.json())
                    .then(data => {
                        const statsElement = document.getElementById(`video-stats-${videoId}`);
                        if (data && statsElement) {
                            statsElement.textContent = `${data.like_count} Likes | ${data.share_count} Shares | ${data.view_count} Views`;
                        }
                    })
                    .catch(err => console.error('Error fetching stats:', err));
            }

            document.addEventListener("DOMContentLoaded", function () {
                const statElements = document.querySelectorAll('[id^="video-stats-"]');
                statElements.forEach(el => {
                    const videoId = el.dataset.videoId;
                    fetchStats(videoId); // Fetch initially
                    setInterval(() => fetchStats(videoId), 5000); // Refresh every 5s
                });
            });