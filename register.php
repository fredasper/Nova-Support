<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

$fullName = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$studentId = trim($input['student_id'] ?? '');
$program = trim($input['program'] ?? '');
$yearLevel = trim($input['year_level'] ?? '');
$subjects = trim($input['subjects'] ?? '');

if ($fullName === '' || $email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['error' => 'full_name, email, and password are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid email format']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(422);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

$check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$check->execute(['email' => $email]);
if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
    exit;
}

$availableCols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
$hasRole = in_array('role', $availableCols, true);
$hasStudentId = in_array('student_id', $availableCols, true);
$hasProgram = in_array('program', $availableCols, true);
$hasYearLevel = in_array('year_level', $availableCols, true);
$hasSubjects = in_array('subjects', $availableCols, true);

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$insertCols = ['full_name', 'email', 'password_hash'];
$params = [
    'full_name' => $fullName,
    'email' => $email,
    'password_hash' => $passwordHash
];

if ($hasRole) {
    $insertCols[] = 'role';
    $params['role'] = 'student';
}

if ($hasStudentId) {
    $insertCols[] = 'student_id';
    $params['student_id'] = $studentId !== '' ? $studentId : null;
}
if ($hasProgram) {
    $insertCols[] = 'program';
    $params['program'] = $program !== '' ? $program : null;
}
if ($hasYearLevel) {
    $insertCols[] = 'year_level';
    $params['year_level'] = $yearLevel !== '' ? $yearLevel : null;
}
if ($hasSubjects) {
    $insertCols[] = 'subjects';
    $params['subjects'] = $subjects !== '' ? $subjects : null;
}

$placeholders = array_map(fn($c) => ':' . $c, $insertCols);
$sql = 'INSERT INTO users (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode([
    'message' => 'Registration successful',
    'user_id' => $pdo->lastInsertId()
]);