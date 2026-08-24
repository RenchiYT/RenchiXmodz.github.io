<?php
require_once 'config.php';

if (empty(DISCORD_CLIENT_ID) || empty(DISCORD_CLIENT_SECRET)) {
    die('Discord OAuth is not configured. Contact the administrator.');
}

$state = generateToken(32);
$_SESSION['discord_state'] = $state;

$params = http_build_query([
    'client_id' => DISCORD_CLIENT_ID,
    'redirect_uri' => DISCORD_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'identify email guilds.join',
    'state' => $state,
    'prompt' => 'consent'
]);

redirect('https://discord.com/api/oauth2/authorize?' . $params);
?>
