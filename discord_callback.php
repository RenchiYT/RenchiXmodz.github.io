<?php
require_once 'config.php';

if (!isset($_GET['code']) || !isset($_GET['state'])) {
    die('Invalid OAuth request.');
}

// Verify state
if (!isset($_SESSION['discord_state']) || $_GET['state'] !== $_SESSION['discord_state']) {
    unset($_SESSION['discord_state']);
    die('State mismatch. Possible CSRF attack.');
}
unset($_SESSION['discord_state']);

// Exchange code for token
$ch = curl_init('https://discord.com/api/oauth2/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'client_id' => DISCORD_CLIENT_ID,
        'client_secret' => DISCORD_CLIENT_SECRET,
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => DISCORD_REDIRECT_URI
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    die('Failed to authenticate with Discord.');
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? null;

if (!$accessToken) {
    die('No access token received.');
}

// Get user info
$ch = curl_init('https://discord.com/api/users/@me');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => true
]);
$userData = json_decode(curl_exec($ch), true);
curl_close($ch);

$discordId = $userData['id'] ?? null;
$username = $userData['username'] ?? 'Unknown';
$avatar = $userData['avatar'] ?? null;
$email = $userData['email'] ?? null;

if (!$discordId) {
    die('Could not fetch Discord user data.');
}

// Try to join the Discord server
if (!empty(DISCORD_GUILD_ID)) {
    $ch = curl_init("https://discord.com/api/guilds/" . DISCORD_GUILD_ID . "/members/" . $discordId);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bot ' . DISCORD_CLIENT_SECRET, // Bot token needed here
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode(['access_token' => $accessToken]),
        CURLOPT_RETURNTRANSFER => true
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// Check if user exists by Discord ID
$stmt = $pdo->prepare("SELECT * FROM users WHERE discord_id = ?");
$stmt->execute([$discordId]);
$user = $stmt->fetch();

if ($user) {
    // Existing user - login
    if ($user['role'] === 'banned') {
        die('Your account has been banned.');
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
    logActivity($pdo, $user['id'], 'discord_login', 'Logged in via Discord');
} else {
    // New user - check if email exists
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existing = $stmt->fetch();
        if ($existing) {
            // Link Discord to existing account
            $pdo->prepare("UPDATE users SET discord_id = ?, discord_avatar = ? WHERE id = ?")
                ->execute([$discordId, $avatar, $existing['id']]);
            $_SESSION['user_id'] = $existing['id'];
            $_SESSION['username'] = $existing['username'];
            $_SESSION['role'] = $existing['role'];
            logActivity($pdo, $existing['id'], 'discord_linked', 'Discord linked to existing account');
        } else {
            // Create new account
            $generatedUsername = $username . '#' . substr($discordId, -4);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, discord_id, discord_avatar) VALUES (?, ?, ?, ?)");
            $stmt->execute([$generatedUsername, $email ?? $discordId . '@discord.user', $discordId, $avatar]);
            $newId = $pdo->lastInsertId();
            $_SESSION['user_id'] = $newId;
            $_SESSION['username'] = $generatedUsername;
            $_SESSION['role'] = 'user';
            logActivity($pdo, $newId, 'discord_register', 'Registered via Discord');
        }
    } else {
        // Create account without email
        $generatedUsername = $username . '#' . substr($discordId, -4);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, discord_id, discord_avatar) VALUES (?, ?, ?, ?)");
        $stmt->execute([$generatedUsername, $discordId . '@discord.renchixmodz', $discordId, $avatar]);
        $newId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $newId;
        $_SESSION['username'] = $generatedUsername;
        $_SESSION['role'] = 'user';
        logActivity($pdo, $newId, 'discord_register', 'Registered via Discord');
    }
}

redirect('dashboard.php');
?>
