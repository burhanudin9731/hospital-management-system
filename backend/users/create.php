<?php
// ============================================================
// Hospital Management System - Users Create
// backend/users/create.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$username = trim(filter_input(INPUT_POST,'username',FILTER_SANITIZE_SPECIAL_CHARS));
$email    = trim(filter_input(INPUT_POST,'email',   FILTER_SANITIZE_EMAIL));
$password = trim(filter_input(INPUT_POST,'password',FILTER_DEFAULT));
$role     = trim(filter_input(INPUT_POST,'role',    FILTER_DEFAULT)) ?: 'staff';

if (!$username || !$email || !$password) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Username, email and password are required.']); exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid email address.']); exit;
}
if (strlen($password) < 8) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Password must be at least 8 characters.']); exit;
}
if (!in_array($role, ['admin','doctor','staff'])) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid role.']); exit;
}

$db = getDB();
$chk = $db->prepare('SELECT user_id FROM USERS WHERE email = ? OR username = ?');
$chk->bind_param('ss',$email,$username); $chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) {
    http_response_code(409); echo json_encode(['success'=>false,'message'=>'Email or username already exists.']);
    $chk->close(); $db->close(); exit;
}
$chk->close();

$hash = hash('sha256', $password);
$stmt = $db->prepare('INSERT INTO USERS (username,email,password_hash,role) VALUES (?,?,?,?)');
$stmt->bind_param('ssss',$username,$email,$hash,$role);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'User created successfully.','user_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create user.']);
}
$stmt->close(); $db->close();
