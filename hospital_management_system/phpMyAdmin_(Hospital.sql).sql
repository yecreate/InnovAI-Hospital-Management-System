-- ======================================================
-- HOSPITAL MANAGEMENT SYSTEM – INNOVAI MEDICAL CENTER
-- Strictly based on Project_ERD.pdf & Project_Schema.pdf
-- Version for MySQL/MariaDB
-- ======================================================

-- ------------------------------------------------------
-- Table: User_Login
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS User_Login (
    User_ID INT PRIMARY KEY AUTO_INCREMENT,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Admin','Receptionist','Doctor','Nurse','Patient') NOT NULL
);

-- ------------------------------------------------------
-- Table: Admin
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Admin (
    Admin_ID INT PRIMARY KEY AUTO_INCREMENT,
    Full_Name VARCHAR(100) NOT NULL,
    Security_Question VARCHAR(255),
    Security_Answer VARCHAR(255),
    User_ID INT UNIQUE NOT NULL,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- Table: Department
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Department (
    Department_ID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Location VARCHAR(100)
);

-- ------------------------------------------------------
-- Table: Receptionist
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Receptionist (
    Receptionist_ID INT PRIMARY KEY AUTO_INCREMENT,
    Full_Name VARCHAR(100) NOT NULL,
    Shift_Time VARCHAR(50),
    Phone VARCHAR(20),
    User_ID INT UNIQUE NOT NULL,
    Admin_ID INT,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Doctor
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Doctor (
    Doctor_ID INT PRIMARY KEY AUTO_INCREMENT,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Specialization VARCHAR(100),
    Email VARCHAR(100),
    License_No VARCHAR(50),
    Phone VARCHAR(20),
    Salary DECIMAL(10,2),
    Department_ID INT,
    User_ID INT UNIQUE NOT NULL,
    Admin_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID) ON DELETE SET NULL,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Doctor_phones (multi‑valued)
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Doctor_phones (
    Doctor_ID INT NOT NULL,
    PhoneNumber VARCHAR(20) NOT NULL,
    PRIMARY KEY (Doctor_ID, PhoneNumber),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- Table: Nurse
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Nurse (
    Nurse_ID INT PRIMARY KEY AUTO_INCREMENT,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Phone VARCHAR(20),
    Department_ID INT,
    User_ID INT UNIQUE NOT NULL,
    Admin_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID) ON DELETE SET NULL,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Room
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Room (
    Room_ID INT PRIMARY KEY AUTO_INCREMENT,
    Room_Type VARCHAR(50),
    Status ENUM('Available','Occupied','Maintenance') DEFAULT 'Available',
    Phone VARCHAR(20),
    Doctor_ID INT,
    Nurse_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE SET NULL,
    FOREIGN KEY (Nurse_ID) REFERENCES Nurse(Nurse_ID) ON DELETE SET NULL,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Patient
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Patient (
    Patient_ID INT PRIMARY KEY AUTO_INCREMENT,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Gender ENUM('Male','Female','Other'),
    Date_of_Birth DATE NOT NULL,
    Age INT,   -- physical column per ERD, updated by trigger
    City VARCHAR(50),
    Street VARCHAR(100),
    Building VARCHAR(50),
    Registration_Date DATE DEFAULT (CURDATE()),
    Blood_Group VARCHAR(10),
    Emergency_Contact VARCHAR(20),
    Room_ID INT,
    Receptionist_ID INT,
    User_ID INT UNIQUE NOT NULL,
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID) ON DELETE SET NULL,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE SET NULL,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID) ON DELETE CASCADE
);

-- Trigger to auto‑update Age from Date_of_Birth (per ERD requirement)
DELIMITER $$
CREATE TRIGGER update_patient_age BEFORE INSERT ON Patient
FOR EACH ROW
BEGIN
    SET NEW.Age = TIMESTAMPDIFF(YEAR, NEW.Date_of_Birth, CURDATE());
END$$
CREATE TRIGGER update_patient_age_on_update BEFORE UPDATE ON Patient
FOR EACH ROW
BEGIN
    IF NEW.Date_of_Birth != OLD.Date_of_Birth THEN
        SET NEW.Age = TIMESTAMPDIFF(YEAR, NEW.Date_of_Birth, CURDATE());
    END IF;
END$$
DELIMITER ;

-- ------------------------------------------------------
-- Table: Patient_phones (multi‑valued)
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Patient_phones (
    Patient_ID INT NOT NULL,
    PhoneNumber VARCHAR(20) NOT NULL,
    PRIMARY KEY (Patient_ID, PhoneNumber),
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- Table: Billing
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Billing (
    Bill_ID INT PRIMARY KEY AUTO_INCREMENT,
    Amount DECIMAL(10,2) NOT NULL,
    Payment_Date DATE,
    Payment_Method VARCHAR(50),
    Payment_Status ENUM('Paid','Pending','Cancelled') DEFAULT 'Pending',
    Patient_ID INT NOT NULL,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Appointment
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Appointment (
    Appointment_ID INT PRIMARY KEY AUTO_INCREMENT,
    Date DATE NOT NULL,
    Time TIME NOT NULL,
    Status ENUM('Scheduled','Checked-In','Completed','Cancelled','Ready') DEFAULT 'Scheduled',
    Diagnosis VARCHAR(255),
    Patient_ID INT NOT NULL,
    Doctor_ID INT NOT NULL,
    Room_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE,
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID) ON DELETE SET NULL,
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Medical_History
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Medical_History (
    Record_ID INT PRIMARY KEY AUTO_INCREMENT,
    Date DATE NOT NULL,
    Diagnosis VARCHAR(255),
    Treatment TEXT,
    Notes TEXT,
    Patient_ID INT NOT NULL,
    Doctor_ID INT NOT NULL,
    Appointment_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE,
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID) ON DELETE SET NULL
);

-- ------------------------------------------------------
-- Table: Prescription
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS Prescription (
    Prescription_ID INT PRIMARY KEY AUTO_INCREMENT,
    Medications VARCHAR(255) NOT NULL,
    Dosage VARCHAR(100),
    Instructions TEXT,
    Appointment_ID INT NOT NULL,
    Doctor_ID INT NOT NULL,
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID) ON DELETE CASCADE,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID) ON DELETE CASCADE
);

-- ------------------------------------------------------
-- Table: AI_Analysis
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS AI_Analysis (
    Analysis_ID INT PRIMARY KEY AUTO_INCREMENT,
    Input_Symptoms TEXT NOT NULL,
    Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    Confidence_Level DECIMAL(5,2),
    Risk_Score DECIMAL(5,2),
    Predicted_Specialization VARCHAR(100),
    Summary_Text TEXT,   -- added for AI health passport summary (waiver granted)
    Patient_ID INT NOT NULL,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID) ON DELETE CASCADE
);