/**
 * Campus Job Posting System - Global Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
  // Initialize Bootstrap Tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Auto dismiss flash alerts after 5 seconds
  const autoAlerts = document.querySelectorAll('.alert-auto-dismiss');
  autoAlerts.forEach(function (alertEl) {
    setTimeout(function () {
      const bsAlert = new bootstrap.Alert(alertEl);
      bsAlert.close();
    }, 5000);
  });

  // Quick Demo Account Auto-Fill in Login Page
  const demoButtons = document.querySelectorAll('[data-demo-role]');
  demoButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const role = this.getAttribute('data-demo-role');
      const emailInput = document.getElementById('login-email');
      const passInput = document.getElementById('login-password');

      if (!emailInput || !passInput) return;

      if (role === 'student') {
        emailInput.value = 'student@university.edu.ph';
        passInput.value = 'Password123!';
      } else if (role === 'employer') {
        emailInput.value = 'registrar@university.edu.ph';
        passInput.value = 'Password123!';
      } else if (role === 'admin') {
        emailInput.value = 'admin@university.edu.ph';
        passInput.value = 'Password123!';
      }

      // Visual flash
      emailInput.classList.add('is-valid');
      passInput.classList.add('is-valid');
      setTimeout(() => {
        emailInput.classList.remove('is-valid');
        passInput.classList.remove('is-valid');
      }, 1500);
    });
  });

  // Live filter for job cards if on job search page
  const searchInput = document.getElementById('live-job-search');
  const jobCardItems = document.querySelectorAll('.job-item-card');

  if (searchInput && jobCardItems.length > 0) {
    searchInput.addEventListener('input', function () {
      const query = this.value.toLowerCase().trim();
      jobCardItems.forEach(function (card) {
        const text = card.textContent.toLowerCase();
        if (text.includes(query)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    });
  }
});

// Helper for print report
function triggerPrintReport() {
  window.print();
}
