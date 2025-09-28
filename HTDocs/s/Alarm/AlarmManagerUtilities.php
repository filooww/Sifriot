<?php
function SysTableNoErrors($table)
{
	$i = array_search($table, $_SESSION['preliminary_flags']['table_errors']);
	if ($i !== false) unset($_SESSION['preliminary_flags']['table_errors'][$i]);
}
function AlgorithmsCheck($dbh)
{
    $fl = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM algorithms WHERE ".AlgorithmErrors());
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
            if ($row[0] > 0)
            {
                if (!in_array("algorithms", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "algorithms";
                $fl = true;
            }
        }
		mysqli_free_result($res);
	}

	if (!$fl) SysTableNoErrors("algorithms");
}
function SimpleSysTableCheck($dbh, $table, $req_str)
{
	$fl_tab_err = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM ".$table." WHERE ".$req_str);
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0)
			{
				if (!in_array($table, $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = $table;
				$fl_tab_err = true;
			}
		}
		mysqli_free_result($res);
	}
	if (!$fl_tab_err) SysTableNoErrors($table);
}
function UserSysTableCheck($dbh)
{
	$fl_tab_err = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM user_ident WHERE ".UserListErrorCondition());
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0)
			{
				if (!in_array("user_ident", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "user_ident";
				$fl_tab_err = true;
			}
		}
		mysqli_free_result($res);
	}
	if (!$fl_tab_err) SysTableNoErrors("user_ident");
}
function DataBaseTableCheck($dbh)
{
	$fl_err = false;
	$fl_sys = false;
	$Mes = array();
	$res = mysqli_query($dbh, "SELECT * FROM db_list");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			if ($row[0] == 0 && $row[1] == "db_manager") $fl_sys = true;
			elseif ($row[1] == "") $fl_err = true;
			if (!GetDB($row[1], $Mes, $row[2])) $fl_err = true;
		}
		mysqli_free_result($res);
		if ((!$fl_sys || $fl_err) && !in_array("db_list", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "db_list";
	}
	if (!$fl_err && $fl_sys) SysTableNoErrors("db_list");
}
function InterfaceTableCheck($dbh)
{
	$fl_err = false;
	$str_req = "SELECT COUNT(*), 'id_title' FROM interface_texts WHERE id_title = 0 OR id_title > 99999";
	$str_req .= " UNION ";
	$str_req .= "SELECT COUNT(*), 'special' FROM interface_special_texts WHERE special_type NOT IN ('table_types','compare_mode','sort_mode','field_align','field_using','field_types','group_types','z_o') OR special_numbers = ''";
	$str_req .= " UNION ";
	$str_req .= "SELECT COUNT(*), 'count' FROM interface_special_texts";
	$res = mysqli_query($dbh, $str_req);
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			if ($row[1] == "id_title" && $row[0] > 0) $fl_err = true;
			if ($row[1] == "special" && $row[0] > 0) $fl_err = true;
			if ($row[1] == "count" && $row[0] != 8) $fl_err = true;
		}
		mysqli_free_result($res);
	}
    if (!$fl_err) $fl_err = (TestSpecialInterfaceTexts($dbh));
    if ($fl_err && !in_array("interface_texts", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "interface_texts";
	if (!$fl_err) SysTableNoErrors("interface_texts");
}
function NoMatchManagerFieldDef($row, $t_def)
{
    $arr_par = array();
    for ($i = 1; $i < 7; $i++) if ($row[$i] != $t_def[$i - 1]) $arr_par[] = "parameter # <b>".(string)$i."</b> -- <b>".SetFieldDefValue($row[$i])."</b> to <b>".SetFieldDefValue($t_def[$i - 1])."</b>";
    return $arr_par;
}
function SetFieldDefValue($vv)
{
    if (is_null($vv)) return "NULL";
    if ($vv == "") return "''";
    return (string)$vv;
}
function TestManagerTableStructure($dbh, $arr_table)
{
    $structure_errors = array();
    foreach (array_keys($arr_table) as $table)
    {
        $res_field = mysqli_query($dbh, "SHOW FULL COLUMNS FROM ".$table);
	    if ($res_field)
        {
            $arr_struct = array();
            while ($row_field = mysqli_fetch_row($res_field))
            {
                if (!isset($arr_table[$table][$row_field[0]])) $structure_errors[$table][$row_field[0]] = "drop the field <b>".$row_field[0]."</b>";
                else
                {
                    $arr_par = NoMatchManagerFieldDef($row_field, $arr_table[$table][$row_field[0]]);
                    if (count($arr_par) > 0) $structure_errors[$table][$row_field[0]] = "change the field <b>".$row_field[0]."</b>: ".implode(", ", $arr_par);
                }
                $arr_struct[$row_field[0]] = array($row_field[1], $row_field[2], $row_field[3], $row_field[4], $row_field[5]);
            }
            mysqli_free_result($res_field);
            foreach (array_keys($arr_table[$table]) as $field) if (!isset($arr_struct[$field])) $structure_errors[$table][$field] = "add the field <b>".$field."</b>";
        }
    }
    return $structure_errors;
}
function ManagerPreliminaryCheck($dbh)
{
    AlgorithmsCheck($dbh);
    SimpleSysTableCheck($dbh, "coding_table", "coding_name = '' OR id_coding < 1");
    DataBaseTableCheck($dbh);
    SimpleSysTableCheck($dbh, "db_s_configs", "config_name NOT REGEXP '^[a-z][a-z0-9_]*$' OR config_name = '' OR config_type > 1 OR config_type = 1 AND (config_value = '' OR config_value <> '' AND config_value NOT REGEXP '^[+-]?[0-9]*\\.?[0-9]+([eE][+-]?[0-9]+)?$')");
    InterfaceTableCheck($dbh);
    UserSysTableCheck($dbh);
    SimpleSysTableCheck($dbh, "languages", "language = '' OR language LIKE '%''%'");
    TranslateTableCheck($dbh);
    SimpleSysTableCheck($dbh, "visits", "visit_count < 1 OR working_mode < -1 OR working_mode > 1 OR work_start IS NULL AND working_mode >= 0 OR work_start IS NOT NULL AND working_mode = -1");
}
function TranslateTableCheck($dbh)
{
	$fl_tab_err = false;
	$res = mysqli_query($dbh, "SELECT COUNT(*) FROM translate_table WHERE letter = '' OR id_lang > 1 AND translit_letters = '' OR id_lang = 0 AND translit_letters <> ''");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			if ($row[0] > 0)
			{
				if (!in_array("translate_table", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "translate_table";
				$fl_tab_err = true;
			}
		}
		mysqli_free_result($res);
	}
	if ($fl_tab_err)
	{
	
	    $res = mysqli_query($dbh, "SELECT translit_letters FROM translate_table WHERE translit_letters <> ''");
	    if ($res)
	    {
		    while ($row = mysqli_fetch_row($res))
		    {
		        if (TransliteratonSetError($row[0]))
                {
                    if (!in_array("translate_table", $_SESSION['preliminary_flags']['table_errors'])) $_SESSION['preliminary_flags']['table_errors'][] = "translate_table";
                    $fl_tab_err = true;
                    break;
                }
			}
		}
		mysqli_free_result($res);
	}
    if (!$fl_tab_err) SysTableNoErrors("translate_table");
}
function SysTableError($table)
{
    if (IsInvalidTableReferences($table) || in_array($table, $_SESSION['preliminary_flags']['table_errors'])) return " <font color='FF0000'>".FTM(Title(200), false)."</font>";
    else
    {
        $arr = array();
        if (isset($_SESSION['preliminary_flags']['no_existed_tables'][$table]))
        {
            if ($_SESSION['preliminary_flags']['no_existed_tables'][$table]) $arr[] = " <font color='FF0000'>".Title(617)." ".Title(82)." ".Title(661)."</font>";
            else $arr[] = "<font color='FF0000'>".Title(617)." ".Title(660)."</font>";
        }
        if (count($arr) > 0) return implode("; ", $arr);
        else return "";
    }
}
function ManagerDataBaseStructureDefinition()
{
    $arr['algorithms']['id_algorithm'] = array("smallint unsigned", null, "NO", "PRI", null, "auto_increment");
    $arr['algorithms']['alg_offset'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['del_from_source'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['beg_limit_set'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['beg_number'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['beg_inc'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['beg_scr'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['inner_limit_set'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['end_limit_set'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['end_number'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['end_inc'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['end_scr'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['del_symbols'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['ins_symbols'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['field_only'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['reg_expression'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "", "");
    $arr['algorithms']['reg_scr'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['algorithms']['alg_remarks'] = array("varchar(255)", "utf8mb3_bin", "NO", "", "New algorithm", "");
    $arr['coding_table']['id_coding'] = array("tinyint unsigned", null, "NO", "PRI", null, "");
    $arr['coding_table']['coding_name'] = array("varchar(255)", "utf8mb3_bin", "NO", "UNI", null, "");
    $arr['db_list']['db_id'] = array("tinyint unsigned", null, "NO", "PRI", null, "");
    $arr['db_list']['db_name'] = array("varchar(31)", "utf8mb3_bin", "NO", "UNI", null, "");
    $arr['db_list']['db_coding'] = array("varchar(31)", "utf8mb3_bin", "NO", "", null, "");
    $arr['db_list']['db_comment'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['db_s_configs']['config_name'] = array("varchar(31)", "utf8mb3_bin", "NO", "PRI", null, "");
    $arr['db_s_configs']['config_value'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['db_s_configs']['config_type'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['db_s_configs']['config_description'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['interface_special_texts']['special_type'] = array("varchar(255)", "utf8mb3_bin", "NO", "PRI", null, "");
    $arr['interface_special_texts']['special_numbers'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['interface_texts']['id_title'] = array("mediumint unsigned", null, "NO", "PRI", "0", "");
    $arr['interface_texts']['id_language'] = array("tinyint unsigned", null, "NO", "PRI", "0", "");
    $arr['interface_texts']['title_text'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['languages']['id_language'] = array("tinyint unsigned", null, "NO", "PRI", null, "");
    $arr['languages']['language'] = array("varchar(255)", "utf8mb3_bin", "NO", "UNI", null, "");
    $arr['translate_table']['id_coding'] = array("tinyint unsigned", null, "NO", "PRI", null, "");
    $arr['translate_table']['id_lang'] = array("tinyint unsigned", null, "NO", "", null, "");
    $arr['translate_table']['letter'] = array("char(1)", "utf8mb3_bin", "NO", "PRI", null, "");
    $arr['translate_table']['translit_letters'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['user_ident']['id_user'] = array("int unsigned", null, "NO", "PRI", null, "auto_increment");
    $arr['user_ident']['name'] = array("varchar(255)", "utf8mb3_bin", "NO", "UNI", null, "");
    $arr['user_ident']['pass'] = array("varchar(255)", "utf8mb3_bin", "NO", "UNI", null, "");
    $arr['user_ident']['user_priority'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['user_ident']['use_lang_id'] = array("tinyint unsigned", null, "NO", "", null, "");
    $arr['user_ident']['user_list_portion'] = array("smallint unsigned", null, "NO", "", "10", "");
    $arr['user_ident']['preffered_db'] = array("tinyint unsigned", null, "YES", "", null, "");
    $arr['user_ident']['date_format'] = array("char(4)", "utf8mb3_bin", "NO", "", ".dmy", "");
    $arr['user_ident']['hide_list'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['user_ident']['match_case'] = array("tinyint unsigned", null, "NO", "", "0", "");
    $arr['visits']['id_db'] = array("tinyint unsigned", null, "NO", "PRI", null, "");
    $arr['visits']['id_user'] = array("int unsigned", null, "NO", "PRI", null, "");
    $arr['visits']['work_start'] = array("datetime", null, "YES", "", null, "");
    $arr['visits']['visit_count'] = array("int unsigned", null, "NO", "", null, "");
    $arr['visits']['working_mode'] = array("tinyint", null, "NO", "", "0", "");
    $arr['visits']['visit_session'] = array("varchar(255)", "utf8mb3_bin", "NO", "", null, "");
    $arr['visits']['rest_time'] = array("mediumint unsigned", null, "NO", "", "0", "");
    return $arr;
}
function TestSpecialInterfaceTexts($dbh)
{
    $_SESSION['spec_titles'] = array("table_types"=>Title(266), "compare_mode"=>Title(400), "sort_mode"=>Title(424), "field_align"=>Title(451), "field_using"=>Title(452), "field_types"=>Title(453), "group_types"=>Title(454), "z_o"=>Title(582));
    $special_interface = GetSpecialNumbers($dbh);
    foreach ($special_interface as $k => $v) foreach ($v['numbers'] as $z) if ($z[1] > 0) return true;
    return false;
}

?>
