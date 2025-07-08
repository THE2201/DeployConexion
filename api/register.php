<?php
session_start();
header('Content-Type: application/json');

require_once '../config/db.php'; 

$data = json_decode(file_get_contents('php://input'), true);

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuario y contraseña son obligatorios.']);
    exit;
}

try {
    // Check if user already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE username = :username");
    $check->execute(['username' => $username]);
    
    if ($check->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Este usuario ya existe.']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the user
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->execute([
        'username' => $username,
        'password' => $hashedPassword,
    ]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar usuario.']);
}
