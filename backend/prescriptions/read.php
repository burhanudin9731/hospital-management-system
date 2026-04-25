<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db        = getDB();
$record_id = intval($_GET['record_id'] ?? 0);

if ($record_id) {
    $stmt = $db->prepare(
        "SELECT pr.*, m.medicine_name, m.dosage_form, m.unit_price,
                (pr.quantity * m.unit_price) AS total_cost
         FROM PRESCRIPTION pr
         JOIN MEDICINE m ON pr.medicine_id=m.medicine_id
         WHERE pr.record_id=? ORDER BY pr.prescription_id"
    );
    $stmt->bind_param('i',$record_id); $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows]);
    $stmt->close();
} else {
    // Return all with patient + doctor info
    $stmt = $db->prepare(
        "SELECT pr.*, m.medicine_name, m.dosage_form, m.unit_price,
                (pr.quantity * m.unit_price) AS total_cost,
                CONCAT(p.first_name,' ',p.last_name) AS patient_name,
                CONCAT(d.first_name,' ',d.last_name) AS doctor_name
         FROM PRESCRIPTION pr
         JOIN MEDICINE      m  ON pr.medicine_id  = m.medicine_id
         JOIN MEDICAL_RECORD mr ON pr.record_id   = mr.record_id
         JOIN PATIENT        p  ON mr.patient_id  = p.patient_id
         JOIN DOCTOR         d  ON mr.doctor_id   = d.doctor_id
         ORDER BY pr.prescribed_date DESC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success'=>true,'data'=>$rows]);
    $stmt->close();
}
$db->close();
