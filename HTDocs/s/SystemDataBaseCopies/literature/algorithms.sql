-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 18, 2024 at 11:05 AM
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
  `alg_field` varchar(31) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `alg_offset` int(2) DEFAULT NULL,
  `del_from_source` int(1) NOT NULL,
  `beg_delimiter` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `beg_number` int(3) NOT NULL DEFAULT '0',
  `beg_inc` int(1) NOT NULL,
  `beg_scr` int(1) NOT NULL,
  `inner_delimiter` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `end_delimiter` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `end_number` int(3) NOT NULL DEFAULT '0',
  `end_inc` int(1) NOT NULL,
  `end_scr` int(11) NOT NULL,
  `del_symbols` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `ins_symbols` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `field_only` int(1) NOT NULL,
  `reg_expression` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `reg_scr` int(1) NOT NULL,
  `alg_remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


test CreateMandatoryTable 00 ***algorithms===Array 
( 
 [0] => 
CREATE TABLE algorithms (
id_algorithm int(11) NOT NULL, 
alg_field varchar(31) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, alg_offset int(2) DEFAULT NULL, del_from_source int(1) NOT NULL, beg_delimiter varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, beg_number int(3) NOT NULL DEFAULT '0', beg_inc int(1) NOT NULL, beg_scr int(1) NOT NULL, inner_delimiter varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, end_delimiter varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, end_number int(3) NOT NULL DEFAULT '0', end_inc int(1) NOT NULL, end_scr int(11) NOT NULL, del_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, ins_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, field_only int(1) NOT NULL, reg_expression varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL, reg_scr int(1) NOT NULL, alg_remarks varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8; [1] => ALTER TABLE algorithms ADD PRIMARY KEY (id_algorithm), ADD UNIQUE KEY id_algorithm (id_algorithm) [2] => [3] => )
test CreateMandatoryTable 00 ***db_configs===Array ( [0] => CREATE TABLE db_configs (config_name varchar(31) CHARACTER SET utf8 COLLATE utf8_bin NULL, config_value varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, config_type int(1) NOT NULL DEFAULT '0', config_description varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8; [1] => ALTER TABLE db_configs ADD PRIMARY KEY (config_name), ADD UNIQUE KEY config_name (config_name) [2] => [3] => )
test CreateMandatoryTable 00 ***field_config===Array ( [0] => CREATE TABLE field_config (own_table int(1) NOT NULL DEFAULT '0', f_ID varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_key int(1) NOT NULL DEFAULT '0', f_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_type int(2) NOT NULL, f_size int(3) DEFAULT '0', f_interval int(1) NOT NULL DEFAULT '0', f_blank int(1) NOT NULL DEFAULT '0', f_unique int(1) NOT NULL DEFAULT '0', f_s_mode int(1) NOT NULL DEFAULT '0', f_table varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_illegals varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_default varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_check int(1) NOT NULL DEFAULT '0', comm int(1) NOT NULL DEFAULT '0', f_filter_md int(1) NOT NULL DEFAULT '1', f_sort_sm int(1) NOT NULL DEFAULT '0', f_using varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, f_align int(1) NOT NULL DEFAULT '0', table_percent varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '', screen_order int(2) NOT NULL DEFAULT '0') ENGINE=InnoDB DEFAULT CHARSET=utf8; [1] => ALTER TABLE field_config ADD UNIQUE KEY field_config_key (own_table,f_ID) [2] => [3] => )
test CreateMandatoryTable 00 ***table_definitions===Array ( [0] => CREATE TABLE table_definitions (table_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, use_type int(2) NOT NULL DEFAULT '0', illegal_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, group_catalog_type int(2) NOT NULL DEFAULT '0', separators varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, max_level int(2) NOT NULL DEFAULT '0', second_catalog_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, table_title varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL, low_fields varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8; [1] => ALTER TABLE table_definitions ADD PRIMARY KEY (table_name), ADD UNIQUE KEY table_name (table_name) [2] => [3] => INSERT INTO table_definitions VALUES ('algorithms',6,'','','',0,'',0,'','Algorithms',''),('db_configs',4,'','','',0,'',0,'','Data base congifurations',''),('field_config',4,'','','',0,'',0,'','Field configurations',''),('table_definitions',4,'','','',0,'',0,'','Table definitions','') )
--
-- Indexes for dumped tables
--

--
-- Indexes for table `algorithms`
--
ALTER TABLE `algorithms`
  ADD PRIMARY KEY (`id_algorithm`),
  ADD UNIQUE KEY `id_algorithm` (`id_algorithm`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
