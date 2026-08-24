<?php require_once 'config.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] === 'banned') {
                $error = 'Your account has been banned. Contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['ff_uid'] = $user['free_fire_uid'];

                $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
                logActivity($pdo, $user['id'], 'login', 'User logged in');
                redirect('dashboard.php');
            }
        } else {
            $error = 'Invalid email/username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - <?= SITE_NAME ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Segoe UI',sans-serif; background:#0d0d0d; color:#fff; display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .auth-card { background:#1a1a2e; border:1px solid #2a2a4a; border-radius:20px; padding:40px; width:420px; max-width:90%; }
    .auth-card h1 { text-align:center; margin-bottom:5px; }
    .auth-card h1 span { color:#e94560; }
    .auth-card p { text-align:center; color:#999; margin-bottom:25px; font-size:14px; }
    .input-group { margin-bottom:18px; }
    .input-group label { display:block; font-size:13px; color:#bbb; margin-bottom:5px; }
    .input-group input { width:100%; padding:12px 16px; border-radius:10px; border:1px solid #333; background:#0d0d0d; color:#fff; font-size:15px; outline:none; transition:0.3s; }
    .input-group input:focus { border-color:#e94560; }
    .btn { display:block; width:100%; background:#e94560; color:#fff; padding:14px; border-radius:30px; border:none; font-weight:700; font-size:15px; cursor:pointer; transition:0.3s; margin-top:5px; }
    .btn:hover { background:#d63851; }
    .btn-discord { background:#5865F2; margin-top:12px; }
    .btn-discord:hover { background:#4752c4; }
    .error { background:#2d1a1a; border:1px solid #e74c3c; color:#e74c3c; padding:12px; border-radius:10px; margin-bottom:15px; font-size:13px; text-align:center; }
    .divider { display:flex; align-items:center; margin:15px 0; color:#666; font-size:13px; }
    .divider::before, .divider::after { content:''; flex:1; border-bottom:1px solid #333; }
    .divider span { padding:0 10px; }
    .footer-link { text-align:center; margin-top:15px; font-size:13px; color:#666; }
    .footer-link a { color:#e94560; text-decoration:none; }
    .checkbox-group { display:flex; align-items:center; margin-bottom:18px; font-size:13px; color:#bbb; }
    .checkbox-group input { margin-right:8px; }
  </style>
</head>
<body>
  <div class="auth-card">
    <h1><span>Renchi</span>Xmodz</h1>
    <p>Sign in to your account</p>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <form method="POST">
      <div class="input-group">
        <label><i class="fas fa-envelope"></i> Email or Username</label>
        <input type="text" name="email" placeholder="your@email.com or username" required>
      </div>
      <div class="input-group">
        <label><i class="fas fa-lock"></i> Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <div class="checkbox-group">
        <input type="checkbox" name="remember" id="remember">
        <label for="remember">Remember me</label>
      </div>
      <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>
    <div class="divider"><span>OR</span></div>
    <a href="discord_login.php" class="btn btn-discord"><i class="fab fa-discord"></i> Sign in with Discord</a>
    <div class="footer-link">Don't have an account? <a href="register.php">Register</a></div>
  </div>
</body>
</html>
