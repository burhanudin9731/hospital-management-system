<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db     = getDB();
$search = trim($_GET['search'] ?? '');
$id     = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $db->prepare('SELECT * FROM PATIENT WHERE patient_id=?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['success'=>true,'data'=>$row]);
    $stmt->close();
} else {
    $like = "%$search%";
    $stmt = $db->prepare(
        "SELECT patient_id,first_name,last_name,date_of_birth,gender,blood_type,phone,email,
                TIMESTAMPDIFF(YEAR,date_of_birth,CURDATE()) AS age, registered_at
         FROM PATIENT
         WHERE CONCAT(first_name,' ',last_name) LIKE ? OR phone LIKE ? OR email LIKE ?
         ORDER BY registered_at DESC"
    );
    $stmt->bind_param('sss',$like,$like,$like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows,'count'=>count($rows)]);
    $stmt->close();
}
$db->close();
