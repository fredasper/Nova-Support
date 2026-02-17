<?php
require_once __DIR__ . '/config/database.php';

$students = [
    [
        'full_name' => 'Anna Lopez',
        'email' => 'anna.lopez@student.appdib.com',
        'password' => 'anna1234',
        'student_id' => '2026-0002',
        'program' => 'BS Information Technology',
        'year_level' => '2nd Year',
        'subjects' => 'Data Structures, Web Development 2, Database Systems 2'
    ],
    [
        'full_name' => 'Mark Reyes',
        'email' => 'mark.reyes@student.appdib.com',
        'password' => 'mark1234',
        'student_id' => '2026-0003',
        'program' => 'BS Computer Science',
        'year_level' => '1st Year',
        'subjects' => 'Discrete Mathematics, Computer Programming 1, Introduction to Computing'
    ]
];

$availableCols = $pdo->query('SHOW COLUMNS FROM users')->fetchAll(PDO::FETCH_COLUMN);
$hasStudentId = in_array('student_id', $availableCols, true);
$hasProgram = in_array('program', $availableCols, true);
$hasYearLevel = in_array('year_level', $availableCols, true);
$hasSubjects = in_array('subjects', $availableCols, true);

foreach ($students as $student) {
    $pdo->prepare('DELETE FROM users WHERE email = :email')->execute(['email' => $student['email']]);

    $insertCols = ['full_name', 'email', 'password_hash', 'role'];
    $params = [
        'full_name' => $student['full_name'],
        'email' => $student['email'],
        'password_hash' => password_hash($student['password'], PASSWORD_DEFAULT),
        'role' => 'student'
    ];

    if ($hasStudentId) {
        $insertCols[] = 'student_id';
        $params['student_id'] = $student['student_id'];
    }
    if ($hasProgram) {
        $insertCols[] = 'program';
        $params['program'] = $student['program'];
    }
    if ($hasYearLevel) {
        $insertCols[] = 'year_level';
        $params['year_level'] = $student['year_level'];
    }
    if ($hasSubjects) {
        $insertCols[] = 'subjects';
        $params['subjects'] = $student['subjects'];
    }

    $placeholders = array_map(fn($c) => ':' . $c, $insertCols);
    $sql = 'INSERT INTO users (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
}

echo '2 student accounts created successfully.';