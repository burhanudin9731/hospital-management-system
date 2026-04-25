<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db         = getDB();
$id         = intval($_GET['id']         ?? 0);
$patient_id = intval($_GET['patient_id'] ?? 0);
$status     = trim($_GET['status'] ?? '');

$sql = "SELECT b.*, CONCAT(p.first_name,' ',p.last_name) AS patient_name, p.phone
        FROM BILL b JOIN PATIENT p ON b.patient_id=p.patient_id";
$conds = []; $params = ''; $binds = [];
if ($id)         { $conds[] = 'b.bill_id=?';      $params .= 'i'; $binds[] = $id; }
if ($patient_id) { $conds[] = 'b.patient_id=?';   $params .= 'i'; $binds[] = $patient_id; }
if ($status)     { $conds[] = 'b.payment_status=?';$params .= 's'; $binds[] = $status; }
if ($conds) $sql .= ' WHERE ' . implode(' AND ', $conds);
$sql .= ' ORDER BY b.bill_date DESC';

$stmt = $db->prepare($sql);
if ($params) $stmt->bind_param($params, ...$binds);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo json_encode(['success'=>true,'data'=>$id ? ($rows[0] ?? null) : $rows,'count'=>count($rows)]);
$stmt->close(); $db->close();
