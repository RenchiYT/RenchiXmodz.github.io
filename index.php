<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= SITE_NAME ?> - Free Fire Proxy</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', sans-serif; background: #0d0d0d; color: #fff; line-height: 1.6; }
    header { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); padding: 18px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e94560; position: sticky; top: 0; z-index: 100; }
    .logo { font-size: 24px; font-weight: 800; color: #e94560; }
    .logo span { color: #fff; }
    nav a { color: #ccc; text-decoration: none; margin-left: 22px; font-size: 14px; transition: 0.3s; }
    nav a:hover { color: #e94560; }
    .hero { background: linear-gradient(135deg, #0f3460, #1a1a2e); text-align: center; padding: 80px 20px 60px; }
    .hero h1 { font-size: 44px; font-weight: 800; margin-bottom: 12px; }
    .hero h1 span { color: #e94560; }
    .hero p { font-size: 17px; color: #bbb; max-width: 680px; margin: 0 auto 30px; }
    .btn { display: inline-block; background: #e94560; color: #fff; padding: 14px 38px; border-radius: 30px; text-decoration: none; font-weight: 700; font-size: 15px; transition: 0.3s; border: none; cursor: pointer; }
    .btn:hover { background: #d63851; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(233, 69, 96, 0.4); }
    .btn-outline { background: transparent; border: 2px solid #e94560; color: #e94560; }
    .btn-outline:hover { background: #e94560; color: #fff; }
    .btn-discord { background: #5865F2; }
    .btn-discord:hover { background: #4752c4; }
    .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; padding: 60px 30px; max-width: 1200px; margin: 0 auto; }
    .card { background: #1a1a2e; padding: 30px 25px; border-radius: 15px; text-align: center; border: 1px solid #2a2a4a; transition: 0.3s; }
    .card:hover { border-color: #e94560; transform: translateY(-5px); }
    .card i { font-size: 40px; color: #e94560; margin-bottom: 15px; }
    .card h3 { font-size: 20px; margin-bottom: 10px; }
    .card p { color: #999; font-size: 14px; }
    .cta-section { text-align: center; padding: 60px 30px; }
    .cta-section h2 { font-size: 32px; margin-bottom: 25px; }
    footer { background: #111; text-align: center; padding: 25px; border-top: 1px solid #222; color: #666; font-size: 13px; }
    footer a { color: #e94560; text-decoration: none; }
    @media (max-width: 768px) { header { flex-direction: column; gap: 10px; } nav a { margin: 0 10px; font-size: 13px; } .hero h1 { font-size: 28px; } }
  </style>
</head>
<body>
<header>
  <div class="logo">Renchi<span>Xmodz</span></div>
  <nav>
    <?php if (isLoggedIn()): ?>
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </nav>
</header>

<section class="hero">
  <h1>🚀 Free Fire <span>Proxy Server</span></h1>
  <p>Route your game traffic through our high-speed proxy servers. Reduce ping, bypass region locks, and enjoy a smoother Free Fire experience. No root required. No complicated setup.</p>
  <?php if (isLoggedIn()): ?>
    <a href="dashboard.php" class="btn">Go to Dashboard</a>
  <?php else: ?>
    <a href="register.php" class="btn">Get Started Free</a>
    <a href="login.php" class="btn btn-outline" style="margin-left:12px;">Login</a>
  <?php endif; ?>
</section>

<section class="features">
  <div class="card"><i class="fas fa-globe"></i><h3>Multiple Regions</h3><p>Servers in US, Europe, Singapore, India, Japan, and Brazil.</p></div>
  <div class="card"><i class="fas fa-mobile-alt"></i><h3>No Root Required</h3><p>Works on any Android device. Just paste a config file and play.</p></div>
  <div class="card"><i class="fas fa-bolt"></i><h3>Reduced Lag</h3><p>Optimized routing reduces packet loss and lowers ping.</p></div>
  <div class="card"><i class="fas fa-lock-open"></i><h3>Region Unlock</h3><p>Access region-locked events and servers worldwide.</p></div>
  <div class="card"><i class="fas fa-shield-alt"></i><h3>Safe & Secure</h3><p>Encrypted tunnel between your device and our servers.</p></div>
  <div class="card"><i class="fas fa-phone-alt"></i><h3>Discord Integration</h3><p>Login with Discord for quick account management.</p></div>
</section>

<section class="cta-section">
  <h2>Ready to <span style="color:#e94560;">dominate</span> Free Fire?</h2>
  <a href="register.php" class="btn btn-discord"><i class="fab fa-discord"></i> Join with Discord</a>
  <span style="color:#666;margin:0 10px;">or</span>
  <a href="register.php" class="btn">Create Account</a>
</section>

<footer>
  <p>&copy; 2026 <a href="#">RenchiXmodz</a> — Free Fire Proxy Server. Not affiliated with Garena.</p>
</footer>
</body>
</html>
