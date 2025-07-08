<?php
ini_set('display_errors', 0);       
ini_set('log_errors', 1);            
error_reporting(E_ALL); 

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'No autenticado']);
  exit;
}

$userId = $_SESSION['user_id'];

// Fetch username
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Last enrolled
$lastStmt = $pdo->query("SELECT name FROM enrollments ORDER BY idenrolled DESC LIMIT 1");
$last = $lastStmt->fetch(PDO::FETCH_ASSOC);

// Total count
$countStmt = $pdo->query("SELECT COUNT(*) AS total FROM enrollments");
$count = $countStmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  'username' => $user['username'] ?? 'N/A',
  'last_enrolled' => $last['name'] ?? 'N/A',
  'enrollment_count' => $count['total'] ?? 0,
]);
