-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2026 at 10:12 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `User_ID` int(11) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('Admin','Receptionist','Doctor','Nurse','Patient') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`User_ID`, `Email`, `Password`, `Role`) VALUES
(1, 'admin.innovai@iu.edu.eg', 'Password123', 'Admin'),
(2, 'reception.sara@iu.edu.eg', 'Password123', 'Receptionist'),
(3, 'reception.ahmed@iu.edu.eg', 'Password123', 'Receptionist'),
(5, 'doctor.mariam@iu.edu.eg', 'Password123', 'Doctor'),
(6, 'doctor.omar@iu.edu.eg', 'Password123', 'Doctor'),
(7, 'doctor.laila@iu.edu.eg', 'Password123', 'Doctor'),
(9, 'nurse.heba@iu.edu.eg', 'Password123', 'Nurse'),
(10, 'nurse.samir@iu.edu.eg', 'Password123', 'Nurse'),
(11, 'patient.mohamed@example.com', 'Password123', 'Patient'),
(12, 'patient.fatma@example.com', 'Password123', 'Patient'),
(13, 'patient.ahmed@example.com', 'Password123', 'Patient'),
(14, 'patient.nour@example.com', 'Password123', 'Patient'),
(15, 'patient.mostafa@example.com', 'Password123', 'Patient'),
(16, 'patient.hana@example.com', 'Password123', 'Patient'),
(17, 'patient.kareem@example.com', 'Password123', 'Patient'),
(18, 'patient.salma@example.com', 'Password123', 'Patient'),
(19, 'patient.omar@example.com', 'Password123', 'Patient'),
(20, 'patient.lina@example.com', 'Password123', 'Patient'),
(21, 'patient.youssef@example.com', 'Password123', 'Patient'),
(22, 'patient.mariam@example.com', 'Password123', 'Patient'),
(23, 'patient.adham@example.com', 'Password123', 'Patient'),
(24, 'patient.nadia@example.com', 'Password123', 'Patient'),
(25, 'patient.hassan@example.com', 'Password123', 'Patient'),
(26, 'patient.dina@example.com', 'Password123', 'Patient'),
(27, 'patient.seif@example.com', 'Password123', 'Patient'),
(28, 'patient.rania@example.com', 'Password123', 'Patient'),
(29, 'patient.tarek@example.com', 'Password123', 'Patient'),
(30, 'patient.sohila@example.com', 'Password123', 'Patient'),
(31, 'patient.karim@example.com', 'Password123', 'Patient'),
(32, 'patient.nourhan@example.com', 'Password123', 'Patient'),
(33, 'patient.ahmedr@example.com', 'Password123', 'Patient'),
(34, 'patient.hend@example.com', 'Password123', 'Patient'),
(35, 'patient.mahmoud@example.com', 'Password123', 'Patient'),
(36, 'patient.eman@example.com', 'Password123', 'Patient'),
(37, 'patient.yassin@example.com', 'Password123', 'Patient'),
(38, 'patient.mona@example.com', 'Password123', 'Patient'),
(39, 'patient.ibrahim@example.com', 'Password123', 'Patient'),
(40, 'patient.nada@example.com', 'Password123', 'Patient'),
(41, 'patient.ali@example.com', 'Password123', 'Patient'),
(42, 'patient.rana@example.com', 'Password123', 'Patient'),
(43, 'patient.hossam@example.com', 'Password123', 'Patient'),
(44, 'patient.sara.hany@example.com', 'Password123', 'Patient'),
(45, 'patient.waleed@example.com', 'Password123', 'Patient'),
(46, 'patient.donia@example.com', 'Password123', 'Patient'),
(47, 'patient.khaled@example.com', 'Password123', 'Patient'),
(48, 'patient.amira@example.com', 'Password123', 'Patient'),
(49, 'patient.mostafa.reda@example.com', 'Password123', 'Patient'),
(50, 'patient.lobna@example.com', 'Password123', 'Patient'),
(51, 'ahmed.ramadan.24030005@iu.edu.eg', '$2y$10$82Ef3d6fxNZz5SXPaMVJLuSJzE6eil/cb576B/vZB7vvQL9ZbEa3i', 'Doctor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`User_ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_login`
--
ALTER TABLE `user_login`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
