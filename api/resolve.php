<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '/../config/db.php';

$code = strtoupper(trim($_GET['code'] ?? ''));

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Library code required']);
    exit;
}

try {
    $dsn = "mysql:host=" . CONFIG_DB_HOST . ";dbname=" . CONFIG_DB_NAME . ";charset=utf8mb4";
    $db  = new PDO($dsn, CONFIG_DB_USER, CONFIG_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $db->prepare("SELECT * FROM subdomain_map WHERE library_code = ? AND active = 1");
    $stmt->execute([$code]);
    $library = $stmt->fetch();

    if (!$library) {
        echo json_encode(['success' => false, 'message' => 'Invalid library code. Please check and try again.']);
        exit;
    }

    echo json_encode([
        'success'      => true,
        'library_name' => $library['client_name'],
        'library_code' => $library['library_code'],
        'api_base_url' => 'https://' . $library['subdomain'] . '.optms.co.in/api',
        'plan'         => $library['plan'],
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}