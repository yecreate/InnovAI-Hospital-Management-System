-- HMS Database Foundation
-- Standardized for: XAMPP / MySQL / PHP 8.x
-- Version: HMS_STRICT_QR_INTEGRATED_V4

CREATE DATABASE IF NOT EXISTS hospital_management_system;
USE hospital_management_system;

-- 1. Central Authentication Table
CREATE TABLE User_Login (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL, -- To be used with password_hash()
    Role ENUM('Admin', 'Receptionist', 'Doctor', 'Nurse', 'Patient') NOT NULL
) ENGINE=InnoDB;

-- 2. Infrastructure: Departments
CREATE TABLE Department (
    Department_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Location VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- 3. Core Personnel: Admin
CREATE TABLE Admin (
    Admin_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(150) NOT NULL,
    Security_Question VARCHAR(255),
    Security_Answer VARCHAR(255),
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Core Personnel: Doctors
CREATE TABLE Doctor (
    Doctor_ID INT AUTO_INCREMENT PRIMARY KEY,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Specialization VARCHAR(100) NOT NULL,
    Email VARCHAR(255),
    License_No VARCHAR(100) UNIQUE,
    Phone VARCHAR(20),
    Salary DECIMAL(10, 2),
    Department_ID INT,
    User_ID INT,
    Admin_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID)
) ENGINE=InnoDB;

-- 5. Core Personnel: Nurses
CREATE TABLE Nurse (
    Nurse_ID INT AUTO_INCREMENT PRIMARY KEY,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Phone VARCHAR(20),
    Department_ID INT,
    User_ID INT,
    Admin_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID)
) ENGINE=InnoDB;

-- 6. Core Personnel: Receptionists
CREATE TABLE Receptionist (
    Receptionist_ID INT AUTO_INCREMENT PRIMARY KEY,
    Full_Name VARCHAR(150) NOT NULL,
    Phone VARCHAR(20),
    Shift_Time VARCHAR(50),
    User_ID INT,
    Admin_ID INT,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID)
) ENGINE=InnoDB;

-- 7. Infrastructure: Rooms
CREATE TABLE Room (
    Room_ID INT AUTO_INCREMENT PRIMARY KEY,
    Phone VARCHAR(20),
    Status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    Room_Type VARCHAR(50),
    Doctor_ID INT,
    Nurse_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Nurse_ID) REFERENCES Nurse(Nurse_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
) ENGINE=InnoDB;

-- 8. Core Data: Patients
CREATE TABLE Patient (
    Patient_ID INT AUTO_INCREMENT PRIMARY KEY,
    Registration_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Gender ENUM('Male', 'Female', 'Other'),
    Date_of_Birth DATE NOT NULL,
    Age INT, -- Physical column per ERD; updated via PHP
    City VARCHAR(100),
    Street VARCHAR(100),
    Building VARCHAR(50),
    Emergency_Contact VARCHAR(20),
    Blood_Group VARCHAR(5),
    Room_ID INT,
    Receptionist_ID INT,
    User_ID INT,
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Specialized Innovation: AI Analysis
CREATE TABLE AI_Analysis (
    Analysis_ID INT AUTO_INCREMENT PRIMARY KEY,
    Input_Symptoms TEXT NOT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Confidence_Level DECIMAL(5, 2),
    Risk_Score INT,
    Predicted_Specialization VARCHAR(100),
    Patient_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 10. Scheduling: Appointments
CREATE TABLE Appointment (
    Appointment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE NOT NULL,
    Time TIME NOT NULL,
    Status ENUM('Scheduled', 'Checked-In', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
    Diagnosis TEXT,
    Patient_ID INT,
    Room_ID INT,
    Doctor_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE,
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
) ENGINE=InnoDB;

-- 11. Clinical Records: Medical History
CREATE TABLE Medical_History (
    Record_ID INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE NOT NULL,
    Diagnosis TEXT,
    Notes TEXT,
    Treatment TEXT,
    Patient_ID INT,
    Doctor_ID INT,
    Appointment_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID)
) ENGINE=InnoDB;

-- 12. Clinical Records: Prescriptions
CREATE TABLE Prescription (
    Prescription_ID INT AUTO_INCREMENT PRIMARY KEY,
    Medications TEXT NOT NULL,
    Dosage VARCHAR(100),
    Instructions TEXT,
    Appointment_ID INT,
    Doctor_ID INT,
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID)
) ENGINE=InnoDB;

-- 13. Financials: Billing
CREATE TABLE Billing (
    Bill_ID INT AUTO_INCREMENT PRIMARY KEY,
    Amount DECIMAL(10, 2) NOT NULL,
    Payment_Date DATE,
    Payment_Method VARCHAR(50),
    Payment_Status ENUM('Paid', 'Unpaid', 'Pending') DEFAULT 'Unpaid',
    Patient_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
) ENGINE=InnoDB;

-- 14. Multi-Valued Attributes: Phones
CREATE TABLE Patient_phones (
    Patient_ID INT,
    PhoneNumber VARCHAR(20),
    PRIMARY KEY (Patient_ID, PhoneNumber),
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Doctor_phones (
    Doctor_ID INT,
    PhoneNumber VARCHAR(20),
    PRIMARY KEY (Doctor_ID, PhoneNumber),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 15. Junction Tables for Complex Relationships
CREATE TABLE Doctors_Patient (
    Doctor_ID INT,
    Patient_ID INT,
    PRIMARY KEY (Doctor_ID, Patient_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Receptionist_Doctors (
    Doctor_ID INT,
    Receptionist_ID INT,
    PRIMARY KEY (Doctor_ID, Receptionist_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SEED DATA: THE INITIAL ADMIN ACCOUNT
-- Login: admin@hms.com | Password: Password123
INSERT INTO User_Login (Email, Password, Role) 
VALUES ('admin@hms.com', '$2y$10$7r3F6Xv7Xf3Q.M.Pz.T7hO9l3Yl5I7v2P3n4B5v6C7D8E9F0G1H2I', 'Admin');

INSERT INTO Admin (Full_Name, Security_Question, Security_Answer, User_ID) 
VALUES ('System Administrator', 'What is your project code?', 'V4_STRICT', 1);