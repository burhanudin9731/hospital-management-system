<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db         = getDB();
$id         = intval($_GET['id']         ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$doctor_id  = intval($_GET['doctor_id']  ?? 0);

$sql = "SELECT mr.*, CONCAT(p.first_name,' ',p.last_name) AS patient_name,
               CONCAT(d.first_name,' ',d.last_name) AS doctor_name, d.specialization
        FROM MEDICAL_RECORD mr
        JOIN PATIENT p ON mr.patient_id = p.patient_id
        JOIN DOCTOR  d ON mr.doctor_id  = d.doctor_id";
$conds = []; $params = ''; $binds = [];
if ($id)         { $conds[] = 'mr.record_id=?';  $params .= 'i'; $binds[] = $id; }
if ($patient_id) { $conds[] = 'mr.patient_id=?'; $params .= 'i'; $binds[] = $patient_id; }
if ($doctor_id)  { $conds[] = 'mr.doctor_id=?';  $params .= 'i'; $binds[] = $doctor_id; }
if ($conds) $sql .= ' WHERE ' . implode(' AND ', $conds);
$sql .= ' ORDER BY mr.record_date DESC';

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($params, ...$binds);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true,'data'=>$id ? ($rows[0] ?? null) : $rows]);
$stmt->close(); $db->close();
