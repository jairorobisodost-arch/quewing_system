<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install SFI Queuing System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
.install-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 40px; width: 100%; max-width: 600px; }
.output { background: #1a1a2e; color: #00ff00; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 0.9rem; max-height: 300px; overflow-y: auto; white-space: pre-wrap; }
</style>
</head>
<body>
<div class="install-card">
    <h2 class="text-center mb-4">SFI QUEUING SYSTEM - Installation</h2>
    
    <div class="mb-3">
        <label class="form-label">MySQL Host</label>
        <input type="text" class="form-control" id="dbHost" value="localhost">
    </div>
    <div class="mb-3">
        <label class="form-label">Database Name</label>
        <input type="text" class="form-control" id="dbName" value="quewing_system">
    </div>
    <div class="mb-3">
        <label class="form-label">MySQL Username</label>
        <input type="text" class="form-control" id="dbUser" value="root">
    </div>
    <div class="mb-3">
        <label class="form-label">MySQL Password</label>
        <input type="password" class="form-control" id="dbPass">
    </div>
    <hr>
    <h5>Admin Account</h5>
    <div class="mb-3">
        <label class="form-label">Admin Username</label>
        <input type="text" class="form-control" id="adminUser" value="admin">
    </div>
    <div class="mb-3">
        <label class="form-label">Admin Password</label>
        <input type="password" class="form-control" id="adminPass" value="admin123">
    </div>
    <div class="mb-3">
        <label class="form-label">Admin First Name</label>
        <input type="text" class="form-control" id="adminFirst" value="System">
    </div>
    <div class="mb-3">
        <label class="form-label">Admin Last Name</label>
        <input type="text" class="form-control" id="adminLast" value="Admin">
    </div>
    <button class="btn btn-success w-100 btn-lg" onclick="install()">Install System</button>
    <div class="mt-3">
        <div class="output d-none" id="output"></div>
    </div>
</div>

<script>
async function install() {
    const btn = document.querySelector('button');
    btn.disabled = true;
    btn.textContent = 'Installing...';
    
    const output = document.getElementById('output');
    output.classList.remove('d-none');
    output.textContent = 'Starting installation...\n';
    
    const data = {
        host: document.getElementById('dbHost').value,
        dbname: document.getElementById('dbName').value,
        user: document.getElementById('dbUser').value,
        pass: document.getElementById('dbPass').value,
        admin_user: document.getElementById('adminUser').value,
        admin_pass: document.getElementById('adminPass').value,
        admin_first: document.getElementById('adminFirst').value,
        admin_last: document.getElementById('adminLast').value
    };
    
    try {
        const res = await fetch('/quewing_system/install/install.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.logs) {
            output.textContent += result.logs.join('\n');
        }
        if (result.success) {
            output.textContent += '\n\n✓ Installation successful! Redirecting to login...';
            setTimeout(() => window.location.href = '/quewing_system/login.php', 2000);
        } else {
            output.textContent += '\n\n✗ Installation failed: ' + (result.error || 'Unknown error');
        }
    } catch (e) {
        output.textContent += '\n\n✗ Error: ' + e.message;
    }
    btn.disabled = false;
    btn.textContent = 'Install System';
}
</script>
</body>
</html>