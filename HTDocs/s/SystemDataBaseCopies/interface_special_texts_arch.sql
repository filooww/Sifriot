-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 17, 2024 at 02:52 PM
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
-- Table structure for table `interface_special_texts`
--

CREATE TABLE `interface_special_texts` (
  `special_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `special_numbers` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `interface_special_texts`
--

INSERT INTO `interface_special_texts` (`special_type`, `special_numbers`) VALUES
('compare_mode', '-449,-69,-450'),
('field_align', '-460,-461,-462'),
('field_types', '384,467,386,387,388,389,390,391,392'),
('field_using', '0,154,155,374,-375,376'),
('group_types', '393,394,395'),
('sort_mode', '393,-63,-64'),
('table_types', '396,397,398,-399,401,374,615'),
('z_o', '76,140,141,189,190,207,208,211,212,309,550,551,552,622,625,319,320,321,322,323,324');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `interface_special_texts`
--
ALTER TABLE `interface_special_texts`
  ADD UNIQUE KEY `special_type` (`special_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
