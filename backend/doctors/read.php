<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db     = getDB();
$search = trim($_GET['search'] ?? '');
$id     = intval($_GET['id'] ?? 0);
$dept   = intval($_GET['department_id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare(
        'SELECT d.*,dp.dept_name FROM DOCTOR d JOIN DEPARTMENT dp ON d.department_id=dp.department_id WHERE d.doctor_id=?'
    );
    $stmt->bind_param('i',$id); $stmt->execute();
    echo json_encode(['success'=>true,'data'=>$stmt->get_result()->fetch_assoc()]);
    $stmt->close();
} else {
    $like = "%$search%";
    $sql  = "SELECT d.doctor_id,d.first_name,d.last_name,d.specialization,d.phone,d.email,
                    dp.dept_name, COUNT(a.appointment_id) AS total_appointments
             FROM DOCTOR d
             JOIN DEPARTMENT dp ON d.department_id=dp.department_id
             LEFT JOIN APPOINTMENT a ON d.doctor_id=a.doctor_id
             WHERE (CONCAT(d.first_name,' ',d.last_name) LIKE ? OR d.specialization LIKE ?)";
    if ($dept) { $sql .= " AND d.department_id=$dept"; }
    $sql .= " GROUP BY d.doctor_id ORDER BY d.last_name";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('ss',$like,$like); $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows)]);
    $stmt->close();
}
$db->close();
