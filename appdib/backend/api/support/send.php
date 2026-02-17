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
$officeKey = trim($input['office_key'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if ($email === '' || $officeKey === '' || $subject === '' || $message === '') {
    http_response_code(422);
    echo json_encode(['error' => 'email, office_key, subject, and message are required']);
    exit;
}

$userStmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$userStmt->execute(['email' => $email]);
$user = $userStmt->fetch();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO support_messages (user_id, office_key, subject, message)
    VALUES (:user_id, :office_key, :subject, :message)
");

$stmt->execute([
    'user_id' => $user['id'],
    'office_key' => $officeKey,
    'subject' => $subject,
    'message' => $message
]);

echo json_encode(['message' => 'Support message saved successfully']);