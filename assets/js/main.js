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
  // 5. FEATURED JOBS CAROUSEL (Center-Mode Engine)
  // ------------------------------------------------------------------------
  const carouselTrack = document.getElementById('featured-carousel-track');
  const btnPrev = document.getElementById('carousel-prev-btn');
  const btnNext = document.getElementById('carousel-next-btn');

  if (carouselTrack && btnPrev && btnNext) {
    const cards = carouselTrack.querySelectorAll('.featured-job-card');
    let currentIndex = 0;
    const totalCards = cards.length;

    function updateCarousel() {
      if (window.innerWidth < 768) {
        carouselTrack.style.transform = 'none';
        cards.forEach((c) => c.classList.add('is-active'));
        return;
      }

      cards.forEach(function (card, idx) {
        if (idx === currentIndex) {
          card.classList.add('is-active');
        } else {
          card.classList.remove('is-active');
        }
      });

      // Calculate translation to keep active card centered in viewport
      const cardWidth = 380;
      const gap = 24;
      const containerWidth = carouselTrack.parentElement.offsetWidth;
      const offset = (containerWidth / 2) - (cardWidth / 2) - (currentIndex * (cardWidth + gap));
      
      carouselTrack.style.transform = `translateX(${offset}px)`;
    }

    btnNext.addEventListener('click', function () {
      currentIndex = (currentIndex + 1) % totalCards;
      updateCarousel();
    });

    btnPrev.addEventListener('click', function () {
      currentIndex = (currentIndex - 1 + totalCards) % totalCards;
      updateCarousel();
    });

    // Initial setup
    updateCarousel();

    // Debounced resize listener
    let resizeTimer = null;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(updateCarousel, 100);
    }, { passive: true });
  }
});
