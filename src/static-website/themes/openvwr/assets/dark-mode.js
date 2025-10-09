// Dark mode toggle functionality

(function() {
  'use strict';

  // Initialize dark mode based on stored preference or system preference
  function initDarkMode() {
    const storedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (storedTheme === 'dark' || (!storedTheme && systemPrefersDark)) {
      document.documentElement.setAttribute('data-theme', 'dark');
    } else if (storedTheme === 'light') {
      document.documentElement.setAttribute('data-theme', 'light');
    }
  }

  // Toggle between light and dark mode
  function toggleDarkMode() {
    const currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
  }

  // Set up toggle button
  function setupToggleButton() {
    const toggleButton = document.getElementById('dark-mode-toggle');

    if (toggleButton) {
      toggleButton.addEventListener('click', toggleDarkMode);
    }
  }

  // Listen for system theme changes
  function setupSystemThemeListener() {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');

    mediaQuery.addEventListener('change', (e) => {
      // Only apply system preference if user hasn't set explicit preference
      if (!localStorage.getItem('theme')) {
        document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
      }
    });
  }

  // Initialize when DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
      initDarkMode();
      setupToggleButton();
      setupSystemThemeListener();
    });
  } else {
    initDarkMode();
    setupToggleButton();
    setupSystemThemeListener();
  }
})();
