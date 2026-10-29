/* Authentication interactions. Account creation and credential validation stay on the server. */
(() => {
  'use strict';
  document.querySelectorAll('[data-password]').forEach(button => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.password);
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      button.setAttribute('aria-pressed', String(show));
      button.setAttribute('aria-label', `${show ? 'Hide' : 'Show'} ${input.name === 'confirm_password' ? 'confirm password' : 'password'}`);
      button.querySelector('i').className = `bi bi-eye${show ? '-slash' : ''}`;
    });
  });
  document.querySelector('[data-server-error]')?.focus();

  const form = document.getElementById('register-form');
  if (!form) return;
  const panes = [...form.querySelectorAll('[data-step]')];
  const progress = [...document.querySelectorAll('#registration-progress li')];
  const summary = document.getElementById('step-error-alert');
  const isStudent = form.dataset.role === 'student';
  let currentStep = Math.min(3, Math.max(1, Number(form.dataset.initialStep) || 1));
  let busy = false;

  const passwordInput = document.getElementById('password');
  const passwordMeter = document.getElementById('password-strength-meter');
  const strengthText = document.getElementById('password-strength-text');
  function updatePasswordStrength() {
    const value = passwordInput.value;
    const checks = {
      length: value.length >= 8,
      upper: /[A-Z]/.test(value),
      lower: /[a-z]/.test(value),
      number: /[0-9]/.test(value),
      special: /[^A-Za-z0-9]/.test(value)
    };
    const count = Object.values(checks).filter(Boolean).length;
    const score = !value ? 0 : !checks.length ? 1 : Math.max(1, count - 1);
    const label = ['Not entered', 'Weak', 'Fair', 'Good', 'Strong'][score];
    passwordMeter.setAttribute('aria-valuenow', String(score));
    passwordMeter.setAttribute('aria-valuetext', value ? `${label} password. Minimum length ${checks.length ? 'met' : 'not met'}.` : 'No password entered');
    passwordMeter.dataset.strength = String(score);
    document.getElementById('password-meter-fill').style.width = `${score * 25}%`;
    if (strengthText.textContent !== label) strengthText.textContent = label;
    strengthText.dataset.strength = String(score);
    document.querySelectorAll('[data-requirement]').forEach(item => {
      const met = value !== '' && checks[item.dataset.requirement];
      item.dataset.met = String(met);
      item.querySelector('i').className = `bi bi-${met ? 'check-circle-fill' : 'circle'}`;
      item.querySelector('.requirement-state').textContent = met ? 'Met: ' : 'Not yet met: ';
    });
  }
  passwordInput.addEventListener('input', updatePasswordStrength);
  passwordInput.addEventListener('change', updatePasswordStrength);
  updatePasswordStrength();

  function displayStep(step, focus = true) {
    currentStep = step;
    panes.forEach(pane => { pane.hidden = Number(pane.dataset.step) !== step; });
    progress.forEach((item, i) => {
      item.classList.toggle('complete', i + 1 < step);
      if (i + 1 === step) item.setAttribute('aria-current', 'step');
      else item.removeAttribute('aria-current');
    });
    if (focus) {
      const title = document.getElementById(`step-title-${step}`);
      title.setAttribute('tabindex', '-1');
      title.focus();
    }
  }

  function clearErrors() {
    form.querySelectorAll('.field-error').forEach(error => error.remove());
    form.querySelectorAll('[aria-invalid]').forEach(input => {
      input.removeAttribute('aria-invalid');
      input.removeAttribute('aria-errormessage');
    });
    summary.hidden = true;
  }

  function showErrors(errors) {
    summary.replaceChildren();
    const title = document.createElement('p');
    title.textContent = 'A few details need your attention.';
    const list = document.createElement('ul');
    for (const {input, message} of errors) {
      const item = document.createElement('li');
      const link = document.createElement('a');
      link.href = `#${input.id}`;
      link.textContent = message;
      link.addEventListener('click', event => { event.preventDefault(); input.focus(); });
      item.append(link); list.append(item);
      input.setAttribute('aria-invalid', 'true');
      const inline = document.createElement('p');
      inline.className = 'field-error'; inline.id = `${input.id}-error`; inline.textContent = message;
      input.setAttribute('aria-errormessage', inline.id);
      (input.closest('.field') || input.closest('.terms')).append(inline);
    }
    summary.append(title, list); summary.hidden = false; summary.focus();
  }

  const institute = document.getElementById('department-select');
  const course = document.getElementById('course-select');
  if (institute && course) {
    // Rebuild with only the chosen institute's options; never preselect a degree program.
    const groups = [...course.querySelectorAll('optgroup')].map(group => group.cloneNode(true));
    function syncCourses(initial = false) {
      const previous = initial ? course.value : '';
      const other = institute.value === 'Other Institute / Outsider';
      const options = groups.find(group => group.label === institute.value);
      course.replaceChildren(new Option('Select degree program', ''));
      if (options) [...options.children].forEach(option => course.append(option.cloneNode(true)));
      if (other) course.append(new Option('Other degree program', 'Other Degree Program'));
      course.value = previous;
      if (course.selectedIndex < 0) course.selectedIndex = 0;
      course.disabled = !institute.value;
      document.getElementById('course-wrap').hidden = !institute.value;
      document.getElementById('other-institute-wrap').hidden = !other;
      document.getElementById('other_institute').required = other;
    }
    institute.addEventListener('change', () => syncCourses());
    syncCourses(true);
  }

  function updateCredential() {
    if (!isStudent) return;
    const email = document.getElementById('reg-email').value.trim();
    const diploma = (email !== '' && !/@kld\.edu\.ph$/i.test(email)) || document.getElementById('year_level').value === 'Alumnus / Graduated';
    document.getElementById('student-proof-label').textContent = diploma ? 'Official KLD diploma' : 'Certificate of Registration (e-COR)';
    document.getElementById('student-proof-desc').textContent = diploma ? 'Required for alumni and personal email accounts as proof of KLD graduation.' : 'Required to verify your current semester enrollment.';
    document.getElementById('reg-student-proof').required = true;
  }
  document.getElementById('reg-email').addEventListener('input', updateCredential);
  document.getElementById('year_level')?.addEventListener('change', updateCredential);
  updateCredential();

  function validateStep(step) {
    const errors = [];
    for (const input of panes[step - 1].querySelectorAll('input, select')) {
      input.setCustomValidity('');
      if (input.disabled) continue;
      let message = '';
      if (input.required && (input.type === 'checkbox' ? !input.checked : !input.value.trim())) {
        const label = input.id === 'termsCheck' ? 'the terms and privacy policy' : document.querySelector(`label[for="${input.id}"]`).textContent.toLowerCase();
        message = input.id === 'termsCheck' ? `Agree to ${label} to continue.` : `Please provide your ${label}.`;
      } else if (input.id === 'reg-email' && (!input.validity.valid || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim()))) {
        message = 'Enter a valid email address, for example name@domain.com.';
      } else if (input.id === 'reg-email' && !isStudent && /@(?:[a-z0-9-]+\.)*kld\.edu\.ph$/i.test(input.value.trim())) {
        message = 'Use your company email. Career Services provides KLD office accounts.';
      } else if (input.id === 'reg-phone' && (!/^\+?[0-9][0-9 ()-]{5,18}[0-9]$/.test(input.value.trim()) || input.value.replace(/\D/g, '').length < 7 || input.value.replace(/\D/g, '').length > 15)) {
        message = 'Enter a valid phone number with 7–15 digits, e.g. 0917 123 4567 or +63 917 123 4567.';
      } else if (input.id === 'student_id' && !/^[0-9]{4}-[0-9]-[0-9]{6}$/.test(input.value.trim())) {
        message = 'Use Student ID format YYYY-T-XXXXXX, e.g. 2025-2-000065.';
      } else if (input.id === 'reg-birthdate' && (!input.validity.valid || input.value > input.max)) {
        message = 'You must be at least 18 years old. Enter a valid date of birth.';
      } else if (input.id === 'password') {
        if (input.value.length < 8) {
          message = 'Use a password with at least 8 characters.';
        } else {
          updatePasswordStrength();
          if (Number(passwordMeter?.dataset?.strength || 0) < 2) {
            message = 'Please choose a stronger password (include uppercase letters, numbers, or symbols).';
          }
        }
      } else if (input.id === 'confirm_password' && input.value !== document.getElementById('password').value) {
        message = 'Your passwords do not match.';
      } else if (input.type === 'file' && input.files.length) {
        if (input.files[0].size > 5 * 1024 * 1024) message = 'Choose a file no larger than 5 MB.';
        else if (!/\.(pdf|jpe?g|png)$/i.test(input.files[0].name)) message = 'Choose a PDF, JPG or PNG document.';
      } else if (!input.validity.valid) message = 'Check this field before continuing.';
      if (message) errors.push({input, message});
    }
    if (errors.length) {
      displayStep(step, false);
      showErrors(errors);
    }
    return !errors.length;
  }

  async function checkAvailability(input, key, inline = false) {
    const value = input.value.trim();
    const feedback = document.getElementById(key === 'email' ? 'email-duplicate-feedback' : 'student-id-duplicate-feedback');
    const report = message => {
      if (inline) {
        feedback.textContent = message;
        feedback.dataset.state = 'unavailable';
        input.setAttribute('aria-invalid', 'true');
      } else {
        feedback.textContent = '';
        showErrors([{input, message}]);
      }
      return false;
    };
    feedback.textContent = 'Checking availability…';
    feedback.dataset.state = 'checking';
    try {
      const response = await fetch(`api/check-account.php?${new URLSearchParams({[key]: value})}`, {headers: {'Accept': 'application/json'}});
      const result = await response.json();
      if (input.value.trim() !== value) return inline ? false : report('This detail changed during the check. Please continue again.');
      if (!response.ok || result.available === false || result.domain_invalid || result.email_exists || result.student_id_exists) {
        return report(result.message || 'Could not verify this detail. Please try again.');
      }
      if (key === 'student_id' && typeof result.student_id_exists !== 'boolean') throw new Error('Invalid availability response');
      feedback.textContent = key === 'student_id' ? 'This Student ID is available.' : 'Available.';
      feedback.dataset.state = 'available';
      input.removeAttribute('aria-invalid');
      return true;
    } catch (_) {
      if (input.value.trim() !== value) return false;
      if (key === 'student_id') return report('Could not check this Student ID. Please try again before continuing.');
      // The controller repeats availability and DNS checks before creating any account.
      feedback.textContent = 'Availability will be checked when you submit.';
      return true;
    }
  }

  const studentId = document.getElementById('student_id');
  if (studentId) {
    const feedback = document.getElementById('student-id-duplicate-feedback');
    let timer;
    const validFormat = () => /^[0-9]{4}-[0-9]-[0-9]{6}$/.test(studentId.value.trim());
    studentId.addEventListener('input', () => {
      clearTimeout(timer);
      feedback.textContent = '';
      delete feedback.dataset.state;
      studentId.removeAttribute('aria-invalid');
      studentId.removeAttribute('aria-errormessage');
      studentId.closest('.field').querySelector('.field-error')?.remove();
      if (validFormat()) timer = setTimeout(() => checkAvailability(studentId, 'student_id', true), 500);
    });
    studentId.addEventListener('blur', event => {
      clearTimeout(timer);
      if (validFormat() && !event.relatedTarget?.closest('[data-next]')) checkAvailability(studentId, 'student_id', true);
    });
  }

  function setBusy(value) {
    busy = value;
    form.setAttribute('aria-busy', String(value));
    form.querySelectorAll('.step-actions button').forEach(button => { button.disabled = value; });
    const button = panes[currentStep - 1].querySelector('.primary');
    if (value) { button.dataset.label = button.querySelector('span').textContent; button.querySelector('span').textContent = 'Checking…'; }
    else form.querySelectorAll('[data-label]').forEach(button => { button.querySelector('span').textContent = button.dataset.label; delete button.dataset.label; });
  }

  form.querySelectorAll('[data-next]').forEach(button => button.addEventListener('click', async () => {
    if (busy) return;
    clearErrors();
    if (!validateStep(currentStep)) return;
    setBusy(true);
    const checkedStep = currentStep;
    try {
      const available = currentStep === 1
        ? await checkAvailability(document.getElementById('reg-email'), 'email')
        : !isStudent || await checkAvailability(document.getElementById('student_id'), 'student_id');
      if (available && validateStep(checkedStep)) { clearErrors(); displayStep(checkedStep + 1); updateCredential(); }
    } finally { setBusy(false); }
  }));
  form.querySelectorAll('[data-back]').forEach(button => button.addEventListener('click', () => {
    if (!busy) { clearErrors(); displayStep(currentStep - 1); }
  }));
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (busy) return;
    if (currentStep < 3) { panes[currentStep - 1].querySelector('[data-next]').click(); return; }
    clearErrors();
    for (let step = 1; step <= 3; step++) if (!validateStep(step)) return;
    setBusy(true);
    document.getElementById('btn-submit-registration').querySelector('span').textContent = 'Submitting…';
    HTMLFormElement.prototype.submit.call(form);
  });
  displayStep(currentStep, false);
})();
