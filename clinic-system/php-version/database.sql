-- Run in phpMyAdmin (Import tab) or: mysql -u root < database.sql
CREATE DATABASE IF NOT EXISTS clinic_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic_db;
DROP TABLE IF EXISTS withdrawals, donations, pay_accounts, visit_notes, appointments, users, patients, doctors;
CREATE TABLE doctors(
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, specialty VARCHAR(100) NOT NULL, kind ENUM('Doctor','Midwife') NOT NULL DEFAULT 'Doctor',
  working_days VARCHAR(20) NOT NULL COMMENT 'weekday numbers, 0=Sun, e.g. 1,2,3,4,5');
CREATE TABLE patients(
  id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, age TINYINT UNSIGNED NOT NULL DEFAULT 0,
  phone VARCHAR(30) NOT NULL DEFAULT '', gender VARCHAR(10) NOT NULL DEFAULT '', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE users(
  id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL,
  role ENUM('staff','doctor','patient') NOT NULL, name VARCHAR(100) NOT NULL, doctor_id INT NULL, patient_id INT NULL, withdraw_hash VARCHAR(255) NULL,
  FOREIGN KEY(doctor_id) REFERENCES doctors(id), FOREIGN KEY(patient_id) REFERENCES patients(id));
CREATE TABLE appointments(
  id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT NOT NULL, doctor_id INT NULL,
  appt_date DATE NULL, appt_time TIME NULL, reason VARCHAR(255) NOT NULL DEFAULT '',
  status ENUM('Pending','Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  active TINYINT GENERATED ALWAYS AS (IF(status='Cancelled',NULL,1)) STORED,
  UNIQUE KEY no_double_booking(doctor_id,appt_date,appt_time,active),
  FOREIGN KEY(patient_id) REFERENCES patients(id), FOREIGN KEY(doctor_id) REFERENCES doctors(id));
CREATE TABLE visit_notes(
  id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT NOT NULL, doctor_id INT NOT NULL,
  note_date DATE NOT NULL, note TEXT NOT NULL,
  FOREIGN KEY(patient_id) REFERENCES patients(id), FOREIGN KEY(doctor_id) REFERENCES doctors(id));
INSERT INTO doctors(name,specialty,kind,working_days) VALUES
 ('Dr. Maria Santos','Family Medicine','Doctor','1,2,3,4,5'),('Dr. Jose Reyes','Pediatrics','Doctor','1,3,5'),('Dr. Ana Cruz','Internal Medicine','Doctor','2,4,6'),('Midwife Rosa Villanueva','Maternal and Newborn Care','Midwife','1,2,3,4,5');
INSERT INTO patients(name,age,phone) VALUES
 ('Liza Mendoza',34,'0917 555 0101'),('Carlo Dela Peña',7,'0918 555 0188'),('Rodel Bautista',58,'0920 555 0144');
INSERT INTO visit_notes(patient_id,doctor_id,note_date,note) VALUES
 (1,1,CURDATE()-INTERVAL 20 DAY,'Seasonal flu. Advised rest and fluids.'),(3,3,CURDATE()-INTERVAL 45 DAY,'Blood pressure 150/95. Follow-up in 4 weeks.');
INSERT INTO appointments(patient_id,doctor_id,appt_date,appt_time,reason) VALUES
 (1,1,CURDATE(),'09:00','Follow-up check'),(2,2,CURDATE(),'10:30','Vaccination'),(3,3,CURDATE()+INTERVAL 2 DAY,'14:00','Blood pressure review');
INSERT INTO appointments(patient_id,reason,status) VALUES (2,'Cough and fever for 3 days','Pending');
CREATE TABLE pay_accounts(
  method ENUM('GCash','Maya','Online banking') PRIMARY KEY, bank_name VARCHAR(100) NOT NULL DEFAULT '',
  account_name VARCHAR(100) NOT NULL DEFAULT '', account_number VARCHAR(50) NOT NULL DEFAULT '');
CREATE TABLE donations(
  id INT AUTO_INCREMENT PRIMARY KEY, patient_id INT NOT NULL, method ENUM('GCash','Maya','Online banking') NOT NULL,
  amount DECIMAL(10,2) NOT NULL, reference VARCHAR(60) NOT NULL, note VARCHAR(140) NOT NULL DEFAULT '',
  status ENUM('Pending','Received','Rejected') NOT NULL DEFAULT 'Pending', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(patient_id) REFERENCES patients(id));
CREATE TABLE withdrawals(
  id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL,
  kind ENUM('Online wallet','Bank transfer') NOT NULL,
  provider ENUM('GCash','Maya','BDO','PBI','MetroBank','LandBank') NOT NULL,
  account_name VARCHAR(100) NOT NULL, account_number VARCHAR(30) NOT NULL,
  amount DECIMAL(10,2) NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(user_id) REFERENCES users(id));
-- Sample receiving accounts. Staff replace these with the clinic's real accounts in the Donations page.
INSERT INTO pay_accounts VALUES ('GCash','','Clinic One','0900 000 0000'),('Maya','','Clinic One','0900 000 0001'),('Online banking','Sample Bank','Clinic One','0000 0000 0000');
-- Users are created by install.php so passwords are hashed with PHP.
