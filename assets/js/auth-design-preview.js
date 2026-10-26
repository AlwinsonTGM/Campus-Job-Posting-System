/* Loaded only by the isolated local demo, never by a real authentication page. */
(() => {
  const next = (screen, play = false) => {
    const url = new URL(location.href);
    url.searchParams.set('screen', screen);
    url.searchParams.set('role', document.getElementById('preview-role').value);
    url.searchParams.delete('play');
    if (play) url.searchParams.set('play', '1');
    return url.href;
  };
  const realFetch = window.fetch.bind(window);
  window.fetch = async (input, options) => {
    const url = new URL(typeof input === 'string' ? input : input.url, location.href);
    if (url.origin === location.origin && url.pathname.endsWith('/verify-email.php')) {
      await new Promise(resolve => setTimeout(resolve, 450));
      return new Response(JSON.stringify({success: true, message: 'Email confirmed. Your registration is ready for administrator review.', redirect: next('pending')}), {headers: {'Content-Type': 'application/json'}});
    }
    return realFetch(input, options);
  };
  document.addEventListener('DOMContentLoaded', () => {
    const play = () => {
      const inputs = [...document.querySelectorAll('.otp-digit-pod')];
      if (!inputs.length || document.getElementById('otp-form-view').style.display === 'none') {
        location.href = next('verify', true);
        return;
      }
      inputs.forEach((input, index) => { input.value = String(index + 1); });
      inputs.at(-1).dispatchEvent(new Event('input', {bubbles: true}));
    };
    document.getElementById('preview-play').addEventListener('click', play);
    for (const id of ['preview-role', 'preview-screen']) {
      document.getElementById(id).addEventListener('change', () => { location.href = next(document.getElementById('preview-screen').value); });
    }
    const form = document.getElementById('otp-form');
    if (form) {
      form.noValidate = true;
      form.addEventListener('submit', () => {
        document.querySelectorAll('.otp-digit-pod').forEach((input, index) => { if (!input.value) input.value = String(index + 1); });
      }, true);
    }
    document.getElementById('resend-form')?.addEventListener('submit', event => {
      event.preventDefault();
      document.getElementById('resend-text').textContent = 'Preview code ready — enter any 6 digits';
    });
    const resubmit = document.querySelector('.review-resubmit');
    if (resubmit) {
      resubmit.noValidate = true;
      resubmit.addEventListener('submit', event => { event.preventDefault(); location.href = next('pending'); });
    }
    const cancel = document.querySelector('.verification-actions>a');
    if (cancel) cancel.href = next('verify');
    if (new URL(location.href).searchParams.get('play') === '1') setTimeout(play, 350);
  });
})();
