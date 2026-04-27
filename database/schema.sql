-- ============================================================
-- Hospital Management System - Database Schema
-- Course: Database Systems (CPE210)
-- ============================================================

DROP TABLE IF EXISTS BILL;
DROP TABLE IF EXISTS PRESCRIPTION;
DROP TABLE IF EXISTS MEDICAL_RECORD;
DROP TABLE IF EXISTS APPOINTMENT;
DROP TABLE IF EXISTS MEDICINE;
DROP TABLE IF EXISTS ROOM;
DROP TABLE IF EXISTS DOCTOR;
DROP TABLE IF EXISTS DEPARTMENT;
DROP TABLE IF EXISTS PATIENT;
DROP TABLE IF EXISTS USERS;

-- TABLE 1: USERS
CREATE TABLE USERS (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(64)  NOT NULL,
    role          ENUM('admin','doctor','staff') NOT NULL DEFAULT 'staff',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 2: DEPARTMENT
CREATE TABLE DEPARTMENT (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    dept_name     VARCHAR(100) NOT NULL UNIQUE,
    floor_number  TINYINT      NOT NULL,
    description   TEXT
);

-- TABLE 3: PATIENT
CREATE TABLE PATIENT (
    patient_id        INT AUTO_INCREMENT PRIMARY KEY,
    first_name        VARCHAR(50)  NOT NULL,
    last_name         VARCHAR(50)  NOT NULL,
    date_of_birth     DATE         NOT NULL,
    gender            ENUM('Male','Female','Other') NOT NULL,
    blood_type        ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-'),
    phone             VARCHAR(20)  NOT NULL,
    email             VARCHAR(100),
    address           TEXT,
    emergency_contact VARCHAR(100),
    emergency_phone   VARCHAR(20),
    registered_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- TABLE 4: DOCTOR
CREATE TABLE DOCTOR (
    doctor_id      INT AUTO_INCREMENT PRIMARY KEY,
    first_name     VARCHAR(50)  NOT NULL,
    last_name      VARCHAR(50)  NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone          VARCHAR(20)  NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    department_id  INT NOT NULL,
    user_id        INT,
    CONSTRAINT fk_doctor_dept FOREIGN KEY (department_id) REFERENCES DEPARTMENT(department_id),
    CONSTRAINT fk_doctor_user FOREIGN KEY (user_id)       REFERENCES USERS(user_id)
);

-- TABLE 5: ROOM
CREATE TABLE ROOM (
    room_id      INT AUTO_INCREMENT PRIMARY KEY,
    room_number  VARCHAR(10)  NOT NULL UNIQUE,
    room_type    ENUM('General','ICU','Private','Emergency','Operating') NOT NULL,
    floor_number TINYINT      NOT NULL,
    status       ENUM('Available','Occupied','Maintenance') NOT NULL DEFAULT 'Available',
    daily_rate   DECIMAL(8,2) NOT NULL
);

-- TABLE 6: MEDICINE
CREATE TABLE MEDICINE (
    medicine_id    INT AUTO_INCREMENT PRIMARY KEY,
    medicine_name  VARCHAR(100) NOT NULL,
    generic_name   VARCHAR(100),
    dosage_form    ENUM('Tablet','Capsule','Syrup','Injection','Cream','Drops') NOT NULL,
    manufacturer   VARCHAR(100),
    unit_price     DECIMAL(8,2) NOT NULL,
    stock_quantity INT NOT NULL DEFAULT 0
);

-- TABLE 7: APPOINTMENT
CREATE TABLE APPOINTMENT (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT  NOT NULL,
    doctor_id      INT  NOT NULL,
    appt_date      DATE NOT NULL,
    appt_time      TIME NOT NULL,
    reason         TEXT,
    status         ENUM('Scheduled','Completed','Cancelled','No-Show') NOT NULL DEFAULT 'Scheduled',
    notes          TEXT,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id) REFERENCES PATIENT(patient_id),
    CONSTRAINT fk_appt_doctor  FOREIGN KEY (doctor_id)  REFERENCES DOCTOR(doctor_id)
);

-- TABLE 8: MEDICAL_RECORD
CREATE TABLE MEDICAL_RECORD (
    record_id      INT AUTO_INCREMENT PRIMARY KEY,
    patient_id     INT  NOT NULL,
    doctor_id      INT  NOT NULL,
    appointment_id INT,
    diagnosis      TEXT NOT NULL,
    treatment      TEXT,
    notes          TEXT,
    record_date    DATE NOT NULL,
    CONSTRAINT fk_record_patient FOREIGN KEY (patient_id)     REFERENCES PATIENT(patient_id),
    CONSTRAINT fk_record_doctor  FOREIGN KEY (doctor_id)      REFERENCES DOCTOR(doctor_id),
    CONSTRAINT fk_record_appt    FOREIGN KEY (appointment_id) REFERENCES APPOINTMENT(appointment_id)
);

-- TABLE 9: PRESCRIPTION
CREATE TABLE PRESCRIPTION (
    prescription_id     INT AUTO_INCREMENT PRIMARY KEY,
    record_id           INT          NOT NULL,
    medicine_id         INT          NOT NULL,
    quantity            INT          NOT NULL,
    dosage_instructions VARCHAR(255) NOT NULL,
    prescribed_date     DATE         NOT NULL,
    CONSTRAINT fk_presc_record   FOREIGN KEY (record_id)   REFERENCES MEDICAL_RECORD(record_id),
    CONSTRAINT fk_presc_medicine FOREIGN KEY (medicine_id) REFERENCES MEDICINE(medicine_id)
);

-- TABLE 10: BILL
CREATE TABLE BILL (
    bill_id          INT AUTO_INCREMENT PRIMARY KEY,
    patient_id       INT           NOT NULL,
    appointment_id   INT,
    consultation_fee DECIMAL(8,2)  NOT NULL DEFAULT 0,
    medicine_cost    DECIMAL(8,2)  NOT NULL DEFAULT 0,
    room_charge      DECIMAL(8,2)  NOT NULL DEFAULT 0,
    other_charges    DECIMAL(8,2)  NOT NULL DEFAULT 0,
    total_amount     DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_status   ENUM('Unpaid','Partial','Paid') NOT NULL DEFAULT 'Unpaid',
    payment_method   ENUM('Cash','Card','Insurance','Online'),
    bill_date        DATE NOT NULL,
    CONSTRAINT fk_bill_patient FOREIGN KEY (patient_id)     REFERENCES PATIENT(patient_id),
    CONSTRAINT fk_bill_appt    FOREIGN KEY (appointment_id) REFERENCES APPOINTMENT(appointment_id)
);
