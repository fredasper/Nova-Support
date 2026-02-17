<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$rows = $pdo->query("SELECT s.created_at, u.full_name, u.email, s.satisfaction, s.ease_of_use, s.helpfulness, s.response_time, s.recommend_score, s.feedback
FROM surveys s
JOIN users u ON u.id = s.user_id
ORDER BY s.created_at DESC
LIMIT 15")->fetchAll();

echo json_encode(['recent_surveys' => $rows]);