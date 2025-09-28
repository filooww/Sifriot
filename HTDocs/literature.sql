-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 21, 2025 at 04:45 AM
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
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `id_author` int(11) NOT NULL,
  `author` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `author_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`id_author`, `author`, `author_low`) VALUES
(-2, 'aaa', 'aaa'),
(0, 'ooo', ''),
(4, 'АДАМС', 'адамс'),
(5, 'BAaa', 'baaa'),
(6, 'RRRRjhgkjdh', 'rrrrjhgkjdh'),
(7, 'Кокотов', 'кокотов'),
(8, 'Kokotov', 'kokotov'),
(9, 'Шaa', 'шaa'),
(10, 'Шкк', 'шкк'),
(11, 'Яaa', 'яaa'),
(12, 'Яяя', 'яяя'),
(13, 'старый', 'старый'),
(14, 'новый', 'новый'),
(16, 'Толстой', 'толстой'),
(17, 'Адамс', 'адамс'),
(18, 'De, lete', 'de, lete'),
(19, 'Яяя 1', 'яяя 1'),
(22, 'AAaa', 'aaaa'),
(23, 'AAbb', 'aabb');

-- --------------------------------------------------------

--
-- Table structure for table `author_groups`
--

CREATE TABLE `author_groups` (
  `id_author_group` int(11) NOT NULL,
  `author_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `author_groups`
--

INSERT INTO `author_groups` (`id_author_group`, `author_set`) VALUES
(20, '10'),
(15, '10,11'),
(16, '11'),
(17, '12'),
(24, '13'),
(36, '14'),
(22, '14,13'),
(29, '16'),
(37, '17'),
(30, '17,14'),
(38, '18'),
(39, '19'),
(32, '21,6'),
(41, '22'),
(7, '22,23,6'),
(5, '22,4,9'),
(42, '23'),
(8, '23,7'),
(18, '4'),
(6, '5'),
(31, '5,6'),
(33, '6'),
(4, '6,4'),
(34, '7'),
(9, '7,4,8'),
(35, '8'),
(23, '8,14'),
(21, '8,4,7'),
(19, '9'),
(14, '9,11');

-- --------------------------------------------------------

--
-- Table structure for table `collapse_ids`
--

CREATE TABLE `collapse_ids` (
  `id_user` int(11) NOT NULL DEFAULT '0',
  `collapse_id` int(11) NOT NULL,
  `collapse_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `collapse_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `collapse_ids`
--

INSERT INTO `collapse_ids` (`id_user`, `collapse_id`, `collapse_set`, `collapse_count`) VALUES
(0, 2, '2', 14),
(0, 14, '6', 10),
(0, 29, '7', 10);

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

-- --------------------------------------------------------

--
-- Table structure for table `field_config_2`
--

CREATE TABLE `field_config_2` (
  `own_table` int(1) NOT NULL DEFAULT '0',
  `f_ID` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_key` int(1) NOT NULL DEFAULT '0',
  `f_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_ref` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_type` int(2) NOT NULL,
  `f_size` int(3) DEFAULT '0',
  `f_interval` int(1) NOT NULL DEFAULT '0',
  `f_blank` int(1) NOT NULL DEFAULT '0',
  `f_unique` int(1) NOT NULL DEFAULT '0',
  `f_s_mode` int(1) NOT NULL DEFAULT '0',
  `f_table_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `f_table` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_table1_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '',
  `f_table1` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_lower` int(1) NOT NULL DEFAULT '0',
  `f_illegals` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_default` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `f_check` int(1) NOT NULL DEFAULT '0',
  `c_type` int(2) NOT NULL DEFAULT '0',
  `comm` int(1) NOT NULL DEFAULT '0',
  `f_filter_md` int(1) NOT NULL DEFAULT '1',
  `f_sort_sm` int(1) NOT NULL DEFAULT '0',
  `f_using` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `table_order` int(2) NOT NULL DEFAULT '0',
  `f_align` int(1) NOT NULL DEFAULT '0',
  `table_percent` varchar(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT '''''',
  `screen_order` int(2) NOT NULL DEFAULT '0',
  `f_type_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `c_type_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `field_config_2`
--

INSERT INTO `field_config_2` (`own_table`, `f_ID`, `f_key`, `f_name`, `f_ref`, `f_type`, `f_size`, `f_interval`, `f_blank`, `f_unique`, `f_s_mode`, `f_table_name`, `f_table`, `f_table1_name`, `f_table1`, `f_lower`, `f_illegals`, `f_default`, `f_check`, `c_type`, `comm`, `f_filter_md`, `f_sort_sm`, `f_using`, `table_order`, `f_align`, `table_percent`, `screen_order`, `f_type_text`, `c_type_text`) VALUES
(1, 'Authors', 0, 'Authors', 'id_author_set', 1, 50, 0, 1, 0, 1, 'Author sets', 'author_groups', 'Authors', 'authors', 0, '', '', 0, 2, 0, 1, 0, '2,3,1,4,5', 11, 0, '12%', 2, 'string', 'abc'),
(1, 'Title', 0, 'Title', 'title', 1, 50, 0, 0, 0, 1, '', '', '', '', 1, '39 96', '', 0, 0, 0, 1, 0, '1,4,5', 7, 0, '28%', 3, 'string', ''),
(1, 'Themes', 0, 'Themes', 'id_theme_set', 1, 50, 0, 1, 0, 1, 'Theme sets', 'theme_sets', 'Themes', 'themes', 0, '', '', 0, 0, 0, 1, 0, '2,3,1,4,5', 10, 0, '10%', 4, 'string', ''),
(1, 'Publishing', 0, 'Publishing', 'id_publishing', 1, 50, 0, 1, 0, 1, 'Publishings', 'publishings', '', '', 0, '', '', 0, 0, 0, 1, 0, '2,3,1,4,5', 2, 0, '6%', 5, 'string', ''),
(1, 'Series', 0, 'Series', 'id_part', 1, 50, 0, 1, 0, 1, 'Series sets', 'part_sets', 'Series', 'parts', 0, '', '', 0, 1, 0, 1, 0, '2,3,1,4,5', 3, 0, '5%', 6, 'string', 'hierarchic'),
(1, 'IssueYear', 0, 'Issue year', 'issue_year', 3, 12, 1, 1, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '2,1,4,5', 4, 2, '8%', 7, 'select', ''),
(1, 'IssueType', 0, 'Issue type', 'id_issue_type', 1, 50, 0, 1, 0, 1, 'Issue types', 'issue_types', '', '', 0, '', '', 0, 0, 0, 1, 0, '2,3,1,4,5', 5, 0, '8%', 8, 'string', ''),
(1, 'Magazine', 0, 'Magazine', 'id_magazine', 1, 50, 0, 1, 0, 1, 'Magazines', 'magazines', '', '', 0, '', '', 0, 0, 0, 1, 0, '2,3,1,4,5', 6, 0, '6%', 9, 'string', ''),
(1, 'UploadDate', 0, 'Upload at', 'upload_date', 2, 12, 1, 0, 0, 0, '', '', '', '', 0, '', '', 1, 0, 0, 0, 0, '1,5', 8, 2, '8%', 10, 'date', ''),
(1, 'Actuality', 0, 'Actuality', 'actuality', 0, 5, 0, 1, 0, 0, '', '', '', '', 0, '', '', 1, 0, 0, 0, 0, '1,5', 9, 1, '', 11, 'integer', ''),
(1, 'ID', 1, 'ID', 'id_publication', 0, 13, 1, 1, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,5', 1, 2, '5%', 1, 'integer', ''),
(0, 'FileName', 0, 'File name', 'file_name', 5, 0, 0, 0, 1, 0, '', '', '', '', 1, '', '', 0, 0, 0, 0, 0, '1,2,4', 2, 0, '50%', 2, 'URL_file', ''),
(0, 'FileDescription', 0, 'File description', 'file_description', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,4', 3, 0, '', 7, 'string', ''),
(0, 'FileIssueYear', 0, 'File issue year', 'file_issue_year', 3, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,2,4', 4, 0, '5%', 3, 'select', ''),
(0, 'FileVolume', 0, 'File volume', 'file_volume', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,2,4', 5, 0, '3%', 4, 'string', ''),
(0, 'FileNumber', 0, 'File number', 'file_number', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,2,4', 6, 0, '3%', 5, 'string', ''),
(0, 'FilePage', 0, 'File page', 'file_page', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,2,4', 7, 0, '3%', 6, 'string', ''),
(0, 'OrdNum', 1, 'Order number', 'ord_num', 0, 0, 0, 0, 1, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1,2,4', 8, 0, '1%', 1, 'integer', ''),
(0, 'file_size', 0, 'File size', 'file_size', 0, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '1', 10, 0, '', 0, 'integer', ''),
(0, 'file_source', 0, 'File source', 'file_source', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 0, 0, '', 11, 0, '', 0, 'string', ''),
(0, 'id_publication', 0, 'id_publication', 'id_publication', 8, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 1, 0, '', 1, 1, '', 0, 'ref', ''),
(1, 'title_low', 0, '', 'title_low', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 1, 0, '', 12, 2, '', 0, 'string', ''),
(1, '_del_mark', 0, '_del_mark', '_del_mark', 7, 0, 1, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 1, 0, '1,5,4', 13, 2, '', 0, 'del_mark', ''),
(0, 'file_name_low', 0, '', 'file_name_low', 1, 0, 0, 0, 0, 0, '', '', '', '', 0, '', '', 0, 0, 0, 1, 0, '', 9, 2, '', 0, 'string', '');

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

-- --------------------------------------------------------

--
-- Table structure for table `issue_types`
--

CREATE TABLE `issue_types` (
  `id_issue_type` int(11) NOT NULL,
  `issue_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `issue_type_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `issue_types`
--

INSERT INTO `issue_types` (`id_issue_type`, `issue_type`, `issue_type_low`) VALUES
(1, 'book', 'book'),
(2, 'Magazine', 'magazine'),
(3, 'second', 'second'),
(4, 'книги', 'книги');

-- --------------------------------------------------------

--
-- Table structure for table `magazines`
--

CREATE TABLE `magazines` (
  `id_magazine` int(11) NOT NULL,
  `magazine` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `magazine_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `magazines`
--

INSERT INTO `magazines` (`id_magazine`, `magazine`, `magazine_low`) VALUES
(13, 'Grani', 'magazine'),
(15, 'Мурзилка', 'magazine'),
(16, 'Look', 'magazine');

-- --------------------------------------------------------

--
-- Table structure for table `parts`
--

CREATE TABLE `parts` (
  `id_part` int(11) NOT NULL,
  `part` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `part_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `parts`
--

INSERT INTO `parts` (`id_part`, `part`, `part_low`) VALUES
(1, 'Kniga-otkrytie', 'part'),
(2, 'Литература', 'part'),
(3, 'XVIII век', 'part'),
(4, 'XIX век', 'part'),
(5, 'XX век', 'part'),
(6, 'Физика', 'part'),
(7, 'Математика', 'part'),
(8, 'Фонфизин', 'part'),
(9, 'Лопе де Вега', 'part'),
(10, 'Гюго', 'part'),
(11, 'Тургенев', 'part'),
(12, 'Толстой', 'part'),
(13, 'Хемингуэй', 'part'),
(14, 'Пастернак', 'part'),
(15, 'Бродский', 'part'),
(16, 'Ломоносов', 'part'),
(17, 'Кавендиш', 'part'),
(18, 'Франклин', 'part'),
(19, 'Максвел', 'part'),
(20, 'Фарадей', 'part'),
(21, 'Томпсон', 'part'),
(22, 'Гиббс', 'part'),
(23, 'Эйнштейн', 'part'),
(24, 'Бор', 'part'),
(25, 'Ландау', 'part'),
(26, 'Фейнман', 'part'),
(27, 'Эйлер', 'part'),
(28, 'Бернулли', 'part'),
(29, 'Лаплас', 'part'),
(30, 'Кантор', 'part'),
(31, 'Коши', 'part'),
(32, 'Дедекинд', 'part'),
(33, 'Колмогоров', 'part'),
(34, 'Гельфанд', 'part'),
(35, 'Parent main 1', 'part'),
(36, 'Son 1', 'part'),
(37, 'Son 2', 'part'),
(38, 'Русская классика', 'part');

-- --------------------------------------------------------

--
-- Table structure for table `part_sets`
--

CREATE TABLE `part_sets` (
  `id_part_set` int(11) NOT NULL,
  `part_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `part_sets`
--

INSERT INTO `part_sets` (`id_part_set`, `part_set`) VALUES
(2, '2'),
(3, '2,3'),
(4, '2,3,8'),
(5, '2,3,9'),
(6, '2,4'),
(7, '2,4,10'),
(8, '2,4,11'),
(9, '2,4,12'),
(111, '2,4,19'),
(112, '2,4,20'),
(113, '2,4,21'),
(10, '2,5'),
(11, '2,5,13'),
(12, '2,5,14'),
(13, '2,5,15'),
(115, '37,4'),
(14, '6'),
(15, '6,3'),
(16, '6,3,16'),
(17, '6,3,17'),
(18, '6,3,18'),
(114, '6,4,22'),
(24, '6,5'),
(25, '6,5,23'),
(26, '6,5,24'),
(27, '6,5,25'),
(28, '6,5,26'),
(29, '7'),
(30, '7,3'),
(31, '7,3,27'),
(32, '7,3,28'),
(33, '7,3,29'),
(34, '7,4'),
(35, '7,4,30'),
(36, '7,4,31'),
(40, '7,5'),
(38, '7,5,33'),
(39, '7,5,34');

-- --------------------------------------------------------

--
-- Table structure for table `primary_files`
--

CREATE TABLE `primary_files` (
  `id_publication` int(11) NOT NULL,
  `path_and_file` varchar(8191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `path_and_file_low` varchar(8191) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `alg_numbers` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `id_publication` int(11) NOT NULL,
  `id_publishing` int(11) DEFAULT '0',
  `id_part` int(11) DEFAULT '0',
  `issue_year` char(4) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `id_issue_type` int(4) DEFAULT '0',
  `id_magazine` int(11) DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `upload_date` date NOT NULL DEFAULT '0000-00-00',
  `actuality` tinyint(1) DEFAULT '0',
  `id_theme_set` int(11) DEFAULT '0',
  `id_author_set` int(11) DEFAULT '0',
  `title_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT '',
  `_del_mark` int(1) NOT NULL DEFAULT '0',
  `add_int` int(3) NOT NULL DEFAULT '0',
  `add_char` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `publication`
--

INSERT INTO `publication` (`id_publication`, `id_publishing`, `id_part`, `issue_year`, `id_issue_type`, `id_magazine`, `title`, `upload_date`, `actuality`, `id_theme_set`, `id_author_set`, `title_low`, `_del_mark`, `add_int`, `add_char`) VALUES
(1, 1, 0, '1905', 4, 0, 'Prim1', '2000-05-07', 21, 5, 4, 'prim1', 1, 0, ''),
(2, 1, 0, '1906', 2, 0, 'Prim2', '2000-05-07', 21, 5, 15, 'prim2', 0, 0, ''),
(3, 1, 0, '1907', 3, 0, 'Prim3', '2007-05-07', 21, 5, 2, 'prim3', 0, 0, ''),
(4, 1, 0, '1908', 3, 4, 'Prim4', '2007-05-07', 21, 5, 6, 'prim4', 0, 0, ''),
(5, 4, 0, '1909', 0, 13, 'Prim5', '2007-05-07', 21, 5, 6, 'prim5', 0, 0, ''),
(6, 29, 0, '1910', 0, 15, 'Prim6', '2007-05-07', 21, 5, 6, 'prim6', 0, 0, ''),
(7, 0, 25, '1911', 0, 16, 'Prim7', '2007-05-07', 21, 5, 6, 'prim7', 0, 0, ''),
(8, 32, 33, '1912', 0, 4, 'Prim8', '2007-05-07', 21, 31, 6, 'prim8', 0, 0, ''),
(9, 1, 0, '1913', 0, 4, 'Prim9', '2007-05-07', 21, 38, 6, 'prim9', 0, 0, ''),
(10, 1, 0, '1914', 0, 4, 'Prim10', '2007-05-07', 21, 37, 32, 'prim10', 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `publishings`
--

CREATE TABLE `publishings` (
  `id_publishing` int(11) NOT NULL,
  `publishing` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `publishing_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `publishings`
--

INSERT INTO `publishings` (`id_publishing`, `publishing`, `publishing_low`) VALUES
(2, 'JJJnJJJggggg', 'jjjnjjjggggg'),
(3, 'bnnnna', 'bnnnna'),
(4, 'bbbnbbb', 'bbbnbbb'),
(5, 'AAAnAAAasdf', 'aaanaaaasdf'),
(6, 'BBBnBBB--', 'bbbnbbb--'),
(16, 'qqqry', 'qqqry'),
(20, 'rrrnqwert', 'rrrnqwert'),
(29, 'yyyyynyy', 'yyyyynyy'),
(31, '11111lihgb', '11111lihgb'),
(32, '22222', '22222'),
(33, '33`333', '33`333'),
(46, 'jjjnjjjjjj', 'jjjnjjjjjj'),
(47, 'Сабашников', 'сабашников'),
(48, 'new new', 'new new');

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
('db_configs', 4, '', 0, '', 0, '', 'Data base congifurations'),
('field_config', 4, '', 0, '', 0, '', 'Field configurations'),
('table_definitions', 4, '', 0, '', 0, '', 'Table definitions');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE `themes` (
  `id_theme` int(11) NOT NULL,
  `theme` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `theme_low` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id_theme`, `theme`, `theme_low`) VALUES
(41, 'Барри', 'theme'),
(42, 'aИстория', 'theme'),
(43, 'Тяготение', 'theme'),
(44, 'rrrrrrr', 'theme'),
(45, 'яXIX век', 'theme'),
(46, 'Literature', 'theme'),
(47, 'тяготение--', 'theme'),
(48, 'Ускорение', 'theme'),
(49, 'ускорение--', 'theme'),
(50, 'яяяя', 'theme'),
(51, 'new test', 'theme'),
(52, 'Литература', 'theme'),
(53, 'Русская литература', 'theme'),
(54, 'Base', 'theme'),
(55, 'nnnnnnnnnnnnnnn', 'theme');

-- --------------------------------------------------------

--
-- Table structure for table `theme_sets`
--

CREATE TABLE `theme_sets` (
  `id_theme_set` int(11) NOT NULL,
  `theme_set` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `theme_sets`
--

INSERT INTO `theme_sets` (`id_theme_set`, `theme_set`) VALUES
(41, '41'),
(43, '43'),
(44, '44'),
(45, '45'),
(34, '45,42'),
(46, '46,44'),
(52, '46,55,44'),
(47, '47'),
(32, '48'),
(48, '49'),
(49, '50'),
(38, '51'),
(50, '52'),
(51, '53'),
(31, '54,41,44,43'),
(35, '54,43'),
(37, '54,44'),
(33, '54,49,46'),
(39, '54,51,41');

-- --------------------------------------------------------

--
-- Table structure for table `userlist`
--

CREATE TABLE `userlist` (
  `id_user` int(11) NOT NULL,
  `user_active` int(1) NOT NULL DEFAULT '0',
  `work_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `visit_count` int(11) NOT NULL DEFAULT '0',
  `user_language` int(11) NOT NULL DEFAULT '0',
  `user_screen_portion` int(3) NOT NULL DEFAULT '0',
  `user_match_case` int(1) NOT NULL DEFAULT '-1',
  `user_hide_list` int(1) NOT NULL DEFAULT '-1',
  `start_pos` int(11) NOT NULL DEFAULT '0',
  `settings_pad` int(1) NOT NULL DEFAULT '0',
  `p_count_filter` int(11) NOT NULL DEFAULT '0',
  `p_code` int(11) NOT NULL DEFAULT '0',
  `view_on` int(1) NOT NULL DEFAULT '0',
  `view_multi_columns` int(1) NOT NULL DEFAULT '0',
  `cat_search_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_s_mode_checks` int(1) NOT NULL DEFAULT '1',
  `cat_search_where` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_search_on` int(1) NOT NULL DEFAULT '0',
  `cat_filter_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_f_mode_checks` int(1) NOT NULL DEFAULT '1',
  `cat_filter_where` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_filter_on` int(1) NOT NULL DEFAULT '0',
  `cat_filter_count` int(11) NOT NULL DEFAULT '0',
  `cat_found_count` int(11) NOT NULL DEFAULT '0',
  `cat_start_pos` int(11) NOT NULL DEFAULT '0',
  `cat_prev_search_out` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_next_search_out` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_view_search` int(1) NOT NULL DEFAULT '0',
  `cat_portion` int(3) NOT NULL DEFAULT '0',
  `cat_tree` int(1) NOT NULL DEFAULT '1',
  `cat_current` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_block` int(1) NOT NULL DEFAULT '0',
  `cat_sel_current` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `cat_flag` int(1) NOT NULL DEFAULT '0',
  `cat_tree_count` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `userlist`
--

INSERT INTO `userlist` (`id_user`, `user_active`, `work_start`, `visit_count`, `user_language`, `user_screen_portion`, `user_match_case`, `user_hide_list`, `start_pos`, `settings_pad`, `p_count_filter`, `p_code`, `view_on`, `view_multi_columns`, `cat_search_text`, `cat_s_mode_checks`, `cat_search_where`, `cat_search_on`, `cat_filter_text`, `cat_f_mode_checks`, `cat_filter_where`, `cat_filter_on`, `cat_filter_count`, `cat_found_count`, `cat_start_pos`, `cat_prev_search_out`, `cat_next_search_out`, `cat_view_search`, `cat_portion`, `cat_tree`, `cat_current`, `cat_block`, `cat_sel_current`, `cat_flag`, `cat_tree_count`) VALUES
(2, 0, '0000-00-00 00:00:00', 346, 2, 15, 0, 1, 0, 1, 0, 0, 0, 0, '', 1, '', 0, '', 1, '', 0, 0, 0, 0, '', '', 0, 9, 0, '', 0, '', 0, 0),
(3, 0, '0000-00-00 00:00:00', 4, 1, 18, 0, 1, 0, 0, 0, 0, 0, 0, '', 1, '', 0, '', 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, '', 0, '', 0, 0),
(4, 0, '0000-00-00 00:00:00', 1, 1, 18, 0, 1, 0, 0, 0, 0, 0, 0, '', 1, '', 0, '', 1, '', 0, 0, 0, 0, '', '', 0, 0, 0, '', 0, '', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id_user` int(11) NOT NULL,
  `field_name` varchar(16) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `sort_order` int(2) NOT NULL DEFAULT '0',
  `sort_mode` int(2) NOT NULL DEFAULT '0',
  `text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `to_text` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
  `md` int(1) DEFAULT '1',
  `iv` int(1) NOT NULL DEFAULT '0',
  `favor_number` int(3) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`id_author`),
  ADD UNIQUE KEY `author` (`author`);

--
-- Indexes for table `author_groups`
--
ALTER TABLE `author_groups`
  ADD PRIMARY KEY (`id_author_group`),
  ADD UNIQUE KEY `author_set` (`author_set`);

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
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD UNIQUE KEY `file_ref` (`id_publication`,`file_name`);

--
-- Indexes for table `issue_types`
--
ALTER TABLE `issue_types`
  ADD PRIMARY KEY (`id_issue_type`),
  ADD UNIQUE KEY `issue_type` (`issue_type`);

--
-- Indexes for table `magazines`
--
ALTER TABLE `magazines`
  ADD PRIMARY KEY (`id_magazine`),
  ADD UNIQUE KEY `magazine` (`magazine`);

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id_part`),
  ADD UNIQUE KEY `part` (`part`);

--
-- Indexes for table `part_sets`
--
ALTER TABLE `part_sets`
  ADD PRIMARY KEY (`id_part_set`),
  ADD UNIQUE KEY `part_set` (`part_set`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id_publication`),
  ADD UNIQUE KEY `id_publication` (`id_publication`);

--
-- Indexes for table `publishings`
--
ALTER TABLE `publishings`
  ADD PRIMARY KEY (`id_publishing`),
  ADD UNIQUE KEY `publishing` (`publishing`);

--
-- Indexes for table `table_definitions`
--
ALTER TABLE `table_definitions`
  ADD PRIMARY KEY (`table_name`),
  ADD UNIQUE KEY `table_name` (`table_name`);

--
-- Indexes for table `themes`
--
ALTER TABLE `themes`
  ADD PRIMARY KEY (`id_theme`),
  ADD UNIQUE KEY `theme` (`theme`);

--
-- Indexes for table `theme_sets`
--
ALTER TABLE `theme_sets`
  ADD PRIMARY KEY (`id_theme_set`),
  ADD UNIQUE KEY `theme_set` (`theme_set`);

--
-- Indexes for table `userlist`
--
ALTER TABLE `userlist`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `id_author` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `author_groups`
--
ALTER TABLE `author_groups`
  MODIFY `id_author_group` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;
--
-- AUTO_INCREMENT for table `issue_types`
--
ALTER TABLE `issue_types`
  MODIFY `id_issue_type` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `magazines`
--
ALTER TABLE `magazines`
  MODIFY `id_magazine` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id_part` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `part_sets`
--
ALTER TABLE `part_sets`
  MODIFY `id_part_set` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;
--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `id_publication` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT for table `publishings`
--
ALTER TABLE `publishings`
  MODIFY `id_publishing` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
--
-- AUTO_INCREMENT for table `themes`
--
ALTER TABLE `themes`
  MODIFY `id_theme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT for table `theme_sets`
--
ALTER TABLE `theme_sets`
  MODIFY `id_theme_set` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
