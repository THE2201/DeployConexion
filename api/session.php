<?php
session_start();

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'logged_in' => true,
        'user' => $_SESSION['username']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
