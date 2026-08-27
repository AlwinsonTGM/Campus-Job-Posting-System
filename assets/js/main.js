/**
 * Campus Job Posting System - Global Main JavaScript
 * Signature 3D Tilt Scroll Physics & Paper Sheet Interactions (COAL101)
 */

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
});
