<?php
// ============================================================
// Hospital Management System - Departments Create
// backend/departments/create.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin']);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

$dept_name   = trim(filter_input(INPUT_POST, 'dept_name',    FILTER_SANITIZE_SPECIAL_CHARS));
$floor       = intval(filter_input(INPUT_POST, 'floor_number', FILTER_SANITIZE_NUMBER_INT));
$description = trim(filter_input(INPUT_POST, 'description',   FILTER_SANITIZE_SPECIAL_CHARS)) ?: null;

if (!$dept_name || !$floor) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Department name and floor number are required.']); exit;
}

$db = getDB();
// Check duplicate
$chk = $db->prepare('SELECT department_id FROM DEPARTMENT WHERE dept_name = ?');
$chk->bind_param('s', $dept_name); $chk->execute(); $chk->store_result();
if ($chk->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success'=>false,'message'=>'Department already exists.']);
    $chk->close(); $db->close(); exit;
}
$chk->close();

$stmt = $db->prepare('INSERT INTO DEPARTMENT (dept_name, floor_number, description) VALUES (?,?,?)');
$stmt->bind_param('sis', $dept_name, $floor, $description);
if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Department created.','department_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create department.']);
}
$stmt->close(); $db->close();
