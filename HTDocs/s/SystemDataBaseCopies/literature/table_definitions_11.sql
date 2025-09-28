-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 17, 2024 at 11:20 AM
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
  `table_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `low_fields` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `table_definitions`
--

INSERT INTO `table_definitions` (`table_name`, `use_type`, `illegal_symbols`, `group_catalog_type`, `separators`, `max_level`, `second_catalog_name`, `table_title`, `low_fields`) VALUES
('algorithms', 1, '', 0, '', 0, '', 'Algorithms', ''),
('author_groups', 3, '', 2, ', ', 3, 'authors', 'Группы авторов', ''),
('authors', 3, ', ', 0, '', 0, '', 'Авторы', 'author'),
('collapse_ids', 4, '', 0, '', 0, '', 'collapse_ids', ''),
('db_configs', 4, '', 0, '', 0, '', 'db_configs', ''),
('field_config', 4, '', 0, '', 0, '', 'field_config', ''),
('files', 2, '', 0, '', 0, '', 'files', 'file_name'),
('issue_types', 3, '', 0, '', 0, '', 'Типы изданий', 'issue_type'),
('magazines', 3, '', 0, '', 0, '', 'Журналы', 'magazine'),
('part_sets', 3, '', 1, ' >> ', 3, 'parts', 'Серии и подсерии', ''),
('parts', 3, ' >> ', 0, '', 0, '', 'Серии', 'part'),
('primary_files', -5, '', 0, '', 0, '', 'primary_files', 'path_and_file'),
('publication', 1, '', 0, '', 0, '', 'Publication', 'title'),
('publishings', 3, '', 0, '', 0, '', 'Издательства', 'publishing'),
('table_definitions', 4, '', 0, '', 0, '', 'table_definitions', ''),
('theme_sets', 3, '', 0, ' >> ', 4, 'themes', 'Наборы тем', ''),
('themes', 3, ' >> ', 0, '', 0, '', 'Темы', 'theme'),
('user_settings', 3, '', 0, ',', 0, 'parts', 'user_settings', ''),
('userlist', 0, '', 0, '', 0, '', 'userlist', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table_definitions`
--
ALTER TABLE `table_definitions`
  ADD PRIMARY KEY (`table_name`),
  ADD UNIQUE KEY `table_name` (`table_name`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
