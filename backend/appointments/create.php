<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$patient_id = intval(filter_input(INPUT_POST,'patient_id', FILTER_SANITIZE_NUMBER_INT));
$doctor_id  = intval(filter_input(INPUT_POST,'doctor_id',  FILTER_SANITIZE_NUMBER_INT));
$appt_date  = trim(filter_input(INPUT_POST,'appt_date',    FILTER_DEFAULT));
$appt_time  = trim(filter_input(INPUT_POST,'appt_time',    FILTER_DEFAULT));
$reason     = trim(filter_input(INPUT_POST,'reason',       FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;
$notes      = trim(filter_input(INPUT_POST,'notes',        FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;

if (!$patient_id||!$doctor_id||!$appt_date||!$appt_time) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'Patient, doctor, date and time are required.']); exit;
}

$db = getDB();
// Check doctor availability (no duplicate slot)
$chk = $db->prepare("SELECT appointment_id FROM APPOINTMENT WHERE doctor_id=? AND appt_date=? AND appt_time=? AND status != 'Cancelled'");
$chk->bind_param('iss',$doctor_id,$appt_date,$appt_time); $chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) {
    http_response_code(409); echo json_encode(['success'=>false,'message'=>'Doctor already has an appointment at this time.']); $chk->close(); $db->close(); exit;
}
$chk->close();

$stmt = $db->prepare('INSERT INTO APPOINTMENT (patient_id,doctor_id,appt_date,appt_time,reason,notes) VALUES (?,?,?,?,?,?)');
$stmt->bind_param('iissss',$patient_id,$doctor_id,$appt_date,$appt_time,$reason,$notes);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Appointment booked successfully.','appointment_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Booking failed.']);
}
$stmt->close(); $db->close();
