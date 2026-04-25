<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$first_name = trim(filter_input(INPUT_POST,'first_name',    FILTER_SANITIZE_SPECIAL_CHARS));
$last_name  = trim(filter_input(INPUT_POST,'last_name',     FILTER_SANITIZE_SPECIAL_CHARS));
$spec       = trim(filter_input(INPUT_POST,'specialization',FILTER_SANITIZE_SPECIAL_CHARS));
$phone      = trim(filter_input(INPUT_POST,'phone',         FILTER_SANITIZE_SPECIAL_CHARS));
$email      = trim(filter_input(INPUT_POST,'email',         FILTER_SANITIZE_EMAIL));
$dept_id    = intval(filter_input(INPUT_POST,'department_id',FILTER_SANITIZE_NUMBER_INT));

if (!$first_name||!$last_name||!$spec||!$phone||!$email||!$dept_id) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
}

$db = getDB();
// check duplicate email
$chk = $db->prepare('SELECT doctor_id FROM DOCTOR WHERE email=?');
$chk->bind_param('s',$email); $chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) { http_response_code(409); echo json_encode(['success'=>false,'message'=>'Email already registered.']); $chk->close(); $db->close(); exit; }
$chk->close();

$stmt = $db->prepare('INSERT INTO DOCTOR (first_name,last_name,specialization,phone,email,department_id) VALUES (?,?,?,?,?,?)');
$stmt->bind_param('sssssi',$first_name,$last_name,$spec,$phone,$email,$dept_id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Doctor added successfully.','doctor_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to add doctor.']);
}
$stmt->close(); $db->close();
