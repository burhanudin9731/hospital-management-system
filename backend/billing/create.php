<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin','staff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$patient_id   = intval(filter_input(INPUT_POST,'patient_id',    FILTER_SANITIZE_NUMBER_INT));
$appt_id      = intval(filter_input(INPUT_POST,'appointment_id',FILTER_SANITIZE_NUMBER_INT)) ?: null;
$consult_fee  = floatval(filter_input(INPUT_POST,'consultation_fee',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
$med_cost     = floatval(filter_input(INPUT_POST,'medicine_cost',   FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
$room_charge  = floatval(filter_input(INPUT_POST,'room_charge',     FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
$other        = floatval(filter_input(INPUT_POST,'other_charges',   FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));
$payment_stat = trim(filter_input(INPUT_POST,'payment_status', FILTER_DEFAULT)) ?: 'Unpaid';
$payment_meth = trim(filter_input(INPUT_POST,'payment_method', FILTER_DEFAULT)) ?: null;
$bill_date    = trim(filter_input(INPUT_POST,'bill_date',      FILTER_DEFAULT)) ?: date('Y-m-d');
$total        = $consult_fee + $med_cost + $room_charge + $other;

if (!$patient_id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Patient is required.']); exit; }

$db   = getDB();
$stmt = $db->prepare('INSERT INTO BILL (patient_id,appointment_id,consultation_fee,medicine_cost,room_charge,other_charges,total_amount,payment_status,payment_method,bill_date) VALUES (?,?,?,?,?,?,?,?,?,?)');
$stmt->bind_param('iidddddsss',$patient_id,$appt_id,$consult_fee,$med_cost,$room_charge,$other,$total,$payment_stat,$payment_meth,$bill_date);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Bill created.','bill_id'=>$db->insert_id,'total'=>$total]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create bill.']);
}
$stmt->close(); $db->close();
