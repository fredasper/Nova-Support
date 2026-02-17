<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$pdo->exec("CREATE TABLE IF NOT EXISTS faq_candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    source ENUM('chat','admin') NOT NULL DEFAULT 'chat',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)");

$rows = $pdo->query("SELECT user_message AS question, COUNT(*) AS ask_count
FROM chat_logs
GROUP BY user_message
HAVING COUNT(*) > 1
ORDER BY ask_count DESC
LIMIT 5")->fetchAll();

if (!$rows) {
    echo json_encode(['message' => 'No repeated chatbot questions to export', 'considered' => 0, 'exported' => 0, 'skipped_duplicates' => 0]);
    exit;
}

$exists = $pdo->prepare("SELECT id FROM faq_candidates WHERE question = :question LIMIT 1");
$insert = $pdo->prepare("INSERT INTO faq_candidates (question, answer, source, status) VALUES (:question, :answer, 'chat', :status)");

$exported = 0;
$skipped_duplicates = 0;
$approvedAssigned = false;

foreach ($rows as $row) {
    $question = trim($row['question'] ?? '');
    if ($question === '') {
        continue;
    }

    $exists->execute(['question' => $question]);
    if ($exists->fetch()) {
        $skipped_duplicates++;
        continue;
    }

    $status = $approvedAssigned ? 'pending' : 'approved';
    $approvedAssigned = true;

    $insert->execute([
        'question' => $question,
        'answer' => 'Draft FAQ answer. Please review and finalize.',
        'status' => $status
    ]);
    $exported++;
}

echo json_encode([
    'message' => 'Top repeated chatbot questions processed for FAQ candidates',
    'considered' => count($rows),
    'exported' => $exported,
    'skipped_duplicates' => $skipped_duplicates,
    'top_question' => $rows[0]['question'] ?? null,
    'top_question_count' => $rows[0]['ask_count'] ?? 0
]);
