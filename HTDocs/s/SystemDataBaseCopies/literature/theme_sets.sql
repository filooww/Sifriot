-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:43 AM
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
-- Table structure for table `theme_sets`
--

CREATE TABLE `theme_sets` (
  `id_theme_set` int(11) NOT NULL,
  `theme_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `theme_sets`
--

INSERT INTO `theme_sets` (`id_theme_set`, `theme_set`) VALUES
(41, '41'),
(43, '43'),
(44, '44'),
(45, '45'),
(34, '45,42'),
(46, '46,44'),
(52, '46,55,44'),
(47, '47'),
(32, '48'),
(48, '49'),
(49, '50'),
(38, '51'),
(50, '52'),
(51, '53'),
(31, '54,41,44,43'),
(35, '54,43'),
(37, '54,44'),
(33, '54,49,46'),
(39, '54,51,41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `theme_sets`
--
ALTER TABLE `theme_sets`
  ADD PRIMARY KEY (`id_theme_set`),
  ADD UNIQUE KEY `theme_set` (`theme_set`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `theme_sets`
--
ALTER TABLE `theme_sets`
  MODIFY `id_theme_set` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
