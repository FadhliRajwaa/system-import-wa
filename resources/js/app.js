/**
 * Application JavaScript
 * Laravel + Livewire + Flux UI
 */

// Import Axios for HTTP requests (optional, Livewire handles most)
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Alpine.js is auto-loaded by Livewire, no need to import

/**
 * Dark Mode Theme Management
 * Listens for system preference changes and applies theme accordingly
 */
(function() {
    // Function to apply theme based on localStorage and system preference
    function applyTheme() {
        const theme = localStorage.getItem('theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    // Listen for system preference changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        const theme = localStorage.getItem('theme') || 'system';
        if (theme === 'system') {
            applyTheme();
        }
    });

    // Listen for storage changes (for when user changes theme in another tab)
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            applyTheme();
        }
    });

    // Apply theme on page load (backup for SPA navigation)
    document.addEventListener('DOMContentLoaded', applyTheme);

    // For Livewire navigation
    document.addEventListener('livewire:navigated', applyTheme);
})();
