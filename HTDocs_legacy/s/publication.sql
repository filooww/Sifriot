CREATE TABLE publication_1
(
  id_publication int unsigned NOT NULL,
  id_publishing smallint unsigned NULL DEFAULT '0',
  id_part smallint unsigned NULL DEFAULT '0',
  issue_year char(4) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL,
  id_issue_type tinyint unsigned NULL DEFAULT '0',
  id_magazine smallint unsigned NULL DEFAULT '0',
  title varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL,
  upload_date date NULL DEFAULT NULL,
  actuality tinyint unsigned NULL DEFAULT '0',
  id_theme_set int unsigned NULL DEFAULT '0',
  id_author_set int unsigned NULL DEFAULT '0',
  title_low varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NULL,
  _del_mark tinyint unsigned NOT NULL DEFAULT '0',
  add_int smallint unsigned NOT NULL DEFAULT '0',
  add_char varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
INSERT INTO publication_1 VALUES
(1,1,0,'1905',4,0,'Prim1',2000-05-07,21,5,4,'prim1',1,0,''),
(2,1,0,'1906',2,0,'Prim2',2000-05-07,21,5,15,'prim2',0,0,''),
(3,1,0,'1907',3,0,'Prim3',2007-05-07,21,5,2,'prim3',0,0,''),
(4,1,0,'1908',3,4,'Prim4',2007-05-07,21,5,6,'prim4',0,0,''),
(5,4,0,'1909',0,13,'Prim5',2007-05-07,21,5,6,'prim5',0,0,''),
(6,29,0,'1910',0,15,'Prim6',2007-05-07,21,5,6,'prim6',0,0,''),
(7,0,25,'1911',0,16,'Prim7',2007-05-07,21,5,6,'prim7',0,0,''),
(8,32,33,'1912',0,4,'Prim8',2007-05-07,21,31,6,'prim8',0,0,''),
(9,1,0,'1913',0,4,'Prim9',2007-05-07,21,38,6,'prim9',0,0,''),
(10,1,0,'1914',0,4,'Prim10',2007-05-07,21,37,32,'prim10',0,0,'');
ALTER TABLE publication_1
  ADD PRIMARY KEY (id_publication),
  ADD UNIQUE KEY id_publication (id_publication);
ALTER TABLE publication_1
  MODIFY id_publication int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;


INSERT INTO field_config VALUES (1,'ext',0,'',0,0,0,0,0,0,'','','',0,0,0,0,'',0,'',0,0) 
INSERT INTO field_config VALUES (-1,'ззext',0,'',0,0,0,0,0,0,'','','',0,0,0,0,'',0,'',0,0) 