<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('admin');
$system_name = get_setting($pdo, 'system_name', 'SFI QUEUING SYSTEM');
$active_page = 'import_clients';
$all_branches = [];
$bs = $pdo->query("SELECT DISTINCT branch FROM customers WHERE branch IS NOT NULL AND branch <> '' ORDER BY branch");
while ($br = $bs->fetch(PDO::FETCH_ASSOC)) {
    $all_branches[] = $br['branch'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Import Client Information — <?php echo htmlspecialchars($system_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  .upload-wrap { max-width: 720px; }

  .drop-zone {
    border: 2px dashed var(--gray-200);
    border-radius: 16px;
    padding: 56px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: var(--gray-50);
  }
  .drop-zone:hover { border-color: var(--green-light); }
  .drop-zone.dragover { border-color: var(--green-main); background: var(--green-bg); }
  .dz-icon {
    width: 64px; height: 64px;
    background: var(--green-pale);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; color: var(--green-main);
    margin: 0 auto 14px;
  }

  .file-chip {
    display: none;
    align-items: center;
    gap: 12px;
    background: var(--green-bg);
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    padding: 14px 18px;
  }
  .file-chip.show { display: flex; }

  .result-panel { display: none; border-radius: 12px; padding: 16px 20px; }
  .result-panel.show { display: block; }
  .result-ok   { background: var(--green-bg); border: 1px solid #bbf7d0; color: var(--green-mid); }
  .result-err  { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }

  .log-row { cursor: pointer; transition: background 0.15s; }
  .log-row:hover { background: var(--green-bg) !important; }
  .log-row:hover td:first-child { color: var(--green-main) !important; }

  /* DETAIL MODAL */
  #detailModal {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 999;
    align-items: flex-start; justify-content: center;
    padding: 40px 16px; overflow-y: auto;
  }
  .detail-box {
    background: #fff; border-radius: 20px; width: 100%;
    max-width: 1100px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    overflow: hidden;
  }
  .detail-head {
    background: linear-gradient(135deg, var(--green-dark), var(--green-main));
    padding: 24px 28px; color: #fff;
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
  }
  .detail-head h5 { font-weight: 800; font-size: 1rem; margin: 0 0 4px; }
  .detail-meta { font-size: 0.8rem; opacity: 0.8; }
  .detail-close {
    position: relative; z-index: 10;
    background: rgba(255,255,255,0.15); border: none; color: #fff;
    width: 36px; height: 36px; border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    line-height: 1; padding: 0; transition: background 0.15s;
  }
  .detail-close:hover { background: rgba(255,255,255,0.3); }
  .detail-stats {
    display: grid; grid-template-columns: repeat(4,1fr); gap: 12px;
    padding: 20px 28px; border-bottom: 1px solid var(--gray-100);
    background: var(--gray-50);
  }
  .detail-stat { background: #fff; border-radius: 10px; padding: 12px 16px; border: 1px solid var(--gray-200); }
  .detail-stat-label { font-size: 0.68rem; font-weight: 700; color: var(--gray-400); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
  .detail-stat-value { font-size: 1.1rem; font-weight: 800; color: var(--gray-800); }

  .detail-search {
    padding: 16px 28px; border-bottom: 1px solid var(--gray-100);
    display: flex; align-items: center; gap: 10px;
  }
  .detail-search input {
    flex: 1; border: 1.5px solid var(--gray-200); border-radius: 10px;
    padding: 9px 14px; font-size: 0.88rem; font-family: 'Inter', sans-serif; outline: none;
  }
  .detail-search input:focus { border-color: var(--green-main); }

  .detail-table-wrap { overflow-x: auto; max-height: 460px; overflow-y: auto; }

  /* ACTION DROPDOWN (gear) */
  .actions-wrap { position: relative; display: inline-block; }
  .btn-icon.gear { background: var(--gray-100); color: var(--gray-600); }
  .btn-icon.gear:hover { background: var(--gray-200); }
  .actions-menu {
    position: absolute; top: calc(100% + 6px); right: 0; min-width: 168px;
    background: #fff; border: 1px solid var(--gray-200); border-radius: 12px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.14); padding: 6px; z-index: 60;
    display: none;
  }
  .actions-menu.up { top: auto; bottom: calc(100% + 6px); }
  .actions-menu button {
    display: flex; align-items: center; gap: 10px; width: 100%;
    background: none; border: none; border-radius: 8px; padding: 9px 12px;
    font-size: 0.83rem; font-weight: 600; font-family: 'Inter', sans-serif;
    color: var(--gray-800); cursor: pointer; text-align: left; white-space: nowrap;
  }
  .actions-menu button:hover { background: var(--gray-100); }
  .actions-menu button.danger { color: #ef4444; }
  .actions-menu button.danger:hover { background: #fef2f2; }
  .actions-menu button i { width: 16px; text-align: center; font-size: 0.9rem; }

  /* EDIT MODAL */
  #editModal {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.5); z-index: 1000;
    align-items: flex-start; justify-content: center;
    padding: 90px 16px 40px;
  }
  .edit-box {
    background: #fff; border-radius: 18px; width: 100%;
    max-width: 440px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    overflow: hidden;
  }
  .edit-head {
    background: linear-gradient(135deg, var(--green-dark), var(--green-main));
    padding: 20px 22px; color: #fff;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
  }
  .edit-head h5 { font-weight: 800; font-size: 1rem; margin: 0; }
  .edit-head-close { background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; padding:4px; line-height:1; }
  .edit-body { padding: 22px; }
  .edit-field { margin-bottom: 18px; }
  .edit-field label {
    display: block; font-size: 0.72rem; font-weight: 700;
    color: var(--gray-500); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 6px;
  }
  .edit-field input, .edit-field select {
    width: 100%; border: 1.5px solid var(--gray-200); border-radius: 10px;
    padding: 10px 12px; font-size: 0.9rem; font-family: 'Inter', sans-serif; outline: none;
    background: #fff; color: var(--gray-800);
  }
  .edit-field input:focus, .edit-field select:focus { border-color: var(--green-main); }
  .edit-foot { padding: 14px 22px; border-top: 1px solid var(--gray-100); display: flex; justify-content: flex-end; gap: 10px; }
  .btn-cancel { background: var(--gray-100); border: none; border-radius: 10px; padding: 10px 20px; font-size: 0.85rem; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; color: var(--gray-700); }
  .btn-save { background: var(--green-main); border: none; border-radius: 10px; padding: 10px 24px; font-size: 0.85rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; color: #fff; }
  .btn-save:hover { background: var(--green-dark); }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-title"><i class="bi bi-upload"></i> Import Client Information</div>
    <button class="btn-primary-green" onclick="loadLogs()">
      <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
  </div>
  <div class="page-body">

    <!-- UPLOAD CARD -->
    <div class="upload-wrap">
      <div class="card-box">
        <div class="card-head">
          <h5><i class="bi bi-cloud-arrow-up"></i> Upload Client CSV</h5>
        </div>
        <div class="card-body-inner">

          <div class="drop-zone" id="dropZone"
               onclick="document.getElementById('csvFileInput').click()"
               ondragover="event.preventDefault(); this.classList.add('dragover');"
               ondragleave="this.classList.remove('dragover');"
               ondrop="handleDrop(event)">
            <div class="dz-icon"><i class="bi bi-file-earmark-spreadsheet"></i></div>
            <div style="font-weight:700;color:var(--gray-800);margin-bottom:4px;">
              Click to browse or drag &amp; drop
            </div>
            <div style="font-size:0.8rem;color:var(--gray-400);">
              CSV file only (UTF-8 encoded, exported from Excel)
            </div>
            <input type="file" id="csvFileInput" accept=".csv" style="display:none;" onchange="handleFileSelect(this)">
          </div>

          <div class="file-chip" id="fileChip" style="margin-top:16px;">
            <i class="bi bi-file-earmark-check" style="color:var(--green-main);font-size:1.3rem;"></i>
            <span id="fileChipName" style="font-size:0.85rem;font-weight:600;color:var(--gray-800);flex:1;"></span>
            <button onclick="clearFile()" style="background:none;border:none;color:var(--gray-400);cursor:pointer;font-size:0.95rem;">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div class="result-panel" id="resultPanel" style="margin-top:16px;"></div>

          <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
            <button class="btn-primary-green" id="importBtn" onclick="submitImport()" disabled
              style="opacity:0.5;cursor:not-allowed;">
              <i class="bi bi-upload"></i> Start Import
            </button>
          </div>

        </div>
      </div>
    </div>

    <!-- IMPORT HISTORY (BELOW) -->
    <div style="margin-top:28px;">

      <!-- SUMMARY CARDS -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
        <div class="card-box" style="padding:20px 24px;">
          <div style="font-size:0.72rem;font-weight:700;color:var(--gray-400);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Total Uploads</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--gray-800);" id="statTotal">—</div>
        </div>
        <div class="card-box" style="padding:20px 24px;">
          <div style="font-size:0.72rem;font-weight:700;color:var(--green-main);text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Total Imported Records</div>
          <div style="font-size:1.8rem;font-weight:800;color:var(--green-main);" id="statImported">—</div>
        </div>
        <div class="card-box" style="padding:20px 24px;">
          <div style="font-size:0.72rem;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:6px;">Total Skipped Rows</div>
          <div style="font-size:1.8rem;font-weight:800;color:#f59e0b;" id="statSkipped">—</div>
        </div>
      </div>

      <!-- HISTORY TABLE -->
      <div class="card-box">
        <div class="card-head">
          <h5><i class="bi bi-clock-history"></i> Import History</h5>
          <span style="font-size:0.75rem;color:var(--gray-400);" id="logCount">Loading...</span>
        </div>
        <div class="card-body-inner" style="padding:0;">
          <table class="table-modern">
            <thead>
              <tr>
                <th>#</th>
                <th>File Name</th>
                <th>Date & Time</th>
                <th>Uploaded By</th>
                <th>Branch</th>
                <th style="text-align:center;">Imported</th>
                <th style="text-align:center;">Skipped</th>
                <th style="text-align:center;">Action</th>
              </tr>
            </thead>
            <tbody id="logsBody">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--gray-400);">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- CUSTOMER DETAIL MODAL -->
<div id="detailModal">
  <div class="detail-box">
    <div class="detail-head">
      <div>
        <h5 id="detailTitle">Import Details</h5>
        <div class="detail-meta" id="detailMeta"></div>
      </div>
      <button type="button" class="detail-close" data-close-detail title="Close"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="detail-stats">
      <div class="detail-stat">
        <div class="detail-stat-label">File</div>
        <div class="detail-stat-value" id="detailFile" style="font-size:0.82rem;word-break:break-all;">—</div>
      </div>
      <div class="detail-stat">
        <div class="detail-stat-label">Uploaded By</div>
        <div class="detail-stat-value" id="detailUploader" style="font-size:0.9rem;">—</div>
      </div>
      <div class="detail-stat">
        <div class="detail-stat-label" style="color:var(--green-main);">Imported</div>
        <div class="detail-stat-value" id="detailImported" style="color:var(--green-main);">—</div>
      </div>
      <div class="detail-stat">
        <div class="detail-stat-label" style="color:#f59e0b;">Branch</div>
        <select id="detailBranch" title="Filter clients by branch" style="font-size:0.9rem;color:#f59e0b;font-weight:700;font-family:'Inter',sans-serif;border:1.5px solid #fde68a;border-radius:8px;background:#fffbeb;padding:6px 8px;cursor:pointer;outline:none;min-width:160px;">
          <option value="">— All branches —</option>
        </select>
      </div>
    </div>

    <div class="detail-search">
      <i class="bi bi-search" style="color:var(--gray-400);"></i>
      <input type="text" id="clientSearch" placeholder="Search by name, client ID, branch..." oninput="filterClients()">
      <span id="clientCount" style="font-size:0.78rem;color:var(--gray-400);white-space:nowrap;"></span>
    </div>

    <div class="detail-table-wrap">
      <table class="table-modern" id="clientsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Client ID</th>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Middle Name</th>
            <th>Birthday</th>
            <th>Gender</th>
            <th>Civil Status</th>
            <th>Contact No.</th>
            <th>Address</th>
            <th>Mother's Maiden Name</th>
            <th>Sub ID</th>
            <th>Center</th>
            <th>Branch</th>
          </tr>
        </thead>
        <tbody id="clientsBody">
          <tr><td colspan="14" style="text-align:center;padding:30px;color:var(--gray-400);">Loading...</td></tr>
        </tbody>
      </table>
    </div>

    <div id="clientPager" style="display:none;padding:14px 28px;border-top:1px solid var(--gray-100);align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
      <span id="clientPagerInfo" style="font-size:0.8rem;color:var(--gray-600);font-weight:600;"></span>
      <div style="display:flex;align-items:center;gap:8px;">
        <button type="button" id="clientPrev" style="background:var(--gray-100);border:none;border-radius:8px;padding:8px 14px;font-size:0.8rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;">‹ Prev</button>
        <span id="clientPageLabel" style="font-size:0.8rem;color:var(--gray-400);font-weight:600;min-width:90px;text-align:center;"></span>
        <button type="button" id="clientNext" style="background:var(--gray-100);border:none;border-radius:8px;padding:8px 14px;font-size:0.8rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;">Next ›</button>
      </div>
    </div>

    <div style="padding:16px 28px;border-top:1px solid var(--gray-100);display:flex;justify-content:flex-end;">
      <button type="button" data-close-detail style="background:var(--gray-100);border:none;border-radius:10px;padding:10px 24px;font-size:0.88rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;">
        Close
      </button>
    </div>
  </div>
</div>

<!-- EDIT IMPORT MODAL -->
<div id="editModal">
  <div class="edit-box">
    <div class="edit-head">
      <h5><i class="bi bi-pencil-square"></i> Edit Import</h5>
      <button type="button" class="edit-head-close" data-close-edit title="Close"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="editForm" autocomplete="off">
      <div class="edit-body">
        <div class="edit-field">
          <label for="editFileName">File Name</label>
          <input type="text" id="editFileName" placeholder="e.g. September 2026 Client Info.csv">
        </div>
        <div class="edit-field">
          <label for="editBranch">Branch</label>
          <select id="editBranch"></select>
          <div style="font-size:0.72rem;color:var(--gray-400);margin-top:5px;">Binabago lang nito ang branch tag ng import na ito.</div>
        </div>
      </div>
      <div class="edit-foot">
        <button type="button" class="btn-cancel" data-close-edit>Cancel</button>
        <button type="submit" class="btn-save">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
// ─────────────────────────────────────────────
// UPLOAD
// ─────────────────────────────────────────────
let _csvFile = null;

function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

function handleFileSelect(input) {
  if (input.files && input.files[0]) setFile(input.files[0]);
}

function handleDrop(e) {
  e.preventDefault();
  document.getElementById('dropZone').classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (file && file.name.toLowerCase().endsWith('.csv')) setFile(file);
  else alert('Please upload a CSV file only.');
}

function setFile(file) {
  _csvFile = file;
  document.getElementById('fileChipName').textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
  document.getElementById('fileChip').classList.add('show');
  const btn = document.getElementById('importBtn');
  btn.disabled = false;
  btn.style.opacity = '1';
  btn.style.cursor = 'pointer';
  document.getElementById('resultPanel').className = 'result-panel';
}

function clearFile() {
  _csvFile = null;
  document.getElementById('csvFileInput').value = '';
  document.getElementById('fileChip').classList.remove('show');
  const btn = document.getElementById('importBtn');
  btn.disabled = true;
  btn.style.opacity = '0.5';
  btn.style.cursor = 'not-allowed';
  document.getElementById('resultPanel').className = 'result-panel';
}

async function submitImport() {
  if (!_csvFile) return;
  const btn = document.getElementById('importBtn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Importing...';

  const formData = new FormData();
  formData.append('csv_file', _csvFile);

  const panel = document.getElementById('resultPanel');
  try {
    const res = await fetch('/quewing_system/api/customers/import.php', { method: 'POST', body: formData });
    const data = await res.json();
    panel.className = 'result-panel show';
    if (data.success) {
      panel.classList.add('result-ok');
      panel.innerHTML = `<strong><i class="bi bi-check-circle-fill"></i> ${esc(data.message)}</strong>
        ${data.errors && data.errors.length ? `<details style="margin-top:10px;"><summary style="cursor:pointer;font-size:0.8rem;">Show skipped rows (${data.errors.length}+)</summary><pre style="font-size:0.72rem;margin-top:8px;white-space:pre-wrap;">${esc(data.errors.join('\n'))}</pre></details>` : ''}`;
      clearFile();
      loadLogs(); // refresh history below
    } else {
      panel.classList.add('result-err');
      panel.innerHTML = `<strong><i class="bi bi-exclamation-circle-fill"></i> ${esc(data.error || 'Import failed')}</strong>`;
    }
  } catch (e) {
    panel.className = 'result-panel show result-err';
    panel.innerHTML = '<strong><i class="bi bi-wifi-off"></i> Connection error. Please try again.</strong>';
  }

  btn.disabled = false;
  btn.innerHTML = '<i class="bi bi-upload"></i> Start Import';
}

// ─────────────────────────────────────────────
// IMPORT HISTORY
// ─────────────────────────────────────────────
let _allClients = [];
let _viewClients = [];
let _page = 1;
let _filterBranch = '';
const _PAGE_SIZE = 100;
const BRANCHES = <?php echo json_encode($all_branches); ?>;

function fmtDate(str) {
  if (!str) return '—';
  const d = new Date(str);
  return d.toLocaleDateString('en-PH', { year:'numeric', month:'long', day:'numeric' })
    + ' ' + d.toLocaleTimeString('en-PH', { hour:'2-digit', minute:'2-digit', hour12:true });
}

function fmtBirthday(str) {
  if (!str || str === '0000-00-00') return '—';
  const d = new Date(str);
  return d.toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
}

function escAttr(v) {
  return String(v == null ? '' : v)
    .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
    .replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function rowClick(e, id) {
  if (e.target.closest('.actions-wrap')) return;
  viewDetail(id);
}

function toggleActions(gear) {
  const wrap = gear.closest('.actions-wrap');
  const menu = wrap.querySelector('.actions-menu');
  const isOpen = menu.style.display === 'block';
  closeActionMenus();
  if (isOpen) return;

  // Render fixed against the viewport so the card's overflow doesn't clip it
  menu.classList.remove('up');
  menu.style.position = 'fixed';
  menu.style.display = 'block';
  menu.style.top = 'auto';
  menu.style.left = 'auto';

  const g = gear.getBoundingClientRect();
  menu.style.visibility = 'hidden';
  const mw = Math.max(170, menu.offsetWidth);
  const mh = menu.offsetHeight;
  menu.style.visibility = '';

  let top = g.bottom + 6;
  let left = g.left + g.width - mw;
  if (top + mh > window.innerHeight) {
    top = g.top - mh - 6;
    menu.classList.add('up');
  }
  if (left < 8) left = 8;
  if (left + mw > window.innerWidth) left = window.innerWidth - mw - 8;
  menu.style.top = top + 'px';
  menu.style.left = left + 'px';
}

function closeActionMenus() {
  document.querySelectorAll('.actions-menu').forEach(m => m.style.display = 'none');
}

async function runLogAction(e) {
  const btn = e.target.closest('[data-act]');
  const gear = e.target.closest('.toggle-actions');
  if (gear) {
    toggleActions(gear);
    return;
  }
  if (!btn) {
    closeActionMenus();
    return;
  }
  const act = btn.dataset.act;
  const logId = parseInt(btn.dataset.id);
  const fileName = btn.dataset.file || '';
  const count = parseInt(btn.dataset.count) || 0;
  closeActionMenus();
  if (act === 'view') viewDetail(logId);
  else if (act === 'edit') openEditModal(logId, fileName, btn.dataset.branch || '');
  else if (act === 'delete') deleteLog(logId, fileName, count);
}

let _editLogId = null;
let _currentLogId = null;

function openEditModal(logId, currentName, currentBranch) {
  _editLogId = logId;
  document.getElementById('editFileName').value = currentName || '';
  const sel = document.getElementById('editBranch');
  const opts = BRANCHES.slice();
  if (currentBranch && currentBranch !== '' && !opts.includes(currentBranch)) opts.unshift(currentBranch);
  sel.innerHTML = '<option value="">— No branch —</option>' + opts.map(b =>
    '<option value="' + escAttr(b) + '"' + (b === currentBranch ? ' selected' : '') + '>' + escAttr(b) + '</option>'
  ).join('');
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('editFileName').focus();
  document.getElementById('editFileName').select();
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
  _editLogId = null;
}

async function saveEdit() {
  const fileName = document.getElementById('editFileName').value.trim();
  const branch = document.getElementById('editBranch').value;
  if (!fileName) { alert('File name cannot be empty.'); return; }
  const res = await fetch('/quewing_system/api/customers/update_log.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ log_id: _editLogId, file_name: fileName, branch: branch })
  });
  const data = await res.json().catch(() => ({}));
  if (!data.success) { alert(data.error || 'Failed to update.'); return; }
  const editedId = _editLogId;
  closeEditModal();
  loadLogs();
  if (_currentLogId === editedId) viewDetail(editedId);
}

async function deleteLog(logId, fileName, count) {
  const plural = parseInt(count) === 1 ? 'customer' : 'customers';
  if (!confirm('Delete this import "' + fileName + '" and ' + (count > 0 ? count + ' linked ' + plural : 'its linked customers') + '? This cannot be undone.')) return;
  const res = await fetch('/quewing_system/api/customers/delete_log.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ log_id: logId })
  });
  const data = await res.json().catch(() => ({}));
  if (!data.success) {
    alert(data.error || 'Failed to delete.');
    return;
  }
  closeDetail();
  loadLogs();
}

async function loadLogs() {
  const res = await fetch('/quewing_system/api/customers/import_logs.php?limit=100');
  if (!res.ok) return;
  const data = await res.json();
  const logs = data.logs || [];

  const totalImported = logs.reduce((s, l) => s + (parseInt(l.total_imported) || 0), 0);
  const totalSkipped  = logs.reduce((s, l) => s + (parseInt(l.total_skipped)  || 0), 0);
  document.getElementById('statTotal').textContent    = logs.length;
  document.getElementById('statImported').textContent = totalImported.toLocaleString();
  document.getElementById('statSkipped').textContent  = totalSkipped.toLocaleString();
  document.getElementById('logCount').textContent     = logs.length + ' upload' + (logs.length !== 1 ? 's' : '');

  const tbody = document.getElementById('logsBody');
  if (logs.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--gray-400);"><i class="bi bi-inbox" style="font-size:1.8rem;display:block;margin-bottom:8px;opacity:0.4;"></i>No imports yet</td></tr>';
    return;
  }

  tbody.innerHTML = logs.map((log, i) => {
    const uploader = log.first_name
      ? log.first_name + ' ' + log.last_name + ' (@' + log.username + ')'
      : (log.username ? '@' + log.username : '—');
    return `<tr class="log-row" onclick="rowClick(event, ${log.id})">
      <td style="color:var(--gray-400);font-weight:600;">${i + 1}</td>
      <td>
        <div style="display:flex;align-items:center;gap:8px;">
          <div style="width:32px;height:32px;background:var(--green-pale);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi bi-file-earmark-spreadsheet" style="color:var(--green-main);font-size:0.9rem;"></i>
          </div>
          <span style="font-weight:600;font-size:0.85rem;">${log.file_name}</span>
        </div>
      </td>
      <td style="font-size:0.83rem;color:var(--gray-600);">${fmtDate(log.imported_at)}</td>
      <td style="font-size:0.83rem;">${uploader}</td>
      <td>${log.branch ? `<span class="badge-green">${log.branch}</span>` : '<span style="color:var(--gray-400);">—</span>'}</td>
      <td style="text-align:center;font-weight:800;color:var(--green-main);">${parseInt(log.total_imported).toLocaleString()}</td>
      <td style="text-align:center;font-weight:700;color:${log.total_skipped > 0 ? '#f59e0b' : 'var(--gray-400)'};">${parseInt(log.total_skipped).toLocaleString()}</td>
      <td style="text-align:center;">
        <div class="actions-wrap">
          <button type="button" class="btn-icon gear toggle-actions" data-id="${log.id}" data-file="${escAttr(log.file_name)}" title="Actions">
            <i class="bi bi-gear"></i>
          </button>
          <div class="actions-menu">
            <button type="button" data-act="view" data-id="${log.id}"><i class="bi bi-eye"></i> View</button>
            <button type="button" data-act="edit" data-id="${log.id}" data-file="${escAttr(log.file_name)}" data-branch="${escAttr(log.branch || '')}"><i class="bi bi-pencil"></i> Edit</button>
            <button type="button" data-act="delete" data-id="${log.id}" data-file="${escAttr(log.file_name)}" data-count="${parseInt(log.total_imported) || 0}"><i class="bi bi-trash"></i> <span style="color:#ef4444;">Delete</span></button>
          </div>
        </div>
      </td>
    </tr>`;
  }).join('');
}

async function viewDetail(logId) {
  _currentLogId = logId;
  document.getElementById('detailModal').style.display = 'flex';
  document.getElementById('clientsBody').innerHTML = '<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--gray-400);">Loading...</td></tr>';
  document.getElementById('clientSearch').value = '';

  const res = await fetch(`/quewing_system/api/customers/by_log.php?log_id=${logId}`);
  const data = await res.json();

  if (!data.log) {
    document.getElementById('clientsBody').innerHTML = '<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--gray-400);">No data found</td></tr>';
    return;
  }

  const log = data.log;
  const uploader = log.first_name ? log.first_name + ' ' + log.last_name + ' (@' + log.username + ')' : (log.username || '—');

  document.getElementById('detailTitle').textContent = log.file_name;
  document.getElementById('detailMeta').textContent  = fmtDate(log.imported_at);
  document.getElementById('detailFile').textContent  = log.file_name;
  document.getElementById('detailUploader').textContent = uploader;
  document.getElementById('detailImported').textContent = parseInt(log.total_imported).toLocaleString() + ' records';

  const bsel = document.getElementById('detailBranch');
  bsel.innerHTML = '<option value="">— All branches —</option>' + BRANCHES.map(b =>
    '<option value="' + escAttr(b) + '">' + escAttr(b) + '</option>'
  ).join('');
  bsel.value = '';
  _filterBranch = '';

  _allClients = data.customers || [];
  _viewClients = _allClients;
  _page = 1;
  renderClients(_viewClients);
}

function buildClientRow(c, i) {
  const motherName = [c.mother_maiden_lastname, c.mother_maiden_firstname, c.mother_maiden_middlename].filter(Boolean).join(', ');
  return `<tr>
    <td style="color:var(--gray-400);font-weight:600;">${i + 1}</td>
    <td style="font-weight:600;white-space:nowrap;">${c.client_id || '—'}</td>
    <td style="font-weight:600;">${c.client_lastname || '—'}</td>
    <td>${c.client_firstname || '—'}</td>
    <td>${c.client_middlename || '—'}</td>
    <td style="white-space:nowrap;">${fmtBirthday(c.birthday)}</td>
    <td>${c.gender || '—'}</td>
    <td style="white-space:nowrap;">${c.civil_status || '—'}</td>
    <td style="white-space:nowrap;">${c.contact_no || '—'}</td>
    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${c.address || ''}">${c.address || '—'}</td>
    <td style="white-space:nowrap;">${motherName || '—'}</td>
    <td>${c.client_subid || '—'}</td>
    <td>${c.center_name || '—'}</td>
    <td>${c.branch ? `<span class="badge-green">${c.branch}</span>` : '—'}</td>
  </tr>`;
}

function updatePager(total, totalPages) {
  const pager = document.getElementById('clientPager');
  const info = document.getElementById('clientPagerInfo');
  const label = document.getElementById('clientPageLabel');
  const prev = document.getElementById('clientPrev');
  const next = document.getElementById('clientNext');
  if (!pager) return;

  if (totalPages <= 1) {
    pager.style.display = 'none';
    return;
  }
  pager.style.display = 'flex';
  info.textContent = total.toLocaleString() + ' client records';
  label.textContent = 'Page ' + _page + ' of ' + totalPages;
  prev.disabled = _page <= 1;
  next.disabled = _page >= totalPages;
  prev.style.opacity = _page <= 1 ? 0.4 : 1;
  next.style.opacity = _page >= totalPages ? 0.4 : 1;
}

function goClientPage(dir) {
  const total = _viewClients.length;
  const totalPages = Math.max(1, Math.ceil(total / _PAGE_SIZE));
  _page = Math.min(totalPages, Math.max(1, _page + dir));
  renderClients(_viewClients);
}

function renderClients(clients) {
  document.getElementById('clientCount').textContent = clients.length.toLocaleString() + ' client' + (clients.length !== 1 ? 's' : '');
  const tbody = document.getElementById('clientsBody');
  if (clients.length === 0) {
    tbody.innerHTML = '<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--gray-400);">No clients found</td></tr>';
    updatePager(0, 1);
    return;
  }
  const totalPages = Math.max(1, Math.ceil(clients.length / _PAGE_SIZE));
  if (_page > totalPages) _page = totalPages;
  const start = (_page - 1) * _PAGE_SIZE;
  const pageClients = clients.slice(start, start + _PAGE_SIZE);
  tbody.innerHTML = pageClients.map((c, i) => buildClientRow(c, start + i)).join('');
  updatePager(clients.length, totalPages);
}

function activeClients() {
  return _filterBranch
    ? _allClients.filter(c => c.branch === _filterBranch)
    : _allClients;
}

function filterClients() {
  const q = document.getElementById('clientSearch').value.toLowerCase();
  const base = activeClients();
  if (!q) {
    _viewClients = base;
    _page = 1;
    renderClients(_viewClients);
    return;
  }
  _viewClients = base.filter(c =>
    [c.client_id, c.client_lastname, c.client_firstname, c.client_middlename, c.branch, c.center_name, c.contact_no]
      .some(v => v && v.toLowerCase().includes(q))
  );
  _page = 1;
  renderClients(_viewClients);
}

function closeDetail() {
  document.getElementById('detailModal').style.display = 'none';
  _allClients = [];
  _viewClients = [];
  _currentLogId = null;
}

// Close modal on backdrop click
document.getElementById('detailModal').addEventListener('click', function(e) {
  if (e.target === this) closeDetail();
});

// Close via any [data-close-detail] element (X button and Close button)
document.addEventListener('click', function(e) {
  if (e.target.closest && e.target.closest('[data-close-detail]')) closeDetail();
  if (e.target.closest && e.target.closest('[data-close-edit]')) closeEditModal();
});

// Close edit modal on backdrop click + submit handler
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) closeEditModal();
});
document.getElementById('editForm').addEventListener('submit', function(e) {
  e.preventDefault();
  saveEdit();
});

// Branch dropdown in detail modal: filter the client list
const _detailBranch = document.getElementById('detailBranch');
_detailBranch.addEventListener('change', function() {
  _filterBranch = this.value;
  _page = 1;
  filterClients();
});

// Close via Escape key (edit modal first, then detail)
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    if (document.getElementById('editModal').style.display === 'flex') closeEditModal();
    else closeDetail();
  }
});

// Pagination buttons
document.getElementById('clientPrev').addEventListener('click', function() { goClientPage(-1); });
document.getElementById('clientNext').addEventListener('click', function() { goClientPage(1); });

// Gear dropdown menu (open/close + actions)
document.addEventListener('click', function(e) {
  if (e.target.closest && (e.target.closest('.toggle-actions') || e.target.closest('[data-act]'))) {
    runLogAction(e);
  } else {
    closeActionMenus();
  }
});

// Close dropdown on scroll (menu is fixed-positioned)
window.addEventListener('scroll', closeActionMenus, true);

loadLogs();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
