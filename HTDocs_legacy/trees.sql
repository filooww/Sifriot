-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 21, 2025 at 04:47 AM
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
-- Database: `trees`
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
-- Dumping data for table `db_configs`
--

INSERT INTO `db_configs` (`config_name`, `config_value`, `config_type`, `config_description`) VALUES
('f000', '9kjh', 1, ''),
('gg00', '25', 1, '');

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
  `table_percent` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `screen_order` int(2) NOT NULL DEFAULT '0',
  `load_order` int(2) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `field_config`
--

INSERT INTO `field_config` (`own_table`, `f_ID`, `f_key`, `f_name`, `f_type`, `f_size`, `f_interval`, `f_blank`, `f_unique`, `f_s_mode`, `f_table`, `f_illegals`, `f_default`, `f_check`, `comm`, `f_filter_md`, `f_sort_sm`, `f_using`, `f_align`, `table_percent`, `screen_order`, `load_order`) VALUES
(1, 'f19', 0, 'file19', 1, 10, 0, 0, 0, 0, '', '', '', 0, 0, 1, 0, '', 0, '', 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `id_part` int(11) NOT NULL,
  `part` tinytext CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `part_tree`
--

CREATE TABLE `part_tree` (
  `id_part_node` int(11) NOT NULL,
  `id_parent_node` int(11) NOT NULL,
  `id_part` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `table_definitions`
--

CREATE TABLE `table_definitions` (
  `table_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `use_type` int(2) NOT NULL DEFAULT '0',
  `illegal_symbols` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `group_catalog_type` int(2) NOT NULL DEFAULT '0',
  `separators` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `max_level` int(2) NOT NULL DEFAULT '0',
  `second_catalog_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `table_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table_definitions`
--

INSERT INTO `table_definitions` (`table_name`, `use_type`, `illegal_symbols`, `group_catalog_type`, `separators`, `max_level`, `second_catalog_name`, `table_title`) VALUES
('00ok', 1, '', 0, '', 0, '', ''),
('db_configs', 4, '', 0, '', 0, '', 'Data base congifurations'),
('field_config', 4, '', 0, '', 0, '', 'Field configurations'),
('gr1', 3, '', 0, '', 0, 'sec1', ''),
('gr2', 3, '', 0, '', 0, 'sec1', ''),
('gr3', 5, '', 0, '', 0, 'sec2', ''),
('gr4', 3, '', 0, '', 0, 'sec2', ''),
('gr5', 3, '', 0, '', 0, 'sec2', ''),
('sec1', 3, '', 0, '', 0, '', ''),
('sec2', 3, '', 0, '', 0, '', ''),
('table_definitions', 4, '', 0, '', 0, '', 'Table definitions');

-- --------------------------------------------------------

--
-- Table structure for table `userlist`
--

CREATE TABLE `userlist` (
  `id_user` int(11) NOT NULL,
  `name` tinytext CHARACTER SET utf8 COLLATE utf8_bin,
  `pass` tinytext CHARACTER SET utf8 COLLATE utf8_bin
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `userlist`
--

INSERT INTO `userlist` (`id_user`, `name`, `pass`) VALUES
(1, 'bob', '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `db_configs`
--
ALTER TABLE `db_configs`
  ADD PRIMARY KEY (`config_name`),
  ADD UNIQUE KEY `config_name` (`config_name`);

--
-- Indexes for table `field_config`
--
ALTER TABLE `field_config`
  ADD UNIQUE KEY `field_config_key` (`own_table`,`f_ID`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id_part`);

--
-- Indexes for table `part_tree`
--
ALTER TABLE `part_tree`
  ADD PRIMARY KEY (`id_part_node`);

--
-- Indexes for table `table_definitions`
--
ALTER TABLE `table_definitions`
  ADD PRIMARY KEY (`table_name`),
  ADD UNIQUE KEY `table_name` (`table_name`);

--
-- Indexes for table `userlist`
--
ALTER TABLE `userlist`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id_part` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `part_tree`
--
ALTER TABLE `part_tree`
  MODIFY `id_part_node` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `userlist`
--
ALTER TABLE `userlist`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
