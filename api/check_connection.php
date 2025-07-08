<?php
header('Content-Type: application/json');
require_once '../config/db.php'; 

try {
    $pdo->query('SELECT 1');
    echo json_encode(['status' => 'success', 'message' => 'Database connected']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
}
