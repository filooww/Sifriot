-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 23, 2025 at 07:20 AM
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
-- Table structure for table `db_configs`
--

CREATE TABLE `db_configs` (
  `config_name` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `config_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `config_type` int(1) NOT NULL DEFAULT '0',
  `config_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `db_configs`
--
ALTER TABLE `db_configs`
  ADD PRIMARY KEY (`config_name`),
  ADD UNIQUE KEY `config_name` (`config_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
