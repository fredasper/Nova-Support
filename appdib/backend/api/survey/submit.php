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
$satisfaction = $input['satisfaction'] ?? null;
$easeOfUse = $input['ease_of_use'] ?? null;
$helpfulness = $input['helpfulness'] ?? null;
$responseTime = $input['response_time'] ?? null;
$recommend = $input['recommend_score'] ?? null;
$feedback = trim($input['feedback'] ?? '');

if ($email === '') {
    http_response_code(422);
    echo json_encode(['error' => 'email is required']);
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
    INSERT INTO surveys (
        user_id, satisfaction, ease_of_use, helpfulness, response_time, recommend_score, feedback
    ) VALUES (
        :user_id, :satisfaction, :ease_of_use, :helpfulness, :response_time, :recommend_score, :feedback
    )
");

$stmt->execute([
    'user_id' => $user['id'],
    'satisfaction' => $satisfaction,
    'ease_of_use' => $easeOfUse,
    'helpfulness' => $helpfulness,
    'response_time' => $responseTime,
    'recommend_score' => $recommend,
    'feedback' => $feedback
]);

echo json_encode(['message' => 'Survey saved successfully']);