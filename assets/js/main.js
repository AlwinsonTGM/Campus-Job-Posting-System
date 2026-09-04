/**
 * Campus Job Posting System - Global Main JavaScript
 * Signature 3D Tilt Scroll Physics & Paper Sheet Interactions (COAL101)
 */

// Global Print Report Trigger Helper
window.triggerPrintReport = function () {
  window.print();
};

document.addEventListener('DOMContentLoaded', function () {
  // ------------------------------------------------------------------------
  // 1. BOOTSTRAP TOOLTIPS & AUTO-DISMISS ALERTS
  // ------------------------------------------------------------------------
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  const autoAlerts = document.querySelectorAll('.alert-auto-dismiss');
  autoAlerts.forEach(function (alertEl) {
    setTimeout(function () {
      const bsAlert = new bootstrap.Alert(alertEl);
      bsAlert.close();
    }, 5000);
  });

  // ------------------------------------------------------------------------
  // 2. INTERSECTION OBSERVER (Fade + Rise Reveals)
  // ------------------------------------------------------------------------
  const revealElements = document.querySelectorAll('.reveal-fade-rise');
  if ('IntersectionObserver' in window && revealElements.length > 0) {
    const revealObserver = new IntersectionObserver(
      function (entries, observer) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
      }
    );

    revealElements.forEach(function (el) {
      revealObserver.observe(el);
    });
  } else {
    // Fallback if IntersectionObserver is unsupported
    revealElements.forEach(function (el) {
      el.classList.add('is-revealed');
    });
  }

  // ------------------------------------------------------------------------
  // 3. SEARCH WIDGET CHIPS & PAY TOGGLE
  // ------------------------------------------------------------------------
  const payToggleSwitch = document.getElementById('pay-type-toggle');
  const payTypeHidden = document.getElementById('pay-type-hidden');
  const payHourlyLabel = document.getElementById('pay-hourly-label');
  const payStipendLabel = document.getElementById('pay-stipend-label');

  if (payToggleSwitch && payTypeHidden) {
    payToggleSwitch.addEventListener('change', function () {
      if (this.checked) {
        payTypeHidden.value = 'Stipend';
        if (payStipendLabel) payStipendLabel.classList.add('text-accent');
        if (payHourlyLabel) payHourlyLabel.classList.remove('text-accent');
      } else {
        payTypeHidden.value = 'Hourly';
        if (payHourlyLabel) payHourlyLabel.classList.add('text-accent');
        if (payStipendLabel) payStipendLabel.classList.remove('text-accent');
      }
    });
  }

  // ------------------------------------------------------------------------
  // 4. KEY METRICS CYCLING COUNTERS ENGINE (Single viewport-triggered run)
  // ------------------------------------------------------------------------
  const metricCounterElements = document.querySelectorAll('[data-counter-target]');
  if (metricCounterElements.length > 0) {
    function runCountUp(el) {
      const targetStr = el.getAttribute('data-counter-target') || '0';
      const prefix = el.getAttribute('data-counter-prefix') || '';
      const suffix = el.getAttribute('data-counter-suffix') || '';
      const targetNum = parseFloat(targetStr.replace(/[^\d.]/g, '')) || 0;
      
      const duration = 1200; // ~1.2s countup
      const startTime = performance.now();

      function updateNumber(now) {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Ease-out cubic
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        const currentVal = Math.round(easeProgress * targetNum);

        el.textContent = `${prefix}${currentVal}${suffix}`;

        if (progress < 1) {
          requestAnimationFrame(updateNumber);
        } else {
          el.textContent = `${prefix}${targetNum}${suffix}`;
        }
      }

      requestAnimationFrame(updateNumber);
    }

    // Run count-up once when section is visible, avoiding continuous layout thrashing
    const metricsSection = document.querySelector('.metrics-section');
    if (metricsSection && 'IntersectionObserver' in window) {
      const metricsObserver = new IntersectionObserver(
        function (entries, observer) {
          if (entries[0].isIntersecting) {
            metricCounterElements.forEach(runCountUp);
            observer.unobserve(entries[0].target);
            observer.disconnect();
          }
        },
        { threshold: 0.2 }
      );
      metricsObserver.observe(metricsSection);
    } else {
      metricCounterElements.forEach(runCountUp);
    }
  }

  // ------------------------------------------------------------------------
  // 5. FEATURED JOBS CAROUSEL (Truly Unlimited Endless / Infinite Loop Engine)
  // ------------------------------------------------------------------------
  const carouselTrack = document.getElementById('featured-carousel-track');
  const btnPrev = document.getElementById('carousel-prev-btn');
  const btnNext = document.getElementById('carousel-next-btn');

  if (carouselTrack && btnPrev && btnNext) {
    const originalCards = Array.from(carouselTrack.querySelectorAll('.featured-job-card'));
    const totalOriginals = originalCards.length;

    if (totalOriginals > 0) {
      // 3 sets before and 3 sets after provides ample buffer on all screen sizes
      const repeatCount = Math.max(3, Math.ceil(12 / totalOriginals));
      const prependClonesCount = repeatCount * totalOriginals;

      // Create prepended clones
      const prependFragment = document.createDocumentFragment();
      for (let r = 0; r < repeatCount; r++) {
        originalCards.forEach((card) => {
          const clone = card.cloneNode(true);
          clone.classList.add('is-clone');
          clone.classList.remove('is-active');
          clone.setAttribute('aria-hidden', 'true');
          clone.querySelectorAll('a, button, input').forEach((el) => el.setAttribute('tabindex', '-1'));
          prependFragment.appendChild(clone);
        });
      }
      carouselTrack.insertBefore(prependFragment, carouselTrack.firstChild);

      // Create appended clones
      const appendFragment = document.createDocumentFragment();
      for (let r = 0; r < repeatCount; r++) {
        originalCards.forEach((card) => {
          const clone = card.cloneNode(true);
          clone.classList.add('is-clone');
          clone.classList.remove('is-active');
          clone.setAttribute('aria-hidden', 'true');
          clone.querySelectorAll('a, button, input').forEach((el) => el.setAttribute('tabindex', '-1'));
          appendFragment.appendChild(clone);
        });
      }
      carouselTrack.appendChild(appendFragment);

      const allCards = Array.from(carouselTrack.querySelectorAll('.featured-job-card'));
      let currentIndex = prependClonesCount; // Start at first original card
      let currentActiveCard = allCards[currentIndex] || null;

      function getMetrics() {
        const firstCard = carouselTrack.querySelector('.featured-job-card');
        const cardWidth = firstCard ? firstCard.offsetWidth : 380;
        const style = window.getComputedStyle(carouselTrack);
        const gap = parseFloat(style.columnGap || style.gap) || 24;
        return { cardWidth, gap, step: cardWidth + gap };
      }

      function getOffsetForIndex(index) {
        const { cardWidth, step } = getMetrics();
        const containerWidth = carouselTrack.parentElement.offsetWidth;
        return (containerWidth / 2) - (cardWidth / 2) - (index * step);
      }

      function getCurrentTranslateX() {
        const style = window.getComputedStyle(carouselTrack);
        const transform = style.transform || style.webkitTransform;
        if (!transform || transform === 'none') {
          return getOffsetForIndex(currentIndex);
        }
        const matrix = transform.match(/^matrix\((.+)\)$/);
        if (matrix) {
          const values = matrix[1].split(', ');
          return parseFloat(values[4]) || getOffsetForIndex(currentIndex);
        }
        const matrix3d = transform.match(/^matrix3d\((.+)\)$/);
        if (matrix3d) {
          const values = matrix3d[1].split(', ');
          return parseFloat(values[12]) || getOffsetForIndex(currentIndex);
        }
        return getOffsetForIndex(currentIndex);
      }

      function updateActiveClasses(index) {
        if (currentActiveCard) {
          currentActiveCard.classList.remove('is-active');
        }
        currentActiveCard = allCards[index] || null;
        if (currentActiveCard) {
          currentActiveCard.classList.add('is-active');
        }
      }

      function moveTo(index, withTransition) {
        if (window.innerWidth < 768) {
          carouselTrack.style.transform = 'none';
          carouselTrack.style.transition = 'none';
          originalCards.forEach((c) => c.classList.add('is-active'));
          return;
        }

        if (withTransition) {
          carouselTrack.style.transition = 'transform 0.32s cubic-bezier(0.22, 1, 0.36, 1)';
        } else {
          carouselTrack.style.transition = 'none';
        }

        const offset = getOffsetForIndex(index);
        carouselTrack.style.transform = `translateX(${offset}px)`;
        updateActiveClasses(index);
      }

      function normalizeRestingPosition() {
        if (currentIndex >= prependClonesCount + totalOriginals || currentIndex < prependClonesCount) {
          const offsetFromBase = currentIndex - prependClonesCount;
          const normalized = ((offsetFromBase % totalOriginals) + totalOriginals) % totalOriginals;
          const newIndex = prependClonesCount + normalized;

          if (newIndex !== currentIndex) {
            carouselTrack.style.transition = 'none';
            currentIndex = newIndex;
            const offset = getOffsetForIndex(currentIndex);
            carouselTrack.style.transform = `translateX(${offset}px)`;
            updateActiveClasses(currentIndex);
            void carouselTrack.offsetWidth; // Force reflow
          }
        }
      }

      // Seamlessly normalize resting coordinates when motion stops
      carouselTrack.addEventListener('transitionend', function (e) {
        if (e.target !== carouselTrack || e.propertyName !== 'transform') return;
        normalizeRestingPosition();
      });

      function handleNext(e) {
        if (e) e.preventDefault();
        if (window.innerWidth < 768) return;

        const { step } = getMetrics();
        const cycleWidth = totalOriginals * step;

        // In-flight wrap: if spamming past base cycle, shift mid-flight coordinates by 1 cycle
        if (currentIndex >= prependClonesCount + totalOriginals) {
          const currentX = getCurrentTranslateX();
          const wrappedX = currentX + cycleWidth;
          carouselTrack.style.transition = 'none';
          carouselTrack.style.transform = `translateX(${wrappedX}px)`;
          currentIndex -= totalOriginals;
          updateActiveClasses(currentIndex);
          void carouselTrack.offsetWidth; // Force synchronous browser style application
        }

        currentIndex++;
        moveTo(currentIndex, true);
      }

      function handlePrev(e) {
        if (e) e.preventDefault();
        if (window.innerWidth < 768) return;

        const { step } = getMetrics();
        const cycleWidth = totalOriginals * step;

        // In-flight wrap: if spamming before base cycle, shift mid-flight coordinates by 1 cycle
        if (currentIndex < prependClonesCount) {
          const currentX = getCurrentTranslateX();
          const wrappedX = currentX - cycleWidth;
          carouselTrack.style.transition = 'none';
          carouselTrack.style.transform = `translateX(${wrappedX}px)`;
          currentIndex += totalOriginals;
          updateActiveClasses(currentIndex);
          void carouselTrack.offsetWidth; // Force synchronous browser style application
        }

        currentIndex--;
        moveTo(currentIndex, true);
      }

      btnNext.addEventListener('click', handleNext);
      btnPrev.addEventListener('click', handlePrev);

      // Initial position
      moveTo(currentIndex, false);

      // Debounced resize listener
      let resizeTimer = null;
      window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
          moveTo(currentIndex, false);
        }, 80);
      }, { passive: true });

      // Window load listener (ensures proper positioning after fonts & images load)
      window.addEventListener('load', function () {
        moveTo(currentIndex, false);
      });
    }
  }

  // ------------------------------------------------------------------------
  // 6. GLOBAL FLOATING SPOTLIGHT SEARCH MODAL ENGINE
  // ------------------------------------------------------------------------
  const spotlightModalEl = document.getElementById('globalSearchModal');
  if (spotlightModalEl) {
    const baseUrl = spotlightModalEl.getAttribute('data-base-url') || '';
    const searchInput = document.getElementById('spotlightSearchInput');
    const searchForm = document.getElementById('spotlightSearchForm');
    const clearBtn = document.getElementById('spotlightClearBtn');
    const resultsContainer = document.getElementById('spotlightResultsList');
    const loadingEl = document.getElementById('spotlightLoading');
    const emptyStateEl = document.getElementById('spotlightEmptyState');
    const resultCountEl = document.getElementById('spotlightResultCount');
    const fullSearchLink = document.getElementById('spotlightFullSearchLink');
    const filterChips = spotlightModalEl.querySelectorAll('.spotlight-chip');

    let activeFilterType = '';
    let activeFilterVal = '';
    let searchDebounceTimer = null;
    let currentAbortController = null;

    // Helper: Escape HTML strings
    function escapeHtml(str) {
      if (!str) return '';
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    // Perform Fetch Query
    function executeSpotlightSearch() {
      const query = (searchInput ? searchInput.value : '').trim();

      // Toggle clear button
      if (clearBtn) {
        if (query.length > 0) {
          clearBtn.classList.remove('d-none');
        } else {
          clearBtn.classList.add('d-none');
        }
      }

      // Update Full Results Page link
      if (fullSearchLink) {
        let fullUrl = `${baseUrl}student/jobs.php`;
        const params = [];
        if (query) params.push(`keyword=${encodeURIComponent(query)}`);
        if (activeFilterType === 'job_type' && activeFilterVal) params.push(`job_type=${encodeURIComponent(activeFilterVal)}`);
        if (activeFilterType === 'work_setup' && activeFilterVal) params.push(`work_setup=${encodeURIComponent(activeFilterVal)}`);
        if (params.length > 0) fullUrl += '?' + params.join('&');
        fullSearchLink.href = fullUrl;
      }

      // Cancel any ongoing fetch request
      if (currentAbortController) {
        currentAbortController.abort();
      }
      currentAbortController = new AbortController();

      // Show loader
      if (loadingEl) loadingEl.classList.remove('d-none');
      if (emptyStateEl) emptyStateEl.classList.add('d-none');

      let apiUrl = `${baseUrl}api/search-jobs.php?limit=8`;
      if (query) apiUrl += `&q=${encodeURIComponent(query)}`;
      if (activeFilterType === 'job_type' && activeFilterVal) apiUrl += `&job_type=${encodeURIComponent(activeFilterVal)}`;
      if (activeFilterType === 'work_setup' && activeFilterVal) apiUrl += `&work_setup=${encodeURIComponent(activeFilterVal)}`;

      fetch(apiUrl, { signal: currentAbortController.signal })
        .then(function (res) {
          if (!res.ok) throw new Error('Search failed');
          return res.json();
        })
        .then(function (data) {
          if (loadingEl) loadingEl.classList.add('d-none');

          const results = data.results || [];
          const total = data.total || 0;

          if (resultCountEl) {
            if (query) {
              resultCountEl.innerHTML = `Found <strong class="text-ink">${total}</strong> matching vacancies`;
            } else {
              resultCountEl.innerHTML = `Showing <strong class="text-ink">${results.length}</strong> top opportunities`;
            }
          }

          if (results.length === 0) {
            if (resultsContainer) resultsContainer.innerHTML = '';
            if (emptyStateEl) emptyStateEl.classList.remove('d-none');
            return;
          }

          if (emptyStateEl) emptyStateEl.classList.add('d-none');

          let html = '';
          results.forEach(function (job) {
            const jobDetailsUrl = `${baseUrl}student/job-details.php?id=${job.id}`;
            const applyUrl = `${baseUrl}student/apply.php?id=${job.id}`;
            const isPartnerBadge = job.is_partner
              ? `<span class="badge-tag-overlay p-1 px-2 small me-1" style="background-color: var(--ink); color: #fff; font-size: 10px;"><i class="bi bi-patch-check-fill text-accent"></i> Partner</span>`
              : '';

            html += `
              <div class="spotlight-job-item">
                <a href="${jobDetailsUrl}" class="spotlight-job-link text-decoration-none text-ink">
                  <img src="${escapeHtml(job.image)}" alt="${escapeHtml(job.title)}" class="spotlight-job-thumb">
                  <div class="spotlight-job-info">
                    <div class="d-flex align-items-center gap-1 mb-1">
                      ${isPartnerBadge}
                      <span class="spotlight-job-meta fw-semibold text-truncate d-inline-block">
                        ${escapeHtml(job.organization_name || job.department)}
                      </span>
                    </div>
                    <div class="spotlight-job-title">${escapeHtml(job.title)}</div>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                      <span class="badge bg-cream text-ink border border-line small py-1 px-2" style="font-size: 11px; font-weight: 600;">
                        ${escapeHtml(job.pay_rate)}
                      </span>
                      <span class="badge bg-surface text-muted-custom border border-line small py-1 px-2" style="font-size: 11px;">
                        <i class="bi bi-geo-alt text-accent me-1"></i>${escapeHtml(job.work_setup)}
                      </span>
                      <span class="badge bg-surface text-muted-custom border border-line small py-1 px-2 d-none d-sm-inline-block" style="font-size: 11px;">
                        ${escapeHtml(job.job_type)}
                      </span>
                    </div>
                  </div>
                </a>
                <div class="spotlight-job-action flex-shrink-0 d-flex align-items-center gap-2">
                  <a href="${applyUrl}" class="btn-accent-pill py-1 px-3 small" style="font-size: 0.75rem; white-space: nowrap;">
                    Apply <i class="bi bi-arrow-up-right ms-1"></i>
                  </a>
                </div>
              </div>
            `;
          });

          if (resultsContainer) {
            resultsContainer.innerHTML = html;
          }
        })
        .catch(function (err) {
          if (err.name === 'AbortError') return; // Ignore aborted fetch
          if (loadingEl) loadingEl.classList.add('d-none');
          if (resultsContainer) {
            resultsContainer.innerHTML = `
              <div class="alert alert-paper alert-paper--warning p-3 text-center small">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Unable to load live results. <a href="${baseUrl}student/jobs.php" class="fw-bold text-ink">Browse all jobs</a>
              </div>
            `;
          }
        });
    }

    // Debounced input handler
    if (searchInput) {
      searchInput.addEventListener('input', function () {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(executeSpotlightSearch, 180);
      });
    }

    // Clear Button Action
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        if (searchInput) {
          searchInput.value = '';
          searchInput.focus();
        }
        executeSpotlightSearch();
      });
    }

    // Filter Chips Click
    filterChips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        filterChips.forEach(function (c) { c.classList.remove('active'); });
        this.classList.add('active');
        activeFilterType = this.getAttribute('data-filter-type') || '';
        activeFilterVal = this.getAttribute('data-filter-val') || '';
        executeSpotlightSearch();
      });
    });

    // Form Submit handling (directs to student/jobs.php)
    if (searchForm) {
      searchForm.addEventListener('submit', function (e) {
        // Let standard GET submit carry keyword to student/jobs.php
      });
    }

    // Modal Events: Autofocus on open & pre-load default opportunities instantly
    spotlightModalEl.addEventListener('show.bs.modal', function () {
      const navCollapse = document.getElementById('navbarMain');
      if (navCollapse && navCollapse.classList.contains('show')) {
        try {
          if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            const bsCollapse = bootstrap.Collapse.getInstance(navCollapse) || new bootstrap.Collapse(navCollapse, { toggle: false });
            bsCollapse.hide();
          } else {
            navCollapse.classList.remove('show');
          }
        } catch (e) {
          navCollapse.classList.remove('show');
        }
      }
      executeSpotlightSearch();
    });
    spotlightModalEl.addEventListener('shown.bs.modal', function () {
      if (searchInput) {
        searchInput.focus();
      }
    });
  }

  // ------------------------------------------------------------------------
  // 7. REAL-TIME INSTANT FILTERING & LIVE SEARCH ENGINE
  // ------------------------------------------------------------------------
  const autoFilterForms = document.querySelectorAll('.auto-filter-form');

  autoFilterForms.forEach(function (form) {
    if (form.dataset.realtimeInitialized) return;
    form.dataset.realtimeInitialized = 'true';

    let debounceTimer = null;
    let currentAbortController = null;

    function executeLiveFilter() {
      const formData = new FormData(form);
      const params = new URLSearchParams();

      for (const [key, val] of formData.entries()) {
        const trimmed = (typeof val === 'string') ? val.trim() : val;
        if (trimmed !== null && trimmed !== undefined && trimmed !== '') {
          params.append(key, trimmed);
        }
      }

      const actionUrl = form.getAttribute('action') || window.location.pathname;
      const queryString = params.toString();
      const targetUrl = actionUrl + (queryString ? '?' + queryString : '');

      // Seamlessly sync browser address bar URL
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, '', targetUrl);
      }

      // Cancel any ongoing fetch request
      if (currentAbortController) {
        currentAbortController.abort();
      }
      currentAbortController = new AbortController();

      const resultsContainer = document.getElementById('filter-results-container');
      if (resultsContainer) {
        resultsContainer.style.transition = 'opacity 0.15s ease';
        resultsContainer.style.opacity = '0.5';
      }

      fetch(targetUrl, {
        signal: currentAbortController.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
        .then(function (res) {
          if (!res.ok) throw new Error('Filter fetch failed');
          return res.text();
        })
        .then(function (htmlText) {
          const parser = new DOMParser();
          const doc = parser.parseFromString(htmlText, 'text/html');

          const newTargetEl = doc.getElementById('filter-results-container');
          const currentTargetEl = document.getElementById('filter-results-container');

          if (newTargetEl && currentTargetEl) {
            currentTargetEl.innerHTML = newTargetEl.innerHTML;
            currentTargetEl.className = newTargetEl.className;
            currentTargetEl.style.opacity = '1';

            // Also update any count badges outside or in table headers
            const newCountEl = doc.querySelector('.card-paper-title + .text-muted-custom');
            const currentCountEl = document.querySelector('.card-paper-title + .text-muted-custom');
            if (newCountEl && currentCountEl && !currentTargetEl.contains(currentCountEl)) {
              currentCountEl.innerHTML = newCountEl.innerHTML;
            }

            // Re-initialize Bootstrap tooltips in new content
            const tooltips = [].slice.call(currentTargetEl.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltips.forEach(function (el) {
              new bootstrap.Tooltip(el);
            });
          } else {
            // Fallback: standard navigation
            window.location.href = targetUrl;
          }
        })
        .catch(function (err) {
          if (err.name === 'AbortError') return;
          if (resultsContainer) resultsContainer.style.opacity = '1';
        });
    }

    // 1. Instant filter on any dropdown change
    form.querySelectorAll('select').forEach(function (selectEl) {
      selectEl.addEventListener('change', function () {
        clearTimeout(debounceTimer);
        executeLiveFilter();
      });
    });

    // 2. Real-time debounced filter on text search inputs
    form.querySelectorAll('input[type="text"], input[type="search"]').forEach(function (inputEl) {
      inputEl.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(executeLiveFilter, 220);
      });

      inputEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          clearTimeout(debounceTimer);
          executeLiveFilter();
        }
      });
    });
  });

  // Global Keyboard Shortcut: Ctrl+K / Cmd+K to toggle Spotlight Search
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
      e.preventDefault();
      const modalEl = document.getElementById('globalSearchModal');
      if (modalEl && typeof bootstrap !== 'undefined') {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        if (modalEl.classList.contains('show')) {
          bsModal.hide();
        } else {
          bsModal.show();
        }
      }
    }
  });

  // Auto-expand FAQ Accordion from URL Hash (e.g., #faq-2 or #work-limits)
  function handleFaqHash() {
    if (!window.location.hash) return;
    const hash = window.location.hash.replace('#', '');
    if (!hash) return;
    const targetEl = document.getElementById(hash);
    if (targetEl) {
      let collapseEl = targetEl.classList.contains('accordion-collapse')
        ? targetEl
        : targetEl.querySelector('.accordion-collapse');
      
      if (!collapseEl && (targetEl.closest('.faq-item-card') || targetEl.closest('.accordion-item'))) {
        const parentCard = targetEl.closest('.faq-item-card') || targetEl.closest('.accordion-item');
        collapseEl = parentCard.querySelector('.accordion-collapse');
      }

      if (collapseEl && !collapseEl.classList.contains('show')) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
          const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
          bsCollapse.show();
        } else {
          collapseEl.classList.add('show');
        }
      }

      // Smoothly scroll to target
      setTimeout(function () {
        const itemToScroll = targetEl.closest('.faq-item-card') || targetEl.closest('.accordion-item') || targetEl;
        itemToScroll.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }, 150);
    }
  }

  handleFaqHash();
  window.addEventListener('hashchange', handleFaqHash);

  // ------------------------------------------------------------------------
  // 12. DEVBLOG & SPRINT CHRONICLES (3D Stage Coverflow & Reader Engine)
  // ------------------------------------------------------------------------
  const devblogTrack = document.getElementById('devblog-track');
  const devblogPrevBtns = Array.from(document.querySelectorAll('.devblog-prev-btn, #devblog-prev-btn'));
  const devblogNextBtns = Array.from(document.querySelectorAll('.devblog-next-btn, #devblog-next-btn'));

  if (devblogTrack && (devblogPrevBtns.length > 0 || devblogNextBtns.length > 0)) {
    const cards = Array.from(devblogTrack.querySelectorAll('.devblog-card'));
    const dots = Array.from(document.querySelectorAll('.devblog-dot'));
    const counterCurrent = document.getElementById('devblog-counter-current');
    const stageContainer = document.querySelector('.devblog-stage-container');
    const totalCards = cards.length;
    let currentIndex = 0; // Starts at index 0 (Sprint 06 - Latest)

    // Load devblogs JSON dataset
    let devblogsData = [];
    const dataScriptEl = document.getElementById('devblogs-data');
    if (dataScriptEl) {
      try {
        devblogsData = JSON.parse(dataScriptEl.textContent || '[]');
      } catch (err) {
        console.warn('Failed to parse devblogs data:', err);
      }
    }

    let isAnimating = false;

    function updateCoverflow(newIndex) {
      if (newIndex < 0 || newIndex >= totalCards) return;
      if (isAnimating && newIndex !== currentIndex) return;

      isAnimating = true;
      currentIndex = newIndex;

      // Update 3D card classes with directional left/right off-screen positions
      cards.forEach(function (card, idx) {
        card.classList.remove('is-active', 'is-prev', 'is-next', 'is-hidden', 'is-hidden-left', 'is-hidden-right');
        if (idx === currentIndex) {
          card.classList.add('is-active');
          card.setAttribute('tabindex', '0');
          card.setAttribute('aria-hidden', 'false');
        } else if (idx === currentIndex - 1) {
          card.classList.add('is-prev');
          card.setAttribute('tabindex', '-1');
          card.setAttribute('aria-hidden', 'true');
        } else if (idx === currentIndex + 1) {
          card.classList.add('is-next');
          card.setAttribute('tabindex', '-1');
          card.setAttribute('aria-hidden', 'true');
        } else if (idx < currentIndex - 1) {
          card.classList.add('is-hidden', 'is-hidden-left');
          card.setAttribute('tabindex', '-1');
          card.setAttribute('aria-hidden', 'true');
        } else {
          card.classList.add('is-hidden', 'is-hidden-right');
          card.setAttribute('tabindex', '-1');
          card.setAttribute('aria-hidden', 'true');
        }
      });

      // Update boundary button states for all navigation buttons
      document.querySelectorAll('.devblog-prev-btn, #devblog-prev-btn, #devblog-cluster-prev-btn').forEach(function (btn) {
        btn.disabled = (currentIndex === 0);
      });
      document.querySelectorAll('.devblog-next-btn, #devblog-next-btn, #devblog-cluster-next-btn').forEach(function (btn) {
        btn.disabled = (currentIndex === totalCards - 1);
      });

      // Update dots
      dots.forEach(function (dot, idx) {
        dot.classList.toggle('is-active', idx === currentIndex);
      });

      // Update counter
      if (counterCurrent && devblogsData[currentIndex]) {
        counterCurrent.textContent = devblogsData[currentIndex].sprint_number || String(devblogsData.length - currentIndex).padStart(2, '0');
      }

      // Reset animation lock after CSS transition completes
      setTimeout(function () {
        isAnimating = false;
      }, 260);
    }

    // Expose global navigation functions
    window.devblogGoPrev = function () {
      if (currentIndex > 0) {
        updateCoverflow(currentIndex - 1);
      }
    };

    window.devblogGoNext = function () {
      if (currentIndex < totalCards - 1) {
        updateCoverflow(currentIndex + 1);
      }
    };

    // Delegated click listener ensures any prev/next button works seamlessly
    document.addEventListener('click', function (e) {
      const prev = e.target.closest('.devblog-prev-btn, #devblog-prev-btn, #devblog-cluster-prev-btn');
      if (prev) {
        e.preventDefault();
        if (!prev.disabled) {
          window.devblogGoPrev();
        }
        return;
      }
      const next = e.target.closest('.devblog-next-btn, #devblog-next-btn, #devblog-cluster-next-btn');
      if (next) {
        e.preventDefault();
        if (!next.disabled) {
          window.devblogGoNext();
        }
        return;
      }
    });

    // Step Dot Handlers
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        const dotIdx = parseInt(this.getAttribute('data-dot-index'), 10);
        if (!isNaN(dotIdx)) {
          updateCoverflow(dotIdx);
        }
      });
    });

    // Direct Side Card Click Handler
    cards.forEach(function (card, idx) {
      card.addEventListener('click', function (e) {
        // If clicking the read trigger button, let the modal trigger handle it
        if (e.target.closest('.devblog-read-trigger')) return;

        if (card.classList.contains('is-prev')) {
          updateCoverflow(currentIndex - 1);
        } else if (card.classList.contains('is-next')) {
          updateCoverflow(currentIndex + 1);
        }
      });
    });

    // Keyboard Arrow Keys when focused or hovering on stage
    let isHoveringStage = false;
    if (stageContainer) {
      stageContainer.addEventListener('mouseenter', function () { isHoveringStage = true; });
      stageContainer.addEventListener('mouseleave', function () { isHoveringStage = false; });
    }

    document.addEventListener('keydown', function (e) {
      const modalEl = document.getElementById('devblogModal');
      const isModalOpen = modalEl && modalEl.classList.contains('show');
      if (isModalOpen) return; // Handled separately when modal is open

      if (isHoveringStage || (document.activeElement && document.activeElement.closest('.devblog-section'))) {
        if (e.key === 'ArrowLeft') {
          e.preventDefault();
          if (currentIndex > 0) updateCoverflow(currentIndex - 1);
        } else if (e.key === 'ArrowRight') {
          e.preventDefault();
          if (currentIndex < totalCards - 1) updateCoverflow(currentIndex + 1);
        }
      }
    });

    // Touch Swipe Gesture Support
    let touchStartX = 0;
    let touchEndX = 0;
    devblogTrack.addEventListener('touchstart', function (e) {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    devblogTrack.addEventListener('touchend', function (e) {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchEndX - touchStartX;
      if (Math.abs(diff) > 45) {
        if (diff > 0 && currentIndex > 0) {
          // Swipe right -> Go to newer / previous sprint
          updateCoverflow(currentIndex - 1);
        } else if (diff < 0 && currentIndex < totalCards - 1) {
          // Swipe left -> Go to older / next sprint
          updateCoverflow(currentIndex + 1);
        }
      }
    }, { passive: true });

    // ------------------------------------------------------------------------
    // Interactive DevBlog Modal Populator
    // ------------------------------------------------------------------------
    const modalEl = document.getElementById('devblogModal');
    let modalCurrentIndex = 0;

    function populateDevblogModal(index) {
      if (!devblogsData[index]) return;
      modalCurrentIndex = index;
      const blog = devblogsData[index];

      // Update Coverflow position synchronously
      updateCoverflow(index);

      // Elements
      const badgeEl = document.getElementById('devblog-modal-sprint-badge');
      const readtimeEl = document.getElementById('devblog-modal-readtime');
      const bannerEl = document.getElementById('devblog-modal-banner');
      const titleEl = document.getElementById('devblog-modal-title');
      const authorImgEl = document.getElementById('devblog-modal-author-img');
      const authorNameEl = document.getElementById('devblog-modal-author-name');
      const authorRoleEl = document.getElementById('devblog-modal-author-role');
      const dateEl = document.getElementById('devblog-modal-date');
      const storyEl = document.getElementById('devblog-modal-story');
      const takeawaysEl = document.getElementById('devblog-modal-takeaways');
      const techstackEl = document.getElementById('devblog-modal-techstack');
      const modalPrevBtn = document.getElementById('devblog-modal-prev-btn');
      const modalNextBtn = document.getElementById('devblog-modal-next-btn');

      if (badgeEl) {
        badgeEl.innerHTML = '<i class="bi bi-flag-fill text-accent me-1"></i>' + (blog.sprint_badge || ('DAY ' + (blog.sprint_number || '01')));
      }
      if (readtimeEl) {
        readtimeEl.innerHTML = '<i class="bi bi-clock-history me-1 text-accent"></i>' + (blog.read_time || '5 min read');
      }
      if (bannerEl) {
        bannerEl.src = blog.cover_image || '';
        bannerEl.alt = blog.title || 'Sprint Cover';
      }
      if (titleEl) titleEl.textContent = blog.title || '';
      if (authorImgEl) {
        // Resolve path relative to document or base URL
        const cardImg = cards[index] ? cards[index].querySelector('.devblog-author-img') : null;
        authorImgEl.src = cardImg ? cardImg.src : (blog.author_image || '');
        authorImgEl.alt = blog.author_name || '';
      }
      if (authorNameEl) authorNameEl.textContent = blog.author_name || '';
      if (authorRoleEl) authorRoleEl.textContent = blog.author_role || '';
      if (dateEl) dateEl.textContent = blog.date || '';
      if (storyEl) storyEl.innerHTML = blog.full_story || '';

      // Populate Takeaways
      if (takeawaysEl) {
        takeawaysEl.innerHTML = '';
        if (Array.isArray(blog.key_takeaways)) {
          blog.key_takeaways.forEach(function (t) {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-start gap-2';
            li.innerHTML = '<i class="bi bi-check-circle-fill text-accent flex-shrink-0 mt-1"></i> <span>' + t + '</span>';
            takeawaysEl.appendChild(li);
          });
        }
      }

      // Populate Tech Stack
      if (techstackEl) {
        techstackEl.innerHTML = '';
        if (Array.isArray(blog.tech_stack)) {
          blog.tech_stack.forEach(function (tech) {
            const span = document.createElement('span');
            span.className = 'badge bg-cream text-ink border border-line px-2 py-1 small fw-semibold';
            span.textContent = tech;
            techstackEl.appendChild(span);
          });
        }
      }

      // Modal navigation buttons
      if (modalPrevBtn) {
        modalPrevBtn.disabled = (modalCurrentIndex === 0);
      }
      if (modalNextBtn) {
        modalNextBtn.disabled = (modalCurrentIndex === totalCards - 1);
      }
    }

    // Trigger button listeners
    document.addEventListener('click', function (e) {
      const trigger = e.target.closest('.devblog-read-trigger');
      if (trigger) {
        e.preventDefault();
        const idx = parseInt(trigger.getAttribute('data-blog-index'), 10);
        if (!isNaN(idx) && modalEl && typeof bootstrap !== 'undefined') {
          populateDevblogModal(idx);
          const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
          bsModal.show();
        }
      }
    });

    // Modal Prev/Next Navigation
    const modalPrevBtn = document.getElementById('devblog-modal-prev-btn');
    const modalNextBtn = document.getElementById('devblog-modal-next-btn');

    if (modalPrevBtn) {
      modalPrevBtn.addEventListener('click', function () {
        if (modalCurrentIndex > 0) {
          populateDevblogModal(modalCurrentIndex - 1);
          const modalBody = document.getElementById('devblog-modal-body');
          if (modalBody) modalBody.scrollTop = 0;
        }
      });
    }

    if (modalNextBtn) {
      modalNextBtn.addEventListener('click', function () {
        if (modalCurrentIndex < totalCards - 1) {
          populateDevblogModal(modalCurrentIndex + 1);
          const modalBody = document.getElementById('devblog-modal-body');
          if (modalBody) modalBody.scrollTop = 0;
        }
      });
    }

    // Initialize initial coverflow state
    updateCoverflow(0);
  }
});

// ------------------------------------------------------------------------
// GLOBAL DOUBLE-SUBMIT PREVENTION & SPINNER ENGINE
// ------------------------------------------------------------------------
document.addEventListener('submit', function (e) {
  const form = e.target;
  if (!form || !(form instanceof HTMLFormElement)) return;

  // Only intercept state-mutating POST forms
  const method = (form.getAttribute('method') || 'GET').toUpperCase();
  if (method !== 'POST') return;

  // Respect HTML5 form validation (do not block if invalid inputs exist)
  if (form.checkValidity && !form.checkValidity()) return;

  // If a custom validator (e.g. shift matrix in apply.php) cancelled submit, abort
  if (e.defaultPrevented) return;

  // If form is already actively submitting, block duplicate submission
  if (form.dataset.submitting === 'true') {
    e.preventDefault();
    return false;
  }

  // Mark form as actively submitting
  form.dataset.submitting = 'true';

  // Disable submit buttons asynchronously on the next tick so the clicked button's
  // name and value are preserved in standard form serialization
  const submitBtns = form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
  submitBtns.forEach(function (btn) {
    btn.classList.add('disabled', 'btn-submitting');
    btn.setAttribute('aria-disabled', 'true');
    setTimeout(function () {
      btn.disabled = true;
    }, 20);
  });

  // Safety fallback: re-enable after 8 seconds if server response or navigation is delayed
  setTimeout(function () {
    form.dataset.submitting = 'false';
    submitBtns.forEach(function (btn) {
      btn.classList.remove('disabled', 'btn-submitting');
      btn.removeAttribute('aria-disabled');
      btn.disabled = false;
    });
  }, 8000);
}, false);

// Reset submitting lock on bfcache restore (browser back button)
window.addEventListener('pageshow', function (event) {
  document.querySelectorAll('form[data-submitting="true"]').forEach(function (form) {
    form.dataset.submitting = 'false';
    form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]').forEach(function (btn) {
      btn.classList.remove('disabled', 'btn-submitting');
      btn.removeAttribute('aria-disabled');
      btn.disabled = false;
    });
  });
});

