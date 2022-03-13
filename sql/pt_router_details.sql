-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2022 at 03:23 PM
-- Server version: 10.4.22-MariaDB
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `persistent_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `pt_router_details`
--

CREATE TABLE `pt_router_details` (
  `id` int(11) NOT NULL,
  `sap_id` varchar(18) NOT NULL,
  `hostname` varchar(14) NOT NULL,
  `loopback` varchar(15) NOT NULL,
  `mac_address` varchar(17) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pt_router_details`
--

INSERT INTO `pt_router_details` (`id`, `sap_id`, `hostname`, `loopback`, `mac_address`, `status`, `created_at`, `created_by`, `updated_by`, `updated_at`) VALUES
(1, 'SAP-IN-MHPUN-1234A', 'INMHPUN123456B', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647112644, NULL, NULL, 1647112644),
(2, 'SAP-IN-MHAUR-1234A', 'INMHAUR123456C', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647112644, NULL, NULL, 1647112644),
(3, 'SAP-IN-MHNAG-1234A', 'INMHNAG123456D', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647112644, NULL, NULL, 1647112644),
(4, 'SAP-IN-MHKHO-1234A', 'INMHKHO123456E', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647112644, NULL, NULL, 1647112644),
(5, 'SAP-IN-GJAHM-1234A', 'INGJAHM123456F', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647112644, NULL, NULL, 1647112644),
(6, 'SAP-IN-MHMUM-1234A', 'INMHMUM123456A', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647168651, NULL, NULL, 1647168651),
(7, 'SAP-IN-MHMUM-1234D', 'INMHMUM123456W', '255.255.255.255', 'D8-9C-67-AE-5B-21', 1, 1647179277, NULL, NULL, 1647179277),
(8, 'SAP-IN-MHMUM-12342', 'INMHMUM1234564', '255.255.255.255', 'D8-9C-67-AE-5B-23', 1, 1647179317, NULL, NULL, 1647179317);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pt_router_details`
--
ALTER TABLE `pt_router_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pt_router_details`
--
ALTER TABLE `pt_router_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
