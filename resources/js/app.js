document.addEventListener('DOMContentLoaded', function() {
    // Theme toggle handling
    const themeToggle = document.querySelector('.theme-controller');
    if (themeToggle) {
        themeToggle.addEventListener('change', function() {
            const newTheme = this.checked ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            
            // Save theme preference
            fetch('/set-theme', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ theme: newTheme })
            });
        });
    }
});