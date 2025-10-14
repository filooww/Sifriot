-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:40 AM
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
-- Database: `literature`
--

-- --------------------------------------------------------

--
-- Table structure for table `part_sets`
--

CREATE TABLE `part_sets` (
  `id_part_set` int(11) NOT NULL,
  `part_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `part_sets`
--

INSERT INTO `part_sets` (`id_part_set`, `part_set`) VALUES
(2, '2'),
(3, '2,3'),
(4, '2,3,8'),
(5, '2,3,9'),
(6, '2,4'),
(7, '2,4,10'),
(8, '2,4,11'),
(9, '2,4,12'),
(111, '2,4,19'),
(112, '2,4,20'),
(113, '2,4,21'),
(10, '2,5'),
(11, '2,5,13'),
(12, '2,5,14'),
(13, '2,5,15'),
(115, '37,4'),
(14, '6'),
(15, '6,3'),
(16, '6,3,16'),
(17, '6,3,17'),
(18, '6,3,18'),
(114, '6,4,22'),
(24, '6,5'),
(25, '6,5,23'),
(26, '6,5,24'),
(27, '6,5,25'),
(28, '6,5,26'),
(29, '7'),
(30, '7,3'),
(31, '7,3,27'),
(32, '7,3,28'),
(33, '7,3,29'),
(34, '7,4'),
(35, '7,4,30'),
(36, '7,4,31'),
(40, '7,5'),
(38, '7,5,33'),
(39, '7,5,34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `part_sets`
--
ALTER TABLE `part_sets`
  ADD PRIMARY KEY (`id_part_set`),
  ADD UNIQUE KEY `part_set` (`part_set`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `part_sets`
--
ALTER TABLE `part_sets`
  MODIFY `id_part_set` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
