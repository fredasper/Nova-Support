<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$email = trim($input['email'] ?? '');
if ($email === '') {
    http_response_code(422);
    echo json_encode(['error' => 'email is required']);
    exit;
}

$availableCols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
$allowedInput = [
    'full_name' => trim($input['full_name'] ?? ''),
    'student_id' => trim($input['student_id'] ?? ''),
    'program' => trim($input['program'] ?? ''),
    'year_level' => trim($input['year_level'] ?? ''),
    'subjects' => trim($input['subjects'] ?? '')
];

$setParts = [];
$params = ['email' => $email];

foreach ($allowedInput as $col => $value) {
    if (!in_array($col, $availableCols, true)) {
        continue;
    }
    if ($value === '') {
        continue;
    }
    $setParts[] = "$col = :$col";
    $params[$col] = $value;
}

if (count($setParts) === 0) {
    http_response_code(422);
    echo json_encode(['error' => 'No valid profile fields to update']);
    exit;
}

$sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE email = :email';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode(['message' => 'Profile updated successfully']);