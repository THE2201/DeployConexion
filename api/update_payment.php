<?php
session_start();
require '../config/db.php'; 

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$amount = $data['amount'];

if (!$id || !$amount || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$stmt = $pdo->prepare("UPDATE enrollments SET balance = balance - ? WHERE idenrolled = ?");
$success = $stmt->execute([$amount, $id]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el pago']);
}
?>
