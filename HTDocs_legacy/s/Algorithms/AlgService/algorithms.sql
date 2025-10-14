-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 05, 2020 at 01:36 PM
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
-- Table structure for table `algorithms`
--

CREATE TABLE `algorithms` (
  `id_algorithm` int(11) NOT NULL,
  `alg_field` tinytext,
  `alg_offset` int(2) DEFAULT NULL,
  `del_from_source` char(1) NOT NULL,
  `beg_delimiter` tinytext,
  `beg_number` int(3) NOT NULL DEFAULT '0',
  `beg_inc` char(1) NOT NULL,
  `beg_scr` char(1) NOT NULL,
  `inner_delimiter` tinytext,
  `end_delimiter` tinytext,
  `end_number` int(3) NOT NULL DEFAULT '0',
  `end_inc` char(1) NOT NULL,
  `end_scr` char(1) NOT NULL,
  `del_symbols` tinytext,
  `ins_symbols` tinytext,
  `field_only` char(1) NOT NULL,
  `reg_expression` tinytext,
  `reg_scr` char(1) NOT NULL,
  `alg_remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `algorithms`
--

INSERT INTO `algorithms` (`id_algorithm`, `alg_field`, `alg_offset`, `del_from_source`, `beg_limit_set`, `beg_number`, `beg_inc`, `beg_scr`, `inner_limit_set`, `end_limit_set`, `end_number`, `end_inc`, `end_scr`, `del_symbols`, `ins_symbols`, `field_only`, `reg_expression`, `reg_scr`, `alg_remarks`) VALUES
(1, 'Series', 1, 0, 'Serii', 0, 0, 0, '', '   -- ', 0, 0, 0, '', '', 0, '', 0, ''),
(2, 'Authors', 0, 1, '', 0, 0, 0, ', ', '. | - | .|-|.| -| — ', 0, 0, 1, '', '', 0, '', 0, ''),
(3, 'Title', 0, 0, '', 0, 0, 0, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(4, 'Series', 4, 0, 'Serii', 0, 0, 0, '', '   -- ', 0, 0, 0, '', '', 0, '', 0, ''),
(5, 'Series', 2, 0, '', 0, 0, 0, '', ' [', 0, 0, 0, '', '', 0, '', 0, ''),
(6, 'Series', 2, 0, '[', 0, 0, 0, '', ']', 0, 0, 0, '', '', 0, '', 0, ''),
(7, 'Title', 1, 0, '. ', 2, 0, 0, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(8, 'Authors', 1, 1, '', 0, 0, 0, '. ', '. ', 2, 0, 1, '', '', 0, '', 0, ''),
(9, 'Series', 3, 0, 'Serii', 0, 0, 0, '', '   -- ', 0, 0, 0, '', '', 0, '', 0, ''),
(10, 'Series', 1, 0, '', 0, 0, 0, '', ' [|[', 0, 0, 0, '', '', 0, '', 0, ''),
(11, 'Series', 1, 0, '[|«', 0, 0, 0, '', ']|»', 0, 0, 0, '', '', 0, '', 0, ''),
(12, 'Authors', 0, 1, '', 0, 0, 0, '', '.', 0, 0, 1, '', '', 0, '', 0, ''),
(13, 'Series', 4, 0, 'Serii', 0, 0, 0, '', '   -- ', 0, 0, 0, '', '', 0, '', 0, ''),
(14, 'Series', 2, 0, '', 0, 0, 0, '', ' [', 0, 0, 0, '', '', 0, '', 0, ''),
(15, 'Title', 1, 1, '. ', 2, 0, 0, '', '', 0, 0, 1, '', '', 0, '', 0, ''),
(16, 'Volume', 0, 0, '. Книга ', 0, 0, 0, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(17, 'Authors', 1, 1, '', 0, 0, 0, '', '. ', 0, 0, 1, '', '', 0, '', 0, ''),
(19, 'Title', 0, 0, ' ', 0, 0, 1, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(20, 'Series', 2, 0, '«', 0, 0, 1, '', '»', 0, 0, 0, '', '', 0, '', 0, ''),
(21, 'Themes', 1, 0, '', 0, 0, 0, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(22, 'Publishing', 1, 0, '', 0, 0, 0, '', '', 0, 0, 0, '', '', 0, '', 0, ''),
(23, 'Title', 0, 0, '', 0, 0, 0, '', '', 0, 0, 0, '_|- |text', ' ||', 0, '', 0, ''),
(24, 'IssueYear', 0, 1, '', 0, 0, 0, '', '', 0, 0, 0, '', '', 0, '/\\d{4}/', 1, ''),
(25, 'Authors', 0, 0, '', 0, 0, 0, '', ' - |_', 0, 0, 0, '', '', 0, '', 0, ''),
(26, 'Volume', 0, 1, 'tom', 0, 0, 1, '', '_', 0, 0, 1, '_', '', 1, '', 0, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `algorithms`
--
ALTER TABLE `algorithms`
  ADD PRIMARY KEY (`id_algorithm`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `algorithms`
--
ALTER TABLE `algorithms`
  MODIFY `id_algorithm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
