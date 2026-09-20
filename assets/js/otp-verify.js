'use strict';

/**
 * Campus Job Posting System - Institutional 6-Digit OTP Pods & Success Morph
 */
document.addEventListener('DOMContentLoaded', () => {
    const inputs = Array.from(document.querySelectorAll('.otp-digit-pod'));
    const hiddenCode = document.getElementById('otp-code-hidden');
    const form = document.getElementById('otp-form');
    const formView = document.getElementById('otp-form-view');
    const successView = document.getElementById('otp-success-view');
    const successMsg = document.getElementById('otp-success-msg');
    const errorAlert = document.getElementById('otp-error-alert');
    const errorText = document.getElementById('otp-error-text');
    const inputsWrapper = document.getElementById('otp-inputs-wrapper');
    const resendBtn = document.getElementById('btn-resend-otp');
    const resendText = document.getElementById('resend-text');
    const submitBtn = document.getElementById('btn-submit-otp');

    let isSubmitting = false;

    const showError = (msg) => {
        if (errorAlert && errorText) {
            errorText.textContent = msg;
            errorAlert.classList.remove('d-none');
        }
    };

    const hideError = () => {
        if (errorAlert) {
            errorAlert.classList.add('d-none');
        }
    };

    const syncHiddenCode = () => {
        const full = inputs.map(input => input.value).join('');
        if (hiddenCode) {
            hiddenCode.value = full;
        }
        return full;
    };

    const triggerHapticError = (msg) => {
        showError(msg);

        inputs.forEach(input => {
            input.classList.remove('filled', 'pod-success');
            input.classList.add('pod-error');
        });

        if (inputsWrapper) {
            inputsWrapper.classList.remove('otp-shake');
            void inputsWrapper.offsetWidth; // Force reflow
            inputsWrapper.classList.add('otp-shake');
        }

        setTimeout(() => {
            inputs.forEach(input => {
                input.value = '';
                input.classList.remove('pod-error');
            });
            syncHiddenCode();
            if (inputs.length > 0) {
                inputs[0].focus();
            }
        }, 600);
    };

    const triggerSuccessMorph = (redirectUrl, message) => {
        hideError();

        // 1. Cascading pulse across the 6 pods
        inputs.forEach((input, i) => {
            setTimeout(() => {
                input.classList.remove('pod-error');
                input.classList.add('pod-success');
            }, i * 45);
        });

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> VERIFIED';
        }

        // 2. Morph view after 300ms
        setTimeout(() => {
            if (formView && successView) {
                formView.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
                formView.style.opacity = '0';
                formView.style.transform = 'scale(0.96)';

                setTimeout(() => {
                    formView.style.display = 'none';
                    if (message && successMsg) {
                        successMsg.textContent = message;
                    }
                    successView.style.display = 'flex';

                    // 3. Smooth redirect after 1.2s
                    setTimeout(() => {
                        window.location.href = redirectUrl || 'index.php';
                    }, 1200);
                }, 250);
            } else {
                window.location.href = redirectUrl || 'index.php';
            }
        }, 320);
    };

    const submitOtpCode = async () => {
        const code = syncHiddenCode();
        if (code.length !== 6) {
            showError('Please enter all 6 digits of your verification code.');
            return;
        }

        if (isSubmitting) {
            return;
        }

        isSubmitting = true;
        hideError();

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Verifying...';
        }

        try {
            const formData = new FormData();
            formData.append('action', 'verify');
            formData.append('otp_code', code);

            const response = await fetch('verify-email.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                triggerSuccessMorph(data.redirect, data.message);
            } else {
                isSubmitting = false;
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> VERIFY EMAIL & CONTINUE';
                }
                triggerHapticError(data.message || 'Invalid verification code.');
            }
        } catch {
            // If fetch/JSON fails, fall back gracefully to standard form submit
            if (form) {
                form.submit();
            }
        }
    };

    // Auto-focus first input on load
    if (inputs.length > 0) {
        inputs[0].focus();
    }

    inputs.forEach((input, index) => {
        input.addEventListener('input', () => {
            const val = input.value.replace(/\D/g, '');
            input.value = val ? val.charAt(val.length - 1) : '';

            if (input.value) {
                input.classList.add('filled');
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            } else {
                input.classList.remove('filled');
            }

            const code = syncHiddenCode();
            if (code.length === 6) {
                submitOtpCode();
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace') {
                if (!input.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                    inputs[index - 1].classList.remove('filled');
                    syncHiddenCode();
                    event.preventDefault();
                } else if (input.value) {
                    input.value = '';
                    input.classList.remove('filled');
                    syncHiddenCode();
                    event.preventDefault();
                }
            } else if (event.key === 'ArrowLeft' && index > 0) {
                inputs[index - 1].focus();
            } else if (event.key === 'ArrowRight' && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        input.addEventListener('paste', (event) => {
            event.preventDefault();
            const clipboard = event.clipboardData || window.clipboardData;
            const pasteText = clipboard ? clipboard.getData('text') : '';
            const digits = pasteText.replace(/\D/g, '').slice(0, 6);

            if (!digits) {
                return;
            }

            digits.split('').forEach((char, i) => {
                if (inputs[i]) {
                    inputs[i].value = char;
                    inputs[i].classList.add('filled');
                }
            });

            syncHiddenCode();

            if (digits.length === 6) {
                submitOtpCode();
            } else {
                const nextFocus = Math.min(digits.length, inputs.length - 1);
                inputs[nextFocus].focus();
            }
        });
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            submitOtpCode();
        });
    }

    // Live 60s Resend Cooldown Countdown
    if (resendBtn && resendText) {
        const rawSeconds = resendBtn.getAttribute('data-remaining');
        let remaining = rawSeconds ? parseInt(rawSeconds, 10) : 0;
        if (remaining > 0) {
            const timerId = setInterval(() => {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(timerId);
                    resendBtn.removeAttribute('disabled');
                    resendBtn.classList.remove('text-muted-custom');
                    resendBtn.classList.add('text-ink');
                    resendText.textContent = 'Resend Verification Code';
                } else {
                    resendText.textContent = `Resend code in ${remaining}s`;
                }
            }, 1000);
        }
    }
});
