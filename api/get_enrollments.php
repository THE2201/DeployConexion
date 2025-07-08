<?php
session_start();
header('Content-Type: application/json');

require_once '../config/db.php'; // adjust as needed

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

try {
$stmt = $pdo->query("SELECT idenrolled, name, phone, dob, allergies, parent, nparent, parenting FROM enrollments ORDER BY created_at DESC");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al cargar inscripciones']);
}
