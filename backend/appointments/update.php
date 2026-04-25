<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$id        = intval(filter_input(INPUT_POST,'appointment_id',FILTER_SANITIZE_NUMBER_INT));
$status    = trim(filter_input(INPUT_POST,'status',          FILTER_DEFAULT));
$appt_date = trim(filter_input(INPUT_POST,'appt_date',       FILTER_DEFAULT)) ?: null;
$appt_time = trim(filter_input(INPUT_POST,'appt_time',       FILTER_DEFAULT)) ?: null;
$notes     = trim(filter_input(INPUT_POST,'notes',           FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;

if (!$id) { http_response_code(422); echo json_encode(['success'=>false,'message'=>'Appointment ID required.']); exit; }

$db   = getDB();
$stmt = $db->prepare('UPDATE APPOINTMENT SET status=?, appt_date=COALESCE(?,appt_date), appt_time=COALESCE(?,appt_time), notes=? WHERE appointment_id=?');
$stmt->bind_param('ssssi',$status,$appt_date,$appt_time,$notes,$id);
echo json_encode(['success'=>$stmt->execute(),'message'=>'Appointment updated.']);
$stmt->close(); $db->close();
