/**
 * Campus Job Posting System - Password Strength Engine & Validation (COAL101)
 * Real-time 4-Stage Strength Scoring & Criteria Checklist
 */

// Global Toggle Password Visibility Helper
window.togglePasswordVisibility = function (inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input) {
    if (input.type === 'password') {
      input.type = 'text';
      if (icon) {
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      }
    } else {
      input.type = 'password';
      if (icon) {
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      }
    }
  }
};

document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const meterFill = document.getElementById('password-meter-fill');
  const strengthText = document.getElementById('password-strength-text');
  const registerForm = document.getElementById('register-form');

  // Requirement elements
  const reqLength = document.getElementById('req-length');
  const reqLower = document.getElementById('req-lower');
  const reqUpper = document.getElementById('req-upper');
  const reqNumber = document.getElementById('req-number');
  const reqSpecial = document.getElementById('req-special');
  const confirmFeedback = document.getElementById('confirm-feedback');

  function updateRequirement(element, isMet) {
    if (!element) return;
    const icon = element.querySelector('i');
    if (isMet) {
      element.classList.add('met');
      element.classList.remove('unmet');
      if (icon) icon.className = 'bi bi-check-circle-fill text-accent';
    } else {
      element.classList.remove('met');
      element.classList.add('unmet');
      if (icon) icon.className = 'bi bi-circle text-muted-custom';
    }
  }

  function resetRequirements() {
    if (reqLength) updateRequirement(reqLength, false);
    if (reqLower) updateRequirement(reqLower, false);
    if (reqUpper) updateRequirement(reqUpper, false);
    if (reqNumber) updateRequirement(reqNumber, false);
    if (reqSpecial) updateRequirement(reqSpecial, false);
  }

  function evaluatePasswordStrength(password) {
    if (!password || password.length === 0) {
      resetRequirements();
      return { score: 0, count: 0 };
    }

    const checks = {
      length: password.length >= 8,
      lower: /[a-z]/.test(password),
      upper: /[A-Z]/.test(password),
      number: /[0-9]/.test(password),
      special: /[^A-Za-z0-9]/.test(password)
    };

    if (reqLength) updateRequirement(reqLength, checks.length);
    if (reqLower) updateRequirement(reqLower, checks.lower);
    if (reqUpper) updateRequirement(reqUpper, checks.upper);
    if (reqNumber) updateRequirement(reqNumber, checks.number);
    if (reqSpecial) updateRequirement(reqSpecial, checks.special);

    let count = 0;
    if (checks.length) count++;
    if (checks.lower) count++;
    if (checks.upper) count++;
    if (checks.number) count++;
    if (checks.special) count++;

    return { count, checks };
  }

  function handlePasswordInput() {
    if (!meterFill) return;
    const val = passwordInput.value;
    if (!val || val.length === 0) {
      meterFill.className = 'password-meter-fill';
      meterFill.style.width = '0%';
      if (strengthText) {
        strengthText.innerHTML = '<span class="text-muted-custom small">Enter password to see strength</span>';
      }
      resetRequirements();
      validatePasswordMatch();
      return;
    }

    const { count } = evaluatePasswordStrength(val);

    if (count <= 2) {
      meterFill.className = 'password-meter-fill strength-weak';
      if (strengthText) {
        strengthText.innerHTML = '<span class="text-danger fw-bold small"><i class="bi bi-shield-x"></i> Weak password</span>';
      }
    } else if (count === 3) {
      meterFill.className = 'password-meter-fill strength-fair';
      if (strengthText) {
        strengthText.innerHTML = '<span class="fw-bold small" style="color: #d97706;"><i class="bi bi-shield-slash"></i> Fair password</span>';
      }
    } else if (count === 4) {
      meterFill.className = 'password-meter-fill strength-good';
      if (strengthText) {
        strengthText.innerHTML = '<span class="fw-bold small" style="color: #2563eb;"><i class="bi bi-shield-check"></i> Good password</span>';
      }
    } else {
      meterFill.className = 'password-meter-fill strength-strong';
      if (strengthText) {
        strengthText.innerHTML = '<span class="text-accent fw-bold small"><i class="bi bi-patch-check-fill text-accent"></i> Strong password!</span>';
      }
    }

    validatePasswordMatch();
  }

  function validatePasswordMatch() {
    if (!confirmPasswordInput || !passwordInput) return true;
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
        confirmFeedback.className = 'valid-feedback d-block small';
        confirmFeedback.innerHTML = '<i class="bi bi-check-circle-fill text-accent"></i> Passwords match.';
      }
      return true;
    } else {
      confirmPasswordInput.classList.remove('is-valid');
      confirmPasswordInput.classList.add('is-invalid');
      if (confirmFeedback) {
        confirmFeedback.className = 'invalid-feedback d-block small';
        confirmFeedback.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i> Passwords do not match.';
      }
      return false;
    }
  }

  if (passwordInput) {
    passwordInput.addEventListener('input', handlePasswordInput);
  }

  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      const pVal = passwordInput ? passwordInput.value : '';
      const { count } = evaluatePasswordStrength(pVal);

      if (count < 2) {
        e.preventDefault();
        alert('Please choose a stronger password (at least 8 characters with numbers or uppercase letters).');
        if (passwordInput) passwordInput.focus();
        return false;
      }

      if (confirmPasswordInput && !validatePasswordMatch()) {
        e.preventDefault();
        alert('Passwords do not match. Please verify your confirm password entry.');
        confirmPasswordInput.focus();
        return false;
      }
    });
  }

  // Initial check
  if (passwordInput && passwordInput.value) {
    handlePasswordInput();
  }
});
