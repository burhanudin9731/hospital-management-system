<?php
// ============================================================
// Hospital Management System - Users Update
// backend/users/update.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$id       = intval(filter_input(INPUT_POST,'user_id', FILTER_SANITIZE_NUMBER_INT));
$username = trim(filter_input(INPUT_POST,'username',  FILTER_SANITIZE_SPECIAL_CHARS));
$email    = trim(filter_input(INPUT_POST,'email',     FILTER_SANITIZE_EMAIL));
$role     = trim(filter_input(INPUT_POST,'role',      FILTER_DEFAULT));
$password = trim(filter_input(INPUT_POST,'password',  FILTER_DEFAULT)); // optional

if (!$id || !$username || !$email || !$role) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'All fields required.']); exit;
}

$db = getDB();
if ($password) {
    // Update with new password
    $hash = hash('sha256', $password);
    $stmt = $db->prepare('UPDATE USERS SET username=?,email=?,role=?,password_hash=? WHERE user_id=?');
    $stmt->bind_param('ssssi',$username,$email,$role,$hash,$id);
} else {
    // Update without changing password
    $stmt = $db->prepare('UPDATE USERS SET username=?,email=?,role=? WHERE user_id=?');
    $stmt->bind_param('sssi',$username,$email,$role,$id);
}
echo json_encode(['success'=>$stmt->execute(),'message'=>$stmt->execute()?'User updated.':'Update failed.']);
$stmt->close(); $db->close();
