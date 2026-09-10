/**
 * Campus Job Posting System — Theme Toggle Controller & Cyber Glitch Persona Engine
 * Handles dark/light mode switching, localStorage persistence, OS prefers-color-scheme,
 * and Cyber Glitch & RGB Split transitions with Hover Peek for developer personas.
 */

(function () {
  'use strict';

  var STORAGE_KEY = 'campus_hire_theme';
  var DARK = 'dark';
  var LIGHT = 'light';

  /**
   * Returns the current effective theme based on saved preference or OS pref.
   */
  function getPreferredTheme() {
    var saved = localStorage.getItem(STORAGE_KEY);
    if (saved === DARK || saved === LIGHT) return saved;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? DARK : LIGHT;
  }

  /**
   * Preloads all developer light and dark image assets for instant glitch rendering.
   */
  function preloadDeveloperPhotos() {
    var targets = document.querySelectorAll('.dev-photo-target');
    targets.forEach(function (img) {
      var lightSrc = img.getAttribute('data-light-src');
      var darkSrc = img.getAttribute('data-dark-src');
      if (lightSrc) {
        var lImg = new Image();
        lImg.src = lightSrc;
      }
      if (darkSrc) {
        var dImg = new Image();
        dImg.src = darkSrc;
      }
    });
  }

  /**
   * Synchronizes developer photos between light and dark versions.
   * If isGlitch is true, executes Cyber Glitch & RGB Split animation.
   */
  function syncDeveloperPhotos(theme, isGlitch) {
    var targets = document.querySelectorAll('.dev-photo-target');
    if (!targets || targets.length === 0) return;

    if (!isGlitch) {
      // Instant silent synchronization (page load / anti-FOUC)
      targets.forEach(function (img) {
        var src = theme === DARK ? img.getAttribute('data-dark-src') : img.getAttribute('data-light-src');
        if (src && img.getAttribute('src') !== src) {
          img.src = src;
        }
      });
      return;
    }

    // Trigger Cyber Glitch & RGB Split Jitter
    var frames = document.querySelectorAll('.dev-photo-glitch-frame');
    frames.forEach(function (frame) {
      frame.classList.remove('is-glitching', 'is-glitching-hover');
      void frame.offsetWidth; // Reflow to reset animation
      frame.classList.add('is-glitching');
    });

    targets.forEach(function (img) {
      img.classList.remove('is-glitching', 'is-glitching-hover');
      void img.offsetWidth;
      img.classList.add('is-glitching');
    });

    // Midpoint Image Swap (140ms into the 320ms glitch distortion)
    setTimeout(function () {
      targets.forEach(function (img) {
        var src = theme === DARK ? img.getAttribute('data-dark-src') : img.getAttribute('data-light-src');
        if (src) {
          img.src = src;
        }
      });
    }, 140);

    // Remove glitch classes after animation completes
    setTimeout(function () {
      frames.forEach(function (frame) {
        frame.classList.remove('is-glitching');
      });
      targets.forEach(function (img) {
        img.classList.remove('is-glitching');
      });
    }, 330);
  }

  /**
   * Initializes interactive Hover Peek preview on developer cards and floating circles.
   */
  function initHoverPeek() {
    var cards = document.querySelectorAll('.dev-card, .floating-photo-circle');
    cards.forEach(function (card) {
      var img = card.querySelector('.dev-photo-target') || (card.classList.contains('dev-photo-target') ? card : null);
      var frame = card.querySelector('.dev-photo-glitch-frame') || (card.classList.contains('dev-photo-glitch-frame') ? card : null);
      if (!img) return;

      var peekTimer = null;
      var cleanupTimer = null;

      card.addEventListener('mouseenter', function () {
        var currentTheme = document.documentElement.getAttribute('data-theme') || LIGHT;
        // Peek the alternate theme persona
        var peekTheme = currentTheme === DARK ? LIGHT : DARK;
        var peekSrc = peekTheme === DARK ? img.getAttribute('data-dark-src') : img.getAttribute('data-light-src');
        if (!peekSrc) return;

        if (frame) {
          frame.classList.remove('is-glitching-hover');
          void frame.offsetWidth;
          frame.classList.add('is-glitching-hover');
        }
        img.classList.remove('is-glitching-hover');
        void img.offsetWidth;
        img.classList.add('is-glitching-hover');

        if (peekTimer) clearTimeout(peekTimer);
        peekTimer = setTimeout(function () {
          img.src = peekSrc;
        }, 90);
      });

      card.addEventListener('mouseleave', function () {
        var currentTheme = document.documentElement.getAttribute('data-theme') || LIGHT;
        var restoreSrc = currentTheme === DARK ? img.getAttribute('data-dark-src') : img.getAttribute('data-light-src');
        if (!restoreSrc) return;

        if (frame) {
          frame.classList.remove('is-glitching-hover');
          void frame.offsetWidth;
          frame.classList.add('is-glitching-hover');
        }
        img.classList.remove('is-glitching-hover');
        void img.offsetWidth;
        img.classList.add('is-glitching-hover');

        if (peekTimer) clearTimeout(peekTimer);
        peekTimer = setTimeout(function () {
          img.src = restoreSrc;
        }, 90);

        if (cleanupTimer) clearTimeout(cleanupTimer);
        cleanupTimer = setTimeout(function () {
          if (frame) frame.classList.remove('is-glitching-hover');
          img.classList.remove('is-glitching-hover');
        }, 240);
      });
    });
  }

  /**
   * Applies the given theme to the document root and developer photos.
   */
  function applyTheme(theme, isGlitch) {
    var html = document.documentElement;
    html.setAttribute('data-theme', theme);
    html.setAttribute('data-bs-theme', theme);

    // Update toggle button icon if it exists
    var icon = document.getElementById('themeToggleIcon');
    if (icon) {
      if (theme === DARK) {
        icon.className = 'bi bi-sun-fill';
      } else {
        icon.className = 'bi bi-moon-fill';
      }
    }

    // Update aria-label on toggle button
    var btn = document.getElementById('themeToggleBtn');
    if (btn) {
      btn.setAttribute('aria-label', theme === DARK ? 'Switch to Light Mode' : 'Switch to Dark Mode');
      btn.setAttribute('title', theme === DARK ? 'Switch to Light Mode' : 'Switch to Dark Mode');
    }

    // Sync developer photos
    syncDeveloperPhotos(theme, !!isGlitch);
  }

  /**
   * Toggles the theme and saves the preference.
   */
  function toggleTheme() {
    var current = document.documentElement.getAttribute('data-theme') || LIGHT;
    var next = current === DARK ? LIGHT : DARK;
    localStorage.setItem(STORAGE_KEY, next);
    applyTheme(next, true);
  }

  // Apply the theme immediately on script execution (before DOM interactive)
  applyTheme(getPreferredTheme(), false);

  // Bind toggle button and setup interactions once DOM is ready
  document.addEventListener('DOMContentLoaded', function () {
    var preferred = getPreferredTheme();
    // Re-apply to sync DOM elements and developer photos
    applyTheme(preferred, false);
    preloadDeveloperPhotos();
    initHoverPeek();

    var btn = document.getElementById('themeToggleBtn');
    if (btn) {
      btn.addEventListener('click', toggleTheme);
    }
  });

  // Listen for OS-level preference changes (if user has no explicit preference saved)
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
    if (!localStorage.getItem(STORAGE_KEY)) {
      applyTheme(e.matches ? DARK : LIGHT, true);
    }
  });

  // Expose toggleTheme globally
  window.toggleTheme = toggleTheme;
  window.syncDeveloperPhotos = syncDeveloperPhotos;
})();
