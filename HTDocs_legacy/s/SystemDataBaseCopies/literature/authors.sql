-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:31 AM
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
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `id_author` int(11) NOT NULL,
  `author` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `author_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`id_author`, `author`, `author_low`) VALUES
(-2, 'aaa', 'aaa'),
(0, 'ooo', ''),
(4, 'АДАМС', 'адамс'),
(5, 'BAaa', 'baaa'),
(6, 'RRRRjhgkjdh', 'rrrrjhgkjdh'),
(7, 'Кокотов', 'кокотов'),
(8, 'Kokotov', 'kokotov'),
(9, 'Шaa', 'шaa'),
(10, 'Шкк', 'шкк'),
(11, 'Яaa', 'яaa'),
(12, 'Яяя', 'яяя'),
(13, 'старый', 'старый'),
(14, 'новый', 'новый'),
(16, 'Толстой', 'толстой'),
(17, 'Адамс', 'адамс'),
(18, 'De, lete', 'de, lete'),
(19, 'Яяя 1', 'яяя 1'),
(22, 'AAaa', 'aaaa'),
(23, 'AAbb', 'aabb');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id_author`),
  ADD UNIQUE KEY `author` (`author`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `id_author` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
