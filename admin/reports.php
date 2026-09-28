<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$active_page = 'reports';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reports — <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  /* FILTER BAR — Branch + Centro + Date lang */
  .filter-bar {
    background:#fff; border:1px solid var(--gray-200); border-radius:14px;
    padding:18px 20px; margin-bottom:20px;
    display:flex; align-items:flex-end; gap:14px; flex-wrap:wrap;
    box-shadow:0 1px 6px rgba(0,0,0,0.05);
  }
  .filter-group { display:flex; flex-direction:column; gap:5px; }
  .filter-label { font-size:0.7rem; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:0.8px; }
  .filter-input {
    border:1px solid var(--gray-200); border-radius:9px; padding:9px 12px;
    font-size:0.88rem; font-family:'Inter',sans-serif; font-weight:500; color:var(--gray-800);
    background:var(--gray-100); outline:none; transition:border 0.15s, background 0.15s;
  }
  .filter-input:focus { border-color:var(--green-main); background:#fff; }
  .btn-outline-gray {
    background:#fff; border:1.5px solid var(--gray-200); color:var(--gray-600);
    border-radius:10px; padding:9px 16px; font-size:0.85rem; font-weight:600;
    font-family:'Inter',sans-serif; cursor:pointer; transition:all 0.15s;
    display:inline-flex; align-items:center; gap:6px;
  }
  .btn-outline-gray:hover { border-color:var(--green-main); color:var(--green-main); }

  /* SUMMARY STRIP */
  .summary-strip {
    display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px;
  }
  .sum-card {
    background:#fff; border:1px solid var(--gray-200); border-radius:12px;
    padding:14px 22px; min-width:150px; box-shadow:0 1px 6px rgba(0,0,0,0.05);
  }
  .sum-num { font-size:1.6rem; font-weight:800; color:var(--green-dark); line-height:1.1; }
  .sum-lbl { font-size:0.7rem; font-weight:700; color:var(--gray-400); text-transform:uppercase; letter-spacing:0.8px; margin-top:3px; }

  /* STATUS PILL */
  .status-pill { font-size:0.7rem; font-weight:700; padding:3px 9px; border-radius:20px; }

  /* LOADING OVERLAY */
  .loading-strip {
    display:none; align-items:center; gap:8px;
    font-size:0.82rem; color:var(--gray-400); font-weight:600;
  }
  .loading-strip.show { display:inline-flex; }

  @media(max-width:768px){
    .filter-bar { align-items:stretch; flex-direction:column; }
    .filter-input { width:100%; }
  }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-bar-chart-line"></i> Reports</div>
    <div>
      <span class="loading-strip" id="loadingStrip">
        <span class="spinner-border spinner-border-sm"></span> Loading...
      </span>
      <button class="btn-outline-gray" onclick="exportCSV()">
        <i class="bi bi-download"></i> Export CSV
      </button>
    </div>
  </div>
  <div class="page-body">

    <!-- FILTERS: Branch + Centro + Date = automatic report -->
    <div class="filter-bar">
      <div class="filter-group">
        <span class="filter-label">Branch</span>
        <select id="branchFilter" class="filter-input" style="min-width:170px;">
          <option value="">All Branches</option>
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">Centro</span>
        <select id="centerFilter" class="filter-input" style="min-width:190px;">
          <option value="">All Centers</option>
        </select>
      </div>
      <div class="filter-group">
        <span class="filter-label">Date</span>
        <input type="date" id="reportDate" class="filter-input" value="<?= $today ?>">
      </div>
      <div style="flex:1;"></div>
      <div class="filter-group" style="justify-content:flex-end;">
        <span style="font-size:0.75rem;color:var(--gray-400);font-weight:600;display:inline-flex;align-items:center;gap:5px;">
          <i class="bi bi-lightning-charge" style="color:var(--green-main);"></i> Auto-update: change any filter
        </span>
      </div>
    </div>

    <!-- SUMMARY -->
    <div class="summary-strip" id="summaryStrip">
      <div class="sum-card">
        <div class="sum-num" id="sumTotal">0</div>
        <div class="sum-lbl">Transactions</div>
      </div>
      <div class="sum-card">
        <div class="sum-num" id="sumCompleted" style="color:var(--green-main);">0</div>
        <div class="sum-lbl">Completed</div>
      </div>
      <div class="sum-card">
        <div class="sum-num" id="sumWaiting" style="color:#f59e0b;">0</div>
        <div class="sum-lbl">Waiting / Serving</div>
      </div>
      <div class="sum-card">
        <div class="sum-num" id="sumNoShow" style="color:#ef4444;">0</div>
        <div class="sum-lbl">No Show</div>
      </div>
    </div>

    <!-- REPORT TABLE -->
    <div class="card-box">
      <div class="card-head">
        <h5><i class="bi bi-receipt"></i> Transaction Report</h5>
        <span class="badge-gray" id="reportBadge">—</span>
      </div>
      <div class="card-body-inner" style="padding:0;">
        <table class="table-modern">
          <thead><tr>
            <th>Ticket #</th>
            <th>Pangalan</th>
            <th>Centro</th>
            <th>Branch</th>
            <th>Service</th>
            <th>Oras</th>
            <th>Counter</th>
            <th>Status</th>
          </tr></thead>
          <tbody id="reportList">
            <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--gray-400);">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
const PENDING_MSG = '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--gray-400);">No transactions for the selected filters</td></tr>';

let state = { date: '<?= $today ?>', branch: '', center: '', data: null, centersLoaded: false };

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

async function api(url) {
  const res = await fetch(url);
  if (res.status === 401) {
    window.location.href = '/quewing_system/landing.php?expired=1';
    return null;
  }
  if (!res.ok) return null;
  return res.json();
}

function fmtTime(t) {
  const d = t.created_at ? new Date(t.created_at.replace(' ', 'T')) : null;
  if (!d || isNaN(d)) return '—';
  return d.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit', hour12: true });
}

const STATUS = {
  waiting:   ['var(--gray-100)', 'var(--gray-600)'],
  called:    ['#fffbeb', '#f59e0b'],
  serving:   ['#eff6ff', '#3b82f6'],
  completed: ['var(--green-pale)', 'var(--green-main)'],
  no_show:   ['#fef2f2', '#ef4444']
};

function fillSelect(sel, items, keepValue) {
  const prev = keepValue ? sel.value : '';
  sel.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
  items.forEach(v => {
    const opt = document.createElement('option');
    opt.value = v; opt.textContent = v;
    sel.appendChild(opt);
  });
  if (keepValue && prev && items.includes(prev)) sel.value = prev;
}

function render() {
  const data = state.data;
  const el = document.getElementById('reportList');
  const badge = document.getElementById('reportBadge');

  // dropdowns (populate once per load; keep user's current selection)
  if (data && Array.isArray(data.branches)) fillSelect(document.getElementById('branchFilter'), data.branches, true);
  if (data && Array.isArray(data.centers))  fillSelect(document.getElementById('centerFilter'), data.centers, true);

  if (!data || !Array.isArray(data.transactions)) {
    el.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:#ef4444;font-weight:600;">Failed to load report</td></tr>';
    badge.textContent = 'error';
    return;
  }

  const rows = data.transactions;
  const completed = rows.filter(r => r.status === 'completed').length;
  const noShow = rows.filter(r => r.status === 'no_show').length;
  const waiting = rows.length - completed - noShow;

  document.getElementById('sumTotal').textContent = rows.length;
  document.getElementById('sumCompleted').textContent = completed;
  document.getElementById('sumWaiting').textContent = waiting;
  document.getElementById('sumNoShow').textContent = noShow;

  const parts = [];
  if (state.branch) parts.push(state.branch);
  if (state.center) parts.push(state.center);
  badge.textContent = `${rows.length} transaction${rows.length === 1 ? '' : 's'} · ${state.date}${parts.length ? ' · ' + parts.join(' · ') : ''}`;

  el.innerHTML = rows.length === 0 ? PENDING_MSG : rows.map(t => {
    const st = t.status || 'waiting';
    const [bg, fg] = STATUS[st] || STATUS.waiting;
    const center = t.center_name || 'Walk-in / No center';
    const branch = t.branch || '—';
    return `<tr>
      <td style="font-weight:800;color:var(--green-dark);white-space:nowrap;">${esc(t.ticket_number)}</td>
      <td style="font-weight:600;">${esc(t.customer_name || 'Walk-in')}</td>
      <td>${esc(center)}</td>
      <td>${esc(branch)}</td>
      <td>${esc(t.service_name || '—')}</td>
      <td style="white-space:nowrap;">${fmtTime(t)}</td>
      <td>${t.counter_number ? 'Counter ' + esc(t.counter_number) : '—'}</td>
      <td><span class="status-pill" style="background:${bg};color:${fg};">${st.replace('_',' ')}</span></td>
    </tr>`;
  }).join('');
}

async function loadReport() {
  document.getElementById('loadingStrip').classList.add('show');
  const qs = `date=${state.date}&branch=${encodeURIComponent(state.branch)}&center=${encodeURIComponent(state.center)}`;
  const data = await api(`/quewing_system/api/reports/transactions.php?${qs}`);
  state.data = data;
  document.getElementById('loadingStrip').classList.remove('show');
  render();
}

// AUTO-UPDATE: kahit anong filter, automatic nagre-refresh ang report
document.getElementById('branchFilter').addEventListener('change', e => {
  state.branch = e.target.value;
  loadReport();
});
document.getElementById('centerFilter').addEventListener('change', e => {
  state.center = e.target.value;
  loadReport();
});
document.getElementById('reportDate').addEventListener('change', e => {
  if (!e.target.value) return;
  state.date = e.target.value;
  loadReport();
});

loadReport();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
