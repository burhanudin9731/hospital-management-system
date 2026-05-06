-- ============================================================
-- Hospital Management System - Seed Data
-- Course: Database Systems (CPE210)
-- ============================================================

-- USERS (password = SHA2('password123', 256) for all)
INSERT INTO USERS (username, email, password_hash, role) VALUES
('admin',       'admin@hospital.com',    SHA2('admin123',    256), 'admin'),
('dr_wilson',   'wilson@hospital.com',   SHA2('password123', 256), 'doctor'),
('dr_patel',    'patel@hospital.com',    SHA2('password123', 256), 'doctor'),
('dr_lee',      'lee@hospital.com',      SHA2('password123', 256), 'doctor'),
('dr_garcia',   'garcia@hospital.com',   SHA2('password123', 256), 'doctor'),
('dr_chen',     'chen@hospital.com',     SHA2('password123', 256), 'doctor'),
('staff_jane',  'jane@hospital.com',     SHA2('password123', 256), 'staff');

-- DEPARTMENTS
INSERT INTO DEPARTMENT (dept_name, floor_number, description) VALUES
('Cardiology',    2, 'Heart and cardiovascular system care'),
('Neurology',     3, 'Brain and nervous system disorders'),
('Pediatrics',    4, 'Medical care for infants, children and adolescents'),
('Orthopedics',   2, 'Bone, joint and muscle conditions'),
('Emergency',     1, '24/7 emergency and trauma care'),
('Radiology',     1, 'Medical imaging and diagnostics');

-- PATIENTS
INSERT INTO PATIENT (first_name, last_name, date_of_birth, gender, blood_type, phone, email, address, emergency_contact, emergency_phone) VALUES
('Ahmed',    'Al-Hassan',  '1985-03-12', 'Male',   'A+',  '05301234567', 'ahmed@email.com',   '123 Karabuk St',  'Fatma Al-Hassan',  '05307654321'),
('Fatma',    'Yilmaz',     '1992-07-25', 'Female', 'B+',  '05312345678', 'fatma@email.com',   '45 Ataturk Ave',  'Ali Yilmaz',       '05318765432'),
('Mehmet',   'Kaya',       '1978-11-03', 'Male',   'O-',  '05323456789', 'mehmet@email.com',  '78 Republic Rd',  'Ayse Kaya',        '05329876543'),
('Ayse',     'Demir',      '2001-05-18', 'Female', 'AB+', '05334567890', 'ayse@email.com',    '12 Freedom Blvd', 'Hasan Demir',      '05330987654'),
('Ali',      'Celik',      '1965-09-30', 'Male',   'A-',  '05345678901', 'ali@email.com',     '56 Market Lane',  'Zeynep Celik',     '05341098765'),
('Zeynep',   'Arslan',     '1998-02-14', 'Female', 'B-',  '05356789012', 'zeynep@email.com',  '90 Hill Rd',      'Mustafa Arslan',   '05352109876'),
('Hasan',    'Ozturk',     '1973-08-07', 'Male',   'O+',  '05367890123', 'hasan@email.com',   '34 River St',     'Emine Ozturk',     '05363210987'),
('Emine',    'Sahin',      '1988-12-22', 'Female', 'A+',  '05378901234', 'emine@email.com',   '67 Park Ave',     'Ibrahim Sahin',    '05374321098'),
('Ibrahim',  'Yildiz',     '1955-04-01', 'Male',   'B+',  '05389012345', 'ibrahim@email.com', '23 Sunset Dr',    'Hatice Yildiz',    '05385432109'),
('Hatice',   'Gungor',     '2010-06-15', 'Female', 'AB-', '05390123456', 'hatice@email.com',  '89 Spring St',    'Recep Gungor',     '05396543210');

-- DOCTORS
INSERT INTO DOCTOR (first_name, last_name, specialization, phone, email, department_id, user_id) VALUES
('James',   'Wilson',  'Cardiologist',       '05301112233', 'wilson@hospital.com',  1, 2),
('Priya',   'Patel',   'Neurologist',         '05312223344', 'patel@hospital.com',   2, 3),
('David',   'Lee',     'Pediatrician',        '05323334455', 'lee@hospital.com',     3, 4),
('Maria',   'Garcia',  'Orthopedic Surgeon',  '05334445566', 'garcia@hospital.com',  4, 5),
('Wei',     'Chen',    'Emergency Physician', '05345556677', 'chen@hospital.com',    5, 6);

-- ROOMS
INSERT INTO ROOM (room_number, room_type, floor_number, status, daily_rate) VALUES
('101', 'General',   1, 'Available',   250.00),
('102', 'General',   1, 'Occupied',    250.00),
('103', 'Private',   1, 'Available',   600.00),
('201', 'ICU',       2, 'Occupied',   1200.00),
('202', 'ICU',       2, 'Available',  1200.00),
('301', 'Private',   3, 'Maintenance', 600.00),
('302', 'General',   3, 'Available',   250.00),
('ER1', 'Emergency', 1, 'Available',   800.00),
('OR1', 'Operating', 2, 'Available',  2000.00);

-- MEDICINES
INSERT INTO MEDICINE (medicine_name, generic_name, dosage_form, manufacturer, unit_price, stock_quantity) VALUES
('Aspirin 100mg',       'Acetylsalicylic Acid', 'Tablet',    'Bayer',      2.50,  500),
('Amoxicillin 500mg',   'Amoxicillin',          'Capsule',   'GSK',        8.00,  300),
('Paracetamol 500mg',   'Acetaminophen',        'Tablet',    'Novartis',   1.50,  800),
('Ibuprofen 400mg',     'Ibuprofen',            'Tablet',    'Reckitt',    4.00,  400),
('Metformin 500mg',     'Metformin HCl',        'Tablet',    'Merck',      5.00,  600),
('Atorvastatin 20mg',   'Atorvastatin',         'Tablet',    'Pfizer',    12.00,  250),
('Omeprazole 20mg',     'Omeprazole',           'Capsule',   'AstraZeneca', 6.50, 350),
('Ciprofloxacin 500mg', 'Ciprofloxacin',        'Tablet',    'Bayer',     15.00,  200),
('Salbutamol Syrup',    'Albuterol',            'Syrup',     'GSK',        9.00,  150),
('Insulin Glargine',    'Insulin Glargine',     'Injection', 'Sanofi',    45.00,  100);

-- APPOINTMENTS
INSERT INTO APPOINTMENT (patient_id, doctor_id, appt_date, appt_time, reason, status) VALUES
(1,  1, '2026-05-05', '09:00:00', 'Chest pain and shortness of breath',  'Scheduled'),
(2,  2, '2026-05-05', '10:30:00', 'Frequent headaches and dizziness',    'Scheduled'),
(3,  4, '2026-05-05', '11:00:00', 'Knee pain after sports injury',       'Scheduled'),
(4,  3, '2026-05-06', '09:30:00', 'Child routine checkup',               'Scheduled'),
(5,  1, '2026-05-06', '14:00:00', 'Hypertension follow-up',              'Scheduled'),
(6,  2, '2026-04-20', '10:00:00', 'Migraine evaluation',                 'Completed'),
(7,  4, '2026-04-22', '11:30:00', 'Back pain consultation',              'Completed'),
(8,  1, '2026-04-23', '09:00:00', 'Heart palpitations',                  'Completed'),
(9,  3, '2026-04-15', '15:00:00', 'Fever and cough in child',            'Completed'),
(10, 5, '2026-04-18', '08:00:00', 'Emergency - high fever',              'Completed');

-- MEDICAL RECORDS
INSERT INTO MEDICAL_RECORD (patient_id, doctor_id, appointment_id, diagnosis, treatment, notes, record_date) VALUES
(6,  2, 6,  'Chronic Migraine',              'Sumatriptan therapy + lifestyle modification', 'Avoid triggers, stress management recommended', '2026-04-20'),
(7,  4, 7,  'Lumbar Disc Herniation',        'Physical therapy and NSAIDs',                  'MRI scan ordered, follow up in 2 weeks',         '2026-04-22'),
(8,  1, 8,  'Atrial Fibrillation',           'Beta-blockers and anticoagulation therapy',     'ECG performed, refer to cardiologist',           '2026-04-23'),
(9,  3, 9,  'Acute Bronchitis',              'Antibiotics and bronchodilators',               'Rest and fluids, review in 5 days',              '2026-04-15'),
(10, 5, 10, 'Viral Fever with Dehydration',  'IV fluids and antipyretics',                    'Admitted for observation',                       '2026-04-18');

-- PRESCRIPTIONS
INSERT INTO PRESCRIPTION (record_id, medicine_id, quantity, dosage_instructions, prescribed_date) VALUES
(1, 8,  10, 'Take 1 tablet twice daily after meals',         '2026-04-20'),
(2, 4,  20, 'Take 1 tablet three times daily with food',     '2026-04-22'),
(2, 3,  10, 'Take 1 tablet as needed for pain (max 3/day)',  '2026-04-22'),
(3, 6,  30, 'Take 1 tablet once daily at bedtime',           '2026-04-23'),
(3, 1,  30, 'Take 1 tablet daily after breakfast',           '2026-04-23'),
(4, 2,  14, 'Take 1 capsule twice daily for 7 days',         '2026-04-15'),
(4, 9,   1, '5ml three times daily for 5 days',              '2026-04-15'),
(5, 3,  10, 'Take 2 tablets every 6 hours as needed',        '2026-04-18');

-- BILLS
INSERT INTO BILL (patient_id, appointment_id, consultation_fee, medicine_cost, room_charge, other_charges, total_amount, payment_status, payment_method, bill_date) VALUES
(6,  6,  150.00,  60.00,    0.00,  20.00,  230.00, 'Paid',    'Card',      '2026-04-20'),
(7,  7,  200.00,  84.00,    0.00,  30.00,  314.00, 'Paid',    'Cash',      '2026-04-22'),
(8,  8,  250.00, 150.00,  600.00,  50.00, 1050.00, 'Partial', 'Insurance', '2026-04-23'),
(9,  9,  120.00,  47.00,    0.00,  15.00,  182.00, 'Paid',    'Cash',      '2026-04-15'),
(10, 10, 180.00,  15.00, 1200.00,  80.00, 1475.00, 'Unpaid',   NULL,       '2026-04-18');
