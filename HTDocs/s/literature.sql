CREATE DATABASE IF NOT EXISTS literature DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_bin;
USE literature;

CREATE TABLE author_groups 
(
  id_author_group int unsigned NOT NULL,
  author_set varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO author_groups VALUES
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
ALTER TABLE author_groups
  ADD PRIMARY KEY (id_author_group),
  ADD UNIQUE KEY author_set (author_set);
ALTER TABLE author_groups
  MODIFY id_author_group int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
  
CREATE TABLE authors 
(
  id_author int unsigned NOT NULL,
  author varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  author_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO authors VALUES
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
ALTER TABLE authors
  ADD PRIMARY KEY (id_author),
  ADD UNIQUE KEY author (author);
ALTER TABLE authors
  MODIFY id_author int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE db_configs 
(
  config_name varchar(31) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  config_value varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  config_type tinyint unsigned NOT NULL DEFAULT '0',
  config_description varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
ALTER TABLE db_configs
  ADD PRIMARY KEY (config_name),
  ADD UNIQUE KEY config_name (config_name);

CREATE TABLE field_config 
(
  own_table tinyint unsigned NOT NULL DEFAULT '0',
  f_ID varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_key tinyint unsigned NOT NULL DEFAULT '0',
  f_name varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_type tinyint unsigned NOT NULL,
  f_size smallint unsigned DEFAULT '0',
  f_interval tinyint unsigned NOT NULL DEFAULT '0',
  f_blank tinyint unsigned NOT NULL DEFAULT '0',
  f_unique tinyint unsigned NOT NULL DEFAULT '0',
  f_s_mode tinyint unsigned NOT NULL DEFAULT '0',
  f_table varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_illegals varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_default varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_check tinyint unsigned NOT NULL DEFAULT '0',
  comm tinyint unsigned NOT NULL DEFAULT '0',
  f_filter_md tinyint unsigned NOT NULL DEFAULT '1',
  f_sort_sm tinyint unsigned NOT NULL DEFAULT '0',
  f_using varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  f_align tinyint unsigned NOT NULL DEFAULT '0',
  table_percent varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  screen_order tinyint unsigned NOT NULL DEFAULT '0',
  load_order tinyint unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
ALTER TABLE field_config
  ADD UNIQUE KEY field_config_key (own_table,f_ID);

CREATE TABLE files 
(
  id_publication int unsigned NOT NULL,
  file_name varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_description varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  file_issue_year char(4) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  file_volume char(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  file_number char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  file_page char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  ord_num smallint unsigned NOT NULL,
  file_name_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_size char(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL DEFAULT '',
  file_source varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO files VALUES
(2, 'CoronaBob.pdf', '', '0', '', '', '', 1, 'coronabob.pdf', '', NULL);
ALTER TABLE files
  ADD UNIQUE KEY file_ref (id_publication,file_name);

CREATE TABLE issue_types 
(
  id_issue_type tinyint unsigned NOT NULL,
  issue_type varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  issue_type_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO issue_types VALUES
(1, 'book', 'book'),
(2, 'Magazine', 'magazine'),
(3, 'second', 'second'),
(4, 'книги', 'книги');
ALTER TABLE issue_types
  ADD PRIMARY KEY (id_issue_type),
  ADD UNIQUE KEY issue_type (issue_type);
ALTER TABLE issue_types
  MODIFY id_issue_type tinyint unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE magazines 
(
  id_magazine smallint unsigned NOT NULL,
  magazine varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  magazine_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO magazines VALUES
(13, 'Grani', 'magazine'),
(15, 'Мурзилка', 'magazine'),
(16, 'Look', 'magazine');
ALTER TABLE magazines
  ADD PRIMARY KEY (id_magazine),
  ADD UNIQUE KEY magazine (magazine);
ALTER TABLE magazines
  MODIFY id_magazine smallint unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE part_sets 
(
  id_part_set smallint unsigned NOT NULL,
  part_set varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO part_sets VALUES
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
ALTER TABLE part_sets
  ADD PRIMARY KEY (id_part_set),
  ADD UNIQUE KEY part_set (part_set);
ALTER TABLE part_sets
  MODIFY id_part_set smallint unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE parts 
(
  id_part smallint unsigned NOT NULL,
  part varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  part_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO parts VALUES
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
ALTER TABLE parts
  ADD PRIMARY KEY (id_part),
  ADD UNIQUE KEY part (part);
ALTER TABLE parts
  MODIFY id_part smallint unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE primary_files 
(
  id_publication int unsigned NOT NULL,
  path_and_file varchar(8191) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  path_and_file_low varchar(8191) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  alg_numbers varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

CREATE TABLE publication 
(
  id_publication int unsigned NOT NULL,
  id_publishing smallint unsigned DEFAULT '0',
  id_part smallint unsigned DEFAULT '0',
  issue_year char(4) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '',
  id_issue_type tinyint unsigned DEFAULT '0',
  id_magazine smallint unsigned DEFAULT '0',
  title varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '',
  upload_date date NULL,
  actuality tinyint unsigned DEFAULT '0',
  id_theme_set int unsigned DEFAULT '0',
  id_author_set int unsigned DEFAULT '0',
  title_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT '',
  _del_mark tinyint unsigned NOT NULL DEFAULT '0',
  add_int smallint unsigned NOT NULL DEFAULT '0',
  add_char varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO publication VALUES
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
ALTER TABLE publication
  ADD PRIMARY KEY (id_publication),
  ADD UNIQUE KEY id_publication (id_publication);
ALTER TABLE publication
  MODIFY id_publication int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE publishings 
(
  id_publishing int unsigned NOT NULL,
  publishing varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  publishing_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO publishings VALUES
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
ALTER TABLE publishings
  ADD PRIMARY KEY (id_publishing),
  ADD UNIQUE KEY publishing (publishing);
ALTER TABLE publishings
  MODIFY id_publishing int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

CREATE TABLE table_definitions 
(
  table_name varchar(31) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  use_type tinyint unsigned NOT NULL DEFAULT '0',
  illegal_symbols varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  group_catalog_type tinyint unsigned NOT NULL DEFAULT '0',
  separators varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  max_level tinyint unsigned NOT NULL DEFAULT '0',
  second_catalog_name varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  table_title varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO table_definitions VALUES
('db_configs', 4, '', 0, '', 0, '', 'Data base congifurations'),
('field_config', 4, '', 0, '', 0, '', 'Field configurations'),
('table_definitions', 4, '', 0, '', 0, '', 'Table definitions');
ALTER TABLE table_definitions
  ADD PRIMARY KEY (table_name),
  ADD UNIQUE KEY table_name (table_name);

CREATE TABLE theme_sets 
(
  id_theme_set int unsigned NOT NULL,
  theme_set varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO theme_sets VALUES
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
ALTER TABLE theme_sets
  ADD PRIMARY KEY (id_theme_set),
  ADD UNIQUE KEY theme_set (theme_set);
ALTER TABLE theme_sets
  MODIFY id_theme_set int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;


CREATE TABLE themes 
(
  id_theme int unsigned NOT NULL,
  theme varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  theme_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO themes VALUES
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
ALTER TABLE themes
  ADD PRIMARY KEY (id_theme),
  ADD UNIQUE KEY theme (theme);
ALTER TABLE themes
  MODIFY id_theme int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
