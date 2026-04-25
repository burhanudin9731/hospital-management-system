<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db         = getDB();
$id         = intval($_GET['id'] ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$doctor_id  = intval($_GET['doctor_id']  ?? 0);
$date       = trim($_GET['date']   ?? '');
$status     = trim($_GET['status'] ?? '');
$today      = $_GET['today'] ?? '';

$sql  = "SELECT a.appointment_id, a.appt_date, a.appt_time, a.reason, a.status, a.notes, a.created_at,
                CONCAT(p.first_name,' ',p.last_name) AS patient_name, p.phone AS patient_phone,
                CONCAT(d.first_name,' ',d.last_name) AS doctor_name, d.specialization,
                dp.dept_name
         FROM APPOINTMENT a
         JOIN PATIENT     p  ON a.patient_id    = p.patient_id
         JOIN DOCTOR      d  ON a.doctor_id     = d.doctor_id
         JOIN DEPARTMENT  dp ON d.department_id = dp.department_id";
$conds = []; $params = ''; $binds = [];

if ($id)         { $conds[] = 'a.appointment_id=?'; $params .= 'i'; $binds[] = $id; }
if ($patient_id) { $conds[] = 'a.patient_id=?';     $params .= 'i'; $binds[] = $patient_id; }
if ($doctor_id)  { $conds[] = 'a.doctor_id=?';      $params .= 'i'; $binds[] = $doctor_id; }
if ($date)       { $conds[] = 'a.appt_date=?';      $params .= 's'; $binds[] = $date; }
if ($status)     { $conds[] = 'a.status=?';         $params .= 's'; $binds[] = $status; }
if ($today)      { $conds[] = 'a.appt_date=CURDATE()'; }

if ($conds) $sql .= ' WHERE ' . implode(' AND ', $conds);
$sql .= ' ORDER BY a.appt_date DESC, a.appt_time ASC';

$stmt = $db->prepare($sql);
if ($params) {
    $stmt->bind_param($params, ...$binds);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true,'data'=>$id ? ($rows[0] ?? null) : $rows,'count'=>count($rows)]);
$stmt->close(); $db->close();
