<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

try {
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

    $stmt = $pdo->query("SELECT question, answer, source, created_at
    FROM faq_candidates
    WHERE status = 'approved'
    ORDER BY created_at DESC
    LIMIT 20");

    $faqs = $stmt->fetchAll();

    echo json_encode([
        'faqs' => $faqs
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to load FAQs'
    ]);
}
