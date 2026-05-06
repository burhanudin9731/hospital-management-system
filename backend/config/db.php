<?php
// ============================================================
// Hospital Management System - Database Configuration
// ============================================================

define('DB_HOST',    'localhost');
define('DB_USER',    'root');       // Change to your MySQL username
define('DB_PASS',    '');           // Change to your MySQL password
define('DB_NAME',    'hospital_management_system');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a MySQLi connection. Exits with JSON error on failure.
 */
function getDB(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit;
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}
