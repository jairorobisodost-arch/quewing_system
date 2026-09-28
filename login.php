<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SFI Queuing System - Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    min-height: 100vh;
    display: flex;
    background: #f0fdf4;
  }

  /* LEFT PANEL */
  .left-panel {
    flex: 1;
    background: linear-gradient(160deg, #0a4a28 0%, #166534 50%, #16a34a 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 48px;
    position: relative;
    overflow: hidden;
  }
  .left-panel::before {
    content: '';
    position: absolute;
    width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(255,255,255,0.07) 0%, transparent 70%);
    top: -100px; right: -100px;
  }
  .left-panel::after {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
    bottom: -50px; left: -50px;
  }
  .brand-logo {
    width: 72px; height: 72px;
    background: rgba(255,255,255,0.15);
    border-radius: 20px;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; color: #fff;
    margin-bottom: 24px;
    border: 2px solid rgba(255,255,255,0.2);
    position: relative; z-index: 1;
  }
  .left-panel h1 {
    font-size: 2rem;
    font-weight: 900;
    color: #fff;
    text-align: center;
    line-height: 1.2;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
    position: relative; z-index: 1;
  }
  .left-panel p {
    color: rgba(255,255,255,0.7);
    text-align: center;
    font-size: 0.92rem;
    line-height: 1.7;
    max-width: 300px;
    position: relative; z-index: 1;
    margin-bottom: 40px;
  }
  .feature-list {
    list-style: none;
    position: relative; z-index: 1;
    width: 100%;
    max-width: 300px;
  }
  .feature-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255,255,255,0.85);
    font-size: 0.88rem;
    font-weight: 500;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .feature-list li:last-child { border: none; }
  .feature-list li i {
    width: 28px; height: 28px;
    background: rgba(255,255,255,0.12);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
  }

  /* RIGHT PANEL */
  .right-panel {
    width: 480px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 40px;
    background: #fff;
    box-shadow: -8px 0 40px rgba(0,0,0,0.06);
  }
  .login-box { width: 100%; max-width: 360px; }
  .login-box .welcome {
    font-size: 0.8rem;
    font-weight: 700;
    color: #16a34a;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
  }
  .login-box h2 {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0a4a28;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
  }
  .login-box .sub {
    color: #94a3b8;
    font-size: 0.88rem;
    margin-bottom: 36px;
  }
  .form-group { margin-bottom: 20px; }
  .form-group label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 7px;
    display: block;
  }
  .input-wrap {
    position: relative;
  }
  .input-wrap i {
    position: absolute;
    left: 14px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.95rem;
  }
  .input-wrap input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px 12px 40px;
    font-size: 0.95rem;
    font-family: 'Inter', sans-serif;
    color: #1e293b;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
  }
  .input-wrap input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
  }
  .input-wrap .toggle-pw {
    position: absolute;
    right: 14px; top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    cursor: pointer;
    font-size: 0.95rem;
    left: auto;
  }
  .btn-login {
    width: 100%;
    background: linear-gradient(135deg, #16a34a, #0a4a28);
    color: #fff;
    border: none;
    border-radius: 12px;
    padding: 14px;
    font-size: 0.97rem;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    margin-top: 8px;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(22,163,74,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .btn-login:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(22,163,74,0.4);
  }
  .btn-login:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
  .error-box {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    color: #dc2626;
    margin-bottom: 18px;
    display: none;
    align-items: center;
    gap: 8px;
  }
  .error-box.show { display: flex; }
  .footer-note {
    text-align: center;
    margin-top: 28px;
    font-size: 0.78rem;
    color: #cbd5e1;
  }

  /* RESPONSIVE */
  @media (max-width: 768px) {
    .left-panel { display: none; }
    .right-panel { width: 100%; box-shadow: none; }
    body { background: #fff; }
  }
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="brand-logo">
    <i class="bi bi-ticket-perforated-fill"></i>
  </div>
  <h1>SFI Queuing System</h1>
  <p>A complete digital queue management solution for efficient customer service.</p>
  <ul class="feature-list">
    <li><i class="bi bi-display"></i> Real-Time Queue Display</li>
    <li><i class="bi bi-people"></i> Multi-Counter Support</li>
    <li><i class="bi bi-star"></i> Priority Lane Management</li>
    <li><i class="bi bi-bar-chart-line"></i> Reports & Analytics</li>
  </ul>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <div class="login-box">
    <div class="welcome">Welcome back</div>
    <h2>Staff Login</h2>
    <p class="sub">Enter your credentials to access the system</p>

    <div class="error-box" id="loginError">
      <i class="bi bi-exclamation-circle-fill"></i>
      <span id="errorMsg">Invalid credentials</span>
    </div>

    <?php if (isset($_GET['expired'])): ?>
    <div class="error-box show" style="background:#fffbeb;border-color:#fde68a;color:#b45309;">
      <i class="bi bi-clock-history"></i>
      <span>Your session has expired. Please login again.</span>
    </div>
    <?php endif; ?>

    <form id="loginForm">
      <div class="form-group">
        <label>Username</label>
        <div class="input-wrap">
          <i class="bi bi-person"></i>
          <input type="text" id="username" placeholder="Enter your username" required autocomplete="username">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <i class="bi bi-lock"></i>
          <input type="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
          <i class="bi bi-eye toggle-pw" id="togglePw"></i>
        </div>
      </div>
      <button type="submit" class="btn-login" id="loginBtn">
        <i class="bi bi-box-arrow-in-right"></i> Login
      </button>
    </form>

    <div class="footer-note">&copy; <?= date('Y') ?> SFI Queuing System</div>
  </div>
</div>

<script>
  // Toggle password visibility
  document.getElementById('togglePw').addEventListener('click', function() {
    const pw = document.getElementById('password');
    const isText = pw.type === 'text';
    pw.type = isText ? 'password' : 'text';
    this.className = isText ? 'bi bi-eye toggle-pw' : 'bi bi-eye-slash toggle-pw';
  });

  // Login form submit
  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errBox = document.getElementById('loginError');
    const errMsg = document.getElementById('errorMsg');
    const btn    = document.getElementById('loginBtn');
    errBox.classList.remove('show');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Logging in...';

    try {
      const res = await fetch('/quewing_system/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          username: document.getElementById('username').value,
          password: document.getElementById('password').value
        })
      });
      const result = await res.json();
      if (res.ok) {
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Redirecting...';
        window.location.href = result.redirect;
      } else {
        errMsg.textContent = result.error || 'Invalid credentials';
        errBox.classList.add('show');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
      }
    } catch(e) {
      errMsg.textContent = 'Connection error. Please try again.';
      errBox.classList.add('show');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Login';
    }
  });
</script>
</body>
</html>
