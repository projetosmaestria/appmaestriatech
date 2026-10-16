/**
 * Theme Toggle & Persistence Logic
 * MaestriaTech - 2026
 */

(function() {
    // 1. Immediate execution to prevent flash of light theme
    const theme = localStorage.getItem('theme');
    if (theme === 'dark') {
        document.documentElement.classList.add('dark-mode');
    }

    // 2. Initialization when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const body = document.body;

        if (!themeToggle || !themeIcon) return;

        // Ensure body has the class if theme is dark
        if (localStorage.getItem('theme') === 'dark') {
            body.classList.add('dark-mode');
            updateIcon('dark');
        }

        // Function to update icon
        function updateIcon(currentTheme) {
            if (currentTheme === 'dark') {
                themeIcon.classList.remove('fa-sun-o');
                themeIcon.classList.add('fa-moon-o');
            } else {
                themeIcon.classList.remove('fa-moon-o');
                themeIcon.classList.add('fa-sun-o');
            }
        }

        // Toggle Event Listener
        themeToggle.addEventListener('click', function() {
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                document.documentElement.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
                updateIcon('light');
            } else {
                body.classList.add('dark-mode');
                document.documentElement.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
                updateIcon('dark');
            }
        });
    });
})();
