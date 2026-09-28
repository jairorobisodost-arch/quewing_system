<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --green-dark:  #0a4a28;
    --green-mid:   #166534;
    --green-main:  #16a34a;
    --green-light: #22c55e;
    --green-pale:  #dcfce7;
    --green-bg:    #f0fdf4;
    --white:       #ffffff;
    --gray-50:     #f8fafc;
    --gray-100:    #f1f5f9;
    --gray-200:    #e2e8f0;
    --gray-400:    #94a3b8;
    --gray-600:    #475569;
    --gray-800:    #1e293b;
    --sidebar-w:   260px;
  }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: var(--green-bg);
    color: var(--gray-800);
    display: flex;
    min-height: 100vh;
  }

  /* ── SIDEBAR ── */
  .sidebar {
    width: var(--sidebar-w);
    background: linear-gradient(160deg, var(--green-dark) 0%, var(--green-mid) 60%, var(--green-main) 100%);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 50;
    overflow: hidden;
  }
  .sidebar::before {
    content: '';
    position: absolute;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
    top: -80px; right: -80px;
    pointer-events: none;
  }

  .sidebar-brand {
    padding: 28px 24px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative; z-index: 1;
  }
  .sidebar-logo {
    width: 42px; height: 42px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    color: #fff;
    border: 1.5px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
  }
  .sidebar-brand-text {
    font-size: 0.82rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.5px;
    line-height: 1.3;
  }
  .sidebar-brand-text span {
    display: block;
    font-size: 0.7rem;
    font-weight: 400;
    color: rgba(255,255,255,0.55);
    margin-top: 2px;
  }

  .sidebar-nav {
    flex: 1;
    padding: 16px 12px;
    overflow-y: auto;
    position: relative; z-index: 1;
  }
  .sidebar-label {
    font-size: 0.65rem;
    font-weight: 700;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    padding: 12px 12px 6px;
  }
  .nav-link-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    color: rgba(255,255,255,0.75);
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
    margin-bottom: 2px;
  }
  .nav-link-item:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
  }
  .nav-link-item.active {
    background: rgba(255,255,255,0.18);
    color: #fff;
    font-weight: 600;
  }
  .nav-link-item i {
    width: 20px;
    font-size: 1rem;
    text-align: center;
    flex-shrink: 0;
  }

  .sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid rgba(255,255,255,0.1);
    position: relative; z-index: 1;
  }
  .user-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: rgba(255,255,255,0.08);
    border-radius: 10px;
    margin-bottom: 8px;
  }
  .user-avatar {
    width: 34px; height: 34px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    color: #fff;
    flex-shrink: 0;
  }
  .user-info { flex: 1; min-width: 0; }
  .user-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .user-role {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.5);
  }
  .btn-logout {
    width: 100%;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.75);
    border-radius: 8px;
    padding: 8px;
    font-size: 0.82rem;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    text-decoration: none;
  }
  .btn-logout:hover {
    background: rgba(255,255,255,0.15);
    color: #fff;
  }

  /* ── MAIN ── */
  .main-content {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }

  .topbar {
    background: var(--white);
    border-bottom: 1px solid var(--gray-200);
    padding: 0 32px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 40;
    box-shadow: 0 1px 8px rgba(0,0,0,0.04);
  }
  .topbar-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--green-dark);
  }
  .topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .topbar-date {
    font-size: 0.8rem;
    color: var(--gray-400);
    font-weight: 500;
  }
  .badge-live {
    background: var(--green-pale);
    color: var(--green-main);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .badge-live::before {
    content: '';
    width: 6px; height: 6px;
    background: var(--green-main);
    border-radius: 50%;
    animation: pulse 1.5s infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  .page-body {
    padding: 32px;
    flex: 1;
  }

  /* ── KPI CARDS ── */
  .kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 28px;
  }
  .kpi-card {
    background: var(--white);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
    border: 1px solid var(--gray-200);
    position: relative;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
  }
  .kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  }
  .kpi-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: 16px 16px 0 0;
  }
  .kpi-card.green::after  { background: linear-gradient(90deg, var(--green-main), var(--green-light)); }
  .kpi-card.blue::after   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
  .kpi-card.amber::after  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
  .kpi-card.red::after    { background: linear-gradient(90deg, #ef4444, #f87171); }

  .kpi-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    margin-bottom: 16px;
  }
  .kpi-card.green  .kpi-icon { background: var(--green-pale); color: var(--green-main); }
  .kpi-card.blue   .kpi-icon { background: #eff6ff; color: #3b82f6; }
  .kpi-card.amber  .kpi-icon { background: #fffbeb; color: #f59e0b; }
  .kpi-card.red    .kpi-icon { background: #fef2f2; color: #ef4444; }

  .kpi-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 6px;
  }
  .kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--gray-800);
    line-height: 1;
  }

  /* ── CONTENT GRID ── */
  .content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
  }

  /* ── CARD ── */
  .card-box {
    background: var(--white);
    border-radius: 16px;
    border: 1px solid var(--gray-200);
    box-shadow: 0 1px 8px rgba(0,0,0,0.05);
    overflow: hidden;
  }
  .card-head {
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .card-head h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--green-dark);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .card-head h5 i {
    color: var(--green-main);
  }
  .card-body-inner {
    padding: 20px 24px;
  }

  /* ── ANNOUNCEMENT ── */
  .ann-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    background: var(--gray-100);
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .ann-status .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
  .ann-status.live { background: var(--green-pale); color: var(--green-main); }
  .ann-status.live .dot { animation: annPulse 1.5s infinite; }
  @keyframes annPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
  .ann-textarea {
    width: 100%;
    border: 1.5px solid var(--gray-200);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.85rem;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    color: var(--gray-800);
    resize: vertical;
    outline: none;
    transition: border 0.15s;
    line-height: 1.5;
  }
  .ann-textarea:focus { border-color: var(--green-main); }
  .ann-btn {
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 0.8rem;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.15s;
  }
  .ann-btn-off { background: var(--gray-100); color: var(--gray-600); }
  .ann-btn-off:hover { background: #fee2e2; color: #dc2626; }
  .ann-btn-on { background: var(--green-pale); color: var(--green-main); }
  .ann-btn-on:hover { background: var(--green-main); color: #fff; }

  /* ── COUNTER STATUS ── */
  .counter-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-100);
  }
  .counter-item:last-child { border-bottom: none; }
  .counter-num {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--green-main), var(--green-dark));
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    flex-shrink: 0;
  }
  .counter-name {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-800);
  }
  .counter-staff {
    font-size: 0.75rem;
    color: var(--gray-400);
    margin-top: 2px;
  }
  .status-badge {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
  }
  .status-badge.active   { background: var(--green-pale); color: var(--green-main); }
  .status-badge.idle     { background: #fffbeb; color: #f59e0b; }
  .status-badge.offline  { background: #f1f5f9; color: var(--gray-400); }

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--gray-400);
    font-size: 0.85rem;
  }
  .empty-state i {
    font-size: 2rem;
    display: block;
    margin-bottom: 8px;
    opacity: 0.4;
  }

  /* ── RESPONSIVE ── */
  @media (max-width: 1100px) {
    .content-grid { grid-template-columns: 1fr; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .main-content { margin-left: 0; }
    .kpi-grid { grid-template-columns: 1fr; }
    .page-body { padding: 16px; }
  }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo"><i class="bi bi-ticket-perforated-fill"></i></div>
    <div class="sidebar-brand-text">
      <?php echo htmlspecialchars($system_name); ?>
      <span>Admin Panel</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-label">Main Menu</div>
    <a href="dashboard.php" class="nav-link-item active">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="services.php" class="nav-link-item">
      <i class="bi bi-list-task"></i> Services
    </a>
    <a href="counters.php" class="nav-link-item">
      <i class="bi bi-grid-1x2"></i> Counters
    </a>
    <a href="users.php" class="nav-link-item">
      <i class="bi bi-people"></i> Users
    </a>

    <div class="sidebar-label" style="margin-top:8px;">Analytics</div>
    <a href="reports.php" class="nav-link-item">
      <i class="bi bi-bar-chart-line"></i> Reports
    </a>

    <div class="sidebar-label" style="margin-top:8px;">System</div>
    <a href="import_clients.php" class="nav-link-item">
      <i class="bi bi-cloud-arrow-up"></i> Import Client Information
    </a>
    <a href="settings.php" class="nav-link-item">
      <i class="bi bi-gear"></i> Settings
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="user-card">
      <div class="user-avatar"><i class="bi bi-person-fill"></i></div>
      <div class="user-info">
        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
        <div class="user-role">Administrator</div>
      </div>
    </div>
    <a href="/quewing_system/api/auth/logout.php" class="btn-logout"
       onclick="event.preventDefault(); if(confirm('Logout?')) window.location.href=this.href;">
      <i class="bi bi-box-arrow-left"></i> Logout
    </a>
  </div>
</aside>

<!-- MAIN CONTENT -->
<div class="main-content">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-speedometer2 me-2" style="color:var(--green-main)"></i>Dashboard</div>
    <div class="topbar-right">
      <span class="topbar-date" id="topbarDate"></span>
      <button class="btn-issue" onclick="openWalkInModal()">
        <i class="bi bi-ticket-perforated"></i> Issue Ticket
      </button>
      <span class="badge-live">LIVE</span>
    </div>
  </div>

  <!-- PAGE BODY -->
  <div class="page-body">

    <!-- KPI CARDS -->
    <div class="kpi-grid">
      <div class="kpi-card green">
        <div class="kpi-icon"><i class="bi bi-ticket-perforated"></i></div>
        <div class="kpi-label">Total Today</div>
        <div class="kpi-value" id="kpiTotal">—</div>
      </div>
      <div class="kpi-card blue">
        <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        <div class="kpi-label">Completed</div>
        <div class="kpi-value" id="kpiCompleted">—</div>
      </div>
      <div class="kpi-card amber">
        <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
        <div class="kpi-label">Avg Wait Time</div>
        <div class="kpi-value" id="kpiWait" style="font-size:1.4rem;">—</div>
      </div>
      <div class="kpi-card red">
        <div class="kpi-icon"><i class="bi bi-display"></i></div>
        <div class="kpi-label">Active Counters</div>
        <div class="kpi-value" id="kpiCounters">—</div>
      </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid">

      <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- TV ANNOUNCEMENT -->
        <div class="card-box">
          <div class="card-head">
            <h5><i class="bi bi-megaphone"></i> TV Announcement</h5>
            <span class="ann-status" id="annStatus">
              <span class="dot"></span>Checking...
            </span>
          </div>
          <div class="card-body-inner">
            <textarea id="annText" class="ann-textarea" maxlength="300" rows="3"
              placeholder="e.g. Magandang umaga! Ang opisina ay magmumula sa 12:00 PM hanggang 1:00 PM. — magsusulat kung sinong counter ang serbisyo ngayon."></textarea>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
              <span style="font-size:0.72rem;color:var(--gray-400);" id="annCharCount">0/300 characters</span>
              <div style="display:flex;gap:8px;">
                <button class="ann-btn ann-btn-off" id="annOffBtn" onclick="setAnnouncement(false)">
                  <i class="bi bi-mic-mute"></i> Turn OFF
                </button>
                <button class="ann-btn ann-btn-on" id="annOnBtn" onclick="setAnnouncement(true)">
                  <i class="bi bi-broadcast"></i> Turn ON
                </button>
              </div>
            </div>
            <div style="font-size:0.75rem;color:var(--gray-400);margin-top:10px;display:flex;align-items:center;gap:6px;">
              <i class="bi bi-tv" style="color:var(--green-main);"></i>
              Lalabas ito bilang banner sa <a href="/quewing_system/queue-display/index.php" target="_blank" style="color:var(--green-main);text-decoration:none;font-weight:600;">Queue Display TV</a> within ~2 seconds.
            </div>
          </div>
        </div>

        <!-- LATEST UPLOAD CARD -->
        <div class="card-box" id="latestUploadCard">
          <div class="card-head">
            <h5><i class="bi bi-cloud-upload"></i> Latest Upload</h5>
            <a href="/quewing_system/admin/import_clients.php" style="font-size:0.75rem;color:var(--green-main);text-decoration:none;font-weight:600;">
              View All <i class="bi bi-arrow-right"></i>
            </a>
          </div>
          <div class="card-body-inner" id="latestUploadBody">
            <div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading...</div>
          </div>
        </div>

        <!-- COUNTER STATUS -->
        <div class="card-box">
          <div class="card-head">
            <h5><i class="bi bi-grid-1x2"></i> Counter Status</h5>
          </div>
          <div class="card-body-inner" id="counterStatus">
            <div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading...</div>
          </div>
        </div>

      </div>

    </div>
  </div>
</div>

<script>
  // Topbar date
  function updateDate() {
    const now = new Date();
    document.getElementById('topbarDate').textContent = now.toLocaleDateString('en-PH', {
      weekday: 'short', year: 'numeric', month: 'short', day: 'numeric'
    });
  }
  updateDate();

  async function api(url) {
    const res = await fetch(url);
    if (!res.ok) return null;
    return res.json();
  }

  function fmtDate(str) {
    if (!str) return '—';
    const d = new Date(str);
    return d.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' })
      + ' ' + d.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', hour12:true });
  }

  async function loadLatestUpload() {
    const data = await api('/quewing_system/api/customers/import_logs.php?latest=1');
    const el = document.getElementById('latestUploadBody');
    if (!data || !data.log) {
      el.innerHTML = '<div class="empty-state"><i class="bi bi-inbox"></i>No uploads yet</div>';
      return;
    }
    const log = data.log;
    const uploader = log.first_name ? log.first_name + ' ' + log.last_name + ' (' + log.username + ')' : (log.username || '—');
    el.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:40px;height:40px;background:var(--green-pale);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-file-earmark-spreadsheet" style="color:var(--green-main);font-size:1.1rem;"></i>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-weight:700;font-size:0.88rem;color:var(--gray-800);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${log.file_name}</div>
            <div style="font-size:0.75rem;color:var(--gray-400);margin-top:2px;">${fmtDate(log.imported_at)}</div>
          </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
          <div style="background:var(--gray-50);border-radius:8px;padding:8px 12px;">
            <div style="font-size:0.68rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:0.8px;">Uploaded By</div>
            <div style="font-size:0.82rem;font-weight:600;color:var(--gray-800);margin-top:2px;">${uploader}</div>
          </div>
          <div style="background:var(--gray-50);border-radius:8px;padding:8px 12px;">
            <div style="font-size:0.68rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:0.8px;">Branch</div>
            <div style="font-size:0.82rem;font-weight:600;color:var(--gray-800);margin-top:2px;">${log.branch || '—'}</div>
          </div>
          <div style="background:var(--green-pale);border-radius:8px;padding:8px 12px;">
            <div style="font-size:0.68rem;font-weight:700;color:var(--green-main);text-transform:uppercase;letter-spacing:0.8px;">Imported</div>
            <div style="font-size:0.88rem;font-weight:800;color:var(--green-main);margin-top:2px;">${log.total_imported} records</div>
          </div>
          <div style="background:#fffbeb;border-radius:8px;padding:8px 12px;">
            <div style="font-size:0.68rem;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.8px;">Skipped</div>
            <div style="font-size:0.88rem;font-weight:800;color:#f59e0b;margin-top:2px;">${log.total_skipped} rows</div>
          </div>
        </div>
      </div>`;
  }

  async function loadDashboard() {
    // KPI
    const report = await api('/quewing_system/api/reports/daily.php');
    if (report && report.total_tickets !== undefined) {
      document.getElementById('kpiTotal').textContent     = report.total_tickets ?? '0';
      document.getElementById('kpiCompleted').textContent = report.completed ?? '0';
      document.getElementById('kpiWait').textContent      = report.avg_wait_formatted || '—';
      const active = (report.counter_stats || []).filter(c => c.status !== 'paused').length;
      document.getElementById('kpiCounters').textContent  = active;

      // Counter status
      const counters = report.counter_stats || [];
      const cEl = document.getElementById('counterStatus');
      if (counters.length > 0) {
        cEl.innerHTML = counters.map(c => {
          const statusClass = c.tickets_served > 0 ? 'active' : (c.staff_name ? 'idle' : 'offline');
          const statusLabel = c.tickets_served > 0 ? 'Active' : (c.staff_name ? 'Idle' : 'Offline');
          return `<div class="counter-item">
            <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
              <div class="counter-num">${c.counter_number ?? '?'}</div>
              <div>
                <div class="counter-name">Counter ${c.counter_number ?? '?'}</div>
                <div class="counter-staff">${c.staff_name ? c.staff_name : 'No staff assigned'}</div>
              </div>
            </div>
            <span class="status-badge ${statusClass}">${statusLabel}</span>
          </div>`;
        }).join('');
      } else {
        cEl.innerHTML = '<div class="empty-state"><i class="bi bi-display"></i>No counters found</div>';
      }
    }
  }

  async function loadAnnouncement() {
    const data = await api('/quewing_system/api/announcement');
    if (!data) return;
    const statusEl = document.getElementById('annStatus');
    if (document.activeElement !== document.getElementById('annText')) {
      document.getElementById('annText').value = data.text || '';
      updateAnnCount();
    }
    statusEl.className = 'ann-status' + (data.active ? ' live' : '');
    statusEl.innerHTML = data.active
      ? '<span class="dot"></span>LIVE on TV'
      : '<span class="dot"></span>OFF';
  }

  function updateAnnCount() {
    const len = document.getElementById('annText').value.length;
    document.getElementById('annCharCount').textContent = len + '/300 characters';
  }
  document.getElementById('annText').addEventListener('input', updateAnnCount);

  async function setAnnouncement(turnOn) {
    const text = document.getElementById('annText').value.trim();
    if (turnOn && !text) {
      alert('Please write an announcement first.');
      return;
    }
    try {
      const res = await fetch('/quewing_system/api/announcement', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(turnOn ? { text: text, active: true } : { active: false })
      });
      const data = await res.json();
      if (!res.ok) { alert(data.error || 'Failed to update announcement.'); return; }
      loadAnnouncement();
    } catch (e) {
      alert('Connection error.');
    }
  }

  loadAnnouncement();
  setInterval(loadAnnouncement, 15000);

  loadDashboard();
  loadLatestUpload();
  setInterval(loadDashboard, 10000);
  setInterval(loadLatestUpload, 30000);
</script>

<!-- WALK-IN ISSUE MODAL -->
<div id="walkInModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#fff;border-radius:20px;width:100%;max-width:560px;margin:24px;box-shadow:0 20px 60px rgba(0,0,0,0.2);overflow:hidden;">

    <div style="padding:24px 28px 16px;border-bottom:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;">
      <div>
        <div style="font-size:1rem;font-weight:800;color:var(--green-dark);">
          <i class="bi bi-ticket-perforated" style="color:var(--green-main);margin-right:8px;"></i>Issue Walk-in Ticket
        </div>
        <div style="font-size:0.78rem;color:var(--gray-400);margin-top:3px;">For customers at the branch without a kiosk ticket</div>
      </div>
      <button onclick="closeWalkInModal()" style="background:none;border:none;font-size:1.2rem;color:var(--gray-400);cursor:pointer;padding:4px;">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- FORM VIEW -->
    <div id="walkInForm" style="padding:24px 28px;">
      <div style="margin-bottom:18px;">
        <label class="wi-label">Service <span style="color:#ef4444;">*</span></label>
        <select id="wiService" class="wi-input">
          <option value="">Loading services...</option>
        </select>
      </div>

      <div style="margin-bottom:18px;">
        <label class="wi-label">Priority</label>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button type="button" class="prio-chip active" data-p="normal" onclick="selectPriority('normal')">Regular</button>
          <button type="button" class="prio-chip" data-p="senior" onclick="selectPriority('senior')">🧓 Senior</button>
          <button type="button" class="prio-chip" data-p="pwd" onclick="selectPriority('pwd')">♿ PWD</button>
          <button type="button" class="prio-chip" data-p="pregnant" onclick="selectPriority('pregnant')">🤰 Pregnant</button>
        </div>
      </div>

      <div style="margin-bottom:18px;position:relative;">
        <label class="wi-label">Customer (search imported members)</label>
        <input type="text" id="wiSearch" class="wi-input" placeholder="Type name, client ID, center, or phone..." autocomplete="off" oninput="customerSearch(this.value)">
        <div id="wiResults" class="wi-results" style="display:none;"></div>
        <div id="wiSelected" class="wi-selected" style="display:none;">
          <div style="flex:1;min-width:0;">
            <div class="wsi-name" id="wsiName"></div>
            <div class="wsi-meta" id="wsiMeta"></div>
          </div>
          <button type="button" class="wsi-clear" onclick="clearSelectedCustomer()" title="Clear selection"><i class="bi bi-x-lg"></i></button>
        </div>
        <div style="font-size:0.72rem;color:var(--gray-400);margin-top:6px;">
          Or <a href="#" onclick="showManualEntry();return false;" style="color:var(--green-main);font-weight:600;">enter manually</a> (walk-in without record)
        </div>
        <input type="hidden" id="wiCustomerId" value="">
      </div>

      <div id="wiManualFields" style="display:none;">
        <div style="margin-bottom:18px;">
          <label class="wi-label">Customer Name (manual)</label>
          <input type="text" id="wiName" class="wi-input" placeholder="e.g. Juan Dela Cruz" maxlength="200">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label class="wi-label">Phone (manual)</label>
            <input type="text" id="wiPhone" class="wi-input" placeholder="09XX XXX XXXX" maxlength="20">
          </div>
          <div>
            <label class="wi-label">Notes</label>
            <input type="text" id="wiNotes" class="wi-input" placeholder="e.g. Loan renewal" maxlength="200">
          </div>
        </div>
      </div>
      <div id="wiNotesImported" style="display:none;margin-bottom:18px;">
        <label class="wi-label">Notes</label>
        <input type="text" id="wiNotes2" class="wi-input" placeholder="e.g. Loan renewal" maxlength="200">
      </div>
    </div>

    <!-- RESULT VIEW -->
    <div id="walkInResult" style="display:none;padding:24px 28px;text-align:center;"></div>

    <div style="padding:16px 28px 24px;display:flex;gap:10px;justify-content:flex-end;">
      <button onclick="closeWalkInModal()" style="background:var(--gray-100);border:none;border-radius:10px;padding:10px 20px;font-size:0.88rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;color:var(--gray-600);">
        Close
      </button>
      <button id="wiSubmitBtn" onclick="submitWalkIn()"
        style="background:linear-gradient(135deg,var(--green-main),var(--green-dark));color:#fff;border:none;border-radius:10px;padding:10px 24px;font-size:0.88rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;gap:8px;">
        <i class="bi bi-ticket-perforated"></i> Issue Ticket
      </button>
      <button id="wiPrintBtn" onclick="printLastTicket()" style="display:none;background:#eff6ff;color:#3b82f6;border:1.5px solid #bfdbfe;border-radius:10px;padding:10px 20px;font-size:0.88rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;">
        <i class="bi bi-printer"></i> Print
      </button>
      <button id="wiAnotherBtn" onclick="resetWalkInForm()" style="display:none;background:var(--green-pale);color:var(--green-main);border:1.5px solid #bbf7d0;border-radius:10px;padding:10px 20px;font-size:0.88rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;">
        <i class="bi bi-plus-lg"></i> Issue Another
      </button>
    </div>

  </div>
</div>

<!-- Import modal removed: use the Import Client Information page in the sidebar -->

<style>
  .btn-issue {
    background: #eff6ff;
    color: #3b82f6;
    border: 1.5px solid #bfdbfe;
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 0.82rem;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
  }
  .btn-issue:hover {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
  }
  .prio-chip {
    border: 1.5px solid var(--gray-200);
    background: #fff;
    color: var(--gray-600);
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.15s;
  }
  .prio-chip:hover { border-color: var(--green-main); color: var(--green-main); }
  .prio-chip.active { background: var(--green-main); border-color: var(--green-main); color: #fff; }
  .wi-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--gray-600);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    display: block;
    margin-bottom: 7px;
  }
  .wi-input {
    width: 100%;
    border: 1.5px solid var(--gray-200);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 0.88rem;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    outline: none;
    transition: border 0.15s;
  }
  .wi-input:focus { border-color: var(--green-main); }
  .wi-row {
    display: flex;
    justify-content: space-between;
    padding: 6px 0;
    border-bottom: 1px dashed var(--gray-100);
    font-size: 0.85rem;
  }
  .wi-row:last-child { border-bottom: none; }
  .wi-row .k { color: var(--gray-400); font-weight: 600; }
  .wi-row .v { font-weight: 700; color: var(--gray-800); }

  .wi-results {
    position: absolute;
    top: calc(100% + 4px);
    left: 0; right: 0;
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.14);
    max-height: 260px;
    overflow-y: auto;
    z-index: 50;
  }
  .wi-result-item {
    padding: 11px 14px;
    cursor: pointer;
    border-bottom: 1px solid var(--gray-100);
    transition: background 0.1s;
  }
  .wi-result-item:hover { background: var(--green-bg); }
  .wi-result-item:last-child { border-bottom: none; }
  .wri-name { font-size: 0.87rem; font-weight: 700; color: var(--gray-800); }
  .wri-name .hl { background: #fef08a; border-radius: 3px; }
  .wri-meta { font-size: 0.74rem; color: var(--gray-400); margin-top: 2px; display: flex; gap: 10px; flex-wrap: wrap; }
  .wri-meta i { color: var(--green-main); margin-right: 3px; }
  .wi-results-empty {
    padding: 14px;
    text-align: center;
    font-size: 0.82rem;
    color: var(--gray-400);
  }
  .wi-selected {
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--green-pale);
    border: 1.5px solid #bbf7d0;
    border-radius: 10px;
    padding: 10px 14px;
  }
  .wsi-name { font-size: 0.88rem; font-weight: 800; color: var(--green-dark); }
  .wsi-meta { font-size: 0.74rem; color: var(--gray-600); margin-top: 2px; }
  .wsi-clear {
    background: none;
    border: none;
    color: var(--gray-400);
    cursor: pointer;
    font-size: 0.85rem;
    padding: 6px;
    border-radius: 6px;
  }
  .wsi-clear:hover { background: rgba(0,0,0,0.06); color: #dc2626; }

</style>

<script>
  let _lastTicket = null;
  let _wiPriority = 'normal';
  let _wiServicesLoaded = false;
  let _searchTimer = null;
  let _selectedCustomer = null;

  function customerSearch(q) {
    clearTimeout(_searchTimer);
    const resultsEl = document.getElementById('wiResults');
    if (!q || q.trim().length < 2) {
      resultsEl.style.display = 'none';
      resultsEl.innerHTML = '';
      return;
    }
    _searchTimer = setTimeout(async () => {
      try {
        const res = await fetch('/quewing_system/api/customers/search.php?q=' + encodeURIComponent(q.trim()));
        const data = await res.json();
        const customers = data.customers || [];
        if (customers.length === 0) {
          resultsEl.innerHTML = '<div class="wi-results-empty">No matching customer found. <a href="#" onclick="showManualEntry();return false;" style="color:var(--green-main);font-weight:600;">Enter manually instead</a></div>';
          resultsEl.style.display = 'block';
          return;
        }
        resultsEl.innerHTML = customers.map(c => {
          const name = [c.client_firstname, c.client_middlename, c.client_lastname].filter(Boolean).join(' ');
          return `<div class="wi-result-item" onclick='selectCustomer(${JSON.stringify({id: c.id, name: name, client_id: c.client_id || "", center: c.center_name || "", phone: c.contact_no || "", birthday: c.birthday || ""})})'>
            <div class="wri-name">${esc(name)}</div>
            <div class="wri-meta">
              ${c.client_id ? '<span><i class="bi bi-hash"></i>' + esc(c.client_id) + '</span>' : ''}
              ${c.center_name ? '<span><i class="bi bi-people"></i>' + esc(c.center_name) + '</span>' : ''}
              ${c.contact_no ? '<span><i class="bi bi-telephone"></i>' + esc(c.contact_no) + '</span>' : ''}
            </div>
          </div>`;
        }).join('');
        resultsEl.style.display = 'block';
      } catch (e) {
        resultsEl.innerHTML = '<div class="wi-results-empty">Search failed. Please try again.</div>';
        resultsEl.style.display = 'block';
      }
    }, 250);
  }

  function selectCustomer(c) {
    _selectedCustomer = c;
    document.getElementById('wiCustomerId').value = c.id;
    document.getElementById('wsiName').textContent = c.name;
    const bits = [];
    if (c.client_id) bits.push('ID: ' + c.client_id);
    if (c.center) bits.push(c.center);
    if (c.phone) bits.push(c.phone);
    document.getElementById('wsiMeta').textContent = bits.join(' · ');
    document.getElementById('wiSearch').value = '';
    document.getElementById('wiResults').style.display = 'none';
    document.getElementById('wiResults').innerHTML = '';
    document.getElementById('wiSelected').style.display = 'flex';
    document.getElementById('wiSearch').style.display = 'none';
    document.getElementById('wiManualFields').style.display = 'none';
    document.getElementById('wiNotesImported').style.display = 'block';
  }

  function clearSelectedCustomer() {
    _selectedCustomer = null;
    document.getElementById('wiCustomerId').value = '';
    document.getElementById('wiSelected').style.display = 'none';
    document.getElementById('wiSearch').style.display = 'block';
    document.getElementById('wiSearch').focus();
    document.getElementById('wiNotesImported').style.display = 'none';
    document.getElementById('wiManualFields').style.display = 'none';
  }

  function showManualEntry() {
    document.getElementById('wiResults').style.display = 'none';
    document.getElementById('wiResults').innerHTML = '';
    document.getElementById('wiSelected').style.display = 'none';
    document.getElementById('wiSearch').style.display = 'none';
    document.getElementById('wiManualFields').style.display = 'block';
    document.getElementById('wiNotesImported').style.display = 'none';
    document.getElementById('wiName').focus();
  }

  // Close results dropdown when clicking outside
  document.addEventListener('click', (e) => {
    const resultsEl = document.getElementById('wiResults');
    if (resultsEl && !e.target.closest('#wiResults') && e.target.id !== 'wiSearch') {
      resultsEl.style.display = 'none';
    }
  });

  function esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  async function loadWalkInServices() {
    if (_wiServicesLoaded) return;
    try {
      const res = await fetch('/quewing_system/api/services/index.php');
      const data = await res.json();
      const sel = document.getElementById('wiService');
      const services = data.services || [];
      sel.innerHTML = services.length
        ? '<option value="">— Select service —</option>' + services.map(s =>
            `<option value="${s.id}">${esc(s.name)}</option>`).join('')
        : '<option value="">No active services found</option>';
      _wiServicesLoaded = true;
    } catch (e) {
      document.getElementById('wiService').innerHTML = '<option value="">Failed to load services</option>';
    }
  }

  function openWalkInModal() {
    document.getElementById('walkInModal').style.display = 'flex';
    loadWalkInServices();
  }

  function closeWalkInModal() {
    document.getElementById('walkInModal').style.display = 'none';
    resetWalkInForm();
  }

  function selectPriority(p) {
    _wiPriority = p;
    document.querySelectorAll('.prio-chip').forEach(c => c.classList.toggle('active', c.dataset.p === p));
  }

  function resetWalkInForm() {
    _lastTicket = null;
    _wiPriority = 'normal';
    _selectedCustomer = null;
    document.querySelectorAll('.prio-chip').forEach(c => c.classList.toggle('active', c.dataset.p === 'normal'));
    document.getElementById('wiSearch').value = '';
    document.getElementById('wiResults').style.display = 'none';
    document.getElementById('wiSelected').style.display = 'none';
    document.getElementById('wiCustomerId').value = '';
    document.getElementById('wiSearch').style.display = 'block';
    document.getElementById('wiManualFields').style.display = 'none';
    document.getElementById('wiName').value = '';
    document.getElementById('wiPhone').value = '';
    document.getElementById('wiNotes').value = '';
    document.getElementById('wiNotes2').value = '';
    document.getElementById('walkInForm').style.display = 'block';
    document.getElementById('walkInResult').style.display = 'none';
    document.getElementById('wiSubmitBtn').style.display = 'flex';
    document.getElementById('wiPrintBtn').style.display = 'none';
    document.getElementById('wiAnotherBtn').style.display = 'none';
  }

  async function submitWalkIn() {
    const serviceId = document.getElementById('wiService').value;
    if (!serviceId) { alert('Please select a service.'); return; }

    const btn = document.getElementById('wiSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Issuing...';

    try {
      const isImported = !!_selectedCustomer;
      const notesVal = isImported
        ? document.getElementById('wiNotes2').value.trim()
        : document.getElementById('wiNotes').value.trim();

      const res = await fetch('/quewing_system/api/tickets/walk-in', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          service_id: serviceId,
          priority: _wiPriority,
          customer_name: isImported ? _selectedCustomer.name : document.getElementById('wiName').value.trim(),
          customer_phone: isImported ? (_selectedCustomer.phone || '') : document.getElementById('wiPhone').value.trim(),
          notes: notesVal,
          customer_id: isImported ? _selectedCustomer.id : null
        })
      });
      const data = await res.json();
      if (res.ok && data.success) {
        _lastTicket = data;
        showTicketResult(data);
      } else {
        alert(data.error || 'Failed to issue ticket.');
      }
    } catch (e) {
      alert('Connection error. Please try again.');
    }

    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-ticket-perforated"></i> Issue Ticket';
  }

  function showTicketResult(t) {
    document.getElementById('walkInForm').style.display = 'none';
    const el = document.getElementById('walkInResult');
    el.innerHTML = `
      <div style="font-size:0.75rem;color:var(--gray-400);text-transform:uppercase;letter-spacing:1.5px;font-weight:700;margin-bottom:10px;">Ticket Issued Successfully</div>
      <div style="font-size:3.2rem;font-weight:900;color:var(--green-dark);letter-spacing:2px;line-height:1;margin-bottom:18px;">${esc(t.ticket_number)}</div>
      <div style="background:var(--green-bg);border:1px solid #bbf7d0;border-radius:12px;padding:14px 20px;margin-bottom:14px;text-align:left;">
        <div class="wi-row"><span class="k">Service</span><span class="v">${esc(t.service_name)}</span></div>
        <div class="wi-row"><span class="k">Customer</span><span class="v">${esc(t.customer_name || 'Walk-in')}</span></div>
        <div class="wi-row"><span class="k">Priority</span><span class="v">${esc(t.priority)}</span></div>
        <div class="wi-row"><span class="k">Position in line</span><span class="v" style="color:var(--green-main);">#${t.queue_position}</span></div>
        <div class="wi-row"><span class="k">Waiting in branch</span><span class="v">${t.waiting_total}</span></div>
      </div>
      <div style="font-size:0.75rem;color:var(--gray-400);">Issued by ${esc(t.issued_by)} · ${esc(t.issued_at)}</div>`;
    el.style.display = 'block';
    document.getElementById('wiSubmitBtn').style.display = 'none';
    document.getElementById('wiPrintBtn').style.display = 'inline-flex';
    document.getElementById('wiAnotherBtn').style.display = 'inline-flex';
    loadDashboard();
  }

  function printLastTicket() {
    if (!_lastTicket) return;
    const t = _lastTicket;

    // Hidden iframe printing: reliable on real printers, no popup blockers
    let frame = document.getElementById('printFrame');
    if (frame) frame.remove();
    frame = document.createElement('iframe');
    frame.id = 'printFrame';
    frame.style.position = 'fixed';
    frame.style.right = '0';
    frame.style.bottom = '0';
    frame.style.width = '0';
    frame.style.height = '0';
    frame.style.border = '0';
    document.body.appendChild(frame);

    const sysName = document.querySelector('.topbar-title') ? document.querySelector('.topbar-title').textContent.trim() : 'SFI Queuing System';

    const doc = frame.contentWindow.document;
    doc.open();
    doc.write(`<!DOCTYPE html><html><head><title>${esc(t.ticket_number)}</title>
      <style>
        @page { size: 80mm auto; margin: 4mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
          font-family: 'Courier New', monospace;
          width: 72mm;
          text-align: center;
          color: #000;
        }
        .sys { font-size: 11px; font-weight: 700; letter-spacing: 1px; }
        .sub { font-size: 9px; color: #444; margin-top: 1px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .num { font-size: 30px; font-weight: 900; letter-spacing: 2px; margin: 5px 0; }
        table { width: 100%; margin-top: 2px; font-size: 10px; border-collapse: collapse; }
        td { padding: 2px 0; text-align: left; }
        td:last-child { text-align: right; font-weight: 700; }
        .foot { font-size: 9px; color: #333; margin-top: 8px; }
        .thanks { font-size: 10px; font-weight: 700; margin-top: 6px; }
      </style></head><body>
      <div class="sys">${esc(sysName)}</div>
      <div class="sub">WALK-IN TICKET</div>
      <div class="divider"></div>
      <div class="num">${esc(t.ticket_number)}</div>
      <div class="divider"></div>
      <table>
        <tr><td>Service</td><td>${esc(t.service_name)}</td></tr>
        <tr><td>Customer</td><td>${esc(t.customer_name || 'Walk-in')}</td></tr>
        <tr><td>Priority</td><td>${esc(t.priority)}</td></tr>
        <tr><td>Position</td><td>#${t.queue_position} in line</td></tr>
        <tr><td>Issued</td><td>${esc(t.issued_at)}</td></tr>
      </table>
      <div class="divider"></div>
      <div class="foot">Please wait for your number to be called.</div>
      <div class="thanks">Thank you!</div>
      </body></html>`);
    doc.close();

    // Wait for layout, then print; remove the iframe afterwards
    frame.contentWindow.focus();
    setTimeout(() => {
      frame.contentWindow.print();
      setTimeout(() => frame.remove(), 1000);
    }, 250);
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
