<?php
require_once __DIR__ . '/../config.php';
require_login();
if ($_SESSION['user_role'] !== 'counter') {
    http_response_code(403);
    exit('Forbidden');
}
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$counter_id = (int)($_SESSION['counter_id'] ?? 0);
$counter_number = $_SESSION['counter_number'] ?? null;
$staff_name = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Counter Dashboard - <?php echo htmlspecialchars($system_name); ?></title>
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
    --gray-300:     #cbd5e1;
    --gray-400:     #94a3b8;
    --gray-600:     #475569;
    --gray-800:     #1e293b;
  }

  body {
    font-family: 'Inter', 'Segoe UI', sans-serif;
    background: var(--gray-50);
    color: var(--gray-800);
  }

  /* ── NAVBAR ── */
  .navbar-top {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255,255,255,0.92);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(22,163,74,0.12);
    padding: 0 40px;
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 1px 0 rgba(22,163,74,0.06);
  }
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
  .nav-counter {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .counter-chip {
    background: var(--green-pale);
    color: var(--green-dark);
    border: 1px solid rgba(22,163,74,0.2);
    border-radius: 50px;
    padding: 7px 16px;
    font-size: 0.85rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .btn-logout {
    background: #fff;
    border: 1.5px solid var(--gray-100);
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--gray-600);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
  }
  .btn-logout:hover {
    border-color: #ef4444;
    color: #ef4444;
    background: #fef2f2;
  }

  /* ── CONTENT ── */
  .content-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 28px 40px 60px;
  }

  /* ── STAT TILES ── */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 20px;
  }
  .stat-tile {
    background: var(--white);
    border: 1px solid var(--gray-100);
    border-radius: 18px;
    padding: 18px 20px;
    box-shadow: 0 4px 18px rgba(22,163,74,0.06);
    position: relative;
    overflow: hidden;
  }
  .stat-tile::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
  }
  .stat-tile.t-green::before  { background: linear-gradient(90deg, var(--green-main), var(--green-light)); }
  .stat-tile.t-blue::before   { background: linear-gradient(90deg, #0ea5e9, #38bdf8); }
  .stat-tile.t-amber::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
  .stat-tile.t-gray::before   { background: linear-gradient(90deg, #64748b, #94a3b8); }
  .stat-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    margin-bottom: 10px;
  }
  .t-green .stat-icon { background: var(--green-pale); color: var(--green-main); }
  .t-blue  .stat-icon { background: #e0f2fe; color: #0ea5e9; }
  .t-amber .stat-icon { background: #fef3c7; color: #f59e0b; }
  .t-gray  .stat-icon { background: var(--gray-100); color: var(--gray-600); }
  .stat-value {
    font-size: 1.7rem;
    font-weight: 900;
    color: var(--gray-800);
    line-height: 1;
  }
  .stat-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-top: 6px;
  }

  /* ── NOW SERVING CARD ── */
  .now-serving-card {
    background: var(--white);
    border-radius: 24px;
    border: 1px solid var(--gray-100);
    box-shadow: 0 12px 40px rgba(22,163,74,0.08);
    padding: 44px 32px;
    text-align: center;
    min-height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
  }
  .now-serving-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--green-main), var(--green-light));
  }
  .serving-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 12px;
  }
  .current-ticket {
    font-size: 4.6rem;
    font-weight: 900;
    color: var(--green-dark);
    letter-spacing: 2px;
    line-height: 1;
    margin-bottom: 20px;
  }
  .customer-info {
    background: var(--green-pale);
    border: 1px solid #bbf7d0;
    border-radius: 14px;
    padding: 16px 22px;
    width: 100%;
    max-width: 420px;
  }
  .customer-info .name {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--green-dark);
    margin-bottom: 4px;
  }
  .customer-info .meta {
    font-size: 0.85rem;
    color: var(--gray-600);
  }
  .customer-info .meta i { margin-right: 6px; color: var(--green-main); }

  /* ── ACTION BUTTONS ── */
  .actions-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-top: 20px;
  }
  .btn-action {
    border: none;
    border-radius: 14px;
    padding: 18px 12px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    font-family: 'Inter', sans-serif;
  }
  .btn-action i { font-size: 1.2rem; }
  .btn-action:hover { transform: translateY(-2px); }
  .btn-action:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    transform: none !important;
  }
  .btn-call {
    background: linear-gradient(135deg, var(--green-main), var(--green-light));
    box-shadow: 0 8px 22px rgba(22,163,74,0.35);
  }
  .btn-call:hover { box-shadow: 0 12px 30px rgba(22,163,74,0.45); }
  .btn-complete {
    background: linear-gradient(135deg, #0ea5e9, #38bdf8);
    box-shadow: 0 8px 22px rgba(14,165,233,0.3);
  }
  .btn-noshow {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    box-shadow: 0 8px 22px rgba(245,158,11,0.3);
  }
  .btn-pause {
    background: linear-gradient(135deg, #64748b, #94a3b8);
    box-shadow: 0 8px 22px rgba(100,116,139,0.3);
  }

  /* ── SIDE CARDS ── */
  .side-card {
    background: var(--white);
    border-radius: 20px;
    border: 1px solid var(--gray-100);
    box-shadow: 0 8px 28px rgba(22,163,74,0.06);
    padding: 22px;
    margin-bottom: 20px;
  }
  .side-card-title {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--green-dark);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .side-card-title i { color: var(--green-main); }

  /* ── MY SESSION ── */
  .session-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 0;
    border-bottom: 1px dashed var(--gray-100);
    font-size: 0.85rem;
  }
  .session-row:last-child { border-bottom: none; }
  .session-row .sk { color: var(--gray-400); font-weight: 600; }
  .session-row .sv { font-weight: 700; color: var(--gray-800); }
  .admin-badge {
    background: var(--green-pale);
    color: var(--green-main);
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .no-counter-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 0.85rem;
    color: #92400e;
    font-weight: 600;
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }

  /* ── QUEUE PREVIEW ── */
  .queue-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 12px;
    margin-bottom: 8px;
    background: var(--gray-50);
    border: 1px solid var(--gray-100);
    transition: all 0.2s;
  }
  .queue-item .qnum {
    width: 44px; height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    background: var(--green-pale);
    color: var(--green-dark);
    font-weight: 800;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .queue-item .qinfo { min-width: 0; flex: 1; }
  .queue-item .qinfo .qn {
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--gray-800);
    margin-bottom: 2px;
  }
  .queue-item .qinfo .qc {
    font-size: 0.78rem;
    color: var(--gray-400);
  }
  .queue-item.next {
    background: var(--green-pale);
    border-color: #bbf7d0;
  }
  .queue-item.next .qn { color: var(--green-dark); }
  .prio-badge {
    font-size: 0.62rem;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    flex-shrink: 0;
  }
  .prio-senior   { background: #fef3c7; color: #b45309; }
  .prio-pwd      { background: #dbeafe; color: #1d4ed8; }
  .prio-pregnant { background: #fce7f3; color: #be185d; }
  .empty-queue {
    color: var(--gray-400);
    font-size: 0.85rem;
    text-align: center;
    padding: 16px;
  }

  /* ── STATUS ── */
  .status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 50px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 700;
  }
  .status-pill .dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: currentColor;
  }
  .status-available {
    background: var(--green-pale);
    color: var(--green-dark);
    border: 1px solid #bbf7d0;
  }
  .status-paused {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fde68a;
  }
  .status-busy {
    background: #e0f2fe;
    color: #075985;
    border: 1px solid #bae6fd;
  }

  /* ── TOAST ── */
  .toast-wrap {
    position: fixed;
    bottom: 28px; right: 28px;
    z-index: 200;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .app-toast {
    background: var(--green-dark);
    color: #fff;
    border-radius: 14px;
    padding: 14px 22px;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 12px 32px rgba(10,74,40,0.4);
    transform: translateX(120%);
    transition: transform 0.3s;
  }
  .app-toast.error { background: #b91c1c; box-shadow: 0 12px 32px rgba(185,28,28,0.4); }
  .app-toast.show { transform: translateX(0); }

  @media (max-width: 992px) {
    .actions-row { grid-template-columns: repeat(2, 1fr); }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .content-wrap { padding: 20px 20px 40px; }
    .navbar-top { padding: 0 20px; }
    .current-ticket { font-size: 3.2rem; }
  }
</style>
</head>
<body>

<nav class="navbar-top">
  <a class="nav-brand" href="#">
    <div class="nav-logo"><i class="bi bi-ticket-perforated-fill"></i></div>
    <div class="nav-name"><?php echo htmlspecialchars($system_name); ?></div>
  </a>
  <div class="nav-counter">
    <span class="counter-chip" id="counterChip">
      <i class="bi bi-person-workspace"></i><?php echo $counter_number ? 'Counter ' . (int)$counter_number : 'Unassigned'; ?>
    </span>
    <a href="/quewing_system/api/auth/logout" class="btn-logout"
       onclick="event.preventDefault(); if(confirm('Logout?')) window.location.href=this.href;">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</nav>

<div class="content-wrap">

  <!-- ── STATS ROW ── -->
  <div class="stats-grid">
    <div class="stat-tile t-green">
      <div class="stat-icon"><i class="bi bi-check2-circle"></i></div>
      <div class="stat-value" id="statServed">—</div>
      <div class="stat-label">Served Today</div>
    </div>
    <div class="stat-tile t-blue">
      <div class="stat-icon"><i class="bi bi-stopwatch"></i></div>
      <div class="stat-value" id="statAvg" style="font-size:1.25rem;padding-top:6px;">—</div>
      <div class="stat-label">Avg Service Time</div>
    </div>
    <div class="stat-tile t-amber">
      <div class="stat-icon"><i class="bi bi-person-x"></i></div>
      <div class="stat-value" id="statNoShow">—</div>
      <div class="stat-label">No-Shows Today</div>
    </div>
    <div class="stat-tile t-gray">
      <div class="stat-icon"><i class="bi bi-people"></i></div>
      <div class="stat-value" id="statWaiting">—</div>
      <div class="stat-label">Waiting (Branch)</div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="now-serving-card">
        <div class="serving-label">NOW SERVING</div>
        <div class="current-ticket" id="currentTicket">—</div>
        <div class="customer-info" id="customerInfo" style="display:none;">
          <div class="name" id="custName"></div>
          <div class="meta" id="custPhone"></div>
          <div class="meta" id="custNotes"></div>
        </div>
      </div>
      <div class="actions-row">
        <button class="btn-action btn-call" id="callBtn" onclick="callNext()"><i class="bi bi-play-fill"></i> Call Next</button>
        <button class="btn-action btn-complete" id="completeBtn" onclick="completeService()"><i class="bi bi-check-circle"></i> Complete</button>
        <button class="btn-action btn-noshow" id="noshowBtn" onclick="markNoShow()"><i class="bi bi-person-x"></i> No Show</button>
        <button class="btn-action btn-pause" id="pauseBtn" onclick="togglePause()"><i class="bi bi-pause-fill"></i> Pause</button>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="side-card">
        <div class="side-card-title"><i class="bi bi-person-badge"></i> My Session</div>
        <div id="sessionInfo">
          <div class="session-row">
            <span class="sk">Staff</span>
            <span class="sv"><?php echo htmlspecialchars($staff_name); ?></span>
          </div>
          <div class="session-row">
            <span class="sk">Counter</span>
            <span class="sv" id="sessCounter">—</span>
          </div>
          <div class="session-row">
            <span class="sk">Assignment</span>
            <span class="sv" id="sessAssign">—</span>
          </div>
          <div class="session-row">
            <span class="sk">Status</span>
            <span class="sv"><span class="status-pill status-available" id="counterStatus" style="padding:5px 12px;font-size:0.78rem;"><span class="dot"></span>Available</span></span>
          </div>
        </div>
        <div class="no-counter-box" id="noCounterBox" style="display:none;margin-top:10px;">
          <i class="bi bi-exclamation-triangle" style="margin-top:2px;"></i>
          <span>No counter assigned to you. Please ask the admin to assign one — you cannot call tickets until then.</span>
        </div>
      </div>
      <div class="side-card">
        <div class="side-card-title"><i class="bi bi-list-ol"></i> Queue Preview</div>
        <div id="queuePreview"><div class="empty-queue">Loading...</div></div>
      </div>
    </div>
  </div>
</div>

<div class="toast-wrap" id="toastWrap"></div>

<script>
let counterId = <?php echo (int)$counter_id; ?>;
let isPaused = false;
let mySession = null;

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

async function api(url, method='GET', body=null) {
    const opts = {method, headers:{'Content-Type':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(url, opts);
    if (res.status === 401) { window.location.href = '/quewing_system/landing.php?expired=1'; }
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
}

function showToast(msg, isError = false) {
    const wrap = document.getElementById('toastWrap');
    const t = document.createElement('div');
    t.className = 'app-toast' + (isError ? ' error' : '');
    t.textContent = msg;
    wrap.appendChild(t);
    setTimeout(() => t.classList.add('show'), 10);
    setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3200);
}

function requireCounter() {
    if (!counterId) {
        showToast('No counter assigned — please contact admin.', true);
        return false;
    }
    return true;
}

/* ── MY SESSION (counter + stats) ── */
async function loadSession() {
    try {
        const data = await api('/quewing_system/api/counters/my');
        mySession = data;

        // Live adoption: admin reassignments propagate instantly
        if (data.counter) {
            counterId = data.counter.id;
            document.getElementById('counterChip').innerHTML =
                '<i class="bi bi-person-workspace"></i>Counter ' + data.counter.counter_number;
            document.getElementById('sessCounter').textContent = 'Counter ' + data.counter.counter_number;
            document.getElementById('sessAssign').innerHTML = data.counter.assigned_by_admin
                ? '<span class="admin-badge">Admin Assigned</span>'
                : 'Auto (first free)';
        } else {
            counterId = 0;
            document.getElementById('counterChip').innerHTML =
                '<i class="bi bi-exclamation-triangle"></i>Unassigned';
            document.getElementById('sessCounter').textContent = '—';
            document.getElementById('sessAssign').textContent = 'None';
        }
        document.getElementById('noCounterBox').style.display = data.counter ? 'none' : 'flex';

        // Paused state sync (survives page refresh)
        isPaused = !!(data.staff && data.staff.paused);
        updatePauseBtn();

        // Stats
        document.getElementById('statServed').textContent = data.stats ? data.stats.served : '—';
        document.getElementById('statNoShow').textContent = data.stats ? data.stats.no_show : '—';
        document.getElementById('statWaiting').textContent = data.waiting_total ?? '—';
        const avg = data.stats && data.stats.avg_duration !== null && data.stats.avg_duration !== undefined ? data.stats.avg_duration : null;
        document.getElementById('statAvg').textContent = avg !== null
            ? (avg >= 60 ? Math.floor(avg/60) + 'm ' + (avg%60) + 's' : avg + 's')
            : '—';
    } catch (e) { /* keep last view */ }
}

function updatePauseBtn() {
    const btn = document.getElementById('pauseBtn');
    if (!btn) return;
    if (!counterId) {
        btn.disabled = true;
        return;
    }
    btn.disabled = false;
    btn.innerHTML = isPaused
        ? '<i class="bi bi-play-fill"></i> Resume'
        : '<i class="bi bi-pause-fill"></i> Pause';
}

/* ── QUEUE ── */
const PRIO_LABEL = {
    senior:   '<span class="prio-badge prio-senior">Senior</span>',
    pwd:      '<span class="prio-badge prio-pwd">PWD</span>',
    pregnant: '<span class="prio-badge prio-pregnant">Pregnant</span>'
};

async function loadQueue() {
    try {
        const data = await api('/quewing_system/api/tickets/queue');
        const el = document.getElementById('queuePreview');
        const current = data.current;
        if (data.tickets && data.tickets.length > 0) {
            el.innerHTML = data.tickets.map((t, i) => {
                const isCurrent = current && t.id === current.id;
                const isNext = !isCurrent && i === 0 ? false : (!current && i === 0);
                return `
                <div class="queue-item ${isCurrent ? 'next' : ''}">
                    <div class="qnum">${i + 1}</div>
                    <div class="qinfo">
                        <div class="qn">${esc(t.ticket_number)}${isCurrent ? ' <i class="bi bi-broadcast" style="color:var(--green-main);font-size:0.7rem;" title="Now serving"></i>' : ''}</div>
                        <div class="qc">${esc(t.service_name || '')}${t.customer_name ? ' · ' + esc(t.customer_name) : ''}</div>
                    </div>
                    ${PRIO_LABEL[t.priority] || ''}
                </div>`;
            }).join('');
        } else {
            el.innerHTML = '<div class="empty-queue">No tickets in queue</div>';
        }
    } catch (e) { /* keep last view */ }
}

async function loadCurrentTicket() {
    try {
        const data = await api('/quewing_system/api/tickets/queue');
        const el = document.getElementById('currentTicket');
        const info = document.getElementById('customerInfo');
        if (data.current && data.current.ticket_number) {
            el.textContent = data.current.ticket_number;
            info.style.display = 'block';
            document.getElementById('custName').textContent = data.current.customer_name || 'Walk-in';
            document.getElementById('custPhone').textContent = data.current.customer_phone ? '📞 ' + data.current.customer_phone : '📞 —';
            document.getElementById('custNotes').textContent = data.current.notes ? '📝 ' + data.current.notes : '';
        } else {
            el.textContent = '—';
            info.style.display = 'none';
        }
    } catch (e) { /* keep last view */ }
}

/* ── ACTIONS ── */
async function callNext() {
    if (!requireCounter()) return;
    if (isPaused) { showToast('Your counter is paused — resume first.', true); return; }
    const btn = document.getElementById('callBtn');
    btn.disabled = true;
    try {
        const data = await api('/quewing_system/api/tickets/queue');
        const callable = (data.tickets || []).find(t => t.status === 'waiting' || t.status === 'called');
        if (callable) {
            await api(`/quewing_system/api/tickets/${callable.id}/call`, 'PATCH');
            showToast('Called ' + callable.ticket_number);
        } else {
            showToast('No waiting tickets in the queue.');
        }
        await Promise.all([loadQueue(), loadCurrentTicket(), loadSession()]);
    } catch (e) { showToast(e.message, true); }
    btn.disabled = false;
}

async function completeService() {
    if (!requireCounter()) return;
    try {
        const data = await api('/quewing_system/api/tickets/queue');
        const cur = data.current;
        if (cur && cur.status === 'serving') {
            await api(`/quewing_system/api/tickets/${cur.id}/complete`, 'PATCH');
            showToast('Completed ' + cur.ticket_number);
        } else {
            showToast('No ticket is currently serving.');
        }
        await Promise.all([loadQueue(), loadCurrentTicket(), loadSession()]);
    } catch (e) { showToast(e.message, true); }
}

async function markNoShow() {
    if (!requireCounter()) return;
    try {
        const data = await api('/quewing_system/api/tickets/queue');
        const cur = data.current;
        if (cur && cur.status === 'serving') {
            await api(`/quewing_system/api/tickets/${cur.id}/no-show`, 'PATCH');
            showToast('Marked no-show ' + cur.ticket_number);
        } else {
            showToast('No ticket is currently serving.');
        }
        await Promise.all([loadQueue(), loadCurrentTicket(), loadSession()]);
    } catch (e) { showToast(e.message, true); }
}

async function togglePause() {
    if (!requireCounter()) return;
    try {
        await api('/quewing_system/api/counters/' + counterId, 'PATCH', {status: isPaused ? 'available' : 'paused'});
        isPaused = !isPaused;
        updatePauseBtn();
        showToast(isPaused ? 'Counter paused.' : 'Counter resumed.');
        loadSession();
    } catch (e) { showToast(e.message, true); }
}

/* ── POLLS ── */
setInterval(loadQueue, 3000);
setInterval(loadCurrentTicket, 3000);
setInterval(loadSession, 5000);
loadQueue();
loadCurrentTicket();
loadSession();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
