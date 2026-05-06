<?php
// ============================================================
// Hospital Management System - Reports Generator
// backend/reports/generate.php
// ============================================================
header('Content-Type: application/json');
require_once __DIR__ . '/../utils/auth_check.php';
require_once __DIR__ . '/../config/db.php';
requireLogin(); requireRole(['admin','staff']);

$type       = trim($_GET['type']       ?? 'summary'); // summary|appointments|billing|patients|doctors
$date_from  = trim($_GET['date_from']  ?? date('Y-m-01'));  // default: first of current month
$date_to    = trim($_GET['date_to']    ?? date('Y-m-d'));   // default: today

$db = getDB();

switch ($type) {

    // ── Overall summary dashboard numbers ────────────────────
    case 'summary':
        $data = [];

        $q = $db->query('SELECT COUNT(*) AS c FROM PATIENT');
        $data['total_patients'] = $q->fetch_assoc()['c'];

        $q = $db->query('SELECT COUNT(*) AS c FROM DOCTOR');
        $data['total_doctors'] = $q->fetch_assoc()['c'];

        $q = $db->query('SELECT COUNT(*) AS c FROM APPOINTMENT');
        $data['total_appointments'] = $q->fetch_assoc()['c'];

        $q = $db->query("SELECT COUNT(*) AS c FROM APPOINTMENT WHERE status='Scheduled'");
        $data['scheduled_appointments'] = $q->fetch_assoc()['c'];

        $q = $db->query("SELECT COUNT(*) AS c FROM APPOINTMENT WHERE status='Completed'");
        $data['completed_appointments'] = $q->fetch_assoc()['c'];

        $q = $db->query('SELECT COUNT(*) AS c FROM MEDICAL_RECORD');
        $data['total_records'] = $q->fetch_assoc()['c'];

        $q = $db->query('SELECT COALESCE(SUM(total_amount),0) AS total FROM BILL');
        $data['total_revenue'] = floatval($q->fetch_assoc()['total']);

        $q = $db->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM BILL WHERE payment_status='Paid'");
        $data['collected_revenue'] = floatval($q->fetch_assoc()['total']);

        $q = $db->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM BILL WHERE payment_status='Unpaid'");
        $data['outstanding_revenue'] = floatval($q->fetch_assoc()['total']);

        $q = $db->query("SELECT COUNT(*) AS c FROM ROOM WHERE status='Available'");
        $data['available_rooms'] = $q->fetch_assoc()['c'];

        $q = $db->query("SELECT COUNT(*) AS c FROM ROOM WHERE status='Occupied'");
        $data['occupied_rooms'] = $q->fetch_assoc()['c'];

        // Appointments per department
        $stmt = $db->prepare(
            'SELECT dp.dept_name, COUNT(a.appointment_id) AS total
             FROM DEPARTMENT dp
             LEFT JOIN DOCTOR d  ON dp.department_id = d.department_id
             LEFT JOIN APPOINTMENT a ON d.doctor_id  = a.doctor_id
             GROUP BY dp.department_id ORDER BY total DESC'
        );
        $stmt->execute();
        $data['by_department'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Monthly appointments (last 6 months)
        $stmt = $db->prepare(
            "SELECT DATE_FORMAT(appt_date,'%b %Y') AS month,
                    COUNT(*) AS total
             FROM APPOINTMENT
             WHERE appt_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY YEAR(appt_date), MONTH(appt_date)
             ORDER BY appt_date ASC"
        );
        $stmt->execute();
        $data['monthly_appointments'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success'=>true,'type'=>'summary','data'=>$data]);
        break;

    // ── Appointments in date range ────────────────────────────
    case 'appointments':
        $stmt = $db->prepare(
            'SELECT a.appointment_id, a.appt_date, a.appt_time, a.status, a.reason,
                    CONCAT(p.first_name," ",p.last_name) AS patient_name,
                    CONCAT(d.first_name," ",d.last_name) AS doctor_name,
                    d.specialization, dp.dept_name
             FROM APPOINTMENT a
             JOIN PATIENT     p  ON a.patient_id    = p.patient_id
             JOIN DOCTOR      d  ON a.doctor_id     = d.doctor_id
             JOIN DEPARTMENT  dp ON d.department_id = dp.department_id
             WHERE a.appt_date BETWEEN ? AND ?
             ORDER BY a.appt_date DESC, a.appt_time'
        );
        $stmt->bind_param('ss', $date_from, $date_to);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Counts by status
        $counts = ['Scheduled'=>0,'Completed'=>0,'Cancelled'=>0,'No-Show'=>0];
        foreach ($rows as $r) { if (isset($counts[$r['status']])) $counts[$r['status']]++; }

        echo json_encode(['success'=>true,'type'=>'appointments',
            'data'=>$rows,'count'=>count($rows),'by_status'=>$counts,
            'date_from'=>$date_from,'date_to'=>$date_to]);
        break;

    // ── Billing report ────────────────────────────────────────
    case 'billing':
        $stmt = $db->prepare(
            'SELECT b.bill_id, b.bill_date, b.total_amount, b.payment_status, b.payment_method,
                    b.consultation_fee, b.medicine_cost, b.room_charge, b.other_charges,
                    CONCAT(p.first_name," ",p.last_name) AS patient_name
             FROM BILL b
             JOIN PATIENT p ON b.patient_id = p.patient_id
             WHERE b.bill_date BETWEEN ? AND ?
             ORDER BY b.bill_date DESC'
        );
        $stmt->bind_param('ss', $date_from, $date_to);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $totals = ['total'=>0,'paid'=>0,'unpaid'=>0,'partial'=>0];
        foreach ($rows as $r) {
            $totals['total'] += floatval($r['total_amount']);
            if ($r['payment_status']==='Paid')    $totals['paid']    += floatval($r['total_amount']);
            if ($r['payment_status']==='Unpaid')  $totals['unpaid']  += floatval($r['total_amount']);
            if ($r['payment_status']==='Partial') $totals['partial'] += floatval($r['total_amount']);
        }

        echo json_encode(['success'=>true,'type'=>'billing',
            'data'=>$rows,'count'=>count($rows),'totals'=>$totals,
            'date_from'=>$date_from,'date_to'=>$date_to]);
        break;

    // ── Patient statistics ────────────────────────────────────
    case 'patients':
        $stmt = $db->prepare(
            'SELECT p.patient_id, CONCAT(p.first_name," ",p.last_name) AS name,
                    p.gender, p.blood_type,
                    TIMESTAMPDIFF(YEAR,p.date_of_birth,CURDATE()) AS age,
                    COUNT(DISTINCT a.appointment_id) AS total_appointments,
                    COUNT(DISTINCT mr.record_id)     AS total_records,
                    p.registered_at
             FROM PATIENT p
             LEFT JOIN APPOINTMENT   a  ON p.patient_id = a.patient_id
             LEFT JOIN MEDICAL_RECORD mr ON p.patient_id = mr.patient_id
             GROUP BY p.patient_id ORDER BY p.registered_at DESC'
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Gender breakdown
        $genders = [];
        foreach ($rows as $r) {
            $g = $r['gender'] ?? 'Other';
            $genders[$g] = ($genders[$g] ?? 0) + 1;
        }

        echo json_encode(['success'=>true,'type'=>'patients',
            'data'=>$rows,'count'=>count($rows),'by_gender'=>$genders]);
        break;

    // ── Doctor performance ────────────────────────────────────
    case 'doctors':
        $stmt = $db->prepare(
            'SELECT d.doctor_id,
                    CONCAT(d.first_name," ",d.last_name) AS doctor_name,
                    d.specialization, dp.dept_name,
                    COUNT(DISTINCT a.appointment_id)                                        AS total_appointments,
                    COUNT(DISTINCT CASE WHEN a.status="Completed" THEN a.appointment_id END) AS completed,
                    COUNT(DISTINCT CASE WHEN a.status="Cancelled" THEN a.appointment_id END) AS cancelled,
                    COUNT(DISTINCT mr.record_id)                                             AS records_created
             FROM DOCTOR d
             JOIN DEPARTMENT dp ON d.department_id = dp.department_id
             LEFT JOIN APPOINTMENT    a  ON d.doctor_id = a.doctor_id
             LEFT JOIN MEDICAL_RECORD mr ON d.doctor_id = mr.doctor_id
             GROUP BY d.doctor_id ORDER BY total_appointments DESC'
        );
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success'=>true,'type'=>'doctors','data'=>$rows,'count'=>count($rows)]);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'Unknown report type.']);
}

$db->close();
