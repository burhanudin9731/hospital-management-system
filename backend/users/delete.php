<?php
// ============================================================
// Hospital Management System - Users Delete
// backend/users/delete.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$id = intval(filter_input(INPUT_POST,'user_id',FILTER_SANITIZE_NUMBER_INT));
if (!$id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid user ID.']); exit; }

// Prevent deleting yourself
if ($id === ($_SESSION['user_id'] ?? 0)) {
    http_response_code(403); echo json_encode(['success'=>false,'message'=>'You cannot delete your own account.']); exit;
}

$db   = getDB();
$stmt = $db->prepare('DELETE FROM USERS WHERE user_id = ?');
$stmt->bind_param('i',$id);
echo json_encode([
    'success' => $stmt->execute() && $stmt->affected_rows > 0,
    'message' => 'User deleted.'
]);
$stmt->close(); $db->close();
