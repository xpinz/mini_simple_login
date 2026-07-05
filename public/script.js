/* ─────────────────────────────────────────────
   script.js – Login Form Logic
   - POST /login with fetch
   - Alternating error color: odd=black, even=red
   - Redirect to /welcome on success
───────────────────────────────────────────── */

(function () {
  const form        = document.getElementById('loginForm');
  const statusMsg   = document.getElementById('statusMsg');
  const loginBtn    = document.getElementById('loginBtn');
  const btnText     = document.getElementById('btnText');
  const btnSpinner  = document.getElementById('btnSpinner');
  const toggleBtn   = document.getElementById('togglePassword');
  const passwordInput = document.getElementById('password');
  const eyeIcon     = document.getElementById('eyeIcon');

  // Track failed login attempts for alternating color
  let failCount = 0;

  // ── Toggle password visibility ──
  toggleBtn.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';

    // Swap icon
    eyeIcon.innerHTML = isPassword
      ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20C5 20 1 12 1 12a18.44 18.44 0 0 1 5.06-5.94"/>
         <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.4 18.4 0 0 1-2.16 3.19"/>
         <line x1="1" y1="1" x2="23" y2="23"/>`
      : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
         <circle cx="12" cy="12" r="3"/>`;
  });

  // ── Show status message ──
  function showStatus(message, type) {
    statusMsg.textContent = message;
    // Remove all state classes
    statusMsg.classList.remove('error-odd', 'error-even', 'success', 'visible');

    // Small reflow trick to re-trigger transition
    void statusMsg.offsetWidth;

    statusMsg.classList.add(type, 'visible');
  }

  // ── Set loading state ──
  function setLoading(loading) {
    loginBtn.disabled = loading;
    btnText.textContent = loading ? 'Signing in…' : 'Login';
    btnSpinner.classList.toggle('hidden', !loading);
  }

  // ── Form submit ──
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = passwordInput.value;

    // Client-side empty check
    if (!username || !password) {
      showStatus('Please enter both username and password.', failCount % 2 === 0 ? 'error-even' : 'error-odd');
      return;
    }

    setLoading(true);

    try {
      const response = await fetch('/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });

      const data = await response.json();

      if (data.success) {
        // Reset fail count on success
        failCount = 0;
        showStatus(`✓ ${data.message}`, 'success');

        // Brief delay then redirect to welcome page
        setTimeout(() => {
          window.location.href = `/welcome?user=${encodeURIComponent(data.username)}`;
        }, 900);
      } else {
        // Increment fail counter and alternate color
        failCount++;
        // Odd failCount → black (error-odd), Even → red (error-even)
        const colorClass = failCount % 2 !== 0 ? 'error-odd' : 'error-even';
        showStatus('Incorrect username or password', colorClass);
        setLoading(false);
      }
    } catch (err) {
      failCount++;
      const colorClass = failCount % 2 !== 0 ? 'error-odd' : 'error-even';
      showStatus('Network error. Please try again.', colorClass);
      setLoading(false);
    }
  });

  // ── Clear message when user starts typing ──
  ['username', 'password'].forEach((id) => {
    document.getElementById(id).addEventListener('input', () => {
      if (statusMsg.classList.contains('visible') &&
          !statusMsg.classList.contains('success')) {
        statusMsg.classList.remove('visible');
      }
    });
  });
})();
