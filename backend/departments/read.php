<?php
// ============================================================
// Hospital Management System - Departments Read
// backend/departments/read.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db = getDB();
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare(
        'SELECT d.*, COUNT(doc.doctor_id) AS total_doctors
         FROM DEPARTMENT d
         LEFT JOIN DOCTOR doc ON d.department_id = doc.department_id
         WHERE d.department_id = ?
         GROUP BY d.department_id'
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $row]);
    $stmt->close();
} else {
    $stmt = $db->prepare(
        'SELECT d.*, COUNT(doc.doctor_id) AS total_doctors
         FROM DEPARTMENT d
         LEFT JOIN DOCTOR doc ON d.department_id = doc.department_id
         GROUP BY d.department_id
         ORDER BY d.dept_name ASC'
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $rows, 'count' => count($rows)]);
    $stmt->close();
}
$db->close();
