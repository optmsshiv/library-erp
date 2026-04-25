<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// require_once '/../core/tenant.php';
require_once __DIR__ . '/core/tenant.php';

// This one line resolves the subdomain and connects to the right DB
$db = Tenant::db();

$input      = json_decode(file_get_contents('php://input'), true);
$member_id  = trim($input['member_id'] ?? '');
$password   = trim($input['password'] ?? '');

if (empty($member_id) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Member ID and password required']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT * FROM members WHERE member_id = ? AND active = 1 LIMIT 1");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();

    if (!$member || !password_verify($password, $member['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid Member ID or password']);
        exit;
    }

    // Generate simple token
    $token = bin2hex(random_bytes(32));

    // Save token in DB
    $db->prepare("UPDATE members SET auth_token = ?, last_login = NOW() WHERE id = ?")
        ->execute([$token, $member['id']]);

    echo json_encode([
        'success'     => true,
        'token'       => $token,
        'member_name' => $member['name'],
        'member_id'   => $member['member_id'],
        'class'       => $member['class'] ?? '',
        'photo'       => $member['photo'] ?? '',
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Login failed. Please try again.']);
}