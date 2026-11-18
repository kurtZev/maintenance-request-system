-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2026 at 06:01 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `maintenance_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `request_id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `requester_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` enum('Electrical','Plumbing','Air Conditioning','Carpentry','IT/Network','Cleaning','Other') NOT NULL DEFAULT 'Other',
  `location` varchar(150) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('Low','Medium','High','Urgent') NOT NULL DEFAULT 'Medium',
  `status` enum('Pending','In Progress','Completed','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  `admin_remarks` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `handled_by` int(10) UNSIGNED DEFAULT NULL,
  `date_submitted` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date_completed` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_requests`
--

INSERT INTO `maintenance_requests` (`request_id`, `reference_no`, `requester_id`, `title`, `category`, `location`, `description`, `priority`, `status`, `admin_remarks`, `photo`, `handled_by`, `date_submitted`, `updated_at`, `date_completed`) VALUES
(1, 'MRS-2026-0001', 2, 'Aircon not cooling', 'Air Conditioning', 'CCS Building, Room 204', 'The aircon unit runs but blows warm air since Monday.', 'High', 'Completed', 'Cleaned filter and recharged refrigerant.', NULL, 1, '2026-11-02 08:15:00', '2026-11-15 15:57:14', '2026-11-04 15:30:00'),
(2, 'MRS-2026-0002', 3, 'Leaking faucet', 'Plumbing', 'Admin Building, 1st Floor Restroom', 'The second faucet drips continuously even when fully closed.', 'Medium', 'In Progress', 'Replacement part ordered.', NULL, 1, '2026-11-05 10:40:00', '2026-11-15 15:57:14', NULL),
(3, 'MRS-2026-0003', 2, 'Flickering lights', 'Electrical', 'CCS Building, Computer Lab 1', 'Two fluorescent lights at the back row flicker during class.', 'Medium', 'Pending', NULL, NULL, NULL, '2026-11-10 13:05:00', '2026-11-15 16:35:00', NULL),
(4, 'MRS-2026-0004', 3, 'No internet connection', 'IT/Network', 'Registrar\'s Office', 'All four computers lost network access this morning.', 'Urgent', 'In Progress', 'Checking the network switch.', NULL, 1, '2026-11-12 08:02:00', '2026-11-15 15:57:14', NULL),
(5, 'MRS-2026-0005', 2, 'Repaint classroom wall', 'Other', 'CCS Building, Room 101', 'Requesting a repaint of the back wall.', 'Low', 'Rejected', 'Scheduled under the semestral repainting instead.', NULL, 1, '2026-11-12 16:20:00', '2026-11-15 15:57:14', NULL),
(27, 'MRS-2026-0027', 13, 'damage in  computer', 'IT/Network', 'LAB3', 'something is wrong', 'High', 'Pending', NULL, '39569f83c03762bfd9f415fb88e2d6b9.jpg', NULL, '2026-11-15 18:56:41', '2026-11-15 18:56:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','requester') NOT NULL DEFAULT 'requester',
  `contact_number` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`, `contact_number`, `department`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Administrator', 'admin@mrs.test', '$2y$12$4P5METpSFkcV5NJ5B1/2sujc/us2b3x5lhKafDWYKSMEkb3Azo/by', 'admin', '09171234567', 'Facilities Office', 'active', '2026-11-15 15:57:14', '2026-11-15 15:57:14'),
(2, 'Maria Santos', 'maria.santos@mrs.test', '$2y$12$DnlOZPNCeVBThZtUVgjMO.CPDSJtvp33SPpKL2V1X9Kq/LhMV8ReW', 'requester', '09181234567', 'College of Computer Studies', 'active', '2026-11-15 15:57:14', '2026-11-15 15:57:14'),
(3, 'Juan Dela Cruz', 'juan.delacruz@mrs.test', '$2y$12$DnlOZPNCeVBThZtUVgjMO.CPDSJtvp33SPpKL2V1X9Kq/LhMV8ReW', 'requester', '09191234567', 'Registrar\'s Office', 'active', '2026-11-15 15:57:14', '2026-11-15 15:57:14'),
(4, 'Second Administrator', 'admin2@mrs.com', '$2y$12$o/ngW4yT.QDkedhSwMsTgeMMyrxgiCq8IkEodRCo4JpUEc48RgQW6', 'admin', NULL, NULL, 'active', '2026-11-15 16:10:10', '2026-11-15 16:10:10'),
(13, 'Kurt Adriann Bendol', 'kurtbendol07@gmail.com', '$2y$12$UvEIDM6H3XSi5Mxs0lIipeWSqcT0LyNbKdTxfgugQtc3j1gjJRxSy', 'requester', '09465075815', 'CICI', 'active', '2026-11-15 18:54:07', '2026-11-15 18:54:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD UNIQUE KEY `uq_requests_reference_no` (`reference_no`),
  ADD KEY `idx_requests_requester` (`requester_id`),
  ADD KEY `idx_requests_handled_by` (`handled_by`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_priority` (`priority`),
  ADD KEY `idx_requests_category` (`category`),
  ADD KEY `idx_requests_date_submitted` (`date_submitted`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`),
  ADD KEY `idx_users_full_name` (`full_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `request_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD CONSTRAINT `fk_requests_handled_by` FOREIGN KEY (`handled_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_requests_requester` FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
