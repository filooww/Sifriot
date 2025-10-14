-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:42 AM
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
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id_theme` int(11) NOT NULL,
  `theme` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `theme_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id_theme`, `theme`, `theme_low`) VALUES
(41, 'Барри', 'theme'),
(42, 'aИстория', 'theme'),
(43, 'Тяготение', 'theme'),
(44, 'rrrrrrr', 'theme'),
(45, 'яXIX век', 'theme'),
(46, 'Literature', 'theme'),
(47, 'тяготение--', 'theme'),
(48, 'Ускорение', 'theme'),
(49, 'ускорение--', 'theme'),
(50, 'яяяя', 'theme'),
(51, 'new test', 'theme'),
(52, 'Литература', 'theme'),
(53, 'Русская литература', 'theme'),
(54, 'Base', 'theme'),
(55, 'nnnnnnnnnnnnnnn', 'theme');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id_theme`),
  ADD UNIQUE KEY `theme` (`theme`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id_theme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
