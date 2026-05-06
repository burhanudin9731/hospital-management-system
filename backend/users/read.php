<?php
// ============================================================
// Hospital Management System - Users Read
// backend/users/read.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin']);

$db     = getDB();
$id     = intval($_GET['id']   ?? 0);
$search = trim($_GET['search'] ?? '');
$role   = trim($_GET['role']   ?? '');

if ($id > 0) {
    $stmt = $db->prepare('SELECT user_id,username,email,role,created_at,last_login FROM USERS WHERE user_id = ?');
    $stmt->bind_param('i',$id); $stmt->execute();
    echo json_encode(['success'=>true,'data'=>$stmt->get_result()->fetch_assoc()]);
    $stmt->close();
} else {
    $conds = []; $params = ''; $binds = [];
    $like  = "%$search%";
    if ($search) { $conds[] = '(username LIKE ? OR email LIKE ?)'; $params .= 'ss'; $binds[] = $like; $binds[] = $like; }
    if ($role)   { $conds[] = 'role = ?'; $params .= 's'; $binds[] = $role; }

    $sql  = 'SELECT user_id,username,email,role,created_at,last_login FROM USERS';
    if ($conds) $sql .= ' WHERE '.implode(' AND ',$conds);
    $sql .= ' ORDER BY created_at DESC';

    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param($params, ...$binds);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows)]);
    $stmt->close();
}
$db->close();
