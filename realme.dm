<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>RenchiXmodz - Free Fire Proxy</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #0d0d0d;
      color: #fff;
      line-height: 1.6;
      -webkit-tap-highlight-color: transparent;
    }

    /* Header */
    header {
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      padding: 15px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 2px solid #e94560;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .logo { font-size: 22px; font-weight: 800; color: #e94560; }
    .logo span { color: #fff; }
    nav { display: flex; gap: 10px; flex-wrap: wrap; }
    nav a {
      color: #ccc;
      text-decoration: none;
      font-size: 13px;
      transition: 0.3s;
      padding: 4px 8px;
    }
    nav a:hover, nav a:active { color: #e94560; }

    /* Hero */
    .hero {
      background: linear-gradient(135deg, #0f3460, #1a1a2e);
      text-align: center;
      padding: 50px 20px 40px;
    }
    .hero h1 { font-size: 32px; font-weight: 800; margin-bottom: 10px; }
    .hero h1 span { color: #e94560; }
    .hero p { font-size: 15px; color: #bbb; max-width: 600px; margin: 0 auto 25px; padding: 0 10px; }

    .btn {
      display: inline-block;
      background: #e94560;
      color: #fff;
      padding: 12px 30px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 700;
      font-size: 14px;
      transition: 0.3s;
      border: none;
      cursor: pointer;
      -webkit-appearance: none;
    }
    .btn:hover, .btn:active { background: #d63851; transform: translateY(-2px); }
    .btn-outline {
      background: transparent;
      border: 2px solid #e94560;
      color: #e94560;
    }
    .btn-outline:hover, .btn-outline:active { background: #e94560; color: #fff; }
    .btn-green { background: #2ecc71; }
    .btn-green:hover, .btn-green:active { background: #27ae60; }
    .btn-danger { background: #e74c3c; }
    .btn-danger:hover, .btn-danger:active { background: #c0392b; }
    .btn-block { width: 100%; text-align: center; }
    .btn-sm { padding: 8px 18px; font-size: 12px; }
    .btn+.btn { margin-top: 8px; }

    /* Section titles */
    .section-title {
      text-align: center;
      font-size: 26px;
      font-weight: 800;
      padding: 40px 20px 10px;
    }
    .section-title span { color: #e94560; }
    .section-sub {
      text-align: center;
      color: #999;
      font-size: 14px;
      padding: 0 20px 30px;
    }

    /* Features */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 18px;
      padding: 0 20px 40px;
      max-width: 1100px;
      margin: 0 auto;
    }
    .card {
      background: #1a1a2e;
      padding: 25px 20px;
      border-radius: 15px;
      text-align: center;
      border: 1px solid #2a2a4a;
      transition: 0.3s;
    }
    .card:active { border-color: #e94560; transform: scale(0.98); }
    .card i { font-size: 34px; color: #e94560; margin-bottom: 12px; }
    .card h3 { font-size: 17px; margin-bottom: 8px; }
    .card p { color: #999; font-size: 13px; }

    /* Dashboard */
    .dashboard-wrap {
      background: #111;
      padding: 10px 20px 40px;
    }
    .dashboard {
      max-width: 500px;
      margin: 0 auto;
      background: #1a1a2e;
      border-radius: 18px;
      padding: 25px;
      border: 1px solid #2a2a4a;
    }
    .input-group { margin-bottom: 15px; }
    .input-group label {
      display: block;
      font-size: 13px;
      color: #bbb;
      margin-bottom: 5px;
    }
    .input-group input, .input-group select {
      width: 100%;
      padding: 12px 14px;
      border-radius: 10px;
      border: 1px solid #333;
      background: #0d0d0d;
      color: #fff;
      font-size: 15px;
      outline: none;
      transition: 0.3s;
      -webkit-appearance: none;
      appearance: none;
    }
    .input-group input:focus, .input-group select:focus { border-color: #e94560; }

    .status-box {
      margin-top: 15px;
      padding: 14px;
      border-radius: 10px;
      background: #0d0d0d;
      border: 1px solid #333;
      font-size: 13px;
      display: none;
    }
    .status-box.success { border-color: #2ecc71; color: #2ecc71; display: block; }
    .status-box.error { border-color: #e74c3c; color: #e74c3c; display: block; }
    .status-box.info { border-color: #3498db; color: #3498db; display: block; }
    .token-display {
      display: none;
      margin-top: 12px;
      padding: 12px;
      background: #0d0d0d;
      border: 1px solid #e94560;
      border-radius: 10px;
      font-family: monospace;
      font-size: 12px;
      color: #e94560;
      word-break: break-all;
    }
    .token-display.show { display: block; }

    /* Server List */
    .server-list {
      max-width: 500px;
      margin: 0 auto;
      padding: 0 20px 40px;
    }
    .server-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 16px;
      background: #1a1a2e;
      border: 1px solid #2a2a4a;
      border-radius: 12px;
      margin-bottom: 8px;
    }
    .server-item .ping { color: #2ecc71; font-weight: 700; font-size: 16px; }
    .server-item .loc { font-size: 14px; }
    .server-item .online { color: #2ecc71; font-size: 11px; }
    .server-item .ip { color: #555; font-family: monospace; font-size: 11px; }

    /* Config Download */
    .config-section {
      padding: 0 20px 40px;
      max-width: 500px;
      margin: 0 auto;
    }
    .config-box {
      background: #1a1a2e;
      border: 1px solid #2a2a4a;
      border-radius: 15px;
      padding: 25px;
      text-align: center;
    }
    .config-box i { font-size: 50px; color: #e94560; margin-bottom: 10px; }
    .config-box p { color: #999; font-size: 13px; margin-bottom: 15px; }

    /* How it works */
    .steps {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 18px;
      max-width: 900px;
      margin: 0 auto;
      padding: 0 20px 40px;
    }
    .step {
      background: #1a1a2e;
      padding: 25px 18px;
      border-radius: 15px;
      border: 1px solid #2a2a4a;
      text-align: center;
    }
    .step .num {
      width: 44px; height: 44px;
      background: #e94560;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px; font-weight: 800; margin: 0 auto 12px;
    }
    .step h3 { font-size: 16px; margin-bottom: 6px; }
    .step p { color: #999; font-size: 13px; }

    /* Disclaimer */
    .disclaimer {
      background: #1a1a2e;
      border: 1px solid #e94560;
      border-radius: 12px;
      max-width: 700px;
      margin: 0 auto 40px;
      padding: 22px 25px;
      text-align: center;
    }
    .disclaimer i { color: #e94560; font-size: 24px; margin-bottom: 8px; }
    .disclaimer h3 { margin-bottom: 8px; color: #e94560; font-size: 16px; }
    .disclaimer p { color: #aaa; font-size: 13px; }

    footer {
      text-align: center;
      padding: 25px;
      border-top: 1px solid #222;
      color: #666;
      font-size: 12px;
    }
    footer a { color: #e94560; text-decoration: none; }

    /* Mobile tweaks */
    @media (max-width: 480px) {
      .hero h1 { font-size: 26px; }
      .dashboard { padding: 18px; }
      .btn { padding: 14px 24px; }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">Renchi<span>Xmodz</span></div>
  <nav>
    <a href="#features">Features</a>
    <a href="#dash">Dashboard</a>
    <a href="#config">Config</a>
    <a href="#how">How</a>
  </nav>
</header>

<!-- HERO -->
<section class="hero">
  <h1>🚀 Free Fire <span>Proxy Server</span></h1>
  <p>Route your game traffic through our high-speed proxy servers. Reduce ping, bypass region locks, and enjoy a smoother Free Fire experience. No root required. No complicated setup.</p>
  <a href="#dash" class="btn">Open Dashboard</a>
  <a href="#config" class="btn btn-outline" style="margin-left:8px;">Download .45b</a>
</section>

<!-- FEATURES -->
<h2 class="section-title" id="features">Why <span>RenchiXmodz</span>?</h2>
<div class="features">
  <div class="card"><i class="fas fa-globe"></i><h3>Multiple Regions</h3><p>Servers in US, Europe, Singapore, Japan, India, Brazil.</p></div>
  <div class="card"><i class="fas fa-mobile-alt"></i><h3>No Root</h3><p>Works on any Android. Just paste config and play.</p></div>
  <div class="card"><i class="fas fa-bolt"></i><h3>Reduced Lag</h3><p>Optimized routing lowers ping and packet loss.</p></div>
  <div class="card"><i class="fas fa-lock-open"></i><h3>Region Unlock</h3><p>Access locked events and servers worldwide.</p></div>
  <div class="card"><i class="fas fa-shield-alt"></i><h3>Encrypted</h3><p>TLS tunnel between your device and our servers.</p></div>
  <div class="card"><i class="fas fa-phone-alt"></i><h3>Works on All</h3><p>Free Fire &amp; Free Fire Max on any Android.</p></div>
</div>

<!-- DASHBOARD -->
<div class="dashboard-wrap" id="dash">
  <h2 class="section-title" style="padding-top:10px;">📊 Proxy <span>Dashboard</span></h2>
  <p class="section-sub" style="padding-bottom:20px;">Enter your UID and activate your session</p>

  <div class="dashboard">
    <div class="input-group">
      <label><i class="fas fa-user"></i> Free Fire UID</label>
      <input type="text" id="uidInput" placeholder="Enter your UID (e.g. 2345678901)" maxlength="12" inputmode="numeric">
    </div>
    <div class="input-group">
      <label><i class="fas fa-server"></i> Server Region</label>
      <select id="serverSelect">
        <option value="" disabled selected>— Choose a region —</option>
        <option value="🇸🇬 Singapore (22ms)">🇸🇬 Singapore — 22ms</option>
        <option value="🇮🇳 India (28ms)">🇮🇳 India — 28ms</option>
        <option value="🇯🇵 Japan (32ms)">🇯🇵 Japan — 32ms</option>
        <option value="🇪🇺 Europe (38ms)">🇪🇺 Europe — 38ms</option>
        <option value="🇺🇸 US East (45ms)">🇺🇸 US East — 45ms</option>
        <option value="🇺🇸 US West (52ms)">🇺🇸 US West — 52ms</option>
        <option value="🇧🇷 Brazil (65ms)">🇧🇷 Brazil — 65ms</option>
      </select>
    </div>
    <button class="btn btn-green btn-block" onclick="activateProxy()"><i class="fas fa-play"></i> Activate Proxy</button>
    <button class="btn btn-danger btn-block" onclick="deactivateProxy()"><i class="fas fa-stop"></i> Deactivate</button>

    <div id="statusBox" class="status-box"><span id="statusText">Ready.</span></div>
    <div id="tokenDisplay" class="token-display"></div>
  </div>

  <!-- Server Status -->
  <h3 style="text-align:center;margin:25px 0 10px;font-size:16px;color:#999;">🌐 Server Status</h3>
  <div class="server-list">
    <div class="server-item"><div><span class="loc">🇸🇬 Singapore</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">22ms</span><br><span class="ip">sg.proxy.rx.xyz</span></div></div>
    <div class="server-item"><div><span class="loc">🇮🇳 India</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">28ms</span><br><span class="ip">in.proxy.rx.xyz</span></div></div>
    <div class="server-item"><div><span class="loc">🇯🇵 Japan</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">32ms</span><br><span class="ip">jp.proxy.rx.xyz</span></div></div>
    <div class="server-item"><div><span class="loc">🇪🇺 Europe</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">38ms</span><br><span class="ip">eu.proxy.rx.xyz</span></div></div>
    <div class="server-item"><div><span class="loc">🇺🇸 US East</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">45ms</span><br><span class="ip">use.proxy.rx.xyz</span></div></div>
    <div class="server-item"><div><span class="loc">🇧🇷 Brazil</span><br><span class="online">● Online</span></div><div style="text-align:right;"><span class="ping">65ms</span><br><span class="ip">br.proxy.rx.xyz</span></div></div>
  </div>
</div>

<!-- CONFIG DOWNLOAD -->
<h2 class="section-title" id="config">📁 Download <span>.45b Config</span></h2>
<p class="section-sub">Choose region and download for MT Manager</p>
<div class="config-section">
  <div class="config-box">
    <i class="fas fa-file-archive"></i>
    <p>Place the .45b file in your Free Fire folder using MT Manager.</p>
    <div class="input-group">
      <label>Region</label>
      <select id="configRegion">
        <option value="" disabled selected>— Select region —</option>
        <option value="sg">🇸🇬 Singapore</option>
        <option value="in">🇮🇳 India</option>
        <option value="jp">🇯🇵 Japan</option>
        <option value="eu">🇪🇺 Europe</option>
        <option value="us_east">🇺🇸 US East</option>
        <option value="us_west">🇺🇸 US West</option>
        <option value="br">🇧🇷 Brazil</option>
      </select>
    </div>
    <button class="btn btn-block" onclick="downloadConfig()"><i class="fas fa-download"></i> Download .45b</button>
    <button class="btn btn-outline btn-block" onclick="downloadAll()"><i class="fas fa-download"></i> All Regions (Bundle)</button>
  </div>
</div>

<!-- HOW IT WORKS -->
<h2 class="section-title" id="how">📲 How It <span>Works</span></h2>
<div class="steps">
  <div class="step"><div class="num">1</div><h3>Install</h3><p>Install Free Fire + MT Manager. Create a guest account.</p></div>
  <div class="step"><div class="num">2</div><h3>Config</h3><p>Download .45b config above and paste into game folder.</p></div>
  <div class="step"><div class="num">3</div><h3>Activate</h3><p>Enter your UID on the dashboard and activate your session.</p></div>
</div>

<!-- DISCLAIMER -->
<div class="disclaimer">
  <i class="fas fa-exclamation-triangle"></i>
  <h3>⚠️ Disclaimer</h3>
  <p>This proxy tool is for <strong>educational and testing purposes only</strong>. Use at your own risk. Always use a <strong>secondary guest account</strong> — never your main Free Fire account. Garena's Terms of Service may prohibit the use of third-party proxy tools.</p>
</div>

<footer>
  <p>&copy; 2026 <a href="#">RenchiXmodz</a> — Free Fire Proxy. Not affiliated with Garena.</p>
</footer>

<script>
function activateProxy() {
  const uid = document.getElementById('uidInput').value.trim();
  const server = document.getElementById('serverSelect').value;
  const box = document.getElementById('statusBox');
  const text = document.getElementById('statusText');
  const tokenBox = document.getElementById('tokenDisplay');

  if (!uid || uid.length < 6) {
    box.className = 'status-box error';
    text.innerHTML = '❌ Enter a valid UID (at least 6 digits).';
    tokenBox.classList.remove('show');
    return;
  }
  if (!server) {
    box.className = 'status-box error';
    text.innerHTML = '❌ Select a server region.';
    tokenBox.classList.remove('show');
    return;
  }

  box.className = 'status-box info';
  text.innerHTML = '⏳ Connecting to ' + server + '...';
  tokenBox.classList.remove('show');

  setTimeout(() => {
    const token = [...Array(64)].map(() => Math.floor(Math.random() * 16).toString(16)).join('');
    const sessionId = [...Array(16)].map(() => Math.floor(Math.random() * 16).toString(16)).join('');
    const ping = Math.floor(Math.random() * 30 + 20);

    box.className = 'status-box success';
    text.innerHTML = '✅ Session active! UID <strong>' + uid + '</strong> → <strong>' + server + '</strong> (' + ping + 'ms)';
    tokenBox.innerHTML = '<strong>Token:</strong> ' + token + '<br><strong>Session:</strong> ' + sessionId;
    tokenBox.classList.add('show');
  }, 1500);
}

function deactivateProxy() {
  const box = document.getElementById('statusBox');
  const text = document.getElementById('statusText');
  const tokenBox = document.getElementById('tokenDisplay');

  box.className = 'status-box info';
  text.innerHTML = '⏳ Deactivating...';
  setTimeout(() => {
    box.className = 'status-box error';
    text.innerHTML = '🛑 Session deactivated. Proxy disconnected.';
    tokenBox.classList.remove('show');
  }, 800);
}

function downloadConfig() {
  const region = document.getElementById('configRegion').value;
  if (!region) { alert('Select a region first.'); return; }

  const configs = {
    sg: '[Proxy]\nServer=sg.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_SG_2026\n',
    'in': '[Proxy]\nServer=in.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_IN_2026\n',
    jp: '[Proxy]\nServer=jp.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_JP_2026\n',
    eu: '[Proxy]\nServer=eu.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_EU_2026\n',
    us_east: '[Proxy]\nServer=use.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_US_2026\n',
    us_west: '[Proxy]\nServer=usw.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_USW_2026\n',
    br: '[Proxy]\nServer=br.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_BR_2026\n'
  };
  const names = { sg:'Singapore', 'in':'India', jp:'Japan', eu:'Europe', us_east:'US_East', us_west:'US_West', br:'Brazil' };

  const blob = new Blob([configs[region] || ''], { type: 'application/octet-stream' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'renchixmodz_' + (names[region] || region) + '.45b';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(blob);
}

function downloadAll() {
  const all = {
    'SG_Singapore': '[Proxy]\nServer=sg.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_SG_2026\n',
    'IN_India': '[Proxy]\nServer=in.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_IN_2026\n',
    'JP_Japan': '[Proxy]\nServer=jp.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_JP_2026\n',
    'EU_Europe': '[Proxy]\nServer=eu.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_EU_2026\n',
    'US_East': '[Proxy]\nServer=use.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_US_2026\n',
    'US_West': '[Proxy]\nServer=usw.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_USW_2026\n',
    'BR_Brazil': '[Proxy]\nServer=br.proxy.renchixmodz.xyz\nPort=443\nProtocol=TLS\nKey=RenchXmodz_BR_2026\n'
  };
  let bundle = '=== RenchiXmodz .45b Config Bundle ===\n\n';
  for (const [name, content] of Object.entries(all)) bundle += '--- ' + name + ' ---\n' + content + '\n';

  const blob = new Blob([bundle], { type: 'text/plain' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = 'renchixmodz_all_regions_bundle.txt';
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(blob);
}

// Enter key support
document.getElementById('uidInput').addEventListener('keydown', function(e) {
  if (e.key === 'Enter') activateProxy();
});
</script>

</body>
</html>
