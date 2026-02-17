<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

$totals = $pdo->query("SELECT
    COUNT(*) AS total_responses,
    ROUND(AVG(satisfaction), 2) AS avg_satisfaction,
    ROUND(AVG(ease_of_use), 2) AS avg_ease_of_use,
    ROUND(AVG(helpfulness), 2) AS avg_helpfulness,
    ROUND(AVG(response_time), 2) AS avg_response_time,
    ROUND(AVG(recommend_score), 2) AS avg_recommend
FROM surveys")->fetch();

$distribution = $pdo->query("SELECT satisfaction, COUNT(*) AS count FROM surveys GROUP BY satisfaction ORDER BY satisfaction DESC")->fetchAll();

echo json_encode([
    'summary' => $totals,
    'satisfaction_distribution' => $distribution
]);