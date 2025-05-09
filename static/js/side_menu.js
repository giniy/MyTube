function toggleSidebar() {
    const sidebar = document.getElementById('sidebarMenu');
    const hamburger = document.querySelector('.hamburger-menu');
    const overlay = document.querySelector('.sidebar-overlay') || document.createElement('div');

    if (!overlay.classList.contains('sidebar-overlay')) {
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    sidebar.classList.toggle('active');
    hamburger.classList.toggle('active');
    overlay.classList.toggle('active');
}
document.querySelectorAll('.sidebar-nav a').forEach(link => {
    link.addEventListener('click', toggleSidebar);
});