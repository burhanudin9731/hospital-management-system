<?php
// ============================================================
// Hospital Management System - Rooms Read
// backend/rooms/read.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db     = getDB();
$id     = intval($_GET['id']     ?? 0);
$status = trim($_GET['status']   ?? '');
$type   = trim($_GET['room_type']?? '');

if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM ROOM WHERE room_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success'=>true,'data'=>$stmt->get_result()->fetch_assoc()]);
    $stmt->close();
} else {
    $conds = []; $params = ''; $binds = [];
    if ($status) { $conds[] = 'status = ?';    $params .= 's'; $binds[] = $status; }
    if ($type)   { $conds[] = 'room_type = ?'; $params .= 's'; $binds[] = $type; }

    $sql = 'SELECT * FROM ROOM' . ($conds ? ' WHERE '.implode(' AND ',$conds) : '') . ' ORDER BY room_number';
    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param($params, ...$binds);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Summary counts
    $summary = ['total'=>0,'available'=>0,'occupied'=>0,'maintenance'=>0];
    foreach ($rows as $r) {
        $summary['total']++;
        $key = strtolower($r['status']);
        if (isset($summary[$key])) $summary[$key]++;
    }
    echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows),'summary'=>$summary]);
    $stmt->close();
}
$db->close();
