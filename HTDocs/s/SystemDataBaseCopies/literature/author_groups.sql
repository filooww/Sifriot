-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:35 AM
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
-- Table structure for table `author_groups`
--

CREATE TABLE `author_groups` (
  `id_author_group` int(11) NOT NULL,
  `author_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `author_groups`
--

INSERT INTO `author_groups` (`id_author_group`, `author_set`) VALUES
(20, '10'),
(15, '10,11'),
(16, '11'),
(17, '12'),
(24, '13'),
(36, '14'),
(22, '14,13'),
(29, '16'),
(37, '17'),
(30, '17,14'),
(38, '18'),
(39, '19'),
(32, '21,6'),
(41, '22'),
(7, '22,23,6'),
(5, '22,4,9'),
(42, '23'),
(8, '23,7'),
(18, '4'),
(6, '5'),
(31, '5,6'),
(33, '6'),
(4, '6,4'),
(34, '7'),
(9, '7,4,8'),
(35, '8'),
(23, '8,14'),
(21, '8,4,7'),
(19, '9'),
(14, '9,11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `author_groups`
--
ALTER TABLE `author_groups`
  ADD PRIMARY KEY (`id_author_group`),
  ADD UNIQUE KEY `author_set` (`author_set`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `author_groups`
--
ALTER TABLE `author_groups`
  MODIFY `id_author_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
