<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin','staff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }
$id = intval(filter_input(INPUT_POST,'appointment_id',FILTER_SANITIZE_NUMBER_INT));
if (!$id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Invalid ID.']); exit; }
$db   = getDB();
$stmt = $db->prepare('DELETE FROM APPOINTMENT WHERE appointment_id=?');
$stmt->bind_param('i',$id);
echo json_encode(['success'=>$stmt->execute(),'message'=>'Appointment deleted.']);
$stmt->close(); $db->close();
