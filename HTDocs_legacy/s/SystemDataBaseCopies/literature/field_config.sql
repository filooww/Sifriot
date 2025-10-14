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
-- Table structure for table `field_config`
--

CREATE TABLE `field_config` (
  `own_table` int(1) NOT NULL DEFAULT '0',
  `f_ID` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_key` int(1) NOT NULL DEFAULT '0',
  `f_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_type` int(2) NOT NULL,
  `f_size` int(3) DEFAULT '0',
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
  `table_percent` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '''''',
  `screen_order` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `field_config`
--

INSERT INTO `field_config` (`own_table`, `f_ID`, `f_key`, `f_name`, `f_type`, `f_size`, `f_interval`, `f_blank`, `f_unique`, `f_s_mode`, `f_table`, `f_illegals`, `f_default`, `f_check`, `comm`, `f_filter_md`, `f_sort_sm`, `f_using`, `f_align`, `table_percent`, `screen_order`) VALUES
(1, '_del_mark', 0, '_del_mark', 7, 0, 1, 0, 0, 0, '', '', '', 0, 0, 1, 0, '1,5,4', 2, '', 0),
(1, 'actuality', 0, 'Actuality', 0, 5, 0, 1, 0, 0, '', '', '', 1, 0, 0, 0, '1,5', 1, '', 11),
(1, 'add_char', 0, 'add_char', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 0, '', 0),
(1, 'add_int', 0, 'add_int', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 0, '', 0),
(1, 'id_author_set', 0, 'Authors', 1, 50, 0, 1, 0, 1, 'author_groups', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '12%', 2),
(1, 'id_issue_type', 0, 'Issue type', 1, 50, 0, 1, 0, 1, 'issue_types', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '8%', 8),
(1, 'id_magazine', 0, 'Magazine', 1, 50, 0, 1, 0, 1, 'magazines', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '6%', 9),
(1, 'id_part', 0, 'Series', 1, 50, 0, 1, 0, 1, 'part_sets', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '5%', 6),
(1, 'id_publication', 1, 'ID', 0, 13, 1, 1, 0, 0, '', '', '', 0, 1, 0, 0, '1,5', 2, '5%', 1),
(1, 'id_publishing', 0, 'Publishing', 1, 50, 0, 1, 0, 1, 'publishings', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '6%', 5),
(1, 'id_theme_set', 0, 'Themes', 1, 50, 0, 1, 0, 1, 'theme_sets', '', '', 0, 0, 1, 0, '2,3,1,4,5', 0, '10%', 4),
(1, 'issue_year', 0, 'Issue year', 3, 12, 1, 1, 0, 0, '', '', '', 0, 0, 0, 0, '2,1,4,5', 2, '8%', 7),
(1, 'title', 0, 'Title', 1, 50, 0, 0, 0, 1, '', '39 96', '', 0, 0, 1, 0, '1,4,5', 0, '28%', 3),
(1, 'title_low', 0, '', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 2, '', 0),
(1, 'upload_date', 0, 'Upload at', 2, 12, 1, 0, 0, 0, '', '', '', 1, 0, 0, 0, '1,5', 2, '8%', 10),
(2, 'file_description', 0, 'File description', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1,4', 0, '', 7),
(2, 'file_issue_year', 0, 'File issue year', 3, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '5%', 3),
(2, 'file_name', 0, 'File name', 5, 0, 0, 0, 1, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '50%', 2),
(2, 'file_name_low', 0, '', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 2, '', 0),
(2, 'file_number', 0, 'File number', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '3%', 5),
(2, 'file_page', 0, 'File page', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '3%', 6),
(2, 'file_size', 0, 'File size', 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1', 0, '', 0),
(2, 'file_source', 0, 'File source', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '', 0, '', 0),
(2, 'file_volume', 0, 'File volume', 1, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '3%', 4),
(2, 'id_publication', 0, 'id_publication', 8, 0, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 1, '', 0),
(2, 'ord_num', 1, 'Order number', 0, 0, 0, 0, 1, 0, '', '', '', 0, 0, 0, 0, '1,2,4', 0, '1%', 1);

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
