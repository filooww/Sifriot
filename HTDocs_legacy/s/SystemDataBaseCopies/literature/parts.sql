-- phpMyAdmin SQL Dump
-- version 4.7.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 02, 2024 at 09:39 AM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `parts`
--
ALTER TABLE `parts`
  ADD PRIMARY KEY (`id_part`),
  ADD UNIQUE KEY `part` (`part`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `parts`
--
ALTER TABLE `parts`
  MODIFY `id_part` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
