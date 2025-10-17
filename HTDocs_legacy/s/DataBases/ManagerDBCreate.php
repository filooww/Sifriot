<?php

function TestManagerTablesExist($dbh)
{
    $tables = ['algorithms', 'coding_table', 'db_list', 'db_s_configs', 'interface_special_texts', 'interface_texts', 'languages', 'translate_table', 'user_ident', 'visits'];
    $all_tables = [];
    $res = mysqli_query($dbh, 'SHOW TABLES');
    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $all_tables[] = $row[0];
        }
        mysqli_free_result($res);
    }
    foreach ($tables as $k_table) {
        if (! in_array($k_table, $all_tables)) {
            $_SESSION['preliminary_flags']['no_existed_tables'][$k_table] = CreateSystemTable($dbh, $k_table);
        }
    }
}
function CreateSystemTable($dbh, $table)
{
    $comm = [];
    switch ($table) {
        case 'algorithms': $comm = CreateAlgorithms();
            break;
        case 'coding_table': $comm = CreateCoding();
            break;
        case 'db_list': $comm = CreateDBList();
            break;
        case 'db_s_configs': $comm = CreateDBSConfig('db_s_configs');
            break;
        case 'interface_texts': $comm = CreateInterfaceTexts();
            break;
        case 'languages': $comm = CreateLanguages();
            break;
        case 'interface_special_texts': $comm = CreateInterfaceSpecialTexts();
            break;
        case 'translate_table': $comm = CreateTranslateTable();
            break;
        case 'user_ident': $comm = CreateUserIdent();
            break;
        case 'visits': $comm = CreateVisits();
            break;
    }
    if (count($comm) > 0) {
        mysqli_query($dbh, $comm[0]);
        if ($comm[1] != '') {
            mysqli_query($dbh, $comm[1]);
        }
        if ($comm[2] != '') {
            mysqli_query($dbh, $comm[2]);
        }
        if ($comm[3] != '') {
            mysqli_query($dbh, $comm[3]);
        }
    }

    return $comm[3] != '';
}
function CreateAlgorithms()
{
    $crt = 'CREATE TABLE algorithms ';
    $crt .= '(id_algorithm smallint unsigned NOT NULL, ';
    $crt .= "alg_offset tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "del_from_source tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "beg_limit_set varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "beg_number tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "beg_inc tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "beg_scr tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "inner_limit_set varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "end_limit_set varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "end_number tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "end_inc tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "end_scr tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "del_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "ins_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "field_only tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "reg_expression varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "reg_scr tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "alg_remarks varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin DEFAULT 'New algorithm') ";
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE algorithms ADD PRIMARY KEY (id_algorithm), ADD UNIQUE KEY id_algorithm (id_algorithm)';
    $mod = 'ALTER TABLE algorithms MODIFY id_algorithm smallint unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1';

    return [$crt, $add, $mod, ''];
}
function CreateCoding()
{
    $crt = 'CREATE TABLE coding_table ';
    $crt .= '(id_coding tinyint unsigned NOT NULL, ';
    $crt .= 'coding_name varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE coding_table ADD PRIMARY KEY (id_coding), ADD UNIQUE KEY id_coding (id_coding), ADD UNIQUE KEY coding_name (coding_name)';
    $ins = "INSERT INTO coding_table VALUES (1,'ASCII')";

    return [$crt, $add, '', $ins];
}
function CreateDBList()
{
    $crt = 'CREATE TABLE db_list ';
    $crt .= '(db_id tinyint unsigned NOT NULL, ';
    $crt .= 'db_name varchar(31) CHARACTER SET utf8 COLLATE utf8mb3_bin NULL, ';
    $crt .= 'db_coding varchar(31) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ';
    $crt .= 'db_comment varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE db_list ADD PRIMARY KEY (db_id), ADD UNIQUE KEY db_name (db_name), ADD UNIQUE KEY db_id (db_id)';
    $ins = "INSERT INTO db_list VALUES (0,'db_manager','utf8','system DB')";

    return [$crt, $add, '', $ins];
}
function CreateDBSConfig($conf_table)
{
    $crt = 'CREATE TABLE '.$conf_table.' ';
    $crt .= '(config_name varchar(31) CHARACTER SET utf8 COLLATE utf8mb3_bin NULL, ';
    $crt .= 'config_value varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ';
    $crt .= "config_type tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= 'config_description varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE '.$conf_table.' ADD PRIMARY KEY (config_name), ADD UNIQUE KEY config_name (config_name)';

    return [$crt, $add, '', ''];
}
function CreateInterfaceTexts()
{
    $crt = 'CREATE TABLE interface_texts ';
    $crt .= "(id_title mediumint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "id_language tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= 'title_text varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE interface_texts ADD UNIQUE KEY title_key (id_title,id_language)';

    return [$crt, $add, '', ''];
}
function CreateLanguages()
{
    $crt = 'CREATE TABLE languages ';
    $crt .= '(id_language tinyint unsigned NOT NULL, ';
    $crt .= 'language varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE languages ADD PRIMARY KEY (id_language), ADD UNIQUE KEY id_language (id_language), ADD UNIQUE KEY language (language)';
    $ins = "INSERT INTO languages VALUES (0,'(special)'),(1,'English')";

    return [$crt, $add, '', $ins];
}
function CreateInterfaceSpecialTexts()
{
    $crt = 'CREATE TABLE interface_special_texts (special_type varchar(255) NOT NULL, special_numbers varchar(255) NOT NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE interface_special_texts ADD UNIQUE KEY special_type (special_type)';
    $ins = "INSERT INTO interface_special_texts VALUES ('table_types',''),('compare_mode',''),'sort_mode',''),'field_align',''),'field_using',''),'field_types',''),'group_types',''),('z_o','')";

    return [$crt, $add, '', $ins];
}
function CreateTranslateTable()
{
    $crt = 'CREATE TABLE translate_table ';
    $crt .= '(id_coding tinyint unsigned NOT NULL, ';
    $crt .= 'id_lang tinyint unsigned NOT NULL, ';
    $crt .= 'letter char(1) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ';
    $crt .= "to_lower char(1) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= 'translit_letters varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ';
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE translate_table ADD UNIQUE KEY translate_key (letter,id_coding)';

    return [$crt, $add, '', ''];
}
function CreateUserIdent()
{
    $crt = 'CREATE TABLE user_ident ';
    $crt .= '(id_user int unsigned NOT NULL, ';
    $crt .= 'name varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin DEFAULT NULL, ';
    $crt .= 'pass varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin DEFAULT NULL, ';
    $crt .= "user_priority tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= 'use_lang_id tinyint unsigned NOT NULL, ';
    $crt .= "user_list_portion smallint unsigned NOT NULL DEFAULT '10', ";
    $crt .= 'preffered_db tinyint unsigned NULL, ';
    $crt .= "date_format char(4) NOT NULL DEFAULT '.dmy', ";
    $crt .= "hide_list tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "match_case tinyint unsigned NOT NULL DEFAULT '0') ";
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE user_ident ADD PRIMARY KEY (id_user), ADD UNIQUE KEY id_user (id_user), ADD UNIQUE KEY name (name), ADD UNIQUE KEY pass (pass)';
    $mod = 'ALTER TABLE user_ident MODIFY id_user int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1';

    return [$crt, $add, $mod, ''];
}
function CreateVisits()
{
    $crt = 'CREATE TABLE visits ';
    $crt .= '(id_db tinyint unsigned NOT NULL, ';
    $crt .= 'id_user int unsigned NOT NULL, ';
    $crt .= 'work_start datetime NOT NULL, ';
    $crt .= 'visit_count int unsigned NOT NULL, ';
    $crt .= "working_mode tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= 'visit_session varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ';
    $crt .= "rest_time mediumint unsigned NOT NULL DEFAULT '0') ";
    $crt .= 'ENGINE=InnoDB DEFAULT CHARSET=utf8;';
    $add = 'ALTER TABLE visits ADD UNIQUE KEY db_user (id_db,id_user)';

    return [$crt, $add, '', ''];
}
