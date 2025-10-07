CREATE TABLE files_1
(
  id_publication int unsigned NOT NULL,
  file_name varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_description varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_issue_year char(4) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_volume char(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_number char(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_page char(9) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  ord_num smallint unsigned NOT NULL,
  file_name_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_size char(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  file_source varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO files_1 VALUES
(2,'CoronaBob.pdf','','0','','','',1,'coronabob.pdf','','');
ALTER TABLE files_1
  ADD UNIQUE KEY file_ref (file_name);
