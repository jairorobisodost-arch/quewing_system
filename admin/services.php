<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$active_page = 'services';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services — <?php echo htmlspecialchars($system_name); ?></title>
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
  .actions-menu button.danger { color: #ef4444; }
  .actions-menu button.danger:hover { background: #fef2f2; }
  .actions-menu button i { width: 16px; text-align: center; font-size: 0.95rem; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-list-task"></i> Services</div>
  </div>
  <div class="page-body">

    <div class="alert-success-green" id="servicesAlert">
      <i class="bi bi-check-circle-fill"></i><span id="alertMsg">Done</span>
    </div>

    <div class="card-box">
      <div class="card-head">
        <h5><i class="bi bi-list-task"></i> Services Management</h5>
        <button class="btn-primary-green" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="resetForm()">
          <i class="bi bi-plus-lg"></i> Add Service
        </button>
      </div>
      <div class="card-body-inner" style="padding:0;">
        <table class="table-modern" id="servicesTable">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Avg Time</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="servicesBody">
            <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400);">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div class="modal fade" id="serviceModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <form id="serviceForm">
        <div class="modal-header" style="border-bottom:1px solid var(--gray-100);padding:20px 24px;">
          <h5 class="modal-title" style="font-weight:700;color:var(--green-dark);" id="serviceModalTitle">Add Service</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:24px;">
          <input type="hidden" id="serviceId">
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Service Name</label>
            <input class="form-control" id="serviceName" placeholder="e.g. Cash Payment" required style="border-radius:10px;">
          </div>
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Avg. Time (minutes)</label>
            <input type="number" class="form-control" id="serviceAvgTime" min="0" placeholder="e.g. 5" style="border-radius:10px;">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--gray-100);padding:16px 24px;gap:8px;">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
          <button type="submit" class="btn-primary-green">Save Service</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let _services = [];

async function api(url, method='GET', body=null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || 'Request failed (' + res.status + ')');
  return data;
}

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
  if (act === 'edit') editService(id);
  else deleteService(id);
}

document.addEventListener('click', closeMenus);
window.addEventListener('scroll', closeMenus, true);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });

async function loadServices() {
  const data = await api('/quewing_system/api/services/index.php');
  _services = data.services || [];
  const tbody = document.getElementById('servicesBody');
  if (_services.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray-400);">No services found</td></tr>';
    return;
  }
  tbody.innerHTML = _services.map((s, i) => `
    <tr>
      <td style="color:var(--gray-400);font-weight:600;">${i+1}</td>
      <td style="font-weight:600;">${esc(s.name)}</td>
      <td>${s.avg_time ? s.avg_time + ' min' : '—'}</td>
      <td>${s.is_active
        ? '<span class="badge-green">Active</span>'
        : '<span class="badge-gray">Inactive</span>'}</td>
      <td style="text-align:center;">
        <div class="actions-wrap">
          <button type="button" class="btn-icon dots" onclick="toggleMenu(event, this)" title="Actions">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <div class="actions-menu">
            <button type="button" class="edit-hover" onclick="menuAction(event, 'edit', ${s.id})">
              <i class="bi bi-pencil"></i> Edit Service
            </button>
            <button type="button" class="danger" onclick="menuAction(event, 'delete', ${s.id})">
              <i class="bi bi-trash"></i> Delete Service
            </button>
          </div>
        </div>
      </td>
    </tr>`).join('');
}

function resetForm() {
  document.getElementById('serviceId').value = '';
  document.getElementById('serviceForm').reset();
  document.getElementById('serviceModalTitle').textContent = 'Add Service';
}

function editService(id) {
  const s = _services.find(x => x.id === id);
  if (!s) return;
  document.getElementById('serviceId').value = s.id;
  document.getElementById('serviceName').value = s.name;
  document.getElementById('serviceAvgTime').value = s.avg_time || '';
  document.getElementById('serviceModalTitle').textContent = 'Edit Service';
  new bootstrap.Modal(document.getElementById('serviceModal')).show();
}

async function deleteService(id) {
  const s = _services.find(x => x.id === id);
  const name = s ? s.name : 'this service';
  if (!confirm('Delete "' + name + '"?')) return;
  try {
    await api(`/quewing_system/api/services/delete.php?id=${id}`, 'DELETE');
    showAlert('Service deleted');
  } catch (err) {
    showAlert(err.message, true);
  }
  loadServices();
}

document.getElementById('serviceForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const id = document.getElementById('serviceId').value;
  const data = {
    name: document.getElementById('serviceName').value,
    avg_time: document.getElementById('serviceAvgTime').value
  };
  const url = id
    ? `/quewing_system/api/services/update.php?id=${id}`
    : '/quewing_system/api/services/create.php';
  const method = id ? 'PATCH' : 'POST';
  try {
    await api(url, method, data);
    bootstrap.Modal.getInstance(document.getElementById('serviceModal')).hide();
    showAlert(id ? 'Service updated!' : 'Service created!');
  } catch (err) {
    showAlert(err.message, true);
  }
  loadServices();
});

function showAlert(msg, isError = false) {
  const a = document.getElementById('servicesAlert');
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

loadServices();
setInterval(loadServices, 15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
