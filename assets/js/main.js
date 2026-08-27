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
  // 3. 3D TILT SCROLL PHYSICS (.sheet container)
  // ------------------------------------------------------------------------
  const sheet = document.querySelector('.sheet');
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (sheet && !prefersReducedMotion && window.innerWidth >= 768) {
    let lastScrollY = window.scrollY || window.pageYOffset;
    let targetRotateX = 0;
    let targetRotateY = 0;
    let currentRotateX = 0;
    let currentRotateY = 0;
    let isTicking = false;
    let scrollTimeout = null;

    const clamp = (num, min, max) => Math.min(Math.max(num, min), max);

    function onScrollPhysics() {
      const currentScrollY = window.scrollY || window.pageYOffset;
      const deltaY = currentScrollY - lastScrollY;
      lastScrollY = currentScrollY;

      // Map velocity to rotateX (±5deg max) and subtle rotateY (±2deg max)
      targetRotateX = clamp(deltaY * 0.12, -5, 5);
      targetRotateY = clamp(deltaY * 0.04, -2, 2);

      clearTimeout(scrollTimeout);
      scrollTimeout = setTimeout(function () {
        // Return to flat at rest
        targetRotateX = 0;
        targetRotateY = 0;
      }, 70);

      if (!isTicking) {
        isTicking = true;
        requestAnimationFrame(updatePhysicsRender);
      }
    }

    function updatePhysicsRender() {
      // Lerp with 0.08 smoothing factor
      currentRotateX += (targetRotateX - currentRotateX) * 0.08;
      currentRotateY += (targetRotateY - currentRotateY) * 0.08;

      if (Math.abs(currentRotateX) < 0.01 && Math.abs(targetRotateX) < 0.01 &&
          Math.abs(currentRotateY) < 0.01 && Math.abs(targetRotateY) < 0.01) {
        currentRotateX = 0;
        currentRotateY = 0;
        sheet.style.transform = 'none';
        isTicking = false;
        return;
      }

      sheet.style.transform = `rotateX(${currentRotateX.toFixed(3)}deg) rotateY(${currentRotateY.toFixed(3)}deg)`;
      requestAnimationFrame(updatePhysicsRender);
    }

    window.addEventListener('scroll', onScrollPhysics, { passive: true });

    // Handle resize
    window.addEventListener('resize', function () {
      if (window.innerWidth < 768) {
        sheet.style.transform = 'none';
      }
    });
  }

  // ------------------------------------------------------------------------
  // 4. SEARCH WIDGET CHIPS & PAY TOGGLE
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
  // 5. KEY METRICS CYCLING COUNTERS ENGINE
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

    // Run initial count-up when section is visible
    let hasCountedInitial = false;
    const metricsSection = document.querySelector('.metrics-section');
    if (metricsSection && 'IntersectionObserver' in window) {
      const metricsObserver = new IntersectionObserver(
        function (entries) {
          if (entries[0].isIntersecting && !hasCountedInitial) {
            hasCountedInitial = true;
            metricCounterElements.forEach(runCountUp);
            // Repeat cycle every 4 seconds
            setInterval(function () {
              metricCounterElements.forEach(runCountUp);
            }, 4000);
          }
        },
        { threshold: 0.25 }
      );
      metricsObserver.observe(metricsSection);
    } else {
      metricCounterElements.forEach(runCountUp);
    }
  }

  // ------------------------------------------------------------------------
  // 6. FEATURED JOBS CAROUSEL (Center-Mode Engine)
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
    window.addEventListener('resize', updateCarousel);
  }
});
