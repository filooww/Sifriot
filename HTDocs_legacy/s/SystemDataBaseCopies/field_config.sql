-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 03, 2025 at 07:24 AM
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
-- Table structure for table `field_config`
--

CREATE TABLE `field_config` (
  `own_table` int(1) NOT NULL DEFAULT '0',
  `f_ID` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_key` int(1) NOT NULL DEFAULT '0',
  `f_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_type` int(2) NOT NULL,
  `f_size` int(3) NOT NULL DEFAULT '0',
  `f_interval` int(1) NOT NULL DEFAULT '0',
  `f_blank` int(1) NOT NULL DEFAULT '0',
  `f_unique` int(1) NOT NULL DEFAULT '0',
  `f_s_mode` int(1) NOT NULL DEFAULT '0',
  `f_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_illegals` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_default` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_check` int(1) NOT NULL DEFAULT '0',
  `comm` int(1) NOT NULL DEFAULT '0',
  `f_filter_md` int(1) NOT NULL DEFAULT '1',
  `f_sort_sm` int(1) NOT NULL DEFAULT '0',
  `f_using` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_align` int(1) NOT NULL DEFAULT '0',
  `table_percent` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `screen_order` int(2) NOT NULL DEFAULT '0',
  `load_order` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `field_config`
--
ALTER TABLE `field_config`
  ADD UNIQUE KEY `field_config_key` (`own_table`,`f_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
