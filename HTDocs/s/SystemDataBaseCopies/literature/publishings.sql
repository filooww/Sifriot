-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:41 AM
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
-- Table structure for table `publishings`
--

CREATE TABLE `publishings` (
  `id_publishing` int(11) NOT NULL,
  `publishing` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `publishing_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `publishings`
--

INSERT INTO `publishings` (`id_publishing`, `publishing`, `publishing_low`) VALUES
(2, 'JJJnJJJggggg', 'jjjnjjjggggg'),
(3, 'bnnnna', 'bnnnna'),
(4, 'bbbnbbb', 'bbbnbbb'),
(5, 'AAAnAAAasdf', 'aaanaaaasdf'),
(6, 'BBBnBBB--', 'bbbnbbb--'),
(16, 'qqqry', 'qqqry'),
(20, 'rrrnqwert', 'rrrnqwert'),
(29, 'yyyyynyy', 'yyyyynyy'),
(31, '11111lihgb', '11111lihgb'),
(32, '22222', '22222'),
(33, '33`333', '33`333'),
(46, 'jjjnjjjjjj', 'jjjnjjjjjj'),
(47, 'Сабашников', 'сабашников'),
(48, 'new new', 'new new');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `publishings`
--
ALTER TABLE `publishings`
  ADD PRIMARY KEY (`id_publishing`),
  ADD UNIQUE KEY `publishing` (`publishing`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `publishings`
--
ALTER TABLE `publishings`
  MODIFY `id_publishing` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
