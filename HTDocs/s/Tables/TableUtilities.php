<?php
function GetTableValue($dbh, $table_name, $value_name, $arrt = true)
{
	$gv = ($arrt) ? array() : "";
	$res = mysqli_query($dbh, "SELECT ".$value_name." FROM table_definitions WHERE table_name = '".$table_name."'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res)) 
		{
			if ($row[0] != "")
			{
				if ($arrt)
				{
					$arr_pairs = explode($_SESSION['char_group'], $row[0]);
					foreach ($arr_pairs as $v) $gv[] = $v;
				}
				else $gv = $row[0];
			}
		}
		else $gv = ($arrt) ? array() : "";
		mysqli_free_result($res);
	}
	return $gv;
}
function ReduceMaxLevel($dbh, $table)
{
	$res = mysqli_query($dbh, "SELECT * FROM ".$table);
	if ($res)
	{
		$max_level = 0;
		while ($row = mysqli_fetch_row($res))
		{
			$n = substr_count($row[1], ",");
			if ($n > $max_level) $max_level = $n;
		}
		if (mysqli_num_rows($res) == 0) $max_level = 0;
		else $max_level++;
		$res_def = mysqli_query($dbh, "SELECT max_level FROM table_definitions WHERE table_name = '".$table."'");
		if ($res_def)
		{
			if ($row_def = mysqli_fetch_row($res_def))
			{
				if ($row_def[0] != $max_level) mysqli_query($dbh, "UPDATE table_definitions SET max_level = ".(string)$max_level." WHERE table_name = '".$table."'");
			}
			mysqli_free_result($res_def);
		}
		mysqli_free_result($res);
	}
}
function ActionTableExit(&$Mes)
{
	$Mes = array();
	if ($_SESSION['user_working_mode'] == 0) return true;
	$t_flags = TestTables($_SESSION['user_working_mode'] == 0);
	if (!IsMandatoryTableCorrect($t_flags['mandatory']) || count($t_flags['d_second_catalogs']) > 0 || $t_flags['empty'] || $t_flags['errors'])
	{
        MessageOnNonUniqueSecondaryTables($t_flags['d_second_catalogs'], $Mes);
        MessageOnMandatoryDBTables($t_flags['mandatory'], $Mes);
		$Mes[] = "<b>".Title(223)."</b>";
		return false;
	}
    else return true;
}
function GetTableDefinitions($dbh, $db)
{
    $DB_all_tables = array_keys($_SESSION['all_field_list']);
	$_SESSION['single_catalogs'] = array();
	$t_def = array();
	$no_definition = array();
    $no_DB = array();
	$res = mysqli_query($dbh, "SELECT * FROM table_definitions");
	if ($res)
	{
        while ($row = mysqli_fetch_row($res))
        {
            $t_def[$row[0]] = array("use_type"=>$row[1], "illegals"=>$row[2], "max_level"=>$row[5], "separators"=>$row[4], "catalog_id"=>"", "catalog_value"=>"", "group_type"=>$row[3], "second_catalog"=>$row[6], "table_title"=>(($row[7] == "") ? $row[0] : $row[7]), "tab_err"=>array());
            if (!in_array($row[0], $DB_all_tables))
            {
                $t_def[$row[0]]['tab_err'][] = Title(427);
                $no_DB[] = $row[0];
            }
			if ($row[1] == 3) CatalogFieldDefinitions($row[0], $row[4], $t_def);
		}
		mysqli_free_result($res);
	}
	foreach ($DB_all_tables as $table)
    {
        if (!in_array($table, array_keys($t_def)))
        {
            $t_def[$table] = array("use_type"=>0, "illegals"=>"", "max_level"=>0, "separators"=>"", "catalog_id"=>"", "catalog_value"=>"", "group_type"=>0, "second_catalog"=>"", "table_title"=>"", "tab_err"=>array(Title(536)));
            $no_definition[] = $table;
        }
    }
    if (count($no_definition) > 0) $_SESSION['db_errors'][] = Title(707)." <b>table_definitions</b> ".Title(708)." ".Title(705)." ".((count($no_definition) == 1) ? FTM(Title(115)) : Title(706))." <b>".implode(", ", $no_definition)."</b>";
    if (count($no_DB) > 0) $_SESSION['db_errors'][] = FTM(Title(709))." <b>".$_SESSION['arr_db'][$db]['db_name']."</b> ".Title(708)." ".((count($no_DB) == 1) ? FTM(Title(115)) : Title(706))." <b>".implode(", ", $no_DB)."</b>";
    ksort($t_def);
	return $t_def;
}
function SetUserLangTable($dbh_sys, &$sw_break)
{
	$fl = false;
	if (AfterLangChoice($dbh_sys, "user_lang_s", "user_lang", $sw_break))
	{
		$_SESSION['table_types'] = GetSpecialTexts($dbh_sys, "table_types");
		$_SESSION['group_types'] = GetSpecialTexts($dbh_sys, "group_types");
		$fl = true;
	}
	return $fl;
}
function AfterUseTypeChoice(&$sw_break, &$Mes)
{
	if ($_POST['use_type_s'] == "") $sw_break = false;
	else
	{
        $a_p = explode("-", $_POST['use_type_s']);
        $new_table_type = array_search($_POST[$_POST['use_type_s']], $_SESSION['table_types']);
		$_SESSION['table_definitions'][$a_p[1]]['use_type'] = $new_table_type;
		if ($new_table_type == 3 && isset($_SESSION['all_field_list'][$a_p[1]])) CatalogFieldDefinitions($a_p[1], $_SESSION['table_definitions'][$a_p[1]]['separators'], $_SESSION['table_definitions']);
		else
		{
			$_SESSION['table_definitions'][$a_p[1]]['catalog_id'] = "";
			$_SESSION['table_definitions'][$a_p[1]]['catalog_value'] = "";
			$_SESSION['table_definitions'][$a_p[1]]['illegals'] = "";
			$_SESSION['table_definitions'][$a_p[1]]['max_level'] = 0;
			$_SESSION['table_definitions'][$a_p[1]]['separators'] = "";
			$_SESSION['table_definitions'][$a_p[1]]['group_type'] = 0;
			$_SESSION['table_definitions'][$a_p[1]]['second_catalog'] = "";
			if ($new_table_type == 1 || $new_table_type == 2 || $new_table_type == 5) TestThisType($a_p[1], $new_table_type, $Mes);
		}
		return true;
	}
	return false;
}
function AfterSecondCatalogChoice(&$sw_break)
{
	if ($_POST['second_catalog_s'] == "") $sw_break = false;
	else
	{
		foreach ($_SESSION['table_definitions'] as $table_name => $table_params)
		{
			if ($_POST["second_catalog-".$table_name] != $table_params['second_catalog'])
			{
				$_SESSION['table_definitions'][$table_name]['second_catalog'] = $_POST["second_catalog-".$table_name];
				return true;
			}
		}
	}
	return false;
}
function GetCatalogList($dbh)
{
	$Mes = array();
	$dbh = GetDB($_SESSION['db_info']['name'], $Mes, $_SESSION['db_info']['coding']);
	if (!$dbh) ExitSession(Title(1)." <b>".$_SESSION['db_info']['name']."</b>`".implode("`", $Mes)."|FF0000", $_SESSION['db_info']['id']);
	$arr_catalogs = array();
	$second_catalogs = array();
	$res = mysqli_query($dbh, "SELECT table_name, second_catalog_name, table_title FROM table_definitions WHERE use_type = 3");
	if ($res)
	{
		while ($row = mysqli_fetch_row($res))
		{
			$arr_catalogs[$row[0]] = array($row[1], $row[2]);
			if ($row[1] != "") $second_catalogs[] = $row[1];
		}
		mysqli_free_result($res);
	}
	foreach ($second_catalogs as $z) unset($arr_catalogs[$z]);
	return $arr_catalogs;
}
function GetCatalogDefinition($dbh, $catalog_name)
{
	$C = array("0"=>array("name"=>"", "tbl"=>"", "id"=>"", "vl"=>"", "cat_type"=>0, "spr"=>"", "max_level"=>0, "invs"=>""), "1"=>array("name"=>"", "tbl"=>"", "id"=>"", "vl"=>"", "cat_type"=>0, "spr"=>"", "max_level"=>0, "invs"=>""));
	$res = mysqli_query($dbh, "SELECT catalog_id, catalog_value, illegal_symbols, group_catalog_type, separators, max_level, second_catalog_name, table_title FROM table_definitions WHERE table_name = '".$catalog_name."'");
	if ($res)
	{
		if ($row = mysqli_fetch_row($res))
		{
			$C['0']['name'] = $row[7];
			$C['0']['table'] = $catalog_name;
			$C['0']['id'] = $row[0];
			$C['0']['value'] = $row[1];
			$C['0']['cat_type'] = $row[3];
			$C['0']['separator'] = $row[4];
			$C['0']['max_level'] = $row[5];
			$C['0']['illegal_symbols'] = $row[2];
   			$C['1']['name'] = "";
			$C['1']['table'] = $row[6];
			$C['1']['id'] = "";
			$C['1']['value'] = "";
			$C['1']['cat_type'] = 0;
			$C['1']['separator'] = "";
			$C['1']['max_level'] = 0;
			$C['1']['illegal_symbols'] = "";
			if ($row[6] != "")
			{
				$res_second = mysqli_query($dbh, "SELECT catalog_id, catalog_value, illegal_symbols, table_title FROM table_definitions WHERE table_name = '".$row[6]."'");
				if ($res_second)
				{
					if ($row_second = mysqli_fetch_row($res_second))
					{
						$C['1']['name'] = $row_second[3];
						$C['1']['id'] = $row_second[0];
						$C['1']['value'] = $row_second[1];
						$C['1']['illegal_symbols'] = $row[2];
					}
					mysqli_free_result($res_second);
				}
			}
		}
		mysqli_free_result($res);
	}
	return $C;
}

?>
