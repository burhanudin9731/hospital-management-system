<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin','staff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$id = intval(filter_input(INPUT_POST,'patient_id',FILTER_SANITIZE_NUMBER_INT));
if (!$id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid patient ID.']); exit; }

$db   = getDB();
$stmt = $db->prepare('DELETE FROM PATIENT WHERE patient_id=?');
$stmt->bind_param('i',$id);
if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success'=>true,'message'=>'Patient deleted.']);
} else {
    http_response_code(404); echo json_encode(['success'=>false,'message'=>'Patient not found.']);
}
$stmt->close(); $db->close();
