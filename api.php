<?php
// api.php - REST API for proxy token verification
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$endpoint = $_GET['endpoint'] ?? '';

switch ($endpoint) {
    case 'verify':
        handleVerify();
        break;
    case 'create':
        handleCreate();
        break;
    case 'status':
        handleStatus();
        break;
    case 'servers':
        handleServers();
        break;
    default:
        jsonResponse(['error' => 'Unknown endpoint. Supported: verify, create, status, servers'], 404);
}

function handleVerify() {
    global $pdo;
    $token = $_GET['token'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token = str_replace('Bearer ', '', $token);
    
    if (empty($token)) {
        jsonResponse(['valid' => false, 'error' => 'Token required'], 401);
    }
    
    $stmt = $pdo->prepare("SELECT u.id, u.username, u.free_fire_uid, u.is_active, u.token_expires, 
                                  ps.server_region, ps.session_id
                           FROM users u
                           LEFT JOIN proxy_sessions ps ON ps.user_id = u.id AND ps.is_active = 1
                           WHERE u.proxy_token = ?");
    $stmt->execute([$token]);
    $data = $stmt->fetch();
    
    if (!$data) {
        jsonResponse(['valid' => false, 'error' => 'Invalid token'], 401);
    }
    
    if (strtotime($data['token_expires']) < time()) {
        jsonResponse(['valid' => false, 'error' => 'Token expired'], 401);
    }
    
    if (!$data['is_active']) {
        jsonResponse(['valid' => false, 'error' => 'Session not active'], 401);
    }
    
    jsonResponse([
        'valid' => true,
        'user_id' => $data['id'],
        'username' => $data['username'],
        'free_fire_uid' => $data['free_fire_uid'],
        'server_region' => $data['server_region'],
        'session_id' => $data['session_id'],
        'expires_at' => $data['token_expires']
    ]);
}

function handleCreate() {
    global $pdo;
    
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
    $expectedKey = 'admin-secret-key-change-me'; // Change this!
    
    if ($apiKey !== $expectedKey) {
        jsonResponse(['error' => 'Unauthorized'], 403);
    }
    
    $userId = (int)($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
    $hours = (int)($_POST['hours'] ?? PROXY_TOKEN_VALID_HOURS);
    
    if (!$userId) {
        jsonResponse(['error' => 'user_id required'], 400);
    }
    
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', strtotime("+$hours hours"));
    
    $stmt = $pdo->prepare("UPDATE users SET proxy_token = ?, token_expires = ? WHERE id = ?");
    $stmt->execute([$token, $expires, $userId]);
    
    jsonResponse(['success' => true, 'token' => $token, 'expires_at' => $expires]);
}

function handleStatus() {
    global $pdo;
    
    $servers = $pdo->query("SELECT region_code, region_name, ping_ms, is_online, load_percent FROM proxy_servers")->fetchAll();
    $activeSessions = $pdo->query("SELECT COUNT(*) as count FROM proxy_sessions WHERE is_active = 1")->fetchColumn();
    $totalUsers = $pdo->query("SELECT COUNT(*) as count FROM users")->fetchColumn();
    $onlineUsers = $pdo->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetchColumn();
    
    jsonResponse([
        'servers' => $servers,
        'stats' => [
            'active_sessions' => (int)$activeSessions,
            'total_users' => (int)$totalUsers,
            'online_users' => (int)$onlineUsers
        ]
    ]);
}

function handleServers() {
    global $pdo;
    $servers = $pdo->query("SELECT region_code, region_name, server_host, server_port, ping_ms, is_online, load_percent FROM proxy_servers WHERE is_online = 1")->fetchAll();
    jsonResponse(['servers' => $servers]);
}
?>
