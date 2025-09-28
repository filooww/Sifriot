-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 28, 2024 at 10:32 AM
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
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id_publication` int(11) NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `file_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `file_issue_year` char(4) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `file_volume` char(5) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `file_number` char(7) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `file_page` char(9) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ord_num` int(4) NOT NULL,
  `file_name_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `file_size` char(11) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `file_source` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id_publication`, `file_name`, `file_description`, `file_issue_year`, `file_volume`, `file_number`, `file_page`, `ord_num`, `file_name_low`, `file_size`, `file_source`) VALUES
(2, 'CoronaBob.pdf', '', '0', '', '', '', 1, 'coronabob.pdf', '', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD UNIQUE KEY `file_ref` (`id_publication`,`file_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
