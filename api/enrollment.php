<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php'; 

// Helper to send JSON responses and exit
function respond($statusCode, $status, $message, $data = null) {
    http_response_code($statusCode);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'error', 'Método no permitido');
}

// Get raw JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    respond(400, 'error', 'Datos JSON inválidos');
}

// Extract and validate required fields
$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$dob = trim($input['dob'] ?? '');
$size = trim($input['size'] ?? '');
$allergies = trim($input['allergies'] ?? '');
$parent = trim($input['parent'] ?? '');
$nparent = trim($input['nparent'] ?? '');
$parenting = trim($input['parenting'] ?? '');

if (!$name || !$phone || !$dob || !$size || !$parent || !$nparent || !$parenting) {
    respond(400, 'error', 'Faltan campos requeridos');
}

// Sanitize and validate phone (simple example)
if (!preg_match('/^\d{7,10}$/', $phone)) {
    respond(400, 'error', 'Teléfono inválido');
}

// Check if user already enrolled: example function in db.php
if (isAlreadyEnrolled($phone, $dob)) {
    respond(409, 'error', 'Usuario ya inscrito');
}

$success = addEnrollment([
    'name' => $name,
    'phone' => $phone,
    'dob' => $dob,
    'size' => $size,
    'allergies' => $allergies,
    'parent' => $parent,
    'nparent' => $nparent,
    'parenting' => $parenting
]);

if ($success) {
    respond(200, 'success', 'Inscrito para MoodCamp 2025 ' . $name . ' nosotros te contactaremos, puedes tomar captura a este mensaje de inscripcion!');
} else {
    respond(500, 'error', 'Error al guardar inscripción');
}
