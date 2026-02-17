<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'email and password are required']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, full_name, email, password_hash FROM admins WHERE email = :email LIMIT 1");
$stmt->execute(['email' => $email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid admin credentials']);
    exit;
}

$_SESSION['admin_id'] = $admin['id'];
$_SESSION['role'] = 'admin';

echo json_encode([
    'message' => 'Admin login successful',
    'admin' => [
        'id' => $admin['id'],
        'full_name' => $admin['full_name'],
        'email' => $admin['email'],
        'role' => 'admin'
    ]
]);