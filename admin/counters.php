<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$active_page = 'counters';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Counters — <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  /* KEBAB ACTION MENU */
  .actions-wrap { position: relative; display: inline-block; }
  .btn-icon.dots {
    width: 34px; height: 34px;
    background: var(--gray-100);
    color: var(--gray-600);
  }
  .btn-icon.dots:hover { background: var(--gray-200); color: var(--gray-800); }
  .actions-menu {
    position: fixed;
    min-width: 180px;
    background: #fff;
    border: 1px solid var(--gray-200);
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.14);
    padding: 6px;
    z-index: 90;
    display: none;
  }
  .actions-menu button {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: none; border: none; border-radius: 8px; padding: 10px 12px;
    font-size: 0.85rem; font-weight: 600; font-family: 'Inter', sans-serif;
    color: var(--gray-800); cursor: pointer; text-align: left; white-space: nowrap;
    transition: background 0.12s;
  }
  .actions-menu button:hover { background: var(--gray-100); }
  .actions-menu button:hover.edit-hover { background: #eff6ff; color: #3b82f6; }
  .actions-menu button:hover.warn-hover { background: #fffbeb; color: #f59e0b; }
  .actions-menu button.danger { color: #ef4444; }
  .actions-menu button.danger:hover { background: #fef2f2; }
  .actions-menu button i { width: 16px; text-align: center; font-size: 0.95rem; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-grid-1x2"></i> Counters</div>
  </div>
  <div class="page-body">

    <div class="alert-success-green" id="counterAlert">
      <i class="bi bi-check-circle-fill"></i><span id="alertMsg">Done</span>
    </div>

    <div class="card-box">
      <div class="card-head">
        <h5><i class="bi bi-grid-1x2"></i> Counters Management</h5>
        <button class="btn-primary-green" data-bs-toggle="modal" data-bs-target="#counterModal" onclick="resetForm()">
          <i class="bi bi-plus-lg"></i> Add Counter
        </button>
      </div>
      <div class="card-body-inner" style="padding:0;">
        <table class="table-modern">
          <thead>
            <tr>
              <th>#</th>
              <th>Counter No.</th>
              <th>Status</th>
              <th>Assigned Staff</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="countersBody">
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400);">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="counterModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <form id="counterForm">
        <div class="modal-header" style="border-bottom:1px solid var(--gray-100);padding:20px 24px;">
          <h5 class="modal-title" style="font-weight:700;color:var(--green-dark);" id="counterModalTitle">Add Counter</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:24px;">
          <input type="hidden" id="counterId">
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Counter Number</label>
            <input type="number" class="form-control" id="counterNumber" required placeholder="e.g. 1" style="border-radius:10px;">
          </div>
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Assign Staff</label>
            <select class="form-select" id="counterStaff" style="border-radius:10px;">
              <option value="">None</option>
            </select>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--gray-100);padding:16px 24px;gap:8px;">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
          <button type="submit" class="btn-primary-green">Save Counter</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let _counters = [];
let _users = [];

const STATUS_LABELS = {
  available: '<span class="badge-green">Available</span>',
  busy:      '<span class="badge-blue">Busy</span>',
  paused:    '<span class="badge-amber">Paused</span>'
};

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

/* ── KEBAB MENU ── */
function toggleMenu(e, btn) {
  e.stopPropagation();
  const menu = btn.closest('.actions-wrap').querySelector('.actions-menu');
  const wasOpen = menu.style.display === 'block';
  closeMenus();
  if (wasOpen) return;

  // Fixed against the viewport so the table can't clip it
  menu.style.display = 'block';
  const r = btn.getBoundingClientRect();
  const mw = menu.offsetWidth;
  const mh = menu.offsetHeight;
  let top = r.bottom + 6;
  let left = r.right - mw;
  if (top + mh > window.innerHeight) top = r.top - mh - 6;
  if (left < 8) left = 8;
  if (left + mw > window.innerWidth) left = window.innerWidth - mw - 8;
  menu.style.top = top + 'px';
  menu.style.left = left + 'px';
}

function closeMenus() {
  document.querySelectorAll('.actions-menu').forEach(m => m.style.display = 'none');
}

function menuAction(e, act, id) {
  e.stopPropagation();
  closeMenus();
  if (act === 'edit') editCounter(id);
  else if (act === 'unassign') unassignCounter(id);
  else deleteCounter(id);
}

document.addEventListener('click', closeMenus);
window.addEventListener('scroll', closeMenus, true);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });

async function api(url, method='GET', body=null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  if (res.status === 401) { window.location.href = '/quewing_system/landing.php?expired=1'; }
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.error || `Request failed (${res.status})`);
    err.status = res.status;
    throw err;
  }
  return data;
}

async function loadUsers() {
  const data = await api('/quewing_system/api/users/index.php');
  _users = (data.users || []).filter(u => u.role === 'counter' && u.is_active == 1);
  const sel = document.getElementById('counterStaff');
  sel.innerHTML = '<option value="">None</option>' + _users.map(u =>
    `<option value="${u.id}">${esc(u.first_name)} ${esc(u.last_name)} (${esc(u.username)})</option>`
  ).join('');
}

async function loadCounters() {
  const data = await api('/quewing_system/api/counters/index.php');
  _counters = data.counters || [];
  const tbody = document.getElementById('countersBody');
  if (_counters.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400);">No counters found</td></tr>';
    return;
  }
  tbody.innerHTML = _counters.map((c, i) => {
    return `<tr>
      <td style="color:var(--gray-400);font-weight:600;">${i+1}</td>
      <td style="font-weight:700;font-size:1rem;">Counter ${c.counter_number}</td>
      <td>${STATUS_LABELS[c.status] || `<span class="badge-gray">${esc(c.status)}</span>`}</td>
      <td>${c.first_name ? esc(c.first_name + ' ' + c.last_name) : '<span style="color:var(--gray-400);">Unassigned</span>'}</td>
      <td style="text-align:center;">
        <div class="actions-wrap">
          <button type="button" class="btn-icon dots" onclick="toggleMenu(event, this)" title="Actions">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <div class="actions-menu">
            <button type="button" class="edit-hover" onclick="menuAction(event, 'edit', ${c.id})">
              <i class="bi bi-pencil"></i> Edit Counter
            </button>
            ${c.first_name ? `
            <button type="button" class="warn-hover" onclick="menuAction(event, 'unassign', ${c.id})">
              <i class="bi bi-person-dash"></i> Unassign Staff
            </button>` : ''}
            <button type="button" class="danger" onclick="menuAction(event, 'delete', ${c.id})">
              <i class="bi bi-trash"></i> Delete Counter
            </button>
          </div>
        </div>
      </td>
    </tr>`;
  }).join('');
}

function resetForm() {
  document.getElementById('counterId').value = '';
  document.getElementById('counterForm').reset();
  document.getElementById('counterModalTitle').textContent = 'Add Counter';
}

function editCounter(id) {
  const c = _counters.find(x => x.id === id);
  if (!c) return;
  document.getElementById('counterId').value = c.id;
  document.getElementById('counterNumber').value = c.counter_number;
  document.getElementById('counterStaff').value = c.staff_id || '';
  document.getElementById('counterModalTitle').textContent = 'Edit Counter';
  new bootstrap.Modal(document.getElementById('counterModal')).show();
}

async function deleteCounter(id) {
  const c = _counters.find(x => x.id === id);
  const name = c ? 'Counter ' + c.counter_number : 'this counter';
  if (!confirm('Delete ' + name + '?')) return;
  try {
    await api(`/quewing_system/api/counters/delete.php?id=${id}`, 'DELETE');
    showAlert('Counter deleted');
  } catch (err) {
    showAlert(err.message, true);
  }
  loadCounters();
}

async function unassignCounter(id) {
  const c = _counters.find(x => x.id === id);
  const name = c ? 'Counter ' + c.counter_number : 'this counter';
  if (!confirm('Remove the assigned staff from ' + name + '?')) return;
  try {
    await api(`/quewing_system/api/counters/update.php?id=${id}`, 'PATCH', { staff_id: null });
    showAlert('Staff unassigned from ' + name);
  } catch (err) {
    showAlert(err.message, true);
  }
  loadCounters();
}

document.getElementById('counterForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const id = document.getElementById('counterId').value;
  const data = {
    counter_number: document.getElementById('counterNumber').value,
    staff_id: document.getElementById('counterStaff').value || null
  };
  const url = id
    ? `/quewing_system/api/counters/update.php?id=${id}`
    : '/quewing_system/api/counters/create.php';
  const method = id ? 'PATCH' : 'POST';
  const btn = this.querySelector('button[type="submit"]');
  btn.disabled = true;
  try {
    await api(url, method, data);
    bootstrap.Modal.getInstance(document.getElementById('counterModal')).hide();
    showAlert(id ? 'Counter updated!' : 'Counter created!');
  } catch (err) {
    showAlert(err.message, true);
  } finally {
    btn.disabled = false;
  }
  loadCounters();
});

function showAlert(msg, isError = false) {
  const a = document.getElementById('counterAlert');
  const icon = a.querySelector('i');
  document.getElementById('alertMsg').textContent = msg;
  a.style.background = isError ? '#fef2f2' : '';
  a.style.borderColor = isError ? '#fecaca' : '';
  a.style.color = isError ? '#dc2626' : '';
  if (icon) icon.className = isError ? 'bi bi-exclamation-triangle-fill' : 'bi bi-check-circle-fill';
  a.classList.add('show');
  clearTimeout(showAlert._t);
  showAlert._t = setTimeout(() => a.classList.remove('show'), 4000);
}

let _countersVisible = true;
document.addEventListener('visibilitychange', () => {
  _countersVisible = !document.hidden;
  if (_countersVisible) loadCounters();
});

loadUsers();
loadCounters();
setInterval(() => { if (_countersVisible) loadCounters(); }, 5000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
