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

$availableCols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
$optionalCols = [];
foreach (['student_id', 'program', 'year_level', 'subjects'] as $col) {
    if (in_array($col, $availableCols, true)) {
        $optionalCols[] = $col;
    }
}

$selectCols = array_merge(['id', 'full_name', 'email', 'password_hash', 'role'], $optionalCols);
$sql = 'SELECT ' . implode(', ', $selectCols) . ' FROM users WHERE email = :email LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'message' => 'Login successful',
    'user' => [
        'id' => $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'student_id' => $user['student_id'] ?? null,
        'program' => $user['program'] ?? null,
        'year_level' => $user['year_level'] ?? null,
        'subjects' => $user['subjects'] ?? null
    ]
]);