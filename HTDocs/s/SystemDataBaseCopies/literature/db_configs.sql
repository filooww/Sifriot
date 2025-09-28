-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 06, 2024 at 03:41 PM
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
  `config_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `config_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `config_type` int(1) NOT NULL,
  `config_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `db_configs`
--

INSERT INTO `db_configs` (`config_name`, `config_value`, `config_type`, `config_description`) VALUES
('date_delimiter', '.', 0, 'Date part separator'),
('dest_dir', 'D:WebProgHTDocsLiterFS', 0, 'Address and directory of file storage'),
('file_list_title', 'Item file list', 0, 'Item file list title'),
('form_title', 'Item form', 0, 'Item form title'),
('hide_list', 'Y', 0, 'Hide the list of literature when configuring this list'),
('image_dir', '/images', 0, 'Directory of images'),
('init_filter_mode', '2', 1, 'Width of catalog value'),
('init_search_mode', '2', 1, 'Width of catalog value'),
('list_title', 'Item list', 0, 'Main list title'),
('match_case', 'N', 0, 'Match case in FILTER, SEARCH, COMPARISSON'),
('number_warning', '30', 0, 'Number of values in warning rows'),
('portion_catalog', '9', 0, 'Number of records reading from the catalog at one time'),
('portion_item', '7', 0, 'Number of records reading from the publication table at one time'),
('screen_saver', 'LIBRARY.jpg', 0, 'Site screen saver'),
('screen_saver_height', '30', 0, 'Height of site screen saver'),
('start_year', '2019', 0, 'Initial issue year of publication'),
('time_zone', 'Israel', 0, 'Name of time zone'),
('try_limit', '3', 0, 'Maximum number of login attempts'),
('upload_log', 'D:WebProgHTDocsLiterSiteServiceUploadLog.txt', 0, 'Upload log name'),
('w_01', '200', 0, 'Size of fields SOURCE FILES and DESTINATION'),
('w_02', '3%', 0, 'Width of placeholder for button USE inside table for upload settings'),
('w_03', '9%', 0, 'Width of placeholder for parameter name inside table for upload settings'),
('w_04', '1%', 0, 'Widht of placeholder for button CALL CATALOG inside table for upload settings'),
('w_05', '5%', 0, 'Width of placeholder for catalog code'),
('w_06', '50px', 0, 'Width of catalog code'),
('w_07', '47', 0, 'Size of filter and search criteria'),
('w_08', '25', 0, 'Width of catalog value'),
('w_09', '64%', 0, 'Width of list settings pad');

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
