-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 06, 2025 at 01:17 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id_int` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `class_date` date NOT NULL,
  `class_id` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','leave') DEFAULT 'present',
  `recorded_by` varchar(20) DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id_int`, `student_id`, `class_date`, `class_id`, `date`, `status`, `recorded_by`, `recorded_at`) VALUES
(1, '202501A001', '2025-09-30', '1-A', '2025-09-30', 'present', 'r1@gmail.com', '2025-09-30 02:02:31'),
(12, '202501A001', '2025-10-01', '1-A', '2025-10-01', 'present', '2025TCH002', '2025-10-01 05:20:32');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` varchar(20) NOT NULL,
  `user_id` varchar(20) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `tuition_fee` decimal(10,2) DEFAULT 0.00,
  `class_teacher_id` varchar(20) DEFAULT NULL,
  `teacher_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `description`, `tuition_fee`, `class_teacher_id`, `teacher_id`) VALUES
('C001', 'PG', 'Play Group', 600.00, NULL, NULL),
('C002', 'LKG', 'Lower Kindergarten', 600.00, NULL, NULL),
('C003', 'UKG', 'Upper Kindergarten', 600.00, NULL, NULL),
('C004', '1', 'Class 1', 700.00, NULL, NULL),
('C005', '2', 'Class 2', 700.00, NULL, NULL),
('C006', '3', 'Class 3', 700.00, NULL, NULL),
('C007', '4', 'Class 4', 800.00, NULL, NULL),
('C008', '5', 'Class 5', 800.00, NULL, NULL),
('C009', '6', 'Class 6', 800.00, NULL, NULL),
('C010', '7', 'Class 7', 900.00, NULL, NULL),
('C011', '8', 'Class 8', 900.00, NULL, NULL),
('C012', '9', 'Class 9', 1200.00, NULL, NULL),
('C013', '10', 'Class 10', 1200.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `classrooms`
--

CREATE TABLE `classrooms` (
  `classroom_id` varchar(20) NOT NULL,
  `class_name` varchar(50) NOT NULL,
  `section` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classrooms`
--

INSERT INTO `classrooms` (`classroom_id`, `class_name`, `section`) VALUES
('1-A', '1', 'A'),
('1-B', '1', 'B'),
('1-C', '1', 'C'),
('10-A', '10', 'A'),
('10-B', '10', 'B'),
('10-C', '10', 'C'),
('2-A', '2', 'A'),
('2-B', '2', 'B'),
('2-C', '2', 'C'),
('3-A', '3', 'A'),
('3-B', '3', 'B'),
('3-C', '3', 'C'),
('4-A', '4', 'A'),
('4-B', '4', 'B'),
('4-C', '4', 'C'),
('5-A', '5', 'A'),
('5-B', '5', 'B'),
('5-C', '5', 'C'),
('6-A', '6', 'A'),
('6-B', '6', 'B'),
('6-C', '6', 'C'),
('7-A', '7', 'A'),
('7-B', '7', 'B'),
('7-C', '7', 'C'),
('8-A', '8', 'A'),
('8-B', '8', 'B'),
('8-C', '8', 'C'),
('9-A', '9', 'A'),
('9-B', '9', 'B'),
('9-C', '9', 'C'),
('LKG-A', 'LKG', 'A'),
('LKG-B', 'LKG', 'B'),
('PG', 'PG', 'NA'),
('PG-A', 'PG', 'A'),
('PG-B', 'PG', 'B'),
('UKG-A', 'UKG', 'A'),
('UKG-B', 'UKG', 'B');

-- --------------------------------------------------------

--
-- Table structure for table `exam_timetable`
--

CREATE TABLE `exam_timetable` (
  `id` int(11) NOT NULL,
  `classroom_id` varchar(20) NOT NULL,
  `exam_type` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `exam_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_timetable`
--

INSERT INTO `exam_timetable` (`id`, `classroom_id`, `exam_type`, `subject`, `exam_date`, `start_time`, `end_time`) VALUES
(1, '1-A', 'Annual Exam', 'Hindi', '2025-10-13', '09:00:00', '10:00:00'),
(2, '1-A', 'Annual Exam', 'English', '2025-10-14', '09:00:00', '10:00:00'),
(3, '1-A', 'Unit Test - 1', 'hindi', '2025-10-09', '09:00:00', '11:00:00'),
(4, '1-A', 'Unit Test - 1', 'math', '2025-10-10', '09:00:00', '11:00:00'),
(5, '1-A', 'Unit Test - 1', 'english', '2025-10-11', '09:00:00', '11:00:00'),
(6, '1-A', 'Unit Test - 1', 'art', '2025-10-12', '09:00:00', '11:00:00'),
(7, '1-A', 'Unit Test - 1', 'science', '2025-10-13', '09:00:00', '11:00:00'),
(8, '1-A', 'Half Yearly', 'hindi', '2025-12-10', '09:09:00', '10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `last_payment_date` date DEFAULT NULL,
  `tuition_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `travel_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `student_id`, `total_amount`, `paid_amount`, `due_amount`, `last_payment_date`, `tuition_fee`, `travel_fee`, `due_date`) VALUES
(1, '202501A001', 1200.00, 1200.00, 0.00, NULL, 700.00, 500.00, NULL),
(2, '2025LKA001', 1000.00, 800.00, 200.00, NULL, 600.00, 400.00, NULL),
(3, '2025PGA001', 600.00, 600.00, 0.00, NULL, 600.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fees_invoices`
--

CREATE TABLE `fees_invoices` (
  `id` varchar(20) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','paid','partial') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees_payments`
--

CREATE TABLE `fees_payments` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `pay_mode` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fees_payments`
--

INSERT INTO `fees_payments` (`id`, `student_id`, `amount`, `pay_date`, `pay_mode`, `receipt_no`) VALUES
(1, '202501A001', 1000.00, '2025-09-30', 'Online', NULL),
(2, '202501A001', 200.00, '2025-09-30', 'Cash', NULL),
(3, '2025LKA001', 200.00, '2025-09-30', 'Cash', NULL),
(4, '2025LKA001', 100.00, '2025-09-30', 'Cash', 'REC-20250930-5929'),
(5, '2025PGA001', 600.00, '2025-10-01', 'Cash', 'REC-20251001-6580'),
(6, '2025LKA001', 100.00, '2025-10-02', 'Cash', 'REC-20251002-8571'),
(7, '2025LKA001', 100.00, '2025-10-02', 'Cash', 'REC-20251002-9441');

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `subject_id` varchar(20) NOT NULL,
  `obtained` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` varchar(20) NOT NULL,
  `invoice_id` varchar(20) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL,
  `paid_on` date NOT NULL,
  `payment_mode` enum('cash','card','online') DEFAULT 'cash',
  `recorded_by` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` varchar(20) NOT NULL,
  `admission_no` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `class_name` varchar(20) DEFAULT NULL,
  `classroom_id` varchar(20) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  `guardian_name` varchar(150) DEFAULT NULL,
  `guardian_phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `admission_no`, `first_name`, `last_name`, `dob`, `gender`, `class_name`, `classroom_id`, `section`, `guardian_name`, `guardian_phone`, `address`, `photo`, `created_at`) VALUES
('202501A001', '202501001', 'saurabh', 'lal', '2022-01-20', 'male', '1', '1-A', 'A', 'kkk', '9871333094', 'Maniyarepur\r\nPOST-CHANDWAK', '202501A001.jpg', '2025-09-29 17:40:16'),
('202501A002', '202501002', 'rahul', 'pandey', '2020-12-12', 'male', '1', '1-A', 'A', 'ppp', '9871333098', 'Maniyarepur\r\nPOST-CHANDWAK', '202501A002.jpeg', '2025-10-05 11:57:17'),
('2025LKA001', '2025LK001', 'surabhi', 'yadav', '2022-01-20', 'female', 'LKG', 'LKG-A', 'A', 'ppp', '1234567890', 'Maniyarepur\r\nPOST-CHANDWAK', '2025LKA001.jpeg', '2025-09-30 14:23:30'),
('2025PGA001', '2025PG001', 'Krishna', 'Dube', '2022-03-12', 'male', 'PG', 'PG-A', 'A', 'ppp', '9871333094', 'C29 shashi garden\r\nMayur Vihar Phase 1', '2025PGA001.jpg', '2025-10-01 05:17:25');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` varchar(20) NOT NULL,
  `class_id` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `teacher_id` varchar(20) DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `class_id`, `name`, `teacher_id`, `total_marks`) VALUES
('SUBJ68ddfcbf80737', 'C002', 'hindi', '2025TCH002', 100),
('SUBJ68de0027e4839', 'C002', 'math', '2025TCH002', 100),
('SUBJ68de03fb1fe84', 'C002', 'math', NULL, 100),
('sub_68e137fe29b03', 'C004', 'hindi', '2025TCH002', 100),
('sub_68e1380922961', 'C004', 'math', '2025TCH002', 100),
('sub_68e138131c253', 'C004', 'english', '2025TCH002', 100),
('sub_68e1381817141', 'C004', 'art', '2025TCH002', 100),
('sub_68e1381f8d7f2', 'C004', 'science', '2025TCH002', 100),
('sub_68e138c7e0ab0', 'C004', 'science', '2025TCH002', 100);

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `classroom_id` varchar(20) DEFAULT 'NA',
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `name`, `subject`, `email`, `phone`, `classroom_id`, `photo`) VALUES
('2025TCH002', 'ruchi singh', 'hindi math', 'r1@gmail.com', '1234567890', '1-A', '2025TCH002.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher') NOT NULL,
  `teacher_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `teacher_id`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', NULL),
(2, 'teacher', 'a426dcf72ba25d046591f81a5495eab7', 'teacher', NULL),
(6, 'r1@gmail.com', 'e807f1fcf82d132f9bb018ca6738a19f', 'teacher', '2025TCH002');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id_int`),
  ADD UNIQUE KEY `student_id` (`student_id`,`date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classrooms`
--
ALTER TABLE `classrooms`
  ADD PRIMARY KEY (`classroom_id`);

--
-- Indexes for table `exam_timetable`
--
ALTER TABLE `exam_timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `fees_invoices`
--
ALTER TABLE `fees_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `fees_payments`
--
ALTER TABLE `fees_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_no` (`admission_no`),
  ADD KEY `fk_student_classroom` (`classroom_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `classroom_id` (`classroom_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id_int` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exam_timetable`
--
ALTER TABLE `exam_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fees_payments`
--
ALTER TABLE `fees_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_classroom` FOREIGN KEY (`classroom_id`) REFERENCES `classrooms` (`classroom_id`) ON DELETE SET NULL;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
