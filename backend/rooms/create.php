<?php
// ============================================================
// Hospital Management System - Rooms Create/Update
// backend/rooms/create.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin','staff']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$action      = trim($_POST['action'] ?? 'create'); // 'create' | 'update_status'
$room_id     = intval($_POST['room_id']     ?? 0);
$room_number = trim(filter_input(INPUT_POST,'room_number',FILTER_SANITIZE_SPECIAL_CHARS));
$room_type   = trim(filter_input(INPUT_POST,'room_type',  FILTER_DEFAULT));
$floor       = intval(filter_input(INPUT_POST,'floor_number',FILTER_SANITIZE_NUMBER_INT));
$status      = trim(filter_input(INPUT_POST,'status',     FILTER_DEFAULT)) ?: 'Available';
$daily_rate  = floatval(filter_input(INPUT_POST,'daily_rate',FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION));

$db = getDB();

if ($action === 'update_status' && $room_id) {
    // Just update the room status
    $stmt = $db->prepare('UPDATE ROOM SET status = ? WHERE room_id = ?');
    $stmt->bind_param('si', $status, $room_id);
    echo json_encode(['success'=>$stmt->execute(),'message'=>'Room status updated.']);
    $stmt->close(); $db->close(); exit;
}

// Create new room
if (!$room_number || !$room_type || !$floor || !$daily_rate) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
}
// Check duplicate room number
$chk = $db->prepare('SELECT room_id FROM ROOM WHERE room_number = ?');
$chk->bind_param('s',$room_number); $chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) {
    http_response_code(409); echo json_encode(['success'=>false,'message'=>'Room number already exists.']);
    $chk->close(); $db->close(); exit;
}
$chk->close();

$stmt = $db->prepare('INSERT INTO ROOM (room_number,room_type,floor_number,status,daily_rate) VALUES (?,?,?,?,?)');
$stmt->bind_param('ssiss',$room_number,$room_type,$floor,$status,$daily_rate);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Room added.','room_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to add room.']);
}
$stmt->close(); $db->close();
