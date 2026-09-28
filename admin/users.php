<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$active_page = 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Users — <?php echo htmlspecialchars($system_name); ?></title>
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
    min-width: 200px;
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
    <div class="topbar-title"><i class="bi bi-people"></i> Users</div>
  </div>
  <div class="page-body">

    <div class="alert-success-green" id="userAlert">
      <i class="bi bi-check-circle-fill"></i><span id="alertMsg">Done</span>
    </div>

    <div class="card-box">
      <div class="card-head">
        <h5><i class="bi bi-people"></i> Users Management</h5>
        <button class="btn-primary-green" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
          <i class="bi bi-plus-lg"></i> Add User
        </button>
      </div>
      <div class="card-body-inner" style="padding:0;">
        <table class="table-modern">
          <thead>
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Full Name</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="usersBody">
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--gray-400);">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- USER MODAL -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <form id="userForm">
        <div class="modal-header" style="border-bottom:1px solid var(--gray-100);padding:20px 24px;">
          <h5 class="modal-title" style="font-weight:700;color:var(--green-dark);" id="userModalTitle">Add User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:24px;">
          <input type="hidden" id="userId">
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Username</label>
            <input class="form-control" id="userUsername" required placeholder="e.g. jdelacruz" style="border-radius:10px;">
          </div>
          <div class="row g-3 mb-3">
            <div class="col">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">First Name</label>
              <input class="form-control" id="userFirstName" placeholder="Juan" style="border-radius:10px;">
            </div>
            <div class="col">
              <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Last Name</label>
              <input class="form-control" id="userLastName" placeholder="Dela Cruz" style="border-radius:10px;">
            </div>
          </div>
          <div class="mb-3">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Role</label>
            <select class="form-select" id="userRole" style="border-radius:10px;">
              <option value="counter">Counter Staff</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="mb-1">
            <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">Password <span id="pwHint" style="font-weight:400;color:var(--gray-400);">(leave blank to keep)</span></label>
            <input type="password" class="form-control" id="userPassword" placeholder="••••••••" style="border-radius:10px;">
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--gray-100);padding:16px 24px;gap:8px;">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
          <button type="submit" class="btn-primary-green">Save User</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div class="modal fade" id="resetModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content" style="border-radius:16px;border:none;">
      <form id="resetForm">
        <div class="modal-header" style="border-bottom:1px solid var(--gray-100);padding:20px 24px;">
          <h5 class="modal-title" style="font-weight:700;color:var(--green-dark);">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" style="padding:24px;">
          <input type="hidden" id="resetUserId">
          <label style="font-size:0.82rem;font-weight:600;color:var(--gray-800);display:block;margin-bottom:6px;">New Password</label>
          <input type="password" class="form-control" id="resetPassword" required placeholder="••••••••" style="border-radius:10px;">
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--gray-100);padding:16px 24px;gap:8px;">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
          <button type="submit" class="btn-primary-green">Reset</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
let _users = [];

async function api(url, method='GET', body=null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(url, opts);
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.error) {
    throw new Error(data.error || ('Request failed (' + res.status + ')'));
  }
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
  if (act === 'edit') editUser(id);
  else if (act === 'reset') openReset(id);
  else deleteUser(id);
}

document.addEventListener('click', closeMenus);
window.addEventListener('scroll', closeMenus, true);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenus(); });

async function loadUsers() {
  const data = await api('/quewing_system/api/users/index.php');
  _users = data.users || [];
  const tbody = document.getElementById('usersBody');
  if (_users.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--gray-400);">No users found</td></tr>';
    return;
  }
  tbody.innerHTML = _users.map((u, i) => `
    <tr>
      <td style="color:var(--gray-400);font-weight:600;">${i+1}</td>
      <td style="font-weight:600;">${esc(u.username)}</td>
      <td>${esc(u.first_name || '')} ${esc(u.last_name || '')}</td>
      <td>${u.role === 'admin'
        ? '<span class="badge-red">Admin</span>'
        : '<span class="badge-blue">Counter Staff</span>'}</td>
      <td>${u.is_active
        ? '<span class="badge-green">Active</span>'
        : '<span class="badge-gray">Inactive</span>'}</td>
      <td style="text-align:center;">
        <div class="actions-wrap">
          <button type="button" class="btn-icon dots" onclick="toggleMenu(event, this)" title="Actions">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
          <div class="actions-menu">
            <button type="button" class="edit-hover" onclick="menuAction(event, 'edit', ${u.id})">
              <i class="bi bi-pencil"></i> Edit User
            </button>
            <button type="button" class="warn-hover" onclick="menuAction(event, 'reset', ${u.id})">
              <i class="bi bi-key"></i> Reset Password
            </button>
            <button type="button" class="danger" onclick="menuAction(event, 'delete', ${u.id})">
              <i class="bi bi-trash"></i> Delete User
            </button>
          </div>
        </div>
      </td>
    </tr>`).join('');
}

function resetForm() {
  document.getElementById('userId').value = '';
  document.getElementById('userForm').reset();
  document.getElementById('userModalTitle').textContent = 'Add User';
  document.getElementById('pwHint').style.display = 'inline';
  document.getElementById('userUsername').removeAttribute('readonly');
}

function editUser(id) {
  const u = _users.find(x => x.id === id);
  if (!u) return;
  document.getElementById('userId').value = u.id;
  document.getElementById('userUsername').value = u.username;
  document.getElementById('userUsername').setAttribute('readonly', true);
  document.getElementById('userFirstName').value = u.first_name || '';
  document.getElementById('userLastName').value = u.last_name || '';
  document.getElementById('userRole').value = u.role;
  document.getElementById('userPassword').value = '';
  document.getElementById('userModalTitle').textContent = 'Edit User';
  new bootstrap.Modal(document.getElementById('userModal')).show();
}

function openReset(id) {
  document.getElementById('resetUserId').value = id;
  document.getElementById('resetPassword').value = '';
  new bootstrap.Modal(document.getElementById('resetModal')).show();
}

async function deleteUser(id) {
  const u = _users.find(x => x.id === id);
  const name = u ? (u.first_name ? u.first_name + ' ' + u.last_name : '@' + u.username) : 'this user';
  if (!confirm('Delete ' + name + '? This cannot be undone.')) return;
  try {
    await api(`/quewing_system/api/users/delete.php?id=${id}`, 'DELETE');
    showAlert('User deleted');
    loadUsers();
  } catch (err) {
    showAlert(err.message, true);
  }
}

document.getElementById('userForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  try {
    const id = document.getElementById('userId').value;
    const data = {
      first_name: document.getElementById('userFirstName').value,
      last_name: document.getElementById('userLastName').value,
      role: document.getElementById('userRole').value
    };
    if (!id) data.username = document.getElementById('userUsername').value;
    const pw = document.getElementById('userPassword').value;
    if (pw) data.password = pw;
    const url = id
      ? `/quewing_system/api/users/update.php?id=${id}`
      : '/quewing_system/api/users/create.php';
    const method = id ? 'PATCH' : 'POST';
    await api(url, method, data);
    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
    showAlert(id ? 'User updated!' : 'User created!');
    loadUsers();
  } catch (err) {
    alert(err.message);
  }
});

document.getElementById('resetForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  try {
    const id = document.getElementById('resetUserId').value;
    const pw = document.getElementById('resetPassword').value;
    if (!pw) { alert('Please enter a new password.'); return; }
    await api(`/quewing_system/api/users/update.php?id=${id}`, 'PATCH', { password: pw });
    bootstrap.Modal.getInstance(document.getElementById('resetModal')).hide();
    showAlert('Password reset!');
  } catch (err) {
    alert(err.message);
  }
});

function showAlert(msg, isError = false) {
  const a = document.getElementById('userAlert');
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

loadUsers();
setInterval(loadUsers, 15000);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
