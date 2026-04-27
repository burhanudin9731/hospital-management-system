-- ============================================================
-- Hospital Management System - Required SQL Queries (5 Types)
-- Course: Database Systems (CPE210)
-- ============================================================

-- ============================================================
-- QUERY 1: SUBQUERY
-- Find all patients who have at least one appointment
-- in the Cardiology department
-- ============================================================
SELECT
    p.patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    p.phone,
    p.blood_type
FROM PATIENT p
WHERE p.patient_id IN (
    SELECT a.patient_id
    FROM APPOINTMENT a
    JOIN DOCTOR d      ON a.doctor_id      = d.doctor_id
    JOIN DEPARTMENT dp ON d.department_id  = dp.department_id
    WHERE dp.dept_name = 'Cardiology'
)
ORDER BY p.last_name;


-- ============================================================
-- QUERY 2: JOIN
-- Retrieve full appointment details including patient name,
-- doctor name, department, and appointment status
-- ============================================================
SELECT
    a.appointment_id,
    CONCAT(p.first_name, ' ', p.last_name)  AS patient_name,
    p.phone                                  AS patient_phone,
    CONCAT(d.first_name, ' ', d.last_name)  AS doctor_name,
    d.specialization,
    dp.dept_name                             AS department,
    a.appt_date,
    a.appt_time,
    a.reason,
    a.status
FROM APPOINTMENT a
JOIN PATIENT    p  ON a.patient_id    = p.patient_id
JOIN DOCTOR     d  ON a.doctor_id     = d.doctor_id
JOIN DEPARTMENT dp ON d.department_id = dp.department_id
ORDER BY a.appt_date DESC, a.appt_time DESC;


-- ============================================================
-- QUERY 3: GROUP BY
-- Count total appointments per doctor and show workload
-- statistics including completed and cancelled counts
-- ============================================================
SELECT
    CONCAT(d.first_name, ' ', d.last_name) AS doctor_name,
    d.specialization,
    dp.dept_name                            AS department,
    COUNT(a.appointment_id)                 AS total_appointments,
    SUM(CASE WHEN a.status = 'Completed'  THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN a.status = 'Scheduled'  THEN 1 ELSE 0 END) AS scheduled,
    SUM(CASE WHEN a.status = 'Cancelled'  THEN 1 ELSE 0 END) AS cancelled
FROM DOCTOR d
JOIN DEPARTMENT dp          ON d.department_id  = dp.department_id
LEFT JOIN APPOINTMENT a     ON d.doctor_id       = a.doctor_id
GROUP BY d.doctor_id, d.first_name, d.last_name, d.specialization, dp.dept_name
ORDER BY total_appointments DESC;


-- ============================================================
-- QUERY 4: DATE FUNCTION
-- List all appointments scheduled in the current month,
-- showing days remaining until each appointment
-- ============================================================
SELECT
    a.appointment_id,
    CONCAT(p.first_name, ' ', p.last_name)  AS patient_name,
    CONCAT(d.first_name, ' ', d.last_name)  AS doctor_name,
    a.appt_date,
    DAYNAME(a.appt_date)                     AS day_of_week,
    a.appt_time,
    DATEDIFF(a.appt_date, CURDATE())         AS days_until_appointment,
    a.status
FROM APPOINTMENT a
JOIN PATIENT p ON a.patient_id = p.patient_id
JOIN DOCTOR  d ON a.doctor_id  = d.doctor_id
WHERE MONTH(a.appt_date) = MONTH(CURDATE())
  AND YEAR(a.appt_date)  = YEAR(CURDATE())
ORDER BY a.appt_date, a.appt_time;


-- ============================================================
-- QUERY 5: CHARACTER FUNCTION
-- Search patients by name (case-insensitive) and display
-- formatted contact information
-- ============================================================
SELECT
    p.patient_id,
    UPPER(CONCAT(p.first_name, ' ', p.last_name))          AS patient_name_upper,
    CONCAT(UPPER(LEFT(p.first_name,1)), LOWER(SUBSTRING(p.first_name,2)),
           ' ',
           UPPER(LEFT(p.last_name,1)),  LOWER(SUBSTRING(p.last_name,2)))
                                                            AS patient_name_proper,
    LPAD(p.phone, 15, ' ')                                  AS phone_formatted,
    COALESCE(p.email, 'No email provided')                  AS email,
    p.blood_type,
    CONCAT(TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()), ' years old') AS age
FROM PATIENT p
WHERE LOWER(CONCAT(p.first_name, ' ', p.last_name)) LIKE LOWER(CONCAT('%', 'a', '%'))
ORDER BY p.last_name;