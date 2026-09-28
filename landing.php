<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SFI Queuing System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --green-dark:   #0a4a28;
    --green-mid:    #166534;
    --green-main:   #16a34a;
    --green-light:  #22c55e;
    --green-pale:   #dcfce7;
    --white:        #ffffff;
    --gray-50:      #f8fafc;
    --gray-100:     #f1f5f9;
    --gray-400:     #94a3b8;
    --gray-600:     #475569;
    --gray-800:     #1e293b;
  }

  html { scroll-behavior: smooth; }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: var(--white);
    color: var(--gray-800);
    overflow-x: hidden;
  }

  /* ── NAVBAR ── */
  .navbar-top {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(22,163,74,0.12);
    padding: 0 48px;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: box-shadow 0.3s;
  }
  .navbar-top.scrolled { box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
  .nav-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
  }
  .nav-logo {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, var(--green-main), var(--green-dark));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(22,163,74,0.35);
  }
  .nav-name {
    font-weight: 800;
    font-size: 1rem;
    color: var(--green-dark);
    letter-spacing: 0.3px;
  }
  .nav-name span { color: var(--green-main); }

  /* ── HERO ── */
  .hero-section {
    min-height: 100vh;
    background: linear-gradient(160deg, #f0fdf4 0%, #dcfce7 40%, #bbf7d0 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 120px 24px 80px;
    position: relative;
    overflow: hidden;
  }
  .hero-section::before {
    content: '';
    position: absolute;
    width: 700px; height: 700px;
    background: radial-gradient(circle, rgba(34,197,94,0.18) 0%, transparent 70%);
    top: -100px; left: 50%;
    transform: translateX(-50%);
    pointer-events: none;
  }
  .hero-section::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(22,163,74,0.12) 0%, transparent 70%);
    bottom: 0; right: 10%;
    pointer-events: none;
  }
  .hero-tag {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: var(--white);
    border: 1px solid rgba(22,163,74,0.3);
    border-radius: 50px;
    padding: 6px 16px;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--green-main);
    text-transform: uppercase;
    letter-spacing: 1.2px;
    margin-bottom: 28px;
    box-shadow: 0 2px 12px rgba(22,163,74,0.12);
  }
  .hero-tag .dot {
    width: 7px; height: 7px;
    background: var(--green-light);
    border-radius: 50%;
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%,100% { transform: scale(1); opacity:1; }
    50% { transform: scale(1.4); opacity:0.7; }
  }

  /* ── ENTRANCE / SCROLL ANIMATIONS ── */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(28px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes navDrop {
    from { transform: translateY(-100%); }
    to   { transform: translateY(0); }
  }
  @keyframes floatY {
    0%,100% { transform: translateY(0); }
    50%     { transform: translateY(-14px); }
  }
  @keyframes drift {
    0%,100% { transform: translate(0,0) scale(1); }
    33%     { transform: translate(36px,-28px) scale(1.08); }
    66%     { transform: translate(-28px,22px) scale(0.94); }
  }
  @keyframes shimmer {
    0%   { background-position: 0% 50%; }
    50%  { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  @keyframes popIn {
    0%   { opacity: 0; transform: scale(.5); }
    70%  { opacity: 1; transform: scale(1.08); }
    100% { opacity: 1; transform: scale(1); }
  }
  @keyframes nudge {
    0%,100% { transform: translateX(0); }
    50%     { transform: translateX(6px); }
  }

  .navbar-top { animation: navDrop .6s cubic-bezier(.22,1,.36,1) both; }

  .hero-anim { opacity: 0; animation: fadeUp .8s cubic-bezier(.22,1,.36,1) forwards; }
  .ha-1 { animation-delay: .10s; }
  .ha-2 { animation-delay: .22s; }
  .ha-3 { animation-delay: .36s; }
  .ha-4 { animation-delay: .50s; }
  .ha-5 { animation-delay: .66s; }

  .hero-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(70px);
    opacity: .55;
    pointer-events: none;
    z-index: 0;
    animation: drift 16s ease-in-out infinite;
  }
  .orb-1 { width: 380px; height: 380px; background: rgba(34,197,94,.35); top: -60px; left: -120px; }
  .orb-2 { width: 300px; height: 300px; background: rgba(22,163,74,.28); bottom: -80px; right: -60px; animation-duration: 20s; animation-delay: -5s; }
  .orb-3 { width: 180px; height: 180px; background: rgba(220,252,231,.9); top: 30%; right: 18%; animation-duration: 14s; animation-delay: -9s; }

  .hero-chip { animation: floatY 5.5s ease-in-out infinite; }
  .hero-chip:nth-child(2) { animation-delay: .7s; }
  .hero-chip:nth-child(3) { animation-delay: 1.4s; }

  #getStartedBtn i { animation: nudge 1.6s ease-in-out infinite; }

  /* scroll reveal */
  .reveal {
    opacity: 0;
    transform: translateY(34px);
    transition: opacity .7s ease, transform .7s cubic-bezier(.22,1,.36,1);
  }
  .reveal.visible { opacity: 1; transform: none; }
  .rv-1 { transition-delay: .08s; }
  .rv-2 { transition-delay: .18s; }
  .rv-3 { transition-delay: .28s; }
  .rv-4 { transition-delay: .38s; }

  .hiw-step.visible .hiw-num { animation: popIn .55s cubic-bezier(.34,1.56,.64,1) .15s both; }

  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; }
    .hero-anim, .reveal { opacity: 1 !important; transform: none !important; }
  }
  .hero-title {
    font-size: clamp(2.4rem, 6vw, 4.2rem);
    font-weight: 900;
    line-height: 1.1;
    color: var(--green-dark);
    margin-bottom: 24px;
    letter-spacing: -1px;
  }
  .hero-title .highlight {
    background: linear-gradient(135deg, var(--green-main), var(--green-light), var(--green-main));
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmer 5s ease infinite;
  }
  .hero-sub {
    font-size: 1.15rem;
    color: var(--gray-600);
    max-width: 520px;
    margin: 0 auto 48px;
    line-height: 1.75;
    font-weight: 400;
  }

  /* ── STATS ── */
  .stats-section {
    background: var(--white);
    padding: 60px 24px;
    border-bottom: 1px solid var(--gray-100);
  }
  .stats-grid {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
  }
  .stat-box {
    text-align: center;
    padding: 28px 20px;
    border-right: 1px solid var(--gray-100);
    position: relative;
  }
  .stat-box:last-child { border-right: none; }
  .stat-box::before {
    content: '';
    position: absolute;
    bottom: 0; left: 50%;
    transform: translateX(-50%);
    width: 40px; height: 3px;
    background: linear-gradient(90deg, var(--green-main), var(--green-light));
    border-radius: 2px;
    opacity: 0;
    transition: opacity 0.3s;
  }
  .stat-box:hover::before { opacity: 1; }
  .stat-num {
    font-size: 2.2rem;
    font-weight: 900;
    color: var(--green-main);
    line-height: 1;
    margin-bottom: 8px;
  }
  .stat-lbl {
    font-size: 0.78rem;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
  }

  /* ── FEATURES ── */
  .features-section {
    background: var(--gray-50);
    padding: 100px 24px;
  }
  .section-eyebrow {
    text-align: center;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--green-main);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 14px;
  }
  .section-heading {
    text-align: center;
    font-size: clamp(1.6rem, 3vw, 2.2rem);
    font-weight: 800;
    color: var(--green-dark);
    margin-bottom: 12px;
    letter-spacing: -0.5px;
  }
  .section-desc {
    text-align: center;
    color: var(--gray-400);
    font-size: 0.97rem;
    margin-bottom: 56px;
  }
  .feat-card {
    background: var(--white);
    border: 1px solid var(--gray-100);
    border-radius: 20px;
    padding: 36px 30px;
    height: 100%;
    transition: all 0.3s;
    position: relative;
    overflow: hidden;
  }
  .feat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--green-main), var(--green-light));
    transform: scaleX(0);
    transition: transform 0.3s;
    transform-origin: left;
  }
  .feat-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 50px rgba(22,163,74,0.12);
    border-color: rgba(22,163,74,0.2);
  }
  .feat-card:hover::before { transform: scaleX(1); }
  .feat-icon {
    width: 56px; height: 56px;
    background: var(--green-pale);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
    color: var(--green-main);
    margin-bottom: 20px;
    transition: background 0.3s;
  }
  .feat-card:hover .feat-icon {
    background: linear-gradient(135deg, var(--green-main), var(--green-light));
    color: #fff;
  }
  .feat-card h5 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--green-dark);
    margin-bottom: 10px;
  }
  .feat-card p {
    font-size: 0.88rem;
    color: var(--gray-400);
    line-height: 1.65;
    margin: 0;
  }

  /* ── HOW IT WORKS ── */
  .hiw-section {
    background: var(--white);
    padding: 100px 24px;
  }
  .hiw-steps {
    max-width: 900px;
    margin: 56px auto 0;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    position: relative;
  }
  .hiw-steps::before {
    content: '';
    position: absolute;
    top: 28px; left: 12.5%; right: 12.5%;
    height: 2px;
    background: linear-gradient(90deg, var(--green-main), var(--green-light));
    z-index: 0;
  }
  .hiw-step {
    text-align: center;
    padding: 0 16px;
    position: relative;
    z-index: 1;
  }
  .hiw-num {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--green-main), var(--green-light));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800;
    font-size: 1.1rem;
    color: #fff;
    margin: 0 auto 20px;
    box-shadow: 0 6px 20px rgba(22,163,74,0.35);
    border: 4px solid #fff;
  }
  .hiw-step h6 {
    font-weight: 700;
    font-size: 0.95rem;
    color: var(--green-dark);
    margin-bottom: 8px;
  }
  .hiw-step p {
    font-size: 0.82rem;
    color: var(--gray-400);
    line-height: 1.55;
  }

  /* ── CTA ── */
  .cta-section {
    background: linear-gradient(135deg, var(--green-dark) 0%, var(--green-mid) 50%, var(--green-main) 100%);
    padding: 100px 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
  }
  .cta-section::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    top: -200px; left: -100px;
    pointer-events: none;
  }
  .cta-section::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
    bottom: -150px; right: 5%;
    pointer-events: none;
  }
  .cta-section h2 {
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 900;
    color: #fff;
    margin-bottom: 16px;
    letter-spacing: -0.5px;
    position: relative; z-index: 1;
  }
  .cta-section p {
    color: rgba(255,255,255,0.75);
    font-size: 1rem;
    margin-bottom: 0;
    position: relative; z-index: 1;
  }

  /* ── FOOTER ── */
  footer {
    background: var(--green-dark);
    padding: 28px 24px;
    text-align: center;
    border-top: 1px solid rgba(255,255,255,0.06);
  }
  footer p {
    color: rgba(255,255,255,0.4);
    font-size: 0.82rem;
    margin: 0;
  }
  footer span { color: rgba(255,255,255,0.6); }

  /* ── RESPONSIVE ── */
  @media (max-width: 768px) {
    .navbar-top { padding: 0 20px; }
    .stats-grid { grid-template-columns: repeat(2,1fr); }
    .stat-box:nth-child(2) { border-right: none; }
    .stat-box:nth-child(1),
    .stat-box:nth-child(2) { border-bottom: 1px solid var(--gray-100); }
    .hiw-steps { grid-template-columns: repeat(2,1fr); gap: 32px; }
    .hiw-steps::before { display: none; }
  }
  @media (max-width: 480px) {
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .hiw-steps { grid-template-columns: 1fr 1fr; }
  }

  /* ── CUSTOM SEARCHABLE DROPDOWN ── */
  .dropdown-search-wrap { position: relative; }
  .dropdown-input-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1.5px solid var(--gray-100);
    border-radius: 10px;
    padding: 11px 16px;
    cursor: pointer;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    user-select: none;
  }
  .dropdown-input-box:hover,
  .dropdown-input-box:focus { border-color: var(--green-main); outline: none; }
  .dropdown-input-box.open {
    border-color: var(--green-main);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
    border-radius: 10px 10px 0 0;
  }
  .selected-text { font-size: 0.95rem; color: #94a3b8; }
  .selected-text.picked { color: #1e293b; font-weight: 500; }
  .dropdown-arrow { color: #94a3b8; font-size: 0.8rem; transition: transform 0.2s; }
  .dropdown-input-box.open .dropdown-arrow { transform: rotate(180deg); color: var(--green-main); }
  .dropdown-panel {
    display: none;
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: #fff;
    border: 1.5px solid var(--green-main);
    border-top: none;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
    z-index: 1060;
  }
  .dropdown-panel.show { display: block; }
  .search-box-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-bottom: 1px solid var(--gray-100);
  }
  .search-icon { color: #94a3b8; font-size: 0.85rem; }
  .search-input {
    border: none;
    outline: none;
    font-size: 0.88rem;
    width: 100%;
    color: #1e293b;
  }
  .dropdown-list {
    list-style: none;
    max-height: 180px;
    overflow-y: auto;
    margin: 0; padding: 6px 0;
  }
  .dropdown-list li {
    padding: 10px 16px;
    font-size: 0.9rem;
    color: #1e293b;
    cursor: pointer;
    transition: background 0.15s;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .dropdown-list li:hover { background: #f0fdf4; color: var(--green-main); }
  .dropdown-list li.active { background: var(--green-pale); color: var(--green-dark); font-weight: 600; }
  .no-result { padding: 12px 16px; font-size: 0.85rem; color: #94a3b8; text-align: center; }

  /* ── LOGIN MODAL ── */
  .modal-content {
    border-radius: 20px;
    border: none;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    overflow: hidden;
  }
  .modal-header {
    background: linear-gradient(135deg, var(--green-dark), var(--green-main));
    color: #fff;
    border: none;
    padding: 24px 28px;
  }
  .modal-header .btn-close { filter: invert(1) brightness(2); }
  .modal-body { padding: 32px 28px 24px; }
  .modal-body .form-control,
  .modal-body .form-select {
    border-radius: 10px;
    border: 1.5px solid var(--gray-100);
    padding: 12px 16px;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .modal-body .form-control:focus,
  .modal-body .form-select:focus {
    border-color: var(--green-main);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.15);
  }
  .btn-login-submit {
    background: linear-gradient(135deg, var(--green-main), var(--green-dark));
    border: none;
    border-radius: 12px;
    padding: 13px;
    font-weight: 700;
    font-size: 0.97rem;
    color: #fff;
    width: 100%;
    transition: all 0.2s;
    box-shadow: 0 4px 16px rgba(22,163,74,0.3);
  }
  .btn-login-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(22,163,74,0.4);
  }

  .btn-print-ticket {
    background: #fff;
    color: var(--green-main);
    border: 2px solid var(--green-main);
    border-radius: 12px;
    padding: 12px 32px;
    font-weight: 700;
    cursor: pointer;
    font-size: 0.95rem;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .btn-print-ticket:hover {
    background: var(--green-main);
    color: #fff;
  }
  .btn-row-2 {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .btn-row-2 .btn-done {
    background: linear-gradient(135deg,#16a34a,#0a4a28);
    color:#fff;
    border:none;
    border-radius:12px;
    padding:12px 32px;
    font-weight:700;
    cursor:pointer;
    font-size:0.95rem;
    display:inline-flex;
    align-items:center;
    gap:8px;
  }

  /* Print-only ticket receipt */
  .print-ticket {
    display: none;
  }
  @media print {
    body * { visibility: hidden; }
    .print-ticket, .print-ticket * { visibility: visible; }
    .print-ticket {
      display: block;
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      padding: 16px;
      font-family: 'Courier New', monospace;
      color: #111;
    }
    .print-ticket .pt-brand {
      font-size: 14px;
      font-weight: 800;
      text-transform: uppercase;
      text-align: center;
      letter-spacing: 1px;
    }
    .print-ticket .pt-sub {
      font-size: 10px;
      text-align: center;
      color: #333;
      margin-top: 2px;
    }
    .print-ticket .pt-date {
      font-size: 10px;
      text-align: center;
      color: #444;
      margin: 6px 0;
    }
    .print-ticket .pt-divider {
      border-top: 1px dashed #999;
      margin: 8px 0;
    }
    .print-ticket .pt-num {
      font-size: 30px;
      font-weight: 900;
      text-align: center;
      letter-spacing: 1px;
      margin: 10px 0;
    }
    .print-ticket .pt-row {
      display: flex;
      justify-content: space-between;
      font-size: 11px;
      padding: 2px 0;
    }
    .print-ticket .pt-row .pt-k { color: #555; }
    .print-ticket .pt-row .pt-v { font-weight: 700; }
    .print-ticket .pt-foot {
      font-size: 9px;
      text-align: center;
      color: #555;
      margin-top: 10px;
    }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar-top" id="navbar">
  <a class="nav-brand" href="#" id="secretBrand">
    <div class="nav-logo"><i class="bi bi-ticket-perforated-fill"></i></div>
    <div class="nav-name">SFI <span>Queuing</span></div>
  </a>
  <div style="font-size:0.82rem; color:var(--gray-400); font-weight:500;">
    Queue Management System
  </div>
</nav>

<!-- HERO -->
<section class="hero-section">
  <div class="hero-orb orb-1"></div>
  <div class="hero-orb orb-2"></div>
  <div class="hero-orb orb-3"></div>
  <div style="position:relative; z-index:1;">
    <h1 class="hero-title hero-anim ha-1">
      Manage Queues<br>
      <span class="highlight">Smarter & Faster</span>
    </h1>
    <p class="hero-sub hero-anim ha-2">
      A complete digital queuing solution for your organization. Real-time updates, multi-counter support, and priority handling built for efficiency.
    </p>
    <div class="hero-anim ha-3" style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-bottom: 36px;">
      <div class="hero-chip" style="background:var(--white); border-radius:16px; padding:14px 28px; box-shadow:0 8px 30px rgba(22,163,74,0.15); display:flex; align-items:center; gap:10px; border:1px solid rgba(22,163,74,0.15);">
        <i class="bi bi-check-circle-fill" style="color:var(--green-main); font-size:1.1rem;"></i>
        <span style="font-weight:600; font-size:0.9rem; color:var(--green-dark);">Real-Time Updates</span>
      </div>
      <div class="hero-chip" style="background:var(--white); border-radius:16px; padding:14px 28px; box-shadow:0 8px 30px rgba(22,163,74,0.15); display:flex; align-items:center; gap:10px; border:1px solid rgba(22,163,74,0.15);">
        <i class="bi bi-check-circle-fill" style="color:var(--green-main); font-size:1.1rem;"></i>
        <span style="font-weight:600; font-size:0.9rem; color:var(--green-dark);">Priority Lanes</span>
      </div>
      <div class="hero-chip" style="background:var(--white); border-radius:16px; padding:14px 28px; box-shadow:0 8px 30px rgba(22,163,74,0.15); display:flex; align-items:center; gap:10px; border:1px solid rgba(22,163,74,0.15);">
        <i class="bi bi-check-circle-fill" style="color:var(--green-main); font-size:1.1rem;"></i>
        <span style="font-weight:600; font-size:0.9rem; color:var(--green-dark);">Multi-Counter</span>
      </div>
    </div>
    <button id="getStartedBtn" class="hero-anim ha-4"
      style="background:linear-gradient(135deg, var(--green-main), var(--green-dark)); color:#fff; border:none; border-radius:14px; padding:16px 48px; font-size:1.05rem; font-weight:700; cursor:pointer; box-shadow:0 8px 28px rgba(22,163,74,0.35); transition:all 0.2s; display:inline-flex; align-items:center; gap:10px;"
      onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 14px 36px rgba(22,163,74,0.45)'"
      onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 28px rgba(22,163,74,0.35)'">
      <i class="bi bi-arrow-right-circle-fill" style="font-size:1.2rem;"></i>
      Get Started
    </button>
  </div>
</section>

<!-- STATS -->
<section class="stats-section">
  <div class="stats-grid">
    <div class="stat-box reveal rv-1">
      <div class="stat-num" data-count="500" data-suffix="+">500+</div>
      <div class="stat-lbl">Tickets Per Day</div>
    </div>
    <div class="stat-box reveal rv-2">
      <div class="stat-num">Live</div>
      <div class="stat-lbl">Queue Updates</div>
    </div>
    <div class="stat-box reveal rv-3">
      <div class="stat-num" data-count="4">4</div>
      <div class="stat-lbl">Priority Levels</div>
    </div>
    <div class="stat-box reveal rv-4">
      <div class="stat-num">24/7</div>
      <div class="stat-lbl">System Uptime</div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="features-section">
  <div class="container" style="max-width:1100px;">
    <div class="section-eyebrow reveal rv-1">Features</div>
    <div class="section-heading reveal rv-2">Everything You Need</div>
    <div class="section-desc reveal rv-3">A complete solution designed for smooth, efficient queue management</div>
    <div class="row g-4">
      <div class="col-md-4 reveal rv-1">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-ticket-perforated"></i></div>
          <h5>Smart Ticketing</h5>
          <p>Auto-generated ticket numbers with support for normal, senior, PWD, and pregnant priority lanes.</p>
        </div>
      </div>
      <div class="col-md-4 reveal rv-2">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-display"></i></div>
          <h5>Live Queue Display</h5>
          <p>Real-time display board showing currently serving numbers. Perfect for lobby screens and waiting areas.</p>
        </div>
      </div>
      <div class="col-md-4 reveal rv-3">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-grid-3x2"></i></div>
          <h5>Multi-Counter</h5>
          <p>Multiple counters operating simultaneously. Each counter manages their own queue independently.</p>
        </div>
      </div>
      <div class="col-md-4 reveal rv-1">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-bar-chart-line"></i></div>
          <h5>Reports & Analytics</h5>
          <p>Daily performance reports with service time tracking, peak hours analysis, and staff productivity data.</p>
        </div>
      </div>
      <div class="col-md-4 reveal rv-2">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-person-badge"></i></div>
          <h5>Role-Based Access</h5>
          <p>Separate dashboards for admins and counter staff with full audit logs for complete accountability.</p>
        </div>
      </div>
      <div class="col-md-4 reveal rv-3">
        <div class="feat-card">
          <div class="feat-icon"><i class="bi bi-sliders"></i></div>
          <h5>Easy Configuration</h5>
          <p>Manage services, counters, and system settings from a clean admin panel. No technical skills needed.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="hiw-section">
  <div class="section-eyebrow reveal rv-1">Process</div>
  <div class="section-heading reveal rv-2">How It Works</div>
  <div class="section-desc reveal rv-3">Simple 4-step process from arrival to service</div>
  <div class="hiw-steps">
    <div class="hiw-step reveal rv-1">
      <div class="hiw-num">1</div>
      <h6>Customer Arrives</h6>
      <p>Customer walks in and approaches the counter or kiosk</p>
    </div>
    <div class="hiw-step reveal rv-2">
      <div class="hiw-num">2</div>
      <h6>Get a Ticket</h6>
      <p>Staff issues a queue ticket with a unique number</p>
    </div>
    <div class="hiw-step reveal rv-3">
      <div class="hiw-num">3</div>
      <h6>Wait & Watch</h6>
      <p>Customer monitors the live display board for their number</p>
    </div>
    <div class="hiw-step reveal rv-4">
      <div class="hiw-num">4</div>
      <h6>Get Served</h6>
      <p>Number is called and customer proceeds to the counter</p>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2 class="reveal">Efficient Queuing Starts Here</h2>
  <p class="reveal rv-1">Designed for organizations that value customer experience and operational efficiency.</p>
</section>

<!-- FOOTER -->
<footer>
  <p>&copy; <?= date('Y') ?> <span>SFI Queuing System</span>. All rights reserved.</p>
</footer>

<!-- CLIENT INFORMATION MODAL -->
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px;">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold mb-1">
            <i class="bi bi-person-fill-add me-2"></i>Client Information
          </h5>
          <div style="font-size:0.8rem; opacity:0.8;">Fill in the details to get your queue ticket</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="clientForm">

          <!-- Client Name -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:0.88rem; color:#1e293b;">
              <i class="bi bi-person me-1" style="color:var(--green-main);"></i>Client Name
            </label>
            <div class="dropdown-search-wrap" id="clientNameWrap">
              <div class="dropdown-input-box" id="clientNameBox" tabindex="0">
                <span class="selected-text" id="clientNameText">Select client name...</span>
                <i class="bi bi-chevron-down dropdown-arrow" id="clientNameArrow"></i>
              </div>
              <div class="dropdown-panel" id="clientNamePanel">
                <div class="search-box-wrap">
                  <i class="bi bi-search search-icon"></i>
                  <input type="text" class="search-input" id="clientNameSearch" placeholder="Search client name...">
                </div>
                <ul class="dropdown-list" id="clientNameList"></ul>
                <div class="no-result d-none" id="clientNameEmpty">No results found</div>
              </div>
              <input type="hidden" id="clientNameVal" name="client_name" required>
            </div>
          </div>

          <!-- Center Name -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:0.88rem; color:#1e293b;">
              <i class="bi bi-building me-1" style="color:var(--green-main);"></i>Center Name
            </label>
            <div class="dropdown-search-wrap" id="centerNameWrap">
              <div class="dropdown-input-box" id="centerNameBox" tabindex="0">
                <span class="selected-text" id="centerNameText">Select center...</span>
                <i class="bi bi-chevron-down dropdown-arrow" id="centerNameArrow"></i>
              </div>
              <div class="dropdown-panel" id="centerNamePanel">
                <div class="search-box-wrap">
                  <i class="bi bi-search search-icon"></i>
                  <input type="text" class="search-input" id="centerNameSearch" placeholder="Search center...">
                </div>
                <ul class="dropdown-list" id="centerNameList"></ul>
                <div class="no-result d-none" id="centerNameEmpty">No results found</div>
              </div>
              <input type="hidden" id="centerNameVal" name="counter_id" required>
            </div>
          </div>

          <!-- Transaction -->
          <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:0.88rem; color:#1e293b;">
              <i class="bi bi-list-check me-1" style="color:var(--green-main);"></i>Transaction
            </label>
            <select class="form-select" id="transactionVal" name="service_id" required
              style="border-radius:10px;border:1.5px solid #e2e8f0;padding:11px 14px;font-size:0.92rem;font-family:'Inter',sans-serif;color:#1e293b;">
              <option value="" disabled selected>Select transaction...</option>
            </select>
          </div>

          <div id="formError" class="alert alert-danger d-none py-2" style="font-size:0.88rem;"></div>

          <button type="submit" class="btn-login-submit" id="submitBtn">
            <i class="bi bi-ticket-perforated me-2"></i>Get Queue Ticket
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- LOGIN MODAL (secret) -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-box-arrow-in-right me-2"></i>Staff Login
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="loginError" class="alert alert-danger d-none py-2" role="alert"></div>
        <form id="loginForm">
          <div class="mb-3">
            <label class="form-label fw-semibold text-dark" style="font-size:0.88rem;">Username</label>
            <input type="text" class="form-control" id="username" placeholder="Enter username" required autocomplete="username">
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold text-dark" style="font-size:0.88rem;">Password</label>
            <input type="password" class="form-control" id="password" placeholder="Enter password" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn-login-submit" id="loginBtn">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Navbar shadow on scroll
  window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 10);
  });

  // ── HERO ENTRANCE: drop animation classes when done so hover transforms work ──
  document.querySelectorAll('.hero-anim').forEach(el => {
    el.addEventListener('animationend', (e) => {
      if (e.target !== el) return;
      el.classList.remove('hero-anim', 'ha-1', 'ha-2', 'ha-3', 'ha-4', 'ha-5');
    });
  });

  // ── COUNT-UP ANIMATION ──
  function animateCount(el) {
    if (el.dataset.counted) return;
    el.dataset.counted = '1';
    const target = parseInt(el.dataset.count, 10);
    const suffix = el.dataset.suffix || '';
    const dur = 1400;
    const t0 = performance.now();
    requestAnimationFrame(function tick(now) {
      const p = Math.min((now - t0) / dur, 1);
      const eased = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * eased) + suffix;
      if (p < 1) requestAnimationFrame(tick);
    });
  }

  // ── SCROLL REVEAL ──
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const revealEls = document.querySelectorAll('.reveal');
  if (reduceMotion || !('IntersectionObserver' in window)) {
    revealEls.forEach(el => {
      el.classList.add('visible');
      el.querySelectorAll('[data-count]').forEach(animateCount);
    });
  } else {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('visible');
        entry.target.querySelectorAll('[data-count]').forEach(animateCount);
        io.unobserve(entry.target);
      });
    }, { threshold: 0.18 });
    revealEls.forEach(el => io.observe(el));
  }

  // ── DROPDOWN HELPER ──
  function initDropdown(cfg) {
    const box    = document.getElementById(cfg.boxId);
    const panel  = document.getElementById(cfg.panelId);
    const search = document.getElementById(cfg.searchId);
    const list   = document.getElementById(cfg.listId);
    const empty  = document.getElementById(cfg.emptyId);
    const text   = document.getElementById(cfg.textId);
    const hidden = document.getElementById(cfg.hiddenId);
    let items = [];

    function open() {
      box.classList.add('open');
      panel.classList.add('show');
      search.value = '';
      renderList('');
      setTimeout(() => search.focus(), 50);
    }
    function close() {
      box.classList.remove('open');
      panel.classList.remove('show');
    }
    function renderList(q) {
      const filtered = items.filter(i => i.label.toLowerCase().includes(q.toLowerCase()));
      list.innerHTML = '';
      if (filtered.length === 0) {
        empty.classList.remove('d-none');
      } else {
        empty.classList.add('d-none');
        filtered.forEach(i => {
          const li = document.createElement('li');
          li.innerHTML = `<i class="bi ${cfg.icon} item-icon"></i>${i.label}`;
          if (hidden.value == i.value) li.classList.add('active');
          li.addEventListener('click', () => {
            hidden.value = i.value;
            text.textContent = i.label;
            text.classList.add('picked');
            list.querySelectorAll('li').forEach(l => l.classList.remove('active'));
            li.classList.add('active');
            close();
            if (cfg.onSelect) cfg.onSelect(i);
          });
          list.appendChild(li);
        });
      }
    }

    box.addEventListener('click', () => panel.classList.contains('show') ? close() : open());
    search.addEventListener('input', e => renderList(e.target.value));
    document.addEventListener('click', e => {
      if (!box.closest('.dropdown-search-wrap').contains(e.target)) close();
    });

    return { setItems(data) { items = data; } };
  }

  // ── INIT DROPDOWNS ──
  const clientDD = initDropdown({
    boxId:'clientNameBox', panelId:'clientNamePanel', searchId:'clientNameSearch',
    listId:'clientNameList', emptyId:'clientNameEmpty', textId:'clientNameText',
    hiddenId:'clientNameVal', icon:'bi-person',
    onSelect(item) {
      // Auto-fill center name when client is selected
      if (item.center) {
        document.getElementById('centerNameVal').value = item.center;
        const ct = document.getElementById('centerNameText');
        ct.textContent = item.center;
        ct.classList.add('picked');
      }
    }
  });
  const centerDD = initDropdown({
    boxId:'centerNameBox', panelId:'centerNamePanel', searchId:'centerNameSearch',
    listId:'centerNameList', emptyId:'centerNameEmpty', textId:'centerNameText',
    hiddenId:'centerNameVal', icon:'bi-building'
  });
  const txDD = null; // Transaction is now a native select

  // ── LOAD DATA WHEN MODAL OPENS ──
  document.getElementById('clientModal').addEventListener('show.bs.modal', async () => {
    try {
      const res = await fetch('/quewing_system/api/public_data.php');
      const data = await res.json();

      // Client names — from customers table (lastname, firstname)
      clientDD.setItems((data.customers || []).map(c => ({
        value: c.client_id || c.id,
        label: [c.client_lastname, c.client_firstname, c.client_middlename]
                .filter(Boolean).join(', '),
        center: c.center_name || ''
      })));

      // Centers — auto-populated from selected client
      centerDD.setItems([]);

      // Transactions — from services table (real DB ids)
      const srvSel = document.getElementById('transactionVal');
      const srvList = data.services || [];
      if (srvList.length) {
        srvSel.innerHTML = '<option value="" disabled selected>Select transaction...</option>' +
          srvList.map(s => `<option value="${s.id}">${s.name.replace(/</g,'&lt;')}</option>`).join('');
      } else {
        srvSel.innerHTML = '<option value="" disabled selected>No transactions available</option>';
      }
      srvSel.value = '';

    } catch(e) {
      console.error('Failed to load data', e);
    }
  });

  // Reset form when modal closes
  document.getElementById('clientModal').addEventListener('hidden.bs.modal', () => {
    location.reload();
  });

  // ── GET STARTED → open client modal ──
  document.getElementById('getStartedBtn').addEventListener('click', () => {
    new bootstrap.Modal(document.getElementById('clientModal')).show();
  });

  // ── SECRET 3-CLICK LOGIN ──
  let clickCount = 0, clickTimer = null;
  document.getElementById('secretBrand').addEventListener('click', function(e) {
    e.preventDefault();
    clickCount++;
    clearTimeout(clickTimer);
    if (clickCount >= 3) {
      clickCount = 0;
      new bootstrap.Modal(document.getElementById('loginModal')).show();
    } else {
      clickTimer = setTimeout(() => { clickCount = 0; }, 1000);
    }
  });

  // ── CLIENT FORM SUBMIT ──
  document.getElementById('clientForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('formError');
    const btn = document.getElementById('submitBtn');
    const clientName = document.getElementById('clientNameVal').value;
    const centerId   = document.getElementById('centerNameVal').value;
    const serviceId  = document.getElementById('transactionVal').value;

    if (!clientName || !centerId || !serviceId) {
      err.textContent = 'Please fill in all fields.';
      err.classList.remove('d-none');
      return;
    }
    err.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    try {
      const res = await fetch('/quewing_system/api/tickets/public_create.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          service_id:    serviceId,
          customer_name: document.getElementById('clientNameText').textContent,
          priority:      'normal'
        })
      });
      const result = await res.json();
      if (res.ok && result.success) {
        // Hide form, show ticket result
        window.lastTicket = result;
        document.getElementById('clientForm').innerHTML = `
          <div style="text-align:center; padding: 10px 0 20px;">
            <div style="width:80px;height:80px;background:linear-gradient(135deg,#16a34a,#0a4a28);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(22,163,74,0.35);">
              <i class="bi bi-ticket-perforated-fill" style="font-size:2rem;color:#fff;"></i>
            </div>
            <div style="font-size:0.82rem;color:#94a3b8;text-transform:uppercase;letter-spacing:1.5px;font-weight:600;margin-bottom:6px;">Your Queue Ticket</div>
            <div style="font-size:3rem;font-weight:900;color:#0a4a28;letter-spacing:2px;line-height:1;margin-bottom:20px;">${result.ticket_number}</div>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin-bottom:20px;text-align:left;">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:0.82rem;color:#94a3b8;font-weight:600;">Service</span>
                <span style="font-size:0.88rem;color:#1e293b;font-weight:600;">${result.service_name}</span>
              </div>
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:0.82rem;color:#94a3b8;font-weight:600;">Client</span>
                <span style="font-size:0.88rem;color:#1e293b;font-weight:600;">${result.customer_name || 'Walk-In'}</span>
              </div>
              <div style="display:flex;justify-content:space-between;">
                <span style="font-size:0.82rem;color:#94a3b8;font-weight:600;">Queue Position</span>
                <span style="font-size:0.88rem;color:#16a34a;font-weight:700;">#${result.queue_position} in line</span>
              </div>
            </div>
            <div style="font-size:0.82rem;color:#94a3b8;margin-bottom:20px;">Please wait for your number to be called.</div>
            <div class="btn-row-2">
              <button class="btn-print-ticket" onclick="printTicket()">
                <i class="bi bi-printer-fill"></i> Print Ticket
              </button>
              <button class="btn-done"
                onclick="bootstrap.Modal.getInstance(document.getElementById('clientModal')).hide(); location.reload();">
                <i class="bi bi-check2 me-2"></i>Done
              </button>
            </div>
          </div>
        `;
      } else {
        err.textContent = result.error || 'Failed to issue ticket.';
        err.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-ticket-perforated me-2"></i>Get Queue Ticket';
      }
    } catch(e) {
      err.textContent = 'Connection error. Please try again.';
      err.classList.remove('d-none');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-ticket-perforated me-2"></i>Get Queue Ticket';
    }
  });

  // ── STAFF LOGIN SUBMIT ──
  document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const err = document.getElementById('loginError');
    const btn = document.getElementById('loginBtn');
    err.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
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
        window.location.href = result.redirect;
      } else {
        err.textContent = result.error || 'Login failed.';
        err.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login';
      }
    } catch(e) {
      err.textContent = 'Connection error.';
      err.classList.remove('d-none');
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Login';
    }
  });
</script>

<!-- PRINT-ONLY TICKET RECEIPT -->
<div class="print-ticket" id="printTicket"></div>

<script>
  // Build and print the issued ticket receipt
  function printTicket() {
    var t = window.lastTicket || {};
    var el = document.getElementById('printTicket');
    var sysName = document.title.replace(' - ', ' - ');
    var now = new Date();
    var dateStr = now.toLocaleString('en-PH', {
      year: 'numeric', month: '2-digit', day: '2-digit',
      hour: '2-digit', minute: '2-digit'
    });
    el.innerHTML = `
      <div class="pt-brand">${sysName}</div>
      <div class="pt-sub">Consolidated Community Affairs Office</div>
      <div class="pt-date">${dateStr}</div>
      <div class="pt-divider"></div>
      <div class="pt-num">${t.ticket_number || ''}</div>
      <div class="pt-divider"></div>
      <div class="pt-row"><span class="pt-k">Service</span><span class="pt-v">${t.service_name || '—'}</span></div>
      <div class="pt-row"><span class="pt-k">Client</span><span class="pt-v">${t.customer_name || 'Walk-In'}</span></div>
      <div class="pt-row"><span class="pt-k">Position</span><span class="pt-v">#${t.queue_position != null ? t.queue_position : '—'} in line</span></div>
      <div class="pt-divider"></div>
      <div class="pt-foot">Kindly wait for your number to be called.<br>Thank you for your patience.</div>
    `;
    window.print();
  }
</script>
</body>
</html>
