<?php require_once 'config.php';

if (isLoggedIn()) redirect('dashboard.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'Username must be 3-20 characters.';
    } else {
        // Check existing
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Username or email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);
            logActivity($pdo, $pdo->lastInsertId(), 'registered', 'New registration');
            $success = 'Account created! You can now login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - <?= SITE_NAME ?></title>
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
    .success { background:#1a2d1a; border:1px solid #2ecc71; color:#2ecc71; padding:12px; border-radius:10px; margin-bottom:15px; font-size:13px; text-align:center; }
    .divider { display:flex; align-items:center; margin:15px 0; color:#666; font-size:13px; }
    .divider::before, .divider::after { content:''; flex:1; border-bottom:1px solid #333; }
    .divider span { padding:0 10px; }
    .footer-link { text-align:center; margin-top:15px; font-size:13px; color:#666; }
    .footer-link a { color:#e94560; text-decoration:none; }
  </style>
</head>
<body>
  <div class="auth-card">
    <h1><span>Renchi</span>Xmodz</h1>
    <p>Create your account</p>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <form method="POST">
      <div class="input-group">
        <label><i class="fas fa-user"></i> Username</label>
        <input type="text" name="username" placeholder="Choose a username" required minlength="3" maxlength="20">
      </div>
      <div class="input-group">
        <label><i class="fas fa-envelope"></i> Email</label>
        <input type="email" name="email" placeholder="your@email.com" required>
      </div>
      <div class="input-group">
        <label><i class="fas fa-lock"></i> Password</label>
        <input type="password" name="password" placeholder="Min. 6 characters" required minlength="6">
      </div>
      <div class="input-group">
        <label><i class="fas fa-check-circle"></i> Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Repeat password" required>
      </div>
      <button type="submit" class="btn"><i class="fas fa-user-plus"></i> Create Account</button>
    </form>
    <div class="divider"><span>OR</span></div>
    <a href="discord_login.php" class="btn btn-discord"><i class="fab fa-discord"></i> Sign up with Discord</a>
    <div class="footer-link">Already have an account? <a href="login.php">Login</a></div>
  </div>
</body>
</html>
