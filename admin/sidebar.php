<?php
// Usage: include this file inside <body>, set $active_page before including
// e.g. $active_page = 'services';
if (!isset($active_page)) $active_page = '';
?>
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

  /* SIDEBAR */
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
    display: flex; align-items: center; gap: 12px;
    position: relative; z-index: 1;
    text-decoration: none;
  }
  .sidebar-logo {
    width: 42px; height: 42px;
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #fff;
    border: 1.5px solid rgba(255,255,255,0.2);
    flex-shrink: 0;
  }
  .sidebar-brand-text {
    font-size: 0.82rem; font-weight: 800; color: #fff; letter-spacing: 0.5px; line-height: 1.3;
  }
  .sidebar-brand-text span {
    display: block; font-size: 0.7rem; font-weight: 400; color: rgba(255,255,255,0.55); margin-top: 2px;
  }
  .sidebar-nav {
    flex: 1; padding: 16px 12px; overflow-y: auto; position: relative; z-index: 1;
  }
  .sidebar-label {
    font-size: 0.65rem; font-weight: 700; color: rgba(255,255,255,0.4);
    text-transform: uppercase; letter-spacing: 1.5px; padding: 12px 12px 6px;
  }
  .nav-link-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border-radius: 10px;
    color: rgba(255,255,255,0.75);
    font-size: 0.88rem; font-weight: 500;
    text-decoration: none; transition: all 0.2s; margin-bottom: 2px;
  }
  .nav-link-item:hover { background: rgba(255,255,255,0.1); color: #fff; }
  .nav-link-item.active { background: rgba(255,255,255,0.18); color: #fff; font-weight: 600; }
  .nav-link-item i { width: 20px; font-size: 1rem; text-align: center; flex-shrink: 0; }
  .sidebar-footer {
    padding: 16px 12px; border-top: 1px solid rgba(255,255,255,0.1); position: relative; z-index: 1;
  }
  .user-card {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px; background: rgba(255,255,255,0.08); border-radius: 10px; margin-bottom: 8px;
  }
  .user-avatar {
    width: 34px; height: 34px; background: rgba(255,255,255,0.2); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 0.85rem; color: #fff; flex-shrink: 0;
  }
  .user-info { flex: 1; min-width: 0; }
  .user-name { font-size: 0.82rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .user-role { font-size: 0.7rem; color: rgba(255,255,255,0.5); }
  .btn-logout {
    width: 100%; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
    color: rgba(255,255,255,0.75); border-radius: 8px; padding: 8px;
    font-size: 0.82rem; font-weight: 500; font-family: 'Inter', sans-serif; cursor: pointer;
    transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none;
  }
  .btn-logout:hover { background: rgba(255,255,255,0.15); color: #fff; }

  /* MAIN */
  .main-content { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
  .topbar {
    background: var(--white); border-bottom: 1px solid var(--gray-200);
    padding: 0 32px; height: 64px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 40;
    box-shadow: 0 1px 8px rgba(0,0,0,0.04);
  }
  .topbar-title { font-size: 1.1rem; font-weight: 700; color: var(--green-dark); display: flex; align-items: center; gap: 8px; }
  .topbar-title i { color: var(--green-main); }
  .page-body { padding: 32px; flex: 1; }

  /* CARD */
  .card-box {
    background: var(--white); border-radius: 16px;
    border: 1px solid var(--gray-200); box-shadow: 0 1px 8px rgba(0,0,0,0.05); overflow: hidden;
  }
  .card-head {
    padding: 20px 24px 16px; border-bottom: 1px solid var(--gray-100);
    display: flex; align-items: center; justify-content: space-between;
  }
  .card-head h5 { font-size: 0.95rem; font-weight: 700; color: var(--green-dark); margin: 0; display: flex; align-items: center; gap: 8px; }
  .card-head h5 i { color: var(--green-main); }
  .card-body-inner { padding: 24px; }

  /* TABLE */
  .table-modern { width: 100%; border-collapse: collapse; }
  .table-modern thead th {
    font-size: 0.75rem; font-weight: 700; color: var(--gray-400);
    text-transform: uppercase; letter-spacing: 0.8px;
    padding: 12px 16px; border-bottom: 2px solid var(--gray-100); background: var(--gray-50);
  }
  .table-modern tbody td { padding: 14px 16px; border-bottom: 1px solid var(--gray-100); font-size: 0.88rem; vertical-align: middle; }
  .table-modern tbody tr:last-child td { border-bottom: none; }
  .table-modern tbody tr:hover { background: var(--green-bg); }

  /* BADGES */
  .badge-green  { background: var(--green-pale); color: var(--green-main); font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
  .badge-gray   { background: var(--gray-100); color: var(--gray-600); font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
  .badge-red    { background: #fef2f2; color: #ef4444; font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
  .badge-blue   { background: #eff6ff; color: #3b82f6; font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
  .badge-amber  { background: #fffbeb; color: #f59e0b; font-size: 0.72rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }

  /* BUTTONS */
  .btn-primary-green {
    background: linear-gradient(135deg, var(--green-main), var(--green-dark));
    color: #fff; border: none; border-radius: 10px;
    padding: 10px 20px; font-size: 0.88rem; font-weight: 600;
    font-family: 'Inter', sans-serif; cursor: pointer; transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(22,163,74,0.25);
  }
  .btn-primary-green:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(22,163,74,0.35); }
  .btn-icon {
    width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; transition: all 0.2s;
  }
  .btn-icon.edit   { background: #eff6ff; color: #3b82f6; }
  .btn-icon.edit:hover { background: #dbeafe; }
  .btn-icon.delete { background: #fef2f2; color: #ef4444; }
  .btn-icon.delete:hover { background: #fee2e2; }
  .btn-icon.key    { background: #fffbeb; color: #f59e0b; }
  .btn-icon.key:hover { background: #fef3c7; }

  /* ALERT */
  .alert-success-green {
    background: var(--green-pale); border: 1px solid #bbf7d0; color: var(--green-mid);
    border-radius: 10px; padding: 10px 16px; font-size: 0.85rem; font-weight: 500;
    display: none; align-items: center; gap: 8px; margin-bottom: 16px;
  }
  .alert-success-green.show { display: flex; }

  @media (max-width: 768px) {
    .sidebar { transform: translateX(-100%); }
    .main-content { margin-left: 0; }
    .page-body { padding: 16px; }
  }
</style>

<aside class="sidebar">
  <a href="/quewing_system/admin/dashboard.php" class="sidebar-brand">
    <div class="sidebar-logo"><i class="bi bi-ticket-perforated-fill"></i></div>
    <div class="sidebar-brand-text">
      <?php echo htmlspecialchars($system_name); ?>
      <span>SFI Queuing System</span>
    </div>
  </a>
  <nav class="sidebar-nav">
    <div class="sidebar-label">Main Menu</div>
    <a href="/quewing_system/admin/dashboard.php" class="nav-link-item <?= $active_page==='dashboard' ? 'active' : '' ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="/quewing_system/admin/services.php" class="nav-link-item <?= $active_page==='services' ? 'active' : '' ?>">
      <i class="bi bi-list-task"></i> Services
    </a>
    <a href="/quewing_system/admin/counters.php" class="nav-link-item <?= $active_page==='counters' ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2"></i> Counters
    </a>
    <a href="/quewing_system/admin/users.php" class="nav-link-item <?= $active_page==='users' ? 'active' : '' ?>">
      <i class="bi bi-people"></i> Users
    </a>
    <div class="sidebar-label" style="margin-top:8px;">Analytics</div>
    <a href="/quewing_system/admin/reports.php" class="nav-link-item <?= $active_page==='reports' ? 'active' : '' ?>">
      <i class="bi bi-bar-chart-line"></i> Reports
    </a>
    <div class="sidebar-label" style="margin-top:8px;">System</div>
    <a href="/quewing_system/admin/import_clients.php" class="nav-link-item <?= $active_page==='import_clients' ? 'active' : '' ?>">
      <i class="bi bi-cloud-arrow-up"></i> Import Client Information
    </a>
    <a href="/quewing_system/admin/settings.php" class="nav-link-item <?= $active_page==='settings' ? 'active' : '' ?>>
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
