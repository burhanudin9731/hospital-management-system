<?php
session_start(); header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole('admin','doctor');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$record_id   = intval(filter_input(INPUT_POST,'record_id',           FILTER_SANITIZE_NUMBER_INT));
$medicine_id = intval(filter_input(INPUT_POST,'medicine_id',         FILTER_SANITIZE_NUMBER_INT));
$quantity    = intval(filter_input(INPUT_POST,'quantity',            FILTER_SANITIZE_NUMBER_INT));
$dosage      = trim(filter_input(INPUT_POST,'dosage_instructions',   FILTER_SANITIZE_SPECIAL_CHARS));
$presc_date  = trim(filter_input(INPUT_POST,'prescribed_date',       FILTER_DEFAULT)) ?: date('Y-m-d');

if (!$record_id||!$medicine_id||!$quantity||!$dosage) {
    http_response_code(422); echo json_encode(['success'=>false,'message'=>'All fields are required.']); exit;
}
$db   = getDB();
$stmt = $db->prepare('INSERT INTO PRESCRIPTION (record_id,medicine_id,quantity,dosage_instructions,prescribed_date) VALUES (?,?,?,?,?)');
$stmt->bind_param('iiiss',$record_id,$medicine_id,$quantity,$dosage,$presc_date);
if ($stmt->execute()) {
    // Reduce stock
    $stk = $db->prepare('UPDATE MEDICINE SET stock_quantity=stock_quantity-? WHERE medicine_id=? AND stock_quantity>=?');
    $stk->bind_param('iii',$quantity,$medicine_id,$quantity); $stk->execute(); $stk->close();
    echo json_encode(['success'=>true,'message'=>'Prescription added.','prescription_id'=>$db->insert_id]);
} else {
    http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to add prescription.']);
}
$stmt->close(); $db->close();
