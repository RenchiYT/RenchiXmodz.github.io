<?php require_once 'config.php';

if (!isAdmin()) redirect('login.php');

// Handle actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['admin_action'] ?? '';
    
    if ($action === 'ban_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE users SET role = 'banned' WHERE id = ?")->execute([$userId]);
        logActivity($pdo, $_SESSION['user_id'], 'admin_ban', "Banned user ID: $userId");
        $message = 'User banned.';
    } elseif ($action === 'unban_user') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$userId]);
        logActivity($pdo, $_SESSION['user_id'], 'admin_unban', "Unbanned user ID: $userId");
        $message = 'User unbanned.';
    } elseif ($action === 'make_admin') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$userId]);
        $message = 'User promoted to admin.';
    } elseif ($action === 'update_server') {
        $id = (int)($_POST['server_id'] ?? 0);
        $ping = (int)($_POST['ping_ms'] ?? 0);
        $online = (int)($_POST['is_online'] ?? 0);
        $load = (int)($_POST['load_percent'] ?? 0);
        $pdo->prepare("UPDATE proxy_servers SET ping_ms = ?, is_online = ?, load_percent = ? WHERE id = ?")
            ->execute([$ping, $online, $load, $id]);
        $message = 'Server updated.';
    } elseif ($action === 'update_settings') {
        foreach ($_POST as $key => $value) {
            if (in_array($key, ['site_name', 'site_url', 'discord_client_id', 'discord_client_secret', 'discord_guild_id', 'proxy_token_valid_hours', 'maintenance_mode'])) {
                $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?")->execute([$value, $key]);
            }
        }
        $message = 'Settings saved.';
    }
}

// Get data
$users = $pdo->query("SELECT id, username, email, discord_id, role, free_fire_uid, is_active, created_at, last_login FROM users ORDER BY id DESC LIMIT 50")->fetchAll();
$servers = $pdo->query("SELECT * FROM proxy_servers")->fetchAll();
$sessions = $pdo->query("SELECT ps.*, u.username FROM proxy_sessions ps JOIN users u ON u.id = ps.user_id WHERE ps.is_active = 1 ORDER BY ps.started_at DESC")->fetchAll();
$settings = $pdo->query("SELECT * FROM settings")->fetchAll();
$settingsMap = [];
foreach ($settings as $s) { $settingsMap[$s['setting_key']] = $s['setting_value']; }
$logs = $pdo->query("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT 30")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',sans-serif; background:#0d0d0d; color:#fff; }
    header { background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); padding:18px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e94560; }
    .logo { font-size:24px; font-weight:800; color:#e94560; }
    .logo span { color:#fff; }
    nav a { color:#ccc; text-decoration:none; margin-left:22px; font-size:14px; transition:0.3s; }
    nav a:hover { color:#e94560; }
    .container { max-width:1400px; margin:0 auto; padding:20px 30px; }
    .header-bar { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
    .header-bar h2 { font-size:26px; }
    .header-bar h2 span { color:#e94560; }
    .message { background:#1a2d1a; border:1px solid #2ecc71; color:#2ecc71; padding:12px; border-radius:10px; margin-bottom:20px; font-size:13px; }
    .tab-nav { display:flex; border-bottom:2px solid #2a2a4a; margin-bottom:25px; }
    .tab-btn { background:none; border:none; color:#999; padding:12px 24px; cursor:pointer; font-size:15px; font-weight:600; transition:0.3s; border-bottom:2px solid transparent; margin-bottom:-2px; }
    .tab-btn.active { color:#e94560; border-bottom-color:#e94560; }
    .tab-btn:hover { color:#fff; }
    .tab-content { display:none; }
    .tab-content.active { display:block; }
    .card { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:15px; padding:20px; margin-bottom:20px; overflow-x:auto; }
    .card h3 { font-size:18px; margin-bottom:15px; }
    .card h3 i { color:#e94560; margin-right:8px; }
    table { width:100%; border-collapse:collapse; font-size:13px; }
    th, td { padding:10px 12px; text-align:left; border-bottom:1px solid #2a2a4a; }
    th { color:#e94560; font-weight:700; font-size:12px; text-transform:uppercase; }
    tr:hover td { background:#0d0d0d; }
    .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
    .badge-admin { background:#1a1a2d; color:#5865F2; border:1px solid #5865F2; }
    .badge-user { background:#1a2d1a; color:#2ecc71; border:1px solid #2ecc71; }
    .badge-banned { background:#2d1a1a; color:#e74c3c; border:1px solid #e74c3c; }
    .btn-sm { padding:5px 14px; border-radius:20px; border:none; font-size:11px; font-weight:700; cursor:pointer; transition:0.3s; color:#fff; text-decoration:none; display:inline-block; }
    .btn-danger-sm { background:#e74c3c; }
    .btn-danger-sm:hover { background:#c0392b; }
    .btn-success-sm { background:#2ecc71; }
    .btn-success-sm:hover { background:#27ae60; }
    .btn-primary-sm { background:#3498db; }
    .btn-primary-sm:hover { background:#2980b9; }
    .input-sm { padding:6px 10px; border-radius:8px; border:1px solid #333; background:#0d0d0d; color:#fff; font-size:12px; width:70px; }
    .select-sm { padding:6px 10px; border-radius:8px; border:1px solid #333; background:#0d0d0d; color:#fff; font-size:12px; }
    .form-group { margin-bottom:12px; }
    .form-group label { display:block; font-size:13px; color:#bbb; margin-bottom:4px; }
    .form-group input { width:100%; padding:10px 14px; border-radius:10px; border:1px solid #333; background:#0d0d0d; color:#fff; font-size:14px; outline:none; }
    .form-group input:focus { border-color:#e94560; }
    .btn { padding:10px 24px; border-radius:30px; border:none; font-weight:700; font-size:14px; cursor:pointer; transition:0.3s; color:#fff; }
    .btn-primary { background:#e94560; }
    .btn-primary:hover { background:#d63851; }
    .stats-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:15px; margin-bottom:20px; }
    .stat-box { background:#0d0d0d; border:1px solid #2a2a4a; border-radius:12px; padding:18px; text-align:center; }
    .stat-box .num { font-size:28px; font-weight:800; color:#e94560; }
    .stat-box .label { font-size:12px; color:#999; margin-top:4px; }
    footer { text-align:center; padding:25px; color:#666; font-size:13px; border-top:1px solid #222; }
    footer a { color:#e94560; text-decoration:none; }
    @media (max-width:768px) { header { flex-direction:column; gap:10px; } .container { padding:15px; } table { font-size:11px; } th, td { padding:6px 8px; } }
  </style>
</head>
<body>
<header>
  <div class="logo">Renchi<span>Xmodz</span> <span style="font-size:12px;color:#e94560;font-weight:400;">Admin</span></div>
  <nav>
    <a href="index.php">Home</a>
    <a href="dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
  </nav>
</header>
<div class="container">
  <div class="header-bar">
    <h2>⚙️ Admin <span>Panel</span></h2>
    <span style="color:#999;font-size:13px;">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
  </div>
  
  <?php if ($message): ?><div class="message"><?= $message ?></div><?php endif; ?>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-box"><div class="num"><?= count($users) ?></div><div class="label">Total Users</div></div>
    <div class="stat-box"><div class="num"><?= $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn() ?></div><div class="label">Online Now</div></div>
    <div class="stat-box"><div class="num"><?= count($sessions) ?></div><div class="label">Active Sessions</div></div>
    <div class="stat-box"><div class="num"><?= count($servers) ?></div><div class="label">Proxy Servers</div></div>
  </div>

  <!-- Tabs -->
  <div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab(event, 'users')">Users</button>
    <button class="tab-btn" onclick="switchTab(event, 'servers')">Servers</button>
    <button class="tab-btn" onclick="switchTab(event, 'sessions')">Sessions</button>
    <button class="tab-btn" onclick="switchTab(event, 'settings')">Settings</button>
    <button class="tab-btn" onclick="switchTab(event, 'logs')">Logs</button>
  </div>

  <!-- Users Tab -->
  <div id="users" class="tab-content active">
    <div class="card">
      <h3><i class="fas fa-users"></i> User Management</h3>
      <table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Discord</th><th>Role</th><th>FF UID</th><th>Active</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= $u['id'] ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td><?= htmlspecialchars($u['email'] ?? 'N/A') ?></td>
              <td><?= $u['discord_id'] ? '<span style="color:#5865F2;"><i class="fab fa-discord"></i> Linked</span>' : '—' ?></td>
              <td><span class="badge badge-<?= $u['role'] ?>"><?= $u['role'] ?></span></td>
              <td><?= htmlspecialchars($u['free_fire_uid'] ?? '—') ?></td>
              <td><?= $u['is_active'] ? '<span style="color:#2ecc71;">●</span>' : '<span style="color:#666;">○</span>' ?></td>
              <td style="font-size:11px;color:#999;"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
              <td>
                <?php if ($u['role'] !== 'admin'): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="admin_action" value="make_admin">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button class="btn-sm btn-primary-sm">Admin</button>
                  </form>
                <?php endif; ?>
                <?php if ($u['role'] !== 'banned'): ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="admin_action" value="ban_user">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button class="btn-sm btn-danger-sm">Ban</button>
                  </form>
                <?php else: ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="admin_action" value="unban_user">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button class="btn-sm btn-success-sm">Unban</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Servers Tab -->
  <div id="servers" class="tab-content">
    <div class="card">
      <h3><i class="fas fa-server"></i> Proxy Server Management</h3>
      <table>
        <thead><tr><th>ID</th><th>Region</th><th>Host</th><th>Port</th><th>Ping</th><th>Online</th><th>Load %</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($servers as $s): ?>
            <tr>
              <form method="POST">
                <td><?= $s['id'] ?></td>
                <td><?= htmlspecialchars($s['region_name']) ?></td>
                <td style="font-family:monospace;font-size:12px;"><?= htmlspecialchars($s['server_host']) ?></td>
                <td><?= $s['server_port'] ?></td>
                <td><input type="number" name="ping_ms" value="<?= $s['ping_ms'] ?>" class="input-sm"></td>
                <td>
                  <select name="is_online" class="select-sm">
                    <option value="1" <?= $s['is_online'] ? 'selected' : '' ?>>Online</option>
                    <option value="0" <?= !$s['is_online'] ? 'selected' : '' ?>>Offline</option>
                  </select>
                </td>
                <td><input type="number" name="load_percent" value="<?= $s['load_percent'] ?>" class="input-sm" min="0" max="100"></td>
                <td>
                  <input type="hidden" name="admin_action" value="update_server">
                  <input type="hidden" name="server_id" value="<?= $s['id'] ?>">
                  <button class="btn-sm btn-primary-sm">Update</button>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sessions Tab -->
  <div id="sessions" class="tab-content">
    <div class="card">
      <h3><i class="fas fa-plug"></i> Active Sessions</h3>
      <?php if (count($sessions) > 0): ?>
        <table>
          <thead><tr><th>Session ID</th><th>User</th><th>Region</th><th>Started</th><th>Duration</th></tr></thead>
          <tbody>
            <?php foreach ($sessions as $ses): ?>
              <tr>
                <td style="font-family:monospace;font-size:11px;"><?= htmlspecialchars(substr($ses['session_id'], 0, 24)) ?>...</td>
                <td><?= htmlspecialchars($ses['username']) ?></td>
                <td><?= htmlspecialchars($ses['server_region']) ?></td>
                <td><?= date('H:i:s', strtotime($ses['started_at'])) ?></td>
                <td><?= floor((time() - strtotime($ses['started_at'])) / 60) ?> min</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p style="color:#999;font-size:14px;">No active sessions.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Settings Tab -->
  <div id="settings" class="tab-content">
    <div class="card" style="max-width:600px;">
      <h3><i class="fas fa-cog"></i> Site Settings</h3>
      <form method="POST">
        <div class="form-group">
          <label>Site Name</label>
          <input type="text" name="site_name" value="<?= htmlspecialchars($settingsMap['site_name'] ?? SITE_NAME) ?>">
        </div>
        <div class="form-group">
          <label>Site URL</label>
          <input type="text" name="site_url" value="<?= htmlspecialchars($settingsMap['site_url'] ?? SITE_URL) ?>">
        </div>
        <div class="form-group">
          <label>Discord Client ID</label>
          <input type="text" name="discord_client_id" value="<?= htmlspecialchars($settingsMap['discord_client_id'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Discord Client Secret</label>
          <input type="text" name="discord_client_secret" value="<?= htmlspecialchars($settingsMap['discord_client_secret'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Discord Guild ID (server to auto-join)</label>
          <input type="text" name="discord_guild_id" value="<?= htmlspecialchars($settingsMap['discord_guild_id'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Proxy Token Valid (hours)</label>
          <input type="number" name="proxy_token_valid_hours" value="<?= htmlspecialchars($settingsMap['proxy_token_valid_hours'] ?? '24') ?>">
        </div>
        <div class="form-group">
          <label>Maintenance Mode</label>
          <select name="maintenance_mode" style="width:100%;padding:10px;border-radius:10px;border:1px solid #333;background:#0d0d0d;color:#fff;">
            <option value="0" <?= ($settingsMap['maintenance_mode'] ?? '0') === '0' ? 'selected' : '' ?>>Disabled</option>
            <option value="1" <?= ($settingsMap['maintenance_mode'] ?? '0') === '1' ? 'selected' : '' ?>>Enabled</option>
          </select>
        </div>
        <input type="hidden" name="admin_action" value="update_settings">
        <button class="btn btn-primary" style="margin-top:10px;">Save Settings</button>
      </form>
    </div>
  </div>

  <!-- Logs Tab -->
  <div id="logs" class="tab-content">
    <div class="card">
      <h3><i class="fas fa-history"></i> Activity Logs</h3>
      <table>
        <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
            <tr>
              <td style="font-size:11px;color:#999;"><?= date('M d H:i', strtotime($l['created_at'])) ?></td>
              <td><?= htmlspecialchars($l['username'] ?? 'System') ?></td>
              <td><?= htmlspecialchars($l['action']) ?></td>
              <td style="color:#999;font-size:12px;"><?= htmlspecialchars($l['details'] ?? '') ?></td>
              <td style="font-family:monospace;font-size:11px;color:#666;"><?= htmlspecialchars($l['ip_address'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<footer><p>&copy; 2026 <a href="#">RenchiXmodz</a> — Admin Panel</p></footer>
<script>
function switchTab(event, tabId) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.getElementById(tabId).classList.add('active');
}
</script>
</body>
</html>
