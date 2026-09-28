<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name     = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$operating_hours = get_setting($pdo, 'operating_hours', '8:00 AM - 5:00 PM');
$max_tickets     = get_setting($pdo, 'max_tickets_per_day', '500');
$ticket_prefix   = get_setting($pdo, 'ticket_prefix', 'SFI');
$welcome_msg     = get_setting($pdo, 'display_welcome_message', 'Welcome! Please wait for your number to be called.');
$footer_msg      = get_setting($pdo, 'display_footer_message', 'Please remain seated and wait for your number to be called. Thank you.');
$voice_tpl       = get_setting($pdo, 'display_voice_message', 'Attention please. Ticket {number}. {name}. Please proceed to the counter.');
$active_page     = 'settings';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings — <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-gear"></i> Settings</div>
  </div>
  <div class="page-body">

    <div style="max-width:600px;">

      <div class="alert-success-green" id="settingsAlert">
        <i class="bi bi-check-circle-fill"></i><span>Settings saved successfully!</span>
      </div>

      <div class="card-box">
        <div class="card-head">
          <h5><i class="bi bi-sliders"></i> System Configuration</h5>
        </div>
        <div class="card-body-inner">
          <form id="settingsForm">

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-type" style="color:var(--green-main);margin-right:6px;"></i>System Name
              </label>
              <input class="form-control" id="settingSystemName"
                value="<?php echo htmlspecialchars($system_name); ?>"
                placeholder="e.g. SFI Queuing System" style="border-radius:10px;">
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-clock" style="color:var(--green-main);margin-right:6px;"></i>Operating Hours
              </label>
              <input class="form-control" id="settingHours"
                value="<?php echo htmlspecialchars($operating_hours); ?>"
                placeholder="e.g. 8:00 AM - 5:00 PM" style="border-radius:10px;">
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-123" style="color:var(--green-main);margin-right:6px;"></i>Max Tickets Per Day
              </label>
              <input type="number" class="form-control" id="settingMaxTickets"
                value="<?php echo htmlspecialchars($max_tickets); ?>"
                min="1" placeholder="e.g. 500" style="border-radius:10px;">
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-ticket-perforated" style="color:var(--green-main);margin-right:6px;"></i>Ticket Prefix
              </label>
              <input class="form-control" id="settingPrefix"
                value="<?php echo htmlspecialchars($ticket_prefix); ?>"
                placeholder="e.g. SFI" style="border-radius:10px;max-width:160px;">
              <div style="font-size:0.75rem;color:var(--gray-400);margin-top:6px;">
                Preview: <strong><?php echo htmlspecialchars($ticket_prefix); ?>-0924-001</strong>
              </div>
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-tv" style="color:var(--green-main);margin-right:6px;"></i>TV Display — Welcome Message (ticker)
              </label>
              <input class="form-control" id="settingWelcomeMsg"
                value="<?php echo htmlspecialchars($welcome_msg); ?>"
                maxlength="200" placeholder="Shown sa ticker kapag walang queue activity" style="border-radius:10px;">
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-tv" style="color:var(--green-main);margin-right:6px;"></i>TV Display — Footer Message
              </label>
              <input class="form-control" id="settingFooterMsg"
                value="<?php echo htmlspecialchars($footer_msg); ?>"
                maxlength="200" placeholder="Permanent na text sa pinaka-ibaba ng TV" style="border-radius:10px;">
            </div>

            <div class="mb-4">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">
                <i class="bi bi-volume-up" style="color:var(--green-main);margin-right:6px;"></i>Voice Announcement Script
              </label>
              <input class="form-control" id="settingVoiceMsg"
                value="<?php echo htmlspecialchars($voice_tpl); ?>"
                maxlength="300" placeholder="Attention please. Ticket {number}. {name}. Please proceed to the counter." style="border-radius:10px;">
              <div style="font-size:0.75rem;color:var(--gray-400);margin-top:6px;">
                Available placeholders: <code>{number}</code> ticket number · <code>{name}</code> pangalan ng customer · <code>{service}</code> serbisyo
              </div>
            </div>

            <button type="submit" class="btn-primary-green">
              <i class="bi bi-floppy"></i> Save Settings
            </button>

          </form>
        </div>
      </div>
    </div>

    <!-- ACTIVITY LOGS SECTION -->
    <div class="card-box" style="margin-top:24px;">
      <div class="card-head">
        <h5><i class="bi bi-activity"></i> Activity Logs</h5>
        <div style="display:flex;align-items:center;gap:10px;">
          <select id="logLimit" class="filter-select" onchange="loadActivityLogs()">
            <option value="20">Last 20</option>
            <option value="50">Last 50</option>
            <option value="100">Last 100</option>
            <option value="250">Last 250</option>
          </select>
          <button class="btn-primary-green" style="padding:8px 16px;font-size:0.82rem;" onclick="loadActivityLogs()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
          </button>
        </div>
      </div>
      <div class="card-body-inner" id="activityLogs" style="padding:8px 24px;max-height:600px;overflow-y:auto;">
        <div class="empty-state"><i class="bi bi-hourglass-split"></i>Loading...</div>
      </div>
    </div>

  </div>
</div>

<style>
  .filter-select {
    border: 1.5px solid var(--gray-200);
    border-radius: 9px;
    padding: 8px 12px;
    font-size: 0.85rem;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    color: var(--gray-800);
    background: #fff;
    outline: none;
    cursor: pointer;
  }
  .log-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 14px 0;
    border-bottom: 1px solid var(--gray-100);
  }
  .log-item:last-child { border-bottom: none; }
  .log-icon {
    width: 36px; height: 36px;
    background: var(--green-pale);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--green-main);
    font-size: 0.9rem;
    flex-shrink: 0;
  }
  .log-action {
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--gray-800);
  }
  .log-detail {
    font-size: 0.8rem;
    color: var(--gray-600);
    margin-top: 2px;
  }
  .log-user {
    font-size: 0.72rem;
    color: var(--gray-400);
    font-weight: 600;
    margin-top: 3px;
  }
  .log-time {
    font-size: 0.72rem;
    color: var(--gray-400);
    white-space: nowrap;
    margin-left: auto;
    flex-shrink: 0;
    text-align: right;
  }
  .log-badge {
    display: inline-block;
    font-size: 0.62rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-left: 8px;
  }
  .log-badge.green { background: var(--green-pale); color: var(--green-main); }
  .log-badge.red   { background: #fef2f2; color: #ef4444; }
  .log-badge.blue  { background: #eff6ff; color: #3b82f6; }
  .log-badge.amber { background: #fffbeb; color: #f59e0b; }
  .log-badge.gray  { background: var(--gray-100); color: var(--gray-600); }
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
</style>

<script>
function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

const LOG_META = {
  'login':           { icon: 'bi-box-arrow-in-right', badge: 'green',  label: 'Login' },
  'logout':          { icon: 'bi-box-arrow-left',    badge: 'gray',    label: 'Logout' },
  'issue_walk_in':   { icon: 'bi-ticket-perforated', badge: 'blue',   label: 'Walk-in Ticket' },
  'create_ticket':   { icon: 'bi-plus-circle',       badge: 'blue',   label: 'New Ticket' },
  'call_ticket':     { icon: 'bi-broadcast',         badge: 'amber',  label: 'Called' },
  'complete_ticket': { icon: 'bi-check-circle',      badge: 'green',  label: 'Completed' },
  'no_show':         { icon: 'bi-person-x',          badge: 'red',    label: 'No Show' },
  'create_counter':  { icon: 'bi-plus-circle',       badge: 'blue',   label: 'Counter Created' },
  'update_counter':  { icon: 'bi-pencil',            badge: 'amber',  label: 'Counter Updated' },
  'delete_counter':  { icon: 'bi-trash',             badge: 'red',    label: 'Counter Deleted' },
  'assign_staff':    { icon: 'bi-person-check',      badge: 'blue',   label: 'Staff Assigned' }
};

async function loadActivityLogs() {
  const el = document.getElementById('activityLogs');
  const limit = document.getElementById('logLimit').value;
  try {
    const data = await api(`/quewing_system/api/audit_logs.php?limit=${limit}`);
    const logs = data.logs || [];
    if (logs.length === 0) {
      el.innerHTML = '<div class="empty-state"><i class="bi bi-inbox"></i>No activity recorded yet</div>';
      return;
    }
    el.innerHTML = logs.map(l => {
      const meta = LOG_META[l.action] || { icon: 'bi-circle', badge: 'gray', label: null };
      const user = (l.first_name || l.username) ? (l.first_name ? l.first_name + ' ' + l.last_name : l.username) : 'System';
      const actionLabel = meta.label || esc(l.action);
      return `<div class="log-item">
        <div class="log-icon"><i class="bi ${meta.icon}"></i></div>
        <div style="flex:1;min-width:0;">
          <div class="log-action">${actionLabel}<span class="log-badge ${meta.badge}">${esc(l.action)}</span></div>
          <div class="log-detail">${esc(l.details || '')}</div>
          <div class="log-user"><i class="bi bi-person" style="margin-right:4px;"></i>${esc(user)}</div>
 </div>
        <div class="log-time">${esc(l.created_at || '')}</div>
      </div>`;
    }).join('');
  } catch (e) {
    el.innerHTML = '<div class="empty-state"><i class="bi bi-wifi-off"></i>Failed to load activity logs</div>';
  }
}

loadActivityLogs();
</script>
<script>
async function api(url, method='GET', body=null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  return fetch(url, opts).then(r => r.json());
}

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const data = {
    system_name:          document.getElementById('settingSystemName').value,
    operating_hours:      document.getElementById('settingHours').value,
    max_tickets_per_day:  document.getElementById('settingMaxTickets').value,
    ticket_prefix:        document.getElementById('settingPrefix').value,
    display_welcome_message: document.getElementById('settingWelcomeMsg').value,
    display_footer_message:  document.getElementById('settingFooterMsg').value,
    display_voice_message:   document.getElementById('settingVoiceMsg').value
  };
  await api('/quewing_system/api/settings.php', 'PATCH', data);
  const a = document.getElementById('settingsAlert');
  a.classList.add('show');
  setTimeout(() => a.classList.remove('show'), 3000);
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
