create database Hospital
use Hospital

CREATE TABLE User_Login (
    User_ID INT PRIMARY KEY,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Role VARCHAR(50) NOT NULL
)

CREATE TABLE Admin (
    Admin_ID INT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Security_Question VARCHAR(255),
    Security_Answer VARCHAR(255),
    User_ID INT,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID)
)

CREATE TABLE Department (
    Department_ID INT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Location VARCHAR(100)
)

CREATE TABLE Receptionist (
    Receptionist_ID INT PRIMARY KEY,
    Full_Name VARCHAR(100) NOT NULL,
    Phone VARCHAR(20),
    Shift_Time VARCHAR(50),
    User_ID INT,
    Admin_ID INT,
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID),
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID)
)

CREATE TABLE Doctor (
    Doctor_ID INT PRIMARY KEY,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Specialization VARCHAR(100),
    Email VARCHAR(100),
    License_No VARCHAR(50),
    Phone VARCHAR(20),
    Salary DECIMAL(10,2),
    Department_ID INT,
    User_ID INT,
    Admin_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID),
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
)

CREATE TABLE Nurse (
    Nurse_ID INT PRIMARY KEY,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Phone VARCHAR(20),
    Department_ID INT,
    User_ID INT,
    Admin_ID INT,
    FOREIGN KEY (Department_ID) REFERENCES Department(Department_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID),
    FOREIGN KEY (Admin_ID) REFERENCES Admin(Admin_ID)
)

CREATE TABLE Room (
    Room_ID INT PRIMARY KEY,
    Phone VARCHAR(20),
    Status VARCHAR(50),
    Room_Type VARCHAR(50),
    Doctor_ID INT,
    Nurse_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Nurse_ID) REFERENCES Nurse(Nurse_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
)

CREATE TABLE Patient (
    Patient_ID INT PRIMARY KEY,
    Registration_Date DATE,
    Fname VARCHAR(50) NOT NULL,
    Lname VARCHAR(50) NOT NULL,
    Gender VARCHAR(10),
    Date_of_Birth DATE,
    City VARCHAR(50),
    Street VARCHAR(100),
    Building VARCHAR(50),
    Emergency_Contact VARCHAR(20),
    Blood_Group VARCHAR(10),
    Room_ID INT,
    Receptionist_ID INT,
    User_ID INT,
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID),
    FOREIGN KEY (User_ID) REFERENCES User_Login(User_ID)
)

CREATE TABLE Patient_phones (
    Patient_ID INT,
    PhoneNumber VARCHAR(20),
    PRIMARY KEY (Patient_ID, PhoneNumber),
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID)
)

CREATE TABLE Doctor_phones (
    Doctor_ID INT,
    PhoneNumber VARCHAR(20),
    PRIMARY KEY (Doctor_ID, PhoneNumber),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID)
)

CREATE TABLE Billing (
    Bill_ID INT PRIMARY KEY,
    Amount DECIMAL(10,2),
    Payment_Date DATE,
    Payment_Method VARCHAR(50),
    Payment_Status VARCHAR(50),
    Patient_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
)

CREATE TABLE Appointment (
    Appointment_ID INT PRIMARY KEY,
    Date DATE,
    Time TIME,
    Status VARCHAR(50),
    Diagnosis VARCHAR(255),
    Patient_ID INT,
    Room_ID INT,
    Doctor_ID INT,
    Receptionist_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID),
    FOREIGN KEY (Room_ID) REFERENCES Room(Room_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Receptionist_ID) REFERENCES Receptionist(Receptionist_ID)
)

CREATE TABLE Medical_History (
    Record_ID INT PRIMARY KEY,
    Date DATE,
    Diagnosis VARCHAR(255),
    Notes VARCHAR(255),
    Treatment VARCHAR(255),
    Patient_ID INT,
    Doctor_ID INT,
    Appointment_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID),
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID)
)

CREATE TABLE Prescription (
    Prescription_ID INT PRIMARY KEY,
    Medications VARCHAR(255),
    Dosage VARCHAR(100),
    Instructions VARCHAR(255),
    Appointment_ID INT,
    Doctor_ID INT,
    FOREIGN KEY (Appointment_ID) REFERENCES Appointment(Appointment_ID),
    FOREIGN KEY (Doctor_ID) REFERENCES Doctor(Doctor_ID)
)

CREATE TABLE AI_Analysis (
    Analysis_ID INT PRIMARY KEY,
    Input_Symptoms VARCHAR(255),
    Timestamp DATETIME,
    Confidence_Level DECIMAL(5,2),
    Risk_Score DECIMAL(5,2),
    Predicted_Specialization VARCHAR(100),
    Patient_ID INT,
    FOREIGN KEY (Patient_ID) REFERENCES Patient(Patient_ID)
)