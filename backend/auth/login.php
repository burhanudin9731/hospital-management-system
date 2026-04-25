<?php
// ============================================================
// Hospital Management System - Login Handler
// backend/auth/login.php
// ============================================================

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// ── 1. Collect & sanitize input ──────────────────────────────
$email    = trim(filter_input(INPUT_POST, 'email',    FILTER_SANITIZE_EMAIL));
$password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT));

// ── 2. PHP server-side validation ───────────────────────────
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (empty($password)) {
    $errors[] = 'Password is required.';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── 3. Hash password with SHA-256 (same as registration) ────
$password_hash = hash('sha256', $password);

// ── 4. Query database ────────────────────────────────────────
$db   = getDB();
$stmt = $db->prepare(
    'SELECT user_id, username, email, role, password_hash
     FROM   USERS
     WHERE  email = ? AND password_hash = ?
     LIMIT  1'
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    $db->close();
    exit;
}

$stmt->bind_param('ss', $email, $password_hash);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// ── 5. Verify user found ─────────────────────────────────────
if (!$user) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    $db->close();
    exit;
}

// ── 6. Update last_login timestamp ──────────────────────────
$upd = $db->prepare('UPDATE USERS SET last_login = NOW() WHERE user_id = ?');
if ($upd) {
    $upd->bind_param('i', $user['user_id']);
    $upd->execute();
    $upd->close();
}
$db->close();

// ── 7. Create session ────────────────────────────────────────
session_regenerate_id(true);          // prevent session fixation

$_SESSION['user_id']  = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $user['email'];
$_SESSION['role']     = $user['role'];
$_SESSION['logged_in']= true;

// ── 8. Redirect target based on role ────────────────────────
$redirectMap = [
    'admin'  => '../../frontend/pages/dashboard/dashboard.html',
    'doctor' => '../../frontend/pages/dashboard/dashboard.html',
    'staff'  => '../../frontend/pages/dashboard/dashboard.html',
];
$redirect = $redirectMap[$user['role']] ?? '../../frontend/pages/dashboard/dashboard.html';

echo json_encode([
    'success'  => true,
    'message'  => 'Login successful! Redirecting...',
    'role'     => $user['role'],
    'username' => $user['username'],
    'redirect' => $redirect,
]);
