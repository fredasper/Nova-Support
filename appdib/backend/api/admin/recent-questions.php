<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$recent = $pdo->query("SELECT user_message, created_at FROM chat_logs ORDER BY created_at DESC LIMIT 15")->fetchAll();
$mostAsked = $pdo->query("SELECT user_message AS question, COUNT(*) AS ask_count FROM chat_logs GROUP BY user_message ORDER BY ask_count DESC LIMIT 1")->fetch();

echo json_encode([
    'recent_questions' => $recent,
    'most_asked' => $mostAsked ?: null
]);