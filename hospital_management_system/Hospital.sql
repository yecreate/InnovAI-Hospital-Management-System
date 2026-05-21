
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `hospital`;
USE `hospital`;

-- --------------------------------------------------------
-- Table structure for table `user_login`
-- --------------------------------------------------------

CREATE TABLE `user_login` (
  `User_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Password` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL UNIQUE,
  `Role` varchar(50) NOT NULL,
  PRIMARY KEY (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
select *
from user_login 
INSERT INTO `user_login` (`User_ID`, `Password`, `Email`, `Role`) VALUES
(1, 'admin123', 'admin@hospital.com', 'Admin'),
(2, 'doctor123', 'doctor@hospital.com', 'Doctor'),
(3, 'patient123', 'patient@hospital.com', 'Patient'),
(4, 'reception123', 'reception@hospital.com', 'Receptionist');

-- --------------------------------------------------------
-- Table structure for table `admin`
-- --------------------------------------------------------

CREATE TABLE `admin` (
  `Admin_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Full_Name` varchar(100) NOT NULL,
  `Security_Question` varchar(255) DEFAULT NULL,
  `Security_Answer` varchar(255) DEFAULT NULL,
  `User_ID` int(11) NOT NULL,
  PRIMARY KEY (`Admin_ID`),
  CONSTRAINT `fk_admin_user` FOREIGN KEY (`User_ID`) REFERENCES `user_login` (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `admin` VALUES
(1, 'Dr. Ahmed Mostafa', 'What is your favorite color?', 'Blue', 1);

-- --------------------------------------------------------
-- Table structure for table `department`
-- --------------------------------------------------------

CREATE TABLE `department` (
  `Department_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `Location` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`Department_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `department` VALUES
(1, 'Cardiology', 'First Floor'),
(2, 'Neurology', 'Second Floor'),
(3, 'Emergency', 'Ground Floor');

-- --------------------------------------------------------
-- Table structure for table `receptionist`
-- --------------------------------------------------------

CREATE TABLE `receptionist` (
  `Receptionist_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Full_Name` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Shift_Time` varchar(50) DEFAULT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Receptionist_ID`),
  CONSTRAINT `fk_reception_user` FOREIGN KEY (`User_ID`) REFERENCES `user_login` (`User_ID`),
  CONSTRAINT `fk_reception_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `receptionist` VALUES
(1, 'Sara Mohamed', '01012345678', 'Morning', 4, 1);

-- --------------------------------------------------------
-- Table structure for table `doctor`
-- --------------------------------------------------------

CREATE TABLE `doctor` (
  `Doctor_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Fname` varchar(50) NOT NULL,
  `Lname` varchar(50) NOT NULL,
  `Specialization` varchar(100) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `License_No` varchar(50) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Salary` decimal(10,2) DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  `Receptionist_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Doctor_ID`),
  CONSTRAINT `fk_doctor_department` FOREIGN KEY (`Department_ID`) REFERENCES `department` (`Department_ID`),
  CONSTRAINT `fk_doctor_user` FOREIGN KEY (`User_ID`) REFERENCES `user_login` (`User_ID`),
  CONSTRAINT `fk_doctor_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`),
  CONSTRAINT `fk_doctor_receptionist` FOREIGN KEY (`Receptionist_ID`) REFERENCES `receptionist` (`Receptionist_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `doctor` VALUES
(1, 'Omar', 'Hassan', 'Cardiology', 'omar@hospital.com', 'LIC123', '01111111111', 25000.00, 1, 2, 1, 1);

-- --------------------------------------------------------
-- Table structure for table `nurse`
-- --------------------------------------------------------

CREATE TABLE `nurse` (
  `Nurse_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Fname` varchar(50) NOT NULL,
  `Lname` varchar(50) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Admin_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Nurse_ID`),
  CONSTRAINT `fk_nurse_department` FOREIGN KEY (`Department_ID`) REFERENCES `department` (`Department_ID`),
  CONSTRAINT `fk_nurse_user` FOREIGN KEY (`User_ID`) REFERENCES `user_login` (`User_ID`),
  CONSTRAINT `fk_nurse_admin` FOREIGN KEY (`Admin_ID`) REFERENCES `admin` (`Admin_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `nurse` VALUES
(1, 'Mona', 'Ali', '01222222222', 1, NULL, 1);

-- --------------------------------------------------------
-- Table structure for table `room`
-- --------------------------------------------------------

CREATE TABLE `room` (
  `Room_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Phone` varchar(20) DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Room_Type` varchar(50) DEFAULT NULL,
  `Doctor_ID` int(11) DEFAULT NULL,
  `Nurse_ID` int(11) DEFAULT NULL,
  `Receptionist_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Room_ID`),
  CONSTRAINT `fk_room_doctor` FOREIGN KEY (`Doctor_ID`) REFERENCES `doctor` (`Doctor_ID`),
  CONSTRAINT `fk_room_nurse` FOREIGN KEY (`Nurse_ID`) REFERENCES `nurse` (`Nurse_ID`),
  CONSTRAINT `fk_room_receptionist` FOREIGN KEY (`Receptionist_ID`) REFERENCES `receptionist` (`Receptionist_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `room` VALUES
(1, '1001', 'Available', 'ICU', 1, 1, 1);

-- --------------------------------------------------------
-- Table structure for table `patient`
-- --------------------------------------------------------

CREATE TABLE `patient` (
  `Patient_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Registration_Date` date DEFAULT NULL,
  `Fname` varchar(50) NOT NULL,
  `Lname` varchar(50) NOT NULL,
  `Gender` varchar(10) DEFAULT NULL,
  `Date_of_Birth` date DEFAULT NULL,
  `City` varchar(50) DEFAULT NULL,
  `Street` varchar(100) DEFAULT NULL,
  `Building` varchar(50) DEFAULT NULL,
  `Emergency_Contact` varchar(20) DEFAULT NULL,
  `Blood_Group` varchar(10) DEFAULT NULL,
  `Room_ID` int(11) DEFAULT NULL,
  `Receptionist_ID` int(11) DEFAULT NULL,
  `User_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Patient_ID`),
  CONSTRAINT `fk_patient_room` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`),
  CONSTRAINT `fk_patient_receptionist` FOREIGN KEY (`Receptionist_ID`) REFERENCES `receptionist` (`Receptionist_ID`),
  CONSTRAINT `fk_patient_user` FOREIGN KEY (`User_ID`) REFERENCES `user_login` (`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `patient` VALUES
(1, '2026-05-01', 'Ali', 'Mahmoud', 'Male', '2001-08-15', 'Cairo', 'Nasr City', '12A', '01099999999', 'O+', 1, 1, 3);

-- --------------------------------------------------------
-- Table structure for table `appointment`
-- --------------------------------------------------------

CREATE TABLE `appointment` (
  `Appointment_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` date DEFAULT NULL,
  `Time` time DEFAULT NULL,
  `Status` varchar(50) DEFAULT NULL,
  `Diagnosis` varchar(255) DEFAULT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Room_ID` int(11) DEFAULT NULL,
  `Doctor_ID` int(11) DEFAULT NULL,
  `Receptionist_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Appointment_ID`),
  CONSTRAINT `fk_appointment_patient` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`Patient_ID`),
  CONSTRAINT `fk_appointment_room` FOREIGN KEY (`Room_ID`) REFERENCES `room` (`Room_ID`),
  CONSTRAINT `fk_appointment_doctor` FOREIGN KEY (`Doctor_ID`) REFERENCES `doctor` (`Doctor_ID`),
  CONSTRAINT `fk_appointment_receptionist` FOREIGN KEY (`Receptionist_ID`) REFERENCES `receptionist` (`Receptionist_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `appointment` VALUES
(1, '2026-05-18', '10:30:00', 'Completed', 'Chest Pain', 1, 1, 1, 1);

-- --------------------------------------------------------
-- Table structure for table `medical_history`
-- --------------------------------------------------------

CREATE TABLE `medical_history` (
  `Record_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Date` date DEFAULT NULL,
  `Diagnosis` varchar(255) DEFAULT NULL,
  `Notes` varchar(255) DEFAULT NULL,
  `Treatment` varchar(255) DEFAULT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Doctor_ID` int(11) DEFAULT NULL,
  `Appointment_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Record_ID`),
  CONSTRAINT `fk_history_patient` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`Patient_ID`),
  CONSTRAINT `fk_history_doctor` FOREIGN KEY (`Doctor_ID`) REFERENCES `doctor` (`Doctor_ID`),
  CONSTRAINT `fk_history_appointment` FOREIGN KEY (`Appointment_ID`) REFERENCES `appointment` (`Appointment_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `prescription`
-- --------------------------------------------------------

CREATE TABLE `prescription` (
  `Prescription_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Medications` varchar(255) DEFAULT NULL,
  `Dosage` varchar(100) DEFAULT NULL,
  `Instructions` varchar(255) DEFAULT NULL,
  `Appointment_ID` int(11) DEFAULT NULL,
  `Doctor_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Prescription_ID`),
  CONSTRAINT `fk_prescription_appointment` FOREIGN KEY (`Appointment_ID`) REFERENCES `appointment` (`Appointment_ID`),
  CONSTRAINT `fk_prescription_doctor` FOREIGN KEY (`Doctor_ID`) REFERENCES `doctor` (`Doctor_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `billing`
-- --------------------------------------------------------

CREATE TABLE `billing` (
  `Bill_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Amount` decimal(10,2) DEFAULT NULL,
  `Payment_Date` date DEFAULT NULL,
  `Payment_Method` varchar(50) DEFAULT NULL,
  `Payment_Status` varchar(50) DEFAULT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  `Receptionist_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Bill_ID`),
  CONSTRAINT `fk_billing_patient` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`Patient_ID`),
  CONSTRAINT `fk_billing_receptionist` FOREIGN KEY (`Receptionist_ID`) REFERENCES `receptionist` (`Receptionist_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `billing` VALUES
(1, 1500.00, '2026-05-18', 'Cash', 'Paid', 1, 1);

-- --------------------------------------------------------
-- Table structure for table `ai_analysis`
-- --------------------------------------------------------

CREATE TABLE `ai_analysis` (
  `Analysis_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Input_Symptoms` text NOT NULL,
  `Timestamp` datetime DEFAULT current_timestamp(),
  `Confidence_Level` decimal(5,2) DEFAULT NULL,
  `Risk_Score` decimal(5,2) DEFAULT NULL,
  `Predicted_Specialization` varchar(100) DEFAULT NULL,
  `Patient_ID` int(11) DEFAULT NULL,
  PRIMARY KEY (`Analysis_ID`),
  CONSTRAINT `fk_ai_patient` FOREIGN KEY (`Patient_ID`) REFERENCES `patient` (`Patient_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ai_analysis` VALUES
(1, 'Fever, cough, headache', '2026-05-18 12:00:00', 92.50, 70.00, 'General Medicine', 1);

create view VW_financial_Report as 
select b.Payment_Status, b.Payment_Date, p.Fname, p.Lname, b.Payment_Method, b.Amount
from Billing b join Patient p 
on b.Patient_ID = p.Patient_ID

select * from VW_financial_Report
select * from patient
COMMIT;