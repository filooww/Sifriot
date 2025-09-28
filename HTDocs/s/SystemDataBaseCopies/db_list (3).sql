-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 16, 2024 at 04:25 AM
-- Server version: 5.6.19-log
-- PHP Version: 5.5.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `db_list`
--

CREATE TABLE `db_list` (
  `db_id` int(2) NOT NULL,
  `db_name` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `db_coding` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `db_comment` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `db_list`
--

INSERT INTO `db_list` (`db_id`, `db_name`, `db_coding`, `db_comment`) VALUES
(0, 'db_manager', 'utf8', 'system DB'),
(1, 'literature', 'utf8', 'Literature'),
(2, 'phys_math_contents', 'utf8', 'Phys & Math');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `db_list`
--
ALTER TABLE `db_list`
  ADD PRIMARY KEY (`db_id`),
  ADD UNIQUE KEY `db_id` (`db_id`),
  ADD UNIQUE KEY `db_name` (`db_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
