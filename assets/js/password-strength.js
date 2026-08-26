/**
 * Campus Job Posting System - Real-time JavaScript Password Strength Validator
 */

document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const meterFill = document.getElementById('password-meter-fill');
  const strengthText = document.getElementById('password-strength-text');
  const registerForm = document.getElementById('register-form');

  // Requirement items
  const reqLength = document.getElementById('req-length');
  const reqLower = document.getElementById('req-lower');
  const reqUpper = document.getElementById('req-upper');
  const reqNumber = document.getElementById('req-number');
  const reqSpecial = document.getElementById('req-special');
  const confirmFeedback = document.getElementById('confirm-feedback');

  if (!passwordInput || !meterFill) return;

  function evaluatePasswordStrength(password) {
    let score = 0;
    const checks = {
      length: password.length >= 8,
      lower: /[a-z]/.test(password),
      upper: /[A-Z]/.test(password),
      number: /[0-9]/.test(password),
      special: /[^A-Za-z0-9]/.test(password)
    };

    // Update UI Checklists if they exist
    if (reqLength) updateRequirement(reqLength, checks.length);
    if (reqLower) updateRequirement(reqLower, checks.lower);
    if (reqUpper) updateRequirement(reqUpper, checks.upper);
    if (reqNumber) updateRequirement(reqNumber, checks.number);
    if (reqSpecial) updateRequirement(reqSpecial, checks.special);

    // Calculate score
    if (checks.length) score += 20;
    if (password.length >= 12) score += 10;
    if (checks.lower) score += 20;
    if (checks.upper) score += 20;
    if (checks.number) score += 15;
    if (checks.special) score += 15;

    return { score, checks };
  }

  function updateRequirement(element, isMet) {
    const icon = element.querySelector('i');
    if (isMet) {
      element.classList.add('met');
      element.classList.remove('unmet');
      if (icon) icon.className = 'bi bi-check-circle-fill text-success';
    } else {
      element.classList.remove('met');
      element.classList.add('unmet');
      if (icon) icon.className = 'bi bi-circle text-muted';
    }
  }

  passwordInput.addEventListener('input', function () {
    const val = passwordInput.value;
    if (!val) {
      meterFill.className = 'password-meter-fill';
      meterFill.style.width = '0%';
      if (strengthText) strengthText.innerHTML = '<span class="text-muted small">Enter password to see strength</span>';
      return;
    }

    const { score } = evaluatePasswordStrength(val);

    if (score < 50) {
      meterFill.className = 'password-meter-fill strength-weak';
      meterFill.style.width = '30%';
      if (strengthText) strengthText.innerHTML = '<span class="text-danger fw-bold small"><i class="bi bi-shield-x"></i> Weak password</span>';
    } else if (score < 80) {
      meterFill.className = 'password-meter-fill strength-medium';
      meterFill.style.width = '65%';
      if (strengthText) strengthText.innerHTML = '<span class="text-warning fw-bold small"><i class="bi bi-shield-slash"></i> Moderate password</span>';
    } else {
      meterFill.className = 'password-meter-fill strength-strong';
      meterFill.style.width = '100%';
      if (strengthText) strengthText.innerHTML = '<span class="text-success fw-bold small"><i class="bi bi-shield-check"></i> Strong password!</span>';
    }

    validatePasswordMatch();
  });

  function validatePasswordMatch() {
    if (!confirmPasswordInput) return true;
    const pVal = passwordInput.value;
    const cpVal = confirmPasswordInput.value;

    if (!cpVal) {
      confirmPasswordInput.classList.remove('is-valid', 'is-invalid');
      if (confirmFeedback) confirmFeedback.innerHTML = '';
      return false;
    }

    if (pVal === cpVal) {
      confirmPasswordInput.classList.remove('is-invalid');
      confirmPasswordInput.classList.add('is-valid');
      if (confirmFeedback) {
        confirmFeedback.className = 'valid-feedback d-block';
        confirmFeedback.innerHTML = '<i class="bi bi-check-circle"></i> Passwords match perfectly.';
      }
      return true;
    } else {
      confirmPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.add('is-invalid');
      if (confirmFeedback) {
        confirmFeedback.className = 'invalid-feedback d-block';
        confirmFeedback.innerHTML = '<i class="bi bi-x-circle"></i> Passwords do not match.';
      }
      return false;
    }
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      const pVal = passwordInput.value;
      const { score } = evaluatePasswordStrength(pVal);

      if (score < 50) {
        e.preventDefault();
        alert('Please choose a stronger password before continuing.');
        passwordInput.focus();
        return false;
      }

      if (confirmPasswordInput && !validatePasswordMatch()) {
        e.preventDefault();
        alert('Passwords do not match. Please recheck your confirm password entry.');
        confirmPasswordInput.focus();
        return false;
      }
    });
  }
});
