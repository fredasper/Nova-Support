<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_GET['email'] ?? '');
if ($email === '') {
    http_response_code(422);
    echo json_encode(['error' => 'email is required']);
    exit;
}

$availableCols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
$optionalCols = [];
foreach (['student_id', 'program', 'year_level', 'subjects'] as $col) {
    if (in_array($col, $availableCols, true)) {
        $optionalCols[] = $col;
    }
}

$selectCols = array_merge(['id', 'full_name', 'email', 'role'], $optionalCols);
$sql = 'SELECT ' . implode(', ', $selectCols) . ' FROM users WHERE email = :email LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

echo json_encode([
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