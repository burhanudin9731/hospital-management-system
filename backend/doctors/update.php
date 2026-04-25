<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$id    = intval(filter_input(INPUT_POST,'doctor_id',FILTER_SANITIZE_NUMBER_INT));
$first_name = trim(filter_input(INPUT_POST,'first_name',FILTER_SANITIZE_SPECIAL_CHARS));
$last_name  = trim(filter_input(INPUT_POST,'last_name', FILTER_SANITIZE_SPECIAL_CHARS));
$spec       = trim(filter_input(INPUT_POST,'specialization',FILTER_SANITIZE_SPECIAL_CHARS));
$phone      = trim(filter_input(INPUT_POST,'phone',         FILTER_SANITIZE_SPECIAL_CHARS));
$email      = trim(filter_input(INPUT_POST,'email',         FILTER_SANITIZE_EMAIL));
$dept_id    = intval(filter_input(INPUT_POST,'department_id',FILTER_SANITIZE_NUMBER_INT));

if (!$id||!$first_name||!$last_name||!$spec||!$phone||!$email||!$dept_id) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'All fields required.']); exit;
}
$db   = getDB();
$stmt = $db->prepare('UPDATE DOCTOR SET first_name=?,last_name=?,specialization=?,phone=?,email=?,department_id=? WHERE doctor_id=?');
$stmt->bind_param('sssssii',$first_name,$last_name,$spec,$phone,$email,$dept_id,$id);
echo json_encode(['success'=>$stmt->execute(),'message'=>$stmt->execute() ? 'Doctor updated.' : 'Update failed.']);
$stmt->close(); $db->close();
