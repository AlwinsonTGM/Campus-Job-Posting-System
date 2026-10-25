/**
 * Roblox-Style Category Search & Quick-Clear Component
 * For Campus Job Posting System - User Directory & Administration
 * Paper Sheet & Obsidian Cyberpunk Theme
 * Unified Brand Palette: Consistent single accent color across all icons
 */
(function () {
  'use strict';

  function escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function clearScopedInputs() {
    const roleSel = document.getElementById('role-select');
    const empSel = document.getElementById('emp-select');
    const verSel = document.getElementById('ver-select');
    const fieldSel = document.getElementById('search-field');
    if (roleSel) roleSel.value = '';
    if (empSel) empSel.value = '';
    if (verSel) verSel.value = '';
    if (fieldSel) fieldSel.value = '';
  }

  // Unified single accent color (text-accent) matching brand consistency
  const ROBLOX_CATEGORIES = [
    {
      id: 'all',
      name: 'All Directory',
      icon: 'bi-people-fill text-accent',
      isDefault: true,
      apply: function () {
        clearScopedInputs();
      }
    },
    {
      id: 'student',
      name: 'Students',
      icon: 'bi-mortarboard-fill text-accent',
      apply: function () {
        clearScopedInputs();
        const roleSel = document.getElementById('role-select');
        if (roleSel) roleSel.value = 'student';
      }
    },
    {
      id: 'employer',
      name: 'Employers & Offices',
      icon: 'bi-building text-accent',
      apply: function () {
        clearScopedInputs();
        const roleSel = document.getElementById('role-select');
        if (roleSel) roleSel.value = 'employer';
      }
    },
    {
      id: 'org_program',
      name: 'Organization / Program',
      icon: 'bi-diagram-3-fill text-accent',
      apply: function () {
        clearScopedInputs();
        const fieldSel = document.getElementById('search-field');
        if (fieldSel) fieldSel.value = 'org_program';
      }
    },
    {
      id: 'id_code',
      name: 'ID / Code',
      icon: 'bi-upc-scan text-accent',
      apply: function () {
        clearScopedInputs();
        const fieldSel = document.getElementById('search-field');
        if (fieldSel) fieldSel.value = 'id_code';
      }
    },
    {
      id: 'university_office',
      name: 'University Offices',
      icon: 'bi-bank text-accent',
      apply: function () {
        clearScopedInputs();
        const roleSel = document.getElementById('role-select');
        const empSel = document.getElementById('emp-select');
        if (roleSel) roleSel.value = 'employer';
        if (empSel) empSel.value = 'university_office';
      }
    },
    {
      id: 'approved_partner',
      name: 'Approved Partners',
      icon: 'bi-patch-check-fill text-accent',
      apply: function () {
        clearScopedInputs();
        const roleSel = document.getElementById('role-select');
        const empSel = document.getElementById('emp-select');
        if (roleSel) roleSel.value = 'employer';
        if (empSel) empSel.value = 'approved_partner';
      }
    },
    {
      id: 'admin',
      name: 'Administrators',
      icon: 'bi-shield-lock-fill text-accent',
      apply: function () {
        clearScopedInputs();
        const roleSel = document.getElementById('role-select');
        if (roleSel) roleSel.value = 'admin';
      }
    },
    {
      id: 'pending_approval',
      name: 'Pending Review',
      icon: 'bi-hourglass-split text-accent',
      apply: function () {
        clearScopedInputs();
        const verSel = document.getElementById('ver-select');
        if (verSel) verSel.value = 'pending_approval';
      }
    },
    {
      id: 'verified',
      name: 'Verified Accounts',
      icon: 'bi-check-circle-fill text-accent',
      apply: function () {
        clearScopedInputs();
        const verSel = document.getElementById('ver-select');
        if (verSel) verSel.value = 'verified';
      }
    }
  ];

  function initRobloxSearch() {
    const wrap = document.getElementById('robloxSearchWrap');
    const input = document.getElementById('search-user');
    const clearBtn = document.getElementById('searchUserClearBtn');
    const dropdown = document.getElementById('robloxSearchDropdown');
    const list = document.getElementById('robloxSearchList');
    const form = input ? input.closest('form') : null;

    if (!wrap || !input || !dropdown || !list) return;
    if (wrap.dataset.robloxInitialized === 'true') return;
    wrap.dataset.robloxInitialized = 'true';

    let highlightedIndex = -1;
    let isOpen = false;

    function syncClearButton() {
      if (!clearBtn) return;
      const hasVal = input.value.trim().length > 0;
      if (hasVal) {
        clearBtn.classList.remove('d-none');
        wrap.classList.add('has-clear-btn');
      } else {
        clearBtn.classList.add('d-none');
        wrap.classList.remove('has-clear-btn');
      }
    }

    function renderItems() {
      const q = input.value.trim();
      const displayQuery = q.length > 0 ? `"${escapeHtml(q)}"` : 'Search';
      list.innerHTML = '';

      ROBLOX_CATEGORIES.forEach(function (cat, index) {
        const item = document.createElement('div');
        item.className = 'roblox-search-item' + (index === highlightedIndex ? ' highlighted' : '');
        item.setAttribute('role', 'option');
        item.setAttribute('tabindex', '-1');
        item.setAttribute('data-index', String(index));
        item.setAttribute('aria-selected', index === highlightedIndex ? 'true' : 'false');

        const defaultBadge = cat.isDefault
          ? '<div class="item-badge-wrap"><span class="badge-roblox-default"><i class="bi bi-check2 text-accent me-1"></i>Default</span></div>'
          : '';

        item.innerHTML =
          '<div class="item-icon-box"><i class="bi ' + cat.icon + '"></i></div>' +
          '<div class="item-body">' +
          '<span class="item-query">' + displayQuery + '</span> ' +
          '<span class="item-in">in</span> ' +
          '<span class="item-category">' + escapeHtml(cat.name) + '</span>' +
          '</div>' +
          defaultBadge;

        item.addEventListener('mouseenter', function () {
          highlightedIndex = index;
          updateHighlight();
        });

        item.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          selectCategory(cat);
        });

        list.appendChild(item);
      });
    }

    function updateHighlight() {
      const items = list.querySelectorAll('.roblox-search-item');
      items.forEach(function (item, idx) {
        if (idx === highlightedIndex) {
          item.classList.add('highlighted');
          item.setAttribute('aria-selected', 'true');
          item.scrollIntoView({ block: 'nearest' });
        } else {
          item.classList.remove('highlighted');
          item.setAttribute('aria-selected', 'false');
        }
      });
    }

    function openDropdown() {
      renderItems();
      dropdown.classList.add('show');
      wrap.classList.add('dropdown-open');
      input.setAttribute('aria-expanded', 'true');
      isOpen = true;
    }

    function closeDropdown() {
      dropdown.classList.remove('show');
      wrap.classList.remove('dropdown-open');
      input.setAttribute('aria-expanded', 'false');
      highlightedIndex = -1;
      isOpen = false;
    }

    function triggerFormFilter() {
      if (!form) return;
      const sortSel = document.getElementById('sort-select');
      if (sortSel) {
        sortSel.dispatchEvent(new Event('change', { bubbles: true }));
      } else {
        input.dispatchEvent(new Event('input', { bubbles: true }));
      }
    }

    function selectCategory(cat) {
      if (typeof cat.apply === 'function') {
        cat.apply();
      }
      closeDropdown();
      triggerFormFilter();
    }

    // Input Events
    input.addEventListener('input', function () {
      syncClearButton();
      highlightedIndex = -1;
      renderItems();
      if (!isOpen) openDropdown();
    });

    input.addEventListener('focus', function () {
      syncClearButton();
      openDropdown();
    });

    input.addEventListener('click', function () {
      if (!isOpen) openDropdown();
    });

    // Keyboard Navigation
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!isOpen) {
          openDropdown();
        }
        highlightedIndex = (highlightedIndex + 1) % ROBLOX_CATEGORIES.length;
        updateHighlight();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!isOpen) {
          openDropdown();
        }
        highlightedIndex = (highlightedIndex - 1 + ROBLOX_CATEGORIES.length) % ROBLOX_CATEGORIES.length;
        updateHighlight();
      } else if (e.key === 'Enter') {
        // "default the user if they just click enter when searching"
        e.preventDefault();
        e.stopPropagation();

        if (isOpen && highlightedIndex >= 0 && highlightedIndex < ROBLOX_CATEGORIES.length) {
          // Explicit category highlighted by arrow keys
          selectCategory(ROBLOX_CATEGORIES[highlightedIndex]);
        } else {
          // Default category behavior when user simply hits Enter
          const defaultCat = ROBLOX_CATEGORIES.find(function (c) { return c.isDefault; }) || ROBLOX_CATEGORIES[0];
          selectCategory(defaultCat);
        }
      } else if (e.key === 'Escape') {
        e.preventDefault();
        closeDropdown();
      } else if (e.key === 'Tab') {
        closeDropdown();
      }
    });

    // Clear Button (X)
    if (clearBtn) {
      clearBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        input.value = '';
        clearScopedInputs();
        syncClearButton();
        closeDropdown();
        triggerFormFilter();
        input.focus();
      });
    }

    // Click Outside Dismissal (both pointerdown and mousedown for universal coverage)
    function handleClickOutside(e) {
      if (isOpen && !wrap.contains(e.target)) {
        closeDropdown();
      }
    }
    document.addEventListener('pointerdown', handleClickOutside);
    document.addEventListener('mousedown', handleClickOutside);

    // Initial sync
    syncClearButton();
  }

  // Self-initialize on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRobloxSearch);
  } else {
    initRobloxSearch();
  }

  // Export to window for dynamic/AJAX re-invocation if needed
  window.initRobloxSearch = initRobloxSearch;
})();
