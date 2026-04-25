<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin','doctor');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$patient_id = intval(filter_input(INPUT_POST,'patient_id',    FILTER_SANITIZE_NUMBER_INT));
$doctor_id  = intval(filter_input(INPUT_POST,'doctor_id',     FILTER_SANITIZE_NUMBER_INT));
$appt_id    = intval(filter_input(INPUT_POST,'appointment_id',FILTER_SANITIZE_NUMBER_INT)) ?: null;
$diagnosis  = trim(filter_input(INPUT_POST,'diagnosis', FILTER_SANITIZE_SPECIAL_CHARS));
$treatment  = trim(filter_input(INPUT_POST,'treatment', FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;
$notes      = trim(filter_input(INPUT_POST,'notes',     FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;
$rec_date   = trim(filter_input(INPUT_POST,'record_date',FILTER_DEFAULT)) ?: date('Y-m-d');

if (!$patient_id||!$doctor_id||!$diagnosis) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Patient, doctor and diagnosis are required.']); exit;
}
$db   = getDB();
$stmt = $db->prepare('INSERT INTO MEDICAL_RECORD (patient_id,doctor_id,appointment_id,diagnosis,treatment,notes,record_date) VALUES (?,?,?,?,?,?,?)');
$stmt->bind_param('iiissss',$patient_id,$doctor_id,$appt_id,$diagnosis,$treatment,$notes,$rec_date);
if ($stmt->execute()) {
    // Mark appointment as completed if linked
    if ($appt_id) {
        $upd = $db->prepare("UPDATE APPOINTMENT SET status='Completed' WHERE appointment_id=?");
        $upd->bind_param('i',$appt_id); $upd->execute(); $upd->close();
    }
    echo json_encode(['success'=>true,'message'=>'Medical record created.','record_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create record.']);
}
$stmt->close(); $db->close();
