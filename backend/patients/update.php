<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$id         = intval(filter_input(INPUT_POST,'patient_id', FILTER_SANITIZE_NUMBER_INT));
$first_name = trim(filter_input(INPUT_POST,'first_name',   FILTER_SANITIZE_SPECIAL_CHARS));
$last_name  = trim(filter_input(INPUT_POST,'last_name',    FILTER_SANITIZE_SPECIAL_CHARS));
$dob        = trim(filter_input(INPUT_POST,'dob',          FILTER_DEFAULT));
$gender     = trim(filter_input(INPUT_POST,'gender',       FILTER_DEFAULT));
$blood_type = trim(filter_input(INPUT_POST,'blood_type',   FILTER_DEFAULT)) ?: null;
$phone      = trim(filter_input(INPUT_POST,'phone',        FILTER_SANITIZE_SPECIAL_CHARS));
$email      = trim(filter_input(INPUT_POST,'email',        FILTER_SANITIZE_EMAIL)) ?: null;
$address    = trim(filter_input(INPUT_POST,'address',      FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;
$ec_name    = trim(filter_input(INPUT_POST,'emergency_contact',FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;
$ec_phone   = trim(filter_input(INPUT_POST,'emergency_phone',  FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;

if (!$id||!$first_name||!$last_name||!$dob||!$gender||!$phone) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Required fields missing.']); exit;
}

$db   = getDB();
$stmt = $db->prepare('UPDATE PATIENT SET first_name=?,last_name=?,date_of_birth=?,gender=?,blood_type=?,phone=?,email=?,address=?,emergency_contact=?,emergency_phone=? WHERE patient_id=?');
$stmt->bind_param('ssssssssssi',$first_name,$last_name,$dob,$gender,$blood_type,$phone,$email,$address,$ec_name,$ec_phone,$id);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Patient updated successfully.']);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Update failed.']);
}
$stmt->close(); $db->close();
