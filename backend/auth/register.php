<?php
// ============================================================
// Hospital Management System - User Registration
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/auth_check.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// ---- Collect & sanitize inputs ----
$username = sanitize($_POST['username'] ?? '');
$email    = sanitize($_POST['email']    ?? '');
$password = $_POST['password']          ?? '';
$confirm  = $_POST['confirm_password']  ?? '';
$role     = sanitize($_POST['role']     ?? 'staff');

// ---- Server-side validation ----
$errors = [];

if (strlen($username) < 3 || strlen($username) > 50) {
    $errors[] = 'Username must be between 3 and 50 characters.';
}
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = 'Username may only contain letters, numbers, and underscores.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = 'Password must contain at least one letter and one number.';
}
if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
}
if (!in_array($role, ['admin', 'doctor', 'staff'], true)) {
    $errors[] = 'Invalid role selected.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ---- Check uniqueness ----
$conn = getDB();

$stmt = $conn->prepare('SELECT user_id FROM USERS WHERE username = ? OR email = ?');
$stmt->bind_param('ss', $username, $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username or email already exists.']);
    exit;
}
$stmt->close();

// ---- Insert user ----
$hash = hashPassword($password);   // SHA-256

$stmt = $conn->prepare(
    'INSERT INTO USERS (username, email, password_hash, role) VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $username, $email, $hash, $role);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully. You can now log in.'
    ]);
} else {
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}
