<?php
require_once __DIR__ . '/config/database.php';

$email = 'admin@appdib.com';
$password = 'admin123';
$fullName = 'Main Admin';

$pdo->prepare("DELETE FROM admins WHERE email = :email")->execute(['email' => $email]);

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO admins (full_name, email, password_hash) VALUES (:full_name, :email, :password_hash)");
$stmt->execute([
    'full_name' => $fullName,
    'email' => $email,
    'password_hash' => $hash,
]);

echo 'Admin seeded successfully: admin@appdib.com / admin123';