-- HMS Functional Mock Data Script (Fixed Configuration)
-- Standardized for: HMS_STRICT_QR_INTEGRATED_V4

SET FOREIGN_KEY_CHECKS = 0;

-- 1. CLEAN SLATE: Safe sequential deletion to bypass MySQL DDL Lock #1701
DELETE FROM User_Login;
DELETE FROM Department;
DELETE FROM Admin;
DELETE FROM Doctor;
DELETE FROM Nurse;
DELETE FROM Receptionist;
DELETE FROM Room;
DELETE FROM Patient;
DELETE FROM AI_Analysis;
DELETE FROM Appointment;
DELETE FROM Medical_History;
DELETE FROM Prescription;
DELETE FROM Billing;
DELETE FROM Patient_phones;
DELETE FROM Doctor_phones;
DELETE FROM Doctors_Patient;
DELETE FROM Receptionist_Doctors;

-- Reset Auto-Increment keys to pristine initialization values
ALTER TABLE User_Login AUTO_INCREMENT = 1;
ALTER TABLE Department AUTO_INCREMENT = 1;
ALTER TABLE Admin AUTO_INCREMENT = 1;
ALTER TABLE Doctor AUTO_INCREMENT = 1;
ALTER TABLE Nurse AUTO_INCREMENT = 1;
ALTER TABLE Receptionist AUTO_INCREMENT = 1;
ALTER TABLE Patient AUTO_INCREMENT = 1;
ALTER TABLE AI_Analysis AUTO_INCREMENT = 1;
ALTER TABLE Appointment AUTO_INCREMENT = 1;
ALTER TABLE Medical_History AUTO_INCREMENT = 1;
ALTER TABLE Prescription AUTO_INCREMENT = 1;
ALTER TABLE Billing AUTO_INCREMENT = 1;

-- 2. CENTRAL AUTHENTICATION DEPLOYMENT (Universal demo password: Password123)
INSERT INTO User_Login (User_ID, Email, Password, Role) VALUES
(1, 'ahmed.ramadan.24030005@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Admin'),
(2, 'youssef.ahmed.24030126@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Doctor'),
(3, 'eyad.mohamed.24030150@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Doctor'),
(4, 'moatasem.mostafa.24030055@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Doctor'),
(5, 'marwan.mohammed.24030006@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Receptionist'),
(6, 'youssef.atef.24030217@iu.edu.eg', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Receptionist'),
(7, 'nurse1@hms.com', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Nurse'),
(8, 'patient1@hms.com', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Patient'),
(9, 'patient2@hms.com', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Patient'),
(10, 'patient3@hms.com', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Patient');

-- 3. MEDICAL DEPARTMENTS
INSERT INTO Department (Department_ID, Name, Location) VALUES
(1, 'Dental (أسنان)', 'Building A, Floor 1'),
(2, 'Ortho (عظام)', 'Building A, Floor 2'),
(3, 'Internal (بطنة)', 'Building B, Floor 1'),
(4, 'Nursing', 'Central Wing'),
(5, 'Reception', 'Main Lobby');

-- 4. ADMINISTRATIVE MASTER PROFILE
INSERT INTO Admin (Admin_ID, Full_Name, Security_Question, Security_Answer, User_ID) VALUES
(1, 'Ahmed Ramadan', 'What is your project code?', 'V4_STRICT', 1);

-- 5. PERSONNEL ENTIY SUBTYPES (Doctors, Receptionists, Nurses)
INSERT INTO Doctor (Doctor_ID, Fname, Lname, Specialization, Email, License_No, Phone, Salary, Department_ID, User_ID, Admin_ID) VALUES
(1, 'Youssef', 'Noby', 'Orthopedic Surgeon', 'youssef.ahmed.24030126@iu.edu.eg', 'LIC-101', '0101112233', 35000.00, 2, 2, 1),
(2, 'Eyad', 'Mohamed', 'Dentist', 'eyad.mohamed.24030150@iu.edu.eg', 'LIC-102', '0122223344', 32000.00, 1, 3, 1),
(3, 'Moatasem', 'Mostafa', 'Internal Medicine', 'moatasem.mostafa.24030055@iu.edu.eg', 'LIC-103', '0155556677', 30000.00, 3, 4, 1);

INSERT INTO Nurse (Nurse_ID, Fname, Lname, Phone, Department_ID, User_ID, Admin_ID) VALUES
(1, 'Sarah', 'Ali', '0101234567', 4, 7, 1);

INSERT INTO Receptionist (Receptionist_ID, Full_Name, Phone, Shift_Time, User_ID, Admin_ID) VALUES
(1, 'Marwan Bahaa', '0109998877', 'Day (8 AM - 4 PM)', 5, 1),
(2, 'Youssef Wael', '0114445566', 'Night (4 PM - 12 AM)', 6, 1);

-- 6. PHYSICAL ROOM ARCHITECTURE (Inpatient Ward Foundations)
INSERT INTO Room (Room_ID, Phone, Status, Room_Type, Doctor_ID, Nurse_ID, Receptionist_ID) VALUES
(101, 'EXT-101', 'Available', 'Single - VIP', 1, 1, 1),
(102, 'EXT-102', 'Occupied', 'Double - Standard', 3, 1, 1),
(103, 'EXT-103', 'Available', 'Single - VIP', 2, 1, 2),
(104, 'EXT-104', 'Maintenance', 'Emergency', NULL, NULL, 1),
(105, 'EXT-105', 'Occupied', 'Ward', 3, 1, 2);

-- 7. CORE REGISTRATION RECORDS: PATIENTS
INSERT INTO Patient (Patient_ID, Registration_Date, Fname, Lname, Gender, Date_of_Birth, Age, City, Street, Building, Emergency_Contact, Blood_Group, Room_ID, Receptionist_ID, User_ID) VALUES
(1, '2026-05-01', 'Omar', 'Kamal', 'Male', '1990-05-15', 36, 'Cairo', 'El-Tahrir', 'Bldg 12', '0100000001', 'A+', 102, 1, 8),
(2, '2026-05-05', 'Hania', 'Sami', 'Female', '1985-11-22', 40, 'Giza', 'Pyramids St', 'Bldg 5A', '0100000002', 'O-', 105, 1, 9),
(3, '2026-05-10', 'Zaid', 'Karim', 'Male', '2005-02-10', 21, 'Alexandria', 'Corniche', 'Bldg 9', '0100000003', 'B+', NULL, 2, 10);

-- 8. CLINICAL INNOVATION LAYER: INTELLIGENT AI TRIAGE LOGS
INSERT INTO AI_Analysis (Analysis_ID, Input_Symptoms, Confidence_Level, Risk_Score, Predicted_Specialization, Patient_ID) VALUES
(1, 'Severe knee pain after falling down stairs.', 92.50, 8, 'Ortho (عظام)', 1),
(2, 'Sudden sharp toothache and gum swelling.', 88.00, 4, 'Dental (أسنان)', 3),
(3, 'Persistent high fever, cough, and fatigue.', 95.00, 9, 'Internal (بطنة)', 2);

-- 9. OPERATIONS: SCHEDULING ENCOUNTERS
INSERT INTO Appointment (Appointment_ID, Date, Time, Status, Diagnosis, Patient_ID, Room_ID, Doctor_ID, Receptionist_ID) VALUES
(1, '2026-05-12', '10:00:00', 'Scheduled', NULL, 1, 102, 1, 1),
(2, '2026-05-10', '11:30:00', 'Checked-In', 'Acute stomach pain', 2, 105, 3, 2),
(3, '2026-05-09', '09:00:00', 'Completed', 'Routine checkup', 3, 101, 2, 1);

-- 10. CLINICAL ENCOUNTERS: HISTORICAL RECORDS
INSERT INTO Medical_History (Record_ID, Date, Diagnosis, Notes, Treatment, Patient_ID, Doctor_ID, Appointment_ID) VALUES
(1, '2026-05-09', 'Mild Gastritis', 'Patient complained of burning sensation.', 'Antacids and bland diet', 3, 2, 3);

-- 11. MEDICATIONS LOG: CLINICAL PRESCRIPTIONS
INSERT INTO Prescription (Prescription_ID, Medications, Dosage, Instructions, Appointment_ID, Doctor_ID) VALUES
(1, 'Amoxicillin, Panadol Extra', '500mg x3 daily', 'Take after meals for 7 days', 3, 2);

-- 12. FINANCIAL GENERAL LEDGER: BILLING DECK
INSERT INTO Billing (Bill_ID, Amount, Payment_Date, Payment_Method, Payment_Status, Patient_ID, Receptionist_ID) VALUES
(1, 1500.00, '2026-05-10', 'Credit Card', 'Paid', 3, 1),
(2, 850.50, '2026-05-09', 'Cash', 'Paid', 1, 2),
(3, 1200.00, NULL, NULL, 'Unpaid', 2, 1);

-- 13. DATA ENHANCEMENT: MULTI-VALUED CONTACT CHANNELS
INSERT INTO Patient_phones (Patient_ID, PhoneNumber) VALUES (1, '0111111111'), (1, '0122222222');
INSERT INTO Doctor_phones (Doctor_ID, PhoneNumber) VALUES (1, '0101112233'), (1, '0104445566');

-- 14. DATA MAPPING LOGS: ENFORCED RELATIONSHIP JUNCTION ENTITIES
INSERT INTO Doctors_Patient (Doctor_ID, Patient_ID) VALUES (1, 1), (3, 2), (2, 3);
INSERT INTO Receptionist_Doctors (Doctor_ID, Receptionist_ID) VALUES (1, 1), (2, 1), (3, 2);

SET FOREIGN_KEY_CHECKS = 1;