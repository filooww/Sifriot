-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 06, 2024 at 03:44 PM
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
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `id_publication` int(11) NOT NULL,
  `id_publishing` int(11) DEFAULT '0',
  `id_part` int(11) DEFAULT '0',
  `issue_year` char(4) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `id_issue_type` int(4) DEFAULT '0',
  `id_magazine` int(11) DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `upload_date` date NOT NULL DEFAULT '0000-00-00',
  `actuality` tinyint(1) DEFAULT '0',
  `id_theme_set` int(11) DEFAULT '0',
  `id_author_set` int(11) DEFAULT '0',
  `title_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `_del_mark` int(1) NOT NULL DEFAULT '0',
  `add_int` int(3) NOT NULL DEFAULT '0',
  `add_char` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `publication`
--

INSERT INTO `publication` (`id_publication`, `id_publishing`, `id_part`, `issue_year`, `id_issue_type`, `id_magazine`, `title`, `upload_date`, `actuality`, `id_theme_set`, `id_author_set`, `title_low`, `_del_mark`, `add_int`, `add_char`) VALUES
(1, 1, 0, '1905', 4, 0, 'Prim1', '2000-05-07', 21, 5, 4, 'prim1', 1, 0, ''),
(2, 1, 0, '1906', 2, 0, 'Prim2', '2000-05-07', 21, 5, 15, 'prim2', 0, 0, ''),
(3, 1, 0, '1907', 3, 0, 'Prim3', '2007-05-07', 21, 5, 2, 'prim3', 0, 0, ''),
(4, 1, 0, '1908', 3, 4, 'Prim4', '2007-05-07', 21, 5, 6, 'prim4', 0, 0, ''),
(5, 4, 0, '1909', 0, 13, 'Prim5', '2007-05-07', 21, 5, 6, 'prim5', 0, 0, ''),
(6, 29, 0, '1910', 0, 15, 'Prim6', '2007-05-07', 21, 5, 6, 'prim6', 0, 0, ''),
(7, 0, 25, '1911', 0, 16, 'Prim7', '2007-05-07', 21, 5, 6, 'prim7', 0, 0, ''),
(8, 32, 33, '1912', 0, 4, 'Prim8', '2007-05-07', 21, 31, 6, 'prim8', 0, 0, ''),
(9, 1, 0, '1913', 0, 4, 'Prim9', '2007-05-07', 21, 38, 6, 'prim9', 0, 0, ''),
(10, 1, 0, '1914', 0, 4, 'Prim10', '2007-05-07', 21, 37, 32, 'prim10', 0, 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id_publication`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `id_publication` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
