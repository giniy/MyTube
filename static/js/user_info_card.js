    document.getElementById("profile-toggle")?.addEventListener("click", function (e) {
        e.stopPropagation();
        const card = document.getElementById("user-info-card");
        card.style.display = card.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", function () {
        const card = document.getElementById("user-info-card");
        if (card) card.style.display = "none";
    });


    document.getElementById("profile-img")?.addEventListener("click", function (e) {
        e.stopPropagation();
        const card = document.getElementById("user-info-card");
        card.style.display = card.style.display === "block" ? "none" : "block";
    });

    document.addEventListener("click", function () {
        const card = document.getElementById("user-info-card");
        if (card) card.style.display = "none";
    });

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