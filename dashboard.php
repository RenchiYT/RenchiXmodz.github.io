<?php require_once 'config.php';

if (!isLoggedIn()) redirect('login.php');

$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get active sessions
$stmt = $pdo->prepare("SELECT * FROM proxy_sessions WHERE user_id = ? AND is_active = 1");
$stmt->execute([$userId]);
$activeSession = $stmt->fetch();

// Get proxy servers
$servers = $pdo->query("SELECT * FROM proxy_servers WHERE is_online = 1")->fetchAll();

// Handle proxy activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'activate') {
        $uid = sanitize($_POST['uid'] ?? '');
        $region = sanitize($_POST['region'] ?? '');
        
        if (empty($uid) || empty($region)) {
            echo json_encode(['success' => false, 'message' => 'UID and region required.']);
            exit;
        }
        
        // Update UID
        $pdo->prepare("UPDATE users SET free_fire_uid = ? WHERE id = ?")->execute([$uid, $userId]);
        
        // Generate token
        $token = generateToken();
        $expires = date('Y-m-d H:i:s', strtotime('+' . PROXY_TOKEN_VALID_HOURS . ' hours'));
        $pdo->prepare("UPDATE users SET proxy_token = ?, token_expires = ?, is_active = 1 WHERE id = ?")
            ->execute([$token, $expires, $userId]);
        
        // Create session
        $sessionId = generateToken(32);
        $stmt = $pdo->prepare("INSERT INTO proxy_sessions (user_id, server_region, session_id) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $region, $sessionId]);
        
        logActivity($pdo, $userId, 'proxy_activated', "Region: $region, UID: $uid");
        
        echo json_encode(['success' => true, 'message' => 'Proxy activated!', 'token' => $token, 'session_id' => $sessionId]);
        exit;
        
    } elseif ($_POST['action'] === 'deactivate') {
        $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE proxy_sessions SET is_active = 0, ended_at = NOW() WHERE user_id = ? AND is_active = 1")
            ->execute([$userId]);
        
        logActivity($pdo, $userId, 'proxy_deactivated', 'Session ended');
        echo json_encode(['success' => true, 'message' => 'Proxy deactivated.']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',sans-serif; background:#0d0d0d; color:#fff; }
    header { background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); padding:18px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #e94560; }
    .logo { font-size:24px; font-weight:800; color:#e94560; }
    .logo span { color:#fff; }
    nav a { color:#ccc; text-decoration:none; margin-left:22px; font-size:14px; transition:0.3s; }
    nav a:hover { color:#e94560; }
    .container { max-width:1200px; margin:0 auto; padding:30px; }
    .welcome { margin-bottom:30px; }
    .welcome h2 { font-size:28px; }
    .welcome h2 span { color:#e94560; }
    .welcome p { color:#999; margin-top:5px; }
    .grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap:25px; margin-bottom:30px; }
    .card { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:15px; padding:25px; }
    .card h3 { font-size:18px; margin-bottom:15px; border-bottom:1px solid #2a2a4a; padding-bottom:10px; }
    .card h3 i { color:#e94560; margin-right:8px; }
    .status-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:700; }
    .status-active { background:#1a2d1a; color:#2ecc71; border:1px solid #2ecc71; }
    .status-inactive { background:#2d1a1a; color:#e74c3c; border:1px solid #e74c3c; }
    .input-group { margin-bottom:15px; }
    .input-group label { display:block; font-size:13px; color:#bbb; margin-bottom:5px; }
    .input-group input, .input-group select { width:100%; padding:10px 14px; border-radius:10px; border:1px solid #333; background:#0d0d0d; color:#fff; font-size:14px; outline:none; transition:0.3s; }
    .input-group input:focus, .input-group select:focus { border-color:#e94560; }
    .btn { display:inline-block; padding:10px 24px; border-radius:30px; border:none; font-weight:700; font-size:14px; cursor:pointer; transition:0.3s; color:#fff; text-decoration:none; }
    .btn-primary { background:#e94560; }
    .btn-primary:hover { background:#d63851; }
    .btn-success { background:#2ecc71; }
    .btn-success:hover { background:#27ae60; }
    .btn-danger { background:#e74c3c; }
    .btn-danger:hover { background:#c0392b; }
    .btn-sm { padding:6px 16px; font-size:12px; }
    .server-item { display:flex; justify-content:space-between; align-items:center; padding:12px 15px; background:#0d0d0d; border:1px solid #2a2a4a; border-radius:10px; margin-bottom:8px; }
    .server-item .ping { color:#2ecc71; font-weight:700; }
    .server-item .region { font-size:14px; }
    .server-item .online { color:#2ecc71; font-size:12px; }
    .server-item .offline { color:#e74c3c; font-size:12px; }
    .token-box { background:#0d0d0d; border:1px solid #2a2a4a; border-radius:10px; padding:12px; font-family:monospace; font-size:13px; color:#e94560; word-break:break-all; margin-top:10px; display:none; }
    .status-message { padding:12px; border-radius:10px; margin-bottom:15px; font-size:13px; display:none; }
    .status-success { background:#1a2d1a; border:1px solid #2ecc71; color:#2ecc71; display:block; }
    .status-error { background:#2d1a1a; border:1px solid #e74c3c; color:#e74c3c; display:block; }
    footer { text-align:center; padding:25px; color:#666; font-size:13px; border-top:1px solid #222; }
    footer a { color:#e94560; text-decoration:none; }
    @media (max-width:768px) { header { flex-direction:column; gap:10px; } nav a { margin:0 8px; font-size:12px; } .container { padding:15px; } }
  </style>
</head>
<body>
<header>
  <div class="logo">Renchi<span>Xmodz</span></div>
  <nav>
    <a href="index.php">Home</a>
    <a href="dashboard.php">Dashboard</a>
    <?php if (isAdmin()): ?><a href="admin.php">Admin</a><?php endif; ?>
    <a href="logout.php">Logout</a>
  </nav>
</header>

<div class="container">
  <div class="welcome">
    <h2>Welcome, <span><?= htmlspecialchars($user['username']) ?></span></h2>
    <p>Manage your proxy sessions and configurations.</p>
  </div>

  <div id="statusMessage" class="status-message"></div>

  <div class="grid">
    <!-- Account Info -->
    <div class="card">
      <h3><i class="fas fa-user"></i> Account Info</h3>
      <p style="margin-bottom:8px;color:#999;font-size:14px;"><strong style="color:#fff;">UID:</strong> <?= $user['free_fire_uid'] ? htmlspecialchars($user['free_fire_uid']) : '<span style="color:#e94560;">Not set</span>' ?></p>
      <p style="margin-bottom:8px;color:#999;font-size:14px;"><strong style="color:#fff;">Role:</strong> <?= ucfirst($user['role']) ?></p>
      <p style="margin-bottom:8px;color:#999;font-size:14px;"><strong style="color:#fff;">Status:</strong> 
        <span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>">
          <?= $user['is_active'] ? '● Active' : '○ Inactive' ?>
        </span>
      </p>
      <p style="font-size:13px;color:#666;">Joined: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
    </div>

    <!-- Activate Proxy -->
    <div class="card">
      <h3><i class="fas fa-bolt"></i> Proxy Control</h3>
      <?php if (!$activeSession): ?>
        <form id="activateForm">
          <input type="hidden" name="action" value="activate">
          <div class="input-group">
            <label><i class="fas fa-gamepad"></i> Free Fire UID</label>
            <input type="text" id="uid" name="uid" placeholder="Enter your Free Fire UID" value="<?= htmlspecialchars($user['free_fire_uid'] ?? '') ?>" maxlength="12">
          </div>
          <div class="input-group">
            <label><i class="fas fa-server"></i> Server Region</label>
            <select id="region" name="region">
              <option value="" disabled selected>— Select region —</option>
              <?php foreach ($servers as $s): ?>
                <option value="<?= $s['region_code'] ?>"><?= $s['region_name'] ?> (<?= $s['ping_ms'] ?>ms)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-success" style="width:100%;"><i class="fas fa-play"></i> Activate Proxy</button>
        </form>
      <?php else: ?>
        <p style="color:#2ecc71;margin-bottom:10px;"><i class="fas fa-check-circle"></i> Active Session</p>
        <p style="font-size:13px;color:#999;">Region: <?= htmlspecialchars($activeSession['server_region']) ?></p>
        <p style="font-size:13px;color:#999;">Session: <?= htmlspecialchars(substr($activeSession['session_id'], 0, 16)) ?>...</p>
        <p style="font-size:13px;color:#999;">Started: <?= date('H:i:s', strtotime($activeSession['started_at'])) ?></p>
        <form id="deactivateForm">
          <input type="hidden" name="action" value="deactivate">
          <button type="submit" class="btn btn-danger" style="width:100%;margin-top:10px;"><i class="fas fa-stop"></i> Deactivate Proxy</button>
        </form>
      <?php endif; ?>
      <div id="tokenBox" class="token-box"></div>
    </div>

    <!-- Quick Download -->
    <div class="card">
      <h3><i class="fas fa-download"></i> Config Download</h3>
      <p style="color:#999;font-size:14px;margin-bottom:15px;">Download your .45b config file for MT Manager.</p>
      <div class="input-group">
        <label>Region</label>
        <select id="configRegion">
          <option value="" disabled selected>— Select —</option>
          <?php foreach ($servers as $s): ?>
            <option value="<?= $s['region_code'] ?>"><?= $s['region_name'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-primary" style="width:100%;" onclick="downloadConfig()"><i class="fas fa-file-archive"></i> Download .45b</button>
    </div>
  </div>

  <!-- Server Status -->
  <div class="card" style="margin-bottom:30px;">
    <h3><i class="fas fa-globe"></i> Server Status</h3>
    <?php foreach ($servers as $s): ?>
      <div class="server-item">
        <div>
          <span class="region"><?= htmlspecialchars($s['region_name']) ?></span>
          <span class="<?= $s['is_online'] ? 'online' : 'offline' ?>"> ● <?= $s['is_online'] ? 'Online' : 'Offline' ?></span>
        </div>
        <div style="text-align:right;">
          <span class="ping"><?= $s['ping_ms'] ?>ms</span>
          <span style="color:#999;font-size:11px;display:block;">Load: <?= $s['load_percent'] ?>%</span>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<footer>
  <p>&copy; 2026 <a href="#">RenchiXmodz</a> — Free Fire Proxy Server.</p>
</footer>

<script>
// Activate proxy
document.getElementById('activateForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  try {
    const res = await fetch('dashboard.php', { method: 'POST', body: formData });
    const data = await res.json();
    const msg = document.getElementById('statusMessage');
    const tokenBox = document.getElementById('tokenBox');
    
    if (data.success) {
      msg.className = 'status-message status-success';
      msg.textContent = data.message;
      tokenBox.style.display = 'block';
      tokenBox.innerHTML = '<strong>Proxy Token:</strong> ' + data.token + '<br><strong>Session ID:</strong> ' + data.session_id;
      setTimeout(() => location.reload(), 2000);
    } else {
      msg.className = 'status-message status-error';
      msg.textContent = data.message;
    }
  } catch (e) {
    console.error(e);
  }
});

// Deactivate proxy
document.getElementById('deactivateForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  try {
    const res = await fetch('dashboard.php', { method: 'POST', body: formData });
    const data = await res.json();
    const msg = document.getElementById('statusMessage');
    if (data.success) {
      msg.className = 'status-message status-success';
      msg.textContent = data.message;
      setTimeout(() => location.reload(), 1500);
    }
  } catch (e) {
    console.error(e);
  }
});

// Download config
function downloadConfig() {
  const region = document.getElementById('configRegion').value;
  if (!region) { alert('Select a region first.'); return; }
  
  const configs = {
    <?php foreach ($servers as $s): ?>
      '<?= $s['region_code'] ?>': `[Proxy]\nServer=<?= $s['server_host'] ?>\nPort=<?= $s['server_port'] ?>\nProtocol=TLS\nKey=RenchXmodz_<?= strtoupper($s['region_code']) ?>_2026\n`,
    <?php endforeach; ?>
  };

  const names = {
    <?php foreach ($servers as $s): ?>
      '<?= $s['region_code'] ?>': '<?= str_replace([' ', '(', ')'], ['_', '', ''], $s['region_name']) ?>',
    <?php endforeach; ?>
  };

  const content = configs[region] || '';
  const blob = new Blob([content], { type: 'application/octet-stream' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'renchixmodz_' + (names[region] || region) + '.45b';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}
</script>
</body>
</html>
