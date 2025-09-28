<?php
function TestDataBaseTablesExist($dbh, $db)
{
    $tables = array("db_configs", "field_config", "table_definitions");
	$all_tables = array();
	$res = mysqli_query($dbh, "SHOW TABLES");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			$all_tables[] = $row[0];
		}
		mysqli_free_result($res);
	}
	$add_tables = array();
	foreach ($tables as $table)
    {
        if (!in_array($table, $all_tables))
        {
            CreateServiceTable($dbh, $table);
            $add_tables[] = $table;
        }
    }
    if (count($add_tables) > 0) $_SESSION['db_errors'][] = ((count($add_tables) == 1) ? FTM(Title(365)) : FTM(Title(115)))." <b>".implode(", ", $add_tables)."</b> ".((count($add_tables) == 1) ? Title(536) : Title(712));
    return $add_tables;
}
function CreateServiceTable($dbh, $table)
{
	$comm = array();
	switch ($table)
	{
		case "db_configs"       : $comm = CreateDBSConfig("db_configs"); break;
		case "field_config"     : $comm = CreateDataBaseFieldConfigs(); break;
		case "table_definitions": $comm = CreateDataBaseTableConfigs(); break;
	}
	if (count($comm) > 0)
    {
        mysqli_query($dbh, $comm[0]);
        if ($comm[1] != "") mysqli_query($dbh, $comm[1]);
        if ($comm[2] != "") mysqli_query($dbh, $comm[2]);
        if ($comm[3] != "") mysqli_query($dbh, $comm[3]);
    }
}
function CreateDataBaseFieldConfigs()
{
    $crt = "CREATE TABLE field_config ";
    $crt .= "(own_table tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_ID varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_key tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_name varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_type tinyint unsigned NOT NULL, ";
    $crt .= "f_size smallint unsigned DEFAULT '0', ";
    $crt .= "f_interval tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_blank tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_unique tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_s_mode tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_table varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_illegals varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_default varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_check tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "comm tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_filter_md tinyint unsigned NOT NULL DEFAULT '1', ";
    $crt .= "f_sort_sm tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "f_using varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "f_align tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "table_percent varchar(5) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL DEFAULT '', ";
    $crt .= "screen_order tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "load_order tinyint unsigned NOT NULL DEFAULT '0') ";
    $crt .= "ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $add = "ALTER TABLE field_config ADD UNIQUE KEY field_config_key (own_table,f_ID)";
    return array($crt, $add, "", "");
}
function CreateDataBaseTableConfigs()
{
    $crt = "CREATE TABLE table_definitions ";
    $crt .= "(table_name varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "use_type tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "illegal_symbols varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "group_catalog_type tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "separators varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "max_level tinyint unsigned NOT NULL DEFAULT '0', ";
    $crt .= "second_catalog_name varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "table_title varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ";
    $crt .= "ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $add = "ALTER TABLE table_definitions ADD PRIMARY KEY (table_name), ADD UNIQUE KEY table_name (table_name)";
	$ins = "INSERT INTO table_definitions VALUES ";
    $ins .= "('db_configs',4,'',0,'',0,'','Data base congifurations'),";
    $ins .= "('field_config',4,'',0,'',0,'','Field configurations'),";
    $ins .= "('table_definitions',4,'',0,'',0,'','Table definitions')";
    return array($crt, $add, "", $ins);
}
//========================
function CreateBasicTable($f_type)
{
    $arr_field = array();
    $key_field = "";
    foreach ($_SESSION['field_definitions'][$f_type] as $k => $v)
    {
        $arr_field[] = $k;
        switch ($v[6])
        {
            case 0  : $arr_field[] .= " int unsigned"; break;
            case 1  : $arr_field[] .= " varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin"; break;
            case 2  : $arr_field[] .= " date"; break;
            case 3  : $arr_field[] .= " int unsigned"; break;
            case 4  : $arr_field[] .= " tinyint unsigned"; break;
            case 5  : $arr_field[] .= " varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin"; break;
            case 6  : $arr_field[] .= " varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin"; break;
            case 7  : $arr_field[] .= " tinyint unsigned"; break;
            case 8  : $arr_field[] .= " int unsigned"; break;
            default : $arr_field[] .= " tinyint unsigned";
        }
        if (count($arr_field) > 0)
        {
            $ctr = "CREATE TABLE ".$_SESSION['mandatory_db_tables'][$f_type]." (".implode(",", $arr_field).") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            if ($v[8] == "") $arr_field[] .= " NOT NULL";
            else $arr_field[] .= " DELAULT '".$v[8]."'";
            if ($v[0]) $key_field = $k;
        }
        $add = ($key_field == "") ? "" : "ALTER TABLE ".$_SESSION['mandatory_db_tables'][$f_type]." ADD PRIMARY KEY (".$key_field.")";
        $mod = ($f_type == 1) ? "ALTER TABLE ".$_SESSION['mandatory_db_tables'][$f_type]." MODIFY ".$key_field." int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1" : "";
        return array($crt, $add, $mod, $key_field);
    }
    return array();
}
function CreateCatalog($table)
{
    $crt = "CREATE TABLE ".$table." ";
    $crt .= "(".$_SESSION['table_definitions'][$table]['catalog_id']." int unsigned NOT NULL, ";
    $crt .= $_SESSION['table_definitions'][$table]['catalog_value']." varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    if ($_SESSION['table_definitions'][$table]['second_catalog'] == "") $crt .= $_SESSION['table_definitions'][$table]['catalog_value']."_low varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ";
    $crt .= "ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $add = "ALTER TABLE ".$table." ADD PRIMARY KEY (".$_SESSION['table_definitions'][$table]['catalog_id'].")";
    $mod = "ALTER TABLE ".$table." MODIFY ".$_SESSION['table_definitions'][$table]['catalog_id']." int unsigned NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
    return array($crt, $add, $mod, "");
}
function CreatePrimaryUpload($table, $key_field)
{
    $crt = "CREATE TABLE ".$table." ";
    $crt .= "(".$key_field." int unsigned NOT NULL, ";
    $crt .= "path_and_file varchar(8191) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "path_and_file_low varchar(8191) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL, ";
    $crt .= "alg_numbers varchar(255) CHARACTER SET utf8 COLLATE utf8mb3_bin NOT NULL) ";
    $crt .= "ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    return array($crt, "", "", "");
}

?>
