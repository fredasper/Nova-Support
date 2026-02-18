<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/database.php';

function execSql(PDO $pdo, string $sql): void {
    $pdo->exec($sql);
}

function ensureUsersTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(120) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role ENUM('student','admin') NOT NULL DEFAULT 'student',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        student_id VARCHAR(50) NULL,
        program VARCHAR(100) NULL,
        year_level VARCHAR(30) NULL,
        subjects TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureAdminsTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(120) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureChatLogsTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS chat_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        user_message TEXT NOT NULL,
        bot_reply TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_chat_user_id (user_id),
        CONSTRAINT fk_chat_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureSurveysTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS surveys (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        satisfaction TINYINT NULL,
        ease_of_use TINYINT NULL,
        helpfulness TINYINT NULL,
        response_time TINYINT NULL,
        recommend_score TINYINT NULL,
        feedback TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_surveys_user_id (user_id),
        CONSTRAINT fk_surveys_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureSupportMessagesTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS support_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        office_key VARCHAR(50) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_support_user_id (user_id),
        CONSTRAINT fk_support_messages_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensureFaqCandidatesTable(PDO $pdo): void {
    execSql($pdo, "CREATE TABLE IF NOT EXISTS faq_candidates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        source ENUM('chat','admin') NOT NULL DEFAULT 'chat',
        status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_faq_status_created (status, created_at),
        CONSTRAINT fk_faq_candidates_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function seedStudent(PDO $pdo, array $student): bool {
    $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $check->execute(['email' => $student['email']]);
    if ($check->fetch()) {
        return false;
    }

    $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, student_id, program, year_level, subjects)
        VALUES (:full_name, :email, :password_hash, :role, :student_id, :program, :year_level, :subjects)');
    $insert->execute([
        'full_name' => $student['full_name'],
        'email' => $student['email'],
        'password_hash' => password_hash($student['password'], PASSWORD_DEFAULT),
        'role' => 'student',
        'student_id' => $student['student_id'],
        'program' => $student['program'],
        'year_level' => $student['year_level'],
        'subjects' => $student['subjects']
    ]);

    return true;
}

function seedAdmin(PDO $pdo, array $admin): bool {
    $check = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
    $check->execute(['email' => $admin['email']]);
    if ($check->fetch()) {
        return false;
    }

    $insert = $pdo->prepare('INSERT INTO admins (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)');
    $insert->execute([
        'full_name' => $admin['full_name'],
        'email' => $admin['email'],
        'password_hash' => password_hash($admin['password'], PASSWORD_DEFAULT)
    ]);

    return true;
}

try {
    ensureUsersTable($pdo);
    ensureAdminsTable($pdo);
    ensureChatLogsTable($pdo);
    ensureSurveysTable($pdo);
    ensureSupportMessagesTable($pdo);
    ensureFaqCandidatesTable($pdo);

    $createdStudent = seedStudent($pdo, [
        'full_name' => 'Jarold Student',
        'email' => 'jarold@example.com',
        'password' => 'secret123',
        'student_id' => '2026-0001',
        'program' => 'BS Information Technology',
        'year_level' => '1st Year',
        'subjects' => 'Programming 1, Web Development, Database Systems'
    ]);

    $createdAdmin = seedAdmin($pdo, [
        'full_name' => 'Main Admin',
        'email' => 'admin@example.com',
        'password' => 'admin123'
    ]);

    echo json_encode([
        'message' => 'Schema setup complete',
        'seeded' => [
            'student_created' => $createdStudent,
            'admin_created' => $createdAdmin
        ],
        'accounts' => [
            'student' => ['email' => 'jarold@example.com', 'password' => 'secret123'],
            'admin' => ['email' => 'admin@example.com', 'password' => 'admin123']
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Setup failed',
        'details' => $e->getMessage()
    ]);
}